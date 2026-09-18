@extends('layouts.super_admin')


@section('title', 'Dashboard Super Admin')


@section('page-title')

    Dashboard Super Admin

@endsection



@section('content')


    <div class="grid grid-cols-4 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Total User
            </p>


            <h1 class="text-4xl font-bold">
                1245
            </h1>

        </div>



        <div class="bg-red-600 text-white rounded-xl shadow p-6">

            <p>
                Kebakaran Aktif
            </p>


            <h1 class="text-4xl font-bold">
                18
            </h1>

        </div>



        <div class="bg-orange-500 text-white rounded-xl shadow p-6">

            <p>
                Risiko Tinggi
            </p>


            <h1 class="text-4xl font-bold">
                42
            </h1>

        </div>



        <div class="bg-green-600 text-white rounded-xl shadow p-6">

            <p>
                Wilayah Aman
            </p>


            <h1 class="text-4xl font-bold">
                210
            </h1>

        </div>



    </div>



    <div class="mt-8 bg-white rounded-xl shadow p-6">


        <h2 class="font-bold text-xl">

            Aktivitas Sistem

        </h2>


        <ul class="mt-5 space-y-3">

            <li>
                🔥 Laporan kebakaran baru masuk
            </li>


            <li>
                🛰️ Data satelit diperbarui
            </li>


            <li>
                🤖 AI selesai melakukan analisis
            </li>


        </ul>


    </div>



@endsection
