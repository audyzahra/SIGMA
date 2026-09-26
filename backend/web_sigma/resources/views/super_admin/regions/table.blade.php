<x-sigma.data-table class="region-table">
    <thead>
        <tr>

            <th>
                Wilayah
            </th>

            <th>
                Level
            </th>

            <th>
                Wilayah Induk
            </th>

            <th>
                Luas Area
            </th>

            <th class="action-column">
                Aksi
            </th>

        </tr>
    </thead>

    <tbody>
        @forelse($regions as $region)
            <tr>
                <td class="region-name">

                    <strong>
                        {{ $region->name }}
                    </strong>

                    <small>
                        {{ $region->code ?? '-' }}
                    </small>

                </td>
                <td>{{ ucfirst($region->level) }}</td>
                <td>{{ $region->parent?->name ?? '—' }}</td>
                <td>{{ $region->area_size ?? '—' }}</td>
                <td>
                    <div class="table-actions">

                        <a href="{{ route('super-admin.regions.show', \App\Helpers\EncryptHelper::encrypt($region->id)) }}"
                            class="action-btn detail" title="Detail">
                            <i data-lucide="eye"></i>
                        </a>

                        <a href="{{ route('super-admin.regions.edit', \App\Helpers\EncryptHelper::encrypt($region->id)) }}"
                            class="action-btn edit" title="Edit">
                            <i data-lucide="square-pen"></i>
                        </a>

                        <form class="inline-action delete-form" method="POST"
                            action="{{ route('super-admin.regions.destroy', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="action-btn delete delete-confirm" title="Hapus">

                                <i data-lucide="trash-2"></i>

                            </button>
                        </form>

                    </div>
                </td>

        </tr>@empty<tr>

                <td colspan="5">Belum ada wilayah.</td>
            </tr>
        @endforelse
    </tbody>

</x-sigma.data-table>{{ $regions->links() }}
