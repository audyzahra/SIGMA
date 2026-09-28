<div class="data-source-fields">


    <div class="two-column">


        <label>

            Nama Data Source

            <input
                name="name"
                required
                value="{{ old('name',$dataSource->name ?? '') }}"
            >

            @error('name')
                <small class="error-text">
                    {{ $message }}
                </small>
            @enderror

        </label>




        <label>

            Provider

            <input
                name="provider"
                required
                value="{{ old('provider',$dataSource->provider ?? '') }}"
            >

        </label>




        <label>

            Tipe

            <select name="type">


                @foreach(['satellite','weather','api'] as $v)

                    <option
                        value="{{ $v }}"
                        @selected(old('type',$dataSource->type ?? '') === $v)
                    >
                        {{ ucfirst($v) }}
                    </option>

                @endforeach


            </select>

        </label>




        <label>

            Status

            <select name="status">


                @foreach(['active','inactive'] as $v)

                    <option
                        value="{{ $v }}"
                        @selected(old('status',$dataSource->status ?? 'active') === $v)
                    >
                        {{ ucfirst($v) }}
                    </option>

                @endforeach


            </select>


        </label>




        <label>

            Endpoint API

            <input
                type="url"
                name="api_endpoint"
                value="{{ old('api_endpoint',$dataSource->api_endpoint ?? '') }}"
            >

        </label>




        <label>

            Credential Key

            <small>
                Tidak ditampilkan setelah disimpan
            </small>


            <input
                type="password"
                name="credentials_key"
            >

        </label>




        <label>

            Sinkronisasi Terakhir


            <input
                type="datetime-local"
                name="last_sync_at"
                value="{{ old(
                    'last_sync_at',
                    isset($dataSource) && $dataSource->last_sync_at
                    ? $dataSource->last_sync_at->format('Y-m-d\TH:i')
                    : ''
                ) }}"
            >


        </label>


    </div>


</div>