@extends('layouts.super_admin')
@section('content')
    <section class="page-heading">
        <div>
            <h1>{{ $dataSource->name }}</h1>
        </div><a class="button button-primary"
            href="{{ route('super-admin.data-sources.edit', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">
            Edit
        </a>
    </section>
    <section class="panel info-card">
        <b>Provider</b><strong>{{ $dataSource->provider }}</strong><b>Tipe</b><strong>{{ $dataSource->type }}</strong><b>Endpoint</b><strong>{{ $dataSource->api_endpoint ?? '—' }}</strong><b>Status</b><strong>{{ $dataSource->status }}</strong><b>Credential</b><strong>{{ $dataSource->credentials_key ? 'Tersimpan (disamarkan)' : '—' }}</strong>
    </section>
@endsection
