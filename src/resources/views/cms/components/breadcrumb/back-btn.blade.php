@props([
    'url' => null,
    'testID' => null,
])

<a
    href="{{ $url ?? url()->previous() }}"
    {{ $attributes->merge([
        'class' => 'btn-action arrow-btn transition-5 pointer bg-primary d-flex align-items-center justify-content-center'
    ]) }}
    data-testid="header-back-btn-{{ $testID ?? '' }}"
>
    <svg xmlns="http://www.w3.org/2000/svg"
        class="svg-inline--fa fa-chevron-left"
        data-icon="chevron-left"
        data-prefix="fas"
        viewBox="0 0 320 512"
    >
        <path fill="#fff"
            d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z">
        </path>
    </svg>
</a>