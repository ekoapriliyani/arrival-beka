<?php

namespace App\Models;

use Database\Factories\ArrivalDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrivalDetail extends Model
{
    /** @use HasFactory<ArrivalDetailFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'arrival_header_id',
        'manufacturer',
        'grade',
        'heat_no',
        'coil_no',
        'weight',
        'size',
        'qr_date',
        'source',
        'scanned_at',
    ];

    /**
     * Where the row came from: a QR scan or a manual entry.
     *
     * @var list<string>
     */
    public const SOURCES = ['scan', 'manual'];

    /**
     * Get the arrival header this scan belongs to.
     *
     * @return BelongsTo<ArrivalHeader, $this>
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(ArrivalHeader::class, 'arrival_header_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'size' => 'float',
            'qr_date' => 'date:Y-m-d',
            'scanned_at' => 'datetime',
        ];
    }
}
