@php
    if (isset($post_type)) {
        $row_field_old_name = $field['name'];
        $row_field_name = $field['name'];
        $row_field_migration_type = $field['migration_type'];
        $row_field_form_field = $field['form_field'];
        $row_field_old_additional_validations = $field['additional_validations'];
        $row_field_additional_validations = $field['additional_validations'];
        $row_field_old_form_field_configs_1 = $field_type ? null : $field['form_field_configs_1'];
        $row_field_form_field_configs_1 = $field_type ? null : $field['form_field_configs_1'];
        $row_field_form_field_configs_2 = $field_type ? null : $field['form_field_configs_2'];
        $row_field_description = $field['description'] ?? '';
        $row_field_is_form = isset($field['is_form']) ? $field['is_form'] : 1;
        $row_field_can_create = isset($field['can_create']) ? $field['can_create'] : 1;
        $row_field_hide_table = isset($field['hide_table']) ? $field['hide_table'] : 0;
        $row_field_can_update = isset($field['can_update']) ? $field['can_update'] : 1;
        $row_field_can_read = isset($field['can_read']) ? $field['can_read'] : 1;
        $row_field_nullable = isset($field['nullable']) && $field['nullable'] ? 1 : 0;
        $row_field_unique = ($field_type ? null : isset($field['unique']) && $field['unique']) ? 1 : 0;
    } else {
        $row_field_old_name = '';
        $row_field_name = '';
        $row_field_migration_type = '';
        $row_field_form_field = '';
        $row_field_old_additional_validations = '';
        $row_field_additional_validations = '';
        $row_field_old_form_field_configs_1 = '';
        $row_field_form_field_configs_1 = '';
        $row_field_form_field_configs_2 = null;
        $row_field_description = null;
        $row_field_hide_table = 0;
        $row_field_can_create = 1;
        $row_field_is_form = 0;
        $row_field_can_update = 1;
        $row_field_can_read = 1;
        $row_field_nullable = 0;
        $row_field_unique = 0;
    }
@endphp

<tr class="sortable-row position-relative field" type="{{$field_type}}">
    <td class="text-center">
        <input type="hidden" name="{{ $field_type ? $field_type . '_' : '' }}old_name[]"
            value="{{ $row_field_old_name }}">
        <input class="form-control" name="{{ $field_type ? $field_type . '_' : '' }}name[]" value="{{ $row_field_name }}">
    </td>
    <td class="text-center">
        <select class="form-control w-100" name="{{ $field_type ? $field_type . '_' : '' }}migration_type[]">
            <option></option>
            @foreach ($migration_types as $type)
                <option value="{{ $type }}" {!! $row_field_migration_type == $type ? 'selected=""' : '' !!}>{{ $type }}</option>
            @endforeach
        </select>
    </td>
    <td class="text-center">
        <select class="form-control w-100" name="{{ $field_type ? $field_type . '_' : '' }}form_field[]">
            <option></option>
            @foreach ($form_fields as $form_field)
                @if ($field_type =='translatable') 
                     @if($form_field!='email' && $form_field!='password' && $form_field!='slug' && $form_field!='password with confirmation' )
                         <option value="{{ $form_field }}" {!! $row_field_form_field == $form_field ? 'selected=""' : '' !!}>{{ $form_field }}</option> --}}
                     @endif 
                @else 
                 <option value="{{ $form_field }}" {!! $row_field_form_field == $form_field ? 'selected=""' : '' !!}>{{ $form_field }}</option> 
                @endif
            @endforeach
        </select>
        <div class="form-field-configs" {!! $row_field_form_field_configs_1 ? '' : 'style="display: none;"' !!}>
            <input type="hidden" name="{{ $field_type ? $field_type . '_' : '' }}old_form_field_configs_1[]"
                value="{{ $row_field_old_form_field_configs_1 }}">
            <input class="form-control mt-2"
                name="{{ $field_type ? $field_type . '_' : '' }}form_field_configs_1[]"
                value="{{ $row_field_form_field_configs_1 }}">

            <input class="form-control mt-2"
                name="{{ $field_type ? $field_type . '_' : '' }}form_field_configs_2[]"
                value="{{ $row_field_form_field_configs_2 }}" {!! $row_field_form_field == 'slug' ? 'type="number"' : '' !!} {!! is_null($row_field_form_field_configs_2) ? 'style="display:none;"' : '' !!}>


        </div>

        <div class="form-field-additional-validation" {!! $row_field_additional_validations ? '' : 'style="display: none;"' !!}>

            <input type="hidden" name="{{ $field_type ? $field_type . '_' : '' }}old_additional_validations[]"
                value="{{ $row_field_old_additional_validations }}">
            <input class="form-control mt-2" placeholder="Validations"
                name="{{ $field_type ? $field_type . '_' : '' }}additional_validations[]"
                value="{{ $row_field_additional_validations }}">
        </div>
    </td>
    <td class="text-center">
        <input class="form-control" name="{{ $field_type ? $field_type . '_' : '' }}description[]"
            value="{{ $row_field_description }}">
    </td>


    <td class="text-center">
        @include('darpersocms::cms.components.form-fields.checkbox-number', [
            'name' => ($field_type ? $field_type . '_' : '') . 'hide_table[]',
            'checked' => $row_field_hide_table,
        ])
    </td>

    <td class="text-center">
        @include('darpersocms::cms.components.form-fields.checkbox-number', [
            'name' => ($field_type ? $field_type . '_' : '') . 'can_create[]',
            'checked' => $row_field_can_create,
        ])
    </td>

    <td class="text-center">
        @include('darpersocms::cms.components.form-fields.checkbox-number', [
            'name' => ($field_type ? $field_type . '_' : '') . 'can_read[]',
            'checked' => $row_field_can_read,
        ])
    </td>

    <td class="text-center">
        @include('darpersocms::cms.components.form-fields.checkbox-number', [
            'name' => ($field_type ? $field_type . '_' : '') . 'can_update[]',
            'checked' => $row_field_can_update,
        ])
    </td>



    <td class="text-center">
        @include('darpersocms::cms.components.form-fields.checkbox-number', [
            'name' => ($field_type ? $field_type . '_' : '') . 'nullable[]',
            'checked' => $row_field_nullable,
        ])
    </td>


    @if (!$field_type)
        <td class="text-center">
            @include('darpersocms::cms.components.form-fields.checkbox-number', [
                'name' => ($field_type ? $field_type . '_' : '') . 'unique[]',
                'checked' => $row_field_unique,
            ])
        </td>
    @endif
    <td class="text-center">
        <button class="btn-action  delete ml-2" type="button" onclick="removeField(this)"><i
            class="fa-solid fa-trash-can"></i></button>
    </td>
</tr>