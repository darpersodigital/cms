@php
    // $items => [['label' => 'Sports', 'url' => '/admin/categories/3'], ...]
    // A null url means the admin is not allowed to open that record, so it stays plain text.
    $parts = [];
    foreach ($items ?? [] as $item) {
        $text = e(strip_tags((string) ($item['label'] ?? '')));
        $parts[] = $item['url']
            ? '<a href="' . e($item['url']) . '" data-testid="relation-link-' . e($testID ?? '') . '">' . $text . '</a>'
            : $text;
    }
@endphp

<div class="py-2">
    <label><b>{{ $label }}</b></label>
    <p class="" data-testID="text-{{ $testID ?? '' }}">{!! implode(', ', $parts) !!}</p>
</div>
