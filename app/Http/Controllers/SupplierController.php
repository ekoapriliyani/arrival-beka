<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display the supplier management page.
     */
    public function index(): View
    {
        return view('suppliers', [
            'suppliers' => Supplier::withCount('arrivals')->orderBy('name')->get(),
            'fields' => Supplier::FIELDS,
        ]);
    }

    /**
     * Register a new supplier with its own barcode layout.
     */
    public function store(Request $request): JsonResponse
    {
        $supplier = Supplier::create($this->validated($request));

        return response()->json($supplier->loadCount('arrivals'), 201);
    }

    /**
     * Update the barcode layout of an existing supplier.
     */
    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($this->validated($request, $supplier));

        return response()->json($supplier->loadCount('arrivals'));
    }

    /**
     * Delete a supplier that no arrival points at.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $arrivalCount = $supplier->arrivals()->count();

        if ($arrivalCount > 0) {
            return response()->json([
                'message' => "Supplier {$supplier->name} masih dipakai {$arrivalCount} data arrival dan tidak bisa dihapus.",
            ], 422);
        }

        $supplier->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Validate and normalise a supplier payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $uniqueName = Rule::unique('suppliers', 'name');

        if ($supplier !== null) {
            $uniqueName = $uniqueName->ignore($supplier->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'code' => ['nullable', 'string', 'max:20'],
            'delimiter' => ['required', 'string', 'max:10'],
            'barcode_format' => ['required', 'string', 'max:255', $this->barcodeFormatRule()],
        ]);

        $validated['code'] = $validated['code'] !== null ? trim($validated['code']) : null;
        $validated['delimiter'] = trim($validated['delimiter']);
        $validated['barcode_format'] = implode(',', $this->splitFormat($validated['barcode_format']));

        return $validated;
    }

    /**
     * Reject unknown, duplicated or missing barcode fields.
     *
     * @return Closure(string, mixed, Closure): void
     */
    private function barcodeFormatRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $fields = $this->splitFormat((string) $value);

            if ($fields === []) {
                $fail('Format barcode wajib diisi.');

                return;
            }

            $unknown = array_diff($fields, Supplier::FIELDS);

            if ($unknown !== []) {
                $fail('Field tidak dikenal: '.implode(', ', $unknown).'. Pilihan: '.implode(', ', Supplier::FIELDS).'.');

                return;
            }

            $duplicates = array_diff_assoc($fields, array_unique($fields));

            if ($duplicates !== []) {
                $fail('Field tidak boleh dobel: '.implode(', ', array_unique($duplicates)).'.');

                return;
            }

            $missing = array_diff(Supplier::REQUIRED_FIELDS, $fields);

            if ($missing !== []) {
                $fail('Field wajib belum ada: '.implode(', ', $missing).'.');
            }
        };
    }

    /**
     * Split a barcode layout into its ordered field names.
     *
     * @return list<string>
     */
    private function splitFormat(string $format): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $format))));
    }
}
