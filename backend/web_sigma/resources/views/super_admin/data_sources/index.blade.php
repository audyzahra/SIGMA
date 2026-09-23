@extends('layouts.super_admin')
@section('title', 'Sumber Data | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <h1>Data Source Management</h1>
        </div><a class="button button-primary" href="{{ route('super-admin.data-sources.create') }}">Tambah Data Source</a>
    </section>
    <form class="panel filters"><input name="search" value="{{ request('search') }}" placeholder="Cari sumber data"><select
            name="type">
            <option value="">Semua tipe</option>
            @foreach (['satellite', 'weather', 'api'] as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach (['active', 'inactive'] as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </select>
        <button class="button button-light">Filter</button>
    </form>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Provider</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataSources as $dataSource)
                    <tr>
                        <td>{{ $dataSource->name }}</td>
                        <td>{{ $dataSource->provider }}</td>
                        <td>{{ $dataSource->type }}</td>
                        <td>{{ $dataSource->status }}</td>
                        <td>
                            <a class="text-action"
                                href="{{ route('super-admin.data-sources.show', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">
                                Detail
                            </a>

                            <a class="text-action"
                                href="{{ route('super-admin.data-sources.edit', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">
                                Edit
                            </a>

                            <form class="inline" method="POST"
                                action="{{ route('super-admin.data-sources.destroy', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-action" onclick="return confirm('Hapus sumber data ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada sumber data.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $dataSources->links() }}</section>
@endsection
