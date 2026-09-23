@extends('layouts.super_admin') @section('content')
    <section class="page-heading">
        <h1>Edit Model AI</h1>
    </section>
    <form class="panel" method="POST"
        action="{{ route('super-admin.ai-models.update', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
        @csrf @method('PUT')
        @include('super_admin.ai_models.form')<button class="button button-primary">Simpan</button></form>
@endsection
