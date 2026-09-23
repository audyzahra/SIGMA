@extends('layouts.super_admin')

@section('title', 'Detail Pengguna | SIGMA')

@section('content')

<section class="page-heading">
    <div>
        <p class="breadcrumb">
            Dashboard / Manajemen Pengguna / Detail
        </p>

        <h1>Detail Pengguna</h1>
    </div>

    <a class="button button-primary"
        href="{{ route('super-admin.manage-users.edit', \App\Helpers\EncryptHelper::encrypt($user->id)) }}">
        Edit Pengguna
    </a>
</section>


<section class="panel user-profile-card">


    {{-- HEADER PROFILE --}}
    <div class="user-profile-header">

        <div class="user-avatar">
            {{ strtoupper(substr($user->name,0,2)) }}
        </div>


        <div class="user-profile-info">

            <h2>
                {{ $user->name }}
            </h2>

            <p>
                {{ $user->email }}
            </p>


            <div class="user-role">

                @foreach($user->getRoleNames() as $role)
                    <span>Role</span>
                    <strong class="role-badge">
                        {{ $role }}
                    </strong>

                @endforeach

            </div>

        </div>

    </div>



    {{-- DETAIL --}}
    <div class="user-detail-grid">


        <div class="detail-item">
            <span>ID Pengguna</span>
            <strong>
                #{{ $user->id }}
            </strong>
        </div>


        <div class="detail-item">
            <span>Email</span>
            <strong>
                {{ $user->email }}
            </strong>
        </div>


        <div class="detail-item">
            <span>Dibuat</span>
            <strong>
                {{ $user->created_at->format('d M Y H:i') }}
            </strong>
        </div>


        <div class="detail-item">
            <span>Diperbarui</span>
            <strong>
                {{ $user->updated_at->format('d M Y H:i') }}
            </strong>
        </div>


    </div>


</section>


@endsection