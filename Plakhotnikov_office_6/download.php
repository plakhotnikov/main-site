<?php
$file = $_GET['file'] ?? '';

if ($file === '' || preg_match('/[\/\\\\]/', $file) || str_contains($file, '..')) {
    http_response_code(400);
    echo 'Неверное имя файла.';
    exit;
}

$path = __DIR__ . '/generated/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    echo 'Файл не найден.';
    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime_types = [
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'odt' => 'application/vnd.oasis.opendocument.text',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf' => 'application/pdf',
];

$content_type = $mime_types[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
