@extends('darpersocms::layouts/dashboard')


@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">

        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action',[
            'title'=> "Role: ".$row->title,
                'can_delete'=>request()->get('admin')['post_types']['admin-roles']['permissions']['delete'],
            'can_edit'=>request()->get('admin')['post_types']['admin-roles']['permissions']['edit'],
            'base_url' => config('cms_config.route_path_prefix') . '/admin-roles/' . $row['id']
        ])

        <div class="white-card">
            <div class="row">
                <div class="col-12 d-flex justify-content-center">
                    <table class="table table-responsive w-100">
                        <thead>
                            <tr>
                                <th class="text-center">Post Type</th>
                                <th class="text-center">Can Browse</th>
                                <th class="text-center">Can Read</th>
                                <th class="text-center">Can Edit</th>
                                <th class="text-center">Can Add</th>
                                <th class="text-center">Can Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admin_role_permissions as $permission)
                                <tr>
                                    <td class="text-center">{{ $permission->page->display_name }}</td>
                                    <td class="text-center {{ $permission->browse ? 'text-success' : 'text-danger' }}"><i
                                            class="fa fa-{{ $permission->browse ? 'check' : 'times' }}"
                                            aria-hidden="true"></i></td>
                                    <td class="text-center {{ $permission->read ? 'text-success' : 'text-danger' }} "><i
                                            class="fa fa-{{ $permission->read ? 'check' : 'times' }}"
                                            aria-hidden="true"></i></td>
                                    <td class="text-center {{ $permission->edit ? 'text-success' : 'text-danger' }}"><i
                                            class="fa fa-{{ $permission->edit ? 'check' : 'times' }}"
                                            aria-hidden="true"></i></td>
                                    <td class="text-center {{ $permission->add ? 'text-success' : 'text-danger' }}"><i
                                            class="fa fa-{{ $permission->add ? 'check' : 'times' }}"
                                            aria-hidden="true"></i></td>
                                    <td class="text-center {{ $permission->delete ? 'text-success' : 'text-danger' }} "><i
                                            class="fa fa-{{ $permission->delete ? 'check' : 'times' }}"
                                            aria-hidden="true"></i>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
