@php
    $value = isset($row)
        ? ($locale
            ? ($row->translate($locale)
                ? $row->translate($locale)[$field['name']]
                : null)
            : $row[$field['name']])
        : null;

    $label = ucwords(str_replace(['_id', '_'], ['', ' '], $field['name']));
    $isRequired = $field['nullable'] ? false : true;
    $description = $field['description'] ?? '';

    if ($field['form_field'] == 'password' || $field['form_field'] == 'password with confirmation') {
        $value = '';
    }

    if (!in_array($field['form_field'], ['multiple images', 'multiple images with alt', 'image with alt', 'multiple files']) && !$value) {
        if ($locale) {
            $value = old($locale . '.' . $field['name']);
        } else {
            $value = old($field['name']);
        }
    }

    $fieldAttributes = [
        'label' => $label,
        'type' => 'text',
        'name' => $field['name'],
        'testID' => $field['name'],
        'value' => $value,
        'required' => $isRequired,
        'description' => $description,
        'locale' => $locale,
    ];

    // Set dynamic path based on form field type
    $fieldComponent = match ($field['form_field']) {
        'textarea',
        'rich-textbox',
        'file',
        'video',
        'image',
        'image with alt',
        'multiple images',
        'multiple images with alt',
        'multiple files',
        'multiple videos',
        'date',
        'time',
        'checkbox',
        'password with confirmation'
            => 'cms.components/form-fields/' . str_replace(' ', '-', $field['form_field']),
        default => 'cms.components/form-fields/TextInput',
    };

    // Special handling for select fields
    if (in_array($field['form_field'], ['select', 'select multiple'])) {
        $fieldAttributes['options'] = $extra_variables[$field['form_field_configs_1']];
        $fieldAttributes['store_column'] = 'id';
        $fieldAttributes['display_column'] = $field['form_field_configs_2'];

        if ($field['form_field'] === 'select multiple') {
            $fieldAttributes['value'] = isset($row)
                ? ($locale
                    ? $row->translate($locale)[str_replace('_id', '', $field['name'])]->pluck('id')->toArray()
                    : $row[str_replace('_id', '', $field['name'])]->pluck('id')->toArray())
                : '';
        }
        $fieldComponent = 'cms/components/form-fields/' . str_replace(' ', '-', $field['form_field']);
    }

    // Special handling for slug field
    if (
        $field['form_field'] === 'slug' &&
        (!isset($row) || isset($locale) || (isset($row) && $field['form_field_configs_1']))
    ) {
        $fieldAttributes['slug_origin'] = $field['form_field_configs_1'];
        $fieldAttributes['is_slug'] = true;
        $fieldAttributes['readonly'] = $field['form_field_configs_2'] == '0';
        $fieldComponent = 'cms/components/form-fields/slug';
    }

    // Special handling for password and color picker
    if (in_array($field['form_field'], ['password', 'color picker', 'number'])) {
        if ($field['form_field'] == 'password') {
            $fieldAttributes['type'] = 'password';
        } elseif ($field['form_field'] == 'color picker') {
            $fieldAttributes['type'] = 'color';
        } elseif ($field['form_field'] == 'number') {
            $fieldAttributes['type'] = 'number';
        }
    }

    if ($field['form_field'] === 'time') {
        $fieldAttributes['format24'] = $field['form_field_configs_2'] === '1';
    }

    // Special handling for checkbox
    if ($field['form_field'] === 'checkbox') {
        $fieldAttributes['checked'] = $value;
        unset($fieldAttributes['value']);
    }

    if (isset($row) && !$field['can_update']) {
        $fieldComponent = null;
        $fieldAttributes = null;
    }

    if (!isset($row) && !$field['can_create']) {
        $fieldComponent = null;
        $fieldAttributes = null;
    }
@endphp

@if ($field['name'] == 'seo_robots')
    <div class="mb-3 seo-robots-wrapper ">
        <div class="d-none ">
            @php
                $fieldAttributes['value'] = isset($fieldAttributes['value']) ? $fieldAttributes['value']:"index, follow"
            @endphp
            @include('darpersocms::' . $fieldComponent, $fieldAttributes)
        </div>

      @php
        $seo_robots_value = isset($row)  ? ($locale
            ? (isset($row->translate($locale)['seo_robots']) && $row->translate($locale)['seo_robots'] != ''
                ? $row->translate($locale)['seo_robots']
                : '')
            : $row['seo_robots'])
        : '';
        $unavailable_after_date = null;

        if (preg_match('/unavailable_after: (\d{4}-\d{2}-\d{2})/', $seo_robots_value, $matches)) {
        // If a match is found, extract the date
        $unavailable_after_date = $matches[1];
        }
      @endphp
        @include('darpersocms::cms/components/form-fields/select-multiple', [
            'name' => $locale . '[select_seo_robots]',
            'testID'=>$locale."-select-seo-robots",
                'required'=>$fieldAttributes['required'],
            'display_column' => 'label',
            'store_column' => 'value',
            'options' => [
                ['value' => 'index', 'label' => 'Index'],
                ['value' => 'follow', 'label' => 'Follow'],
                ['value' => 'noindex', 'label' => 'Noindex'],
                ['value' => 'nofollow', 'label' => 'Nofollow'],
                ['value' => 'noarchive', 'label' => 'Noarchive'],
                ['value' => 'nosnippet', 'label' => 'Nosnippet'],
                ['value' => 'unavailable_after', 'label' => 'Unavailable After'],
            ],
           'value' => isset($row)
    ? ($locale
        ? (isset($row->translate($locale)['seo_robots']) &&
            $row->translate($locale)['seo_robots'] != ''
            ? (strpos($row->translate($locale)['seo_robots'], 'unavailable_after') !== false
                ? array_merge(explode(', ', $row->translate($locale)['seo_robots']), ['unavailable_after'])
                : explode(', ', $row->translate($locale)['seo_robots'])
            )
            : ['index', 'follow'])
        : (strpos($row['seo_robots'], 'unavailable_after') !== false
            ? array_merge(explode(', ', $row['seo_robots']), ['unavailable_after'])
            : explode(', ', $row['seo_robots'])
        )
    )
    : ['index', 'follow'],
        ])

        <div class="mt-3 seo-robots-unavailable-after-container pos-z">
            @include('darpersocms::cms/components/form-fields/date', [
                'label' => 'Seo Robots Unavailable After',
                'required'=>true,
                'name' => 'unavailable_after',
                'value' => isset( $unavailable_after_date)
                    ? str_replace(' ', '', $unavailable_after_date)  
                    : '',
            ])
        </div>
    </div>
@elseif (isset($fieldComponent) && isset($fieldAttributes))
    <div class="mb-3">
        @include('darpersocms::' . $fieldComponent, $fieldAttributes)
    </div>
@endif