<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Modules\Tours\Models\Library\LibraryCabin;
use App\Modules\Tours\Models\Library\LibraryCabinGroup;
use App\Modules\Tours\Models\Library\LibraryDestination;
use App\Modules\Tours\Models\Library\LibraryPort;
use App\Modules\Tours\Models\Library\LibraryShip;
use App\Modules\Tours\Models\Library\LibraryShipCompany;
use Illuminate\View\View;

/**
 * Cruise Kütüphanesi yönetim merkezi (super-admin / central DB).
 *
 * Global katalog (gemi firması, gemi, kabin, liman, destinasyon) buradan
 * yönetilir.  Tenant'lar bu kütüphaneyi DEĞİŞTİREMEZ; "Kütüphaneden İçeri
 * Al" ile kendi DB'lerine kopyalar (LibraryImporter).
 */
class LibraryHubController extends Controller
{
    public function index(): View
    {
        $counts = [
            'ship_companies' => LibraryShipCompany::count(),
            'ships'          => LibraryShip::count(),
            'cabins'         => LibraryCabin::count(),
            'cabin_groups'   => LibraryCabinGroup::count(),
            'ports'          => LibraryPort::count(),
            'destinations'   => LibraryDestination::count(),
        ];

        return view('admin.library.index', ['counts' => $counts]);
    }
}
