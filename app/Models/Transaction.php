<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'nomor', 'tanggal', 'jenis', 'supplier_id', 'distributor_id',
        'agent_id', 'user_id', 'total',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPihakNamaAttribute(): string
    {
        if ($this->jenis === 'masuk') {
            return $this->supplier?->nama ?? '-';
        }

        return $this->agent?->nama ?? $this->distributor?->nama ?? '-';
    }

    public function getJenisPembeliAttribute(): ?string
    {
        if ($this->jenis !== 'keluar') {
            return null;
        }

        return $this->agent_id ? 'Agen' : 'Distributor';
    }
}