@extends('darpersocms::layouts/dashboard')



@section('dashboard-content')
    <div class="container-fluid px-md-5 mt-5 ">

        <div class="white-card">
            <div class="row ">
                <div class="col-lg-6">
                    @include('darpersocms::cms.components.breadcrumb.index', [
                        'title' => 'SHOW ' . $page['display_name'] . '#' . $row->id,
                    ])
                </div>


                <div class="col-lg-6 text-right">
                    <div class="actions d-flex justify-content-end align-items-center">

                        <div class="isRead mr-3 {{ $row->read ? ' read ' : ' ' }}" data-id="{{ $row['id'] }}">

                            <p class="mark-unread"> <span class="mr-2">Mark as unread </span> <i
                                    class="fa-solid fa-envelope-open"></i> </p>
                            <p class="mark-read"> <span class="mr-2">Mark as read </span> <i
                                    class="fa-solid fa-envelope"></i> </p>
                        </div>

                        <div class="isStar mr-2 text-primary" data-id="{{ $row['id'] }}">
                            <div class="btn-action lg edit ">
                                @if ($row->star)
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                @else
                                    <i class="fa-regular fa-star" aria-hidden="true"></i>
                                @endif
                            </div>
                        </div>
                        @if ($page['delete'])
                            @if (request()->get('admin')['post_types'][$page['route']]['permissions']['delete'])
                                <form class="d-inline" onsubmit="return confirm('Are you sure?')" method="post"
                                    action="{{ url(config('cms_config.route_path_prefix') . '/' . $page['route'] . '/' . $row['id']) }}">
                                    @csrf
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn-action lg delete "><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>


        </div>
        <div class="white-card">
            @foreach ($page_fields as $field)
                @if ($field['name'] == 'star' || $field['name'] == 'read' || $field['hide_table'] == 1)
                    @continue
                @else
                    @include('darpersocms::cms/post-type/show-fields', ['locale' => null])
                @endif
            @endforeach

        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $('.isStar').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).find('i').toggleClass('fa-regular')
            $(this).find('i').toggleClass('fa-solid');
            $.ajax({
                url: "{{ url(config('cms_config.route_path_prefix') . '/formMessages/' . $page['route'] . '/star/') }}/" +
                    $(this).attr('data-id'),
                type: "GET",
                success: function(response) {
                    console.log("response ", response)
                },
                error: function(error) {
                    console.log("Error:", error);
                }
            })
        })

        $('.isRead').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).toggleClass('read')
            $.ajax({
                url: "{{ url(config('cms_config.route_path_prefix') . '/formMessages/' . $page['route'] . '/read/') }}/" +
                    $(this).attr('data-id'),
                type: "GET",
                success: function(response) {
                    console.log("response ", response)
                },
                error: function(error) {
                    console.log("Error:", error);
                }
            })

        })
    </script>
@endsection
