<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'event' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = AuditLog::query()->with('user')->latest('id');
        $query->when($filters['event'] ?? null, fn ($q, $value) => $q->where('event', $value))
            ->when(filled($filters['user_id'] ?? null), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when($filters['from'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['to'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%");
                if (ctype_digit($search)) {
                    $q->orWhere('auditable_id', (int) $search);
                }
                $q->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%"));
            });
        }

        return view('audit.index', [
            'logs' => $query->paginate(30)->withQueryString(),
            'events' => AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'username']),
            'filters' => $filters,
            'totalLogs' => AuditLog::count(),
            'todayLogs' => AuditLog::whereDate('created_at', today())->count(),
        ]);
    }
}
