@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post"
        action="{{ isset($post_type) ? url(config('cms_config.route_path_prefix') . '/post-types/' . $post_type['id']) : url(config('cms_config.route_path_prefix') . '/post-types') }}"
        ajax>

        <div class="container-fluid px-md-5 mt-5 ">
            <div class="white-card">
                @include('darpersocms::cms.components.breadcrumb.index', [
                    'title' => isset($post_type) ? 'Edit CMS page #' . $post_type['id'] : 'Add CMS page',
                ])
            </div>

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
                                @include('darpersocms::cms.components/form-fields/input', [
                                    'label' => $field['label'],
                                    'name' => $field['name'],
                                    'type' => 'text',
                                    'value' => $post_type[$field['name']] ?? '',
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
                                @include('darpersocms::cms.components/form-fields/input', [
                                    'label' => $field['label'],
                                    'name' => $field['name'],
                                    'type' => 'text',
                                    'value' => $post_type[$field['name']] ?? '',
                                    'locale' => null,
                                    'description' => '',
                                    'required' => $field['required'],
                                ])
                            </div>
                        @endforeach
                        @include('darpersocms::cms.components.form-fields.label', ['label' => 'Sort By Direction'])
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
                    <div class="btn-action lg add btn-add"><i class="fa-solid fa-plus"></i></div>
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
                                    @include('darpersocms::cms/post-types/post-types-field-row', ['field_type' => ''])
                                @endforeach
                            @else
                                @include('darpersocms::cms/post-types/post-types-field-row', ['field_type' => ''])
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="white-card">
                <div class="d-flex justify-content-between">
                    <h4>Translatable Fields</h4>
                    <div class="btn-action lg add  btn-add"><i class="fa-solid fa-plus"></i></div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="fields table" data-type="translatable">
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
                                    @include('darpersocms::cms/post-types/post-types-field-row', [
                                        'field_type' => 'translatable',
                                        'post_type' => null,
                                    ])
                                @endif
                            @else
                                @include('darpersocms::cms/post-types/post-types-field-row', [
                                    'field_type' => 'translatable',
                                    'post_type' => null,
                                ])
                            @endif


                        </tbody>
                    </table>
                </div>
                <div class="px-4 text-right">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
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

        @if (!isset($post_type) || (isset($post_type) && isset($translatable_fields) && !count($translatable_fields)))
            $('[name="translatable_name[]"]').closest('.field').remove();
        @endif

        $(document).on('change', '[name="form_field[]"]', function() {
            var select = $(this);
            var additional_field = select.closest('td').find('.form-field-configs');
            var form_field_validation_config = select.closest('td').find('.form-field-additional-validation');

            var form_field_config_1 = additional_field.find('input[name="form_field_configs_1[]"');
            var form_field_config_2 = additional_field.find('input[name="form_field_configs_2[]"');

            var additional_validations = additional_field.find('input[name="additional_validations[]"');

            function resetFields() {
                form_field_config_1.prop('required', false).hide().val('');
                form_field_config_2.prop('required', false).hide().val('');
                additional_validations.hide().val('');
                additional_field.slideUp();
                form_field_validation_config.slideUp();
            }


            function configureFields(placeholder1, placeholder2, type2) {
                form_field_config_1.prop('required', true).attr('placeholder', placeholder1).show();
                form_field_config_2.prop('required', true).attr('placeholder', placeholder2).attr('type', type2)
                    .show();
                if (type2 == 'number') form_field_config_2.attr('min', 0).attr('max', 1);
                additional_field.slideDown();
            }

            additional_field.slideUp(function() {
                var value = select.val();
                if (value === 'slug') {
                    resetFields();
                    configureFields('Slug origin', 'Editable', 'number');
                } else if (value === 'select' || value === 'select multiple') {
                    resetFields();
                    configureFields('DB table', 'DB column', 'text');
                } else {
                    additional_validations.attr('placeholder', 'Validations').show();
                    form_field_validation_config.slideDown();
                }
            });
        });

        $(document).on('change', '[name="translatable_form_field[]"]', function() {
            var select = $(this);
            var additional_field = select.closest('td').find('.form-field-configs');
            var form_field_validation_config = select.closest('td').find('.form-field-additional-validation');
            var form_field_config_1 = additional_field.find('input[name="form_field_configs_1[]"');
            var form_field_config_2 = additional_field.find('input[name="form_field_configs_2[]"');
            var additional_validations = additional_field.find('input[name="additional_validations[]"');
            additional_field.slideUp(function() {
                form_field_config_1.prop('required', false).hide();
                form_field_config_2.prop('required', false).hide();;
                additional_validations.attr('placeholder', 'Validations').val('').show();
                form_field_validation_config.slideDown();
            });
        });



        $('.btn-add').on('click', function() {
            var fields_wrapper = $(this).parent().parent().find('table.fields');
            console.log("fields_wrapper.attr('data-type') ", fields_wrapper.attr('type'));
            fields_wrapper.append('<tr class="field">' + (fields_wrapper.attr('data-type') == 'translatable' ?
                translateableHTMLField : HTMLField) + '</tr>');
            fields_wrapper.find('.field:last input').val('');
            fields_wrapper.find('.field:last input[name="number"]').val(0);
            fields_wrapper.find('.field:last input[name="can_create[]"]').val(1);
            fields_wrapper.find('.field:last input[name="can_update[]"]').val(1);
            fields_wrapper.find('.field:last input[name="can_read[]"]').val(1);
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
            $('.checkbox-number input[type="checkbox"]').on('change', function() {
                $(this).closest('.checkbox-number').find('input[type="number"]').val($(this).is(
                    ':checked') ? 1 : 0)
            });
        });
    </script>
@endsection
