@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/organizations.css') }}">
@endpush


@push('scripts')
    <script src="{{ asset('js/super_admin/organizations.js') }}"></script>
@endpush
@section('title', 'Organisasi | SIGMA')
@section('content')
    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Dashboard / Organisasi
            </p>

            <h1>
                Manajemen Organisasi
            </h1>

            <p>
                Direktori organisasi yang terhubung dengan sistem SIGMA.
            </p>
        </div>


        <a class="button button-primary" href="{{ route('super-admin.organizations.create') }}">
            Tambah Organisasi
        </a>

    </section>
    <form class="panel filters organization-filter" method="GET" action="{{ route('super-admin.organizations.index') }}">

        <input name="search" value="{{ request('search') }}" placeholder="Cari organisasi">


        <select name="type">

            <option value="">
                Semua tipe
            </option>

            @foreach (['government', 'team', 'company'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach

        </select>



        <select name="status">

            <option value="">
                Semua status
            </option>


            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach


        </select>

        <button class="button button-light">
            Filter
        </button>

        @if (request()->hasAny(['search', 'type', 'status']))
            <a class="button button-light" href="{{ route('super-admin.organizations.index') }}">
                Reset
            </a>
        @endif

    </form>

    <section class="panel"><x-sigma.data-table class="organization-table">
            <thead>
                <tr>

                    <th>
                        Organisasi
                    </th>

                    <th>
                        Tipe
                    </th>

                    <th>
                        Wilayah
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
                @forelse($organizations as $organization)
                    <tr>
                        <td class="organization-name">

                            <strong>
                                {{ $organization->name }}
                            </strong>

                            <small>
                                {{ $organization->email ?? 'Tidak ada email' }}
                            </small>

                        </td>
                        <td><span class="badge type-{{ $organization->type }}">
                                {{ ucfirst($organization->type) }}
                            </span></td>
                        <td>{{ $organization->region?->name ?? '—' }}</td>
                        <td>
                            <span
                                class="badge status-{{ $organization->status }}">{{ ucfirst($organization->status) }}</span>
                        </td>
                        <td>

                            <div class="table-actions">


                                <a href="{{ route('super-admin.organizations.show', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}"
                                    class="action-btn detail" title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>



                                <a href="{{ route('super-admin.organizations.edit', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}"
                                    class="action-btn edit" title="Edit">

                                    <i data-lucide="square-pen"></i>

                                </a>



                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.organizations.destroy', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">

                                    @csrf
                                    @method('DELETE')


                                    <button class="action-btn delete" title="Hapus"
                                        onclick="return confirm('Hapus organisasi ini?')">

                                        <i data-lucide="trash-2"></i>

                                    </button>


                                </form>


                            </div>

                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada organisasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $organizations->links() }}</section>
@endsection
