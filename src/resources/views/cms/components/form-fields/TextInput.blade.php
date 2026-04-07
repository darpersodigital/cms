@php
    $input_name = $name;
    if (isset($locale) && $locale != null) {
        $input_name = $locale . '[' . $name . ']';
    }
    $inputId = $testID ?? trim((string) ($placeholder ?? $name));
    $isPassword = ($type ?? 'text') === 'password';

@endphp

<div class="form-input-container input-wrapper mt-3 {{ !empty($icon) ? 'with-icon' : '' }} text-left {{ $styles ?? '' }} "
    data-testid="textInput-{{ $testID ?? '' }}">
    @if (($type ?? 'text') != 'checkbox' && !empty($label))
        @include('darpersocms::cms.components.form-fields.label', [
            'label' => $label,
            'required' => $required ?? false,
        ])
    @endif

    <div class="position-relative" data-testid="textInputWrapper-{{ $testID ?? '' }}">
        @if (($type ?? 'text') == 'checkbox')
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="{{ $inputId }}" name="{{ $input_name }}"
                    value="1" data-testid="{{ $testID ?? '' }}" {{ !empty($value) ? 'checked' : '' }}
                    {!! isset($slug_origin) ? 'data-slug-origin="' . $slug_origin . '"' : '' !!}
                           
                    >

                @if (!empty($label))
                    <label class="custom-control-label" for="{{ $inputId }}"
                        data-testid="label-{{ $testID ?? '' }}">
                        {{ $label }}
                    </label>
                @endif
            </div>
        @else
            <div>
                <input class="custom-form-input" name="{{ $input_name }}" type="{{ $type ?? 'text' }}"
                   {!! isset($type) && $type=='number'? 'step="any"' :"" !!}
                    id="{{ $inputId }}" value="{{ old($input_name, $value ?? '') }}"
                    placeholder="{{ $placeholder ?? '' }}" data-testid="{{ $testID ?? '' }}" {!! isset($slug_origin) ? 'data-slug-origin="' . $slug_origin . '"' : '' !!}>
            </div>
        @endif

        @if ($isPassword)
            <div class="password-eye pointer " onclick="togglePasswordVisibility('{{ $inputId }}', this)">
                <i class="fa-solid fa-eye-slash"></i>
            </div>
        @endif

        @if (!empty($icon))
            <div class="floating-icon text-primary">
                <i class="{{ $icon }}"></i>
            </div>
        @endif



        @if (!empty($ChildComponent))
            {!! $ChildComponent !!}
        @endif
    </div>
    @include('darpersocms::cms.components.form-fields.field-error')
</div>




@if ($isPassword)
    <script>
        function togglePasswordVisibility(inputId, triggerElement) {
            const input = document.getElementById(inputId);
            const icon = triggerElement.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
@endif
