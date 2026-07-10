<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/refresh-dashboard.php';

try {
    $config = ga4_load_config();
    $cacheFile = $config['cache_file'] ?? (__DIR__ . '/data/dashboard-cache.json');

    if (!is_file($cacheFile)) {
        if (!empty($config['allow_mock_fallback'])) {
            $payload = ga4_refresh_payload($config);
            ga4_write_cache($payload, $cacheFile);
        } else {
            throw new RuntimeException('Cache file not found: ' . $cacheFile);
        }
    }

    $text = file_get_contents($cacheFile);
    if ($text === false) {
        throw new RuntimeException('Unable to read cache file');
    }
    echo $text;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'statusLabel' => '讀取失敗',
        'statusMessage' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
