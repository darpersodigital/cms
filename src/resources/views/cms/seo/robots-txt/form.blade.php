@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/robots-txts/' . $row['id']) : url(config('cms_config.route_path_prefix') . '/robots-txts') }}"
        ajax>
        <div class="container-fluid px-md-5  mt-3">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? 'Edit Robots TXT #' . $row['id'] : 'Add Robots TXT',
                'submit' => isset($row) ? 'Update' : 'Add',
                'testID' => 'robots-txts',
            ])
            <div class="white-card">

                @if (isset($row))
                    @method('put')
                @endif


                @include('darpersocms::cms.components/form-fields/textarea', [
                    'label' => 'Content',
                    'name' => 'content',
                    'testID' => 'content',
                    'disable_counter'=>true,
                    'type' => 'text',
                    'value' => isset($row) ? $row->content : '',
                    'error' => $errors->first('content'),
                    'required' => false,
                    'description' => '',
                    'locale' => '',
                ])


                @csrf

            </div>
        </div>

    </form>
    <style>
        textarea {
            min-height: 700px !important;
            height: 700px !important;
        }
    </style>
@endsection
