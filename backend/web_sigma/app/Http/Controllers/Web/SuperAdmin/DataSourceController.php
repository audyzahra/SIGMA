<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DataSourceController extends Controller
{
    public function index(Request $request): View
    {
        $dataSources = DataSource::when($request->search, fn ($query, $value) => $query->where(fn ($query) => $query->where('name', 'like', "%{$value}%")->orWhere('provider', 'like', "%{$value}%")))->when($request->type, fn ($query, $value) => $query->where('type', $value))->when($request->status, fn ($query, $value) => $query->where('status', $value))->latest()->paginate(15)->withQueryString();

        return view('super_admin.data_sources.index', compact('dataSources'));
    }

    public function create(): View
    {
        return view('super_admin.data_sources.create');
    }

    public function store(Request $request, SuperAdminAuditService $audit): RedirectResponse
    {
        $source = DataSource::create($this->validated($request));
        $audit->log($request, 'CREATE', 'data_sources', "Membuat sumber data {$source->name}", null, $source);

        return redirect()->route('super-admin.data-sources.index')->with('success', 'Sumber data dibuat.');
    }

    public function show(DataSource $dataSource): View
    {
        return view('super_admin.data_sources.show', compact('dataSource'));
    }

    public function edit(DataSource $dataSource): View
    {
        return view('super_admin.data_sources.edit', compact('dataSource'));
    }

    public function update(Request $request, DataSource $dataSource, SuperAdminAuditService $audit): RedirectResponse
    {
        $old = clone $dataSource;
        $dataSource->update($this->validated($request, $dataSource));
        $audit->log($request, 'UPDATE', 'data_sources', "Memperbarui sumber data {$dataSource->name}", $old, $dataSource);

        return redirect()->route('super-admin.data-sources.show', $dataSource)->with('success', 'Sumber data diperbarui.');
    }

    public function destroy(Request $request, DataSource $dataSource, SuperAdminAuditService $audit): RedirectResponse
    {
        $old = clone $dataSource;
        $dataSource->delete();
        $audit->log($request, 'DELETE', 'data_sources', "Menghapus sumber data {$old->name}", $old);

        return redirect()->route('super-admin.data-sources.index')->with('success', 'Sumber data dihapus.');
    }

    private function validated(Request $request, ?DataSource $source = null): array
    {
        $rules = ['name' => ['required', 'string', 'max:255'], 'provider' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::in(['satellite', 'weather', 'api'])], 'api_endpoint' => ['nullable', 'url', 'max:65535'], 'status' => ['required', Rule::in(['active', 'inactive'])], 'last_sync_at' => ['nullable', 'date']];
        if ($request->filled('credentials_key')) {
            $rules['credentials_key'] = ['string', 'max:255'];
        }

return $request->validate($rules);
    }
}
