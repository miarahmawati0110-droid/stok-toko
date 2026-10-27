<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use Illuminate\Database\Eloquent\Model;

class DistributorController extends MasterController
{
    protected string $model = Distributor::class;
    protected string $route = 'distributors';
    protected string $title = 'Distributor';
    protected array $columns = [
        'nama' => 'Nama Distributor',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'email' => 'Email',
    ];
    protected array $search = ['nama', 'alamat', 'telepon', 'email'];

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Distributor', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'telepon', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'required|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
        ];
    }
}