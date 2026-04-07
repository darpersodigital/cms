<div class="input-wrapper mt-3  text-left form-input-container {{$style ??""}}" data-testid="select-container-{{ $testID ?? '' }}">
    @if (isset($label))
        @include('darpersocms::cms.components/form-fields/label')
    @endif
    <select class="custom-form-input custom-select w-100" name="{{ $name }}">
        <option></option>
        @foreach ($options as $option)
            <option value="{{ $option[$store_column] }}" {{ $value == $option[$store_column] ? 'selected' : '' }}>
                {{ strip_tags($option[$display_column]) }}</option>
        @endforeach
    </select>

    @include('darpersocms::cms.components.form-fields.field-error')

</div>
