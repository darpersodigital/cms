<div class="py-2">
    <label><b>{{ $label }}</b></label>
    <p class="mb-0" style="white-space: pre-wrap; overflow-wrap: break-word;"
        data-testID="text-{{ $testID ?? '' }}">{{ strip_tags($value) }}</p>
</div>
