<?php echo form_open(get_uri("stock/item_save"), array("id" => "material-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <?php $this->load->view("stock/item/form"); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang('close'); ?>
    </button>
    <?php if ((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)) { ?>
        <button type="submit" class="btn btn-primary">
            <span class="fa fa-check-circle"></span>
            <?php echo lang('save'); ?>
        </button>
    <?php } ?>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $("#material-form").appForm({
            onSuccess: function (result) {
                console.log(result);
                if (result.view === "details") {
                    appAlert.success(result.message, { duration: 10000 });
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    $("#material-table").appTable({ newData: result.data, dataId: result.id });
                }
            }
        });
    });
</script>