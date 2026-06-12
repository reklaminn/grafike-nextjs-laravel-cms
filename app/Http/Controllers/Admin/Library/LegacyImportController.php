<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Services\Library\CsvLibraryImporter;
use App\Modules\Tours\Support\ArraySheetImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Upload-tabanlı legacy import (Phase 1.5.h).
 *
 * Akış: (1) varlık seç + CSV/XLSX yükle → (2) kolon eşle → (3) import.
 * Eski canlı DB'ye bağlanmak yerine export edip yükleme (kullanıcı tercihi);
 * şema önceden bilinmek zorunda değil — kullanıcı kolonları runtime'da eşler.
 */
class LegacyImportController extends Controller
{
    private const TMP_DIR = 'library-imports';

    public function form(): View
    {
        return view('admin.library.import.form', [
            'specs' => CsvLibraryImporter::entitySpecs(),
        ]);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $specs = CsvLibraryImporter::entitySpecs();

        $data = $request->validate([
            'entity' => ['required', 'string', 'in:' . implode(',', array_keys($specs))],
            'file'   => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:20480'],
        ]);

        $sheets = Excel::toArray(new ArraySheetImport, $request->file('file'));
        $rows   = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return back()->with('error', 'Dosyada başlık + en az bir veri satırı bulunamadı.');
        }

        $headers = array_values(array_map(static fn ($h) => trim((string) $h), $rows[0]));
        $samples = array_slice($rows, 1, 5);

        // Geçici sakla — eşleme adımından sonra import bunu okuyacak.
        $token = $request->file('file')->store(self::TMP_DIR, 'local');

        return view('admin.library.import.map', [
            'entity'    => $data['entity'],
            'spec'      => $specs[$data['entity']],
            'headers'   => $headers,
            'samples'   => $samples,
            'dataCount' => count($rows) - 1,
            'token'     => $token,
            'languages' => Language::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $specs = CsvLibraryImporter::entitySpecs();

        $data = $request->validate([
            'entity'      => ['required', 'string', 'in:' . implode(',', array_keys($specs))],
            'token'       => ['required', 'string'],
            'language_id' => ['nullable', 'integer'],
            'map'         => ['required', 'array'],
            'map.*'       => ['nullable', 'string'],
        ]);

        $token = $data['token'];
        if (! Str::startsWith($token, self::TMP_DIR . '/') || ! Storage::disk('local')->exists($token)) {
            return redirect()->route('admin.library.import.form')
                ->with('error', 'Yüklenen dosya bulunamadı, lütfen tekrar yükleyin.');
        }

        $sheets  = Excel::toArray(new ArraySheetImport, $token, 'local');
        $rows    = $sheets[0] ?? [];
        $headers = array_values(array_map(static fn ($h) => trim((string) $h), array_shift($rows) ?? []));

        $mapping = array_filter($data['map'], static fn ($v) => $v !== null && $v !== '');
        $assoc   = [];
        foreach ($rows as $row) {
            if (count(array_filter($row, static fn ($v) => $v !== null && $v !== '')) === 0) {
                continue; // boş satır
            }
            $assoc[] = $this->zip($headers, array_values($row));
        }

        $result = (new CsvLibraryImporter)->import(
            $data['entity'],
            $assoc,
            $mapping,
            isset($data['language_id']) ? (int) $data['language_id'] : null
        );

        Storage::disk('local')->delete($token);

        $flash = $result->summary();
        if ($result->warnings !== []) {
            $flash .= ' (' . count($result->warnings) . ' uyarı: ' . implode(' | ', array_slice($result->warnings, 0, 5)) . ')';
        }

        return redirect()->route('admin.library.import.form')->with('success', $flash);
    }

    /**
     * Başlıklarla satırı eşle; uzunluk farkını güvenle yönet.
     *
     * @param  array<int, string> $headers
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function zip(array $headers, array $row): array
    {
        $out = [];
        foreach ($headers as $i => $header) {
            if ($header === '') {
                continue;
            }
            $out[$header] = $row[$i] ?? null;
        }

        return $out;
    }
}
