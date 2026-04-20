<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">

            <!-- Title -->
            <h4 class="mb-sm-0 font-size-18 fw-bold">
                {{ $breadcrumb['title'] ?? '' }}
            </h4>

            <div class="page-title">

                @if(!empty($breadcrumb['breadcrumb']))
                    @php
                        $currentRoute = request()->route()->getName();

                        $routeAliases = [
                            'admin.no-barcode' => ['admin.products.create'],
                            'admin.dashboard' => ['dashboard'],
                        ];
                    @endphp

                    <ol class="breadcrumb m-0">

                        @foreach($breadcrumb['breadcrumb'] as $breadcrumbItem)

                            @php
                                $routeName = $breadcrumbItem['route'] ?? '';

                                $isActive =
                                    $currentRoute == $routeName ||
                                    (isset($routeAliases[$routeName]) &&
                                        in_array($currentRoute, $routeAliases[$routeName]));
                            @endphp

                            <li class="breadcrumb-item {{ $isActive ? 'active-route' : '' }}">

                                @if(!empty($routeName))
                                    <a href="{{ route($routeName) }}" class="{{ $isActive ? 'text-white fw-bold' : '' }}">
                                        {{ $breadcrumbItem['title'] }}
                                    </a>
                                @else
                                    {{ $breadcrumbItem['title'] }}
                                @endif

                            </li>

                        @endforeach

                    </ol>

                @else

                    <span>
                        @if(!empty($breadcrumb['route4']))
                            <a class="btn btn-warning waves-effect waves-light" href="{{ route($breadcrumb['route4']) }}">
                                {{ $breadcrumb['route4Title'] ?? '' }}
                            </a>
                        @endif

                        @if(!empty($breadcrumb['route1']))
                            <a class="btn btn-success waves-effect waves-light" href="{{ route($breadcrumb['route1']) }}">
                                {{ $breadcrumb['route1Title'] ?? '' }}
                            </a>
                        @endif
                    </span>

                @endif

            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<style>
    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
        padding: 6px 8px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        background: #b8e7f9;
        color: #556ee6;
    }

    .active-route a {
        background: linear-gradient(45deg, #556ee6, #34c38f);
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(85, 110, 230, 0.25);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        color: #adb5bd;
    }
</style>