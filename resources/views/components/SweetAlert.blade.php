@if(session('success') || session('error') || session('warning') || session('info'))
<script>
document.addEventListener('DOMContentLoaded', function () {

    let type = "";
    let message = "";

    @if(session('success'))
        type = "success";
        message = @json(session('success'));
    @elseif(session('error'))
        type = "error";
        message = @json(session('error'));
    @elseif(session('warning'))
        type = "warning";
        message = @json(session('warning'));
    @elseif(session('info'))
        type = "info";
        message = @json(session('info'));
    @endif

    Swal.fire({
        icon: type,
        title: type.charAt(0).toUpperCase() + type.slice(1) + "!",
        text: message,
        timer: 3000,
        showConfirmButton: false
    });

});
</script>
@endif