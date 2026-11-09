<?php

namespace App\Http\Controllers;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;

class ScannerController extends Controller
{
    /**
     * Display the QR tag scanner page.
     */
    public function index(): View
    {
        $headers = ArrivalHeader::with('supplier')->withCount('details')->orderByDesc('id')->get();

        $activeHeader = $headers->first();

        /** @var ArrivalDetail[] $activeDetails */
        $activeDetails = $activeHeader
            ? $activeHeader->details()->orderBy('id')->get()
            : collect();

        return view('scanner', [
            'headers' => $headers,
            'activeHeader' => $activeHeader,
            'activeDetails' => $activeDetails,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
