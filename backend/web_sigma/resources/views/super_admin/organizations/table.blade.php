<x-sigma.data-table class="organization-table">

    <thead>
        <tr>

            <th>
                Organisasi
            </th>

            <th>
                Tipe
            </th>

            <th>
                Wilayah
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

        @forelse($organizations as $organization)
            <tr>

                <td class="organization-name">

                    <strong>
                        {{ $organization->name }}
                    </strong>

                    <small>
                        {{ $organization->email ?? 'Tidak ada email' }}
                    </small>

                </td>


                <td>

                    <span class="badge type-{{ $organization->type }}">
                        {{ ucfirst($organization->type) }}
                    </span>

                </td>


                <td>
                    {{ $organization->region?->name ?? '—' }}
                </td>


                <td>

                    <span class="badge status-{{ $organization->status }}">
                        {{ ucfirst($organization->status) }}
                    </span>

                </td>


                <td>

                    <div class="table-actions">


                        <a href="{{ route('super-admin.organizations.show', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}"
                            class="action-btn detail">

                            <i data-lucide="eye"></i>

                        </a>


                        <a href="{{ route('super-admin.organizations.edit', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}"
                            class="action-btn edit">

                            <i data-lucide="square-pen"></i>

                        </a>



                        <form class="inline-action delete-form" method="POST"
                            action="{{ route('super-admin.organizations.destroy', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">

                            @csrf
                            @method('DELETE')


                            <button type="submit" class="action-btn delete delete-confirm" title="Hapus">

                                <i data-lucide="trash-2"></i>

                            </button>


                        </form>


                    </div>

                </td>


            </tr>


        @empty


            <tr>

                <td colspan="5">
                    Belum ada organisasi.
                </td>

            </tr>
        @endforelse


    </tbody>


</x-sigma.data-table>


{{ $organizations->links() }}
