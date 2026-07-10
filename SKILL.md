---
name: ga4-gsc-universal-dashboard
description: "Use when turning GA4 + GSC metrics into a polished dashboard that works in plain HTML sites, WordPress admin pages, or embedded front-end views."
version: 1.0.0
author: Hermes Agent
license: MIT
metadata:
  hermes:
    tags: [ga4, gsc, dashboard, analytics, html, wordpress, frontend]
    related_skills: [hermes-agent-skill-authoring]
---

# GA4 + GSC Universal Dashboard

## Overview

This skill packages the structure and data model behind the `hostswp-ga4-gsc-automation-ga4exact` dashboard into a reusable implementation pattern.

Use it when you want to build a polished analytics dashboard that:

- pulls data from **Google Analytics 4 Data API**
- pulls SEO/search data from **Google Search Console API**
- renders as a **standalone HTML dashboard**, a **WordPress admin page**, or a **front-end embedded analytics view**
- keeps the **same payload shape** across platforms so the front-end can be reused

The original WordPress implementation combines:
- GA4 overview metrics
- daily trend charts
- country / referral / channel / device breakdowns
- realtime active users + active pages
- new vs returning users
- top pages
- GSC clicks / impressions / CTR / position
- GSC top queries + top pages

The goal of this skill is to preserve that architecture while removing WordPress lock-in.

## When to Use

Use this skill when the user asks to:

- rebuild a GA4/GSC dashboard outside WordPress
- clone a working WordPress analytics screen into a pure HTML/JS page
- create an embeddable front-end dashboard fed by API data
- standardize one analytics payload so multiple sites can share the same UI
- produce a “data command center” page with cards, charts, rankings, and realtime panels

Do **not** use this skill when:

- the user only wants a raw CSV export
- the user only wants GA4 ecommerce event tracking, not a dashboard UI
- the user needs Looker Studio instead of a custom dashboard

## Source Dashboard Mapping

The source implementation came from a WordPress plugin page with this menu slug:

- `hostswp-ga4-gsc-automation-ga4exact`

Related implementation functions in the source plugin include:

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
- `fetch_gsc_daily_position()`

That means the reusable pattern is:

1. fetch and normalize GA4/GSC data server-side
2. merge it into a single dashboard payload
3. render the payload with a reusable front-end shell

## Data Sources

### GA4 Data API

Core metrics and dimensions used by the source implementation:

| Purpose | Dimensions | Metrics |
|---|---|---|
| Overview | none | `sessions`, `totalUsers`, `screenPageViews` |
| Daily trend | `date` | `sessions`, `totalUsers`, `screenPageViews` |
| Country breakdown | `country` | `totalUsers` or `activeUsers` |
| Top pages | `pageTitle` | `screenPageViews` |
| Referral breakdown | `sessionSource` + medium filter | `sessions` |
| Channel breakdown | `sessionDefaultChannelGroup` | `sessions` |
| Device breakdown | `deviceCategory` | `sessions` |
| Device model | `mobileDeviceModel` | `sessions` |
| Screen resolution | `screenResolution` | `sessions` |
| New vs returning | `newVsReturning` | `totalUsers` |
| Realtime | page / screen dimensions | active users style metrics |

### Search Console API

Core outputs used by the source implementation:

| Purpose | Dimensions | Metrics |
|---|---|---|
| GSC summary | none | `clicks`, `impressions`, `ctr` |
| Daily search trend | `date` | `clicks`, `impressions`, `position` |
| Top queries | `query` | `clicks` |
| Top pages | `page` | `clicks` |
| Position trend | `date` | `position` |

## Canonical Payload Shape

Build one payload for all renderers. The source implementation effectively exposes these top-level sections:

```json
{
  "status": "ok",
  "statusLabel": "資料已同步",
  "statusMessage": "GA4 與 GSC 指標已完成更新",
  "rangeLabel": "近 28 天",
  "gscSite": "https://example.com/",
  "overviewCards": [],
  "gscCards": [],
  "countries": [],
  "referrals": [],
  "topPages": [],
  "deviceLegend": [],
  "deviceModels": [],
  "screenResolutions": [],
  "newVsReturning": {},
  "charts": {},
  "realtime": {},
  "gscQueries": [],
  "gscPages": []
}
```

Recommended normalized structure:

```json
{
  "status": "ok",
  "statusLabel": "資料已同步",
  "statusMessage": "GA4 與 GSC 指標已完成更新",
  "rangeLabel": "近 28 天",
  "gscSite": "https://example.com/",
  "overviewCards": [
    {"label": "工作階段", "value": "12,480", "sub": "GA4 sessions", "change": "+12.4%", "direction": "up", "tone": "good"},
    {"label": "使用者", "value": "9,302", "sub": "GA4 totalUsers"},
    {"label": "瀏覽量", "value": "24,911", "sub": "GA4 screenPageViews"}
  ],
  "gscCards": [
    {"label": "搜尋點擊", "value": "3,812", "sub": "GSC clicks"},
    {"label": "搜尋曝光", "value": "128,440", "sub": "GSC impressions"},
    {"label": "CTR", "value": "2.97%", "sub": "GSC ctr"}
  ],
  "countries": [{"name": "Taiwan", "value": "5,120", "icon": "🌏"}],
  "referrals": [{"name": "google", "value": "2,422", "icon": "↗"}],
  "topPages": [{"title": "/blog/ai-course", "views": "1,842"}],
  "deviceLegend": [{"label": "mobile", "percent": "72%", "color": "#63b9d1"}],
  "deviceModels": [{"name": "iPhone", "value": "2,204"}],
  "screenResolutions": [{"name": "390x844", "value": "1,921"}],
  "newVsReturning": {"new": 4200, "returning": 1100, "new_pct": 79, "returning_pct": 21},
  "charts": {
    "labels": ["06/01", "06/02"],
    "sessions": [220, 245],
    "views": [460, 502],
    "gscClicks": [38, 41],
    "gscImpressions": [1420, 1510],
    "gscPosition": [14.2, 13.9],
    "channelLabels": ["Organic Search", "Direct"],
    "channelValues": [1820, 1330],
    "deviceLabels": ["mobile", "desktop"],
    "deviceValues": [72, 28],
    "deviceColors": ["#63b9d1", "#5b24ff"]
  },
  "realtime": {
    "active_30m": 18,
    "active_5m": 6,
    "recentActiveUsers": 18,
    "top_pages": [
      {"title": "/", "activeUsers": "6"},
      {"title": "/course", "activeUsers": "4"}
    ]
  },
  "gscQueries": [{"label": "ai 課程", "value": "182"}],
  "gscPages": [{"label": "/blog/ai-course", "value": "140"}]
}
```

## Implementation Pattern

### Step 1 — Build a server-side data collector

Create one endpoint or job that fetches:

1. GA4 summary
2. GA4 daily trend
3. GA4 country / source / channel / device / top page breakdowns
4. GA4 realtime users + active pages
5. GA4 new vs returning
6. GSC summary
7. GSC daily trend
8. GSC top queries
9. GSC top pages
10. GSC average position trend

Completion criteria:
- [ ] every section returns either normalized data or an explicit fallback object
- [ ] front-end never needs to know raw Google API response shapes

### Step 2 — Normalize all responses into one payload

Rules:

- convert numbers to integers/floats server-side
- pre-format display values where the UI expects labels like `12,480` or `2.97%`
- replace empty values with `(not set)` or `0`
- keep chart arrays aligned by index
- keep top-list rows pre-shaped for rendering

Completion criteria:
- [ ] front-end consumes one JSON payload
- [ ] no chart requires special-case parsing of Google API rows

### Step 3 — Separate UI shell from platform

Make the front-end renderer reusable by keeping it platform-agnostic:

- **HTML site** → fetch `/api/dashboard`
- **WordPress admin** → echo payload into the page or localize script data
- **WordPress front-end** → embed widget container + fetch API
- **static demo** → load `payload.example.json`

Completion criteria:
- [ ] the same chart/card renderer works in at least two environments

### Step 4 — Recreate the dashboard layout

Recommended section order based on the source dashboard:

1. Hero / header with title, sync status, date range
2. Overview cards
3. GSC cards
4. Realtime summary + active pages panel
5. Main trend chart
6. Channel / device donut or bar charts
7. Country / referral / top pages rankings
8. New vs returning block
9. GSC top queries + top pages rankings
10. Settings / data-source metadata area

Completion criteria:
- [ ] desktop layout is dashboard-like, not a plain table dump
- [ ] mobile stacks cleanly with readable cards and ranks

### Step 5 — Add graceful fallbacks

If one source fails:

- keep the page rendering
- display a status banner
- show missing sections as empty cards / no-data states
- do not break the entire dashboard because one API call failed

Completion criteria:
- [ ] GA4 failure still allows GSC section render
- [ ] GSC failure still allows GA4 section render

## Platform Recipes

### A. Plain HTML / JS site

- backend: Node / PHP / Python endpoint that returns normalized JSON
- frontend: fetch JSON and render cards/charts
- ideal when the user wants a public or protected analytics page outside CMS

### B. WordPress admin page

- add admin menu page
- fetch data server-side in PHP
- print payload into the page using `wp_json_encode()`
- render with vanilla JS or Chart.js

### C. Embedded front-end analytics section

- protect endpoint with auth if needed
- lazy-load charts after first paint
- keep card summaries visible even if charts are delayed

## Visual Style Guidance

The source page uses a polished “command center” look:

- rounded cards (`16px–20px` radius)
- light blue / white dashboard palette
- gradient headline accents
- strong summary cards
- soft borders and subtle shadows
- pill badges for status / range / source
- list-based rank panels instead of dense data tables

If recreating the UI elsewhere, preserve:

- high scanability
- top-level KPI hierarchy
- rank lists for pages / sources / countries
- realtime card prominence

## Common Pitfalls

1. **Passing raw Google API rows to the front-end**
   Normalize first. Raw row parsing creates renderer sprawl.

2. **Mixing UI formatting with API fetching**
   Fetchers should return normalized data; rendering layer handles layout.

3. **No fallback for missing GSC permissions**
   GSC often fails because the connected account does not have site access. Return a usable empty state.

4. **Realtime panel breaking the page**
   Realtime endpoints are noisy and can fail more often. Keep a fallback object like:
   `{ "active_30m": 0, "active_5m": 0, "top_pages": [] }`

5. **Different payload shapes per platform**
   If HTML, WordPress, and embedded versions all have different JSON shapes, maintenance becomes painful. Keep one canonical schema.

6. **Overusing tables**
   This dashboard works best with cards, ranked lists, and charts — not spreadsheet-style admin grids.

## Verification Checklist

- [ ] GA4 summary metrics render correctly
- [ ] GA4 daily trend chart labels align with values
- [ ] top countries / referrals / pages render as rank lists
- [ ] device breakdown legend percentages add up logically
- [ ] new vs returning percentages are correct
- [ ] realtime panel renders even when zero
- [ ] GSC cards show clicks / impressions / CTR
- [ ] GSC top queries and top pages render independently of GA4
- [ ] page still renders when one API fails
- [ ] the same payload shape works in HTML and WordPress versions

## One-Shot Recipes

### Build a static prototype from mock data

1. create `payload.example.json`
2. build `index.html` that fetches or embeds the payload
3. render cards, charts, rank lists, and realtime sections
4. verify mobile stacking

### Convert a WordPress-only dashboard into a reusable front-end component

1. extract server-side data fetchers
2. create a normalization layer that returns one JSON schema
3. rebuild UI as framework-agnostic HTML/CSS/JS
4. use the same component inside WordPress and plain HTML

### Add GA4/GSC dashboard to a generic site

1. connect Google OAuth or service account where applicable
2. store property ID and site URL
3. fetch GA4 + GSC data on schedule or request
4. cache payload for 5–30 minutes
5. render via front-end widget or full-page dashboard
