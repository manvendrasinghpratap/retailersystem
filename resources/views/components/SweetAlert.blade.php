@if(session('success'))
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
    </script>
    @endif
    @if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: @json(session('success')),
      timer: 3000,
      showConfirmButton: false
    });
  });
</script>
@endif


    @if(session('error'))
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        text: "{{ session('error') }}",
        timer: 3000,
        showConfirmButton: false
    });
    </script>
@endif
@if(session('warning'))
    <script>
    Swal.fire({
        icon: 'warning',
        title: 'Oops!',
        text: "{{ session('warning') }}",
        timer: 3000,
        showConfirmButton: false
    });
    </script>
@endif

@if(session('info'))
    <script>
    Swal.fire({
        icon: 'info',
        title: 'Oops!',
        text: "{{ session('info') }}",
        timer: 3000,
        showConfirmButton: false
    });
    </script>
@endif

@if(session('redirect'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: "{{ session('redirect') }}",
    timer: 3000,
    showConfirmButton: false
});
</script>
@endif

