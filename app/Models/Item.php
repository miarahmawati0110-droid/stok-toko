<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga',
        'stok',
        'tanggal_masuk',
        'supplier_id',
        'distributor_id',
        'user_id',
        'foto',
    ];

    /**
     * Relasi barang dengan supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi barang dengan distributor.
     */
    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    /**
     * Relasi barang dengan pengguna.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
