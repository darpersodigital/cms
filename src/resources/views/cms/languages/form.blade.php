@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/languages/' . $row['id']) : url(config('cms_config.route_path_prefix') . '/languages') }}"
        ajax>
        <div class="container-fluid px-md-5  mt-3">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? 'Edit Language #' . $row['id'] : 'Add Language',
                'submit' => isset($row) ?  'Update' : 'Add',
                'testID'=>'languages'
            ])
            <div class="white-card">

                @if (isset($row))
                    @method('put')
                @endif

                @include('darpersocms::cms.components/form-fields/TextInput', [
                    'label' => 'Title',
                    'name' => 'title',
                    'testID'=>'title',
                    'type' => 'text',
                    'value' => isset($row) ? $row->title : '',
                    'error' => $errors->first('title'),
                    'required' => true,
                    'description' => '',
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/TextInput', [
                    'label' => 'Slug',
                    'name' => 'slug',
                    'testID'=>'slug',
                    'type' => 'text',
                    'value' => isset($row) ? $row->slug : '',
                    'error' => $errors->first('slug'),
                    'required' => true,
                    'description' => '',
                    'locale' => '',
                ])
                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Direction',
                    'name' => 'direction',
                    'options' => [
                        [
                            'title' => 'Left To Right',
                            'value' => 'ltr',
                        ],
                        [
                            'title' => 'Right To Left',
                            'value' => 'rtl',
                        ],
                    ],
                    'store_column' => 'value',
                    'display_column' => 'title',
                    'value' => isset($row) ? $row->direction : '',
                    'required' => true,
                    'error' => $errors->first('direction'),
                    'description' => '',
                    'testID'=>'direction',
                    'locale' => '',
                ])
                @csrf

            </div>
        </div>

    </form>
@endsection
