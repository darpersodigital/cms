@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/admin-roles';
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3">
        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => 'Admin Roles',
            'can_add' => request()->get('admin')['post_types']['admin-roles']['permissions']['add'],
            'testID' => 'admin-roles',
        ])

        <div class="white-card">
            <div class="datatable-container">
                <table class="no-export datatable-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th><span class="pr-1">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->title }}</td>

                                @include('darpersocms::cms.post-type._includes.row-actions', [
                                    'can_view' => request()->get('admin')['post_types']['admin-roles'][
                                        'permissions'
                                    ]['read'],
                                    'can_edit' => request()->get('admin')['post_types']['admin-roles'][
                                        'permissions'
                                    ]['edit'],
                                    'can_delete' => request()->get('admin')['post_types']['admin-roles'][
                                        'permissions'
                                    ]['delete'],
                                    'testID'=>'admin-roles'
                                ])

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
