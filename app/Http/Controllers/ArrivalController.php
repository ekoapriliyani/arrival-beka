<?php

namespace App\Http\Controllers;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArrivalController extends Controller
{
    /**
     * List every arrival header, newest first.
     */
    public function index(): JsonResponse
    {
        $headers = ArrivalHeader::with('supplier')->withCount('details')->orderByDesc('id')->get();

        return response()->json($headers);
    }

    /**
     * Store a new arrival header scanned against.
     */
    public function store(Request $request): JsonResponse
    {
        $header = ArrivalHeader::create($request->validate([
            'po_number' => ['required', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'item' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
        ]));

        return response()->json($header->load('supplier'), 201);
    }

    /**
     * Show one header together with its scanned rows.
     */
    public function show(ArrivalHeader $arrival): JsonResponse
    {
        $arrival->load(['supplier', 'details' => fn ($query) => $query->orderBy('id')]);

        return response()->json($arrival);
    }

    /**
     * Save a single QR tag scan, or a manual fallback entry, under the given header.
     */
    public function storeDetail(Request $request, ArrivalHeader $arrival): JsonResponse
    {
        $validated = $request->validate([
            'manufacturer' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'string', 'max:50'],
            'heat_no' => ['required', 'string', 'max:100'],
            'coil_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique('arrival_details', 'coil_no')->where('arrival_header_id', $arrival->id),
            ],
            'weight' => ['required', 'numeric', 'min:0'],
            'size' => ['required', 'numeric', 'min:0'],
            'qr_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', Rule::in(ArrivalDetail::SOURCES)],
        ], [
            'coil_no.unique' => 'Data duplikat: Coil No ini sudah pernah discan pada PO ini.',
        ]);

        $detail = $arrival->details()->create([
            ...$validated,
            'source' => $validated['source'] ?? 'scan',
            'scanned_at' => now(),
        ]);

        return response()->json($detail, 201);
    }

    /**
     * Clear every scan of one header, keeping the header itself.
     */
    public function clearDetails(ArrivalHeader $arrival): JsonResponse
    {
        $deleted = $arrival->details()->delete();

        return response()->json(['deleted' => $deleted]);
    }

    /**
     * Delete an arrival header and its scans.
     */
    public function destroy(ArrivalHeader $arrival): JsonResponse
    {
        $arrival->delete();

        return response()->json(['deleted' => true]);
    }
}
