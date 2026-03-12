<table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
     <thead>
        <tr>
           <th>SNo.</th>
           <th>@lang('translation.paymentmethod')</th>
           <th>@lang('translation.amount') @lang('translation.b_ngn')</th>
           <th>@lang('translation.createdby')</th>
           <th>@lang('translation.transactiondatetime')</th>
        </tr>
     </thead>
 <tbody>
         @foreach($subscriptionPaymentDetails as $details)
         <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $details->payment_method_name }}</td>
            <td>{{$details->amount}}</td>
            <td>{{$details->user->name}}</td>
            <td>{{ App\Helpers\Settings::getFormattedDatetime($details->created_at)}}</td>
         </tr>
         @endforeach
 </tbody>
</table>