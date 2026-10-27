<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class SupplierController extends MasterController
{
    protected string $model = Supplier::class;
    protected string $route = 'suppliers';
    protected string $title = 'Supplier';
    protected array $columns = [
        'nama' => 'Nama Supplier',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'email' => 'Email',
    ];
    protected array $search = ['nama', 'alamat', 'telepon', 'email'];

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Supplier', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'telepon', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'required|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
        ];
    }
}