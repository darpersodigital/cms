<div data-testid="burger-menu" class="burger-menu ml-auto">
    <div></div>
    <div></div>
    <div></div>
</div>



<div class="side-menu expanded">
    <div class="logo-wrapper  d-flex py-3">
        <img src="{{ url('asset?path=cms-images/' . config('cms_config.logo')) }}" alt="" class="mx-auto">
    </div>

    <div class="admin-menu ">
        @include('darpersocms::cms.components.navigation.menu-item', [
            'title' => 'Dashboard',
            'page'=>null,
            'route' => 'admin-dashboard',
            'icon' => 'fa-solid fa-house',
            'isActive' => Route::current()->getName() === 'admin-dashboard',
        ])


        @foreach (request()->get('admin')['post_types_grouped'] as $group)
            @if (!$group['icon'] && !$group['title'])
                @foreach ($group['pages'] as $page)
                    @include('darpersocms::cms.components.navigation.menu-item', ['page' => $page])
                @endforeach
            @else
                @php
                    $prefix = config('cms_config.route_path_prefix');
                    $groupActive = collect($group['pages'])->contains(function ($page) use ($prefix) {
                        return request()->is($prefix . '/' . $page['route'] . '*');
                    });
                @endphp
                

                <div class="admin-menu-item-wrapper {{ $groupActive ? 'active' : '' }}" data-testid="admin-menu-item-wrapper-group">
                    <div
                        class="admin-menu-item with-children flex-column w-100 justify-content-start {{ $groupActive ? 'active' : '' }}">

                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="d-flex justify-content-between align-items-center">
                                <i class="text-center mr-2 fa {{ $group['icon'] }}" aria-hidden="true"></i>
                                <div class="title">{{ $group['title'] }}</div>
                            </div>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                    </div>

                    <div class="children mr-auto mt-1 ml-3">
                        @foreach ($group['pages'] as $page)
                            @include('darpersocms::cms.components.navigation.menu-item', [
                                'page' => $page,
                            ])
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach


    </div>
</div>
