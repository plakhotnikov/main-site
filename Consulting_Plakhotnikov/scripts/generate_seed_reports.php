<?php
/**
 * Курсовой проект «Консалтинговая компания»
 * CLI: генерация DOCX-файлов для всех записей в таблице `reports`,
 * у которых на диске нет соответствующего файла.
 *
 * Запуск:
 *   docker compose exec web php /var/www/html/Consulting_Plakhotnikov/scripts/generate_seed_reports.php
 * или локально (с прокинутыми DB_HOST=127.0.0.1):
 *   DB_HOST=127.0.0.1 php Consulting_Plakhotnikov/scripts/generate_seed_reports.php
 */

declare(strict_types=1);

use App\Core\Database;
use App\Models\Consultation;
use App\Models\Report;
use App\Models\Request as RequestModel;
use App\Services\ReportDocx;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Запускать только из CLI\n");
    exit(1);
}

$rootDir = dirname(__DIR__);
require $rootDir . '/../vendor/autoload.php';

spl_autoload_register(static function (string $class) use ($rootDir): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $relative = substr($class, 4);
    $path = $rootDir . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config = require $rootDir . '/config/config.php';
$GLOBALS['APP_CONFIG'] = $config;

$reportsDir = (string)$config['app']['reports_dir'];
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0775, true);
}

$reports = Database::query('SELECT id, request_id, content, file_path FROM reports ORDER BY request_id');
if (!$reports) {
    echo "В таблице reports пусто — нечего генерить.\n";
    exit(0);
}

$created = 0;
$skipped = 0;
$failed = 0;

foreach ($reports as $row) {
    $requestId = (int)$row['request_id'];
    $fileName = (string)$row['file_path'];
    if ($fileName === '') {
        echo "[SKIP] report#{$row['id']}: пустой file_path\n";
        $skipped++;
        continue;
    }

    $filePath = $reportsDir . '/' . basename($fileName);
    if (is_file($filePath)) {
        echo "[SKIP] {$fileName}: уже существует\n";
        $skipped++;
        continue;
    }

    $req = RequestModel::clientView($requestId);
    if ($req === null) {
        echo "[FAIL] {$fileName}: заявка #{$requestId} не найдена\n";
        $failed++;
        continue;
    }

    try {
        ReportDocx::generate(
            $filePath,
            $req,
            (string)$row['content'],
            RequestModel::totalCost($requestId),
            Consultation::forRequest($requestId)
        );
        echo "[OK]   {$fileName}\n";
        $created++;
    } catch (\Throwable $e) {
        echo "[FAIL] {$fileName}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n--- Итого: создано {$created}, пропущено {$skipped}, ошибок {$failed} ---\n";
