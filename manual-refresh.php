<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/refresh-dashboard.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $config = ga4_load_config();
    $payload = ga4_refresh_payload($config);
    $cacheFile = $config['cache_file'] ?? (__DIR__ . '/data/dashboard-cache.json');
    ga4_write_cache($payload, $cacheFile);

    echo json_encode([
        'status' => 'ok',
        'message' => 'GA4 dashboard cache refreshed',
        'cacheFile' => $cacheFile,
        'refreshedAtLabel' => $payload['refreshMeta']['refreshedAtLabel'] ?? null,
        'sourceMode' => $payload['refreshMeta']['sourceMode'] ?? ($config['mode'] ?? null),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
