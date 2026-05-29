<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Industry;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Request as RequestModel;
use App\Models\RequestService;
use App\Models\RequestStatus;
use App\Models\Service;

final class ClientController
{
    public function dashboard(): void
    {
        Auth::requireRole('client');
        $clientId = Auth::clientId();
        $requests = RequestModel::forClient($clientId);
        $client   = Client::find((int)$clientId);

        $stats = [
            'total'       => count($requests),
            'in_progress' => count(array_filter($requests, fn ($r) => in_array($r['status_code'], ['assigned', 'in_progress', 'review'], true))),
            'completed'   => count(array_filter($requests, fn ($r) => $r['status_code'] === 'completed')),
            'new'         => count(array_filter($requests, fn ($r) => $r['status_code'] === 'new')),
        ];

        Template::render('client/dashboard', [
            'title'    => 'Личный кабинет',
            'requests' => array_slice($requests, 0, 5),
            'stats'    => $stats,
            'client'   => $client,
        ]);
    }

    public function requests(): void
    {
        Auth::requireRole('client');
        $clientId = Auth::clientId();
        $requests = RequestModel::forClient($clientId);
        Template::render('client/requests', [
            'title'    => 'Мои заявки',
            'requests' => $requests,
        ]);
    }

    public function createForm(): void
    {
        Auth::requireRole('client');
        Template::render('client/request_create', [
            'title'      => 'Новая заявка',
            'services'   => Service::active(),
            'industries' => Industry::all([], 'name'),
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('client');
        Csrf::check($_POST['_csrf'] ?? null);

        $clientId    = Auth::clientId();
        $serviceId   = (int)($_POST['service_id'] ?? 0);
        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $priority    = (string)($_POST['priority'] ?? 'normal');
        $deadline    = (string)($_POST['deadline'] ?? '');
        $extraIds    = array_map('intval', (array)($_POST['extra_services'] ?? []));
        $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
        $contactEmail = trim((string)($_POST['contact_email'] ?? ''));
        $contactInn   = trim((string)($_POST['contact_inn'] ?? ''));
        $paymentMethod = (string)($_POST['payment_method'] ?? '');

        $errors = [];
        if ($serviceId <= 0)              { $errors[] = 'Выберите основную услугу'; }
        if ($title === '')                { $errors[] = 'Укажите заголовок'; }
        if ($description === '')          { $errors[] = 'Опишите задачу'; }
        if (!in_array($priority, ['low', 'normal', 'high'], true)) {
            $errors[] = 'Некорректный приоритет';
        }
        if (!in_array($paymentMethod, ['cash', 'card', 'transfer'], true)) {
            $errors[] = 'Выберите способ оплаты';
        }
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email';
        }
        if ($errors) {
            Helpers::flash('error', implode('. ', $errors));
            Helpers::redirect('client_request_create');
        }

        $newStatus = RequestStatus::findByCode('new');
        if ($newStatus === null) {
            Helpers::flash('error', 'Статус "new" не найден в БД');
            Helpers::redirect('client_request_create');
        }

        Database::pdo()->beginTransaction();
        try {
            $contactBlock = '';
            if ($contactPhone !== '' || $contactEmail !== '' || $contactInn !== '') {
                $contactBlock = "\n\n— Контактные данные —"
                    . ($contactPhone !== '' ? "\nТелефон: $contactPhone" : '')
                    . ($contactEmail !== '' ? "\nEmail: $contactEmail" : '')
                    . ($contactInn   !== '' ? "\nИНН: $contactInn"     : '');
            }

            $requestId = RequestModel::create([
                'client_id'   => $clientId,
                'service_id'  => $serviceId,
                'status_id'   => (int)$newStatus['id'],
                'title'       => $title,
                'description' => $description . $contactBlock,
                'priority'    => $priority,
                'deadline'    => $deadline !== '' ? $deadline : null,
            ]);

            // Если клиент дал свежий телефон / email — обновим профиль для удобства
            if ($contactPhone !== '' || $contactEmail !== '') {
                $userId = Auth::id();
                Database::execute(
                    'UPDATE users
                        SET phone = COALESCE(NULLIF(?, ""), phone),
                            email = COALESCE(NULLIF(?, ""), email)
                      WHERE id = ?',
                    [$contactPhone, $contactEmail, $userId]
                );
            }

            foreach ($extraIds as $sid) {
                if ($sid > 0 && $sid !== $serviceId) {
                    RequestService::attach($requestId, $sid);
                }
            }

            $total = RequestModel::totalCost($requestId);
            Payment::create([
                'request_id' => $requestId,
                'amount'     => $total,
                'method'     => $paymentMethod,
            ]);

            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            Helpers::flash('error', 'Не удалось создать заявку: ' . $e->getMessage());
            Helpers::redirect('client_request_create');
        }

        Helpers::flash('success', "Заявка #{$requestId} создана, оплата принята");
        Helpers::redirect('client_request_view', ['id' => $requestId]);
    }

    public function view(): void
    {
        Auth::requireRole('client');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)$req['client_id'] !== Auth::clientId()) {
            Helpers::flash('error', 'Заявка не найдена');
            Helpers::redirect('client_requests');
        }

        $extras = RequestService::servicesFor($id);
        $consultations = Consultation::forRequest($id);
        $report = Report::findByRequest($id);
        $payments = Payment::forRequest($id);
        $totalCost = RequestModel::totalCost($id);

        Template::render('client/request_view', [
            'title'         => 'Заявка #' . $id,
            'request'       => $req,
            'extras'        => $extras,
            'consultations' => $consultations,
            'report'        => $report,
            'payments'      => $payments,
            'total_cost'    => $totalCost,
        ]);
    }

    public function downloadReport(): void
    {
        Auth::requireRole('client');
        $id = (int)($_GET['id'] ?? 0);
        $req = RequestModel::clientView($id);
        if ($req === null || (int)$req['client_id'] !== Auth::clientId()) {
            http_response_code(404);
            echo 'Не найдено';
            return;
        }
        $report = Report::findByRequest($id);
        if ($report === null || empty($report['file_path'])) {
            Helpers::flash('error', 'Отчёт ещё не сформирован');
            Helpers::redirect('client_request_view', ['id' => $id]);
        }
        $reportsDir = (string)$GLOBALS['APP_CONFIG']['app']['reports_dir'];
        $filePath = $reportsDir . '/' . basename($report['file_path']);
        if (!is_file($filePath)) {
            Helpers::flash('error', 'Файл отчёта недоступен');
            Helpers::redirect('client_request_view', ['id' => $id]);
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
