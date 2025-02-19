@extends('darpersocms::layouts.auth')


@section('content')
    <div class=" bg-primary h-100 d-flex align-items-center justify-content-center login-page">
        <div class="container  h-100 d-flex align-items-center justify-content-center">
            <div class="row w-100 h-100 d-flex align-items-center justify-content-center align-items-center">
                <div class="col-lg-10">
                    <div class="white-card card-shadow ">
                        <div class="row align-items-center">
                            <div class="col-lg-4 text-center py-5">
                                <img src="{{ url('asset?path=cms-images/'.config('cms_config.logo')) }}" alt="" class="logo w-100 ">
                            </div>

                            <div class="col-lg-8">
                                <h3 class="text-center">Login</h3>
                                @if ($errors->any())
                                <div class="alert alert-danger mt-4">
                                    <ul class="list-none">
                                        @foreach ($errors->all() as $error)
                                            <li class="">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                                <form method="POST" class="form-wrapper pb-4 mt-3"  >
                                    @csrf
                                    <div class="input-wrapper">
                                        <label for="">Email</label>
                                        <input type="text" name="email" id="" class="form-control" placeholder="Email" value="">
                                    </div>
                                    <div class="input-wrapper">
                                        <label for="">Password</label>
                                        <input type="password" name="password" id="" class="form-control" placeholder="Password" value="">
                                    </div>
                                    <div class="text-right mt-3">
                                        {{-- <button class="btn btn-sm btn-primary px-4">Submit</button> --}}
                                        <input type="submit" class="btn btn-sm btn-primary px-4">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
