@extends('darpersocms::layouts/dashboard')


@section('dashboard-content')

@php
    $urlPath = '/post-types/custom' . (isset($post_type) ? '/' . $post_type['id'] : '');
@endphp
    <form method="post"
        action="{{ url(config('cms_config.route_path_prefix') . $urlPath) }}">

        <div class="container-fluid px-md-5 mt-5 ">
            <div class="white-card">
                @include('darpersocms::cms.components.breadcrumb.index', [
                    'title' => isset($post_type)
                        ? 'Edit Custom CMS page #' . $post_type['id']
                        : 'Add Custom CMS page',
                ])
            </div>

            <div class="white-card">
                @if (isset($post_type))
                    @method('put')
                @endif

                @include('darpersocms::cms.components.errors.errors')
                @php
                    $fields = [
                        ['label' => 'Display name plural', 'name' => 'display_name_plural', 'slug_origin' => null],
                        ['label' => 'Route', 'name' => 'route', 'slug_origin' => 'display_name_plural'],
                        ['label' => 'Icon', 'name' => 'icon', 'slug_origin' => null],
                    ];
                @endphp

                @foreach ($fields as $field)
                   <div class="mb-3">
                    @include('darpersocms::cms.components.form-fields.input', [
                        'label' => $field['label'],
                        'name' => $field['name'],
                        'type' => 'text',
                        'value' => $post_type[$field['name']] ?? '',
                        'locale' => null,
                        'slug_origin' => $field['slug_origin'],
                    ])
                   </div>
                @endforeach

                <div class="d-flex justify-content-end">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </div>

            </div>
        </div>
    </form>
@endsection
