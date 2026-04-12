<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>{{ config('cms_config.seo_title') }}</title>
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
    <link rel="stylesheet" href="{{ url('asset?path=css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">

    @foreach (config('cms_config.additional_styles') as $path)
        <link rel="stylesheet" type="text/css" href="{{ url($path) }}">
    @endforeach


</head>

<body>

    <div data-testid="darperso-cms-dashboard"></div>
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


        @include('darpersocms::cms.components.navigation.side-menu')

        <div class="right-content">
            <div class="floating-bg bg-primary"></div>
            @include('darpersocms::cms.components.navigation.top-menu')

            @yield('dashboard-content')
        </div>

    </div>


    @include('darpersocms::cms/components/footer')


    @include('darpersocms::layouts.AdminLoader')



    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.6.2/standard-all/ckeditor.js"></script>
    <script src="{{ url('asset?path=js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ url('asset?path=js/buttons.html5.min.js') }}"></script>
    <script src="{{ url('asset?path=js/pdfmake.min.js') }}"></script>
    <script src="{{ url('asset?path=js/vfs_fonts.js') }}"></script>
       <script type="module" src="{{ url('asset?path=js/ai-seo.js') }}"></script>
    <script src="{{ url('asset?path=js/main.js') }}"></script>
    <script src="{{ url('asset?path=js/jquery.mjs.nestedSortable.js') }}"></script>


    @yield('scripts')

    @foreach (config('cms_config.additional_methods') as $path)
        <script type="text/javascript" src="{{ url($path) }}"></script>
    @endforeach


</body>

</html>
