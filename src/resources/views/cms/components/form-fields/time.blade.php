@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }
    $is24 = isset($format24) ? $format24 : false;
@endphp
<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')
    <div class="time-picker no-selection">
        <div class="change-time-container upper">
            <span>
                <i class="fa-solid fa-chevron-up text-primary"></i>
            </span>
            <span>
                <i class="fa-solid fa-chevron-up text-primary"></i>
            </span>
            @if (!$is24)
                <span>
                    <i class="fa-solid fa-chevron-up text-primary"></i>
                </span>
            @endif
        </div>
        <div class="time-form-container" data-format="{{ $is24 ? '24' : '12' }}">
            <input class="hour" readonly="" value="{{ date('h', strtotime($value)) }}">
            <input class="minutes" readonly="" value="{{ date('i', strtotime($value)) }}">
            @if (!$is24)
                <input class="period" readonly value="{{ date('A', strtotime($value)) }}">
            @endif
            <input type="hidden" name="{{ $input_name }}"
                value="{{ $value ? date('h:i A', strtotime($value)) : '12:00 AM' }}">
        </div>
        <div class="change-time-container lower">
            <span>
                <i class="fa-solid fa-chevron-down text-primary"></i>
            </span>
            <span>
                <i class="fa-solid fa-chevron-down text-primary"></i>
            </span>
            @if (!$is24)
                <span>
                    <i class="fa-solid fa-chevron-down text-primary"></i>
                </span>
            @endif
        </div>
    </div>
    @include('darpersocms::cms.components.form-fields.field-error')
</div>
