@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/admin-roles';
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">
        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action',[
            'title'=>"Admin Roles",
            'can_add'=> request()->get('admin')['post_types']['admin-roles']['permissions']['add'] ,
        ])
       
        <div class="white-card">
            <div class="datatable-container">
                <table class="no-export datatable-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Title</th>
                            <th class="text-right"><span class="pr-1">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>
                                    <label class="checkbox-container checkbox-delete-container">
                                        <input type="checkbox" value="{{ $row->id }}">
                                        <div></div>
                                    </label>
                                </td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->title }}</td>

                                @include('darpersocms::cms.post-type._includes.row-actions',[
                                    'can_view'=>request()->get('admin')['post_types']['admin-roles']['permissions']['read'],
                                    'can_edit'=>request()->get('admin')['post_types']['admin-roles']['permissions']['edit'],
                                    'can_delete'=>request()->get('admin')['post_types']['admin-roles']['permissions']['delete'],
                           
                                ])
                              
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
