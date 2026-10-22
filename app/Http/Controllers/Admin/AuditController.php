<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminAuditLog::query()
            ->with(['admin:id,name,email', 'auditable'])
            ->latest();

        // Фильтры
        $query->when($request->admin_id, fn($q, $id) => $q->where('admin_id', $id));
        $query->when($request->action, fn($q, $action) => $q->where('action', $action));
        $query->when($request->search, fn($q, $s) => $q->where(function($w) use ($s) {
            $w->where('auditable_type', 'like', "%$s%")
                ->orWhere('comment', 'like', "%$s%")
                ->orWhereHas('admin', fn($u) => $u->where('name', 'like', "%$s%"));
        }));

        $logs = $query->paginate(30)->withQueryString();

        // Список админов для фильтра
        $admins = User::role('admin')->get(['id', 'name']);

        // Словарь действий для человеко-читаемого вида
        $actions = [
            'withdrawal_approve'   => 'Подтверждение вывода',
            'withdrawal_reject'    => 'Отклонение вывода',
            'balance_manual'       => 'Ручное изменение баланса',
            'review_toggle_public' => 'Скрытие/показ отзыва',
            'review_verify'        => 'Проверка отзыва',
            'review_delete'        => 'Удаление отзыва',
            'review_note'          => 'Заметка к отзыву',
        ];

        return Inertia::render('Admin/Audit/Index', [
            'logs'    => $logs,
            'admins'  => $admins,
            'actions' => $actions,
            'filters' => $request->only(['admin_id', 'action', 'search']),
        ]);
    }
}
