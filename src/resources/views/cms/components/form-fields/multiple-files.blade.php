@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }
    $currentValue = $value;
    if (!isset($value) || !$value) {
        $value = [];
    } else {
        $value = json_decode($value);
    }
@endphp

<div class="form-input-container multiple-images-container">
    @include('darpersocms::cms.components/form-fields/label')

    <input type="hidden" class="custom-form-input current-multiple-images-value" name="current_{{ $input_name }}[]"
        value="{{ $currentValue }}">
    @if (count($value) >=1)
        <div class="images-preview  py-2" {!! count($value) ? '' : 'style="display: none;"' !!}>
            <div class="row images-sortable">
                @foreach ($value as $file)
                    @if (getType($file) == 'string')
                        <div class="col-auto single-multiple-image mb-3" data-image="{!! $file !!}">
                            {{-- <img class="img-thumbnail" src="{{ Storage::url($file) }}"> --}}
                            <a href="{{ Storage::url($file) }}" target="_blank" class="theme-btn sm"><i
                                    class="fa fa-file" aria-hidden="true"></i><span class="ml-2">View File</span></a>
                            <div class="bg-danger text-white delete-btn" data-image="{{ $file }}" data-testid="remove-current-file-{{$loop->index }}-{{$locale ? $locale .'-' :""}}{{$testID ?? ""}}">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </div>
                            <input type="hidden" name="{{ $input_name }}[]" value="{{ $file }}">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    <label class="custom-file-wrapper placeholder" data-text="Upload Files">
        <input type="file" class="custom-form-input" multiple name="{{ $input_name }}[]" value="" data-testid="files-input-input-{{$locale ? $locale .'-' :""}}{{$testID ?? ""}}">
    </label>

    @include('darpersocms::cms.components.form-fields.field-error')

</div>
