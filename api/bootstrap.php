<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config.php';

function apiError(int $code, string $message): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

function requireTestSession(string $method): void
{
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        apiError(405, 'This request method is not allowed.');
    }
    session_name('linespeed_session');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $expected = (string) ($_SESSION['speed_test_token'] ?? '');
    $provided = (string) ($_SERVER['HTTP_X_TEST_TOKEN'] ?? '');
    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        session_write_close();
        apiError(403, 'Please reload the speed test page and try again.');
    }

    // A lightweight per-session limit. For public high-traffic sites, also use
    // hosting/CDN rate limits because new sessions can bypass this guard.
    $now = time();
    $recent = array_values(array_filter(
        $_SESSION['speed_requests'] ?? [],
        static fn($timestamp): bool => is_int($timestamp) && $timestamp > $now - 300
    ));
    if (count($recent) >= 48) {
        session_write_close();
        header('Retry-After: 120');
        apiError(429, 'Too many test requests. Please try again in a few minutes.');
    }
    $recent[] = $now;
    $_SESSION['speed_requests'] = $recent;
    session_write_close();
}
