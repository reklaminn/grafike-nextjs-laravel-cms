<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Library;

use App\Modules\Tours\Models\Library\LibraryCabin;
use App\Modules\Tours\Models\Library\LibraryCabinTranslation;
use App\Modules\Tours\Models\Library\LibraryDestination;
use App\Modules\Tours\Models\Library\LibraryDestinationTranslation;
use App\Modules\Tours\Models\Library\LibraryPort;
use App\Modules\Tours\Models\Library\LibraryPortTranslation;
use App\Modules\Tours\Models\Library\LibraryShip;
use App\Modules\Tours\Models\Library\LibraryShipCompany;
use App\Modules\Tours\Models\Library\LibraryShipCompanyTranslation;
use App\Modules\Tours\Models\Library\LibraryShipTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Eski sistem CSV/XLSX export'unu central `library_*` tablolarına aktarır
 * (Phase 1.5.h, upload-tabanlı legacy import).
 *
 * Çalışma: kullanıcı dosyayı yükler, kolonları kütüphane alanlarına eşler;
 * bu servis satırları gezip eşlemeye göre upsert eder.
 *
 *   - Idempotent: `legacy_id` (eski id) eşlendiyse onunla, yoksa slug ile
 *     match → tekrar import duplicate üretmez.
 *   - İlişki çözümü: gemi → parent firma legacy_id; kabin → parent gemi
 *     legacy_id ile resolve edilir (önce parent import edilmiş olmalı).
 *   - Çeviri alanları seçilen tek dile (`language_id`) yazılır.
 *   - Tip dönüşümü: bool/int/decimal/csv_array.
 *   - Bağlantı sabit 'central' (tenancy initialize olsa bile).
 */
class CsvLibraryImporter
{
    /**
     * Desteklenen varlıklar + alan şemaları.  scalars: alan => tip.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function entitySpecs(): array
    {
        return [
            'ship_company' => [
                'label'            => 'Gemi Firması',
                'trackKey'         => 'ship_companies',
                'model'            => LibraryShipCompany::class,
                'translationModel' => LibraryShipCompanyTranslation::class,
                'translationFk'    => 'library_ship_company_id',
                'slugField'        => 'slug',
                'parent'           => null,
                'scalars'          => [
                    'slug' => 'string', 'name' => 'string', 'company_type' => 'string',
                    'operator' => 'string', 'founded_year' => 'int', 'headquarters' => 'string',
                    'website' => 'string', 'logo_url' => 'string', 'uses_cabin_groups' => 'bool',
                    'is_active' => 'bool', 'sort_order' => 'int',
                ],
                'translations'     => [
                    'description' => 'text', 'meta_title' => 'string', 'meta_description' => 'text',
                ],
            ],
            'port' => [
                'label'            => 'Liman',
                'trackKey'         => 'ports',
                'model'            => LibraryPort::class,
                'translationModel' => LibraryPortTranslation::class,
                'translationFk'    => 'library_port_id',
                'slugField'        => 'slug',
                'parent'           => null,
                'scalars'          => [
                    'slug' => 'string', 'country_code' => 'string', 'latitude' => 'decimal',
                    'longitude' => 'decimal', 'population' => 'int', 'video_url' => 'string',
                    'timezone' => 'string', 'cover_url' => 'string', 'is_active' => 'bool',
                    'sort_order' => 'int',
                ],
                'translations'     => [
                    'name' => 'string', 'short_description' => 'text', 'long_description' => 'text',
                    'meta_title' => 'string', 'meta_description' => 'text',
                ],
            ],
            'destination' => [
                'label'            => 'Destinasyon',
                'trackKey'         => 'destinations',
                'model'            => LibraryDestination::class,
                'translationModel' => LibraryDestinationTranslation::class,
                'translationFk'    => 'library_destination_id',
                'slugField'        => 'slug',
                'parent'           => null,
                'scalars'          => [
                    'slug' => 'string', 'latitude' => 'decimal', 'longitude' => 'decimal',
                    'compatible_tour_types' => 'csv_array', 'cover_url' => 'string',
                    'is_active' => 'bool', 'is_featured' => 'bool', 'sort_order' => 'int',
                ],
                'translations'     => [
                    'name' => 'string', 'description' => 'text',
                    'meta_title' => 'string', 'meta_description' => 'text',
                ],
            ],
            'ship' => [
                'label'            => 'Gemi',
                'trackKey'         => 'ships',
                'model'            => LibraryShip::class,
                'translationModel' => LibraryShipTranslation::class,
                'translationFk'    => 'library_ship_id',
                'slugField'        => 'slug',
                'parent'           => [
                    'model' => LibraryShipCompany::class,
                    'fk'    => 'library_ship_company_id',
                    'label' => 'Gemi Firması',
                ],
                'scalars'          => [
                    'slug' => 'string', 'name' => 'string', 'star_rating' => 'int',
                    'local_agent' => 'string', 'flag_country_code' => 'string', 'imo_number' => 'string',
                    'year_built' => 'int', 'passenger_capacity' => 'int', 'crew_count' => 'int',
                    'deck_count' => 'int', 'tonnage' => 'int', 'length_m' => 'decimal',
                    'beam_m' => 'decimal', 'cruise_speed_knots' => 'decimal', 'facilities' => 'csv_array',
                    'cover_url' => 'string', 'is_active' => 'bool', 'sort_order' => 'int',
                ],
                'translations'     => [
                    'description' => 'text', 'meta_title' => 'string', 'meta_description' => 'text',
                ],
            ],
            'cabin' => [
                'label'            => 'Kabin',
                'trackKey'         => 'cabins',
                'model'            => LibraryCabin::class,
                'translationModel' => LibraryCabinTranslation::class,
                'translationFk'    => 'library_cabin_id',
                'slugField'        => null,
                'parent'           => [
                    'model' => LibraryShip::class,
                    'fk'    => 'library_ship_id',
                    'label' => 'Gemi',
                ],
                'scalars'          => [
                    'cabin_category_slug' => 'string', 'brand_subcategory' => 'string',
                    'code' => 'string', 'deck_name' => 'string', 'max_capacity' => 'int',
                    'base_price_per_person' => 'int', 'is_active' => 'bool', 'sort_order' => 'int',
                ],
                'translations'     => [
                    'name' => 'string', 'description' => 'text',
                ],
            ],
        ];
    }

    /**
     * @param  string                          $entity     spec anahtarı
     * @param  array<int, array<string,mixed>> $rows       başlık-anahtarlı satırlar
     * @param  array<string, string>           $mapping    libraryField => csvHeader (+ legacy_id, parent_legacy_id)
     * @param  int|null                        $languageId çeviri alanları için dil
     */
    public function import(string $entity, array $rows, array $mapping, ?int $languageId): ImportResult
    {
        $specs = self::entitySpecs();
        if (! isset($specs[$entity])) {
            throw new InvalidArgumentException("Bilinmeyen varlık: {$entity}");
        }
        $spec   = $specs[$entity];
        $result = new ImportResult();

        DB::connection('central')->transaction(function () use ($rows, $mapping, $languageId, $spec, $entity, $result) {
            foreach ($rows as $rowNum => $row) {
                $this->importRow($row, $rowNum, $mapping, $languageId, $spec, $entity, $result);
            }
        });

        return $result;
    }

    /**
     * @param  array<string,mixed>   $row
     * @param  array<string,string>  $mapping
     * @param  array<string,mixed>   $spec
     */
    private function importRow(array $row, int $rowNum, array $mapping, ?int $languageId, array $spec, string $entity, ImportResult $result): void
    {
        $attrs = [];

        foreach ($spec['scalars'] as $field => $type) {
            $header = $mapping[$field] ?? null;
            if ($header === null || $header === '') {
                continue;
            }
            $cast = $this->cast($row[$header] ?? null, $type);
            if ($cast !== null) {
                $attrs[$field] = $cast;
            }
        }

        // legacy_id (idempotency anahtarı)
        $legacyId = null;
        if (! empty($mapping['legacy_id'])) {
            $legacyId = $this->toInt($row[$mapping['legacy_id']] ?? null);
            if ($legacyId !== null) {
                $attrs['legacy_id'] = $legacyId;
            }
        }

        // Parent ilişki çözümü (ship → company, cabin → ship)
        if ($spec['parent'] !== null) {
            $pHeader = $mapping['parent_legacy_id'] ?? null;
            $pValue  = $pHeader ? $this->toInt($row[$pHeader] ?? null) : null;
            $parent  = $pValue !== null
                ? $spec['parent']['model']::where('legacy_id', $pValue)->first()
                : null;

            if ($parent === null) {
                $result->warn("Satır " . ($rowNum + 1) . ": üst {$spec['parent']['label']} (legacy_id={$pValue}) bulunamadı — atlandı. (Önce {$spec['parent']['label']} import edilmeli.)");

                return;
            }
            $attrs[$spec['parent']['fk']] = $parent->id;
        }

        // Çeviri alanları
        $tdata = [];
        foreach ($spec['translations'] as $field => $type) {
            $header = $mapping[$field] ?? null;
            if ($header === null || $header === '') {
                continue;
            }
            $cast = $this->cast($row[$header] ?? null, $type);
            if ($cast !== null && $cast !== '') {
                $tdata[$field] = $cast;
            }
        }

        // Slug zorunluysa ve boşsa üret (çeviri adından ya da legacy id'den)
        if ($spec['slugField'] !== null) {
            $slugField = $spec['slugField'];
            if (empty($attrs[$slugField])) {
                $base = Str::slug((string) ($tdata['name'] ?? $attrs['name'] ?? ''));
                if ($base === '' && $legacyId === null) {
                    $result->warn("Satır " . ($rowNum + 1) . ": slug/ad boş ve legacy_id yok — atlandı.");

                    return;
                }
                $attrs[$slugField] = $base !== ''
                    ? ($legacyId !== null ? "{$base}-{$legacyId}" : $base)
                    : "{$entity}-{$legacyId}";
            }
        }

        // Upsert: önce legacy_id, sonra slug
        $model = null;
        if ($legacyId !== null) {
            $model = $spec['model']::where('legacy_id', $legacyId)->first();
        }
        if ($model === null && $spec['slugField'] !== null && ! empty($attrs[$spec['slugField']])) {
            $model = $spec['model']::where($spec['slugField'], $attrs[$spec['slugField']])->first();
        }

        if ($model !== null) {
            $model->fill($attrs)->save();
            $result->track($spec['trackKey'], false);
        } else {
            $model = $spec['model']::create($attrs);
            $result->track($spec['trackKey'], true);
        }

        // Çeviri satırı
        if ($languageId !== null && $tdata !== []) {
            $spec['translationModel']::updateOrCreate(
                [$spec['translationFk'] => $model->id, 'language_id' => $languageId],
                $tdata
            );
        }
    }

    private function cast(mixed $raw, string $type): mixed
    {
        if ($raw === null) {
            return null;
        }
        $value = is_string($raw) ? trim($raw) : $raw;

        return match ($type) {
            'int'       => $this->toInt($value),
            'decimal'   => is_numeric($value) ? (float) $value : null,
            'bool'      => $this->toBool($value),
            'csv_array' => $this->toArray($value),
            default     => ($value === '' ? null : (string) $value), // string/text
        };
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function toBool(mixed $value): bool
    {
        $v = is_string($value) ? mb_strtolower(trim($value)) : $value;

        return in_array($v, [1, '1', true, 'true', 'yes', 'evet', 'aktif', 'x', 'e'], true);
    }

    /**
     * @return array<int, string>|null
     */
    private function toArray(mixed $value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        $parts = array_values(array_filter(array_map('trim', preg_split('/[,;|]/', $value) ?: [])));

        return $parts === [] ? null : $parts;
    }
}
