@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }

    $currentValue = $value;

    // Normalize $value so it is ALWAYS an array
    if (empty($value)) {
        $value = [];
    } elseif (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = is_array($decoded) ? $decoded : [];
    } elseif (is_object($value)) {
        $value = (array) $value;
    } elseif (!is_array($value)) {
        $value = [];
    }

    // Normalize current value for hidden input
    if (is_array($currentValue) || is_object($currentValue)) {
        $currentValue = json_encode($currentValue);
    } elseif (!is_string($currentValue)) {
        $currentValue = '';
    }

    $alt_name = $name . '_alt[]';
    if ($locale) {
        $alt_name = $locale."[". $name . '_alt'."][]";
    }



@endphp
<div class="form-input-container multiple-images-container">
    @include('darpersocms::cms.components/form-fields/label')

    <div class="images-preview  pb-3 pt-2" {!! count($value) ? '' : 'style="display: none;"' !!}>
        <div class="row sortable-file-input ">
            @foreach ($value as $img)
                @php
                    $image = isset($img['file']) ? $img['file'] : null;
                    $alt = !empty($img['alt']) ? $img['alt'] : '';
                @endphp
                @if (is_string($image) && $image !== '')
                    <div class="col-lg-6 single-multiple-file mb-3 with-alt" data-image="{{ $image }}"  data-alt="{{ $alt }}">
                        <img class="img-thumbnail" src="{{ Storage::url($image) }}">
                        <div class="bg-danger text-white delete-btn" data-image="{{ $image }}"  data-alt="{{ $alt }}"
                            data-testid="remove-current-image-{{ $loop->index }}-{{ $locale ? $locale . '-' : '' }}{{ $testID ?? '' }}">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </div>
                        <input type="hidden" name="{{ $input_name }}[]" value="{{ $image }}">
                      <div class="alt-input">
                          @include('darpersocms::cms.components/form-fields/TextInput', [
                            'label' => null,
                            'maxlength' => '150',
                            'value' => $alt,
                            'locale'=>null,
                            'name' => $alt_name ,
                            'placeholder' => 'SEO image alt',
                        ])
                      </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    <input type="hidden" class="custom-form-input current-multiple-images-value" name="current_{{ $input_name }}"
        value="{{ $currentValue }}">
    <label class="custom-file-wrapper placeholder" data-text="Upload images">
        <input type="file" class="custom-form-input" multiple name="{{ $input_name }}[]" value=""
            data-testid="images-input-input-{{ $locale ? $locale . '-' : '' }}{{ $testID ?? '' }}">
    </label>

    @include('darpersocms::cms.components.form-fields.field-error')
</div>
