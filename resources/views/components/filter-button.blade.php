<button type="reset" {{ $attributes->merge(['class' => 'btn btn-secondary']) }}>{{ $slot ?? 'Reset' }}</button>

<input type="submit" class="btn btn-primary {{ $attributes->merge(['class' => 'btn btn-secondary']) }}" value="Filter" />
<a href="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'') }}" class="btn btn-secondary" value="Reset">Reset</a>