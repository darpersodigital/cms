@extends('darpersocms::layouts/dashboard')


@section('dashboard-content')
    <form id="post-type-form" method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id'] . $appends_to_query) : url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '') }}"
        ajax>
        <div class="container-fluid px-md-5 mt-5 ">
            @include('darpersocms::cms.components.breadcrumb.breadcrumb-action', [
                'title' => isset($row)
                    ? 'Edit ' . $page['display_name'] . ' #' . $row['id']
                    : 'Add ' . $page['display_name'],
            ])

            <div class="white-card">
                @if (isset($row))
                    @method('put')
                @endif


                @include('darpersocms::cms.components.errors.errors')

                @if (isset($row))
                    <div class="mb-3">
                        @include('darpersocms::cms.components/form-fields/checkbox', [
                            'label' => 'Published',
                            'name' => 'published',
                            'checked' => isset($row['published']) ? $row['published'] : 0,
                            'required' => false,
                            'locale' => null,
                        ])
                    </div>
                @endif

                @foreach ($page_fields as $field)
                    @if ($field['name'] == 'slug' && isset($row))
                        @php
                            $field['form_field_configs_2'] = 1;
                            $field['can_update'] = 1;
                        @endphp
                    @endif
                    @if (
                        $field['form_field'] &&
                            ((!isset($row) && (isset($field['can_create']) && $field['can_create'] == 1)) ||
                                (isset($row) && $field['can_update'] == 1)))
                        @include('darpersocms::cms/post-type/form-fields', ['locale' => null])
                    @endif
                @endforeach
                
                @if (count($page_translatable_fields)>0)
                    @foreach ($languages as $language)
                        <div class="form-input-container">
                            @if (count($languages) > 1)
                                <label>{{ $language->title }}</label>
                            @endif
                            <div class="{{ count($languages) > 1 ? 'pl-3 ' : '' }}">
                                @foreach ($page_translatable_fields as $field)
                                    @include('darpersocms::cms/post-type/form-fields', ['locale' => $language->slug])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                @csrf
                <div class="form-buttons-container justify-content-end d-flex mt-3">
                    @if (!isset($row))
                        <input type="number" name="published" id="isPublished" value="1" class="d-none">
                        <button type="submit" class="btn btn-secondary btn-draft mr-1">Save As Draft</button>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary btn-publish ml-1">
                        @if (!isset($row))
                            Publish
                        @else
                            Update
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
