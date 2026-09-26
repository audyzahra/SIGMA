<form method="POST"
action="{{ route('government.field-teams.store') }}">

@csrf


<label>
Nama Tim
</label>

<input
name="team_name"
class="form-control">


<label>
Ketua Tim
</label>

<input
name="leader_name"
class="form-control">


<label>
Anggota Petugas
</label>


@foreach($officers as $officer)

<div>

<input
type="checkbox"
name="members[]"
value="{{ $officer->id }}"
>

{{ $officer->name }}

</div>


@endforeach


<button>
Simpan Tim
</button>


</form>
