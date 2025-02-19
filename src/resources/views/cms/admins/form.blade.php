@extends('darpersocms::layouts/dashboard')

@php
    $baseUrl = config('cms_config.route_path_prefix') . '/admins';
@endphp

@section('dashboard-content')
    <form method="post" action="{{ isset($row->id) ? url($baseUrl . '/' . $row->id) : url($baseUrl) }}"
        enctype="multipart/form-data" ajax>

        <div class="container-fluid px-md-5 mt-5">

            @include('darpersocms::cms.components.breadcrumb.breadcrumb-action', [
                'title' => isset($row) ? "EDIT ADMIN #{$row->id}" : 'ADD ADMIN',
            ])

            <div class="white-card">
               
                @include('darpersocms::cms.components.errors.errors')

                @csrf
                @isset($row)
                    @method('PUT')
                @endisset

               <div class="mb-3">
                @include('darpersocms::cms.components/form-fields/input', [
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'value' => old('name') ?? $row->name ?? '',
                    'locale' => null,
                ])
               </div>
               <div class="mb-3">
                @include('darpersocms::cms.components/form-fields/image', [
                    'label' => 'Image',
                    'name' => 'image',
                    'value' => $row->image ?? '',
                    'locale' => null,
                ])
               </div>
              <div class="mb-3">
                @include('darpersocms::cms.components/form-fields/input', [
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'text',
                    'value' => old('email') ?? $row->email ?? '',
                    'locale' => null,
                ])
              </div>
               <div class="mb-3">
                @include('darpersocms::cms.components/form-fields/password-with-confirmation', [
                    'label' => 'Password',
                    'name' => 'password',
                    'locale' => null,
                ])
               </div>
                @include('darpersocms::cms.components/form-fields/select', [
                    'label' => 'Admin Role',
                    'name' => 'admin_role_id',
                    'options' => $admin_roles,
                    'store_column' => 'id',
                    'display_column' => 'title',
                    'value' => old('admin_role_id') ?? $row->admin_role_id ?? '',
                    'locale' => null,
                ])

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-sm btn-primary "> {{ isset($row) ? 'Update' : 'Add' }}</button>
                </div>
            </div>
        </div>


    </form>
@endsection
