@php
    $filters = [];

    foreach ($page_fields as $field) {
        if ($field['form_field'] == 'select' || $field['form_field'] == 'select multiple') {
            $filters[] = $field;
        }
    }
    $base_url = config('cms_config.route_path_prefix') . '/' . $page['route'] . '/';
@endphp

@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')

    <div class="container-fluid px-md-5 mt-5  {{ $page->server_side_pagination ? ' server-side-pagination ' : '' }}">

        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action',[
            'title'=>$page['display_name_plural'],
            'can_delete'=> request()->get('admin')['post_types'][$page['route']]['permissions']['delete'] &&   $page['delete'] && count($rows) > 1
        ])


        <div class="white-card">
            @include('darpersocms::cms.post-type._includes.server-side-pagination-filter')
            <div
                class="datatable-container  {{ $page['server_side_pagination'] ? 'table-responsive' : ' mt-4 ' }} {{ count($filters) ? 'has-filters' : '' }}">
                <table
                    class="{{ $page['server_side_pagination'] ? 'table' : 'datatable' }} {{ $page['with_export'] ? '' : 'no-export' }} datatable-table">
                    <thead>
                        <tr>
                            <th>
                                <label class="checkbox-container check-all-checkboxes">
                                    <input type="checkbox">
                                    <div></div>
                                </label>
                            </th>
                            <th>#</th>
                            <th> </th>
                            @foreach ($page_fields as $field)
                                @if ($field['name'] == 'star' || $field['name'] == 'read' || $field['hide_table'] == 1)
                                    @continue
                                @else
                                    <th>
                                        <div>{{ str_replace(['_id', '_'], ['', ' '], $field['name']) }}</div>
                                        @if ($page['server_side_pagination'])
                                            <div
                                                class="{{ $field['form_field'] != 'select multiple' ? 'sort-arrows' : '' }} position-relative d-inline {{ request('sort_by') == $field['name'] ? request('sort_by_direction') : '' }}">
                                            </div>
                                        @endif
                                    </th>
                                @endif
                            @endforeach

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="cf-body">
                        @foreach ($rows as $row)
                            <tr class="{{ $row->read ? ' read ' : ' ' }} clickable-row"
                                data-href="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id']) }}">
                                <td>
                                    <label class="checkbox-container checkbox-delete-container">
                                        <input type="checkbox" value="{{ $row['id'] }}">
                                        <div></div>
                                    </label>
                                </td>

                                <td>
                                    <a
                                        href="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id']) }}">
                                        {{ $row['id'] }}
                                    </a>
                                </td>

                                <td class="">
                                    <div class="isStar text-primary" data-id="{{ $row['id'] }}">
                                        @if ($row->star)
                                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                                        @else
                                            <i class="fa-regular fa-star" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                </td>

                                @foreach ($page_fields as $field)
                                    @if ($field['name'] == 'star' || $field['name'] == 'read' || $field['hide_table'] == 1)
                                        @continue
                                    @elseif ($field['form_field'] == 'image')
                                        <td>
                                            @if ($row[$field['name']])
                                                <img src="{{ Storage::url($row[$field['name']]) }}" class="img-thumbnail">
                                            @endif
                                        </td>
                                    @elseif ($field['form_field'] == 'multiple images' || $field['form_field'] == 'multiple-images')
                                        <td>
                                            @if ($row[$field['name']])
                                                @php
                                                    $files = json_decode($row[$field['name']]);
                                                @endphp
                                                @foreach ($files as $file)
                                                    <img src="{{ Storage::url($file) }}"
                                                        class="img-thumbnail multiple-image">
                                                @endforeach
                                            @endif
                                        </td>
                                    @elseif ($field['form_field'] == 'file')
                                        <td>
                                            @if ($row[$field['name']])
                                                <a href="{{ Storage::url($row[$field['name']]) }}" target="_blank"><i
                                                        class="fa fa-file" aria-hidden="true"></i></a>
                                                <p style="font-size: 0;">{{ Storage::url($row[$field['name']]) }}</p>
                                            @endif
                                        </td>
                                    @elseif ($field['form_field'] == 'textarea')
                                        <td>
                                            <div class="max-lines">{{ $row[$field['name']] }}</div>
                                        </td>
                                    @elseif ($field['form_field'] == 'checkbox')
                                        <td>
                                            @if ($row[$field['name']])
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            @else
                                                <i class="fa fa-times" aria-hidden="true"></i>
                                            @endif
                                        </td>
                                    @else
                                        <td>
                                            {{ $row[$field['name']] }}</td>
                                    @endif
                                @endforeach


                                <td>
                                    <div class="d-flex justify-content-end">
                                        @if ($page['show'] || !request()->get('admin')['admin_role_id'])
                                            @if (request()->get('admin')['post_types'][$page['route']]['permissions']['read'])
                                                <a href="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id']) }}"
                                                    class="btn-action view mr-2"><i class="fa-solid fa-eye"></i></a>
                                            @endif
                                        @endif

                                        @if ($page['delete'] || !request()->get('admin')['admin_role_id'])
                                            @if (request()->get('admin')['post_types'][$page['route']]['permissions']['delete'])
                                                <form class="row-delete d-inline-block " method="post"
                                                    action="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id'] . $appends_to_query) }}"
                                                    onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button class="btn-action  delete ml-2" type="submit"><i
                                                            class="fa-solid fa-trash-can"></i></button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>


                <div class="row  mx-0">
                    <div class="col-lg-12 px-0">
                        <div class="server-pagination-numbers">
                            @php
                                $last_item_in_page = $rows->perPage() * $rows->currentPage();
                                $first_item_in_page = $last_item_in_page - ($rows->perPage() - 1);
                            @endphp
                            Showing {{ $first_item_in_page }} to
                            {{ $last_item_in_page > $rows->total() ? $rows->total() : $last_item_in_page }} of
                            {{ $rows->total() }} entries
                        </div>
                    </div>
                    <div class="col-lg-12 pagination-btns position-relative text-center ">
                        {{ $rows->onEachSide(1)->appends($_GET)->links() }}
                    </div>


                </div>
            </div>

        </div>
    </div>


@endsection



@section('scripts')
    <script>
        $('.isStar').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).find('i').toggleClass('fa-regular')
            $(this).find('i').toggleClass('fa-solid');
            $.ajax({
                url: "{{ url(config('cms_config.route_path_prefix') . '/formMessages/' . $page['route'] . '/star/') }}/" +
                    $(this).attr('data-id'),
                type: "GET",
                success: function(response) {
                    console.log("response ", response)
                },
                error: function(error) {
                    console.log("Error:", error);
                }
            })
        })
    </script>
@endsection
