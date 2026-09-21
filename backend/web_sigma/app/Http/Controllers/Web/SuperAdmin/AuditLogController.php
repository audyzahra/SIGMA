<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $auditLogs = AuditLog::with('user')->when($request->search, fn ($query, $value) => $query->where(fn ($query) => $query->where('action', 'like', "%{$value}%")->orWhere('module', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%")))->when($request->user_id, fn ($query, $value) => $query->where('user_id', $value))->when($request->action, fn ($query, $value) => $query->where('action', $value))->when($request->module, fn ($query, $value) => $query->where('module', $value))->when($request->date, fn ($query, $value) => $query->whereDate('created_at', $value))->latest()->paginate(20)->withQueryString();

        return view('super_admin.audit_logs.index', ['auditLogs' => $auditLogs, 'users' => User::orderBy('name')->get(), 'actions' => AuditLog::distinct()->orderBy('action')->pluck('action'), 'modules' => AuditLog::distinct()->orderBy('module')->pluck('module')]);
    }

    public function show(AuditLog $auditLog): View
    {
        return view('super_admin.audit_logs.show', ['auditLog' => $auditLog->load('user')]);
    }
}
