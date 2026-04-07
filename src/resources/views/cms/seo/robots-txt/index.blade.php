@extends('darpersocms::layouts/dashboard')
@php
    $base_url = config('cms_config.route_path_prefix') . '/robots-txts';
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader',[
            'title'=>"Robots",
            'can_add'=> request()->get('admin')['post_types']['robots-txts']['permissions']['add'],
            'can_delete'=> request()->get('admin')['post_types']['robots-txts']['permissions']['delete'],
            'testID'=> 'robots-txts'
        ])
  
        
        <div class="white-card ">
            <div class=" datatable-container mt-3">
                <table class="datatable-table no-export ">
                    <thead>
                        <tr>
                             <th>
                                <label class="checkbox-container check-all-checkboxes">
                                    <input type="checkbox">
                                    <div></div>
                                </label>
                            </th>
                            <th>#</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>
                                    <label class="checkbox-container checkbox-delete-container">
                                        <input type="checkbox" value="{{ $row['id'] }}">
                                        <div></div>
                                    </label>
                                </td>
                                
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->content }}</td>

                                @include('darpersocms::cms.post-type._includes.row-actions',[
                                    'can_edit'=>request()->get('admin')['post_types']['robots-txts']['permissions']['edit'],
                                    'can_delete'=>request()->get('admin')['post_types']['robots-txts']['permissions']['delete'],
                                    'testID'=>'robots-txts'
                                ])

                                {{-- <td >
                                    <div class="d-flex justify-content-end">
                                    @if ()
                                        <a href="{{ url(config('cms_config.route_path_prefix') . '/languages/' . $row->id . '/edit') }}"
                                            class="btn-action edit mr-2"><i class="fa-solid fa-pen"></i></a>
                                    @endif
                                    @if (request()->get('admin')['post_types']['languages']['permissions']['delete'])
                                        <form class="d-inline" onsubmit="return confirm('Are you sure?')" method="post"
                                            action="{{ url(config('cms_config.route_path_prefix') . '/languages/' . $row->id) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="btn-action delete "><i
                                                class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    @endif
                                    </div>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
