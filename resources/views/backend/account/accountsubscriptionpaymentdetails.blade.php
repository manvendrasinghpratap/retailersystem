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
  @php($i=0)
 @foreach($subscriptionPaymentDetails as $details)
 @php($method = '')
 @if($details->payment_method == 1)
  @php($method = 'POS')
 @elseif($details->payment_method == 2)
 @php($method = 'Transfer')
 @endif
  <tr>
   <td>{{++$i}}</td>
   <td>{{$method}}</td>
   <td>{{$details->amount}}</td>
   <td>{{$details->user->name}}</td>
   <td>{{ App\Helpers\Settings::getFormattedDatetime($details->created_at)}}</td>
  </tr>
@endforeach
 </tbody>
</table>