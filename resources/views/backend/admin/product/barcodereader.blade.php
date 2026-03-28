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
            if (e.key === 'Enter') {
                e.preventDefault();

                let barcode = this.value.trim();
                let routeName = "{{ request()->route()->getName() }}";

                if (barcode !== '') {

                    fetch("{{ route('admin.barcode.validateBarcode') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ barcode: barcode, routeName: routeName })
                    })
                        .then(res => res.json())
                        .then(data => {
                            // console.log(data); return;
                            if (data.adjustmentType > 1 && data.status == false) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Invalid Barcode',
                                    text: 'This barcode is not allowed for this operation!',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }
                            new Audio('/beep.wav').play();
                            if (data.status) {
                                var route = "{{ route('admin.inventory.update', 'TOKEN') }}"
                            } else {
                                var route = "{{ route('admin.products.create', 'TOKEN') }}"
                            }
                            let url = route.replace('TOKEN', encodeURIComponent(data.payload));
                            window.location.href = url;
                        })
                        .catch(err => {
                            console.error(err);
                        });

                    this.value = '';
                }
            }
        });
    </script>
@endsection