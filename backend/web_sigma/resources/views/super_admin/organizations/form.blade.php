<div class="organization-fields">


    <div class="two-column">


        <label>
            Nama Organisasi

            <input
                name="name"
                value="{{ old('name', $organization->name ?? '') }}"
                required
            >

            @error('name')
                <small class="error-text">{{ $message }}</small>
            @enderror

        </label>



        <label>
            Tipe Organisasi

            <select name="type" required>

                @foreach (['government', 'team', 'company'] as $type)

                    <option
                        value="{{ $type }}"
                        @selected(old('type', $organization->type ?? '') === $type)
                    >
                        {{ ucfirst($type) }}
                    </option>

                @endforeach

            </select>

        </label>



        <label>
            Wilayah

            <select name="region_id">

                <option value="">
                    Tanpa wilayah
                </option>


                @foreach ($regions as $region)

                    <option
                        value="{{ $region->id }}"
                        @selected(
                            (string) old('region_id', $organization->region_id ?? '')
                            ===
                            (string) $region->id
                        )
                    >
                        {{ $region->name }}
                    </option>

                @endforeach


            </select>

        </label>




        <label>
            Status

            <select name="status" required>


                @foreach (['active','inactive'] as $status)

                    <option
                        value="{{ $status }}"
                        @selected(
                            old('status', $organization->status ?? 'active') === $status
                        )
                    >
                        {{ ucfirst($status) }}
                    </option>

                @endforeach


            </select>

        </label>



        <label>
            Telepon

            <input
                name="phone"
                value="{{ old('phone', $organization->phone ?? '') }}"
            >

        </label>



        <label>
            Email

            <input
                type="email"
                name="email"
                value="{{ old('email', $organization->email ?? '') }}"
            >

        </label>


    </div>



    <label class="full-width">

        Alamat

        <textarea
            name="address"
            rows="4"
        >{{ old('address', $organization->address ?? '') }}</textarea>


    </label>


</div>