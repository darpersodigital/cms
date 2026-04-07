    {{-- Charts --}}
    <div class="row ">
        <div class="col-md-12  mt-3">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Visits Trend</h3>
                </div>
                <div class="card-body">
                    {!! $charts['line']->container() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-6 mt-3">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-ol"></i> Top 10 Most Visited Pages</h3>
                   
                </div>
                <div class="card-body">
                    {!! $charts['bar']->container() !!}
                </div>
            </div>
        </div>

        <div class="col-md-6 mt-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link"></i> Top 10 Most Visited Pages Links</h3>
                   
                </div>
                <div class="card-body">
                    <ol>
                        @foreach ($top_pages as $page)
                            @php
                                $url = $page['fullPageUrl'];

                                // If it doesn't start with http:// or https://, add https://
if (!Str::startsWith($url, ['http://', 'https://'])) {
    $url = 'https://' . ltrim($url, '/');
                                }

                            @endphp

                            <li>
                                <a href="{{ $url }}" target="_blank">{{ $page['pageTitle'] }}</a>
                                ({{ $page['screenPageViews'] }} views)
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

    </div>

    <div class="row ">
        <div class="col-md-6 mt-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-globe-americas"></i> Visits by Country</h3>
                   
                </div>
                <div class="card-body">
                    {!! $charts['country_bar']->container() !!}
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-mobile-alt"></i> Top Devices</h3>
                    
                </div>
                <div class="card-body">
                    {!! $charts['device_doughnut']->container() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-6 mt-3">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link"></i> Top 5 Referrers</h3>
                    
                </div>
                <div class="card-body">
                    {!! $charts['referrer_pie']->container() !!}
                </div>
            </div>
        </div>

        <div class="col-md-6 mt-3">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fab fa-chrome"></i> Top 5 Browsers</h3>
                  
                </div>
                <div class="card-body">
                    {!! $charts['browser_bar']->container() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-12 mt-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Visits Trend: Total vs. Unique Visitors
                    </h3>
                   
                </div>
                <div class="card-body">
                    {!! $charts['visits_users_trend_line']->container() !!}
                </div>
            </div>
        </div>
    </div>
