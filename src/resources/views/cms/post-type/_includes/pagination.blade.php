@if ($page['server_side_pagination'])
    <div class="row  mx-0">
        <div class="col-lg-12 px-0">
            <div class="server-pagination-numbers">
                @php
                    $last_item_in_page = $rows->perPage() * $rows->currentPage();
                    $first_item_in_page = $last_item_in_page - ($rows->perPage() - 1);
                @endphp
                Showing {{ $first_item_in_page }} to
                {{ $last_item_in_page > $rows->total() ? $rows->total() : $last_item_in_page }} of
                {{ $rows->total() }} entries
            </div>
        </div>
        <div class="col-lg-12 pagination-btns position-relative text-center ">
            {{ $rows->onEachSide(1)->appends($_GET)->links() }}
        </div>
    </div>
@endif
