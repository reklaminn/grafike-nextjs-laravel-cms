<?php

declare(strict_types=1);

namespace App\Modules\Tours\Support;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * maatwebsite/excel ile CSV/XLSX dosyasını ham satır dizisine çevirir.
 *
 * Heading-row kullanmadan ilk satır = başlık olacak şekilde okur; kütüphane
 * legacy import'unda kullanıcı kolonları kendisi eşleyeceği için başlıkları
 * manuel işleriz.  CSV + XLSX + encoding farkları maatwebsite tarafında çözülür.
 */
class ArraySheetImport implements ToArray
{
    /** @var array<int, array<int, mixed>> */
    public array $rows = [];

    /**
     * @param array<int, array<int, mixed>> $array
     */
    public function array(array $array): void
    {
        $this->rows = $array;
    }
}
