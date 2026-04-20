@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <form method="post" enctype="multipart/form-data" ajax>


        <div class="container-fluid px-md-5  mt-3">

            @include('darpersocms::cms.components.breadcrumb.ScreenTitleHeader', [
                'title' => 'EDIT PROFILE',
                'submit' => 'Update Profile',
                'testID'=>'update-profile'
            ])

            <div class="white-card">


                @csrf

                @include('darpersocms::cms.components.form-fields.TextInput', [
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'styles'=>'mt-0',
                    'testID'=>'full_name',
                    'disable_counter'=>true,
                    'type' => 'text',
                    'value' => request()->get('admin')['full_name'],
                    'locale' => '',
                    'required' => true,
                ])

                @include('darpersocms::cms.components.form-fields.TextInput', [
                    'label' => 'Username',
                    'name' => 'user_name',
                    'testID'=>'user_name',
                    'disable_counter'=>true,
                    'type' => 'text',
                    'value' => request()->get('admin')['user_name'],
                    'locale' => '',
                    'required' => true,
                ])

                @include('darpersocms::cms.components/form-fields/image', [
                    'label' => 'Image',
                    'name' => 'image',
                    'testID'=>'image',
                    'value' => request()->get('admin')['image'],
                    'locale' => '',
                ])

                @include('darpersocms::cms.components/form-fields/password-with-confirmation', [
                    'label' => 'Password',
                    'name' => 'password',
                    'required'=>false,
                    'testID'=>'password',
                    'locale' => null,
                    'value' => '',
                ])

            </div>
        </div>
    </form>
@endsection
