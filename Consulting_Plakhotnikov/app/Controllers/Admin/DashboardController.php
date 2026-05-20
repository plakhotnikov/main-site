<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Template;
use App\Models\Consultant;
use App\Models\Payment;
use App\Models\Request as RequestModel;

final class DashboardController
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $counts = [
            'users'        => (int)Database::one('SELECT COUNT(*) c FROM users')['c'],
            'clients'      => (int)Database::one('SELECT COUNT(*) c FROM clients')['c'],
            'consultants'  => (int)Database::one('SELECT COUNT(*) c FROM consultants')['c'],
            'requests'     => (int)Database::one('SELECT COUNT(*) c FROM requests')['c'],
            'completed'    => (int)Database::one("SELECT COUNT(*) c FROM requests r JOIN request_statuses s ON s.id=r.status_id WHERE s.code='completed'")['c'],
            'in_progress'  => (int)Database::one("SELECT COUNT(*) c FROM requests r JOIN request_statuses s ON s.id=r.status_id WHERE s.code IN ('assigned','in_progress','review')")['c'],
            'revenue'      => (float)(Database::one('SELECT COALESCE(SUM(amount), 0) s FROM payments')['s'] ?? 0),
        ];

        Template::render('admin/dashboard', [
            'title'        => 'Админ-панель',
            'counts'       => $counts,
            'workload'     => Consultant::workload(),
            'revenue_cat'  => Payment::revenueByCategory(),
            'recent'       => array_slice(RequestModel::fullForAdmin(), 0, 6),
        ]);
    }
}
