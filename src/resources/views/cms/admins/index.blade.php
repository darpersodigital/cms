@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/admins';
@endphp
@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">

        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action',[
            'title'=>"Admins",
            'can_add'=> request()->get('admin')['post_types']['admins']['permissions']['add'],
            'can_delete'=>count($rows)>1 && request()->get('admin')['post_types']['admins']['permissions']['delete'],
            'base_url' =>$base_url
        ])

        <div class="white-card">
            <div class="datatable-container mt-4">
                <table class="datatable-table no-export">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
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
                                <td>{{ $row->name }}</td>
                                <td>
                                    @if (isset($row->image) && $row->image)
                                        <img src="{{ Storage::url($row->image) }}" class="img-thumbnail">
                                    @endif
                                </td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->role->title }}</td>
                                @include('darpersocms::cms.post-type._includes.row-actions',[
                                    'can_view'=>request()->get('admin')['post_types']['admins']['permissions']['read'],
                                    'can_edit'=>request()->get('admin')['post_types']['admins']['permissions']['edit'],
                                    'can_delete'=>request()->get('admin')['post_types']['admins']['permissions']['delete'],
                                    'base_url'=>$base_url
                                ])
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
