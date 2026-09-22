@extends('layouts.super_admin')

@php
    use App\Helpers\EncryptHelper;
@endphp

@section('title','Aspirasi Masyarakat')

@section('content')

<section class="page-heading">
    <div>
        <h1>Aspirasi Masyarakat</h1>
        <p>Daftar masukan dan laporan yang dikirim oleh masyarakat.</p>
    </div>
</section>


<div class="panel">

    <div class="panel-title">
        <div>
            <h3>Data Aspirasi</h3>
            <p>Total aspirasi masyarakat</p>
        </div>

        <span class="pill">
            {{ $aspirations->total() }} Data
        </span>
    </div>


    <div class="table-wrap">

        <table class="data-table">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>


            <tbody>

            @foreach($aspirations as $item)

                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>


                    <td>
                        <b>{{ $item->name }}</b>
                    </td>


                    <td>
                        {{ $item->email }}
                    </td>


                    <td>
                        {{ $item->phone }}
                    </td>


                    <td>
                        {{ $item->region ?? '-' }}
                    </td>


                    <td>

                        @if($item->status=='pending')

                            <span class="status pending">
                                Pending
                            </span>

                        @else

                            <span class="status">
                                Selesai
                            </span>

                        @endif

                    </td>


                    <td>

                        <a href="{{ route(
                            'super-admin.aspirations.show',
                            EncryptHelper::encrypt($item->id)
                            ) }}"
                            class="button button-primary">

                            Detail

                        </a>

                    </td>


                </tr>


            @endforeach

            </tbody>


        </table>

    </div>


    <div class="pagination">
        {{ $aspirations->links() }}
    </div>


</div>


@endsection