<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
requireTestSession('POST');
$maxBytes = MAX_UPLOAD_MB * 1024 * 1024;
$length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($length > $maxBytes) {
    apiError(413, 'Upload data exceeds the server test limit.');
}
if (!str_starts_with(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/octet-stream')) {
    apiError(415, 'Unsupported test data type.');
}
$stream = fopen('php://input', 'rb');
if (!$stream) {
    apiError(500, 'Unable to read the upload test.');
}
$total = 0;
while (!feof($stream)) {
    $data = fread($stream, 65536);
    if ($data === false) {
        fclose($stream);
        apiError(500, 'Could not complete upload test.');
    }
    if ($data === '') { break; }
    $total += strlen($data);
    if ($total > $maxBytes) {
        fclose($stream);
        apiError(413, 'Upload data exceeds the server test limit.');
    }
}
fclose($stream);
if ($total < 1024 || ($length > 0 && $total !== $length)) {
    apiError(400, 'Incomplete upload test.');
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['ok' => true, 'received_bytes' => $total]);
