@extends('layouts.super_admin')
@section('title', 'Organisasi | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">Super Admin / Organisasi</p>
            <h1>Organization Management</h1>
            <p>Direktori organisasi yang terhubung ke SIGMA.</p>
        </div><a class="button button-primary" href="{{ route('super-admin.organizations.create') }}">Tambah Organisasi</a>
    </section>
    <form class="panel filters" method="GET"><input name="search" value="{{ request('search') }}"
            placeholder="Cari organisasi"><select name="type">
            <option value="">Semua tipe</option>
            @foreach (['government', 'team', 'company'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="button button-light">Filter</button>
    </form>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $organization)
                    <tr>
                        <td><b>{{ $organization->name }}</b><small>{{ $organization->email }}</small></td>
                        <td><span class="pill">{{ ucfirst($organization->type) }}</span></td>
                        <td>{{ $organization->region?->name ?? '—' }}</td>
                        <td><span class="status {{ $organization->status }}">{{ ucfirst($organization->status) }}</span>
                        </td>
                        <td>
                            <a class="text-action"
                                href="{{ route('super-admin.organizations.show', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">
                                Detail
                            </a>

                            <a class="text-action"
                                href="{{ route('super-admin.organizations.edit', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">
                                Edit
                            </a>

                            <form class="inline" method="POST"
                                action="{{ route('super-admin.organizations.destroy', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="text-action"
                                    onclick="return confirm('Hapus organisasi ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        </tr>@empty<tr>
                        <td colspan="5">Belum ada organisasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $organizations->links() }}</section>
@endsection
