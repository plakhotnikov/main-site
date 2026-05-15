<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Report;

final class ReportsController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/reports/index', [
            'title'   => 'Отчёты',
            'reports' => Report::withRequest(),
        ]);
    }

    public function download(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $report = Report::find($id);
        if ($report === null || empty($report['file_path'])) {
            Helpers::flash('error', 'Файл отчёта не найден');
            Helpers::redirect('admin_reports');
        }
        $reportsDir = (string)$GLOBALS['APP_CONFIG']['app']['reports_dir'];
        $path = $reportsDir . '/' . basename($report['file_path']);
        if (!is_file($path)) {
            Helpers::flash('error', 'Файл недоступен');
            Helpers::redirect('admin_reports');
        }
        $name = basename($path);
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
