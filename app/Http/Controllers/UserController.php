<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserController extends MasterController
{
    protected string $model = User::class;
    protected string $route = 'users';
    protected string $title = 'User';
    protected array $columns = [
        'nama' => 'Nama Lengkap',
        'username' => 'Username',
        'role' => 'Role',
    ];
    protected array $search = ['nama', 'username', 'role'];

    protected function fields(?Model $row = null): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'rules' => 'required|string|max:255'],
            [
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'rules' => 'required|string|max:50|unique:users,username'.($row ? ','.$row->id : ''),
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'hint' => $row ? 'Kosongkan jika password tidak diubah.' : 'Minimal 6 karakter.',
                'rules' => $row ? 'nullable|string|min:6' : 'required|string|min:6',
            ],
            [
                'name' => 'role',
                'label' => 'Role',
                'type' => 'select',
                'options' => ['Admin' => 'Admin', 'Kasir' => 'Kasir', 'Gudang' => 'Gudang'],
                'rules' => 'required|in:Admin,Kasir,Gudang',
            ],
        ];
    }

    protected function beforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }

    public function destroy($id)
    {
        if ((int) $id === auth()->id()) {
            return back()->with('err', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        return parent::destroy($id);
    }
}