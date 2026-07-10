# GA4 + GSC Universal Dashboard Skill

A reusable Hermes skill and demo project based on the `hostswp-ga4-gsc-automation-ga4exact` dashboard architecture.

This repo turns that WordPress-specific analytics screen into a **platform-agnostic pattern** you can reuse in:

- plain HTML websites
- WordPress admin pages
- WordPress front-end embeds
- custom PHP / Node / Python dashboards

## What this repo includes

- `SKILL.md` — reusable Hermes skill for building the dashboard
- `demo/index.html` — front-end dashboard, now reading the daily refreshed cache
- `examples/payload.example.json` — example payload schema
- `api.php` — JSON endpoint that serves the latest cached payload
- `refresh-dashboard.php` — refresh job that rebuilds `data/dashboard-cache.json`
- `config.example.php` — config template for mock/file/remote JSON modes
- `.github/workflows/daily-refresh.yml` — GitHub Actions scheduler at 08:00 Asia/Taipei daily
- `references/source-mapping.md` — mapping notes from the original WordPress implementation

## Core dashboard sections

Derived from the source plugin implementation:

- GA4 overview cards
- GSC overview cards
- realtime active users
- realtime top active pages
- daily trend chart
- channel breakdown
- device breakdown
- top countries
- referral source ranking
- top pages ranking
- new vs returning split
- GSC top queries
- GSC top pages
- sync / range / status metadata

## Source implementation reference

Original source plugin location used for mapping:

- `/www/beonecom_716/public/wp-content/plugins/hostswp-ga4-gsc-automation/hostswp-ga4-gsc-automation.php`

Key source functions identified:

- `render_ga4exact_bridge_page()`
- `build_ga4exact_standalone_payload()`
- `fetch_ga4exact_summary()`
- `fetch_ga4exact_daily_timeseries()`
- `fetch_ga4exact_named_breakdown()`
- `fetch_ga4exact_new_vs_returning()`
- `fetch_ga4exact_realtime()`
- `fetch_gsc_summary()`
- `fetch_gsc_daily_timeseries()`
- `fetch_gsc_top_queries()`
- `fetch_gsc_top_pages()`

## How to preview the front-end demo

Open:

- `demo/index.html`

The demo reads from:

- `../data/dashboard-cache.json`

If serving locally, any static file server works. Example:

```bash
cd ga4-gsc-universal-dashboard-skill
python3 -m http.server 8787
```

Then visit:

- `http://localhost:8787/demo/`

## Intended reuse pattern

### For plain HTML sites

- build a backend endpoint that returns the normalized payload
- reuse the same UI shell from `demo/index.html`
- secure the endpoint if the dashboard is private

### For WordPress

- keep Google API calls in PHP
- normalize responses into one payload
- localize or print the payload into the admin/front-end page
- reuse the same chart/card renderer

### For other stacks

Any backend can work as long as it returns the canonical payload shape described in `SKILL.md`.

## Suggested next extensions

- split the demo CSS / JS into separate files
- replace mock data with live GA4/GSC endpoint calls
- add Chart.js or ECharts
- add authentication for private dashboards
- add payload caching (5–30 min)

## 每日早上 8 點自動更新

這個 repo 現在已內建兩種更新方式：

### 1. GitHub Actions 每日排程

已加入：

- `.github/workflows/daily-refresh.yml`

排程時間：

- `0 0 * * *`（UTC）
- 等於 **Asia/Taipei 每天早上 8:00**

#### 若要接真實資料
請在 GitHub repo 設定：

- **Variables**
  - `GA4_DASHBOARD_MODE=remote_json`
  - `GA4_DASHBOARD_TITLE`
  - `GA4_DASHBOARD_GSC_SITE`
  - `GA4_DASHBOARD_RANGE_LABEL`
  - `GA4_DASHBOARD_REMOTE_JSON_URL`
  - `GA4_DASHBOARD_REMOTE_HEADERS_JSON`（可選）
  - `GA4_DASHBOARD_REMOTE_QUERY_JSON`（可選）
  - `GA4_DASHBOARD_REMOTE_USE_POST`（可選）
  - `GA4_DASHBOARD_REMOTE_POST_JSON`（可選）
- **Secret**
  - `GA4_DASHBOARD_REMOTE_BEARER_TOKEN`

workflow 會每天自動執行：

```bash
php refresh-dashboard.php
```

並更新：

- `data/dashboard-cache.json`

### 2. 伺服器 cron

如果你部署在一般 PHP 主機，也可以直接設 server cron：

```bash
0 8 * * * /usr/bin/php /path/to/ga4-gsc-universal-dashboard-skill/refresh-dashboard.php
```

這會在每天早上 8 點更新 cache 檔。

### 前端讀取位置

- `demo/index.html` → 讀 `../data/dashboard-cache.json`
- `api.php` → 回傳最新 cache JSON

## License

MIT
