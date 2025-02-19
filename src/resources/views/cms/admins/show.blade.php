@extends('darpersocms::layouts/dashboard')



@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">

        <div class="white-card">
            <div class="row px-0 reverse-row-sm">
                <div class="col-lg-6">
                    @include('darpersocms::cms.components.breadcrumb.index', ['title' => 'SHOW ADMIN #' . $row->id])
                </div>
                <div class="col-lg-6 ">
                    <div class="d-flex justify-content-end">
                        @if (request()->get('admin')['post_types']['admins']['permissions']['edit'])
                        <a href="{{ url(config('cms_config.route_path_prefix') . '/admins/' . $row->id . '/edit') }}"
                            class="btn-action lg edit mr-2"><i class="fa-solid fa-pen"></i></a>
                    @endif
                        @if (request()->get('admin')['post_types']['admins']['permissions']['delete'])
                            <form class="d-inline" onsubmit="return confirm('Are you sure?')" method="post"
                                action="{{ url(config('cms_config.route_path_prefix') . '/admins/' . $row->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn-action lg delete "><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        <div class="white-card">
   

            @include('darpersocms::cms.components/show-fields/text', ['label' => 'Name', 'value' => $row->name])
            @include('darpersocms::cms.components/show-fields/image', ['label' => 'Image', 'value' => $row->image])
            @include('darpersocms::cms.components/show-fields/text', ['label' => 'Email', 'value' => $row->email])
            @include('darpersocms::cms.components/show-fields/text', ['label' => 'Role', 'value' => $row->role->title])

        </div>
    </div>
@endsection
