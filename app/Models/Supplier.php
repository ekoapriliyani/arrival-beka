<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * Every field a supplier barcode may map onto.
     *
     * @var list<string>
     */
    public const FIELDS = ['manufacturer', 'grade', 'heat_no', 'coil_no', 'weight', 'size', 'qr_date'];

    /**
     * The fields a supplier barcode must carry to fill an arrival detail.
     *
     * @var list<string>
     */
    public const REQUIRED_FIELDS = ['manufacturer', 'grade', 'heat_no', 'coil_no', 'weight', 'size'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'delimiter',
        'barcode_format',
    ];

    /**
     * Get the arrivals booked against this supplier.
     *
     * @return HasMany<ArrivalHeader, $this>
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(ArrivalHeader::class);
    }

    /**
     * The barcode layout as an ordered list of field names.
     *
     * @return list<string>
     */
    public function fields(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->barcode_format))));
    }

    /**
     * The supplier as shown to the user, prefixed with its code when set.
     */
    public function label(): string
    {
        return $this->code ? $this->code.' · '.$this->name : $this->name;
    }
}
