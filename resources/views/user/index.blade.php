```blade
@extends('layouts.app')

@section('title', 'Data User')

@section('content')
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h2>Data User</h2>
            <p>Kelola data pengguna sistem toko.</p>
        </div>

        <a href="{{ route($route.'.create') }}"
           style="background:#2563eb; color:white; padding:10px 15px; border-radius:6px; text-decoration:none;">
            + Tambah User
        </a>
    </div>

    @if(session('ok'))
        <div style="background:#dcfce7; padding:12px; margin-bottom:15px; border-radius:6px;">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('err'))
        <div style="background:#fee2e2; padding:12px; margin-bottom:15px; border-radius:6px;">
            {{ session('err') }}
        </div>
    @endif

    <form action="{{ route($route.'.index') }}" method="GET" style="margin-bottom:20px;">
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Cari nama, username, atau role..."
               style="padding:10px; width:280px; max-width:100%; border:1px solid #ccc; border-radius:6px;">

        <button type="submit"
                style="padding:10px 16px; border:0; border-radius:6px; cursor:pointer;">
            Cari
        </button>

        <a href="{{ route($route.'.index') }}">Reset</a>
    </form>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; background:white;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:12px; border:1px solid #ddd;">No</th>

                    @foreach($columns as $col => $label)
                        <th style="padding:12px; border:1px solid #ddd;">
                            {{ $label }}
                        </th>
                    @endforeach

                    <th style="padding:12px; border:1px solid #ddd;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td style="padding:12px; border:1px solid #ddd;">
                            {{ $rows->firstItem() + $loop->index }}
                        </td>

                        @foreach($columns as $col => $label)
                            <td style="padding:12px; border:1px solid #ddd;">
                                {{ $ctrl->cell($row, $col) }}
                            </td>
                        @endforeach

                        <td style="padding:12px; border:1px solid #ddd; white-space:nowrap;">
                            <a href="{{ route($route.'.edit', $row->id) }}"
                               style="color:#2563eb; text-decoration:none;">
                                Edit
                            </a>

                            <form action="{{ route($route.'.destroy', $row->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        style="background:#dc2626; color:white; border:0; padding:6px 10px; border-radius:5px; cursor:pointer;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 2 }}"
                            style="padding:20px; text-align:center;">
                            Data user belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $rows->links() }}
    </div>
</div>
@endsection
```