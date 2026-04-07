@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/sitemaps/' . $row['id']) : url(config('cms_config.route_path_prefix') . '/sitemaps') }}"
        ajax>
        <div class="container-fluid px-md-5  mt-3">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? 'Edit Sitemap #' . $row['id'] : 'Add Sitemap',
                'submit' => isset($row) ? 'Update' : 'Add',
                'testID' => 'sitemaps',
            ])
            <div class="white-card">

                @if (isset($row))
                    @method('put')
                @endif

                @include('darpersocms::cms.components/form-fields/checkbox', [
                    'label' => 'Published',
                    'name' => 'published',
                    'checked' => isset($row['published']) ? $row['published'] : 0,
                    'required' => false,
                    'locale' => null,
                ])


                @include('darpersocms::cms.components/form-fields/TextInput', [
                    'label' => 'Url',
                    'name' => 'url',
                    'testID' => 'url',
                    'type' => 'text',
                    'value' => isset($row) ? $row->url : '',
                    'error' => $errors->first('url'),
                    'required' => false,
                    'description' => '',
                    'locale' => '',
                ])


                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Locale',
                    'name' => 'locale',
                    'options' => $languages->map(function ($language) {
                            return [
                                'title' => $language['slug'],
                                'value' => $language['slug'],
                            ];
                        })->toArray(),
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) ? $row->locale : '',
                    'required' => false,
                    'error' => $errors->first('locale'),
                    'description' => '',
                    'testID' => 'locale',
                    'locale' => '',
                ])
                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Priority',
                    'name' => 'priority',
                    'options' => [
                        [
                            'title' => 'Highest (1.0)',
                            'value' => '1.0',
                        ],
                        [
                            'title' => 'Very High (0.9)',
                            'value' => '0.9',
                        ],
                        [
                            'title' => 'High (0.8)',
                            'value' => '0.8',
                        ],
                        [
                            'title' => 'Above Average (0.7)',
                            'value' => '0.7',
                        ],
                        [
                            'title' => 'Normal (0.5)',
                            'value' => '0.5',
                        ],
                        [
                            'title' => 'Low (0.4)',
                            'value' => '0.4',
                        ],
                        [
                            'title' => 'Very Low (0.3)',
                            'value' => '0.3',
                        ],
                        [
                            'title' => 'Minimal (0.2)',
                            'value' => '0.2',
                        ],
                        [
                            'title' => 'Lowest (0.1)',
                            'value' => '0.1',
                        ],
                    ],
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) && isset($row->priority) ? $row->priority : '0.5',
                    'required' => false,
                    'error' => $errors->first('priority'),
                    'description' => '',
                    'testID' => 'priority',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Change Frequency',
                    'name' => 'change_frequency',
                    'options' => [
                        [
                            'title' => 'Always',
                            'value' => 'always',
                        ],
                        [
                            'title' => 'Hourly',
                            'value' => 'hourly',
                        ],
                        [
                            'title' => 'Daily',
                            'value' => 'daily',
                        ],
                        [
                            'title' => 'Weekly',
                            'value' => 'weekly',
                        ],
                        [
                            'title' => 'Monthly',
                            'value' => 'monthly',
                        ],
                        [
                            'title' => 'Yearly',
                            'value' => 'yearly',
                        ],
                        [
                            'title' => 'Never',
                            'value' => 'never',
                        ],
                    ],
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) &&isset($row->change_frequency ) ? $row->change_frequency : 'monthly',
                    'required' => false,
                    'error' => $errors->first('change_frequency'),
                    'description' => '',
                    'testID' => 'change_frequency',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Post Type',
                    'name' => 'post_type_id',
                    'options' => $postTypes->map(function ($postType) {
                            return [
                                'title' => $postType['display_name'],
                                'value' => $postType['id'],
                            ];
                        })->toArray(),
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) ? $row->post_type_id : '',
                    'required' => false,
                    'error' => $errors->first('post_type_id'),
                    'description' => '',
                    'testID' => 'post_type_id',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Children Post Type',
                    'name' => 'post_type_children_id',
                    'options' => $postTypes_multiple->map(function ($postType) {
                            return [
                                'title' => $postType['display_name'],
                                'value' => $postType['id'],
                            ];
                        })->toArray(),
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) ? $row->post_type_children_id : '',
                    'required' => false,
                    'error' => $errors->first('post_type_children_id'),
                    'description' => '',
                    'testID' => 'post_type_children_id',
                    'locale' => '',
                ])
                @csrf

            </div>
        </div>

    </form>
@endsection
