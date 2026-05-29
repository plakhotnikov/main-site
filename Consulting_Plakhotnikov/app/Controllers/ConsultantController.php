<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Request as RequestModel;
use App\Models\RequestService;
use App\Models\RequestStatus;
use App\Services\ReportDocx;

final class ConsultantController
{
    public function dashboard(): void
    {
        Auth::requireRole('consultant');
        $consultantId = Auth::consultantId();
        $requests = RequestModel::forConsultant($consultantId);
        $stats = [
            'total'       => count($requests),
            'active'      => count(array_filter($requests, fn ($r) => in_array($r['status_code'], ['assigned', 'in_progress', 'review'], true))),
            'completed'   => count(array_filter($requests, fn ($r) => $r['status_code'] === 'completed')),
        ];
        Template::render('consultant/dashboard', [
            'title'    => 'Кабинет консультанта',
            'requests' => array_slice($requests, 0, 6),
            'stats'    => $stats,
        ]);
    }

    public function requests(): void
    {
        Auth::requireRole('consultant');
        $consultantId = Auth::consultantId();
        $requests = RequestModel::forConsultant($consultantId);
        Template::render('consultant/requests', [
            'title'    => 'Мои заявки',
            'requests' => $requests,
        ]);
    }

    public function view(): void
    {
        Auth::requireRole('consultant');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Заявка вам не назначена');
            Helpers::redirect('consultant_requests');
        }
        Template::render('consultant/request_view', [
            'title'         => 'Заявка #' . $id,
            'request'       => $req,
            'extras'        => RequestService::servicesFor($id),
            'consultations' => Consultation::forRequest($id),
            'statuses'      => RequestStatus::ordered(),
            'report'        => Report::findByRequest($id),
            'payments'      => Payment::forRequest($id),
            'total_cost'    => RequestModel::totalCost($id),
        ]);
    }

    public function changeStatus(): void
    {
        Auth::requireRole('consultant');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['request_id'] ?? 0);
        $status = (string)($_POST['status_code'] ?? '');
        $comment = trim((string)($_POST['comment'] ?? ''));
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Доступ запрещён');
            Helpers::redirect('consultant_requests');
        }
        $allowed = ['in_progress', 'review', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            Helpers::flash('error', 'Неверный статус');
            Helpers::redirect('consultant_request_view', ['id' => $id]);
        }
        RequestModel::changeStatus($id, $status, $comment);
        Helpers::flash('success', 'Статус заявки обновлён');
        Helpers::redirect('consultant_request_view', ['id' => $id]);
    }

    public function addConsultation(): void
    {
        Auth::requireRole('consultant');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['request_id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Доступ запрещён');
            Helpers::redirect('consultant_requests');
        }
        $heldAt = (string)($_POST['held_at'] ?? '');
        $duration = (int)($_POST['duration_min'] ?? 60);
        $notes = trim((string)($_POST['notes'] ?? ''));
        if ($heldAt === '' || $notes === '') {
            Helpers::flash('error', 'Укажите дату и описание консультации');
            Helpers::redirect('consultant_request_view', ['id' => $id]);
        }
        $heldAt = str_replace('T', ' ', $heldAt);
        if (strlen($heldAt) === 16) {
            $heldAt .= ':00';
        }
        Consultation::create([
            'request_id'   => $id,
            'held_at'      => $heldAt,
            'duration_min' => $duration > 0 ? $duration : 60,
            'notes'        => $notes,
        ]);
        Helpers::flash('success', 'Консультация добавлена');
        Helpers::redirect('consultant_request_view', ['id' => $id]);
    }

    public function createReportForm(): void
    {
        Auth::requireRole('consultant');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Доступ запрещён');
            Helpers::redirect('consultant_requests');
        }
        Template::render('consultant/report_create', [
            'title'   => 'Отчёт по заявке #' . $id,
            'request' => $req,
            'report'  => Report::findByRequest($id),
        ]);
    }

    public function saveReport(): void
    {
        Auth::requireRole('consultant');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['request_id'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '') {
            Helpers::flash('error', 'Заполните содержание отчёта');
            Helpers::redirect('consultant_create_report', ['id' => $id]);
        }
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Доступ запрещён');
            Helpers::redirect('consultant_requests');
        }

        $existing = Report::findByRequest($id);

        $reportsDir = (string)$GLOBALS['APP_CONFIG']['app']['reports_dir'];
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0775, true);
        }
        $fileName = sprintf('report_%d_%s.docx', $id, date('Y-m-d_His'));
        $filePath = $reportsDir . '/' . $fileName;

        ReportDocx::generate($filePath, $req, $content, RequestModel::totalCost($id), Consultation::forRequest($id));

        if ($existing === null) {
            Report::create([
                'request_id'    => $id,
                'consultant_id' => Auth::consultantId(),
                'content'       => $content,
                'file_path'     => $fileName,
            ]);
        } else {
            // Удалим старый файл
            $old = $reportsDir . '/' . basename((string)$existing['file_path']);
            if ($existing['file_path'] && is_file($old)) {
                @unlink($old);
            }
            Report::update((int)$existing['id'], [
                'content'   => $content,
                'file_path' => $fileName,
            ]);
        }

        // После генерации отчёта — заявка идёт на согласование
        if ($req['status_code'] !== 'completed') {
            RequestModel::changeStatus($id, 'review', 'Отчёт подготовлен и направлен клиенту');
        }

        Helpers::flash('success', 'Отчёт создан, файл сохранён в storage/reports/');
        Helpers::redirect('consultant_request_view', ['id' => $id]);
    }

    public function downloadReport(): void
    {
        Auth::requireRole('consultant');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)($req['consultant_id'] ?? 0) !== Auth::consultantId()) {
            Helpers::flash('error', 'Заявка вам не назначена');
            Helpers::redirect('consultant_requests');
        }
        $report = Report::findByRequest($id);
        if ($report === null || empty($report['file_path'])) {
            Helpers::flash('error', 'Отчёт ещё не сформирован');
            Helpers::redirect('consultant_request_view', ['id' => $id]);
        }
        $reportsDir = (string)$GLOBALS['APP_CONFIG']['app']['reports_dir'];
        $filePath = $reportsDir . '/' . basename($report['file_path']);
        if (!is_file($filePath)) {
            Helpers::flash('error', 'Файл отчёта недоступен');
            Helpers::redirect('consultant_request_view', ['id' => $id]);
        }
        $name = basename($filePath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

}
