@extends('darpersocms::layouts/dashboard')

@php
    $showSeoChecker = false;
     $required_fields = ['seo_title', 'seo_description', 'seo_keywords'];
    $found = [];
    foreach ($page_translatable_fields as $field) {
        if (in_array($field['name'], $required_fields)) {
            $found[] = $field['name'];
        }
    }
    if (count($found) === count($required_fields) && count($found)>0 ) {
        $showSeoChecker = true;
    }
@endphp

@section('dashboard-content')

    <form id="post-type-form" method="post" enctype="multipart/form-data"
        action="{{ isset($row) ? url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id'] . $appends_to_query) : url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '') }}"
        ajax>
        <div class="container-fluid px-md-5  mt-3" data-testid="edit-post-type-{{ $page['route'] }}">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row)
                    ? 'Edit ' . $page['display_name'] . ' #' . $row['id']
                    : 'Add ' . $page['display_name'],
                'seo_check_function' => $showSeoChecker,
                'submit' => isset($row) ? 'Update' : 'Create',
            ])

            <div class="white-card">
                @if (isset($row))
                    @method('put')
                @endif
              

                @include('darpersocms::cms.components.errors.errors')
                @if (isset($row) && $page['single_record'] !== 1)
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

                @if (count($page_translatable_fields) > 0)
                    @foreach ($languages as $language)
                        <div class="">
                            @if (count($languages) > 1)
                                <label>{{ $language->title }}</label>
                            @endif
                            <div class="{{ count($languages) > 1 ? 'pl-3 ' : '' }}">
                                @foreach ($page_translatable_fields as $field)
                                    @include('darpersocms::cms/post-type/form-fields', [
                                        'locale' => $language->slug,
                                    ])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                @csrf

                <div id="seo-results"></div>

                <div class="form-buttons-container justify-content-end d-flex mt-3">
                    @if (!isset($row))
                        <input type="number" name="published" id="isPublished" value="1" class="d-none">
                        <button type="submit" class="theme-btn sm secondary btn-draft mr-1"
                            data-testid="draft-post-type-{{ $page['route'] }}">Save As Draft</button>
                    @endif

                    <button type="submit" class="theme-btn sm submit btn-publish ml-1"
                        data-testid="submit-post-type-{{ $page['route'] }}">
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

@if ($showSeoChecker)
    @section('scripts')
        <script type="module">
            import {
                checkSEOHealthWithAI
            } from "{{ url('asset?path=js/ai-seo.js') }}";
            document.addEventListener('DOMContentLoaded', async function() {
                await checkSEOHealthWithAI("{{ env('OPENAI_API_KEY') }}");
                $('.seo-check-btn').click(() => {
                    checkSEOHealthWithAI("{{ env('OPENAI_API_KEY') }}", true);
                })
            });
        </script>
    @endsection

@endif
