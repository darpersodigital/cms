@php
    $error = $error ?? $errors->first($name);
    $hasError = !empty($error);
    $errorMessage = is_array($error) ? $error['message'] ?? '' : (is_object($error) ? $error->message ?? '' : $error);
@endphp
@if ($hasError)
    <span data-testid="field-error-{{ $testID ?? '' }}" class="text-danger text-error">
        {{ $errorMessage }}
    </span>
@endif
