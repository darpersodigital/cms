<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Shouf El Menu CMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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

    @foreach (config('cms_config.additional_styles') as $path)
        <link rel="stylesheet" type="text/css" href="{{ url($path) }}">
    @endforeach


</head>

<body>

    @yield('content')

    <div class="loader-wrapper bg-primary"
        style="position: fixed;
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
    <div class="custom-bg"></div>

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
    <script src="{{ url('asset?path=js/main.js') }}"></script>
    <script src="{{ url('asset?path=js/jquery.mjs.nestedSortable.js') }}"></script>
    @yield('scripts')
    @foreach (config('cms_config.additional_methods') as $path)
        <script type="text/javascript" src="{{ url($path) }}"></script>
    @endforeach
</body>

</html>
