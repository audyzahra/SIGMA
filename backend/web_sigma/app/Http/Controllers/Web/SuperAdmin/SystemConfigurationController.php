<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemConfigurationController extends Controller
{
    public function index(Request $request): View
    {
        $configurations = SystemConfiguration::when(
                $request->search,
                fn ($query, $value) => $query->where(
                    fn ($query) => $query
                        ->where('key', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%")
                )
            )
            ->when(
                $request->type,
                fn ($query, $value) => $query->where('type', $value)
            )
            ->orderBy('key')
            ->paginate(15)
            ->withQueryString();

        return view(
            'super_admin.configurations.index',
            compact('configurations')
        );
    }

    public function create(): View
    {
        return view('super_admin.configurations.create');
    }

    public function store(
        Request $request,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $configuration = SystemConfiguration::create(
            $this->validated($request)
        );

        $audit->log(
            $request,
            'CREATE',
            'system_configurations',
            "Membuat konfigurasi {$configuration->key}",
            null,
            $configuration
        );

        return redirect()
            ->route('super-admin.configurations.index')
            ->with('success', 'Konfigurasi dibuat.');
    }

    public function show(string $configuration): View
    {
        $id = EncryptHelper::decrypt($configuration);

        $configuration = SystemConfiguration::findOrFail($id);

        return view(
            'super_admin.configurations.show',
            compact('configuration')
        );
    }

    public function edit(string $configuration): View
    {
        $id = EncryptHelper::decrypt($configuration);

        $configuration = SystemConfiguration::findOrFail($id);

        return view(
            'super_admin.configurations.edit',
            compact('configuration')
        );
    }

    public function update(
        Request $request,
        string $configuration,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($configuration);

        $configuration = SystemConfiguration::findOrFail($id);

        $old = clone $configuration;

        $configuration->update(
            $this->validated($request, $configuration)
        );

        $audit->log(
            $request,
            'UPDATE',
            'system_configurations',
            "Memperbarui konfigurasi {$configuration->key}",
            $old,
            $configuration
        );

        return redirect()
            ->route(
                'super-admin.configurations.show',
                EncryptHelper::encrypt($configuration->id)
            )
            ->with('success', 'Konfigurasi diperbarui.');
    }

    public function destroy(
        Request $request,
        string $configuration,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($configuration);

        $configuration = SystemConfiguration::findOrFail($id);

        $old = clone $configuration;

        $configuration->delete();

        $audit->log(
            $request,
            'DELETE',
            'system_configurations',
            "Menghapus konfigurasi {$old->key}",
            $old
        );

        return redirect()
            ->route('super-admin.configurations.index')
            ->with('success', 'Konfigurasi dihapus.');
    }

    private function validated(
        Request $request,
        ?SystemConfiguration $configuration = null
    ): array {
        $data = $request->validate([
            'key' => [
                'required',
                'string',
                'max:100',
                Rule::unique('system_configurations', 'key')
                    ->ignore($configuration)
            ],

            'value' => [
                'nullable',
                'string'
            ],

            'type' => [
                'required',
                Rule::in([
                    'string',
                    'integer',
                    'boolean'
                ])
            ],

            'description' => [
                'nullable',
                'string'
            ]
        ]);

        if (
            $data['type'] === 'integer' &&
            $data['value'] !== null &&
            filter_var($data['value'], FILTER_VALIDATE_INT) === false
        ) {
            validator(
                [],
                ['value' => 'required'],
                [
                    'value.required' =>
                        'Nilai harus berupa bilangan bulat.'
                ]
            )->validate();
        }

        if (
            $data['type'] === 'boolean' &&
            $data['value'] !== null &&
            ! in_array(
                strtolower($data['value']),
                ['0', '1', 'true', 'false'],
                true
            )
        ) {
            validator(
                [],
                ['value' => 'required'],
                [
                    'value.required' =>
                        'Nilai boolean harus true, false, 1, atau 0.'
                ]
            )->validate();
        }

        return $data;
    }
}