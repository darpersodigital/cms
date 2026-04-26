<?php

namespace Darpersodigital\Cms\Controllers\seo;
use Illuminate\Http\Request;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\GoogleAnalytic;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Collection;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;


use Darpersodigital\Cms\Services\EnvServices;

class GoogleAnalyticsController extends BaseController
{
    private EnvServices $envServices;

    protected $dateRanges = ['today', 'weekly', 'monthly', 'custom'];


    public function __construct(EnvServices $envServices)
    {
        $this->envServices = $envServices;
    }

    public function index(Request $request)
    {
        $row = GoogleAnalytic::first();
        $page = PostType::where('route', 'google-analytics')->firstOrFail();
          
        if(!env('ANALYTICS_PROPERTY_ID')) {
              return view('darpersocms::cms.seo.google-analytics.index');
        }
        // 1. Get and process the selected date range
        $range = $request->input('date_range', 'weekly');
        list($period, $startDate, $endDate) = $this->getAnalyticsPeriod($request, $range);

        // 2. Fetch all required data from Google Analytics API
        $data = $this->fetchAnalyticsData($period);

        // 3. Prepare Charts
        $charts = $this->prepareCharts($data);
        $dateRanges = $this->dateRanges;
        $currentRange = $range;
        $startDate =  $startDate->toDateString();
        $endDate = $endDate->toDateString();

        return view('darpersocms::cms.seo.google-analytics.index', array_merge(
            [
                'row' => $row,
                'page' => $page,
                'data' => $data,
            ],
            $data,
            [
                'charts' => $charts,
                'currentRange' => $currentRange,
                'dateRanges' => $dateRanges,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]
        ));
    }

    public function create()
    {
        return view('darpersocms::cms.seo.google-analytics.form', [
            'ga4GuideHtml' => $this->getGa4GuideHtml(),
        ]);
    }

    private function saveGoogleAnalyticsData(Request $request, $id = null)
    {
        $row = $id ? GoogleAnalytic::findOrFail($id) : new GoogleAnalytic();
        $serviceAccountRules = ['nullable', 'file', 'mimes:json'];
        if (!$id || !$row->service_account_credentials_json) {
            $serviceAccountRules[0] = 'required';
        }

        $request->validate([
            'property_id' => ['required', 'string'],
            'cache_lifetime_in_minutes' => ['required', 'integer', 'min:0'],
            'service_account_credentials_json' => $serviceAccountRules,
        ]);

        $row->property_id = $request->property_id;
        $row->cache_lifetime_in_minutes = (int) $request->cache_lifetime_in_minutes;

        if ($request->hasFile('service_account_credentials_json')) {
            if ($id && $row->service_account_credentials_json) {
                $existingFilePath = storage_path('app/private/' . ltrim($row->service_account_credentials_json, '/'));
                if (File::exists($existingFilePath)) {
                    File::delete($existingFilePath);
                }
            }

            $privateDirectory = storage_path('app/private/google-analytics');
            if (!File::exists($privateDirectory)) {
                File::makeDirectory($privateDirectory, 0755, true);
            }

            $fileName = uniqid('service-account-', true) . '.json';
            $request->file('service_account_credentials_json')->move($privateDirectory, $fileName);
            $row->service_account_credentials_json = 'google-analytics/' . $fileName;
        }

        $row->save();
        $this->syncAnalyticsConfig($row);
        $message = $id ? 'Record edited successfully' : 'Record added successfully';
        $request->session()->flash('success', $message);

        return redirect(config('cms_config.route_path_prefix') . '/google-analytics');
    }

    public function store(Request $request)
    {
        return $this->saveGoogleAnalyticsData($request);
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $row = GoogleAnalytic::findOrFail($id);
        return view('darpersocms::cms.seo.google-analytics.form', [
            'row' => $row,
            'ga4GuideHtml' => $this->getGa4GuideHtml(),
        ]);
    }

    public function guideAsset(string $filename)
    {
        $safeFilename = basename($filename);
        if ($safeFilename !== $filename) {
            abort(404);
        }

        $assetPath = __DIR__ . '/ga4/ga4_laravel_dashboard_assets/' . $safeFilename;
        if (!File::exists($assetPath)) {
            abort(404);
        }

        return response()->file($assetPath);
    }

    private function getGa4GuideHtml(): string
    {
        $guidePath =  __DIR__ . '/ga4/ga4_credentials_guide.md' ;

        if (!File::exists($guidePath)) {
            return '<p>GA4 guide not found at: ' . e($guidePath) . '</p>';
        }

        $guideMarkdown = (string) File::get($guidePath);
        $guideHtml = Str::markdown($guideMarkdown, [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        $assetBaseUrl = url(config('cms_config.route_path_prefix') . '/google-analytics/guide-assets');
        $guideHtml = preg_replace_callback(
            '/src="ga4_laravel_dashboard_assets\/([^"]+)"/',
            static fn ($matches) => 'src="' . $assetBaseUrl . '/' . rawurlencode($matches[1]) . '"',
            $guideHtml
        );

        return (string) $guideHtml;
    }

    public function update(Request $request, $id)
    {
        return $this->saveGoogleAnalyticsData($request, $id);
    }

    public function destroy($id)
    {
        $array = explode(',', $id);
        foreach ($array as $analyticId) {
            $row = GoogleAnalytic::find($analyticId);
            if (!$row) {
                continue;
            }

            if ($row->service_account_credentials_json) {
                $filePath = storage_path('app/private/' . ltrim($row->service_account_credentials_json, '/'));
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }

            $row->delete();
        }

        $this->envServices->removeEnvValue('ANALYTICS_PROPERTY_ID');
        $this->envServices->removeEnvValue('ANALYTICS_SERVICE_ACCOUNT_CREDENTIALS_JSON');

        $analyticsConfigPath = config_path('analytics.php');
        if (File::exists($analyticsConfigPath)) {
            File::delete($analyticsConfigPath);
        }

        return redirect(config('cms_config.route_path_prefix') . '/google-analytics')->with('success', 'Record deleted successfully');
    }

    private function syncAnalyticsConfig(GoogleAnalytic $row): void
    {
        $propertyId = (string) $row->property_id;
        $cacheLifetimeInMinutes = (int) $row->cache_lifetime_in_minutes;
        $credentialsFromRow = (string) $row->service_account_credentials_json;
        $this->envServices->setEnvValue('ANALYTICS_PROPERTY_ID', $propertyId);
        $this->envServices->setEnvValue('ANALYTICS_SERVICE_ACCOUNT_CREDENTIALS_JSON', $credentialsFromRow);
        $this->writeAnalyticsConfigFile($cacheLifetimeInMinutes);
    }

    private function writeAnalyticsConfigFile(int $cacheLifetimeInMinutes): void
    {
        $analyticsConfig = <<<PHP
        <?php

        return [
            'property_id' => env('ANALYTICS_PROPERTY_ID'),
            'service_account_credentials_json' => storage_path('app/private/' . ltrim(env('ANALYTICS_SERVICE_ACCOUNT_CREDENTIALS_JSON'), '/')),
            'cache_lifetime_in_minutes' => {$cacheLifetimeInMinutes},
            'cache' => [
                'store' => 'file',
            ],
        ];
        PHP;

        File::put(config_path('analytics.php'), $analyticsConfig . PHP_EOL);
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
        // Fetch data in parallel where possible
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
            ->backgroundColor('rgba(52, 152, 219, 0.2)') // Blue
            ->color('#3498db');

        $visitsUsersTrendChart->dataset('Unique Visitors', 'line', $data['visits_and_users_trend']->pluck('totalUsers')->all())
            ->backgroundColor('rgba(231, 76, 60, 0.2)') // Red
            ->color('#e74c3c');
        $charts['visits_users_trend_line'] = $visitsUsersTrendChart;

        return $charts;
    }
}
