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
    $languages = $languages ?? collect();
    $translatable_fields = $translatable_fields ?? ($page_translatable_fields ?? json_decode($page['translatable_fields'] ?? '[]', true) ?? []);
@endphp
@section('dashboard-content')
    <div class="container-fluid px-md-5  mt-3 {{ $page->server_side_pagination ? ' server-side-pagination ' : '' }}">

        @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
            'title' => $page['display_name_plural'],
            'can_add' => $page['add'] && $permissions['add'],
            'can_order' => $page['order_display'] && count($rows) > 1,
            'testID' => $page['route'],
            'can_delete' => $page['delete'] && count($rows) >= 1 && $permissions['delete'],
            'base_url' => $base_url,
        ])

        <div class="white-card" data-testid="list-{{ $page['route'] }}">
            @include('darpersocms::cms.post-type._includes.server-side-pagination-filter')
            <div
                class="datatable-container  {{ $page['server_side_pagination'] ? 'table-responsive' : '  ' }} {{ count($filters) ? 'has-filters' : '' }}">
                <table data-export-name="{{ $page['display_name_plural'] }}"
                    class="{{ $page['server_side_pagination'] ? 'table' : 'datatable' }} {{ $page->with_export ? '' : 'no-export' }} datatable-table post-type-datatable-table">
                    <thead>
                        <tr>
                            <th class="no-export-column">
                                <label class="checkbox-container check-all-checkboxes">
                                    <input type="checkbox">
                                    <div></div>
                                </label>
                            </th>
                            <th>#</th>
                            @foreach ($page_fields as $field)
                                @if (in_array($field['form_field'], ['password', 'password with confirmation']))
                                    @continue
                                @endif

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

                                <th class="{{ $field['hide_table'] == 1 ? 'export-only-column' : '' }}">
                                    <a
                                        @if ($page['server_side_pagination'] && $field['form_field'] !== 'select multiple') href="{{ url($base_url . $appends_to_sort_query) }}" @endif>
                                        {{ str_replace(['_id', '_'], ['', ' '], $field['name']) }}
                                    </a>
                                </th>
                            @endforeach
                            @foreach ($translatable_fields as $field)
                                @if (in_array($field['form_field'], ['password', 'password with confirmation']))
                                    @continue
                                @endif
                                @foreach ($languages as $language)
                                    <th class="export-only-column">
                                        {{ str_replace(['_id', '_'], ['', ' '], $field['name']) }} ({{ $language->slug }})
                                    </th>
                                @endforeach
                            @endforeach
                            <th>Published</th>
                            <th class="no-export-column">Actions</th>
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

                                    @if (in_array($formField, ['password', 'password with confirmation']))
                                        @continue
                                    @endif

                                    <td class="{{ $field['hide_table'] == 1 ? 'export-only-column' : '' }}">
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
                                                    @foreach (json_decode($row[$fieldName] ?? '[]', true) ?? [] as $file)
                                                        @if (getType($file) == 'string')
                                                            <img src="{{ Storage::url($file) }}"
                                                                class="img-thumbnail multiple-image">
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @break

                                            @case('multiple files')
                                                @if ($row[$fieldName])
                                                    @foreach (json_decode($row[$fieldName] ?? '[]', true) ?? [] as $file)
                                                        @if (getType($file) == 'string')
                                                            <a href="{{ Storage::url($file) }}" target="_blank">
                                                                <i class="fa fa-file" aria-hidden="true"></i>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @break

                                              @case('multiple videos')
                                                @if ($row[$fieldName])
                                                    @foreach (json_decode($row[$fieldName] ?? '[]', true) ?? [] as $file)
                                                        @if (getType($file) == 'string')
                                                            <a href="{{ Storage::url($file) }}" target="_blank">
                                                                <i class="fa-solid fa-video" aria-hidden="true"></i>
                                                            </a>
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
                                                @endif
                                            @break

                                             @case('video')
                                                @if (isset($row[$fieldName]) && $row[$fieldName])
                                                    <a href="{{ Storage::url($row[$fieldName]) }}" target="_blank">
                                                        <i class="fa-solid fa-video" aria-hidden="true"></i>
                                                    </a>
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
                                                {{ $field['form_field_configs_2'] === '1'
                                                    ? date('H:i', strtotime($row[$fieldName]))
                                                    : date('h:i A', strtotime($row[$fieldName])) }}
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
                                @foreach ($translatable_fields as $field)
                                    @if (in_array($field['form_field'], ['password', 'password with confirmation']))
                                        @continue
                                    @endif
                                    @foreach ($languages as $language)
                                        @php
                                            $locale = $language->slug;
                                            $translatedRow = method_exists($row, 'translate') ? $row->translate($locale) : null;
                                            $formField = $field['form_field'];
                                            $fieldName = $field['name'];
                                            $rawValue = $translatedRow ? ($translatedRow[$fieldName] ?? null) : null;
                                            $cellValue = '';
                                            $cellIsHtml = false;

                                            if ($formField === 'checkbox') {
                                                $cellValue = $rawValue ? '1' : '0';
                                            } elseif (in_array($formField, ['rich-textbox', 'textarea'])) {
                                                $cellValue = strip_tags((string) ($rawValue ?? ''));
                                            } elseif ($formField === 'multiple images') {
                                                $decoded = is_array($rawValue)
                                                    ? $rawValue
                                                    : (json_decode($rawValue ?? '[]', true) ?? []);
                                                $files = array_values(array_filter(array_map(function ($item) {
                                                    return is_string($item) && $item ? Storage::url($item) : null;
                                                }, $decoded)));
                                                $links = array_map(function ($url) {
                                                    $safeUrl = e($url);
                                                    return '<a href="' . $safeUrl . '" target="_blank">' . $safeUrl . '</a>';
                                                }, $files);
                                                $cellValue = implode('<br>', $links);
                                                $cellIsHtml = true;
                                            } elseif ($formField === 'multiple files') {
                                                $decoded = is_array($rawValue)
                                                    ? $rawValue
                                                    : (json_decode($rawValue ?? '[]', true) ?? []);
                                                $files = array_values(array_filter(array_map(function ($item) {
                                                    return is_string($item) && $item ? Storage::url($item) : null;
                                                }, $decoded)));
                                                $links = array_map(function ($url) {
                                                    $safeUrl = e($url);
                                                    return '<a href="' . $safeUrl . '" target="_blank">' . $safeUrl . '</a>';
                                                }, $files);
                                                $cellValue = implode('<br>', $links);
                                                $cellIsHtml = true;
                                            } elseif (in_array($formField, ['image', 'file','video'])) {
                                                $cellValue = is_string($rawValue) && $rawValue ? Storage::url($rawValue) : '';
                                            } elseif ($formField === 'time') {
                                                $cellValue = !empty($rawValue)
                                                    ? ($field['form_field_configs_2'] === '1'
                                                        ? date('H:i', strtotime($rawValue))
                                                        : date('h:i A', strtotime($rawValue)))
                                                    : '';
                                            } else {
                                                $cellValue = is_scalar($rawValue) ? (string) $rawValue : '';
                                            }
                                        @endphp
                                        <td class="export-only-column">
                                            @if ($cellIsHtml)
                                                {!! $cellValue !!}
                                            @else
                                                {{ $cellValue }}
                                            @endif
                                        </td>
                                    @endforeach
                                @endforeach

                                <td>
                                    <span
                                        class="badge badge-pill {{ $row['published'] ? 'badge-primary' : 'badge-secondary' }}">
                                        {{ $row['published'] ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                @include('darpersocms::cms.post-type._includes.row-actions', [
                                    'can_view' => $page['show'] && $permissions['read'],
                                    'can_edit' => $page['edit'] && $permissions['edit'],
                                    'can_delete' => $page['delete'] && $permissions['delete'],
                                    'testID' => $page['route'],
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



@section('scripts')
    <script>

        $(document).ready(() => {
            $('.datatable-table.post-type-datatable-table').each(function() {
                const table = $(this);

                const params = new URLSearchParams(window.location.search);
                const value = params.get('per_page');

                const exportFileName = () => {
                    const now = new Date();
                    const date =
                        now.getFullYear() + '-' +
                        String(now.getMonth() + 1).padStart(2, '0') + '-' +
                        String(now.getDate()).padStart(2, '0');

                    const time =
                        String(now.getHours()).padStart(2, '0') + '-' +
                        String(now.getMinutes()).padStart(2, '0');

                    const tableName = table.data('export-name') || 'export';

                    return `${tableName}_${date}_${time}`;
                };
                const fileName = exportFileName();
                const normalizeExportCell = function(data, node) {
                    const source = node && node.innerHTML ? node.innerHTML : (data == null ? '' : String(data));
                    if (!source) {
                        return '';
                    }

                    const toAbsoluteUrl = function(url) {
                        if (!url) {
                            return '';
                        }

                        if (/^(https?:)?\/\//i.test(url) || /^(mailto:|tel:|data:)/i.test(url)) {
                            return url;
                        }

                        const normalized = url.startsWith('/') ? url : `/${url}`;
                        return `${window.location.origin}${normalized}`;
                    };

                    const absolutizePathLikeText = function(text) {
                        if (!text || typeof text !== 'string') {
                            return text;
                        }

                        return text
                            .split('\n')
                            .map((line) => line.trim())
                            .filter(Boolean)
                            .map((line) => line.split(',').map((part) => part.trim()).filter(Boolean).map((part) => {
                                if (/^(https?:)?\/\//i.test(part) || /^(mailto:|tel:|data:)/i.test(part)) {
                                    return part;
                                }

                                if (part.includes('/') && !part.includes(' ')) {
                                    return toAbsoluteUrl(part);
                                }

                                return part;
                            }).join(', '))
                            .join('\n');
                    };

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = source;

                    const linkNodes = Array.from(wrapper.querySelectorAll('a[href]'));
                    const linkTexts = linkNodes
                        .map((a) => (a.textContent || '').replace(/\s+/g, ' ').trim())
                        .filter(Boolean);

                    const linkHrefs = linkNodes
                        .map((a) => toAbsoluteUrl((a.getAttribute('href') || '').trim()))
                        .filter(Boolean);

                    const isFileLink = linkNodes.some((a) => {
                        const href = (a.getAttribute('href') || '').toLowerCase();
                        return a.querySelector('i.fa-file') || /\.(pdf|doc|docx|xls|xlsx|csv|zip|rar|txt|jpg|jpeg|png|gif|webp|svg)$/i.test(href);
                    });

                    if (linkHrefs.length && (isFileLink || !linkTexts.length)) {
                        return linkHrefs.length > 1 ? linkHrefs.join('\n') : linkHrefs[0];
                    }

                    if (linkTexts.length) {
                        return linkTexts.join(', ');
                    }

                    if (linkHrefs.length) {
                        return linkHrefs.length > 1 ? linkHrefs.join('\n') : linkHrefs[0];
                    }

                    const imageSrcs = Array.from(wrapper.querySelectorAll('img[src]'))
                        .map((img) => toAbsoluteUrl((img.getAttribute('src') || '').trim()))
                        .filter(Boolean);

                    if (imageSrcs.length) {
                        return imageSrcs.length > 1 ? imageSrcs.join('\n') : imageSrcs[0];
                    }

                    const iconClasses = Array.from(wrapper.querySelectorAll('i[class]'))
                        .flatMap((icon) => Array.from(icon.classList));
                    if (iconClasses.includes('fa-check')) {
                        return '1';
                    }
                    if (iconClasses.includes('fa-times') || iconClasses.includes('fa-xmark')) {
                        return '0';
                    }

                    const plainText = (wrapper.textContent || '')
                        .replace(/\u00a0/g, ' ')
                        .replace(/\s*,\s*/g, ', ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    return absolutizePathLikeText(plainText);
                };

                const isExportableColumn = function(idx, data, node) {
                    if (node && node.classList) {
                        return !node.classList.contains('no-export-column');
                    }

                    const header = table.find('thead th').get(idx);
                    return header ? !header.classList.contains('no-export-column') : true;
                };

                const options = {
                    pageLength: value ? parseInt(value, 10) : 25,
                    searching: !table.hasClass('table'),
                    paging: !table.hasClass('table'),
                    info: !table.hasClass('table'),
                };

                const exportOnlyIndexes = [];
                table.find('thead th.export-only-column').each(function() {
                    exportOnlyIndexes.push($(this).index());
                });

                if (exportOnlyIndexes.length) {
                    options.columnDefs = [{
                        targets: exportOnlyIndexes,
                        visible: false,
                        searchable: false
                    }];
                }

                if (!table.hasClass('no-export')) {
                    options.dom = 'Blfrtip';
                    options.buttons = [{
                            extend: 'csvHtml5',
                            text: 'Export CSV',
                            filename: fileName,
                            exportOptions: {
                                columns: isExportableColumn,
                                stripNewlines: false,
                                format: {
                                    body: function(data, row, column, node) {
                                        return normalizeExportCell(data, node);
                                    }
                                }
                            }
                        },
                    ];
                }
                table.DataTable(options);
            });
        });
    </script>
@endsection
