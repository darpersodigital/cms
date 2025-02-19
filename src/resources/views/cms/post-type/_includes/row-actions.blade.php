@php
    if (!isset($appends_to_query)) $appends_to_query='';
@endphp
<td>
    <div class="d-flex justify-content-end">
        @if (isset($can_view) && $can_view)
            <a href="{{ url($base_url . '/' . $row['id']) }}"
                class="btn-action view mr-2"><i class="fa-solid fa-eye"></i></a>
        @endif
        @if (isset($can_edit) && $can_edit)
            <a href="{{ url($base_url . '/' . $row['id'] . '/edit' . $appends_to_query) }}"
                class="btn-action edit mr-2"><i class="fa-solid fa-pen"></i></a>
        @endif
        @if (isset($can_delete) && $can_delete)
            <form class="row-delete d-inline-block " method="post"
                action="{{ url($base_url . '/' . $row['id'] . $appends_to_query) }}"
                onsubmit="return confirm('Are you sure?')">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn-action  delete "><i
                        class="fa-solid fa-trash-can"></i></button>
            </form>
        @endif
    </div>
</td>