<div class="row">
    <div class="col-12 dataTables_filter text-right mb-3">
        <div class="d-flex justify-content-start  ">
            <form method="GET" action="{{ url(config('cms_config.route_path_prefix') . '/google-analytics') }}"
                class="form-inline">

                <div class="d-flex align-items-end flex-wrap text-left">
                    @php
                        $dateRangeOptions = collect($dateRanges)
                            ->map(
                                fn($range) => [
                                    'value' => $range,
                                    'title' => ucfirst($range),
                                ],
                            )
                            ->all();
                    @endphp

                    <div class="d-flex flex-wrap flex-row">
                        @include('darpersocms::cms.components/form-fields/select', [
                            'name' => 'date_range',
                            'options' => $dateRangeOptions,
                            'label' => 'Filter Data Range',
                            'store_column' => 'value',
                            'display_column' => 'title',
                            'value' => $currentRange,
                            'testID' => 'date_range',
                            'error' => '',
                            'locale' => '',
                            'style' => 'mt-0 mr-3',
                        ])
                        <div class="custom-date">
                            <div class=" d-flex align-items-end flex-row flex-wrap ">

                                @include('darpersocms::cms.components/form-fields/date', [
                                    'label' => 'Start Date',
                                    'name' => 'start_date',
                                    'testID' => 'start_date',
                                    'type' => 'text',
                                    'value' => $startDate,
                                    'error' => $errors->first('start_date'),
                                    'required' => true,
                                    'description' => '',
                                    'style' => 'mr-2',
                                    'locale' => '',
                                ])

                                @include('darpersocms::cms.components/form-fields/date', [
                                    'label' => 'End Date',
                                    'name' => 'end_date',
                                    'testID' => 'end_date',
                                    'type' => 'text',
                                    'value' => $endDate,
                                    'error' => $errors->first('endDate'),
                                    'required' => true,
                                    'description' => '',
                                    'style' => 'mr-2',
                                    'locale' => '',
                                ])
                                <div class="d-flex mb-1">
                                    <button type="submit" class="theme-btn sm ">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
