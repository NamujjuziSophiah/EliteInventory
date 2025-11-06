<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->with(['user' => function($q){ $q->select('id','name'); }]);

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // paginate and preserve current query string parameters for pagination links
        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->appends($request->query());

        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $users = User::select('id','name')->orderBy('name')->get();

        return view('admin.audit.index', compact('logs','actions','users'));
    }
}
