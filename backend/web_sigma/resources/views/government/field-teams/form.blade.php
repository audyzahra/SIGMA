<div class="field-team-fields">

    <div class="form-group">

        <label for="team_name">
            Nama Tim
        </label>

        <input
            type="text"
            id="team_name"
            name="team_name"
            value="{{ old('team_name', $team->team_name ?? '') }}"
            placeholder="Masukkan nama tim"
            autocomplete="off"
            required
        >

    </div>


    <div class="form-group">

        <label for="leader_name">
            Ketua Tim
        </label>

        <input
            type="text"
            id="leader_name"
            name="leader_name"
            value="{{ old('leader_name', $team->leader_name ?? '') }}"
            placeholder="Masukkan nama ketua tim"
            autocomplete="off"
        >

    </div>


    <div class="form-group">

        <label for="phone">
            Nomor Telepon
        </label>

        <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone', $team->phone ?? '') }}"
            placeholder="Masukkan nomor telepon"
            autocomplete="tel"
        >

    </div>

</div>


<div class="field-team-members">

    <div class="section-label">

        <label>
            Anggota Petugas
        </label>

        <span>
            Pilih petugas yang menjadi anggota tim.
        </span>

    </div>


    <div class="member-list">

        @forelse ($officers as $officer)

            <label class="checkbox-item">

                <input
                    type="checkbox"
                    name="members[]"
                    value="{{ $officer->id }}"
                    @if (isset($team) && $team->members->contains('id', $officer->id))
                        checked
                    @endif
                >

                <span>
                    {{ $officer->name }}
                </span>

            </label>

        @empty

            <div class="empty-members">
                Belum ada petugas yang tersedia.
            </div>

        @endforelse

    </div>

</div>