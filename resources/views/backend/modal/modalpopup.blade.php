<div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style=" max-width: 90% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="printwarehouseslip" class="modal fade bs-example-modal-center" tabindex="-1" role="dialog" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style=" max-width: 80% !important;">
        <div class="modal-content outprint" id="outprint">
            <div class="modal-header">
                <h5 class="modal-title"></h5><a href="javascript:void(0)" class="printpage right"><i
                        style="color:rgb(242, 204, 13)" class="fas fa-print action-btn"></i></a>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!----- used in food product -->
<div class="modal fade" id="foodproductpopup" role="dialog" aria-labelledby="foodproductpopupTitle"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog " style=" max-width: 1100px !important;">
        <div class="modal-content foodproductpopupcontent">
            <div class="modal-header">
                <h5 class="modal-title" id="foodproductpopupTitle"></h5>
                <button type="button" class="btn-close closeandreloaditems" data-bs-dismiss="modal-" aria-label="Close"></button>
            </div>
            <div class="modal-body foodproductpopupmodalbody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light closeandreloaditems" data-bs-dismiss="modal-">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!----- Changed Password start------------>
@if( (request()->route()->getName() != 'administrator.editPassword') && (request()->route()->getName() != 'staff.add') && (request()->route()->getName() != 'staff.edit'))
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="changepasswordform" id="changepasswordform">
                        <input type="hidden" name="changepassworduserid" id="changepassworduserid" value="" />
						<x-text-input name="password" label="New Password" value="" id="password" required class="password form-control" mainrows="12"/>
						<x-text-input name="password_confirmation" label="Confirm New Password" id="password_confirmation" value="" required class="password form-control" mainrows="12"/>                        
                    </form>
                </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary saveaccountpassword">{{__('translation.save')}} </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('translation.close')}}</button>
                </div>
            </div>
        </div>
    </div>
@endif    
<!----- Changed Password End------------>

<!----- paymenthistory modal start------------>
    <div class="modal fade" id="getsubscriptionpricemodalpopup" tabindex="-1" aria-labelledby="getsubscriptionpricemodalpopupLabel" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog seventyper" data-keyboard="false" data-backdrop="static">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="getsubscriptionpricemodalpopupLabel">@lang('translation.paymenthistory')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body showsubscriptionpriceinmodalpopup">
                </div>
                <div class="modal-footer">
					 <x-href-input name="Close" label="Close" href="javascript::void(0);" class="btn btn-secondary" data-bs-dismiss="modal"/>
                </div>
            </div>
        </div>
    </div>
<!----- paymenthistory modal End------------>
<!----- Update payment modal Begin------------>

    <div class="modal fade" id="updatepaymentstatus" tabindex="-1" aria-labelledby="updatepaymentstatusLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" data-keyboard="false" data-backdrop="static">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updatepaymentstatusLabel">{{__('translation.updatepaymentstatus')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="updatepaymentstatusform" id="updatepaymentstatusform">
                            <input type="hidden" class = "payment_status_order_id" name="payment_status_order_id" id="payment_status_order_id" value="" />     
                            <x-select-dropdown name="payment_status_update" id="payment_status_update" :label="__('translation.payment') . ' ' . __('translation.status')" :options="config('constants.payment_status')" :selected="old('payment_status', '')" class="payment_status-form-control" mainrows="12" />
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary updateorderpaymentstatus">{{__('translation.update')}} </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
    </div>
<!----- Update payment modal end------------>

<!----- Update order modal Begin------------>

    <div class="modal fade" id="updateorderstatus" tabindex="-1" aria-labelledby="updateorderstatusLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" data-keyboard="false" data-backdrop="static" style="max-width:50%; max-height:70%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateorderstatusLabel">{{__('translation.updateorder')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="updateorderstatusform" id="updateorderstatusform">
                            <input type="hidden" class = "order_status_order_id" name="order_status_order_id" id="order_status_order_id" value="" />     
                            <input type="hidden" class = "order_customer_id" name="order_customer_id" id="order_customer_id" value="" />  
                            {{-- <input type="hidden" class = "delivery_staff_id" name="delivery_staff_id" id="delivery_staff_id" value="23456" />   --}}
                            <input type="hidden" class = "orderstatushidden" name="orderstatushidden" id="orderstatushidden" value="" />  
                             <x-select-dropdown name="orderstatus" id="orderstatus" :label="__('translation.order_status')" :options="config('constants.order_status')" class="order_status-form-control" mainrows="12" :disabled="true" /> 
                            <x-date-input name="delivery_date" :label="__('translation.delivery_date')" value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr " mainrows="12"/>  
                            <x-select-dropdown name="delivery_option" id="delivery_option" :label="__('translation.delivery_option')" :options="config('constants.delivery_option')"  class="payment_status-form-control" mainrows="12"  required />
                            <div id="delivery_option_error" class="invalid-feedback d-none">Please select a delivery option.</div>
                            <label for="delivery_staff_id">{{ __('translation.delivery_staff') }}</label>
                            <select name="delivery_staff_id" id="delivery_staff_id" class="form-control mb-2 delivery_staff_error" required>
                            <option value=" ">{{__('translation.delivery_staff')}}</option>
                            </select>
                            <x-textarea-input name="delivery_location" id="delivery_location" :label="__('translation.delivery_location')" value="" class="form-control" rows="2" cols="20" mainrows="12" />
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary updateorderdeliverystatus">{{__('translation.update')}} </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
    </div>
<!----- Update order modal end------------>

<!----- Order Comment popup begin --->
    <div class="modal fade" id="ordercomment" tabindex="-1" aria-labelledby="orderCommentLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog" style="max-width:50%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mx-auto" id="orderCommentLabel">{{ __('translation.comment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="commentform" id="commentform">
                            <input type="hidden" name="comment_order_id" id="comment_order_id" class="comment_order_id" value="" />
                            <div class="row">
                               <x-textarea-input name="order_comment" id="order_comment" :label="__('translation.comment')" value="{{ old('comment', $order->comment ?? '') }}" class="form-control" rows="5" cols="20" mainrows="12" />

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary saveordercomment">{{__('translation.savebutton')}}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('translation.close')}}</button>
                    </div>
                </div>
            </div>
    </div>
    <!----- Comment popup end --->

    <!----- Update order modal pending to delivered Begin------------>

    <div class="modal fade" id="update_order_status_to_delivered_popup" tabindex="-1" aria-labelledby="update_order_status_to_delivered_popupLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" data-keyboard="false" data-backdrop="static" style="max-width:50%; max-height:70%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="update_order_status_to_delivered_popupLabel">{{__('translation.updateorder')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="update_order_status_to_delivered_popupform" id="update_order_status_to_delivered_popupform">   
                            <input type="hidden" class = "orderdeliveryid" name="orderdeliveryid" id="orderdeliveryid" value="" />  
                            <input type="hidden" class = "deliveryorderid" name="deliveryorderid" id="deliveryorderid" value="" />  
                            <x-textarea-input name="delivery_instruction_pending" id="delivery_instruction_pending" :label="__('translation.delivery_instruction')" value="" class="form-control" rows="3" cols="10" mainrows="12" :disabled=true/>
                            
                             <x-select-dropdown name="deliverorderstatus" id="deliverorderstatus" :label="__('translation.order_status')" :options="config('constants.deliver_order_status')" class="order_status-form-control" mainrows="12"/> 
                             <x-select-dropdown name="deliver_to" id="deliver_to" :label="__('translation.deliver_to')" :options="config('constants.emergecyRelationship')" class="order_status-form-control" mainrows="12" /> 
                            <x-date-input name="delivered_date" :label="__('translation.delivered_date')" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days')) )}}" value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr " mainrows="12"/> 
                            <x-textarea-input name="note" id="note" :label="__('translation.notes')" value="" class="form-control" rows="2" cols="10" mainrows="12" />
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary updateorderstatusdelivery">{{__('translation.update')}} {{__('translation.order_status')}} </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('translation.close')}}</button>
                    </div>
                </div>
            </div>
    </div>
<!----- Update order modal pending to delivered  end------------>

<!----- Order Comment popup begin --->
    <div class="modal fade" id="order_status_not_confirmed_popup" tabindex="-1" aria-labelledby="order_status_not_confirmed_popupLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog" style="max-width:50%;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title mx-auto" id="order_status_not_confirmed_popupLabel">{{ __('translation.delivery_instruction') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form name="commentform" id="commentform">
                            <input type="hidden" name="comment_order_id" id="comment_order_id" class="comment_order_id" value="" />
                            <div class="row">
                               <x-textarea-input name="delivery_instruction" id="delivery_instruction" :label="__('translation.delivery_instruction')" value="" class="form-control" rows="5" cols="20" mainrows="12" :disabled="true" />

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('translation.close')}}</button>
                    </div>
                </div>
            </div>
    </div>
    <!----- Comment popup end --->
