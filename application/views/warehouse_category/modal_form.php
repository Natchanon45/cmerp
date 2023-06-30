<?php echo form_open(get_uri("warehouse_category/dev2_postCategory"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>

<?php
$readonly = false;
if (isset($model_info->id) && $model_info->id) {
    if (!$model_info->can_update) {
        $readonly = true;
    }
}
?>

<input type="hidden" name="id" value="<?php echo isset($model_info->id) && $model_info->id ? $model_info->id : ''; ?>">
<div class="modal-body clearfix">

    <div class="form-group">
        <label for="title" class="col-md-3"><?php echo lang('warehouse_category_code'); ?></label>
        <div class="col-md-9">
            <?php echo form_input(array(
                "id" => "code",
                "name" => "code",
                "class" => "form-control",
                "placeholder" => lang('warehouse_category_code'),
                "value" => isset($model_info->location_code) && $model_info->location_code ? $model_info->location_code : '',
                "required" => true,
                "readonly" => $readonly
            )); ?>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3"><?php echo lang('warehouse_category_name'); ?></label>
        <div class="col-md-9">
            <?php echo form_input(array(
                "id" => "title",
                "name" => "title",
                "class" => "form-control",
                "placeholder" => lang('warehouse_category_name'),
                "value" => isset($model_info->location_name) && $model_info->location_name ? $model_info->location_name : '',
                "required" => true
            )); ?>
        </div>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#category-form").appForm({
            onSuccess: function(result) {
                $("#category-table").appTable(
                    { newData: result.data, dataId: result.id }
                );
            }
        });

        $("#code").focus();
    });
</script>