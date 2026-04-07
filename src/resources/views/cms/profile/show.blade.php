@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => 'PROFILE',
            'base_url' => config('cms_config.route_path_prefix') . '/profile',
			'can_edit'=>true,
             'testID'=>'show-profile'
        ])

        <div class="white-card">
            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Full Name',
                'testID'=>'full_name',
                'value' => request()->get('admin')['full_name'],
            ])
            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Username',
                'testID'=>'user_name',
                'value' => request()->get('admin')['user_name'],
            ])
            @include('darpersocms::cms.components/show-fields/image', [
                'label' => 'Image',
                'testID'=>'image',
                'value' => request()->get('admin')['image'] ? request()->get('admin')['image'] : '',
            ])

        </div>

    </div>
@endsection
