 <div class="row mb-5 mb-md-3  ">
        <div class="col-sm-auto ">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($total_visits) }}</h3>

                    <p>Total page view</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>

            </div>
        </div>

         <div class="col-sm-auto ">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($total_users) }}</h3>

                    <p>Total users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>

            </div>
        </div>

        <div class="col-sm-auto ">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($new_users) }}</h3>

                    <p>New users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>

            </div>
        </div>

        <div class="col-sm-auto ">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ Str::startsWith($avg_session_duration, '00:') ? substr($avg_session_duration, 3) : $avg_session_duration }}
                    </h3>

                    <p>Average session duration</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-auto ">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $bounce_rate }}%</h3>

                    <p>Bounce rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

    </div>