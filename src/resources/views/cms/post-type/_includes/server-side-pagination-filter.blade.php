@if ($page->server_side_pagination)
<div class="row">
    <div class="col-md-4">
        <div class="server-side-showing-nbr">
            <form>
                <div class="dataTables_length">
                    <label>
                        Show
                        <select name="per_page" class="w-auto">
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                            </option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                            </option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100
                            </option>
                            <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500
                            </option>
                            <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000
                            </option>
                        </select>
                        entries
                    </label>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8  dataTables_length text-right">
        <div class="d-flex justify-content-end align-items-center">
            <form>
                <label>
                    Search: <input type="search" name="search" value="{{ request('search') }}">
                </label>
            </form>
            <label class="filter-wrapper">
                @if (count($filters))
                    <i class="fa fa-filter ml-3"></i>
                @endif
            </label>
        </div>
    </div>
</div>
@endif