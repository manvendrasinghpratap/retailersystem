<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu">@lang('translation.Menu')</li>
                <li>
                    <a href="{{ route('itemOrder') }}">
                        <i data-feather="home"></i>
                        <span class="badge rounded-pill bg-soft-success text-success float-end"></span>
                        <span data-key="t-dashboard">@lang('translation.Dashboards')</span>
                    </a>
                </li>
                @if(Auth::user()->user_type < 3)
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span data-key="t-maps">@lang('translation.staffmanagement') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('staff') }}" data-key="t-v-maps">@lang('translation.staff') Listing</a></li>
                        <li><a href="{{ route('staff.add') }}" data-key="t-g-maps">@lang('translation.addstaff') </a></li>
                    </ul>
                </li>
                @endif
                <li>
                    <a href="{{ route('order.index') }}">
                        <i data-feather="package"></i>
                        <span data-key="t-dashboard">Orders</span>
                    </a>
                </li>
                @if(Auth::user()->user_type < 3)
				<li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="command"></i>
                        <span data-key="t-maps">@lang('translation.manage_inventory') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('inventory') }}" data-key="t-v-maps">@lang('translation.inventory') Listing</a></li>
                        <li><a href="{{ route('inventory.add') }}" data-key="t-g-maps">@lang('translation.add_inventory') </a></li>
                    </ul>
                </li>
                @endif
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="sliders"></i>
                        <span data-key="t-maps">@lang('translation.report') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('staffreport') }}" data-key="t-v-maps">@lang('translation.staffreport')</a></li>
						@if(Auth::user()->user_type < 3)
						<li><a href="{{ route('dailyReport') }}" data-key="t-v-maps">@lang('translation.liqourDailyReport')</a></li>
						<li><a href="{{ route('productreport') }}" data-key="t-v-maps">@lang('translation.productreport')</a></li>
						<li><a href="{{ route('stockpurchase') }}" data-key="t-v-maps">@lang('translation.stockpurchase')</a></li>
						<li><a href="{{ route('roomavailability') }}" data-key="t-v-maps">@lang('translation.roomavailability')</a></li>
						<li><a href="{{ route('report.checkin') }}" data-key="t-v-maps">@lang('translation.checkin')</a></li>
						<li><a href="{{ route('report.checkout') }}" data-key="t-v-maps">@lang('translation.checkout')</a></li>
						<li><a href="{{ route('report.dailyguest') }}" data-key="t-v-maps">@lang('translation.dailyguest')</a></li>
						<li><a href="{{ route('report.dailysales') }}" data-key="t-v-maps">@lang('translation.daily_sales')</a></li>
						@endif
                    </ul>
                </li>
                @if(Auth::user()->user_type > 0)
                <?php /*  <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="sliders"></i>
                        <span data-key="t-maps">@lang('translation.manage_inventory') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('inventory') }}" data-key="t-v-maps">@lang('translation.inventory') Listing</a></li>
                        <li><a href="{{ route('inventory.add') }}" data-key="t-g-maps">@lang('translation.add_inventory') </a></li>
                    </ul>
                </li>
                */ ?>

                @endif

                @if(Auth::user()->user_type < 3)
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="clipboard"></i>
                        <span data-key="t-maps">@lang('translation.productmanagement') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('productlist') }}" data-key="t-v-maps">@lang('translation.product') Listing</a></li>
                        <li><a href="{{ route('menu.create') }}" data-key="t-g-maps">Create Menu  </a></li>
                    </ul>
                </li>
                @endif
                @if(Auth::user()->user_type < 3)
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="airplay"></i>
                        <span data-key="t-maps">@lang('translation.laundry') @lang('translation.management')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        {{-- <li><a href="{{ route('laundry.item.add') }}" data-key="t-g-maps">@lang('translation.addlaundaryitem') </a></li> --}}
                        <li><a href="{{ route('laundry.categories') }}" data-key="t-g-maps">@lang('translation.categories') </a></li>
                        <li><a href="{{ route('laundry.items') }}" data-key="t-v-maps">@lang('translation.laundry') @lang('translation.items') @lang('translation.listing')</a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="shopping-bag"></i>
                        <span data-key="t-maps">@lang('translation.table') @lang('translation.management')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('tables.index') }}" data-key="t-v-maps">@lang('translation.table') @lang('translation.listing')</a></li>
                        <li><a href="{{ route('table.add') }}" data-key="t-g-maps">@lang('translation.addtable') </a></li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('guestcomplimentory.index') }}">
                        <i data-feather="check-square"></i>
                        <span data-key="t-maps">@lang('translation.comp') @lang('translation.mng')</span>
                    </a>
                </li>
                @endif
                <hr/>
                @if( in_array(Auth::user()->designation_id,\Config::get('constants.admin_Supervisor')))
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="key"></i>
                        <span data-key="t-maps">@lang('translation.vendors') Management </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('vendors') }}" data-key="t-v-maps">@lang('translation.vendors')</a></li>
                        <li><a href="{{ route('createvendor') }}" data-key="t-g-maps"> @lang('translation.newvendor')  </a></li>
                    </ul>
                </li>
                @endif
                @if( in_array(Auth::user()->designation_id,\Config::get('constants.admin_Supervisor')))
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="truck"></i>
                        <span data-key="t-maps">@lang('translation.itemmanagement') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('items') }}" data-key="t-v-maps">@lang('translation.items')</a></li>
                        <li><a href="{{ route('additem') }}" data-key="t-g-maps"> @lang('translation.newitem')  </a></li>
                    </ul>
                </li>
                @endif
                @if( in_array(Auth::user()->designation_id,\Config::get('constants.admin_Supervisor')))
                <li>
                    <a  title = "@lang('translation.Warehousemanagementtitle')" href="javascript: void(0);" class="has-arrow">
                        <i data-feather="book"></i>
                        <span data-key="t-maps">@lang('translation.Warehousemanagement') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a title = "@lang('translation.instock')" href="{{ route('warehousecurrentstock') }}" data-key="t-v-maps">@lang('translation.instock')</a></li>
                        <li><a title = "@lang('translation.damage_item_list')" href="{{ route('warehouse.damagelist') }}" data-key="t-v-maps">@lang('translation.damage_item_list')</a></li> 
                        <li><a title = "@lang('translation.Warehouseinventory')" href="{{ route('warehouseinventories') }}" data-key="t-v-maps">@lang('translation.Warehouseinventory')</a></li>
                        <li><a href="{{ route('warehouseinventory.add') }}" data-key="t-g-maps"> @lang('translation.warehouseinventoriesaddfood')  </a></li>
                        <li><a href="{{ route('warehouseinventory.add.drink') }}" data-key="t-g-maps"> @lang('translation.warehouseinventoriesadddrink')  </a></li>
                    </ul>
                </li>
                @endif
                @if(Auth::user()->user_type < 4 && (in_array(Auth::user()->designation_id,\Config::get('constants.requisitionform.allowedDesignation'))))
                <li>
                    <a  title = "@lang('translation.requisitionnotes')" href="javascript: void(0);" class="has-arrow">
                        <i data-feather="home"></i>
                        <span data-key="t-maps">@lang('translation.requisitionnotes') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a title = "@lang('translation.requisitionlist')" href="{{ route('requisitionnotes.index') }}" data-key="t-v-maps">@lang('translation.requisitionlist')</a></li>
                        <li><a title = "@lang('translation.drinksrequisitionnotes')" href="{{ route('requisitionnotes.drinksrequisitionnotes') }}" data-key="t-v-maps">@lang('translation.drinksrequisitionnotes')</a></li>
                        <li><a title = "@lang('translation.itemsrequisitionnotes')" href="{{ route('requisitionnotes.itemsrequisitionnotes') }}" data-key="t-g-maps"> @lang('translation.itemsrequisitionnotes')  </a></li>
                    </ul>
                </li>
                @endif
                @if(Auth::user()->user_type < 2)
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-maps">@lang('translation.guest_registration') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('room.index') }}" data-key="t-v-maps">Rooms </a></li>
                        <li><a href="{{ route('room.create') }}" data-key="t-g-maps">Create Room  </a></li>
                    </ul>
                </li>
                @endif

				@if(Auth::user()->user_type == 1)
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="layout"></i>
                        <span data-key="t-maps">@lang('translation.CMS') </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('cms.index') }}" data-key="t-v-maps">Page List</a></li>
                        <li><a href="{{ route('cms.create') }}" data-key="t-g-maps">Page Create </a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="list"></i>
                        <span data-key="t-maps">Menu</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('menu_type.index') }}" data-key="t-v-maps">Menu Type List </a></li>
                        <li><a href="{{ route('menu_type.create') }}" data-key="t-g-maps">Create Menu Type </a></li>
                        <li><a href="{{ route('menu_admin.index') }}" data-key="t-v-maps">Menu List </a></li>
                        <li><a href="{{ route('menu.create') }}" data-key="t-g-maps">Create Menu  </a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="box"></i>
                        <span data-key="t-maps">Services </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('services.index') }}" data-key="t-v-maps">Services List </a></li>
                        <li><a href="{{ route('services.create') }}" data-key="t-g-maps">Create Services  </a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-maps">Aminities </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('aminities_admin.index') }}" data-key="t-v-maps">Aminities List </a></li>
                        <li><a href="{{ route('aminities.create') }}" data-key="t-g-maps">Create Aminities  </a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-maps">Banquet </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('banquet.index') }}" data-key="t-v-maps">Banquet List </a></li>
                        <li><a href="{{ route('banquet.create') }}" data-key="t-g-maps">Create Banquet  </a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-maps">Room Types </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('room.index') }}" data-key="t-v-maps">Room Type List </a></li>
                        <li><a href="{{ route('room.create') }}" data-key="t-g-maps">Create Room Type  </a></li>
                    </ul>
                </li>

                <li>
                    <a href="/admin/users">
                        <i data-feather="settings"></i>
                        <span data-key="t-dashboard">Users</span>
                    </a>
                </li>

                <li>
                    <a href="/admin/bookings">
                        <i data-feather="settings"></i>
                        <span data-key="t-dashboard">Booking</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('order.index') }}">
                        <i data-feather="settings"></i>
                        <span data-key="t-dashboard">Order</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="award"></i>
                        <span data-key="t-maps">Event </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('events_admin.index') }}" data-key="t-v-maps">Events List </a></li>
                        <li><a href="{{ route('events.create') }}" data-key="t-g-maps">Create Events  </a></li>
                    </ul>
                </li>
                <li>
                    <a href="setting">
                        <i data-feather="settings"></i>
                        <span data-key="t-dashboard">@lang('translation.Setting')</span>
                    </a>
                </li>
				@endif
                <!--
                <li class="menu-title" data-key="t-apps">@lang('translation.Apps')</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="shopping-cart"></i>
                        <span data-key="t-ecommerce">@lang('translation.Ecommerce')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ecommerce-products" key="t-products">@lang('translation.Products')</a></li>
                        <li><a href="ecommerce-product-detail" data-key="t-product-detail">@lang('translation.Product_Detail')</a></li>
                        <li><a href="ecommerce-orders" data-key="t-orders">@lang('translation.Orders')</a></li>
                        <li><a href="ecommerce-customers" data-key="t-customers">@lang('translation.Customers')</a></li>
                        <li><a href="ecommerce-cart" data-key="t-cart">@lang('translation.Cart')</a></li>
                        <li><a href="ecommerce-checkout" data-key="t-checkout">@lang('translation.Checkout')</a></li>
                        <li><a href="ecommerce-shops" data-key="t-shops">@lang('translation.Shops')</a></li>
                        <li><a href="ecommerce-add-product" data-key="t-add-product">@lang('translation.Add_Product')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="apps-chat">
                        <i data-feather="message-square"></i>
                        <span data-key="t-chat">@lang('translation.Chat')</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="mail"></i>
                        <span data-key="t-email">@lang('translation.Email')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="apps-email-inbox" data-key="t-inbox">@lang('translation.Inbox')</a></li>
                        <li><a href="apps-email-read" data-key="t-read-email">@lang('translation.Read_Email')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="apps-calendar">
                        <i data-feather="calendar"></i>
                        <span data-key="t-calendar">@lang('translation.Calendars')</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span data-key="t-contacts">@lang('translation.Contacts')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="apps-contacts-grid" data-key="t-user-grid">@lang('translation.User_Grid')</a></li>
                        <li><a href="apps-contacts-list" data-key="t-user-list">@lang('translation.User_List')</a></li>
                        <li><a href="apps-contacts-profile" data-key="t-profile">@lang('translation.Profile')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="trello"></i>
                        <span data-key="t-tasks">@lang('translation.Tasks')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tasks-list" key="t-task-list">@lang('translation.Task_List')</a></li>
                        <li><a href="tasks-kanban" key="t-kanban-board">@lang('translation.Kanban_Board')</a></li>
                        <li><a href="tasks-create" key="t-create-task">@lang('translation.Create_Task')</a></li>
                    </ul>
                </li>

                <li class="menu-title" data-key="t-pages">@lang('translation.Pages')</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="layers"></i>
                        <span data-key="t-authentication">@lang('translation.Authentication')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="auth-login" data-key="t-login">@lang('translation.Login')</a></li>
                        <li><a href="auth-register" data-key="t-register">@lang('translation.Register')</a></li>
                        <li><a href="auth-recoverpw" data-key="t-recover-password">@lang('translation.Recover_Password')</a></li>
                        <li><a href="auth-lock-screen" data-key="t-lock-screen">@lang('translation.Lock_Screen')</a></li>
                        <li><a href="auth-logout" data-key="t-logout">@lang('translation.Logout')</a></li>
                        <li><a href="auth-confirm-mail" data-key="t-confirm-mail">@lang('translation.Confirm_Mail')</a></li>
                        <li><a href="auth-email-verification" data-key="t-email-verification">@lang('translation.Email_verification')</a></li>
                        <li><a href="auth-two-step-verification" data-key="t-two-step-verification">@lang('translation.Two_step_verification')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="file-text"></i>
                        <span data-key="t-pages">@lang('translation.Pages')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="pages-starter" key="t-starter-page">@lang('translation.Starter_Page')</a></li>
                        <li><a href="pages-maintenance" key="t-maintenance">@lang('translation.Maintenance')</a></li>
                        <li><a href="pages-comingsoon" key="t-coming-soon">@lang('translation.Coming_Soon')</a></li>
                        <li><a href="pages-timeline" key="t-timeline">@lang('translation.Timeline')</a></li>
                        <li><a href="pages-faqs" key="t-faqs">@lang('translation.FAQs')</a></li>
                        <li><a href="pages-pricing" key="t-pricing">@lang('translation.Pricing')</a></li>
                        <li><a href="pages-404" key="t-error-404">@lang('translation.Error_404')</a></li>
                        <li><a href="pages-500" key="t-error-500">@lang('translation.Error_500')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="layouts-horizontal">
                        <i data-feather="layout"></i>
                        <span data-key="t-horizontal">@lang('translation.Horizontal')</span>
                    </a>
                </li>

                <li class="menu-title mt-2" data-key="t-components">@lang('translation.Components')</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="briefcase"></i>
                        <span data-key="t-components">@lang('translation.Bootstrap')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ui-alerts" key="t-alerts">@lang('translation.Alerts')</a></li>
                        <li><a href="ui-buttons" key="t-buttons">@lang('translation.Buttons')</a></li>
                        <li><a href="ui-cards" key="t-cards">@lang('translation.Cards')</a></li>
                        <li><a href="ui-carousel" key="t-carousel">@lang('translation.Carousel')</a></li>
                        <li><a href="ui-dropdowns" key="t-dropdowns">@lang('translation.Dropdowns')</a></li>
                        <li><a href="ui-grid" key="t-grid">@lang('translation.Grid')</a></li>
                        <li><a href="ui-images" key="t-images">@lang('translation.Images')</a></li>
                        <li><a href="ui-modals" key="t-modals">@lang('translation.Modals')</a></li>
                        <li><a href="ui-offcanvas" key="t-offcanvas">@lang('translation.Offcanvas')</a></li>
                        <li><a href="ui-progressbars" key="t-progress-bars">@lang('translation.Progress_Bars')</a></li>
                        <li><a href="ui-placeholders" key="t-placeholders">@lang('translation.Placeholders')</a></li>
                        <li><a href="ui-tabs-accordions" key="t-tabs-accordions">@lang('translation.Tabs_&_Accordions')</a></li>
                        <li><a href="ui-typography" key="t-typography">@lang('translation.Typography')</a></li>
                        <li><a href="ui-video" key="t-video">@lang('translation.Video')</a></li>
                        <li><a href="ui-general" key="t-general">@lang('translation.General')</a></li>
                        <li><a href="ui-colors" key="t-colors">@lang('translation.Colors')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="gift"></i>
                        <span data-key="t-ui-elements">@lang('translation.Extended')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="extended-lightbox" data-key="t-lightbox">@lang('translation.Lightbox')</a></li>
                        <li><a href="extended-rangeslider" data-key="t-range-slider">@lang('translation.Range_Slider')</a></li>
                        <li><a href="extended-sweet-alert" data-key="t-sweet-alert">@lang('translation.Sweet_Alert') 2</a></li>
                        <li><a href="extended-session-timeout" data-key="t-session-timeout">@lang('translation.Session_Timeout')</a></li>
                        <li><a href="extended-rating" data-key="t-rating">@lang('translation.Rating')</a></li>
                        <li><a href="extended-notifications" data-key="t-notifications">@lang('translation.Notifications')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="box"></i>
                        <span class="badge rounded-pill bg-soft-danger text-danger float-end">7</span>
                        <span data-key="t-forms">@lang('translation.Forms')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="form-elements" data-key="t-form-elements">@lang('translation.Basic_Elements')</a></li>
                        <li><a href="form-validation" data-key="t-form-validation">@lang('translation.Validation')</a></li>
                        <li><a href="form-advanced" data-key="t-form-advanced">@lang('translation.Advanced_Plugins')</a></li>
                        <li><a href="form-editors" data-key="t-form-editors">@lang('translation.Editors')</a></li>
                        <li><a href="form-uploads" data-key="t-form-upload">@lang('translation.File_Upload')</a></li>
                        <li><a href="form-wizard" data-key="t-form-wizard">@lang('translation.Wizard')</a></li>
                        <li><a href="form-mask" data-key="t-form-mask">@lang('translation.Mask')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="sliders"></i>
                        <span data-key="t-tables">@lang('translation.Tables')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tables-basic" data-key="t-basic-tables">@lang('translation.Bootstrap_Basic')</a></li>
                        <li><a href="tables-datatable" data-key="t-data-tables">@lang('translation.Data_Tables')</a></li>
                        <li><a href="tables-responsive" data-key="t-responsive-table">@lang('translation.Responsive')</a></li>
                        <li><a href="tables-editable" data-key="t-editable-table">@lang('translation.Editable_Table')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="pie-chart"></i>
                        <span data-key="t-charts">@lang('translation.Charts')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="charts-apex" data-key="t-apex-charts">@lang('translation.Apex_Charts')</a></li>
                        <li><a href="charts-echart" data-key="t-e-charts">@lang('translation.E_Charts')</a></li>
                        <li><a href="charts-chartjs" data-key="t-chartjs-charts">@lang('translation.Chartjs')</a></li>
                        <li><a href="charts-knob" data-key="t-knob-charts">@lang('translation.Jquery_Knob')</a></li>
                        <li><a href="charts-sparkline" data-key="t-sparkline-charts">@lang('translation.Sparkline')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-icons">@lang('translation.Icons')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="icons-feather" data-key="t-feather">@lang('translation.Feather')</a></li>
                        <li><a href="icons-boxicons" data-key="t-boxicons">@lang('translation.Boxicons')</a></li>
                        <li><a href="icons-materialdesign" data-key="t-material-design">@lang('translation.Material_Design')</a></li>
                        <li><a href="icons-dripicons" data-key="t-dripicons">@lang('translation.Dripicons')</a></li>
                        <li><a href="icons-fontawesome" data-key="t-font-awesome">@lang('translation.Font_awesome') 5</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="map"></i>
                        <span data-key="t-maps">@lang('translation.Maps')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="maps-google" data-key="t-g-maps">@lang('translation.Google')</a></li>
                        <li><a href="maps-vector" data-key="t-v-maps">@lang('translation.Vector')</a></li>
                        <li><a href="maps-leaflet" data-key="t-l-maps">@lang('translation.Leaflet')</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="share-2"></i>
                        <span data-key="t-multi-level">@lang('translation.Multi_Level')</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="javascript: void(0);" data-key="t-level-1-1">@lang('translation.Level_1.1')</a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" data-key="t-level-1-2">@lang('translation.Level_1.2')</a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="javascript: void(0);" data-key="t-level-2-1">@lang('translation.Level_2.1')</a></li>
                                <li><a href="javascript: void(0);" data-key="t-level-2-2">@lang('translation.Level_2.2')</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>-->

            </ul>

            <!--<div class="card sidebar-alert shadow-none text-center mx-4 mb-0 mt-5">
                <div class="card-body">
                    <img src="assets/images/giftbox.png" alt="">
                    <div class="mt-4">
                        <h5 class="alertcard-title font-size-16">Unlimited Access</h5>
                        <p class="font-size-13">Upgrade your plan from a Free trial, to select ‘Business Plan’.</p>
                        <a href="#!" class="btn btn-primary mt-2">Upgrade Now</a>
                    </div>
                </div>
            </div>-->
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
