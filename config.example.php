<?php
return [
    // 資料來源模式：mock / file / remote_json
    'mode' => 'mock',

    // 顯示資訊
    'dashboard_title' => 'GA4 + GSC 每日更新儀表板',
    'gsc_site' => 'https://example.com/',
    'range_label' => '近 28 天',

    // 快取輸出檔；GitHub Actions 與伺服器 cron 都會更新這份
    'cache_file' => __DIR__ . '/data/dashboard-cache.json',

    // 每日自動更新時間（Asia/Taipei 08:00）
    'timezone' => 'Asia/Taipei',
    'daily_refresh_hour' => 8,
    'daily_refresh_minute' => 0,

    // ===== mock 模式 =====
    'mock_file' => __DIR__ . '/examples/payload.example.json',

    // ===== file 模式 =====
    'source_file' => '',

    // ===== remote_json 模式 =====
    // 你的後端 endpoint 可直接吐出標準 payload
    'remote_json_url' => '',
    'remote_bearer_token' => '',
    'remote_headers' => [
        // 'X-Api-Key: your-key',
    ],

    // 其他 query 參數（例如站點、property id、日期區間）
    'remote_query' => [
        // 'property_id' => 'properties/123456789',
    ],

    // 若後端需要 POST，改成 true
    'remote_use_post' => false,
    'remote_post_json' => [
    ],

    // 是否允許 api.php 在 cache 缺失時回退 mock
    'allow_mock_fallback' => true,
];
