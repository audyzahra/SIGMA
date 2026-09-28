@extends('layouts.government')

@section('title', 'Detail Tim Pemadam | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/field_team.css') }}">
@endpush

@section('content')

    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Beranda › Tim Pemadam › Detail
            </p>

            <h1>
                {{ $team->team_name }}
            </h1>

            <p>
                Informasi lengkap tim petugas lapangan.
            </p>

        </div>


        <div class="field-team-header-actions">

            <a href="{{ route(
                    'government.field-teams.edit',
                    \App\Helpers\EncryptHelper::encrypt($team->id)
                ) }}"
               class="button button-primary">
                Edit Tim
            </a>

            <a href="{{ route('government.field-teams.index') }}"
               class="button button-light">
                Kembali
            </a>

        </div>

    </section>


    <section class="panel">

        <div class="info-card">

            <div>
                <b>Nama Tim</b>

                <strong>
                    {{ $team->team_name }}
                </strong>
            </div>


            <div>
                <b>Ketua Tim</b>

                <strong>
                    {{ $team->leader_name ?? '-' }}
                </strong>
            </div>


            <div>
                <b>No Telepon</b>

                <strong>
                    {{ $team->phone ?? '-' }}
                </strong>
            </div>


            <div>
                <b>Status</b>

                <span class="status {{ $team->status }}">
                    {{ ucfirst($team->status) }}
                </span>
            </div>

        </div>


        <div class="field-team-detail-divider"></div>


        <div class="field-team-member-section">

            <div class="section-label">

                <label>
                    Anggota Petugas
                </label>

                <span>
                    Petugas yang tergabung dalam tim ini.
                </span>

            </div>


            <div class="detail-member-list">

                @forelse($team->members as $member)

                    <span class="member-pill">
                        {{ $member->name }}
                    </span>

                @empty

                    <p class="empty-detail-member">
                        Belum ada anggota.
                    </p>

                @endforelse

            </div>

        </div>

    </section>

@endsection