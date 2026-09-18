@extends('layouts.government')


@section('title', 'Government Command Center')


@section('page-title')

    Government Command Center

@endsection



@section('content')


    <div class="grid grid-cols-3 gap-6">


        <div class="bg-red-600 text-white rounded-xl p-6">


            <p>
                Status Darurat
            </p>


            <h1 class="text-4xl font-bold">
                TINGGI
            </h1>


        </div>



        <div class="bg-orange-500 text-white rounded-xl p-6">


            <p>
                Peringatan Aktif
            </p>


            <h1 class="text-4xl font-bold">
                25
            </h1>


        </div>




        <div class="bg-green-600 text-white rounded-xl p-6">


            <p>
                Wilayah Aman
            </p>


            <h1 class="text-4xl font-bold">
                85%
            </h1>


        </div>


    </div>



    <div class="mt-8 grid grid-cols-2 gap-6">


        <div class="bg-white rounded-xl shadow p-6">


            <h2 class="font-bold text-xl">
                GIS Monitoring
            </h2>


            <div class="h-72 mt-5 bg-gray-200 rounded-xl flex items-center justify-center">

                🗺️ MAP AREA

            </div>


        </div>




        <div class="bg-white rounded-xl shadow p-6">


            <h2 class="font-bold text-xl">
                Tim Lapangan
            </h2>


            <ul class="mt-5 space-y-3">

                <li>🚒 Tim 01 Aktif</li>

                <li>🚒 Tim 02 Menuju Lokasi</li>

                <li>🚒 Tim 03 Standby</li>

            </ul>


        </div>


    </div>



@endsection
