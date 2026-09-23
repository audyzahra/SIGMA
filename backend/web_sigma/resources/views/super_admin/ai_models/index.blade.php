@extends('layouts.super_admin')
@section('content')
    <section class="page-heading">
        <div>
            <h1>AI Model Management</h1>
        </div><a class="button button-primary" href="{{ route('super-admin.ai-models.create') }}">Tambah Model</a>
    </section>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th>Model</th>
                    <th>Tipe</th>
                    <th>Versi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aiModels as $aiModel)
                    <tr>
                        <td><b>{{ $aiModel->name }}</b><small>{{ $aiModel->code }}</small></td>
                        <td>{{ $aiModel->type }}</td>
                        <td>{{ $aiModel->version }}</td>
                        <td>{{ $aiModel->status }}</td>
                        <td>
                            <a class="text-action"
                                href="{{ route('super-admin.ai-models.show', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
                                Detail
                            </a>

                            <a class="text-action"
                                href="{{ route('super-admin.ai-models.edit', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
                                Edit
                            </a>

                            <form class="inline" method="POST"
                                action="{{ route('super-admin.ai-models.destroy', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
                                @csrf
                                @method('DELETE')

                                <button class="text-action" onclick="return confirm('Hapus model ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        </tr>@empty<tr>
                        <td colspan="5">Belum ada model.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $aiModels->links() }}</section>
@endsection
