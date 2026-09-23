@extends('layouts.super_admin') @section('content')
    <section class="page-heading">
        <div>
            <h1>{{ $aiModel->name }}</h1>
        </div>
        <a class="button button-primary"
            href="{{ route('super-admin.ai-models.edit', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
            Edit
        </a>
    </section>
    <section class="panel info-card"><b>Kode</b><strong>{{ $aiModel->code }}</strong><b>Jenis</b><strong>{{ $aiModel->type }}
            /
            {{ $aiModel->input_type }}</strong><b>Versi</b><strong>{{ $aiModel->version ?? '—' }}</strong><b>Framework</b><strong>{{ $aiModel->framework ?? '—' }}</strong><b>Akurasi</b><strong>{{ $aiModel->accuracy ?? '—' }}</strong><b>Status</b><strong>{{ $aiModel->status }}</strong><b>Deskripsi</b><strong>{{ $aiModel->description ?? '—' }}</strong>
    </section>
@endsection
