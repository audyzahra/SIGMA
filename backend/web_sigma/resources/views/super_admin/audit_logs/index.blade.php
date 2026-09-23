@extends('layouts.super_admin') @section('content')
    <section class="page-heading">
        <h1>Audit Trail</h1>
        <p>Riwayat aktivitas hanya-baca.</p>
    </section>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Modul</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $auditLog)
                    <tr>
                        <td>{{ $auditLog->created_at }}</td>
                        <td>{{ $auditLog->user?->name ?? 'Sistem' }}</td>
                        <td>{{ $auditLog->action }}</td>
                        <td>{{ $auditLog->module }}</td>
                        <td>
                            @php
                                $encryptedId = \App\Helpers\EncryptHelper::encrypt($auditLog->id);
                            @endphp

                            <a class="text-action" href="{{ route('super-admin.audit-logs.show', $encryptedId) }}">
                                Detail
                            </a>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada audit log.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $auditLogs->links() }}</section>
@endsection
