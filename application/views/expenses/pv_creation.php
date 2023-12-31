<style type="text/css">
    .modal-dialog {
        width: 400px;
    }
</style>

<?php
    echo form_open(
        get_uri("expenses/pv_creation_save"),
        array(
            "id" => "pv_creation_form",
            "class" => "general-form",
            "role" => "form"
        )
    );
?>

<input type="hidden" id="expenseId" name="expenseId" value="<?php echo $id; ?>">
<div class="modal-body clearfix">
    <span>คุณต้องการสร้างใบสำคัญจ่ายหรือไม่?</span>
</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-info">
        <span class="fa fa-check"></span>
        <?php echo lang("create"); ?>
    </button>

    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("close"); ?>
    </button>

    <!-- <button type="button" class="btn btn-warning" id="btnVerifyId">
        <span class="fa fa-list-alt"></span> 
        <?php // echo lang("verify_id"); ?>
    </button> -->
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        // $("#btnVerifyId").on("click", function (e) {
        //     e.preventDefault();
        //     console.log($("#expenseId").val());
        // });

        $("#pv_creation_form").appForm({
            onSuccess: function (result) {
                // console.log(result);
                
                if (result.target) {
                    window.open(result.target, "_blank");
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 501);
                }
            }
        });
    });
</script>