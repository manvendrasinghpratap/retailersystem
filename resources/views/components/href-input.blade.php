@props([
    'name',
    'label' => null,
    'href' => 'javascript:void(0)',
    'action' => 'edit',
])

<a href="{{ $href }}" id="{{ $name }}" {{ $attributes->class(['']) }}>
    @switch($action)
        @case('edit')
            <i class="fas fa-edit action-btn text-success" title="Edit"></i>
            @break

        @case('view')
            <i class="fas fa-eye action-btn text-success" title="View"></i>
            @break

        @case('pdf')
            <i class="fas fa-file-pdf action-btn text-danger" title="PDF"></i>
            @break
		@case('update')
            <i class="fas fa-sync-alt action-btn text-danger" title="Unsoldstock"></i>
            @break
		@case('lock')
            <i class="fas fa-lock action-btn text-danger" title="Expired - Valid for 2 days only"></i>
            @break	
		@case('print')
            <i class="fas fa-print action-btn text-warning" title="Print"></i>
            @break
		@case('csv')
			<i class="fas fa-file-csv action-btn text-success" title="Export CSV"></i>
            @break
        @case('login')
            <i class="fas fa-sign-in-alt action-btn text-primary" title="Login"></i>            
        @break
        @case('label')
             @if(!empty($label) && $label !='' && $label != null){{ $label }}@endif          
        @break
        @case('print_barcode')
             <i class="fas action-btn fa-print" title="Print Barcode"></i>
        @break
        @default
            <i class="fas action-btn text-success" title="View">{{ $label }}</i>
    @endswitch
</a>
