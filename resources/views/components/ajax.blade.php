@if(Route::has('subscription.statusUpdate'))
<script>
function  statusSwitch(data,id) {
		var selectedStatus = data ? 1:0;
		$.ajax({
			url: '{{ route("subscription.statusUpdate") }}',
			type: 'POST',
			data: {
				id: id,
				status: selectedStatus,
				_token: '{{ csrf_token() }}'
			},
			success: function(response) {
				Swal.fire({
				icon: 'success',
				title: 'Success!',
				text: response.message,
				timer: 2000,
				showConfirmButton: false
				}).then(function() {
				location.reload();
				});
			},
			error: function(xhr) {
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
function changeStatus(data,id, url = '') 
{
    alert(id);
            var selectedStatus = data ? 1:0;
            if(url) {
                var updateUrl = url;
            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    id: customer_id,
                    status: new_status,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                    }).then(function() {
                    location.reload();
                    });
                },
                error: function(xhr) {
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
                    ).then(function() {
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

            
            
</script>
