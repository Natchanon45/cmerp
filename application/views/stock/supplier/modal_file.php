<?php echo form_open(get_uri("stock/supplier_save_file"), array("id" => "file-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>" />
    <?php
        $this->load->view("includes/multi_file_uploader", array(
            "upload_url" => get_uri("stock/upload_file"),
            "validation_url" => get_uri("stock/validate_file"),
        ));
    ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default cancel-upload" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" disabled="disabled" class="btn btn-primary start-upload"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#file-form").appForm({
            onSuccess: function (result) {
                $("#supplier-file-table").appTable({reload: true});
            }
        });
    });
</script>    
