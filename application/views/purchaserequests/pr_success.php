<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">

<script>
    setTimeout(function () {
        swal({
            title: "Success",
            text: "PR Created Successfully.",
            type: "success",
            timer: 1200,
            showConfirmButton: false
        }, function () {
            window.location.href = '<?php echo get_uri('purchaserequests'); ?>';
        });
    });
</script>