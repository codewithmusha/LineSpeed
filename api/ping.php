<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
requireTestSession('GET');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Accel-Buffering: no');
echo json_encode(['ok' => true]);
