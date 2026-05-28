<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Library;

/**
 * LibraryImporter çıktısı — hangi varlıktan kaç kayıt oluşturuldu/güncellendi
 * + uyarılar.  Admin paneline "X firma, Y gemi, Z liman içeri alındı" özeti
 * dönmek + log için.
 */
class ImportResult
{
    /** @var array<string, array{created:int, updated:int}> */
    public array $entities = [];

    public int $rewritten = 0;

    /** @var list<string> */
    public array $warnings = [];

    public function track(string $entity, bool $created): void
    {
        $this->entities[$entity] ??= ['created' => 0, 'updated' => 0];
        $this->entities[$entity][$created ? 'created' : 'updated']++;
    }

    public function warn(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function totalCreated(): int
    {
        return array_sum(array_column($this->entities, 'created'));
    }

    public function totalUpdated(): int
    {
        return array_sum(array_column($this->entities, 'updated'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'entities'      => $this->entities,
            'rewritten'     => $this->rewritten,
            'warnings'      => $this->warnings,
            'total_created' => $this->totalCreated(),
            'total_updated' => $this->totalUpdated(),
        ];
    }

    /**
     * Türkçe insan-okur özet (flash mesaj için).
     */
    public function summary(): string
    {
        $labels = [
            'ship_companies' => 'firma',
            'ships'          => 'gemi',
            'cabins'         => 'kabin',
            'cabin_groups'   => 'kabin grubu',
            'ports'          => 'liman',
            'destinations'   => 'destinasyon',
        ];

        $parts = [];
        foreach ($this->entities as $entity => $counts) {
            $n = $counts['created'] + $counts['updated'];
            if ($n > 0) {
                $parts[] = $n . ' ' . ($labels[$entity] ?? $entity);
            }
        }

        $base = $parts === [] ? 'Hiçbir kayıt içeri alınmadı' : ('İçeri alındı: ' . implode(', ', $parts));
        if ($this->rewritten > 0) {
            $base .= " · {$this->rewritten} açıklama AI ile özgünleştirildi";
        }

        return $base . '.';
    }
}
