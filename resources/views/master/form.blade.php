```blade
@extends('layouts.app')

@section('title', ($row ? 'Ubah ' : 'Tambah ') . $title)

@section('content')
<style>
    .user-form-page {
        color: #243247;
        animation: formMasuk .35s ease;
    }

    @keyframes formMasuk {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-form-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .user-form-heading h2 {
        margin: 0 0 6px;
        font-size: 25px;
        font-weight: 750;
    }

    .user-form-heading p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
    }

    .user-form-card {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 13px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, .04);
    }

    .user-form-card h3 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 700;
    }

    .user-form-description {
        margin: 0 0 24px;
        color: #64748b;
        font-size: 12px;
    }

    .user-form-field {
        margin-bottom: 19px;
    }

    .user-form-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 650;
        color: #334155;
    }

    .user-form-field input:not([type="checkbox"]),
    .user-form-field select,
    .user-form-field textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 11px 13px;
        border: 1px solid #d7dfeb;
        border-radius: 8px;
        background: #fff;
        color: #243247;
        font-family: inherit;
        font-size: 13px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .user-form-field input:focus,
    .user-form-field select:focus,
    .user-form-field textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
    }

    .user-form-field small {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 11px;
    }

    .user-form-error {
        color: #dc2626;
        margin-top: 6px;
        font-size: 12px;
    }

    .user-form-alert {
        padding: 13px 16px;
        margin-bottom: 18px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: 13px;
    }

    .user-form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        border-top: 1px solid #edf0f5;
        padding-top: 18px;
        margin-top: 8px;
    }

    .user-form-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 17px;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s, transform .2s;
    }

    .user-form-btn:hover {
        transform: translateY(-1px);
    }

    .user-form-save {
        background: #2563eb;
        color: #fff;
    }

    .user-form-save:hover {
        background: #1d4ed8;
    }

    .user-form-cancel {
        background: #e2e8f0;
        color: #334155;
    }

    .user-form-cancel:hover {
        background: #cbd5e1;
    }

    @media (max-width: 600px) {
        .user-form-card {
            padding: 17px;
        }

        .user-form-heading h2 {
            font-size: 22px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .user-form-page {
            animation: none;
        }
    }
</style>

<div class="user-form-page">
    <div class="user-form-heading">
        <div>
            <h2>{{ $row ? 'Ubah ' : 'Tambah ' }}{{ $title }}</h2>
            <p>Kelola dan perbarui data {{ strtolower($title) }}.</p>
        </div>

        <a class="user-form-btn user-form-cancel"
           href="{{ route($route . '.index') }}">
            ← Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="user-form-alert">
            <strong>Periksa kembali data yang kamu masukkan.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="user-form-card">
        <h3>Informasi {{ $title }}</h3>
        <p class="user-form-description">
            Lengkapi kolom berikut dengan benar.
        </p>

        <form method="POST"
              action="{{ $row
                  ? route($route . '.update', $row->id)
                  : route($route . '.store') }}">

            @csrf

            @if ($row)
                @method('PUT')
            @endif

            @foreach ($fields as $field)
                @php
                    $name = $field['name'];
                    $type = $field['type'] ?? 'text';
                    $value = old($name, $row ? $row->{$name} : '');
                @endphp

                <div class="user-form-field">
                    <label for="{{ $name }}">
                        {{ $field['label'] }}

                        @if (!str_contains($field['rules'] ?? '', 'nullable'))
                            <span style="color:#dc2626">*</span>
                        @endif
                    </label>

                    @if ($type === 'select')
                        <select id="{{ $name }}" name="{{ $name }}" required>
                            <option value="">-- Pilih {{ $field['label'] }} --</option>

                            @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}"
                                    @selected((string) $value === (string) $optionValue)>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>

                    @elseif ($type === 'textarea')
                        <textarea id="{{ $name }}"
                                  name="{{ $name }}"
                                  rows="4">{{ $value }}</textarea>

                    @elseif ($type === 'checkbox')
                        <input id="{{ $name }}"
                               type="checkbox"
                               name="{{ $name }}"
                               value="1"
                               @checked((bool) old($name, $row ? $row->{$name} : false))>

                    @else
                        <input id="{{ $name }}"
                               type="{{ $type }}"
                               name="{{ $name }}"
                               value="{{ $type === 'password' ? '' : $value }}"
                               placeholder="{{ $type === 'password' ? 'Masukkan password' : 'Masukkan ' . strtolower($field['label']) }}"
                               @if (!str_contains($field['rules'] ?? '', 'nullable'))
                                   required
                               @endif>
                    @endif

                    @if (!empty($field['hint']))
                        <small>{{ $field['hint'] }}</small>
                    @endif

                    @error($name)
                        <div class="user-form-error">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach

            <div class="user-form-actions">
                <a class="user-form-btn user-form-cancel"
                   href="{{ route($route . '.index') }}">
                    Batal
                </a>

                <button type="submit" class="user-form-btn user-form-save">
                    {{ $row ? 'Simpan Perubahan' : 'Simpan Data' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
```