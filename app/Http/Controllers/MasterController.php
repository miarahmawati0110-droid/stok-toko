<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

abstract class MasterController extends Controller
{
    protected string $model;
    protected string $route;
    protected string $title;
    protected array $columns = [];
    protected array $search = [];
    protected array $with = [];

    abstract protected function fields(?Model $row = null): array;

    public function cell(Model $row, string $col)
    {
        return $row->{$col};
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $query = ($this->model)::query()->with($this->with);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                foreach ($this->search as $col) {
                    $w->orWhere($col, 'like', "%{$q}%");
                }
            });
        }

        $rows = $query->orderBy($this->search[0] ?? 'id')->paginate(10)->withQueryString();

        return view('master.index', [
            'rows' => $rows,
            'q' => $q,
            'title' => $this->title,
            'route' => $this->route,
            'columns' => $this->columns,
            'ctrl' => $this,
        ]);
    }

    public function create()
    {
        return view('master.form', $this->formData(null));
    }

    public function edit($id)
    {
        $row = ($this->model)::findOrFail($id);
        return view('master.form', $this->formData($row));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        ($this->model)::create($this->beforeSave($data));

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $row = ($this->model)::findOrFail($id);
        $data = $request->validate($this->rules($row));
        $row->update($this->beforeSave($data));

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil diubah.');
    }

    public function destroy($id)
    {
        $row = ($this->model)::findOrFail($id);

        try {
            $row->delete();
        } catch (QueryException $e) {
            return back()->with('err', $this->title.' tidak bisa dihapus karena masih dipakai data lain.');
        }

        return redirect()->route($this->route.'.index')->with('ok', $this->title.' berhasil dihapus.');
    }

    protected function beforeSave(array $data): array
    {
        return $data;
    }

    protected function rules(?Model $row = null): array
    {
        $rules = [];
        foreach ($this->fields($row) as $f) {
            $rules[$f['name']] = $f['rules'];
        }
        return $rules;
    }

    private function formData(?Model $row): array
    {
        return [
            'row' => $row,
            'title' => $this->title,
            'route' => $this->route,
            'fields' => $this->fields($row),
        ];
    }
}