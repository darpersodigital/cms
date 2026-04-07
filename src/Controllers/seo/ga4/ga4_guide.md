# How to Add Google Analytics (GA4) to a Laravel Dashboard via Google API — Step-by-Step Guide



## 1. Required Packages


```bash
composer require spatie/laravel-analytics
composer require consoletvs/charts
```

> Note: ConsoleTVs Charts may require certain Chart.js versions. If you see `Call to undefined method ConsoleTVs\Charts\Classes\Chartjs\Dataset::script()` or `container()`, verify the Charts package version and the tutorial examples you're using — the API changed across releases. If needed, match the package version to the example or use Chartisan / Chart.js directly.

Publish Spatie config:

```bash
php artisan vendor:publish --tag="analytics-config"
```

The following config file will be published in `config/analytics.php`:

```php
<?php

return [
    'property_id' => env('ANALYTICS_PROPERTY_ID'),

    'service_account_credentials_json' => storage_path('app/private/analytics/service-account-credentials.json'),

    'cache_lifetime_in_minutes' => 60 * 24,

    'cache' => [
        'store' => 'file',
    ],
];
```

## 2. How to obtain the credentials to communicate with Google Analytics

### Getting credentials

The first thing you’ll need to do is get credentials to use Google APIs. Head over to Google APIs / Google Cloud Console and select or create a project.

![Create or select a project](ga4_laravel_dashboard_assets/step-1-create-project.webp)

Next, specify which APIs the project may consume. Go to the API Library and search for **Google Analytics Data API**.

![Open API Library](ga4_laravel_dashboard_assets/step-2-open-library.webp)

![Search for analytics data](ga4_laravel_dashboard_assets/step-3-search-api.webp)

Choose **Enable** to enable the API.

![Enable Google Analytics Data API](ga4_laravel_dashboard_assets/step-4-enable-api.webp)

Now that you’ve created a project with access to the Analytics API, download a file with the credentials. Click **Credentials** in the sidebar and create a **Service account key**.

![Create service account](ga4_laravel_dashboard_assets/step-5-service-account.webp)

On the next screen you can give the service account a name. In the service account ID you’ll see an email address. You’ll use this email address later in the guide.

![Service account details](ga4_laravel_dashboard_assets/step-6-service-account-details.webp)

Go to the details screen of your created service account and select **Keys**. From **Add key**, select **Create new key**.

![Create new key](ga4_laravel_dashboard_assets/step-7-create-key.webp)

Select **JSON** as the key type and click **Create** to download the JSON file.

![Select JSON key type](ga4_laravel_dashboard_assets/step-8-json-key.webp)

**Important:** Download the JSON file and **do not commit it to git**.

Place the file inside Laravel storage, for example:

```text
storage/app/private/analytics/service-account-credentials.json
```

Also add this to `.gitignore`:

```gitignore
/storage/app/private/analytics/*.json
```

Save the JSON inside your Laravel project at the location specified in the `service_account_credentials_json` key of the config file. Because the JSON file contains potentially sensitive information, do not commit it to your repository.

## 3. Grant permissions to your Analytics property

This guide assumes you already created a Google Analytics account and are using the new GA4 properties.

First, find your property ID. In Analytics, go to **Settings > Property Settings**. Copy the property ID and use it for the `ANALYTICS_PROPERTY_ID` key in your `.env` file.

![Find property ID](ga4_laravel_dashboard_assets/step-9-property-id.webp)

Now give access to the service account you created. Go to **Property Access Management** in the Admin section of the property. Click the plus sign in the top right corner to add a new user.

On that screen, grant access to the email address found in the `client_email` key from the JSON file you downloaded in the previous step. **Analyst** role is enough.

![Grant property access](ga4_laravel_dashboard_assets/step-10-property-access.webp)

## 4. Update `.env` and `config/analytics.php`

Add to `.env`:

```dotenv
ANALYTICS_PROPERTY_ID=123456789
ANALYTICS_SERVICE_ACCOUNT_CREDENTIALS_JSON=storage/app/private/analytics/service-account-credentials.json
```

## 6. Create `DashboardController` (ready-to-use)

**Path:** `App\Http\Controllers\DashboardController`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Collection;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class DashboardController extends Controller
{
    // Available date ranges for the filter dropdown
    protected $dateRanges = ['today', 'weekly', 'monthly', 'custom'];

    public function index(Request $request)
    {
        // 1. Get and process the selected date range
        $range = $request->input('date_range', 'weekly');
        list($period, $startDate, $endDate) = $this->getAnalyticsPeriod($request, $range);

        // 2. Fetch all required data from Google Analytics API
        $data = $this->fetchAnalyticsData($period);

        // 3. Prepare Charts
        $charts = $this->prepareCharts($data);

        // 4. Return to the view
        return view('backend.dashboard.index', array_merge($data, [
            'charts'       => $charts,
            'currentRange' => $range,
            'dateRanges'   => $this->dateRanges,
            'startDate'    => $startDate->toDateString(),
            'endDate'      => $endDate->toDateString(),
        ]));
    }

    // --- Helper for Date Filtering ---
    private function getAnalyticsPeriod(Request $request, $range): array
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today();

        switch ($range) {
            case 'weekly':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                break;
            case 'monthly':
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date', $startDate));
                $endDate = Carbon::parse($request->input('end_date', $endDate));
                break;
        }

        $period = Period::create($startDate, $endDate);

        return [$period, $startDate, $endDate];
    }

    // --- Data Fetching Method ---
    private function fetchAnalyticsData(Period $period): array
    {
        $totalVisitorsAndPageViews = Analytics::fetchTotalVisitorsAndPageViews($period);
        $visitsTrend = $this->getVisitsTrend($period);
        $usersTrend = $this->getUsersTrend($period);

        $data = [
            // Info Boxes
            'total_visits' => $totalVisitorsAndPageViews->sum('screenPageViews') ?? 0,
            'total_users'  => $usersTrend->sum('totalUsers') ?? 0,
            'new_users' => $usersTrend->sum('newUsers') ?? 0,
            'bounce_rate' => $this->calculateBounceRate($period),
            'avg_session_duration' => $this->getAvgSessionDuration($period),

            // Charts Data
            'visits_by_day'  => $visitsTrend,
            'top_pages'      => Analytics::fetchMostVisitedPages($period)->take(10),
            'user_countries' => $this->getUserData($period, ['country'])->take(6),
            'top_referrers'  => $this->getTopReferrers($period)->take(5),
            'top_browsers'   => $this->getUserData($period, ['browser'])->take(5),
            'top_devices'    => $this->getUserData($period, ['deviceCategory'])->take(5),
            'visits_and_users_trend' => $this->getVisitsAndUsersTrend($period),
        ];

        return $data;
    }

    // --- Individual Data Fetching Helpers ---
    private function getVisitsAndUsersTrend(Period $period): Collection
    {
        return Analytics::get($period, ['totalUsers', 'screenPageViews'], ['date'])
            ->map(function ($item) {
                return [
                    'date' => $item['date']->format('Y-m-d'),
                    'totalUsers' => $item['totalUsers'],
                    'screenPageViews' => $item['screenPageViews'],
                ];
            });
    }

    private function getVisitsTrend(Period $period): Collection
    {
        return Analytics::get($period, ['screenPageViews'], ['date'])
            ->mapWithKeys(function ($item) {
                return [$item['date']->format('Y-m-d') => $item['screenPageViews']];
            });
    }

    private function getUserData(Period $period, array $dimensions): Collection
    {
        return Analytics::get($period, ['screenPageViews'], $dimensions)
            ->mapWithKeys(function ($item) use ($dimensions) {
                return [$item[$dimensions[0]] => $item['screenPageViews']];
            })
            ->sortByDesc(fn ($views) => $views);
    }

    private function getTopReferrers(Period $period): Collection
    {
        return Analytics::fetchTopReferrers($period);
    }

    private function calculateBounceRate(Period $period): float
    {
        $data = Analytics::get($period, ['bounceRate']);
        return isset($data[0]['bounceRate']) ? round((float) $data[0]['bounceRate'] * 100, 2) : 0.0;
    }

    private function getAvgSessionDuration(Period $period): string
    {
        $data = Analytics::get($period, ['averageSessionDuration']);
        $avgSeconds = isset($data[0]['averageSessionDuration']) ? (float) $data[0]['averageSessionDuration'] : 0;
        return gmdate("H:i:s", (int) $avgSeconds);
    }

    private function getUsersTrend(Period $period): Collection
    {
        return Analytics::get($period, ['totalUsers', 'newUsers'], ['date'])
            ->map(function ($item) {
                return [
                    'date' => $item['date']->format('Y-m-d'),
                    'totalUsers' => $item['totalUsers'],
                    'newUsers' => $item['newUsers'],
                ];
            });
    }

    // --- Chart Preparation Method ---
    private function prepareCharts(array $data): array
    {
        $charts = [];
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#1abc9c'];

        // Line Chart: Visits Trend
        $lineChart = new Chart;
        $lineChart->title('Visits Trend Over Time');
        $lineChart->labels($data['visits_by_day']->keys()->all());
        $lineChart->options(['responsive' => true, 'maintainAspectRatio' => false]);
        $lineChart->dataset('Page Views', 'line', $data['visits_by_day']->values()->all())
            ->backgroundColor('rgba(52, 152, 219, 0.2)')
            ->color('#3498db');
        $charts['line'] = $lineChart;

        // Bar Chart: Top 10 Most Visited Pages
        $barChart = new Chart;
        $barChart->title('Top 10 Most Visited Pages');
        $barChart->labels($data['top_pages']->pluck('pageTitle')->all());
        $barChart->options([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'xAxes' => [[
                    'ticks' => [
                        'display' => false
                    ]
                ]]
            ]
        ]);
        $barChart->dataset('Page Views', 'bar', $data['top_pages']->pluck('screenPageViews')->all())
            ->backgroundColor($colors);
        $charts['bar'] = $barChart;

        // Bar Chart: Visits by Country
        $countryBarChart = new Chart;
        $countryBarChart->title('Visits by Country');
        $countryBarChart->labels($data['user_countries']->keys()->all());
        $countryBarChart->options([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'xAxes' => [['ticks' => ['beginAtZero' => true]]],
                'yAxes' => [['ticks' => ['beginAtZero' => true]]]
            ]
        ]);
        $countryBarChart->dataset('Page Views', 'bar', $data['user_countries']->values()->all())
            ->backgroundColor(array_slice($colors, 0, count($data['user_countries'])));
        $charts['country_bar'] = $countryBarChart;

        // Pie Chart: Top Referrers
        $referrerPieChart = new Chart;
        $referrerPieChart->title('Top 5 Referrers');
        $referrerPieChart->labels($data['top_referrers']->pluck('pageReferrer')->all());
        $referrerPieChart->options(['responsive' => true, 'maintainAspectRatio' => false]);
        $referrerPieChart->dataset('Views', 'pie', $data['top_referrers']->pluck('screenPageViews')->all())
            ->backgroundColor(array_slice($colors, 0, count($data['top_referrers'])));
        $charts['referrer_pie'] = $referrerPieChart;

        // Bar Chart: Top Browsers
        $browserBarChart = new Chart;
        $browserBarChart->title('Top 5 Browsers');
        $browserBarChart->labels($data['top_browsers']->keys()->all());
        $browserBarChart->options([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'xAxes' => [['ticks' => ['beginAtZero' => true]]],
                'yAxes' => [['ticks' => ['beginAtZero' => true]]]
            ]
        ]);
        $browserBarChart->dataset('Views', 'bar', $data['top_browsers']->values()->all())
            ->backgroundColor(array_slice($colors, 0, count($data['top_browsers'])));
        $charts['browser_bar'] = $browserBarChart;

        // Doughnut Chart: Top Devices
        $deviceDoughnutChart = new Chart;
        $deviceDoughnutChart->title('Top Devices');
        $deviceDoughnutChart->labels($data['top_devices']->keys()->all());
        $deviceDoughnutChart->options(['responsive' => true, 'maintainAspectRatio' => false]);
        $deviceDoughnutChart->dataset('Views', 'doughnut', $data['top_devices']->values()->all())
            ->backgroundColor(array_slice($colors, 0, count($data['top_devices'])));
        $charts['device_doughnut'] = $deviceDoughnutChart;

        // Line Chart: Visits Trend (Total Visitors vs. Unique Visitors)
        $visitsUsersTrendChart = new Chart;
        $visitsUsersTrendChart->title('Visits Trend: Total vs. Unique Visitors');
        $visitsUsersTrendChart->labels($data['visits_and_users_trend']->pluck('date')->all());
        $visitsUsersTrendChart->options(['responsive' => true, 'maintainAspectRatio' => false]);
        $visitsUsersTrendChart->dataset('Total Visitors', 'line', $data['visits_and_users_trend']->pluck('screenPageViews')->all())
            ->backgroundColor('rgba(52, 152, 219, 0.2)')
            ->color('#3498db');
        $visitsUsersTrendChart->dataset('Unique Visitors', 'line', $data['visits_and_users_trend']->pluck('totalUsers')->all())
            ->backgroundColor('rgba(231, 76, 60, 0.2)')
            ->color('#e74c3c');
        $charts['visits_users_trend_line'] = $visitsUsersTrendChart;

        return $charts;
    }
}
```

## 9. Security & production checklist

- NEVER commit the service account JSON to git.
- Keep `storage/` out of public access and appropriately permissioned.
- Use a Laravel cache driver like Redis or Memcached in production to reduce API calls.
- Respect Google API quotas and use caching (`cache_lifetime_in_minutes`).

## 10. Final checklist (before deploying)

- Service account JSON in `storage/app/private/analytics/`
- `.env` values set (`ANALYTICS_PROPERTY_ID`)
- `config/analytics.php` points to the storage path
- Service account added to the GA property with Analyst role
- Composer packages installed and updated
- Controller and Blade file present, route registered
- Caching configured for production


