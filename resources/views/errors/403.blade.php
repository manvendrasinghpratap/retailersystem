@extends('../backend.layouts.master-without-nav')

@section('title')
    @lang('translation.Error_403')
@endsection

@section('body')

    <body>
@endsection
    @section('content')
        <div class="my-5 pt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mb-5 pt-5">
                            <h1 class="error-title mt-5"><span>403!</span></h1>
                            <h4 class="text-uppercase mt-5">Oops! Access Restricted</h4>
                            <p class="font-size-15 mx-auto text-muted w-50 mt-4">You dont have permission to view this page. Please reach out to your admin if you need access.</p>
                            <div class="mt-5 text-center">
                                <a class="btn btn-primary waves-effect waves-light" href="{{ env('APP_URL') }}">Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end container -->
        </div>
        <!-- end content -->
    @endsection
    <script>
        setTimeout(function () {
            window.location.href = "{{ url('/') }}";
        }, 5000);
    </script>