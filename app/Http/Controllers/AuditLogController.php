<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $action = (string) $request->query('action', '');

        $logs = AuditLog::query()
            ->with('user')
            ->when($q !== '', fn ($query) => $query->where('summary', 'ilike', "%{$q}%"))
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('logs', 'q', 'action', 'actions'));
    }
}
