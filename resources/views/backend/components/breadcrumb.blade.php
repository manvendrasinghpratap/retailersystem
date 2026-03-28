<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                {{ !empty($breadcrumb) && array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
            </h4>
            <div class="page-title">
                <span>
                    @if(!empty($breadcrumb) && array_key_exists('route4', $breadcrumb) && !empty($breadcrumb['route4']))
                        <a class="btn btn-warning waves-effect waves-light" href="{{ !empty($breadcrumb['route4']) ? route($breadcrumb['route4']) : url('/') }}">{{ $breadcrumb['route4Title'] ?? '' }}</a>
                    @endif
                    @if(!empty($breadcrumb) && array_key_exists('route1', $breadcrumb) && !empty($breadcrumb['route1']))
                        <a class="btn btn-success waves-effect waves-light" href="{{ !empty($breadcrumb['route1']) ? route($breadcrumb['route1']) : url('/') }}">{{ $breadcrumb['route1Title'] ?? '' }}</a>
                    @endif

                </span>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->