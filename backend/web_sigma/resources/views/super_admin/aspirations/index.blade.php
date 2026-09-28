@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/aspirations.css') }}">
@endpush


@php
    use App\Helpers\EncryptHelper;
@endphp


@section('title', 'Aspirasi Masyarakat | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Aspirasi Masyarakat
            </p>


            <h1>
                Aspirasi Masyarakat
            </h1>


            <p>
                Daftar masukan dan laporan yang dikirim oleh masyarakat.
            </p>


        </div>


    </section>




    <section class="panel">


        <div class="panel-title">

            <div>

                <h3>
                    Data Aspirasi
                </h3>


                <p>
                    Total aspirasi masyarakat
                </p>


            </div>


            <span class="pill">
                {{ $aspirations->total() }} Data
            </span>


        </div>




        <x-sigma.data-table class="aspiration-table">


            <thead>

                <tr>

                    <th>
                        No
                    </th>

                    <th>
                        Nama
                    </th>

                    <th>
                        Kontak
                    </th>

                    <th>
                        Wilayah
                    </th>

                    <th>
                        Status
                    </th>

                    <th class="action-column">
                        Aksi
                    </th>


                </tr>


            </thead>




            <tbody>


                @forelse($aspirations as $item)
                    <tr>


                        <td>
                            {{ $loop->iteration }}
                        </td>



                        <td class="aspiration-name">

                            <strong>
                                {{ $item->name }}
                            </strong>

                            <small>
                                {{ $item->organization ?? '-' }}
                            </small>

                        </td>



                        <td>

                            {{ $item->email }}

                            <br>

                            <small>
                                {{ $item->phone }}
                            </small>


                        </td>



                        <td>

                            {{ $item->region ?? '-' }}

                        </td>




                        <td>


                            @if ($item->status === 'pending')
                                <span class="status pending">
                                    Pending
                                </span>
                            @elseif($item->status === 'process')
                                <span class="status">
                                    Diproses
                                </span>
                            @else
                                <span class="status">
                                    Selesai
                                </span>
                            @endif



                        </td>



                        <td>


                            <div class="table-actions">


                                <a href="{{ route('super-admin.aspirations.show', EncryptHelper::encrypt($item->id)) }}"
                                    class="action-btn detail" title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>


                            </div>


                        </td>


                    </tr>


                @empty


                    <tr>

                        <td colspan="6">
                            Belum ada aspirasi.
                        </td>

                    </tr>
                @endforelse


            </tbody>


        </x-sigma.data-table>




        {{ $aspirations->links() }}



    </section>


@endsection
