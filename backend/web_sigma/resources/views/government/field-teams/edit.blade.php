@extends('layouts.government')

@section('title', 'Edit Tim Pemadam | SIGMA')


@section('content')

    <div class="page-heading">

        <div>
            <p class="breadcrumb">
                Beranda › Tim Pemadam › Edit
            </p>

            <h1>
                Edit Tim Pemadam
            </h1>

            <p>
                Perbarui informasi tim dan anggota petugas lapangan.
            </p>
        </div>


        <a href="{{ route('government.field-teams.index') }}" class="button button-light">
            ← Kembali
        </a>

    </div>



    <div class="panel">

        <form action="{{ route('government.field-teams.update', $team->id) }}" method="POST">

            @csrf
            @method('PUT')


            <div class="form-group">

                <label>
                    Nama Tim
                </label>

                <input type="text" name="team_name" value="{{ old('team_name', $team->team_name) }}" required>

            </div>



            <div class="form-group">

                <label>
                    Ketua Tim
                </label>

                <input type="text" name="leader_name" value="{{ old('leader_name', $team->leader_name) }}">

            </div>

            <div class="form-group">

                <label>
                    Pilih Anggota Petugas
                </label>


                <div class="member-list">

                    @foreach ($officers as $officer)
                        <label class="checkbox-item">


                            <input type="checkbox" name="members[]" value="{{ $officer->id }}"
                                {{ $team->members->contains('id', $officer->id) ? 'checked' : '' }}>


                            <span>
                                {{ $officer->name }}
                            </span>


                        </label>
                    @endforeach

                </div>


            </div>



            <div class="form-actions">


                <a href="{{ route('government.field-teams.index') }}" class="button button-light">
                    Batal
                </a>


                <button type="submit" class="button button-primary">
                    Simpan Perubahan
                </button>


            </div>



        </form>

    </div>


@endsection
