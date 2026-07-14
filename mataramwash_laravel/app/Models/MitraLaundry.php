<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraLaundry extends Model
{
    use HasFactory;

    // Explicitly define the table name as it is singular in the DB
    protected $table = 'mitra_laundry';

    protected $fillable = [
        'nama_mitra',
        'foto_toko',
        'latitude',
        'longitude',
        'google_maps_link',
        'alamat',
        'no_telp',
        'rating',
        'harga_per_kg',
        'jam_buka',
        'status_buka',
        'icon_type',
    ];

    /**
     * Relationship: A Mitra has many Services.
     */
    public function layanan()
    {
        return $this->hasMany(MitraLayanan::class, 'mitra_id');
    }

    /**
     * Relationship: A Mitra has many Orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'mitra_id');
    }
}
