@php
    $_can_add = isset($can_add) && $can_add;
    $_can_order = isset($can_order) && $can_order;
    $_can_edit= isset($can_edit) && $can_edit;
    $_can_delete= isset($can_delete) && $can_delete;
@endphp

<div class="white-card">
    <div class="row align-items-center">
        <div class="col-lg-6 {{  ($_can_add || $_can_order || $_can_edit || $_can_delete) ?   'col-10' : 'col-12' }}">
            <div class="">
                @if (isset($show_publish) && $show_publish &&property_exists($row,'published')  )
                    @if ($row['published'])
                        <span class="badge badge-pill badge-primary  ">Published</span>
                    @else
                        <span class="badge badge-pill badge-secondary  ">Draft</span>
                    @endif
                @endif
                @include('darpersocms::cms.components.breadcrumb.index', ['title' => $title])
             </div>
        </div>
        <div class="col-lg-6 col-2">
            <div class="d-flex justify-content-end  flex-sm-row flex-column">
                @if ($_can_add)
                    <a href="{{ url($base_url . '/create') }}" class="btn-action lg add ml-sm-2 mb-1 mb-sm-0"><i
                            class="fa-solid fa-plus"></i></a>
                @endif
                @if ($_can_order)
                    <a href="{{ url($base_url . '/order') }}" class="btn-action lg view ml-sm-2 mb-1 mb-sm-0"><i
                            class="fa-solid fa-arrows-to-dot"></i></a>
                @endif
                @if ($_can_edit)
                    <a href="{{ url($base_url . '/edit') }}" class="btn-action lg edit ml-sm-2 mb-1 mb-sm-0"><i
                            class="fa-solid fa-pen"></i></a>
                @endif
                @if ($_can_delete)
                    <form method="post" action="{{ url($base_url . '/') }}" class="bulk-delete "
                        onsubmit="return confirm('Are you sure?')">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn-action lg delete ml-sm-2"><i class="fa-solid fa-trash-can"></i></button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
