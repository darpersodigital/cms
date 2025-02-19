@extends('/darpersocms::layouts/dashboard')

@php
    $sort_by = $page['sort_by'] ?: $page['order_display'];
@endphp
@section('dashboard-content')

    <div class="container-fluid px-md-5 mt-5 ">
        @include('darpersocms::cms.components.breadcrumb.breadcrumb-action', [
            'title' => 'Order ' . $page['display_name_plural'],
        ])
        <div class="white-card">
            <div class="row">
                <div class="col-12">
                    @if (count($rows))
                        <form method="post">
                            @method('put')
                            @csrf
                            <ul class="sortable list-inline m-0">
                                @foreach ($rows as $row)
                                    <li class="sortable-row ">
                                        <input type="hidden" name="pos[{{ $row->id }}]" value="{{ $row->pos }}">

                                        @php
                                            $order_field = null;
                                            foreach ($page_fields as $field) {
                                                if ($field['name'] == $sort_by) {
                                                    $order_field = $field;
                                                    break;
                                                }
                                            }

                                            if (!$order_field) {
                                                foreach ($page_translatable_fields as $field) {
                                                    if ($field['name'] == $sort_by) {
                                                        $order_field = $field;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if ($order_field)
                                            @if ($order_field['name'] == $sort_by)
                                                @if ($order_field['form_field'] == 'image')
                                                    <img src="{{ Storage::url($row[$sort_by]) }}">
                                                @elseif ($order_field['form_field'] == 'select')
                                                    {{ $row[str_replace('_id', '', $order_field['name'])][$order_field['form_field_configs_2']] }}
                                                @elseif ($order_field['form_field'] == 'select multiple')
                                                    @foreach ($row[$order_field['form_field_configs_1']] as $i => $second_table_row)
                                                        @if ($i)
                                                            ,
                                                        @endif
                                                        {{ $second_table_row[$order_field['form_field_configs_2']] }}
                                                    @endforeach
                                                @else
                                                    {!! $row[$sort_by] !!}
                                                @endif
                                            @endif
                                        @else
                                            {!! $row[$sort_by] !!}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            <div class="text-right">
                                <button class="btn btn-sm btn-primary">Update Order</button>
                            </div>
                        </form>
                    @else
                        <h5 class="text-center m-0 py-4">No record found for sorting</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

