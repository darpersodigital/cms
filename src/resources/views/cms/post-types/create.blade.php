@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post"
        action="{{ isset($post_type) ? url(config('cms_config.route_path_prefix') . '/post-types/' . $post_type['id']) : url(config('cms_config.route_path_prefix') . '/post-types') }}"
        ajax>
        @csrf

        <div class="container-fluid px-md-5  mt-3">
            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($post_type) ? 'Edit CMS page #' . $post_type['id'] : 'Add CMS page',
                'submit' => isset($post_type) ? 'Update' : 'Create',
                'testID' => 'post-type',
            ])

            <div class="white-card">
                @if (isset($post_type))
                    @method('put')
                @endif
                @include('darpersocms::cms.components.errors.errors')
                <div class="row mb-2">
                    @php
                        $fields = [
                            ['label' => 'Database table', 'name' => 'database_table', 'required' => true],
                            ['label' => 'Model', 'name' => 'model_name', 'required' => true],
                            ['label' => 'Display name', 'name' => 'display_name', 'required' => true],
                            ['label' => 'Display name plural', 'name' => 'display_name_plural', 'required' => true],
                            ['label' => 'Icon', 'name' => 'icon', 'required' => false],
                            ['label' => '', 'name' => '', 'required' => false],
                        ];
                        $fields2 = [
                            ['label' => 'Sort By', 'name' => 'sort_by', 'required' => false],
                            ['label' => 'Order display column', 'name' => 'order_display', 'required' => false],
                        ];
                    @endphp

                    @foreach ($fields as $field)
                        <div class="col-lg-4 mb-3">
                            @if ($field['label'] != '')
                                @include('darpersocms::cms.components/form-fields/TextInput', [
                                    'label' => $field['label'],
                                    'name' => $field['name'],
                                    'type' => 'text',
                                    'value' => $post_type[$field['name']] ?? '',
                                    'error' => $errors->first($field['name']),
                                    'locale' => null,
                                    'description' => '',
                                    'required' => $field['required'],
                                ])
                            @endif
                        </div>
                    @endforeach

                    <div class="col-lg-4 mb-3">
                        @foreach ($fields2 as $field)
                            <div class="mb-3">
                                @include('darpersocms::cms.components/form-fields/TextInput', [
                                    'label' => $field['label'],
                                    'name' => $field['name'],
                                    'type' => 'text',
                                    'value' => $post_type[$field['name']] ?? '',
                                    'error' => $errors->first($field['name']),
                                    'locale' => null,
                                    'description' => '',
                                    'required' => $field['required'],
                                ])
                            </div>
                        @endforeach
                        @include('darpersocms::cms.components.form-fields.label', [
                            'label' => 'Sort By Direction',
                        ])
                        <select name="sort_by_direction" class="w-100">
                            @foreach (['asc', 'desc'] as $direction)
                                <option value="{{ $direction }}"
                                    {{ isset($post_type) && $post_type['sort_by_direction'] == $direction ? 'selected' : '' }}>
                                    {{ $direction }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @php
                        $checkboxGroups = [
                            [
                                'columns' => 'col-lg-4 post-type-checkboxes',
                                'checkboxes' => [
                                    ['label' => 'With Add', 'name' => 'add'],
                                    ['label' => 'With Edit', 'name' => 'edit'],
                                    ['label' => 'With Delete', 'name' => 'delete'],
                                    ['label' => 'With Show', 'name' => 'show'],
                                    ['label' => 'With Export', 'name' => 'with_export'],
                                ],
                            ],
                            [
                                'columns' => 'col-lg-4 post-type-checkboxes',
                                'checkboxes' => [
                                    ['label' => 'Server side paginate', 'name' => 'server_side_pagination'],

                                    ['label' => 'Hide From Menu', 'name' => 'hidden'],
                                    ['label' => 'Single record page', 'name' => 'single_record'],
                                    ['label' => 'Is Form', 'name' => 'is_form'],
                                    ['label' => 'Has Sitemap', 'name' => 'has_sitemap'],
                                    ['label' => 'Show Dashboard', 'name' => 'show_dashboard'],
                                    ['label' => 'Custom CRUD', 'name' => 'custom_crud'],
                                ],
                            ],
                        ];

                        if (!isset($translatable_fields['seo_title'])) {
                            $checkboxGroups[1]['checkboxes'][] = [
                                'label' => 'Generate SEO Fields',
                                'name' => 'with_seo',
                                'checked' => false,
                            ];
                        }
                    @endphp

                    @foreach ($checkboxGroups as $group)
                        <div class="{{ $group['columns'] }}">
                            @foreach ($group['checkboxes'] as $checkbox)
                                @include('darpersocms::cms/components/form-fields/checkbox', [
                                    'label' => $checkbox['label'],
                                    'name' => $checkbox['name'],
                                    'inline_label' => true,
                                    'checked' => $checkbox['checked'] ?? ($post_type[$checkbox['name']] ?? ''),
                                    'locale' => null,
                                ])
                            @endforeach
                        </div>
                    @endforeach

                </div>

            </div>

            <div class="white-card">
                <div class="d-flex justify-content-between">
                    <h4>Fields</h4>
                    <div class="btn-action lg add btn-add" data-testid="add-field-row"><i class="fa-solid fa-plus"></i>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="fields table">
                        <thead>
                            <tr>
                                <th class="text-center">NAME <span class="text-danger">*</span></th>
                                <th class="text-center">Migration Type <span class="text-danger">*</span></th>
                                <th class="text-center">Form Field <span class="text-danger">*</span></th>
                                <th class="text-center">Description</th>
                                <th class="text-center">HideTbl<span class="text-danger">*</span></th>
                                <th class="text-center">C<span class="text-danger">*</span></th>
                                <th class="text-center">R<span class="text-danger">*</span></th>
                                <th class="text-center">U<span class="text-danger">*</span></th>
                                <th class="text-center">Nullable<span class="text-danger">*</span></th>
                                <th class="text-center">Unique<span class="text-danger">*</span></th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody class="sortable">
                            @if (isset($post_type))
                                @php
                                    $fields = json_decode($post_type['fields'], true);
                                @endphp
                                @foreach ($fields as $field_key => $field)
                                    @include('darpersocms::cms/post-types/post-types-field-row', [
                                        'field_type' => '',
                                    ])
                                @endforeach
                            @else
                                @include('darpersocms::cms/post-types/post-types-field-row', [
                                    'field_type' => '',
                                    'post_type' => null,
                                ])
                            @endif
                            @include('darpersocms::cms/post-types/post-types-field-row', [
                                'field_type' => '',
                                'post_type' => null,
                            ])
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="white-card">
                <div class="d-flex justify-content-between">
                    <h4>Translatable Fields</h4>
                    <div class="btn-action lg add  btn-add" data-testid="add-field-tr-row"><i class="fa-solid fa-plus"></i>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="fields table translatable" data-type="translatable">
                        <thead>
                            <tr>
                                <th class="text-center">NAME <span class="text-danger">*</span></th>
                                <th class="text-center">Migration Type <span class="text-danger">*</span></th>
                                <th class="text-center">Form Field <span class="text-danger">*</span></th>
                                <th class="text-center">Description</th>
                                <th class="text-center">HideTbl</th>
                                <th class="text-center">C</th>
                                <th class="text-center">R</th>
                                <th class="text-center">U</th>
                                <th class="text-center">Nullable <span class="text-danger">*</span></th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody class="sortable">
                            @if (isset($post_type))
                                @php
                                    $translatable_fields = json_decode($post_type['translatable_fields'], true);
                                @endphp
                                @if (isset($translatable_fields) && count($translatable_fields) > 0)
                                    @foreach ($translatable_fields as $field_key => $field)
                                        @include('darpersocms::cms/post-types/post-types-field-row', [
                                            'field_type' => 'translatable',
                                        ])
                                    @endforeach
                                @else
                                @endif
                            @endif
                            @include('darpersocms::cms/post-types/post-types-field-row', [
                                'field_type' => 'translatable',
                                'post_type' => null,
                            ])

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        var HTMLField = $('[name="name[]"]').closest('.field').last().html();

        var translateableHTMLField = $('[name="translatable_name[]"]').closest('.field').last().html();

        $('input[name="database_table"]').on("keyup", function() {
            var v = $(this).val();
            $('input[name="display_name"]').val(displayName(v));
            $('input[name="display_name_plural"]').val(displayNamePlural(v));
            $('input[name="model_name"]').val(modelName(v));
        });

        $(document).on('change', '[name="form_field[]"], [name="translatable_form_field[]"]', function() {
            var select = $(this);
            var row = select.closest('tr');
            var isTranslatable = select.attr('name') === 'translatable_form_field[]';
            var prefix = isTranslatable ? 'translatable_' : '';
            var additional_field = row.find('.form-field-configs');
            var form_field_validation_config = row.find('.form-field-additional-validation');
            var form_field_config_1 = additional_field.find(`input[name="${prefix}form_field_configs_1[]"]`);
            var form_field_config_2 = additional_field.find(`input[name="${prefix}form_field_configs_2[]"]`);
            var additional_validations = row.find(`input[name="${prefix}additional_validations[]"]`);

            function resetFields() {
                form_field_config_1.prop('required', false).hide().val('').removeAttr('placeholder');
                form_field_config_2.prop('required', false).hide().val('').removeAttr('placeholder type min max');
                additional_validations.hide().val('');
                additional_field.hide();
                form_field_validation_config.hide();
            }

            function configureFields(placeholder1, placeholder2, type2, required1, required2) {
                form_field_config_1.prop('required', required1).attr('placeholder', placeholder1).show();
                form_field_config_2.prop('required', required2).attr('placeholder', placeholder2).attr('type',
                        type2)
                    .show();
                if (type2 === 'number') {
                    form_field_config_2.attr('min', 0).attr('max', 1);
                } else {
                    form_field_config_2.removeAttr('min max');
                }
                additional_field.slideDown();
            }

            function configureDropdownFields(field1, field2) {
                form_field_config_1.prop('required', field1?.required).attr('placeholder', field1?.placeholder)
                    .show();
                if (field2) {
                    form_field_config_2.prop('required', field2?.required).attr('placeholder', field2?.placeholder)
                        .attr('type', field2?.type).show();
                    if (field2?.type === 'number') {
                        form_field_config_2.attr('min', 0).attr('max', 1);
                    } else {
                        form_field_config_2.removeAttr('min max');
                    }
                }
                additional_field.slideDown();
            }
            resetFields();
            var value = select.val();
            if (value === 'slug') {
                configureDropdownFields({
                    placeholder: "Slug origin",
                    required: true
                }, {
                    placeholder: "Editable",
                    required: true,
                    type: "number"
                })
            } else if (value === 'time') {
                configureDropdownFields({
                    placeholder: "Validation",
                    required: false
                }, {
                    placeholder: "is24Hour",
                    required: false,
                    type: "number"
                })
            } else if (!isTranslatable && (value === 'select' || value === 'select multiple')) {
                configureDropdownFields({
                    placeholder: "DB table",
                    required: true,
                    type: "text"
                }, {
                    placeholder: "DB column",
                    required: true,
                    type: "text"
                })
            } else {
                additional_validations.attr('placeholder', 'Validations').show();
                form_field_validation_config.slideDown();
            }
        });



        $('.btn-add').on('click', function() {
            var fields_wrapper = $(this).parent().parent().find('table.fields');

            fields_wrapper.append('<tr class="field">' + (fields_wrapper.attr('data-type') == 'translatable' ?
                translateableHTMLField : HTMLField) + '</tr>');

            const lastRow = fields_wrapper.find('.field:last');

            lastRow.find('input[type="text"], input[type="hidden"]').val('');

            fields_wrapper.find('.field:last input[name="can_create[]"]').val(1);
            fields_wrapper.find('.field:last input[name="can_update[]"]').val(1);
            fields_wrapper.find('.field:last input[name="can_read[]"]').val(1);

            fields_wrapper.find('.field:last input[name="nullable[]"]').val(0);
            fields_wrapper.find('.field:last input[name="unique[]"]').val(0);
            fields_wrapper.find('.field:last select').val('');
            fields_wrapper.find('.field:last').find('.form-field-configs').hide();
        });


        function removeField(btn) {
            $(btn).closest('.field').remove();
        }

        function ucwords(str) {
            return str.replace(/\b\w/g, char => char.toUpperCase());
        }

        function displayNamePlural(tableName) {
            return ucwords(tableName.replace(/_/g, ' '));
        }

        function displayName(tableName) {
            tableName = displayNamePlural(tableName);
            if (tableName.endsWith('ies')) return tableName.slice(0, -3) + 'y';
            if (tableName.endsWith('s')) return tableName.slice(0, -1);
            return tableName;
        }

        function modelNamePlural(tableName) {
            return ucwords(tableName.replace(/_/g, ' ')).replace(/ /g, '');
        }

        function modelName(tableName) {
            tableName = modelNamePlural(tableName);
            if (tableName.endsWith('ies')) return tableName.slice(0, -3) + 'y';
            if (tableName.endsWith('s')) return tableName.slice(0, -1);
            return tableName;
        }
        $(document).ready(function() {
            function toggleFormDefaults() {
                const isChecked = $('input[name="is_form"]').is(':checked');
                if (isChecked) {
                    $('input[name="show"]').prop('checked', true);
                    $('input[name="delete"]').prop('checked', true);
                    $('input[name="server_side_pagination"]').prop('checked', true);
                }
            }
            $('input[name="is_form"]').on('change', function() {
                toggleFormDefaults();
            });

            toggleFormDefaults();
        });

          $(document).ready(function() {
            function toggleFormDefaults() {
                const isChecked = $('input[name="single_record"]').is(':checked');
                if (isChecked) {
                    $('input[name="add"]').prop('checked', true);
                    $('input[name="edit"]').prop('checked', true);
                    $('input[name="show"]').prop('checked', true);
                }
            }
            $('input[name="single_record"]').on('change', function() {
                toggleFormDefaults();
            });

            toggleFormDefaults();
        });




        
        $(document).ready(() => {
            $('[name="translatable_name[]"]').closest('.field').last().remove();
            $('[name="name[]"]').closest('.field').last().remove();
        })
     
        $(document).on('change', '.checkbox-number input[type="checkbox"]', function() {
            $(this).closest('.checkbox-number')
                .find('input[type="number"], input[type="hidden"]')
                .val($(this).is(':checked') ? 1 : 0);
        });
    </script>
@endsection
