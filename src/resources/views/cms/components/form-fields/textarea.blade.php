@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }
@endphp

<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')
    <div class="position-relative">
        <textarea class="custom-form-input" name="{{ $input_name }}" rows="5" {{-- onkeyup="wordCount(this)" --}}>{{ $value }}</textarea>
        @if (!isset($disable_counter) || (isset($disable_counter) && !$disable_counter))
            @include('darpersocms::cms.components.form-fields.character-word-count')
        @endif
    </div>
    @include('darpersocms::cms.components.form-fields.field-error')
</div>
