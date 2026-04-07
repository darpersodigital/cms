@extends('darpersocms::layouts.auth')


@section('content')

    <div class=" bg-primary h-100v d-flex align-items-center justify-content-center " data-testid="login-form">
        <div class="container  h-100v d-flex align-items-center justify-content-center">
            <div class="row w-100 h-100v d-flex align-items-center justify-content-center align-items-center">
                <div class="col-lg-6">
                    <div class="white-card  p-5">
                        <div class="row align-items-center justify-content-center">
                            <div class="col-12 text-center">
                                <img src="{{ url('asset?path=cms-images/' . config('cms_config.logo')) }}" alt=""
                                    class=" " style="max-width: 120px">
                            </div>
                            <div class="col-lg-12 mt-5">
                                <h3 class="text-center">Sign in with email</h3>
                                @php
                                    $filteredErrors = collect($errors->messages())
                                        ->except(['email', 'password'])
                                        ->flatten();
                                @endphp

                                @if ($filteredErrors->isNotEmpty())
                                    <div class="alert alert-danger mt-4" data-testid="login-errors">
                                        <ul class="list-none">
                                            @foreach ($filteredErrors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" class="form-wrapper pb-4 mt-3 ">
                                    @csrf

                                    @include('darpersocms::cms.components.form-fields.TextInput', [
                                        'name' => 'email',
                                        'label' => 'Email',
                                        'value' => '',
                                        'placeholder' => 'Email',
                                        'error' => $errors->first('email'),
                                        'required' => true,
                                        'testID' => 'login-email',
                                    ])

                                    @include('darpersocms::cms.components.form-fields.TextInput', [
                                        'name' => 'password',
                                        'label' => 'Password',
                                        'value' => '',
                                        'type' => 'password',
                                        'placeholder' => 'password',
                                        'error' => $errors->first('password'),
                                        'required' => true,
                                        'testID' => 'login-password',
                                    ])

                                    <div class=" mt-4">
                                        <input class="theme-btn bg-primary" data-testid="submit" type="submit"
                                            value="Login">
                                    </div>
                                </form>

                            </div>
                            <div class="col-lg-12 text-center">
                                <p class="mt-3">{!! config('cms_config.login_footer') !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
