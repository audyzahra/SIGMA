@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/audit_logs.css') }}">
@endpush


@section('title', 'Audit Trail | SIGMA')


@section('content')


    <section class="page-heading">


        <div>

            <p class="breadcrumb">
                Dashboard / Audit Trail
            </p>


            <h1>
                Audit Trail
            </h1>


            <p>
                Riwayat aktivitas pengguna dan perubahan sistem SIGMA.
            </p>


        </div>

        <p>Riwayat aktivitas hanya-baca.</p>


    </section>




    <section class="panel">


        <x-sigma.data-table class="audit-table">


            <thead>

                <tr>

                    <th>
                        Waktu
                    </th>


                    <th>
                        Pengguna
                    </th>


                    <th>
                        Aksi
                    </th>


                    <th>
                        Modul
                    </th>


                    <th class="action-column">
                        Aksi
                    </th>


                </tr>


            </thead>



            <tbody>


                @forelse($auditLogs as $auditLog)
                    <tr>


                        <td>

                            {{ $auditLog->created_at }}

                        </td>



                        <td>

                            <strong>
                                {{ $auditLog->user?->name ?? 'Sistem' }}
                            </strong>

                        </td>




                        <td>

                            <span class="badge action-{{ strtolower($auditLog->action) }}">

                                {{ $auditLog->action }}

                            </span>

                        </td>




                        <td>

                            {{ $auditLog->module }}

                        </td>




                        <td>


                            <div class="table-actions">


                                @php
                                    $encryptedId = \App\Helpers\EncryptHelper::encrypt($auditLog->id);
                                @endphp



                                <a href="{{ route('super-admin.audit-logs.show', $encryptedId) }}" class="action-btn detail"
                                    title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>



                            </div>


                        </td>


                    </tr>


                @empty


                    <tr>

                        <td colspan="5">

                            Belum ada audit log.

                        </td>

                    </tr>
                @endforelse


            </tbody>



        </x-sigma.data-table>


        {{ $auditLogs->links() }}


    </section>


@endsection
