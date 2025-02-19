<div class="custom-popup filter-popup ">
    <div class="row justify-content-center align-items-center h-100">
        <div class="col-lg-6 ">
            <div class="white-card filter-select-items">
                <div class="d-flex mb-2 justify-content-between">
                    <h6>Filter :</h6>
                    <h5><i class="fa fa-times close-popup"></i></h5>
                </div>
                <form>
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    <div class="row">
                        @foreach ($filters as $i => $filter)
                            <div class="col-lg-6">
                                <input type="hidden" name="search_by_relationships[{{ $i }}][constraint]"
                                    value="{{ $filter['form_field'] == 'select multiple' ? 'whereHas' : 'whereIn' }}">
                                <input type="hidden" name="search_by_relationships[{{ $i }}][table_name]"
                                    value="{{ $filter['form_field_configs_1'] }}">
                                <input type="hidden" name="search_by_relationships[{{ $i }}][field_name]"
                                    value="{{ $filter['name'] }}">

                                @include('darpersocms::cms.components/form-fields/select-multiple', [
                                    'label' => ucwords(
                                        str_replace('_', ' ', $filter['form_field_configs_1'])),
                                    'name' => 'search_by_relationships[' . $i . '][value][]',
                                    'options' => $extra_variables[$filter['form_field_configs_1']],
                                    'store_column' => 'id',
                                    'display_column' => $filter['form_field_configs_2'],
                                
                                    // 'value' => isset($row) ? ($locale ? $row->translate($locale)[str_replace(['_id'], [''], $field['name'])]->pluck('id')->toArray() : $row[str_replace(['_id'], [''], $field['name'])])->pluck('id')->toArray() : '',
                                
                                    'value' =>
                                        isset(request('search_by_relationships')[$i]['value']) &&
                                        request('search_by_relationships')[$i]['value']
                                            ? ''
                                            : '',
                                    'required' => false,
                                    'description' => '',
                                    'locale' => 'en',
                                ])
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ url($base_url) }}" class="btn btn-secondary btn-sm mr-2">Clear</a>
                        <button class="btn btn-primary btn-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>