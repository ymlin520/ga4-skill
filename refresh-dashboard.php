<?php
declare(strict_types=1);

function ga4_env_array(string $key): array {
    $raw = getenv($key);
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function ga4_load_config(): array {
    $base = file_exists(__DIR__ . '/config.php')
        ? require __DIR__ . '/config.php'
        : require __DIR__ . '/config.example.php';

    $envMap = [
        'mode' => getenv('GA4_DASHBOARD_MODE') ?: null,
        'dashboard_title' => getenv('GA4_DASHBOARD_TITLE') ?: null,
        'gsc_site' => getenv('GA4_DASHBOARD_GSC_SITE') ?: null,
        'range_label' => getenv('GA4_DASHBOARD_RANGE_LABEL') ?: null,
        'cache_file' => getenv('GA4_DASHBOARD_CACHE_FILE') ?: null,
        'timezone' => getenv('GA4_DASHBOARD_TIMEZONE') ?: null,
        'remote_json_url' => getenv('GA4_DASHBOARD_REMOTE_JSON_URL') ?: null,
        'remote_bearer_token' => getenv('GA4_DASHBOARD_REMOTE_BEARER_TOKEN') ?: null,
        'source_file' => getenv('GA4_DASHBOARD_SOURCE_FILE') ?: null,
        'mock_file' => getenv('GA4_DASHBOARD_MOCK_FILE') ?: null,
    ];

    foreach ($envMap as $k => $v) {
        if ($v !== null && $v !== '') {
            $base[$k] = $v;
        }
    }

    $remoteHeaders = ga4_env_array('GA4_DASHBOARD_REMOTE_HEADERS_JSON');
    if ($remoteHeaders) {
        $base['remote_headers'] = $remoteHeaders;
    }
    $remoteQuery = ga4_env_array('GA4_DASHBOARD_REMOTE_QUERY_JSON');
    if ($remoteQuery) {
        $base['remote_query'] = $remoteQuery;
    }
    $remotePostJson = ga4_env_array('GA4_DASHBOARD_REMOTE_POST_JSON');
    if ($remotePostJson) {
        $base['remote_post_json'] = $remotePostJson;
    }
    if (($x = getenv('GA4_DASHBOARD_REMOTE_USE_POST')) !== false && $x !== '') {
        $base['remote_use_post'] = in_array(strtolower($x), ['1', 'true', 'yes'], true);
    }

    return $base;
}

function ga4_read_json_file(string $path): array {
    if (!is_file($path)) {
        throw new RuntimeException("JSON file not found: {$path}");
    }
    $text = file_get_contents($path);
    if ($text === false) {
        throw new RuntimeException("Unable to read file: {$path}");
    }
    $data = json_decode($text, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON file: {$path}");
    }
    return $data;
}

function ga4_fetch_remote_json(array $config): array {
    $url = $config['remote_json_url'] ?? '';
    if (!$url) {
        throw new RuntimeException('remote_json_url is empty');
    }

    $headers = ['Accept: application/json'];
    foreach (($config['remote_headers'] ?? []) as $h) {
        if (is_string($h) && trim($h) !== '') {
            $headers[] = $h;
        }
    }
    if (!empty($config['remote_bearer_token'])) {
        $headers[] = 'Authorization: Bearer ' . $config['remote_bearer_token'];
    }

    $query = $config['remote_query'] ?? [];
    if (!empty($query)) {
        $sep = str_contains($url, '?') ? '&' : '?';
        $url .= $sep . http_build_query($query);
    }

    $usePost = !empty($config['remote_use_post']);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERAGENT => 'Hermes-GA4-Dashboard/1.0',
    ]);

    if ($usePost) {
        $body = json_encode($config['remote_post_json'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false || $err) {
        throw new RuntimeException('Remote fetch failed: ' . $err);
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Remote fetch HTTP ' . $status . ': ' . substr($response, 0, 500));
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('Remote response is not valid JSON');
    }
    return $data;
}

function ga4_normalize_payload(array $payload, array $config): array {
    $tz = new DateTimeZone($config['timezone'] ?? 'Asia/Taipei');
    $now = new DateTimeImmutable('now', $tz);
    $payload['dashboardTitle'] = $config['dashboard_title'] ?? ($payload['dashboardTitle'] ?? 'GA4 + GSC 每日更新儀表板');
    $payload['gscSite'] = $config['gsc_site'] ?? ($payload['gscSite'] ?? '');
    $payload['rangeLabel'] = $config['range_label'] ?? ($payload['rangeLabel'] ?? '近 28 天');
    $payload['status'] = $payload['status'] ?? 'ok';
    $payload['statusLabel'] = $payload['statusLabel'] ?? '資料已同步';
    $payload['statusMessage'] = $payload['statusMessage'] ?? 'GA4 與 GSC 指標已完成更新';
    $payload['refreshMeta'] = [
        'refreshedAtIso' => $now->format(DateTimeInterface::ATOM),
        'refreshedAtLabel' => $now->format('Y-m-d H:i:s'),
        'timezone' => $tz->getName(),
        'sourceMode' => $config['mode'] ?? 'mock',
        'dailySchedule' => sprintf('%02d:%02d', (int)($config['daily_refresh_hour'] ?? 8), (int)($config['daily_refresh_minute'] ?? 0)),
    ];
    return $payload;
}

function ga4_refresh_payload(array $config): array {
    $mode = $config['mode'] ?? 'mock';
    return match ($mode) {
        'mock' => ga4_normalize_payload(ga4_read_json_file($config['mock_file']), $config),
        'file' => ga4_normalize_payload(ga4_read_json_file($config['source_file']), $config),
        'remote_json' => ga4_normalize_payload(ga4_fetch_remote_json($config), $config),
        default => throw new RuntimeException('Unsupported mode: ' . $mode),
    };
}

function ga4_write_cache(array $payload, string $cacheFile): void {
    $dir = dirname($cacheFile);
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException('Failed to create cache dir: ' . $dir);
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Failed to encode payload JSON');
    }
    if (file_put_contents($cacheFile, $json . PHP_EOL) === false) {
        throw new RuntimeException('Failed to write cache file: ' . $cacheFile);
    }
}

if (PHP_SAPI === 'cli') {
    try {
        $config = ga4_load_config();
        $payload = ga4_refresh_payload($config);
        $cacheFile = $config['cache_file'] ?? (__DIR__ . '/data/dashboard-cache.json');
        ga4_write_cache($payload, $cacheFile);
        echo "UPDATED {$cacheFile}\n";
        echo 'MODE ' . ($config['mode'] ?? 'mock') . "\n";
        echo 'REFRESHED ' . ($payload['refreshMeta']['refreshedAtLabel'] ?? '') . "\n";
    } catch (Throwable $e) {
        fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
