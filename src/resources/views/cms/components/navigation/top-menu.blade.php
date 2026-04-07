@php
    $userAvatar = request()->get('admin')['image']
        ? Storage::url(request()->get('admin')['image'])
        : url('asset?path=cms-images/placeholder.png');
@endphp

<div class="admin-account-wrapper custom-dropdown-wrapper ml-auto pointer "  >
    <div class="px-md-3 dropdown-trigger pointer d-flex align-items-center" data-testid="admin-popup">
        <p class="text-white mr-2" data-testid="account-user_name"><b>{{ request()->get('admin')['user_name'] }}</b></p>
        <img class="avatar border-primary mr-1" src="{{ $userAvatar }}" alt=""
            data-testid="account-avatar-image" />
        <i class={`fa-solid fa-caret-${isAccountDropdownExpanded ? 'up ' : 'down' } fs-14 text-white`}></i>
    </div>
    <div class="custom-dropdown-wrapper-items admin-profile py-0 px-3">
        <div class="d-inline-block d-flex align-items-center py-3">
            <img src={{ $userAvatar }} alt="" />
            <div class="profile-body text-left ml-2">
                <p>{{ request()->get('admin')['email'] }}</p>
                <p data-testid="account-full_name">{{ request()->get('admin')['full_name'] }}</p>
            </div>
        </div>
        <div class="horizontal-line black "></div>
        <div class="py-3 ">

            <a data-testid="btn-action-edit-profile" href="{{ route('admin-profile-edit') }}"
                class="drop-item  fs-14 py-0 pb-2 mb-2 ">
                <i class="fa fa-user mr-2" aria-hidden="true"></i>
                Edit Profile
            </a>
            <div class="line"></div>
            <a class="theme-btn bg-danger btn-sm w-100 " data-testid="log-out" href="{{ route('admin-logout') }}">
                <span class="mr-2 ">Logout</span>
                <i class="fa-solid fa-arrow-right-from-bracket "></i>
            </a>


        </div>
    </div>
</div>
