@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}} | {{array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ request()->route()->getName() == 'admin.products.create' ? $breadcrumb['route2Title'] : ($breadcrumb['route3Title'])}}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="" onkeydown="return event.key != 'Enter';" autocomplete="off">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="requisition_item_id" value="{{ request()->requisition_item_id ?? '' }}" id="requisition_item_id">
                            <input type="hidden" name="purchase_item_tracking_barcode" value="{{ request()->purchase_item_tracking_barcode ?? '' }}" id="purchase_item_tracking_barcode">
                            <x-text-input id="barcode" name="barcode" label="Barcode" class="barcode" required placeholder="Scan barcode here" autofocus maxlength="15" />
                        </div>
                    </form>
                    <!-- end card -->
                </div>
                <!-- end col -->
            </div>
        </div>
    </div>
    </div>
    <!-- end row -->

@endsection
@section('script')
    <script>
        document.addEventListener('click', () => {
            document.getElementById('barcode').focus();
        });

        document.getElementById('barcode').addEventListener('keydown', function (e) {

            if (e.key !== 'Enter' && e.key !== 'Tab') {
                return;
            }

            e.preventDefault();

            let barcode = this.value.trim();
            let routeName = "{{ request()->route()->getName() }}";
            let returnRoute = "{{ url()->previous() }}";
            let requisition_item_id = document.getElementById('requisition_item_id').value.trim();
            let purchase_item_tracking_barcode = document.getElementById('purchase_item_tracking_barcode').value.trim();

            // Validate Requisition Item ID
            if (!requisition_item_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Requisition Item',
                    text: 'Requisition Item ID is required.'
                }).then(() => {
                    window.location.href = returnRoute;
                });
                return;
            }
            if (!purchase_item_tracking_barcode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Purchase Item Tracking Barcode',
                    text: 'Purchase Item Tracking Barcode is required.'
                }).then(() => {
                    window.location.href = returnRoute;
                });
                return;
            }

            // Validate Barcode
            if (!barcode) {
                return;
            }

            fetch("{{ route('admin.barcode.validateBarcodeRequisitionId') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    barcode: barcode,
                    routeName: routeName,
                    requisition_item_id: requisition_item_id,
                    purchase_item_tracking_barcode: purchase_item_tracking_barcode,
                    returnRoute: returnRoute
                })
            })
                .then(async (response) => {
                    const data = await response.json();
                    // console.log(data); alert('dd'); return false;
                    // Laravel validation failed (422)
                    if (!response.ok) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: data.message || 'Validation failed.'
                        }).then(() => {

                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = returnRoute;
                            }

                        });

                        return;
                    }

                    // Invalid barcode for adjustment
                    if (data.adjustmentType > 1 && data.status === false) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Barcode',
                            text: data.message || 'This barcode is not allowed for this operation!'
                        });

                        return;
                    }

                    // Success
                    new Audio('/beep.wav').play();

                    let route = data.status
                        ? "{{ route('admin.inventory.update', 'TOKEN') }}"
                        : "{{ route('admin.products.create', 'TOKEN') }}";

                    let url = route.replace('TOKEN', encodeURIComponent(data.payload));
                    // alert(data);
                    // alert(url);
                    // return false;
                    window.location.href = url;

                })
                .catch((error) => {

                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong.'
                    }).then(() => {
                        window.location.href = returnRoute;
                    });

                });

            this.value = '';

        });
    </script>
@endsection