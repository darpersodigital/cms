@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }
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
            <span>
                <i class="fa-solid fa-chevron-up text-primary"></i>
            </span>
        </div>
        <div class="time-form-container">
            <input class="hour" readonly="" value="{{ date('h', strtotime($value)) }}">
            <input class="minutes" readonly="" value="{{ date('i', strtotime($value)) }}">
            <input class="period" readonly="" value="{{ date('A', strtotime($value)) }}">
            <input type="hidden" name="{{ $input_name }}" value="{{ $value ? date('h:i A', strtotime($value)) : '12:00 AM' }}">
        </div>
        <div class="change-time-container lower">
            <span>
               <i class="fa-solid fa-chevron-down text-primary"></i>
            </span>
            <span>
               <i class="fa-solid fa-chevron-down text-primary"></i>
            </span>
            <span>
               <i class="fa-solid fa-chevron-down text-primary"></i>
            </span>
        </div>
    </div>
</div>