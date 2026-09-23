<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AIModelController extends Controller
{
    public function index(Request $request): View
    {
        $aiModels = AiModel::with('creator')
            ->when(
                $request->search,
                fn ($query, $value) => $query->where(
                    fn ($query) => $query
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%")
                )
            )
            ->when(
                $request->type,
                fn ($query, $value) => $query->where('type', $value)
            )
            ->when(
                $request->status,
                fn ($query, $value) => $query->where('status', $value)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'super_admin.ai_models.index',
            compact('aiModels')
        );
    }

    public function create(): View
    {
        return view('super_admin.ai_models.create');
    }

    public function store(
        Request $request,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $model = AiModel::create(
            $this->validated($request) + [
                'created_by' => $request->user()->id
            ]
        );

        $audit->log(
            $request,
            'CREATE',
            'ai_models',
            "Mendaftarkan model {$model->name}",
            null,
            $model
        );

        return redirect()
            ->route('super-admin.ai-models.index')
            ->with('success', 'Model AI didaftarkan.');
    }

    public function show(string $aiModel): View
    {
        $id = EncryptHelper::decrypt($aiModel);

        $aiModel = AiModel::with('creator')
            ->findOrFail($id);

        return view(
            'super_admin.ai_models.show',
            compact('aiModel')
        );
    }

    public function edit(string $aiModel): View
    {
        $id = EncryptHelper::decrypt($aiModel);

        $aiModel = AiModel::findOrFail($id);

        return view(
            'super_admin.ai_models.edit',
            compact('aiModel')
        );
    }

    public function update(
        Request $request,
        string $aiModel,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($aiModel);

        $aiModel = AiModel::findOrFail($id);

        $old = clone $aiModel;

        $aiModel->update(
            $this->validated($request, $aiModel)
        );

        $audit->log(
            $request,
            'UPDATE',
            'ai_models',
            "Memperbarui model {$aiModel->name}",
            $old,
            $aiModel
        );

        return redirect()
            ->route(
                'super-admin.ai-models.show',
                EncryptHelper::encrypt($aiModel->id)
            )
            ->with('success', 'Model AI diperbarui.');
    }

    public function destroy(
        Request $request,
        string $aiModel,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($aiModel);

        $aiModel = AiModel::findOrFail($id);

        $old = clone $aiModel;

        $aiModel->delete();

        $audit->log(
            $request,
            'DELETE',
            'ai_models',
            "Menghapus model {$old->name}",
            $old
        );

        return redirect()
            ->route('super-admin.ai-models.index')
            ->with('success', 'Model AI dihapus.');
    }

    private function validated(
        Request $request,
        ?AiModel $model = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ai_models', 'code')
                    ->ignore($model)
            ],

            'version' => [
                'nullable',
                'string',
                'max:50'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'type' => [
                'required',
                Rule::in([
                    'classification',
                    'prediction',
                    'detection',
                    'recommendation'
                ])
            ],

            'input_type' => [
                'required',
                Rule::in([
                    'image',
                    'satellite',
                    'weather',
                    'text',
                    'geo'
                ])
            ],

            'output_type' => [
                'nullable',
                'string',
                'max:100'
            ],

            'framework' => [
                'nullable',
                'string',
                'max:100'
            ],

            'algorithm' => [
                'nullable',
                'string',
                'max:100'
            ],

            'model_file' => [
                'nullable',
                'string',
                'max:255'
            ],

            'model_size' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'accuracy' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'precision_score' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'recall_score' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'f1_score' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'training_dataset' => [
                'nullable',
                'string',
                'max:255'
            ],

            'trained_at' => [
                'nullable',
                'date'
            ],

            'deployed_at' => [
                'nullable',
                'date'
            ],

            'endpoint_url' => [
                'nullable',
                'url',
                'max:255'
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'testing',
                    'deprecated'
                ])
            ]
        ]);
    }
}