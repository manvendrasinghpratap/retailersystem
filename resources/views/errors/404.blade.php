@extends('backend.layouts.master-without-nav')

@section('title')
    @lang('translation.Error_404')
@endsection

@section('content')
    <div class="my-5 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mb-5 pt-5">
                        <h1 class="error-title mt-5"><span>404!</span></h1>
                        <h4 class="text-uppercase mt-5">Sorry, page not found</h4>
                        <p class="font-size-15 mx-auto text-muted w-50 mt-4">
                            The page you are looking for might have been removed or is temporarily unavailable.
                        </p>

                        <div class="mt-5 text-center">
                            <a class="btn btn-primary waves-effect waves-light" href="{{ url('/') }}">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Optional redirect after 5 seconds --}}
<script>
    setTimeout(function () {
        window.location.href = "{{ url('/') }}";
    }, 5000);
</script>