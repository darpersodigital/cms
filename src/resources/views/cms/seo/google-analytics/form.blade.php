@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/google-analytics/' . $row['id']) : url(config('cms_config.route_path_prefix') . '/google-analytics') }}"
        ajax>
        <div class="container-fluid px-md-5  mt-3">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? 'Edit Google Analytic' : 'Add Google Analytic',
                'submit' => isset($row) ? 'Update' : 'Add',
                'testID' => 'google-analytics',
            ])
            <div class="white-card">

                @if (isset($row))
                    @method('put')
                @endif

                @include('darpersocms::cms.components/form-fields/TextInput', [
                    'label' => 'Property Id',
                    'name' => 'property_id',
                    'testID' => 'property_id',
                    'disable_counter' => true,
                    'type' => 'text',
                    'value' => isset($row) ? $row->property_id : '',
                    'error' => $errors->first('property_id'),
                    'required' => true,
                    'description' => '',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/TextInput', [
                    'label' => 'Cache Lifetime in minitues',
                    'name' => 'cache_lifetime_in_minutes',
                    'testID' => 'cache_lifetime_in_minutes',
                    'disable_counter' => true,
                    'type' => 'text',
                    'value' => isset($row->cache_lifetime_in_minutes) ? $row->cache_lifetime_in_minutes : 60 * 24,
                    'error' => $errors->first('cache_lifetime_in_minutes'),
                    'required' => true,
                    'description' => '',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/file', [
                    'label' => 'Service File',
                    'name' => 'service_account_credentials_json',
                    'testID' => 'service_account_credentials_json',
                    'type' => 'text',
                    'style' => 'mt-3',
                    'value' => isset($row) ? $row->service_account_credentials_json : '',
                    'error' => $errors->first('service_account_credentials_json'),
                    'required' => true,
                    'description' => '',
                    'locale' => '',
                ])
                @csrf


                <div class="ga4-guide-content mt-2">
                    {!! $ga4GuideHtml ?? '' !!}
                </div>

            </div>
        </div>

    </form>
    <style>
        textarea {
            min-height: 700px !important;
        }

        .ga4-guide-content {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            max-height: 700px;
            overflow: auto;
        }

        .ga4-guide-content img {
            max-width: 100%;
            height: auto;
            margin: 8px 0 16px;
            border-radius: 6px;
        }

        .ga4-guide-content p {
            margin-top: 15px;
        }

        .ga4-guide-content h2 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .ga4-guide-content h2:first-child {
            margin-top: 0;
        }
    </style>
@endsection
