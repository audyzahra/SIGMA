@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/ai_models.css') }}">
@endpush

@section('title', 'Model AI | SIGMA')
@section('content')
    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Model AI
            </p>


            <h1>
                AI Model Management
            </h1>


            <p>
                Kelola model kecerdasan buatan yang digunakan oleh sistem SIGMA.
            </p>


        </div>



        <a class="button button-primary" href="{{ route('super-admin.ai-models.create') }}">
            Tambah Model
        </a>


    </section>
    <section class="panel">
        <x-sigma.data-table class="ai-model-table">
            <thead>

                <tr>

                    <th>
                        Model AI
                    </th>


                    <th>
                        Tipe
                    </th>


                    <th>
                        Versi
                    </th>


                    <th>
                        Status
                    </th>


                    <th class="action-column">
                        Aksi
                    </th>


                </tr>

            </thead>
            <tbody>
                @forelse($aiModels as $aiModel)
                    <tr>
                        <td class="ai-model-name">

                            <strong>
                                {{ $aiModel->name }}
                            </strong>


                            <small>
                                {{ $aiModel->code }}
                            </small>

                        </td>



                        <td>

                            <span class="badge">
                                {{ ucfirst($aiModel->type) }}
                            </span>

                        </td>




                        <td>
                            {{ $aiModel->version ?? '—' }}
                        </td>




                        <td>

                            <span class="status {{ $aiModel->status }}">
                                {{ ucfirst($aiModel->status) }}
                            </span>

                        </td>
                        <td>


                            <div class="table-actions">


                                <a href="{{ route('super-admin.ai-models.show', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}"
                                    class="action-btn detail" title="Detail">

                                    <i data-lucide="eye"></i>

                                </a>



                                <a href="{{ route('super-admin.ai-models.edit', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}"
                                    class="action-btn edit" title="Edit">

                                    <i data-lucide="square-pen"></i>

                                </a>




                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.ai-models.destroy', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">


                                    @csrf

                                    @method('DELETE')


                                    <button class="action-btn delete" title="Hapus"
                                        onclick="return confirm('Hapus model ini?')">

                                        <i data-lucide="trash-2"></i>

                                    </button>


                                </form>


                            </div>


                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada model.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $aiModels->links() }}
    </section>
@endsection
