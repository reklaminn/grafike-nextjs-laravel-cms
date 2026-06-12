<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Library;

use App\Models\Language;
use App\Models\Tenant;
use App\Modules\Tours\Models\Cabin;
use App\Modules\Tours\Models\CabinCategory;
use App\Modules\Tours\Models\CabinGroup;
use App\Modules\Tours\Models\CabinGroupTranslation;
use App\Modules\Tours\Models\CabinTranslation;
use App\Modules\Tours\Models\Destination;
use App\Modules\Tours\Models\DestinationTranslation;
use App\Modules\Tours\Models\Library\LibraryDestination;
use App\Modules\Tours\Models\Library\LibraryPort;
use App\Modules\Tours\Models\Library\LibraryShipCompany;
use App\Modules\Tours\Models\Port;
use App\Modules\Tours\Models\PortTranslation;
use App\Modules\Tours\Models\Ship;
use App\Modules\Tours\Models\ShipCompany;
use App\Modules\Tours\Models\ShipCompanyTranslation;
use App\Modules\Tours\Models\ShipTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Central kütüphane → tenant DB kopyalama servisi (Phase 1.5.h).
 *
 * Çağıran taraf tenancy()->initialize($tenant) yapmış olmalı: library_*
 * modelleri her zaman 'central' connection'ından okur (modelde sabit),
 * tenant modelleri ise aktif (tenant) connection'a yazar.
 *
 * Idempotent: tenant kayıtları `source_library_id` ile eşlenir; varsa
 * güncellenir, yoksa oluşturulur.  Aynı slug'lı manuel kayıt varsa ona
 * bağlanır (source_library_id set edilir) — duplicate üretmez.
 *
 * FK sırası: ship_company → ship → cabin → cabin_group(+pivot);
 *            port; destination(+port pivot).
 *
 * AI özgünleştirme: kopya işlemi transaction içinde verbatim yapılır;
 * `$aiRewrite` true ise commit SONRASI açıklamalar dile göre batch'lenip
 * yeniden yazılır (best-effort, hata → orijinal korunur).
 */
class LibraryImporter
{
    /** @var list<array{model:Model, language_id:int, fields:array<string,string>}> */
    private array $rewriteJobs = [];

    public function __construct(private readonly LibraryDescriptionRewriter $rewriter)
    {
    }

    /**
     * @param array{ship_company_ids?:array<int>, port_ids?:array<int>, destination_ids?:array<int>} $selection
     */
    public function import(array $selection, bool $aiRewrite = false, ?Tenant $tenant = null): ImportResult
    {
        $this->rewriteJobs = [];
        $result = new ImportResult();

        DB::transaction(function () use ($selection, $aiRewrite, $result) {
            $this->runCopy($selection, $aiRewrite, $result);
        });

        if ($aiRewrite) {
            $this->runRewrites($tenant, $result);
        }

        return $result;
    }

    /**
     * @param array{ship_company_ids?:array<int>, port_ids?:array<int>, destination_ids?:array<int>} $selection
     */
    private function runCopy(array $selection, bool $aiRewrite, ImportResult $result): void
    {
        $portMap = [];  // library_port_id => tenant_port_id

        // 1) Doğrudan seçilen limanlar
        $portIds = array_map('intval', $selection['port_ids'] ?? []);
        if ($portIds !== []) {
            foreach (LibraryPort::with('translations')->whereIn('id', $portIds)->get() as $libPort) {
                $portMap[$libPort->id] = $this->importPort($libPort, $aiRewrite, $result);
            }
        }

        // 2) Gemi firmaları → gemiler → kabinler → kabin grupları
        $companyIds = array_map('intval', $selection['ship_company_ids'] ?? []);
        if ($companyIds !== []) {
            $companies = LibraryShipCompany::with([
                'translations',
                'ships.translations',
                'ships.cabins.translations',
                'cabinGroups.translations',
                'cabinGroups.cabins',
            ])->whereIn('id', $companyIds)->get();

            $defaultCategoryId = CabinCategory::orderBy('id')->value('id');

            foreach ($companies as $libCo) {
                $tenantCo = $this->importShipCompany($libCo, $aiRewrite, $result);
                $cabinMap = []; // library_cabin_id => tenant_cabin_id

                foreach ($libCo->ships as $libShip) {
                    $tenantShip = $this->importShip($libShip, $tenantCo->id, $aiRewrite, $result);

                    foreach ($libShip->cabins as $libCabin) {
                        $catId = $libCabin->cabin_category_slug
                            ? (CabinCategory::where('slug', $libCabin->cabin_category_slug)->value('id') ?? $defaultCategoryId)
                            : $defaultCategoryId;

                        if ($catId === null) {
                            $result->warn("Kabin '{$libCabin->code}' için kabin kategorisi bulunamadı (tenant'ta CabinCategory seed eksik) — atlandı.");
                            continue;
                        }

                        $cabinMap[$libCabin->id] = $this->importCabin($libCabin, $tenantShip->id, (int) $catId, $aiRewrite, $result);
                    }
                }

                // Kabin grupları + pivot (cabinMap üzerinden)
                foreach ($libCo->cabinGroups as $libGroup) {
                    $tenantGroup = $this->importCabinGroup($libGroup, $tenantCo->id, $aiRewrite, $result);

                    $pivot = [];
                    foreach ($libGroup->cabins as $libCabin) {
                        if (isset($cabinMap[$libCabin->id])) {
                            $pivot[$cabinMap[$libCabin->id]] = ['sort_order' => $libCabin->pivot->sort_order ?? 0];
                        }
                    }
                    if ($pivot !== []) {
                        $tenantGroup->cabins()->syncWithoutDetaching($pivot);
                    }
                }
            }
        }

        // 3) Destinasyonlar (+ portları otomatik içeri al + pivot)
        $destinationIds = array_map('intval', $selection['destination_ids'] ?? []);
        if ($destinationIds !== []) {
            $destinations = LibraryDestination::with(['translations', 'ports.translations'])
                ->whereIn('id', $destinationIds)->get();

            foreach ($destinations as $libDest) {
                foreach ($libDest->ports as $libPort) {
                    if (! isset($portMap[$libPort->id])) {
                        $portMap[$libPort->id] = $this->importPort($libPort, $aiRewrite, $result);
                    }
                }

                $tenantDest = $this->importDestination($libDest, $aiRewrite, $result);

                $pivot = [];
                foreach ($libDest->ports as $libPort) {
                    if (isset($portMap[$libPort->id])) {
                        $pivot[$portMap[$libPort->id]] = ['sort_order' => $libPort->pivot->sort_order ?? 0];
                    }
                }
                if ($pivot !== []) {
                    $tenantDest->ports()->syncWithoutDetaching($pivot);
                }
            }
        }
    }

    private function importShipCompany(LibraryShipCompany $lib, bool $ai, ImportResult $r): ShipCompany
    {
        /** @var ShipCompany $model */
        $model = $this->upsert(ShipCompany::class, $lib->id, $lib->slug, [
            'source_library_id' => $lib->id,
            'slug'              => $lib->slug,
            'name'              => $lib->name,
            'company_type'      => $lib->company_type,
            'operator'          => $lib->operator,
            'founded_year'      => $lib->founded_year,
            'headquarters'      => $lib->headquarters,
            'website'           => $lib->website,
            'uses_cabin_groups' => $lib->uses_cabin_groups,
            'is_active'         => $lib->is_active,
            'sort_order'        => $lib->sort_order,
        ], $r, 'ship_companies');

        $this->copyTranslations(
            $lib, $model, 'ship_company_id', ShipCompanyTranslation::class,
            ['description', 'meta_title', 'meta_description'],
            ['description', 'meta_description'], $ai
        );

        return $model;
    }

    private function importShip($lib, int $tenantCompanyId, bool $ai, ImportResult $r): Ship
    {
        /** @var Ship $model */
        $model = $this->upsert(Ship::class, $lib->id, $lib->slug, [
            'source_library_id'  => $lib->id,
            'ship_company_id'    => $tenantCompanyId,
            'slug'               => $lib->slug,
            'name'               => $lib->name,
            'star_rating'        => $lib->star_rating,
            'local_agent'        => $lib->local_agent,
            'flag_country_code'  => $lib->flag_country_code,
            'imo_number'         => $lib->imo_number,
            'year_built'         => $lib->year_built,
            'passenger_capacity' => $lib->passenger_capacity,
            'crew_count'         => $lib->crew_count,
            'deck_count'         => $lib->deck_count,
            'tonnage'            => $lib->tonnage,
            'length_m'           => $lib->length_m,
            'beam_m'             => $lib->beam_m,
            'cruise_speed_knots' => $lib->cruise_speed_knots,
            'facilities'         => $lib->facilities,
            'is_active'          => $lib->is_active,
            'sort_order'         => $lib->sort_order,
        ], $r, 'ships');

        $this->copyTranslations(
            $lib, $model, 'ship_id', ShipTranslation::class,
            ['description', 'meta_title', 'meta_description'],
            ['description', 'meta_description'], $ai
        );

        return $model;
    }

    private function importCabin($lib, int $tenantShipId, int $categoryId, bool $ai, ImportResult $r): int
    {
        /** @var Cabin $model */
        $model = $this->upsert(Cabin::class, $lib->id, null, [
            'source_library_id'     => $lib->id,
            'ship_id'               => $tenantShipId,
            'cabin_category_id'     => $categoryId,
            'brand_subcategory'     => $lib->brand_subcategory,
            'code'                  => $lib->code,
            'deck_name'             => $lib->deck_name,
            'max_capacity'          => $lib->max_capacity,
            'base_price_per_person' => $lib->base_price_per_person,
            'is_active'             => $lib->is_active,
            'sort_order'            => $lib->sort_order,
        ], $r, 'cabins');

        $this->copyTranslations(
            $lib, $model, 'cabin_id', CabinTranslation::class,
            ['name', 'description'],
            ['description'], $ai
        );

        return $model->id;
    }

    private function importCabinGroup($lib, int $tenantCompanyId, bool $ai, ImportResult $r): CabinGroup
    {
        /** @var CabinGroup $model */
        $model = $this->upsert(CabinGroup::class, $lib->id, null, [
            'source_library_id' => $lib->id,
            'ship_company_id'   => $tenantCompanyId,
            'slug'              => $lib->slug,
            'is_active'         => $lib->is_active,
            'sort_order'        => $lib->sort_order,
        ], $r, 'cabin_groups');

        $this->copyTranslations(
            $lib, $model, 'cabin_group_id', CabinGroupTranslation::class,
            ['name', 'description'],
            ['description'], $ai
        );

        return $model;
    }

    private function importPort(LibraryPort $lib, bool $ai, ImportResult $r): int
    {
        /** @var Port $model */
        $model = $this->upsert(Port::class, $lib->id, $lib->slug, [
            'source_library_id' => $lib->id,
            'slug'              => $lib->slug,
            'country_code'      => $lib->country_code,
            'latitude'          => $lib->latitude,
            'longitude'         => $lib->longitude,
            'population'        => $lib->population,
            'video_url'         => $lib->video_url,
            'timezone'          => $lib->timezone,
            'is_active'         => $lib->is_active,
            'sort_order'        => $lib->sort_order,
        ], $r, 'ports');

        $this->copyTranslations(
            $lib, $model, 'port_id', PortTranslation::class,
            ['name', 'short_description', 'long_description', 'meta_title', 'meta_description'],
            ['short_description', 'long_description', 'meta_description'], $ai
        );

        return $model->id;
    }

    private function importDestination(LibraryDestination $lib, bool $ai, ImportResult $r): Destination
    {
        /** @var Destination $model */
        $model = $this->upsert(Destination::class, $lib->id, $lib->slug, [
            'source_library_id'     => $lib->id,
            'slug'                  => $lib->slug,
            'latitude'              => $lib->latitude,
            'longitude'             => $lib->longitude,
            'compatible_tour_types' => $lib->compatible_tour_types,
            'is_active'             => $lib->is_active,
            'is_featured'           => $lib->is_featured,
            'sort_order'            => $lib->sort_order,
        ], $r, 'destinations');

        $this->copyTranslations(
            $lib, $model, 'destination_id', DestinationTranslation::class,
            ['name', 'description', 'meta_title', 'meta_description'],
            ['description', 'meta_description'], $ai
        );

        return $model;
    }

    /**
     * Idempotent upsert: önce source_library_id, sonra (varsa) slug ile eşle.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string,mixed>  $attrs
     */
    private function upsert(string $modelClass, int $libraryId, ?string $slug, array $attrs, ImportResult $r, string $entityKey): Model
    {
        $model = $modelClass::withTrashed()->where('source_library_id', $libraryId)->first();

        if (! $model && $slug !== null) {
            $model = $modelClass::withTrashed()->where('slug', $slug)->first();
        }

        if ($model) {
            if (method_exists($model, 'trashed') && $model->trashed()) {
                $model->restore();
            }
            $model->fill($attrs)->save();
            $r->track($entityKey, false);

            return $model;
        }

        $model = $modelClass::create($attrs);
        $r->track($entityKey, true);

        return $model;
    }

    /**
     * Library çeviri satırlarını tenant çeviri tablosuna kopyalar
     * ([fk, language_id] ile idempotent).  AI açıksa rewritable alanlar
     * commit sonrası batch işlenmek üzere kuyruğa alınır.
     *
     * @param  object                $lib                library model (->translations)
     * @param  Model                 $tenantModel        tenant parent
     * @param  string                $fkColumn           e.g. 'ship_id'
     * @param  class-string<Model>   $translationClass
     * @param  list<string>          $copyFields
     * @param  list<string>          $rewritableFields
     */
    private function copyTranslations(
        object $lib,
        Model $tenantModel,
        string $fkColumn,
        string $translationClass,
        array $copyFields,
        array $rewritableFields,
        bool $ai,
    ): void {
        foreach ($lib->translations as $t) {
            $data = [];
            foreach ($copyFields as $field) {
                $data[$field] = $t->{$field};
            }

            /** @var Model $trans */
            $trans = $translationClass::updateOrCreate(
                [$fkColumn => $tenantModel->id, 'language_id' => $t->language_id],
                $data
            );

            if ($ai && $rewritableFields !== []) {
                $fields = [];
                foreach ($rewritableFields as $field) {
                    $value = $trans->{$field};
                    if (is_string($value) && trim($value) !== '') {
                        $fields[$field] = $value;
                    }
                }
                if ($fields !== []) {
                    $this->rewriteJobs[] = [
                        'model'       => $trans,
                        'language_id' => (int) $t->language_id,
                        'fields'      => $fields,
                    ];
                }
            }
        }
    }

    /**
     * Commit sonrası: kuyruktaki çevirileri dile göre grupla, batch
     * yeniden yaz, geri kaydet.  Best-effort (rewriter hatada orijinali döner).
     */
    private function runRewrites(?Tenant $tenant, ImportResult $result): void
    {
        if ($this->rewriteJobs === []) {
            return;
        }

        $byLang = [];
        foreach ($this->rewriteJobs as $index => $job) {
            $byLang[$job['language_id']][$index] = $job;
        }

        foreach ($byLang as $languageId => $jobs) {
            $language = Language::find($languageId);
            if (! $language) {
                continue;
            }

            $flat = [];
            foreach ($jobs as $index => $job) {
                foreach ($job['fields'] as $field => $text) {
                    $flat["{$index}::{$field}"] = $text;
                }
            }

            $rewritten = $this->rewriter->rewrite($flat, $language, $tenant);

            foreach ($jobs as $index => $job) {
                $changed = false;
                foreach ($job['fields'] as $field => $original) {
                    $new = $rewritten["{$index}::{$field}"] ?? $original;
                    if (is_string($new) && $new !== $original) {
                        $job['model']->{$field} = $new;
                        $changed = true;
                        $result->rewritten++;
                    }
                }
                if ($changed) {
                    $job['model']->save();
                }
            }
        }
    }
}
