@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">
        <div class="white-card">
            <div class="row">
                <div class="col-lg-6">
                    @include('darpersocms::cms.components.breadcrumb.index', ['title' => 'POST TYPES'])
                </div>
                <div class="col-lg-6">
                    <div class="d-flex align-items-center justify-content-end">
                        <a href="{{ url(config('cms_config.route_path_prefix') . '/post-types/create') }}"
                            class="btn-action lg add mr-2"><i class="fa-solid fa-plus"></i></a>
                        <a href="{{ url(config('cms_config.route_path_prefix') . '/post-types/create/custom') }}"
                            class="btn-action lg add mr-2"><i class="fa-solid fa-square-plus"></i></a>
                        <a href="{{ url(config('cms_config.route_path_prefix') . '/post-types/order') }}"
                            class="btn-action lg view mr-2"><i class="fa-solid fa-arrows-to-dot"></i></a>
                    </div>
                </div>
            </div>

            <div class="datatable-container mt-3">
                <table class="no-export datatable-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Name Plural</th>
                            <th>Database</th>
                            <th>Route</th>
                            <th>Model</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @if ($row->id > 4)
                                <tr>
                                    <td>{{ $row->display_name }}</td>
                                    <td>{{ $row->display_name_plural }}</td>
                                    <td>{{ $row->database_table }}</td>
                                    <td>{{ $row->route }}</td>
                                    <td>{{ $row->model_name }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <a href="{{ url(config('cms_config.route_path_prefix') . '/post-types/' . ($row['custom_page'] ? 'custom/' : '') . $row['id'] . '/edit') }}"
                                                class="btn-action edit "><i class="fa-solid fa-pen"></i></a>
                                                <form class="d-inline" onsubmit="return confirm('Are you sure?')"
                                                    action="{{ url(config('cms_config.route_path_prefix') . '/post-types/' . $row['id']) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-action  delete ml-2" type="submit"><i
                                                            class="fa-solid fa-trash-can"></i></button>
                                                </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
