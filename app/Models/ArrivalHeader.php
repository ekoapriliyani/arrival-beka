<?php

namespace App\Models;

use Database\Factories\ArrivalHeaderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArrivalHeader extends Model
{
    /** @use HasFactory<ArrivalHeaderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'tanggal',
        'item',
        'description',
        'supplier_id',
    ];

    /**
     * Get the supplier this arrival was booked against.
     *
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the raw QR tag scans belonging to this arrival.
     *
     * @return HasMany<ArrivalDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(ArrivalDetail::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
        ];
    }
}
