<div class="configuration-fields">


    <div class="two-column">


        <label>

            Key Konfigurasi

            <input type="text" name="key" required value="{{ old('key', $configuration->key ?? '') }}">

            @error('key')
                <small class="error-text">
                    {{ $message }}
                </small>
            @enderror

        </label>




        <label>

            Tipe Data

            <select name="type" required>

                @foreach (['string', 'integer', 'boolean'] as $v)
                    <option value="{{ $v }}" @selected(old('type', $configuration->type ?? 'string') === $v)>

                        {{ ucfirst($v) }}

                    </option>
                @endforeach

            </select>

        </label>


    </div>



    <label>

        Nilai Konfigurasi


        <textarea name="value" rows="5" placeholder="Masukkan nilai konfigurasi...">{{ old('value', $configuration->value ?? '') }}</textarea>


    </label>




    <label>

        Deskripsi


        <textarea name="description" rows="4" placeholder="Jelaskan fungsi konfigurasi ini...">{{ old('description', $configuration->description ?? '') }}</textarea>


    </label>


</div>
