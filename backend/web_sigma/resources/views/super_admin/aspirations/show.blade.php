@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/aspirations.css') }}">
@endpush


@section('title', 'Detail Aspirasi | SIGMA')


@section('content')


    <section class="page-heading">


        <div>


            <p class="breadcrumb">
                Dashboard / Aspirasi Masyarakat / Detail
            </p>


            <h1>
                Detail Aspirasi
            </h1>

        </div>

    </section>




    <section class="panel">



        <div class="panel-title">


            <div>

                <h3>
                    {{ $aspiration->name }}
                </h3>


                <p>
                    Dikirim {{ $aspiration->created_at->format('d M Y H:i') }}
                </p>

            </div>




            @if ($aspiration->status == 'pending')
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



            <div class="info-item">

                <b>
                    Email
                </b>

                <strong>
                    {{ $aspiration->email }}
                </strong>

            </div>



            <div class="info-item">

                <b>
                    No HP
                </b>

                <strong>
                    {{ $aspiration->phone }}
                </strong>

            </div>



            <div class="info-item">

                <b>
                    Organisasi
                </b>

                <strong>
                    {{ $aspiration->organization ?? '-' }}
                </strong>

            </div>



            <div class="info-item">

                <b>
                    Wilayah
                </b>

                <strong>
                    {{ $aspiration->region ?? '-' }}
                </strong>

            </div>



        </div>





        <div class="content-box">


            <h3>
                Isi Aspirasi
            </h3>


            <p>
                {{ $aspiration->description }}
            </p>


        </div>





        <div class="content-box">


            <h3>
                Update Status
            </h3>



            <form method="POST" action="{{ route('super-admin.aspirations.status', $aspiration) }}" class="status-form">


                @csrf
                @method('PATCH')



                <label>

                    Status Aspirasi


                    <select name="status">


                        <option value="pending" @selected($aspiration->status == 'pending')>
                            Pending
                        </option>


                        <option value="done" @selected($aspiration->status == 'done')>
                            Selesai
                        </option>


                    </select>


                </label>



                <button class="button button-primary">

                    Update Status

                </button>



            </form>


        </div>



    </section>


@endsection
