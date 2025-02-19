@extends('darpersocms::layouts/dashboard')

@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">
        <div class="white-card">
            @include('darpersocms::cms.components.breadcrumb.index', ['title' => 'Side Menu'])
            @if (count(request()->get('admin')['post_types_grouped']))
                <div class="mt-3">
                    <form id="add-column">
                        <div class="row align-items-center">
                            <div class="col-lg-3">
                                <label>Title</label>
                                <input name="title" class="form-control">
                            </div>
                            <div class="col-lg-3">
                                <label>Icon</label>
                                <input name="icon" class="form-control">
                            </div>
                            <div class="col-lg-3">
                                <label></label>
                                <div class="text-left mt-1">
                                    <button type="submit" class="btn btn-primary btn-sm px-3">Add Dropdown</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <form method="post" id="order-form">
                    @csrf
                    <ul class="nested-sortable list-unstyled m-0">
                        @foreach (request()->get('admin')['post_types_grouped'] as $group)
                            @if (!$group['title'] && !$group['icon'])
                                @foreach ($group['pages'] as $page)
                                    @if (!$page['display_name_plural'])
                                        @continue
                                    @endif
                                    <li class="sortable-menu-row  ">
                                        <input type="hidden" name="id[]" value="{{ $page['id'] }}">
                                        <input type="hidden" name="title[]" value="{{ $page['display_name_plural'] }}">
                                        <input type="hidden" name="icon[]" value="{{ $page['icon'] }}">
                                        <input type="hidden" name="parent_title[]" value="">
                                        <input type="hidden" name="parent_icon[]" value="">
                                        <div class="py-2">
                                            <i class="text-center mr-2 fa {{ $page['icon'] }}" aria-hidden="true"></i>
                                            {{ $page['display_name_plural'] }}
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="sortable-menu-row  ">
                                    <input type="hidden" name="id[]" value="">
                                    <input type="hidden" name="title[]" value="{{ $group['title'] }}">
                                    <input type="hidden" name="icon[]" value="{{ $group['icon'] }}">
                                    <input type="hidden" name="parent_title[]" value="">
                                    <input type="hidden" name="parent_icon[]" value="">

                                    <div class="py-2">
                                        <i class="text-center mr-2 fa {{ $group['icon'] }}" aria-hidden="true"></i>
                                        {{ $group['title'] }}
                                    </div>
                                    <ul>
                                        @foreach ($group['pages'] as $page)
                                            @if (!$page['display_name_plural'])
                                                @continue
                                            @endif
                                            <li class="sortable-menu-row  ">
                                                <input type="hidden" name="id[]" value="{{ $page['id'] }}">
                                                <input type="hidden" name="title[]"
                                                    value="{{ $page['display_name_plural'] }}">
                                                <input type="hidden" name="icon[]" value="{{ $page['icon'] }}">
                                                <input type="hidden" name="parent_title[]" value="">
                                                <input type="hidden" name="parent_icon[]" value="">
                                                <div class="py-2">
                                                    <i class="text-center mr-2 fa {{ $page['icon'] }}"
                                                        aria-hidden="true"></i> {{ $page['display_name_plural'] }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sm btn-primary">Update</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#add-column').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var title = form.find('[name="title"]').val();
                var icon = form.find('[name="icon"]').val();

                $('.nested-sortable').append(
                    ' <li class="sortable-menu-row  "> <input type="hidden" name="id[]" value=""> <input type="hidden" name="title[]" value="' +
                    title + '"> <input type="hidden" name="icon[]" value="' + icon +
                    '"> <input type="hidden" name="parent_title[]" value=""> <input type="hidden" name="parent_icon[]" value=""> <div class="py-2"> <i class="text-center mr-2 fa ' +
                    icon + '" aria-hidden="true"></i> ' + title + ' </div> </li>');

                form[0].reset();
            });

            $('.nested-sortable').nestedSortable({
                listType: 'ul',
                handle: 'div',
                items: 'li',
                maxLevels: 2,
                toleranceElement: '> div',
            });

            $('#order-form').on('submit', function(e) {
                $('.nested-sortable > li').each(function() {
                    var parent = $(this);
                    var parent_title = parent.find('[name="title[]"]').val();
                    var parent_icon = parent.find('[name="icon[]"]').val();
                    parent.find('ul li').each(function() {
                        var child = $(this);
                        child.find('[name="parent_title[]"]').val(parent_title);
                        child.find('[name="parent_icon[]"]').val(parent_icon);
                    });
                });
            });

        });
    </script>
@endsection
