<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            {{-- HEADER --}}
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel"><i class="ri-user-add-line me-1"></i> Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- BODY --}}
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="ri-information-line me-1"></i>
                    <strong>Customer Required</strong>
                    <br>
                    This invoice is not linked to a customer. Please add customer information before processing the return.
                </div>
                <input type="hidden" id="customer_modal_sale_id">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" id="modal_invoice_no" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control" id="modal_barcode" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modal_customer_name" class="form-label">Customer Name<span class="text-danger">*</span></label>
                        <input type="text" id="modal_customer_name" class="form-control" maxlength="255" placeholder="Enter customer name">
                        <div class="invalid-feedback" id="modal_customer_name_error"></div>
                    </div>
                    {{-- Phone --}}
                    <div class="col-md-6 mb-3">
                        <label for="modal_customer_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" id="modal_customer_phone" class="form-control" maxlength="13" placeholder="Enter phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric">
                        <div class="invalid-feedback" id="modal_customer_phone_error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="modal_customer_email" class="form-label">Email</label>
                        <input type="email" id="modal_customer_email" class="form-control" maxlength="255" placeholder="Enter email address">
                        <div class="invalid-feedback" id="modal_customer_email_error"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelCustomerModal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Create & Link Customer</button>
            </div>
        </div>
    </div>
</div>