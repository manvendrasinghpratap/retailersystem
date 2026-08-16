<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form id="approvalForm">
                @csrf
                <input type="hidden" id="modal_sale_id" name="sale_id">

                <!-- Header -->
                <div class="modal-header bg-light border-0 py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-file-signature fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="approvalModalLabel">Credit Sale Approval</h5>
                            <small class="text-muted fs-7">Invoice <span id="modal_invoice_no" class="fw-semibold text-dark">-</span></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="modalAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert"></div>

                    <!-- Customer & Sale Summary Card -->
                    <div class="card bg-light border-0 mb-4 rounded-3">
                        <div class="card-body p-3">
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3">Customer & Order Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6 col-6">
                                    <span class="d-block text-muted small">Customer Name</span>
                                    <strong id="modal_customer_name" class="text-dark fs-6">-</strong>
                                </div>
                                <div class="col-md-6 col-6">
                                    <span class="d-block text-muted small">Phone / Email</span>
                                    <span id="modal_customer_contact" class="fw-semibold text-dark">-</span>
                                </div>
                                <div class="col-md-6 col-6">
                                    <span class="d-block text-muted small">Total Amount</span>
                                    <strong id="modal_total_amount" class="text-success fs-5">-</strong>
                                </div>
                                <div class="col-md-6 col-6">
                                    <span class="d-block text-muted small">Payment Type</span>
                                    <span id="modal_payment_type" class="badge bg-warning text-dark px-2 py-1">Credit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Decision <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="action" id="actionApprove" value="approve" checked>
                                <label class="btn btn-outline-success w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2" for="actionApprove">
                                    <i class="fas fa-check-circle fs-5"></i>
                                    <span class="fw-bold">Approve</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="action" id="actionReject" value="reject">
                                <label class="btn btn-outline-danger w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2" for="actionReject">
                                    <i class="fas fa-times-circle fs-5"></i>
                                    <span class="fw-bold">Reject</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Note TextArea (Mandatory) -->
                    <div class="mb-2">
                        <label for="modal_note" class="form-label fw-semibold text-dark">
                            Approval / Rejection Note <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control rounded-3" id="modal_note" name="note" rows="3" placeholder="Enter reason or additional notes (required)..." required></textarea>
                        <div class="invalid-feedback">Please provide a note or reason before submitting.</div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 bg-light py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold" id="submitApprovalBtn">
                        <i class="fas fa-paper-plane me-1"></i> Submit Decision
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>