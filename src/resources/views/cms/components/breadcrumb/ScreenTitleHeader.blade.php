@php
    $_can_add = isset($can_add) && $can_add;
    $_can_order = isset($can_order) && $can_order;
    $_can_edit = isset($can_edit) && $can_edit;
    $_can_delete = isset($can_delete) && $can_delete;

    $_disable_header_actions = isset($disableHeaderActions) && $disableHeaderActions;
    $_auto = isset($auto) && $auto;

    $_has_actions =
        isset($children) ||
        $_can_add ||
        $_can_order ||
        $_can_edit ||
        $_can_delete ||
        isset($submit) ||
        isset($rightAction) ||
        isset($additionalActions);

    $_title_col_class = $_auto ? 'col-auto' : ($_has_actions ? 'col-lg-6' : 'col-12');
    $_actions_col_class = $_auto ? 'col-auto' : 'col-lg-6 mt-lg-0 mt-2';
@endphp

<div class="white-card position-relative">
    <div class="row align-items-center justify-content-between">
        <div class="{{ $_title_col_class }}">
            <div>
                <h2 class="screen-title-header text-left d-flex align-items-center mb-0">
                    <div class="pb-1">
                        @include('darpersocms::cms.components.breadcrumb.back-btn', [''])
                    </div>

                    <span class="pl-3">{{ $title ?? '' }}</span>
                </h2>
            </div>
        </div>

        @if ($_has_actions)
            <div class="{{ $_actions_col_class }}">
                <div class="row justify-content-end align-items-center">
                    @if (isset($children))
                        {!! $children !!}
                    @endif

                    @if ($_can_add || $_can_order || $_can_edit || $_can_delete)
                        <div class="col-12 col-md-auto text-right">
                            <div class="d-flex align-items-center flex-row flex-wrap justify-content-end ">
                                @if ($_can_add)
                                    @if (!$_disable_header_actions)
                                        <a href="{{ isset($add_url) ? $add_url : url($base_url . '/create') }}"
                                            class="btn-action lg add ml-2 mb-1 mb-sm-0"
                                            data-testid="header-btn-add-{{ $testID ?? '' }}">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    @else
                                        <div class="btn-action lg add ml-2 mb-1 mb-sm-0"
                                            data-testid="header-btn-add-{{ $testID ?? '' }}">
                                            <i class="fa-solid fa-plus"></i>
                                        </div>
                                    @endif
                                @endif

                                @if ($_can_order)
                                    @if (!$_disable_header_actions)
                                        <a href="{{ isset($order_url) ? $order_url : url($base_url . '/order') }}"
                                            class="btn-action lg view ml-2 mb-1 mb-sm-0">
                                            <i class="fa-solid fa-arrows-to-dot"></i>
                                        </a>
                                    @else
                                        <div class="btn-action lg view ml-2 mb-1 mb-sm-0">
                                            <i class="fa-solid fa-arrows-to-dot"></i>
                                        </div>
                                    @endif
                                @endif

                                @if ($_can_edit)
                                    @if (!$_disable_header_actions)
                                        <a href="{{ isset($edit_url) ? $edit_url : url($base_url . '/edit') }}"
                                            class="btn-action lg edit ml-2 mb-1 mb-sm-0"
                                             data-testid="header-btn-edit-{{ $testID ?? '' }}"
                                            >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @else
                                        <div class="btn-action lg edit ml-2 mb-1 mb-sm-0" data-testid="header-btn-edit-{{ $testID ?? '' }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </div>
                                    @endif
                                @endif

                                @if ($_can_delete)
                                    @if (!$_disable_header_actions)
                                        <form method="post"
                                            action="{{ isset($delete_url) ? $delete_url : url($base_url . '/') }}"
                                            class="bulk-delete ml-2"
                                            onsubmit="return confirm('{{ $delete_confirm_message ?? 'Are you sure you want to delete this item?' }}')">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn-action lg delete"  data-testid="header-btn-delete-{{ $testID ?? '' }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        <div class="btn-action lg delete ml-2">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (isset($additionalActions))
                        {!! $additionalActions !!}
                    @endif

                    @if (isset($submit) && $submit)
                        <div class="col-auto text-right">
                            <button type="submit" class="theme-btn sm submit"
                                data-testid="header-submit-action-{{ $testID ?? '' }}">
                                {{ $submit }}
                            </button>
                        </div>
                    @endif

                    @if (isset($rightAction) && $rightAction)
                        <div class="col-auto text-right" data-testid="header-right-action-{{ $testID ?? '' }}">
                            @if (!empty($rightAction['url']))
                                <a href="{{ $rightAction['url'] }}" class="theme-btn submit">
                                    <span>{{ $rightAction['title'] ?? 'Update' }}</span>
                                </a>
                            @else
                                <div class="theme-btn submit"
                                    @if (!empty($rightAction['onPress'])) onclick="{{ $rightAction['onPress'] }}" @endif>
                                    <span>{{ $rightAction['title'] ?? 'Update' }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if (isset($endChildren) && $endChildren)
            <div class="col-12">
                {!! $endChildren !!}
            </div>
        @endif
    </div>
</div>
