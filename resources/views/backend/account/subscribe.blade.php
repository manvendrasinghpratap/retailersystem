@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('css')
@endsection
@section('content')
@include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{$breadcrumb['routeTitle']}}
                    </h4>
                </div>
                 @include('frontend.components.sessionmessage')
                <div class="card-body">
                        <form method="POST" action="{{ $route == 'add' ? route('store.subscribe') : route('store.subscribe') }}" enctype="multipart/form-data" class="needs-validation" novalidate onSubmit="return validatedata();">
                            <input type="hidden" value="{{$accountId}}" name="account"  id="account_id" />
                            <input type="hidden" value="" id="mainsubscrptionprice" name="mainsubscrptionprice" class="mainsubscrptionprice">
                            <input type="hidden" value="" id="subscrptionprice" name="subscrptionprice" class="subscrptionprice">
                            @csrf
                            <div class="row">
                                <x-select-dropdown name="subscription_id" label="Subscription Plan" :options="$subscriptionplan" :selected="$accountdetails->user->userdetail->state_of_origin ?? ''" class="subscription_id getsubscriptionprice" required/>
                                <x-date-input name="start_date"  label="Subscription Start Date" value="{{ $accountdetails->user->start_date ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr start_date" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year')))}}"/>    
                            </div>

                            <div class="row">
                                <div class="label vital-signs">@lang('translation.subscriptionprice')</div>
                            </div>
                            <div class="row">
                                <label>@lang('translation.subscriptionprice') : <span class="mainamountpayable"></span></label>
                            </div>
                            <div class="row">
                                <div class="label vital-signs">@lang('translation.discount')</div>
                            </div>
                            <div class="row">
                                <x-text-input name="discount" label="Discount (In Amount)" value="{{ $accountdetails->discount ?? 0 }}" required class="onlyinteger setdefaultzero nocutcopypaste calculatepayableamount" min="0" max="100"/>
                            </div>
                            <div class="row">
								<div class="label vital-signs">@lang('translation.payment') @lang('translation.status')</div>
							</div>
                            <div class="row">
                                <label>@lang('translation.amountpayable') : <span class="amountpayable"></span></label>
                            </div>
                            <div class="row">
                                <label class="error errormsgonexceedpaymen"></label>
                                <x-text-input name="pos" label="POS" value="{{ $accountdetails->user->pos ?? 0 }}"  class="posandtransferamount setdefaultzero onlyinteger" required/>
                                <x-text-input name="transfer" label="Transfer" value="{{ $accountdetails->user->pos ?? 0 }}" class="posandtransferamount setdefaultzero onlyinteger" required/>
                            </div>
                            <div class="row">
                                <x-form-buttons submitText="{{$submitText}}" resetText="Cancel" url="{{route('accounts')}}" class="btn-success" />
                            </div>
                        </form>
                    <!-- end card -->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    
    <script type="text/javascript">
       /// Select2 code is written in common .js
       function validatedata(){
                var valid = true;
                var subscriptionid = $(".subscription_id").val();
                if (subscriptionid == "") {
                    $(".error_subscription_id").html("Required.").addClass("error-color").show();
                    $(".subscription_id").addClass("input-error").focus();
                    valid = false;
                } 
               let status = calculatesubsriptionamount();
               if(status == false){
                    valid = false;
               }
                return valid;
        }
    $('.setusername').keyup(function() {
        var firstname = $('#first_name').val().trim().replace(/\s+/g, '').toLowerCase();
        var cell_phone = $('#cell_phone').val().trim().replace(/\s+/g, '').toLowerCase();
        var name = firstname + cell_phone;
        var newusername = name.trim().replace(/\s+/g, '').toLowerCase();
        $('.username').val(newusername);
    });
    $('.posandtransferamount').on('keyup blur keydown', function() {
        calculatesubsriptionamount();
    });
    
    function calculatesubsriptionamount(){
        let valid = true; 
        var paymentsum = 0;
        var subscrptionprice = parseInt($('.subscrptionprice').val());
        $('.posandtransferamount').each(function() {
            $('.errormsgonexceedpaymen').html('');
            paymentsum += Number($(this).val());
        });
        if (paymentsum != subscrptionprice) {
            $('.errormsgonexceedpaymen').html('Please check the entered amount!!');
            valid = false; 
        } else {
            $('.errormsgonexceedpaymen').html('');
            valid = true; 
        }
        return valid;
    }
    $(document).ready(function() {

        $('.calculatepayableamount').on('keyup blur keydown change',function(){
            $('.posandtransferamount').val(0);
            var length = $(this).val().length;
            var mainsubscrptionprice = parseInt($('.mainsubscrptionprice').val());
            if( ($(this).val() > mainsubscrptionprice) || isNaN(mainsubscrptionprice)){
                $(this).val('');
                $('.subscrptionprice').val(mainsubscrptionprice);
                $('.amountpayable').html(mainsubscrptionprice);
            }
        });
        
        $('.calculatepayableamount').on('blur',function(){
            $('.posandtransferamount').val(0);
            var length = $(this).val().length;
            var subscrptionprice = parseInt($('.subscrptionprice').val());
            if( ($(this).val() > subscrptionprice) || isNaN(subscrptionprice)){
                $(this).val('');
                $('.amountpayable').html(subscrptionprice);
                $('.subscrptionprice').val(mainsubscrptionprice);

            }else{
                let finalamounttobepaidafterdiscount = subscrptionprice - $(this).val();
            //console.log(finalamounttobepaidafterdiscount);

                $('.subscrptionprice').val(finalamounttobepaidafterdiscount);
                $('.amountpayable').html(finalamounttobepaidafterdiscount);
            }

        });
    });

   

    </script>
@endsection
