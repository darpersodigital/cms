@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/languages/' . $row['id']) : url(config('cms_config.route_path_prefix') . '/languages') }}"
        ajax>
        <div class="container-fluid px-md-5 mt-5 ">

            @include('darpersocms::cms.components.breadcrumb.breadcrumb-action', [
                'title' => isset($row) ? 'Edit Language #' . $row['id'] : 'Add Language',
            ])

            <div class="white-card">

                @include('darpersocms::cms.components.errors.errors')


                @if (isset($row))
                    @method('put')
                @endif
                <div class="mb-3">
                    @include('darpersocms::cms.components/form-fields/input', [
                        'label' => 'Slug',
                        'name' => 'slug',
                        'type' => 'text',
                        'value' => isset($row) ? $row->slug : '',
                        'required' => true,
                        'description' => '',
                        'locale' => '',
                    ])
                </div>
                <div class="mb-3">
                    @include('darpersocms::cms.components/form-fields/input', [
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'value' => isset($row) ? $row->title : '',
                        'required' => true,
                        'description' => '',
                        'locale' => '',
                    ])
                </div>
                <div class="mb-3">
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
                        'description' => '',
                        'locale' => '',
                    ])
                </div>
                @csrf

                <div class="form-buttons-container text-right">

                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                </div>
            </div>
        </div>

    </form>
@endsection
