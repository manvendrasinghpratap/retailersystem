<!-- JAVASCRIPT -->
@php($cssRefresh = Config::get('app.css_refresh'))
<script src="{{ asset('assets/backend/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/backend/libs/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/backend/libs/feather-icons/feather-icons.min.js') }}"></script>
<script src="{{ asset('assets/backend/libs/metismenu/metismenu.min.js') }}"></script>
@yield('script')
@stack('scripts')
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="{{ asset('assets/backend/js/customvalidatation.js?id='.$cssRefresh) }}"></script>
{{-- <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script> --}}
<script src="{{ asset('assets/backend/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/backend/js/common.js?id='.$cssRefresh) }}"></script>
<script src="{{ asset('assets/backend/js/pages/form-validation.init.js') }}"></script>