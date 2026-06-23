<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'mitra_id',
        'nama_pelanggan',
        'layanan',
        'berat_atau_qty',
        'tarif_per_kg',
        'biaya_antar_jemput',
        'total_harga',
        'status_pembayaran',
        'status_transfer',
    ];

    /**
     * Relationship: An Order belongs to a Mitra.
     */
    public function mitra()
    {
        return $this->belongsTo(MitraLaundry::class, 'mitra_id');
    }
}
