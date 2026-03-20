<!-- start page title -->
<div class="row">
    <div class="col-12">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                {{ !empty($breadcrumb) && array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
            </h4>
            @if(!empty($breadcrumb) && array_key_exists('route1', $breadcrumb))
           <div class="page-title-right">
            <span><a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(1)) }}" class="btn btn-success waves-effect waves-light" data-key="t-add_stock">@lang('translation.add_stock')</a></span>
            <span><a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(2)) }}" class="btn btn-warning waves-effect waves-light" data-key="t-sale_stock">@lang('translation.sale_stock')</a></span>
            <span><a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(3)) }}" class="btn btn-info waves-effect waves-light" data-key="t-return_stock">@lang('translation.return_stock')</a></span>
            <span><a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(4)) }}" class="btn btn-secondary waves-effect waves-light" data-key="t-damage_stock">@lang('translation.damage_stock')</a></span>
            <span><a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(5)) }}" class="btn btn-primary waves-effect waves-light" data-key="t-deduct_stock">@lang('translation.deduct_stock')</a></span>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- end page title -->
<!-- 
<span>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory') }}" class="dropdown-item" data-key="t-in_stock">@lang('translation.in_stock')</a>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(1)) }}" class="dropdown-item" data-key="t-add_stock">@lang('translation.add_stock')</a>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(2)) }}" class="dropdown-item" data-key="t-sale_stock">@lang('translation.sale_stock')</a>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(3)) }}" class="dropdown-item" data-key="t-return_stock">@lang('translation.return_stock')</a>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(4)) }}" class="dropdown-item" data-key="t-damage_stock">@lang('translation.damage_stock')</a>
                <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(5)) }}" class="dropdown-item" data-key="t-deduct_stock">@lang('translation.deduct_stock')</a>
            </span> -->
