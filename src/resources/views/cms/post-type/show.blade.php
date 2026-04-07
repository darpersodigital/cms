@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id'];
@endphp

@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3" data-testid="show-post-type-{{ $page['route'] }}">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => 'Show ' . $page['display_name'] . ($page['single_record'] ? '' : ' #' . $row->id),
            'can_edit' =>
                $page['edit'] && request()->get('admin')['post_types'][$page['route']]['permissions']['edit'],
            'can_order' => null,
            'can_delete' =>
                $page['delete'] && request()->get('admin')['post_types'][$page['route']]['permissions']['delete'],
            'base_url' => $base_url,
            'testID' => $page['route'],
        ])

        <div class="white-card">
            @if ($page['single_record'] !== 1 )
                <div class="pb-2">
                    @if ($row['published'])
                        <span class="badge badge-pill badge-primary" data-testid="show-published">Published</span>
                    @else
                        <span class="badge badge-pill badge-secondary" data-testid="show-draft">Draft</span>
                    @endif
                </div>
            @endif
            @foreach ($page_fields as $field)
                @include('darpersocms::cms/post-type/show-fields', ['locale' => null])
            @endforeach
            @if (count($translatable_fields))
                @foreach ($languages as $language)
                    <div class="form-input-container {{ count($languages) > 1 ? ' mt-4' : '' }}">
                        @if (count($languages) > 1)
                            <h6>{{ $language->title }}</h6>
                        @endif
                        <div class="{{ count($languages) > 1 ? ' pl-3 ' : '' }}">
                            @foreach ($translatable_fields as $field)
                                @include('darpersocms::cms/post-type/show-fields', [
                                    'locale' => $language->slug,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
