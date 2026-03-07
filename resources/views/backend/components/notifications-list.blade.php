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
