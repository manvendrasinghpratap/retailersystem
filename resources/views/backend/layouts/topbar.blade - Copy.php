<header id="page-topbar">
    @php
        use Illuminate\Support\Facades\File;
        use App\Models\OrderNotification;

        $avatar = Auth::user()->avatar;
        $customAvatarPath = public_path('uploads/staff/small/' . $avatar);
        $defaultAvatarPath = URL::asset('images/' . $avatar);

        if (!empty($avatar) && $avatar !== 'default.png' && File::exists($customAvatarPath)) {
            $avatarUrl = URL::asset('uploads/staff/small/' . $avatar);
        } else {
            $avatarUrl = $defaultAvatarPath;
        }

        $isAdministrator = Auth::user()->user_type == \Config::get('constants.superadmin') ? 'administrator' : 'admin';

        $unreadCount = OrderNotification::where('is_read', false)->count();
        $latestNotifications = OrderNotification::latest()->take(5)->get();
    @endphp   

    <!-- 🔔 Notification Styles -->
    

    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ url("/$isAdministrator") }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('assets/images/fav.png') }}" alt="" height="30">ssssssssssss
                    </span>
                </a>

                <a href="{{ url("/$isAdministrator") }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('assets/images/fav.png') }}" alt="" height="30">sssssssss
                    </span>
                </a>
            </div>
        </div>

        <div class="d-flex align-items-center">

            <!-- 🔔 Notification Dropdown -->
            {{-- <div class="dropdown d-inline-block me-2 notification-dropdown">
                <button type="button" class="btn header-item bg-soft-light position-relative"
                    id="notificationDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell font-size-18"></i>
                    @if($unreadCount > 0)
                        <span id="notification-count"
                            class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 shadow-sm"
                    aria-labelledby="notificationDropdown">
                    <div class="dropdown-header bg-light border-bottom py-2 px-3">
                        <strong>Notifications</strong>
                    </div>

                    <div class="list-group list-group-flush notification-list" style="max-height: 320px; overflow-y: auto;">
                        @forelse($latestNotifications as $note)
                            <a href="{{ route('customer.order.review', \App\Helpers\Settings::getEncodeCode($note->user_order_id) ?? '#') }}"
                                class="list-group-item list-group-item-action notification-item {{ $note->is_read ? '' : 'unread' }}">
                                <div class="d-flex align-items-start">
                                    <div class="icon me-3">
                                        <i class="fas fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $note->message }}</div>
                                        <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-3">
                                <small class="text-muted">No new notifications</small>
                            </div>
                        @endforelse
                    </div>

                    <div class="text-center border-top py-2">
                        <a href="{{ route('customer.orders') }}" class="text-decoration-none small fw-medium">
                            View All
                        </a>
                    </div>
                </div>
            </div> --}}
            <!-- 🔔 End Notification Dropdown -->

            <!-- 👤 User Dropdown -->
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="{{ $avatarUrl }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ Auth::user()->name }}
                        <br>
                        <small class="text-muted d-block fw-medium">
                            <i>{{ Auth::user()->designation->name ?? 'No designation' }}</i>
                        </small>
                    </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> Profile
                    </a>
                    <a class="dropdown-item" href="{{ route('editPassword') }}">
                        <i class="mdi mdi-lock font-size-16 align-middle me-1"></i> Change Password
                    </a>

                    <a class="dropdown-item" href="{{ url('/') }}">
                        <i class="mdi mdi-web font-size-16 align-middle me-1"></i> Frontend
                    </a>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="javascript:void(0);"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bx bx-power-off font-size-16 align-middle me-1"></i> Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                </div>
            </div>
            <!-- 👤 End User Dropdown -->

        </div>
    </div>
</header>

<!-- 🔁 AJAX for Live Notifications -->
@push('scripts')
<script>
$(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: "{{ route('notifications.fetch') }}",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(response) {
                console.log("🔄 Notifications refreshed:", response); // Debug log
                if (response.count > 0) {
                    $('#notification-count').text(response.count).show();
                } else {
                    $('#notification-count').hide();
                }
                $('.notification-list').html(response.notifications);
            },
            error: function(xhr, status, error) {
                console.error("⚠️ Error fetching notifications:", error);
            }
        });
    }

    // Run once immediately
    fetchNotifications();

    // Auto-refresh every 15 seconds
    setInterval(fetchNotifications, 15000);
    $('#notificationDropdown').on('click', function() {
    $.ajax({
        url: "{{ route('notifications.markRead') }}",
        method: "GET",
        success: function() {
            // Hide unread badge count
            $('#notification-count').hide();
            
            // Remove unread highlight from notification items
            $('.notification-item').removeClass('unread');
        },
        error: function(xhr, status, error) {
            console.error("Error marking notifications as read:", error);
        }
    });
});

});
</script>
@endpush

