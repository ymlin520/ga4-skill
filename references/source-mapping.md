# Source Mapping Notes

This repo was derived from the WordPress plugin implementation at:

- `/www/beonecom_716/public/wp-content/plugins/hostswp-ga4-gsc-automation/hostswp-ga4-gsc-automation.php`

## Identified page slug

- `hostswp-ga4-gsc-automation-ga4exact`

## Identified page renderer

- `render_ga4exact_bridge_page()`

## Identified payload builder

- `build_ga4exact_standalone_payload()`

## Identified GA4 fetchers

- `fetch_ga4exact_summary()`
- `fetch_ga4exact_daily_timeseries()`
- `fetch_ga4exact_named_breakdown()`
- `fetch_ga4exact_new_vs_returning()`
- `fetch_ga4exact_realtime()`

## Identified GSC fetchers

- `fetch_gsc_summary()`
- `fetch_gsc_daily_timeseries()`
- `fetch_gsc_top_queries()`
- `fetch_gsc_top_pages()`
- `fetch_gsc_daily_position()`

## Notable dashboard sections inferred from source

- hero / overview shell
- realtime summary panel
- realtime active pages list
- overview cards
- GSC cards
- trend charts
- country / referral / top pages rankings
- device legend and device model rankings
- new vs returning section
- GSC top query and top page sections

## Why this repo is generic

The source page lived in WordPress, but the actual reusable asset is the normalized payload + renderer split. Once the payload is stabilized, the same dashboard can be hosted in:

- WordPress admin
- WordPress front-end
- standalone HTML
- custom web apps
