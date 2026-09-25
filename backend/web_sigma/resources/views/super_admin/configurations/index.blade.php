@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/configurations.css') }}">
@endpush

@section('title', 'Konfigurasi Sistem | SIGMA')
@section('content')
    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Konfigurasi Sistem
            </p>


            <h1>
                Konfigurasi Sistem
            </h1>


            <p>
                Kelola parameter dan pengaturan utama sistem SIGMA.
            </p>

        </div>


        <a class="button button-primary" href="{{ route('super-admin.configurations.create') }}">
            Tambah Konfigurasi
        </a>

    </section>

    <section class="panel">

        <x-sigma.data-table class="configuration-table">

            <thead>

                <tr>

                    <th>
                        Key
                    </th>

                    <th>
                        Nilai
                    </th>

                    <th>
                        Tipe
                    </th>

                    <th class="action-column">
                        Aksi
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($configurations as $configuration)
                    <tr>

                        <td class="configuration-key">

                            <strong>
                                {{ $configuration->key }}
                            </strong>

                        </td>

                        <td class="configuration-value">

                            {{ Str::limit($configuration->value, 80) }}

                        </td>

                        <td>

                            <span class="badge">

                                {{ ucfirst($configuration->type) }}

                            </span>

                        </td>

                        <td>

                            <div class="table-actions">


                                <a href="{{ route('super-admin.configurations.show', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}"
                                    class="action-btn detail" title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>



                                <a href="{{ route('super-admin.configurations.edit', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}"
                                    class="action-btn edit" title="Edit">

                                    <i data-lucide="square-pen"></i>

                                </a>



                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.configurations.destroy', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}">

                                    @csrf
                                    @method('DELETE')


                                    <button class="action-btn delete" title="Hapus"
                                        onclick="return confirm('Hapus konfigurasi ini?')">

                                        <i data-lucide="trash-2"></i>

                                    </button>


                                </form>


                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4">
                            Belum ada konfigurasi.
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </x-sigma.data-table>

        {{ $configurations->links() }}

    </section>
@endsection
