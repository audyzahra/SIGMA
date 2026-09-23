@extends('layouts.super_admin')


@section('title','Detail Aspirasi')


@section('content')


<section class="page-heading">

    <div>
        <p class="breadcrumb">
            Beranda › Aspirasi › Detail
        </p>

        <h1>
            Detail Aspirasi
        </h1>

        <p>
            Informasi lengkap aspirasi masyarakat.
        </p>
    </div>


    <a href="{{ route('super-admin.aspirations.index') }}"
       class="button button-light">
        Kembali
    </a>

</section>



<div class="panel">


    <div class="panel-title">

        <div>

            <h3>
                {{ $aspiration->name }}
            </h3>

            <p>
                Dikirim {{ $aspiration->created_at->format('d M Y H:i') }}
            </p>

        </div>


        @if($aspiration->status == 'pending')

            <span class="status pending">
                Pending
            </span>

        @elseif($aspiration->status == 'process')

            <span class="status">
                Diproses
            </span>

        @else

            <span class="status">
                Selesai
            </span>

        @endif


    </div>



    <div class="info-card">

        <div>
            <strong>Email</strong>
            <br>
            {{ $aspiration->email }}
        </div>


        <div>
            <strong>No HP</strong>
            <br>
            {{ $aspiration->phone }}
        </div>


        <div>
            <strong>Organisasi</strong>
            <br>
            {{ $aspiration->organization ?? '-' }}
        </div>


        <div>
            <strong>Wilayah</strong>
            <br>
            {{ $aspiration->region ?? '-' }}
        </div>

    </div>




    <div class="panel">

        <h3>
            Isi Aspirasi
        </h3>


        <p>
            {{ $aspiration->description }}
        </p>


    </div>




    <div class="panel">


        <h3>
            Update Status
        </h3>



        <form method="POST"
            action="{{ route('super-admin.aspirations.status',$aspiration) }}"
            class="form-grid">


            @csrf

            @method('PATCH')


            <label>
                Status Aspirasi

                <select name="status">


                    <option value="pending"
                    {{ $aspiration->status == 'pending' ? 'selected':'' }}>
                        Pending
                    </option>

                    <option value="done"
                    {{ $aspiration->status == 'done' ? 'selected':'' }}>
                        Selesai
                    </option>


                </select>

            </label>



            <button class="button button-primary">
                Update Status
            </button>


        </form>


    </div>



</div>


@endsection