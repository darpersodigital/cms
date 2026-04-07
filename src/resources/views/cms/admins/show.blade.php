@extends('darpersocms::layouts/dashboard')



@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => 'SHOW ADMIN #' . $row->id,
            'can_delete' => request()->get('admin')['post_types']['admins']['permissions']['delete'],
            'can_edit' => request()->get('admin')['post_types']['admins']['permissions']['edit'],
            'base_url' => config('cms_config.route_path_prefix') . '/admins/' . $row['id'],
                     'testID'=>'admins'
        ])

        <div class="white-card">

            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Full Name',
                'value' => $row->full_name,
                'testID'=>'full_name'
            ])

            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Username',
                'value' => $row->user_name,
                'testID'=>'user_name'

            ])
            @include('darpersocms::cms.components/show-fields/image', [
                'label' => 'Image',
                'testID'=>'image',
                'value' => $row->image,
            ])
            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Email',
                'testID'=>'email',

                'value' => $row->email,
            ])
            @include('darpersocms::cms.components/show-fields/text', [
                'label' => 'Role',
                'testID'=>'admin_role_id',
                'value' => $row->role->title,
            ])

        </div>
    </div>
@endsection
