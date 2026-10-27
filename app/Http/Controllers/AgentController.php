<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Model;

class AgentController extends MasterController
{
    protected string $model = Agent::class;
    protected string $route = 'agents';
    protected string $title = 'Agen';
    protected array $columns = [
        'nama' => 'Nama Agen',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'distributor_id' => 'Distributor',
    ];
    protected array $search = ['nama', 'alamat', 'telepon'];
    protected array $with = ['distributor'];

    public function cell(Model $row, string $col)
    {
        return $col === 'distributor_id' ? $row->distributor?->nama : parent::cell($row, $col);
    }

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Agen', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            ['name' => 'telepon', 'label' => 'Telepon', 'type' => 'text', 'rules' => 'required|string|max:30'],
            [
                'name' => 'distributor_id',
                'label' => 'Distributor (membawahi)',
                'type' => 'select',
                'options' => Distributor::orderBy('nama')->pluck('nama', 'id')->all(),
                'rules' => 'required|exists:distributors,id',
            ],
        ];
    }
}