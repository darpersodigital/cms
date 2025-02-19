@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id'];
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">
        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action', [
            'title' => 'Show ' . $page['display_name'] . ' #' . $row->id,
            'can_edit' =>
                $page['edit'] && request()->get('admin')['post_types'][$page['route']]['permissions']['edit'],
            'can_order' => null,
            'can_delete' =>
                $page['delete'] && request()->get('admin')['post_types'][$page['route']]['permissions']['delete'],
            'base_url' => $base_url,
            'show_publish' => true,
        ])
        <div class="white-card">
            @foreach ($page_fields as $field)
                @include('darpersocms::cms/post-type/show-fields', ['locale' => null])
            @endforeach
            @if (count($translatable_fields))
                @foreach ($languages as $language)
                    <div class="form-input-container mt-4">
                        @if (count($languages) > 1)
                            <h6>{{ $language->title }}</h6>
                        @endif
                        <div class="pl-3{{ count($languages) > 1 ? '  ' : '' }}">
                            @foreach ($translatable_fields as $field)
                                @include('darpersocms::cms/post-type/show-fields', ['locale' => $language->slug])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
