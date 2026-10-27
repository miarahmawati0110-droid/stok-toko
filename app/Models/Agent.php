<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = ['distributor_id', 'nama', 'alamat', 'telepon'];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }
}