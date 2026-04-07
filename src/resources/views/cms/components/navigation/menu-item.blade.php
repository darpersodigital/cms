@props([
    'page' => null,
    'title' => null,
    'route' => null,
    'icon' => null,
    'isActive' => null,
    'route' => null,
])

@php
    $prefix = config('cms_config.route_path_prefix');

    if ($page) {
        $routePath = $prefix . '/' . $page['route'];
        $route = $page['route'];
        $title = $page['display_name_plural'] ?? '';
        $icon = $page['icon'] ?? '';
        $isActive = request()->is($routePath) || request()->is($routePath . '/*');
        $url = url($routePath);
    } else {
        $url = $route ? route($route) : '#';
    }
@endphp

@if(!empty($title))
    <a class="admin-menu-item-wrapper" href="{{ $url }}" title="{{ $title }}" data-testid="route-{{$route}}">
        <div class="admin-menu-item {{ $isActive ? 'active' : '' }}">
             <i class="text-center mr-2 fa {{  $icon }}" aria-hidden="true"></i>
            <div class="title">{{ $title }}</div>
        </div>
    </a>
@endif