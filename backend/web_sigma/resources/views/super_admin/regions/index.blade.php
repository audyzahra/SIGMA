@extends('layouts.super_admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/regions.css') }}">
@endpush
@section('title', 'Wilayah | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Wilayah
            </p>
            <h1>Manajemen Wilayah</h1>
            <p>Wilayah administratif SIGMA.</p>
        </div>
        <a class="button button-primary" href="{{ route('super-admin.regions.create') }}">Tambah Wilayah</a>
    </section>

    <form class="panel filters region-filter" method="GET" action="{{ route('super-admin.regions.index') }}">
        <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode"><select name="level">
            <option value="">Semua level</option>
            @foreach (['province', 'regency', 'district'] as $level)
                <option value="{{ $level }}" @selected(request('level') === $level)>{{ ucfirst($level) }}</option>
            @endforeach
        </select>
        <button class="button button-light">Filter</button>
    </form>

    <section class="panel"><x-sigma.data-table class="region-table">
            <thead>
                <tr>

                    <th>
                        Wilayah
                    </th>

                    <th>
                        Level
                    </th>

                    <th>
                        Wilayah Induk
                    </th>

                    <th>
                        Luas Area
                    </th>

                    <th class="action-column">
                        Aksi
                    </th>

                </tr>
            </thead>

            <tbody>
                @forelse($regions as $region)
                    <tr>
                        <td class="region-name">

                            <strong>
                                {{ $region->name }}
                            </strong>

                            <small>
                                {{ $region->code ?? '-' }}
                            </small>

                        </td>
                        <td>{{ ucfirst($region->level) }}</td>
                        <td>{{ $region->parent?->name ?? '—' }}</td>
                        <td>{{ $region->area_size ?? '—' }}</td>
                        <td>
                            <div class="table-actions">

                                <a href="{{ route('super-admin.regions.show', \App\Helpers\EncryptHelper::encrypt($region->id)) }}"
                                    class="action-btn detail" title="Detail">
                                    <i data-lucide="eye"></i>
                                </a>

                                <a href="{{ route('super-admin.regions.edit', \App\Helpers\EncryptHelper::encrypt($region->id)) }}"
                                    class="action-btn edit" title="Edit">
                                    <i data-lucide="square-pen"></i>
                                </a>

                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.regions.destroy', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button class="action-btn delete" title="Hapus"
                                        onclick="return confirm('Hapus wilayah ini?')">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>

                            </div>
                        </td>

                </tr>@empty<tr>

                        <td colspan="5">Belum ada wilayah.</td>
                    </tr>
                @endforelse
            </tbody>

        </x-sigma.data-table>{{ $regions->links() }}
        
    </section>
@endsection
