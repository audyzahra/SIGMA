@extends('layouts.super_admin')

@section('title', 'Detail Role | SIGMA')


@section('content')


    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Dashboard / Role & Permission / Detail
            </p>

            <h1>
                {{ $role->name }}
            </h1>
        </div>

        <a class="button button-primary"
            href="{{ route('super-admin.role-permissions.edit', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
            Edit Permission
        </a>

    </section>

    <section class="panel">
        <h3>
            Permission Terpasang
        </h3>

        <div class="form-grid">


            @forelse(
                $role->permissions->groupBy(fn($permission)=>explode('.',$permission->name)[0])
                as $module=>$permissions
                )

                <div>
                    <b>
                        {{ ucfirst($module) }}
                    </b>

                    <ul>
                        @foreach ($permissions as $permission)
                            <li>
                                {{ $permission->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty

                <p>
                    Belum ada permission.
                </p>

            @endforelse
        </div>
    </section>
@endsection
