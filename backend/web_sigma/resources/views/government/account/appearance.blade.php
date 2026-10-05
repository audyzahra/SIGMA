@extends('layouts.government')

@section('title', 'Tampilan | SIGMA')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/government/account/appearance.css') }}"
    >
@endpush

@section('content')

<div class="dashboard-page">

    {{-- Heading --}}
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                BERANDA › PENGATURAN › TAMPILAN
            </p>

            <h1>Tampilan</h1>

            <p>
                Atur tampilan dashboard sesuai dengan preferensi Anda.
            </p>
        </div>
    </section>


    {{-- Back --}}
    <a
        href="{{ route('government.account.index') }}"
        class="settings-back"
    >
        <i data-lucide="arrow-left"></i>
        Kembali ke Pengaturan
    </a>


    {{-- Appearance Card --}}
    <section class="appearance-card">

        <div class="appearance-section">

            <div class="appearance-section-heading">

                <div class="appearance-heading-icon">
                    <i data-lucide="palette"></i>
                </div>

                <div>
                    <h2>Tema Tampilan</h2>

                    <p>
                        Pilih tema yang nyaman digunakan saat bekerja.
                    </p>
                </div>

            </div>


            <div class="theme-options">

                {{-- Light --}}
                <label class="theme-option">

                    <input
                        type="radio"
                        name="theme_preview"
                        value="light"
                        {{ $preference->theme === 'light' ? 'checked' : '' }}
                    >

                    <span class="theme-preview theme-light">

                        <span class="preview-topbar"></span>

                        <span class="preview-body">
                            <span class="preview-sidebar"></span>

                            <span class="preview-content">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </span>

                    </span>

                    <span class="theme-option-info">
                        <strong>Terang</strong>
                        <small>Tampilan terang dan bersih</small>
                    </span>

                </label>


                {{-- Dark --}}
                <label class="theme-option">

                    <input
                        type="radio"
                        name="theme_preview"
                        value="dark"
                        {{ $preference->theme === 'dark' ? 'checked' : '' }}
                    >

                    <span class="theme-preview theme-dark">

                        <span class="preview-topbar"></span>

                        <span class="preview-body">
                            <span class="preview-sidebar"></span>

                            <span class="preview-content">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </span>

                    </span>

                    <span class="theme-option-info">
                        <strong>Gelap</strong>
                        <small>Lebih nyaman pada kondisi minim cahaya</small>
                    </span>

                </label>


                {{-- System --}}
                <label class="theme-option">

                    <input
                        type="radio"
                        name="theme_preview"
                        value="system"
                        {{ $preference->theme === 'system' ? 'checked' : '' }}
                    >

                    <span class="theme-preview theme-system">

                        <span class="system-half system-light">
                            <span></span>
                        </span>

                        <span class="system-half system-dark">
                            <span></span>
                        </span>

                    </span>

                    <span class="theme-option-info">
                        <strong>Sistem</strong>
                        <small>Mengikuti pengaturan perangkat</small>
                    </span>

                </label>

            </div>

        </div>


        <div class="appearance-divider"></div>


        {{-- Font Size --}}
        <div class="appearance-section">

            <div class="appearance-section-heading">

                <div class="appearance-heading-icon">
                    <i data-lucide="type"></i>
                </div>

                <div>
                    <h2>Ukuran Tampilan</h2>

                    <p>
                        Atur ukuran teks pada dashboard.
                    </p>
                </div>

            </div>


            <div class="font-options">

                <label class="font-option">

                    <input
                        type="radio"
                        name="font_preview"
                        value="small"
                        {{ $preference->font_size === 'small' ? 'checked' : '' }}
                    >

                    <span class="font-sample font-small">
                        Aa
                    </span>

                    <span>
                        <strong>Kecil</strong>
                        <small>Lebih banyak informasi dalam satu layar</small>
                    </span>

                </label>


                <label class="font-option">

                    <input
                        type="radio"
                        name="font_preview"
                        value="medium"
                        {{ $preference->font_size === 'medium' ? 'checked' : '' }}
                    >

                    <span class="font-sample font-medium">
                        Aa
                    </span>

                    <span>
                        <strong>Standar</strong>
                        <small>Ukuran tampilan default</small>
                    </span>

                </label>


                <label class="font-option">

                    <input
                        type="radio"
                        name="font_preview"
                        value="large"
                        {{ $preference->font_size === 'large' ? 'checked' : '' }}
                    >

                    <span class="font-sample font-large">
                        Aa
                    </span>

                    <span>
                        <strong>Besar</strong>
                        <small>Lebih mudah dibaca</small>
                    </span>

                </label>

            </div>

        </div>


        <div class="appearance-divider"></div>


        {{-- Animation --}}
        <div class="appearance-section">

            <div class="appearance-section-heading">

                <div class="appearance-heading-icon">
                    <i data-lucide="sparkles"></i>
                </div>

                <div>
                    <h2>Animasi</h2>

                    <p>
                        Atur efek animasi pada antarmuka SIGMA.
                    </p>
                </div>

            </div>


            <label class="appearance-toggle">

                <span>

                    <strong>Aktifkan animasi</strong>

                    <small>
                        Gunakan animasi saat membuka menu dan berpindah halaman.
                    </small>

                </span>

                <input
                    type="hidden"
                    name="animations_enabled"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="animations_enabled"
                    value="1"
                    {{ $preference->animations_enabled ? 'checked' : '' }}
                >

                <span class="toggle-switch"></span>

            </label>

        </div>


        {{-- Save --}}
        <div class="appearance-footer">

            <form
                action="{{ route('government.account.appearance.update') }}"
                method="POST"
                id="appearance-form"
            >

                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="theme"
                    id="theme-value"
                    value="{{ $preference->theme }}"
                >

                <input
                    type="hidden"
                    name="font_size"
                    id="font-size-value"
                    value="{{ $preference->font_size }}"
                >

                <input
                    type="hidden"
                    name="animations_enabled"
                    id="animations-value"
                    value="{{ $preference->animations_enabled ? 1 : 0 }}"
                >

                <button
                    type="submit"
                    class="appearance-save-button"
                >
                    <i data-lucide="save"></i>
                    Simpan Perubahan
                </button>

            </form>

        </div>

    </section>

</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const themeOptions = document.querySelectorAll(
        'input[name="theme_preview"]'
    );

    const fontOptions = document.querySelectorAll(
        'input[name="font_preview"]'
    );

    const themeValue = document.getElementById('theme-value');
    const fontSizeValue = document.getElementById('font-size-value');

    const animationCheckbox = document.querySelector(
        'input[name="animations_enabled"][type="checkbox"]'
    );

    const animationValue = document.getElementById('animations-value');


    themeOptions.forEach(option => {

        option.addEventListener('change', function () {

            if (this.checked) {
                themeValue.value = this.value;
            }

        });

    });


    fontOptions.forEach(option => {

        option.addEventListener('change', function () {

            if (this.checked) {
                fontSizeValue.value = this.value;
            }

        });

    });


    if (animationCheckbox) {

        animationCheckbox.addEventListener('change', function () {

            animationValue.value = this.checked ? '1' : '0';

        });

    }

});
</script>

@endpush