@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/field_team.css') }}">
@endpush

@section('title', 'Tim Pemadam | SIGMA')

@section('content')

    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Beranda › Tim Pemadam
            </p>

            <h1>
                Tim Pemadam
            </h1>

            <p>
                Kelola tim petugas lapangan SIGMA.
            </p>
        </div>

        <a href="{{ route('government.field-teams.create') }}"
           class="button button-primary">
            + Tambah Tim
        </a>

    </section>


    <section class="panel">

        <x-sigma.data-table class="field-team-table">

            <thead>
                <tr>
                    <th>Nama Tim</th>
                    <th>Ketua</th>
                    <th>Anggota</th>
                    <th>Status</th>
                    <th class="action-column">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($teams as $team)

                    <tr>

                        <td>
                            <div class="field-team-name">
                                <strong>
                                    {{ $team->team_name }}
                                </strong>

                                <small>
                                    {{ $team->phone ?? '-' }}
                                </small>
                            </div>
                        </td>

                        <td>
                            {{ $team->leader_name ?? '-' }}
                        </td>

                        <td>
                            @forelse($team->members as $member)

                                <span class="member-pill">
                                    {{ $member->name }}
                                </span>

                            @empty

                                -

                            @endforelse
                        </td>

                        <td>
                            <span class="status {{ $team->status }}">
                                {{ ucfirst($team->status) }}
                            </span>
                        </td>

                        <td>
                            <div class="table-actions">

                                <a href="{{ route(
                                        'government.field-teams.show',
                                        \App\Helpers\EncryptHelper::encrypt($team->id)
                                    ) }}"
                                   class="action-btn detail"
                                   title="Detail">
                                    <i data-lucide="eye"></i>
                                </a>

                                <a href="{{ route(
                                        'government.field-teams.edit',
                                        \App\Helpers\EncryptHelper::encrypt($team->id)
                                    ) }}"
                                   class="action-btn edit"
                                   title="Edit">
                                    <i data-lucide="square-pen"></i>
                                </a>

                                <form class="inline-action delete-form"
                                      method="POST"
                                      action="{{ route(
                                          'government.field-teams.destroy',
                                          \App\Helpers\EncryptHelper::encrypt($team->id)
                                      ) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="action-btn delete delete-confirm"
                                            title="Hapus">
                                        <i data-lucide="trash-2"></i>
                                    </button>

                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            Belum ada tim pemadam.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </x-sigma.data-table>

        {{ $teams->links() }}

    </section>

@endsection