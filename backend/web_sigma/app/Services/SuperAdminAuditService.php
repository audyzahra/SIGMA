<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SuperAdminAuditService
{
    public function log(Request $request, string $action, string $module, string $description, ?Model $old = null, ?Model $new = null): void
    {
        $safe = static fn (?Model $model) => $model ? collect($model->getAttributes())->except(['password', 'remember_token', 'credentials_key', 'token', 'api_key', 'secret'])->all() : null;
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'module' => $module, 'description' => $description, 'old_values' => $safe($old), 'new_values' => $safe($new), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
    }
}
