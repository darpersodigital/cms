@extends('darpersocms::layouts/dashboard')

@php
    $baseUrl = config('cms_config.route_path_prefix') . '/admins';
@endphp

@section('dashboard-content')
    <form method="post" action="{{ isset($row->id) ? url($baseUrl . '/' . $row->id) : url($baseUrl) }}"
        enctype="multipart/form-data" ajax>

        <div class="container-fluid px-md-5 mt-5">
            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => isset($row) ? "EDIT ADMIN #{$row->id}" : 'ADD ADMIN',
                'submit' => isset($row) ? 'Update' : 'Add',
                'testID' => 'admins',
            ])

            <div class="white-card">
                @csrf
                @isset($row)
                    @method('PUT')
                @endisset


                @include('darpersocms::cms.components.form-fields.TextInput', [
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'type' => 'text',
                    'testID' => 'full_name',
                    'styles' => 'mt-0',
                    'value' => old('full_name') ?? ($row->full_name ?? ''),
                    'locale' => null,
                    'error' => $errors->first('full_name'),
                    'required' => true,
                ])


                @include('darpersocms::cms.components.form-fields.TextInput', [
                    'label' => 'Username',
                    'name' => 'user_name',
                    'testID' => 'user_name',
                    'type' => 'text',
                    'value' => old('user_name') ?? ($row->user_name ?? ''),
                    'locale' => null,
                    'error' => $errors->first('user_name'),
                    'required' => true,
                ])

                @include('darpersocms::cms.components.form-fields.TextInput', [
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'text',
                    'testID' => 'email',
                    'value' => old('email') ?? ($row->email ?? ''),
                    'locale' => null,
                    'locale' => null,
                    'error' => $errors->first('email'),
                    'required' => true,
                ])

                @include('darpersocms::cms.components/form-fields/image', [
                    'label' => 'Image',
                    'name' => 'image',
                    'testID' => 'image',
                    'value' => $row->image ?? '',
                    'locale' => null,
                ])


                @include('darpersocms::cms.components/form-fields/password-with-confirmation', [
                    'label' => 'Password',
                    'name' => 'password',
                    'testID' => 'password',
                    'locale' => null,
                    'value' => '',
                ])

                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Admin Role',
                    'name' => 'admin_role_id',
                    'options' => $admin_roles,
                    'store_column' => 'id',
                    'testID' => 'admin_role_id',
                    'display_column' => 'title',
                    'value' => old('admin_role_id') ?? ($row->admin_role_id ?? ''),
                    'error' => $errors->first('admin_role_id'),
                    'locale' => null,
                ])

            </div>
        </div>


    </form>
@endsection
