@extends('darpersocms::layouts/dashboard')


@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">
        <div class="white-card">
            <form method="post" enctype="multipart/form-data" ajax>
                <div class="d-flex align-items-center mb-3">
                    @include('darpersocms::cms.components.breadcrumb.index', ['title' => 'EDIT PROFILE'])
                </div>
                @include('darpersocms::cms.components.errors.errors')
                @csrf

                <div class="mb-3">
                    @include('darpersocms::cms.components/form-fields/input', [
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'value' => request()->get('admin')['name'],
                    'locale' => '',
                ])
                </div>
                 <div class="mb-3">
                    @include('darpersocms::cms.components/form-fields/input', [
                    'label' => 'Password',
                    'name' => 'password',
                    'type' => 'password',
                    'value' => '',
                    'locale' => '',
                ])
                </div>
                <div class="mb-3">
                    @include('darpersocms::cms.components/form-fields/input', [
                    'label' => 'Confirm Password',
                    'name' => 'password_confirmation',
                    'type' => 'password',
                    'value' => '',
                    'locale' => '',
                ])
                </div>
             <div class="mb-3">
                @include('darpersocms::cms.components/form-fields/image', [
                    'label' => 'Image',
                    'name' => 'image',
                    'value' => request()->get('admin')['image'],
                    'locale' => '',
                ])
             </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
