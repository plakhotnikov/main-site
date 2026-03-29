<?php
$file = $_GET['file'] ?? '';

if ($file === '' || preg_match('/[\/\\\\]/', $file) || str_contains($file, '..')) {
    http_response_code(400);
    echo 'Неверное имя файла.';
    exit;
}

$path = __DIR__ . '/../generated/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    echo 'Файл не найден.';
    exit;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
