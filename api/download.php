<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
requireTestSession('GET');
$input = $_GET['mb'] ?? null;
if (!is_string($input) || !ctype_digit($input)) {
    apiError(400, 'Invalid download test size.');
}
$mb = (int) $input;
if ($mb < 1 || $mb > MAX_DOWNLOAD_MB) {
    apiError(400, 'Download test size is outside allowed limits.');
}
$bytes = $mb * 1024 * 1024;
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
while (ob_get_level() > 0) {
    @ob_end_clean();
}
header('Content-Type: application/octet-stream');
header('Content-Length: ' . $bytes);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Accel-Buffering: no');
header('Content-Disposition: attachment; filename="connection-test.bin"');
header('Accept-Ranges: none');
// Random bytes are incompressible: repeated or zero-filled data can give fake speeds.
$remaining = $bytes;
while ($remaining > 0 && connection_status() === CONNECTION_NORMAL) {
    $chunkSize = min(65536, $remaining);
    echo random_bytes($chunkSize);
    $remaining -= $chunkSize;
    flush();
}
