@extends('layouts.super_admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/data_sources.css') }}">
@endpush

@section('title', 'Sumber Data | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Sumber Data
            </p>
            <h1>
                Data Source Management
            </h1>
            <p>
                Kelola sumber data yang digunakan oleh sistem SIGMA.
            </p>
        </div>
        <a class="button button-primary" href="{{ route('super-admin.data-sources.create') }}">
            Tambah Data Source
        </a>
    </section>
    <form class="panel filters data-source-filter" method="GET" action="{{ route('super-admin.data-sources.index') }}"><input
            name="search" value="{{ request('search') }}" placeholder="Cari sumber data">
        <select name="type">

            <option value="">
                Semua tipe
            </option>

            @foreach (['satellite', 'weather', 'api'] as $v)
                <option value="{{ $v }}" @selected(request('type') === $v)>
                    {{ ucfirst($v) }}
                </option>
            @endforeach

        </select>
        <select name="status">

            <option value="">
                Semua status
            </option>


            @foreach (['active', 'inactive'] as $v)
                <option value="{{ $v }}" @selected(request('status') === $v)>
                    {{ ucfirst($v) }}
                </option>
            @endforeach

        </select>
        <button class="button button-light">Filter</button>
    </form>
    <section class="panel">
        <x-sigma.data-table class="data-source-table">
            <thead>

                <tr>

                    <th>
                        Nama Data
                    </th>


                    <th>
                        Provider
                    </th>


                    <th>
                        Tipe
                    </th>


                    <th>
                        Status
                    </th>


                    <th class="action-column">
                        Aksi
                    </th>


                </tr>

            </thead>
            <tbody>
                @forelse($dataSources as $dataSource)
                    <tr>
                        <td class="data-source-name">

                            <strong>
                                {{ $dataSource->name }}
                            </strong>

                            <small>
                                {{ $dataSource->provider }}
                            </small>

                        </td>


                        <td>
                            {{ $dataSource->provider }}
                        </td>


                        <td>

                            <span class="badge">
                                {{ ucfirst($dataSource->type) }}
                            </span>

                        </td>


                        <td>

                            <span class="status {{ $dataSource->status }}">
                                {{ ucfirst($dataSource->status) }}
                            </span>

                        </td>
                        <td>

                            <div class="table-actions">


                                <a href="{{ route('super-admin.data-sources.show', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}"
                                    class="action-btn detail" title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>



                                <a href="{{ route('super-admin.data-sources.edit', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}"
                                    class="action-btn edit" title="Edit">

                                    <i data-lucide="square-pen"></i>

                                </a>



                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.data-sources.destroy', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">

                                    @csrf
                                    @method('DELETE')


                                    <button class="action-btn delete" title="Hapus"
                                        onclick="return confirm('Hapus sumber data ini?')">

                                        <i data-lucide="trash-2"></i>

                                    </button>


                                </form>


                            </div>

                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada sumber data.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $dataSources->links() }}
    </section>
@endsection
