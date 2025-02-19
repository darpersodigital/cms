<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Shouf El Menu CMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
        integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  
    <link rel="stylesheet" href="{{ asset('assets/fontawesome-cms/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('asset?path=css/main.css') }}">
    
    @foreach(config('cms_config.additional_styles') as $path)
		<link rel="stylesheet" type="text/css" href="{{ url($path) }}">
	@endforeach


</head>

<body>
    @if (session('success'))
    <div class="alert alert-success session-popup">
        {{ session('success') }}
    </div>
@endif

    @if (session('error'))
    <div class="alert alert-danger session-popup">
        {{ session('error') }}
    </div>
    @endif

    <div class="page-wrapper ">
        <div class="side-menu expanded">
            <div class="logo-wrapper  d-flex py-3">
                <img src="{{ url('asset?path=cms-images/'.config('cms_config.logo')) }}" alt="" class="mx-auto">
            </div>


            <div class="admin-menu mt-3">
                <div class="admin-menu-item-wrapper">
                    <div class="admin-menu-item @if (Route::current()->getName() === 'dashboard') active @endif">
                        <i class="fa-solid fa-house"></i>
                        <div class="title">Dashboard</div>
                    </div>
                </div>

                {{-- <a href={{ route('post-types') }} class=" admin-menu-item-wrapper   ">
                    <div class="admin-menu-item @if (Route::current()->getName() === 'post-types') active @endif">
                        <i class="fa-solid fa-table-columns"></i>
                        <div class="title">Post Types</div>
                    </div>
                </a> --}}


                @foreach (request()->get('admin')['post_types_grouped'] as $group)
                    @if (!$group['icon'] && !$group['title'])
                        @foreach ($group['pages'] as $page)
                            @if (!$page['display_name_plural'])
                                @continue
                            @endif
                            <a class="admin-menu-item-wrapper"
                                href="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route']) }}"
                                title="{{ $page['display_name_plural'] }}">
                                <div
                                    class="admin-menu-item {{ request()->is(config('cms_config.route_path_prefix') . '/' . $page['route']) || request()->is(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/*') ? 'active' : '' }}">
                                    <i class="text-center mr-2 fa {{ $page['icon'] }}" aria-hidden="true"></i>
                                    <div class="title"> {{ $page['display_name_plural'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="admin-menu-item-wrapper   ">
                            <div
                                class="admin-menu-item with-children flex-column w-100 justify-content-start @foreach ($group['pages'] as $page){{ request()->is(config('cms_config.route_path_prefix') . '/' . $page['route'] . '*') ? 'active' : '' }} @endforeach">
                                <div class="d-flex  w-100 justify-content-between align-items-center">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <i class="text-center mr-2 fa {{ $group['icon'] }}" aria-hidden="true"></i>
                                        <div class="title">{{ $group['title'] }} </div>

                                    </div>

                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>

                                <div class="children mr-auto mt-1">
                                    @foreach ($group['pages'] as $page)
                                        @if (!$page['display_name_plural'])
                                            @continue
                                        @endif
                                        <a class="title"
                                            href="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route']) }}"
                                            title="{{ $page['display_name_plural'] }}">{{ $page['display_name_plural'] }}</a>
                                    @endforeach

                                </div>
                            </div>

                        </div>
                    @endif
                @endforeach

            </div>

        </div>

        <div class="right-content">

            <div class="floating-bg bg-primary"></div>

            <div class="admin-account-wrapper custom-dropdown-wrapper ml-auto pointer ">
                <div class=" dropdown-trigger d-flex align-items-center">
                    <p class="text-white mr-2"><b>Seroujkh</b></p>
                    <img class="avatar"
                        src="{{ request()->get('admin')['image'] ? Storage::url(request()->get('admin')['image']) : url('asset?path=cms-images/placeholder.png') }}">
                </div>
                <div class="custom-dropdown-wrapper-items admin-profile ">

                    <div class="d-flex flex-column">
                        <a href="{{ route('admin-profile-edit') }}">
                            <i class="fa fa-user mr-2" aria-hidden="true"></i>
                            My Profile
                        </a>
                        <div class="line"></div>
                        <a href="{{ route('admin-logout') }}">
                            <i class="fa fa-sign-out mr-2" aria-hidden="true"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            @yield('dashboard-content')
        </div>

    </div>


    @include('darpersocms::cms/components/footer')

   
 <div class="loader-wrapper bg-primary" style="position: fixed;
    height: 100vh;
    width: 100%;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999999999999999999999999;
    transition: all 1s;">
        <div class="spinner">
            <div class="spinner-item"></div>
            <div class="spinner-item"></div>
            <div class="spinner-item"></div>
            <div class="spinner-item"></div>
            <div class="spinner-item"></div>
        </div>
    </div> 


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.6.2/standard-all/ckeditor.js"></script>
    <script src="{{url('asset?path=js/dataTables.buttons.min.js') }}"></script>
    <script src="{{url('asset?path=js/buttons.html5.min.js') }}"></script>
    <script src="{{url('asset?path=js/pdfmake.min.js') }}"></script>
    <script src="{{url('asset?path=js/vfs_fonts.js') }}"></script>
    <script src="{{url('asset?path=js/main.js') }}"></script>
    <script src="{{url('asset?path=js/jquery.mjs.nestedSortable.js') }}"></script>


    @yield('scripts')

    @foreach(config('cms_config.additional_methods') as $path)
		<script type="text/javascript" src="{{ url($path) }}"></script>
	@endforeach

</body>

</html>
