@extends('darpersocms::layouts/dashboard')

@php
    $base_url = config('cms_config.route_path_prefix') . '/' . $page['route'];
    $permissions = request()->get('admin')['post_types'][$page['route']]['permissions'];
    $filters = [];
    foreach ($page_fields as $field) {
        if ($field['form_field'] == 'select' || $field['form_field'] == 'select multiple') {
            $filters[] = $field;
        }
    }
@endphp
@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5  {{ $page->server_side_pagination ? ' server-side-pagination ' : '' }}">
        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action',[
            'title'=>$page['display_name_plural'],
            'can_add'=> $page['add'] && $permissions['add'] ,
            'can_order'=> $page['order_display'] && count($rows) > 1  ,
            'can_delete'=>$page['delete'] && count($rows) > 1 && $permissions['delete'],
            'base_url' =>$base_url
        ])
        <div class="white-card">
            @include('darpersocms::cms.post-type._includes.server-side-pagination-filter')
            <div
                class="datatable-container  {{ $page['server_side_pagination'] ? 'table-responsive' : '  ' }} {{ count($filters) ? 'has-filters' : '' }}">
                <table
                    class="{{ $page['server_side_pagination'] ? 'table' : 'datatable' }} {{ $page->with_export ? '' : 'no-export' }} datatable-table">
                    <thead>
                        <tr>
                            <th>
                                <label class="checkbox-container check-all-checkboxes">
                                    <input type="checkbox">
                                    <div></div>
                                </label>
                            </th>
                            <th>#</th>
                            @foreach ($page_fields as $field)
                                @if (in_array($field['form_field'], ['password', 'password with confirmation']) || $field['hide_table'] == 1)
                                    @continue
                                @else
                                    @php
                                        $queryParams = [
                                            'per_page' => request('per_page'),
                                            'search' => request('search'),
                                            'sort_by' => $field['name'],
                                            'sort_by_direction' =>
                                                request('sort_by') === $field['name']
                                                    ? (request('sort_by_direction') === 'asc'
                                                        ? 'desc'
                                                        : 'asc')
                                                    : 'asc',
                                        ];
                                        $queryParams = array_filter($queryParams);
                                        $appends_to_sort_query = '?' . http_build_query($queryParams);
                                    @endphp

                                    <th>
                                        <a
                                            @if ($page['server_side_pagination'] && $field['form_field'] !== 'select multiple') href="{{ url($base_url . $appends_to_sort_query) }}" @endif>
                                            {{ str_replace(['_id', '_'], ['', ' '], $field['name']) }}
                                        </a>

                                    </th>
                                @endif
                            @endforeach
                            <th>Published</th>
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

                                <td> {{ $row->id }} </td>

                                @foreach ($page_fields as $field)
                                    @php
                                        $formField = $field['form_field'];
                                        $fieldName = $field['name'];
                                    @endphp

                                    @if (in_array($formField, ['password', 'password with confirmation']) || $field['hide_table'] == 1)
                                        @continue
                                    @endif

                                    <td>
                                        @switch($formField)
                                            @case('select')
                                                @if ($row[str_replace('_id', '', $fieldName)])
                                                    <a
                                                        href="{{ url(config('cms_config.route_path_prefix') . '/' . str_replace('_', '-', $field['form_field_configs_1']) . '/' . $row[$fieldName]) }}">
                                                        {{ strip_tags($row[str_replace('_id', '', $fieldName)][$field['form_field_configs_2']]) }}
                                                    </a>
                                                @endif
                                            @break

                                            @case('select multiple')
                                                @foreach ($row[str_replace('_id', '', $fieldName)] as $i => $pivot)
                                                    {{ $i ? ', ' : '' }}
                                                    <a
                                                        href="{{ url(config('cms_config.route_path_prefix') . '/' . str_replace('_', '-', $field['form_field_configs_1']) . '/' . $pivot->id) }}">
                                                        {{ $pivot[$field['form_field_configs_2']] }}
                                                    </a>
                                                @endforeach
                                            @break

                                            @case('multiple images')
                                                @if ($row[$fieldName])
                                                    @foreach (json_decode($row[$fieldName]) as $file)
                                                      @if (getType($file)=='string') 
                                                      <img src="{{ Storage::url($file) }}" class="img-thumbnail multiple-image">
                                                      @endif
                                                    @endforeach
                                                @endif
                                            @break

                                            @case('image')
                                                @if ($row[$fieldName])
                                                    <img src="{{ Storage::url($row[$fieldName]) }}" class="img-thumbnail">
                                                @endif
                                            @break

                                            @case('file')
                                                @if (isset($row[$fieldName]) && $row[$fieldName])
                                                    <a href="{{ Storage::url($row[$fieldName]) }}" target="_blank">
                                                        <i class="fa fa-file" aria-hidden="true"></i>
                                                    </a>
                                                    {{-- <p class="d-none">{{ Storage::url($row[$fieldName]) }}</p> --}}
                                                @endif
                                            @break

                                            @case('rich-textbox')
                                                <div class="max-lines">{{ strip_tags($row[$fieldName]) }}</div>
                                            @break

                                            @case('checkbox')
                                                <i class="fa {{ $row[$fieldName] ? 'fa-check' : 'fa-times' }}"
                                                    aria-hidden="true"></i>
                                            @break

                                            @case('time')
                                                {{ date('h:i A', strtotime($row[$fieldName])) }}
                                            @break

                                            @case('textarea')
                                                <div class="max-lines">{{ $row[$fieldName] }}</div>
                                            @break

                                            @case('color picker')
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded mr-1"
                                                        style="width: 22px; height: 22px; background-color: {{ $row[$fieldName] }}">
                                                    </div>
                                                    <span>{{ $row[$fieldName] }}</span>
                                                </div>
                                            @break

                                            @default
                                                {{ $row[$fieldName] }}
                                        @endswitch
                                    </td>
                                @endforeach

                                <td>
                                    <span
                                        class="badge badge-pill {{ $row['published'] ? 'badge-primary' : 'badge-secondary' }}">
                                        {{ $row['published'] ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                @include('darpersocms::cms.post-type._includes.row-actions',[
                                    'can_view'=>$page['show'] && $permissions['read'],
                                    'can_edit' =>$page['edit'] && $permissions['edit'],
                                    'can_delete'=>$page['delete'] && $permissions['delete']
                                ])
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @include('darpersocms::cms.post-type._includes.pagination')
            </div>
        </div>
    </div>

    @include('darpersocms::cms.post-type._includes.relationship-filter-modal')
@endsection
