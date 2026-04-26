@extends('darpersocms::layouts/dashboard')
@php
    $base_url =
        config('cms_config.route_path_prefix') . '/google-analytics' . (isset($row['id']) ? '/' . $row['id'] : '');
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => 'Google Analytics ' . '(' . ucfirst($currentRange ?? '') . ')',
            'can_add' =>
                !isset($row) && request()->get('admin')['post_types']['google-analytics']['permissions']['add'],
            'can_edit' =>
                isset($row) && request()->get('admin')['post_types']['google-analytics']['permissions']['edit'],
            'can_delete' =>
                isset($row) && request()->get('admin')['post_types']['google-analytics']['permissions']['delete'],
            'testID' => 'google-analytics',
            'base_url' => $base_url,
        ])


        @if (isset($row))
            <div class="white-card analytics">

                @include('darpersocms::cms.seo.google-analytics.components.filtering-form')
                @include('darpersocms::cms.seo.google-analytics.components.info-boxes')
                @include('darpersocms::cms.seo.google-analytics.components.charts')

            </div>
        @endif
    </div>
@endsection


@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>

    @if (isset($charts))
        {!! $charts['line']->script() !!}
        {!! $charts['bar']->script() !!}
        {!! $charts['country_bar']->script() !!}
        {!! $charts['referrer_pie']->script() !!}
        {!! $charts['browser_bar']->script() !!}
        {!! $charts['device_doughnut']->script() !!}
        {!! $charts['visits_users_trend_line']->script() !!}
    @endif


    <script>
        $(document).ready(() => {
            const $dateRangeSelect = $('select[name="date_range"]');
            if (!$dateRangeSelect.length) {
                return;
            }

            const toggleCustomDateInputs = (isCustom) => {
                const $form = $dateRangeSelect.closest('form');
                const $customDateRange = $form.find('.custom-date');
                const $startDate = $form.find('input[name="start_date"]');
                const $endDate = $form.find('input[name="end_date"]');

                if (isCustom) {
                    $customDateRange.stop(true, true).slideDown();
                    $startDate.prop('disabled', false).prop('required', true);
                    $endDate.prop('disabled', false).prop('required', true);
                    return;
                }

                $customDateRange.stop(true, true).slideUp();
                $startDate.prop('disabled', true).prop('required', false).val('');
                $endDate.prop('disabled', true).prop('required', false).val('');
            };

            toggleCustomDateInputs($dateRangeSelect.val() === 'custom');

            $dateRangeSelect.on('change', function() {
                const isCustom = $(this).val() === 'custom';
                toggleCustomDateInputs(isCustom);

                if (!isCustom) {
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
@endsection
