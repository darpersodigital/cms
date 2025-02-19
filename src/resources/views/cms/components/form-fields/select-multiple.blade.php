@php
    if (!isset($value) || !$value) {
        $value = [];
    }
@endphp

<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')

    <div class="select-multiple-custom-container  ">
        <select class="custom-form-input select-multiple-custom custom-form-input w-100" data-name="{{ $name }}"
            multiple>
            @foreach ($options as $option)
                <option value="{{ $option[$store_column] }}" {!! in_array($option[$store_column], $value) ? 'selected' : '' !!}>
                    {{ $option[$display_column] }}
                </option>
            @endforeach
        </select>

        <div class="selected-options d-none">
            @foreach ($value as $selected_id)
                @foreach ($options as $option)
                    @if ($option->id == $selected_id)
                     <div class="selected-option">
                        <input type="hidden" name="{{ $name }}[]" value="{{ $selected_id }}"
                        class="selected-option-id">
                    <input type="text" name="pos_{{ $name }}[{{ $selected_id }}]" value="">
                     </div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
</div>
