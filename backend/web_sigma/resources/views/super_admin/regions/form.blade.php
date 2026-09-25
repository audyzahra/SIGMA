<div class="region-fields">


    <div class="two-column">


        <label>

            Nama Wilayah

            <input
                name="name"
                value="{{ old('name', $region->name ?? '') }}"
                required
            >

            @error('name')
                <small class="error-text">
                    {{ $message }}
                </small>
            @enderror

        </label>




        <label>

            Kode Wilayah

            <input
                name="code"
                value="{{ old('code', $region->code ?? '') }}"
            >

            @error('code')
                <small class="error-text">
                    {{ $message }}
                </small>
            @enderror

        </label>




        <label>

            Level Wilayah

            <select name="level" required>

                @foreach(['province','regency','district'] as $level)

                    <option
                        value="{{ $level }}"
                        @selected(
                            old('level', $region->level ?? '') === $level
                        )
                    >
                        {{ ucfirst($level) }}
                    </option>

                @endforeach

            </select>

        </label>




        <label>

            Wilayah Induk

            <select name="parent_id">

                <option value="">
                    Tidak ada
                </option>


                @foreach($parents as $parent)

                    <option
                        value="{{ $parent->id }}"
                        @selected(
                            (string) old(
                                'parent_id',
                                $region->parent_id ?? ''
                            )
                            ===
                            (string) $parent->id
                        )
                    >
                        {{ $parent->name }}
                    </option>

                @endforeach


            </select>

        </label>




        <label>

            Luas Area (km²)

            <input
                type="number"
                step="0.01"
                min="0"
                name="area_size"
                value="{{ old('area_size', $region->area_size ?? '') }}"
            >

            @error('area_size')
                <small class="error-text">
                    {{ $message }}
                </small>
            @enderror

        </label>



    </div>


</div>