@extends('layouts.government')

@section('title', 'Tim Pemadam | SIGMA')


@section('content')

<div class="page-heading">

    <div>
        <p class="breadcrumb">
            Beranda › Tim Pemadam
        </p>

        <h1>
            Tim Pemadam
        </h1>

        <p>
            Kelola tim petugas lapangan SIGMA.
        </p>
    </div>


    <a href="{{ route('government.field-teams.create') }}"
       class="button button-primary">
        + Tambah Tim
    </a>

</div>



<div class="panel">

<table width="100%">

<thead>
<tr>
    <th>Nama Tim</th>
    <th>Ketua</th>
    <th>Anggota</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>


<tbody>

@forelse($teams as $team)

<tr>

<td>
    {{ $team->team_name }}
</td>


<td>
    {{ $team->leader_name ?? '-' }}
</td>


<td>
    @forelse($team->members as $member)

        <span>
            {{ $member->name }}
        </span>

        @if(!$loop->last)
        ,
        @endif

    @empty

        Belum ada anggota

    @endforelse
</td>


<td>
    {{ $team->status }}
</td>


<td>

    <a href="{{ route('government.field-teams.edit', $team->id) }}"
       class="button button-light">
        ✏ Edit
    </a>


    <form action="{{ route('government.field-teams.destroy', $team->id) }}"
          method="POST"
          style="display:inline">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="button button-danger"
                onclick="return confirm('Yakin hapus tim ini?')">
            🗑 Hapus
        </button>

    </form>

</td>


</tr>


@empty

<tr>
<td colspan="5">
    Belum ada tim pemadam.
</td>
</tr>

@endforelse


</tbody>


</table>

</div>


@endsection
