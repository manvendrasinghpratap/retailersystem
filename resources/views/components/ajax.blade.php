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
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                }
            });
        }
    </script>
@endif
<script>
    function changeStatus(data, id, url = '') {
        alert(id);
        var selectedStatus = data ? 1 : 0;
        if (url) {
            var updateUrl = url;
            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    id: customer_id,
                    status: new_status,
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                }
            });
        }
    }

    ////////////////////////////// To change the  status Begin ////////////////
    $(document).ready(function () {
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
                        location.reload(); // reload after delete
                    });
                    console.log(response.message || 'Status updated successfully');
                },
                error: function (xhr) {
                    // Revert checkbox state if error occurs
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            });
        });
    });
    ////////////////////////////// To change the  status End ////////////////

    ////////////////////////////// To delete the record Begin ////////////////
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

                // AJAX delete request
                $.ajax({
                    url: routeUrl,
                    type: 'POST',
                    data: {
                        id: deleteId,
                        _token: '{{ csrf_token() }}' // CSRF token
                    },
                    success: function (response) {

                        Swal.fire(
                            'Deleted!',
                            response.message || 'Record has been deleted.',
                            'success'
                        ).then(function () {
                            location.reload(); // Reload page after delete
                        });

                    },
                    error: function () {

                        Swal.fire(
                            'Error!',
                            'Something went wrong.',
                            'error'
                        );

                    }
                });
            }
        });
    });

    //////  account Change Password Start /////

    $(document).on('click', '.saveaccountpassword', function (e) {
        e.preventDefault(); // Prevent form submission
        let changepassworduserid = $("#changepassworduserid").val().trim();
        let changepasswordrouteurl = $("#changepasswordrouteurl").val().trim();
        let password = $("#password").val().trim();
        let confirmPassword = $("#password_confirmation").val().trim();
        let isValid = true;

        $(".error_password, .error_confirm_password").text(""); // Clear previous errors

        if (password === "") {
            $(".error_password").text("Password is required.");
            isValid = false;
        }
        else if (password.length < 6) {
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
                    Swal.fire(
                        'Success!',
                        response.message || 'Record has been deleted.',
                        'success'
                    );
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            });

        }
    });



    //////  Account Change Password End /////

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
                Swal.fire(
                    'Error!',
                    'Something went wrong.',
                    'error'
                );
            }
        });
        // $('.showsubscriptionpriceinmodalpopup').html('showsubscriptionpriceinmodalpopup');


    });

    //////  Get Subscription price Start getsubscriptionprice /////

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
                $('.mainamountpayable').html(response.price)
                $('.amountpayable').html(response.price)
                $('.posandtransferamount').val(0);
                $('.calculatepayableamount').val(0);
            },
            error: function (xhr) {
                Swal.fire(
                    'Error!',
                    'Something went wrong.',
                    'error'
                );
            }
        });

    });

    //////  Get Subscription price End /////

    //////  Delete Data Start /////

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
                // if confirmed, do AJAX delete
                $.ajax({
                    url: routeUrl,
                    type: 'POST',
                    data: {
                        id: deleteId,
                        _token: '{{ csrf_token() }}' // CSRF token required
                    },
                    success: function (response) {
                        Swal.fire(
                            'Deleted!',
                            response.message || 'Record has been deleted.',
                            'success'
                        ).then(function () {
                            location.reload(); // reload after delete
                        });
                    },
                    error: function (xhr) {
                        Swal.fire(
                            'Error!',
                            'Something went wrong.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).ready(function () {
        $(document).on('change', '.acl-toggle', function () {
            let checkbox = $(this);
            let designationid = checkbox.data('designationid');
            let routeid = checkbox.data('routeid');
            let status = checkbox.is(':checked') ? 1 : 0;
            let routeUrl = checkbox.data('routeurl');

            if (!routeUrl) {
                showAlert('error', 'Error', 'Route URL missing');
                return;
            }

            $.post(routeUrl, {
                _token: "{{ csrf_token() }}",
                designationid: designationid,
                routeid: routeid,
                is_allowed: status
            })

                .done(function (res) {

                    if (!res.success) {
                        showAlert('error', 'Error', res.message || 'Update failed');
                        checkbox.prop('checked', !status); // revert
                    } else {
                        showAlert('success', 'Success', res.message || 'Updated successfully');
                    }

                })

                .fail(function () {
                    showAlert('error', 'Error', 'Server error');
                    checkbox.prop('checked', !status); // revert
                });

        });
    });

</script>