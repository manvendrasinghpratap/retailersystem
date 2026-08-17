@if(Route::has('administrator.subscription.statusUpdate'))
    <script>
        function statusSwitch(data, id) {
            var selectedStatus = data ? 1 : 0;
            $.ajax({
                url: '{{ route("administrator.subscription.statusUpdate") }}',
                type: 'POST',
                data: {
                    id: id,
                    status: selectedStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to perform this action.', 'error');
                    } else {
                        Swal.fire('Oops...', 'Something went wrong!', 'error');
                    }
                }
            });
        }
    </script>
@endif

<script>
    function changeStatus(data, id, url = '') {
        var selectedStatus = data ? 1 : 0;
        if (url) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    id: id, // Fixed: replaced customer_id
                    status: selectedStatus, // Fixed: replaced new_status
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to perform this action.', 'error');
                    } else {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                }
            });
        }
    }

    $(document).ready(function () {

        ////////////////////////////// Status Change Handler ////////////////
        $(document).on('change', '.changestatus', function () {
            const checkbox = $(this);
            const id = checkbox.data('id');
            const url = checkbox.data('url');
            const status = checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    id: id,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire(
                        'Success!',
                        response.message || 'Status updated successfully.',
                        'success'
                    ).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    checkbox.prop('checked', !status); // Revert switch state
                    if (xhr.status === 403) {
                        Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to perform this action.', 'error');
                    } else {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                }
            });
        });

        ////////////////////////////// Delete Record Handler ////////////////
        $(document).on('click', '.deleteData', function () {
            var deleteId = $(this).data('deleteid');
            var routeUrl = $(this).data('routeurl');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: routeUrl,
                        type: 'POST',
                        data: {
                            id: deleteId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire(
                                'Deleted!',
                                response.message || 'Record has been deleted.',
                                'success'
                            ).then(function () {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            if (xhr.status === 403) {
                                var errorMessage = xhr.responseJSON?.message || 'You do not have permission to perform this action.';
                                Swal.fire('Permission Denied!', errorMessage, 'error');
                            } else {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        }
                    });
                }
            });
        });

        ////////////////////////////// Change Password Handler ////////////////
        $(document).on('click', '.saveaccountpassword', function (e) {
            e.preventDefault();
            let changepassworduserid = $("#changepassworduserid").val().trim();
            let changepasswordrouteurl = $("#changepasswordrouteurl").val().trim();
            let password = $("#password").val().trim();
            let confirmPassword = $("#password_confirmation").val().trim();
            let isValid = true;

            $(".error_password, .error_password_confirmation").text("");

            if (password === "") {
                $(".error_password").text("Password is required.");
                isValid = false;
            } else if (password.length < 6) {
                $(".error_password").text("Password must be at least 6 characters.");
                isValid = false;
            }

            if (confirmPassword === "") {
                $(".error_password_confirmation").text("Confirm Password is required.");
                isValid = false;
            }

            if (password !== "" && confirmPassword !== "" && password !== confirmPassword) {
                $(".error_password_confirmation").text("Passwords do not match.");
                isValid = false;
            }

            if (isValid) {
                $('#exampleModal').modal('hide');
                $.ajax({
                    url: changepasswordrouteurl,
                    type: 'POST',
                    data: {
                        staff_id: changepassworduserid,
                        password: password,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire('Success!', response.message || 'Password updated successfully.', 'success');
                    },
                    error: function (xhr) {
                        if (xhr.status === 403) {
                            Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to perform this action.', 'error');
                        } else {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    }
                });
            }
        });

        ////////////////////////////// Modal / Dropdown Handlers ////////////////
        $(document).on('click', '.accountsubscriptionpaymentdetails', function (e) {
            let accountSubscriptionId = $(this).attr('data-subscriptionid');
            $('#getsubscriptionpricemodalpopup').modal('show');
            $.ajax({
                url: "{{ route('administrator.accountsubscriptionpaymentdetails') }}",
                type: 'POST',
                data: {
                    accountSubscriptionId: accountSubscriptionId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('.showsubscriptionpriceinmodalpopup').html(response.html);
                },
                error: function (xhr) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });

        $(document).on('change', '.getsubscriptionprice', function (e) {
            $('.posandtransferamount').val(0);
            $('.calculatepayableamount').val(0);
            $('.errormsgonexceedpaymen').html('');
            let subscriptionid = $(".subscription_id").val().trim();
            $.ajax({
                url: "{{ route('administrator.getsubscriptionprice') }}",
                type: 'POST',
                data: {
                    subscriptionid: subscriptionid,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('.subscrptionprice').val(response.price);
                    $('#mainsubscrptionprice').val(response.price);
                    $('.mainamountpayable').html(response.price);
                    $('.amountpayable').html(response.price);
                    $('.posandtransferamount').val(0);
                    $('.calculatepayableamount').val(0);
                },
                error: function (xhr) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });

        ////////////////////////////// ACL Toggle Handler ////////////////
        $(document).on('change', '.acl-toggle', function () {
            let checkbox = $(this);
            let designationid = checkbox.data('designationid');
            let routeid = checkbox.data('routeid');
            let status = checkbox.is(':checked') ? 1 : 0;
            let routeUrl = checkbox.data('routeurl');

            if (!routeUrl) {
                Swal.fire('Error', 'Route URL missing', 'error');
                return;
            }

            $.ajax({
                url: routeUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    designationid: designationid,
                    routeid: routeid,
                    is_allowed: status
                },
                success: function (res) {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Update failed', 'error');
                        checkbox.prop('checked', !status);
                    } else {
                        Swal.fire('Success', res.message || 'Updated successfully', 'success');
                    }
                },
                error: function (xhr) {
                    checkbox.prop('checked', !status);
                    if (xhr.status === 403) {
                        Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to update this.', 'error');
                    } else {
                        Swal.fire('Error', 'Server error', 'error');
                    }
                }
            });
        });

        ////////////////////////////// Master Item Form Submit ////////////////
        $('#masterItemForm').on('submit', function (e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('admin.master_items.store.ajax') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#saveMasterItemBtn').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                    $('#saveMasterItemBtn').prop('disabled', false).text('Save');
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        form.reset();
                        $('#masterItemModal').modal('hide');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    $('#saveMasterItemBtn').prop('disabled', false).text('Save');
                    if (xhr.status === 403) {
                        Swal.fire('Permission Denied!', xhr.responseJSON?.message || 'You do not have permission to create this item.', 'error');
                    } else {
                        let message = xhr.responseJSON?.message || 'Something went wrong';
                        Swal.fire('Error', message, 'error');
                    }
                }
            });
        });

    });
</script>