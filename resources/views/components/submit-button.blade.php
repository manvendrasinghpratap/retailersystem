
<div class="form-group center">
	<button type="submit" {{ $attributes->merge(['class' => 'btn btn-primary']) }} >{{ $slot ?? 'Submit' }}</button>
	<button type="reset" {{ $attributes->merge(['class' => 'btn btn-secondary']) }}>{{ $slot ?? 'Reset' }}</button>
</div>