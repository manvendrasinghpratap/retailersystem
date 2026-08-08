@extends('backend.layouts.master-horizontal')

@section('content')

    <div class="container-fluid">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h4 class="card-title mb-0">
                    Customer Returns
                </h4>

                <a href="{{ route('admin.sale-returns.create') }}" class="btn btn-primary">
                    Customer Return
                </a>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped align-middle">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Return No</th>

                                <th>Invoice No</th>

                                <th>Customer</th>

                                <th>Products</th>

                                <th>Return Amount</th>

                                <th>Refund</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($returns as $return)

                                                    <tr>

                                                        <td>
                                                            {{ $returns->firstItem() + $loop->index }}
                                                        </td>

                                                        <td>
                                                            <strong>
                                                                {{ $return->return_no }}
                                                            </strong>
                                                        </td>

                                                        <td>
                                                            {{ $return->sale->invoice_no ?? '-' }}
                                                        </td>

                                                        <td>
                                                            {{ $return->customer->name ?? 'Walk-in' }}
                                                        </td>

                                                        <td style="max-width: 250px;">

                                                            @php
                                                                $products = $return->items
                                                                    ->pluck('product.name')
                                                                    ->filter()
                                                                    ->implode(', ');
                                                            @endphp

                                                            <div style="
                                                                                                    max-width:250px;
                                                                                                    white-space:normal;
                                                                                                    overflow-wrap:anywhere;
                                                                                                    word-break:break-word;
                                                                                                ">
                                                                {{ $products ?: '-' }}
                                                            </div>

                                                        </td>

                                                        <td>
                                                            {{ __('translation.currency') }}
                                                            {{ number_format($return->total_amount, 2) }}
                                                        </td>

                                                        <td>

                                                            @if($return->refund_type === 'refund')

                                                                <span class="badge bg-warning">
                                                                    Refund
                                                                </span>

                                                            @else

                                                                <span class="badge bg-info">
                                                                    Credit Adjustment
                                                                </span>

                                                            @endif

                                                        </td>

                                                        <td>

                                                            @if($return->status === 'completed')

                                                                <span class="badge bg-success">
                                                                    Completed
                                                                </span>

                                                            @else

                                                                <span class="badge bg-danger">
                                                                    Cancelled
                                                                </span>

                                                            @endif

                                                        </td>

                                                        <td>
                                                            {{ \App\Helpers\Settings::formatDate(
                                    $return->created_at,
                                    Config::get('constants.dateformat.slashdmy')
                                ) }}
                                                        </td>

                                                        <td>

                                                            <a href="{{ route(
                                    'admin.sale-returns.show',
                                    \App\Helpers\Settings::getEncodeCode($return->id)
                                ) }}" class="btn btn-sm btn-primary">
                                                                View
                                                            </a>

                                                        </td>

                                                    </tr>

                            @empty

                                <tr>

                                    <td colspan="10" class="text-center">
                                        {{ __('translation.no_data_found') }}
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{ $returns->links() }}

            </div>

        </div>

    </div>

@endsection