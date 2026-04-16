@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    {{-- ================= FILTER SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadcustomerpdf',    
                        'pdfRoute' => route('admin.customers.exportPdf'),
                        'pdfClass' => 'downloadcustomerpdf',
                        'csvId' =>'downloadcustomercsv',    
                        'csvRoute' => route('admin.customers.exportCsv'),
                        'csvClass' => 'downloadcustomercsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="name" label="Customer Name" value="{{ request('name') ?? '' }}" mainrows="3" />
                            <x-text-input name="phones" id="phones" label="Phones" value="{{ request('phones') ?? '' }}" mainrows="3" class="onlyinteger" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="$status ?? []" :selected="request('status') ?? ''" class="accountstatus" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" />
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LISTING SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.customer_name') }}</th>
                                    <th>{{ __('translation.phone') }}</th>
                                    <th>{{ __('translation.email') }}</th>
                                    <th>{{ __('translation.wallet_balance') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.createdat') }}</th>
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if(!empty($customers) && $customers->count() > 0)
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->phone }}</td>
                                            <td>{{ $customer->email }}</td>
                                            <td>{{ __('translation.currency') . number_format($customer->wallet_balance, 2) }}</td>
                                            <td>
                                                @if($customer->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ \App\Helpers\Settings::getFormattedDatetime($customer->created_at) }}</td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.customers.edit', ['id' => \App\Helpers\Settings::getEncodeCode($customer->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($customer->id) }}" data-routeurl="{{ route('admin.customers.softdelete', $customer->id) }}" />
                                            </td>

                                        </tr>

                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            No customers available
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(!empty($customers) && $customers->count() > 0)
                        <div class="right user-navigation right">
                            {!! $customers->appends(request()->input())->links() !!}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadcustomerpdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadcustomercsv', 'data-downloadroutepdf');
    });
</script>
@endsection