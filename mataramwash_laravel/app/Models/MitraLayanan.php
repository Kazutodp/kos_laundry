<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraLayanan extends Model
{
    use HasFactory;

    protected $table = 'mitra_layanan';

    protected $fillable = [
        'mitra_id',
        'nama_layanan',
        'harga',
        'detail',
        'kategori',
    ];

    /**
     * Relationship: Service belongs to a Partner.
     */
    public function partner()
    {
        return $this->belongsTo(MitraLaundry::class, 'mitra_id');
    }
}
