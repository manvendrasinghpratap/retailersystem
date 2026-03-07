<!-- start page title -->
<div class="row">
    <div class="col-12">
    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                {{ !empty($breadcrumb) && array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
            </h4>
            @if(!empty($breadcrumb) && array_key_exists('route2', $breadcrumb))
           <div class="page-title-right">
            <span>
                <a class="btn btn-success waves-effect waves-light"
                href="{{ !empty($breadcrumb) && array_key_exists('route2', $breadcrumb) ? route($breadcrumb['route2']) : url('/') }}">{{ !empty($breadcrumb) && array_key_exists('route2Title', $breadcrumb) ? $breadcrumb['route2Title'] : '' }}
            </a>
            </span>
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- end page title -->
