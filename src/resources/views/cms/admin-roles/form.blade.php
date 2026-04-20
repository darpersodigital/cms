@extends('darpersocms::layouts/dashboard')

@php
    $baseUrl = config('cms_config.route_path_prefix') . '/admin-roles';
@endphp

@section('dashboard-content')
    <form method="post" action="{{ isset($row['id']) ? url($baseUrl . '/' . $row['id']) : url($baseUrl) }}"
        enctype="multipart/form-data" ajax>

        <div class="container-fluid px-md-5 mt-5">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? "EDIT ADMIN ROLE #{$row['id']}" : 'ADD New ADMIN ROLE',
                'submit' => isset($row) ? 'Update' : 'Add',
                'testID' => 'admin-roles',
            ])
            <div class="white-card">
                @csrf
                @isset($row)
                    @method('PUT')
                @endisset

                <div class="mb-4">
                    @include('darpersocms::cms.components.form-fields.TextInput', [
                        'label' => 'Title',
                        'name' => 'title',
                        'testID'=>'title',
                        'type' => 'text',
                        'error' => $errors->first('title'),
                        'disable_counter'=>true,
                        'styles' => 'mt-0',
                        'required' => true,
                        'value' => $row->title ?? '',
                    ])

                </div>

                @include('darpersocms::cms.components/form-fields/checkbox', [
                    'label' => 'Select All',
                    'inline_label' => true,
                    'name' => 'toggle_all_permission',
                    'checked' => false,
                ])

                <div class="row mt-3">
                    @foreach ($post_types_permissions as $post_type)
                        @continue($post_type['route'] === 'post-types')

                        <div class="form-input-container col admin-role-container" data-testid="permission-{{$post_type['route']}}">
                            <label>{{ $post_type['display_name_plural'] }}</label><br>
                            @php
                                $permissions = ['browse', 'read', 'edit', 'add', 'delete'];
                            @endphp

                            @foreach ($permissions as $permission)
                                <div class="post-type-permission" >
                                    @include('darpersocms::cms.components/form-fields/checkbox', [
                                        'label' => ucfirst($permission),
                                        'testID' => 'admin-roles',
                                        'inline_label' => true,
                                        'name' => "{$permission}_{$post_type['id']}",
                                        'checked' => isset($row) ? $post_type['permissions'][$permission] : false,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>


            </div>
        </div>

    </form>
@endsection


@section('scripts')
    <script>
        $('.admin-role-container label').on('click', function() {
            var inputs = $(this).closest('.form-input-container').find('input');
            var checked = false;
            for (let input of inputs) {
                checked = input.checked;
                if (checked) break;
            }
            inputs.prop('checked', !checked);
        });

        $('[name="toggle_all_permission"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('.post-type-permission input').prop('checked', true);
            } else {
                $('.post-type-permission input').prop('checked', false);
            }
        });
    </script>
@endsection
