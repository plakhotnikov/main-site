<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Request as RequestModel;
use App\Models\RequestService;
use App\Models\RequestStatus;

final class RequestsController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/requests/index', [
            'title'    => 'Заявки',
            'requests' => RequestModel::fullForAdmin(),
        ]);
    }

    public function view(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null) {
            Helpers::flash('error', 'Заявка не найдена');
            Helpers::redirect('admin_requests');
        }
        Template::render('admin/requests/view', [
            'title'         => 'Заявка #' . $id,
            'request'       => $req,
            'extras'        => RequestService::servicesFor($id),
            'consultations' => Consultation::forRequest($id),
            'consultants'   => Consultant::workload(),
            'statuses'      => RequestStatus::ordered(),
            'report'        => Report::findByRequest($id),
            'payments'      => Payment::forRequest($id),
            'total_cost'    => RequestModel::totalCost($id),
        ]);
    }

    public function assign(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['request_id'] ?? 0);
        $consultantId = (int)($_POST['consultant_id'] ?? 0);
        if ($id <= 0 || $consultantId <= 0) {
            Helpers::flash('error', 'Выберите консультанта');
            Helpers::redirect('admin_request_view', ['id' => $id]);
        }
        RequestModel::assignConsultant($id, $consultantId);
        Helpers::flash('success', 'Консультант назначен, заявка переведена в статус «Назначена»');
        Helpers::redirect('admin_request_view', ['id' => $id]);
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        RequestModel::delete($id);
        Helpers::flash('success', 'Заявка удалена (consultations/reports/payments — каскадом)');
        Helpers::redirect('admin_requests');
    }

    public function bulkDelete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $ids = (array)($_POST['ids'] ?? []);
        $count = RequestModel::bulkDeleteByProc($ids);
        Helpers::flash('success', "Удалено заявок: $count (через sp_bulk_delete_requests)");
        Helpers::redirect('admin_requests');
    }
}
