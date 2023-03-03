<?php echo form_open(get_uri("materialrequests/save_category"), array("id" => "pr-cat-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <div class="form-group">
        <label for="title" class="col-md-3"><?php echo lang('category_name'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "title",
                    "name" => "title",
                    "value" => $model_info->title,
                    "class" => "form-control",
                    "placeholder" => lang('category_name'),
                    //"data-rule-required" => true,
                    //"data-msg-required" => lang("field_required"),
                ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "description",
                "name" => "description",
                "value" => $model_info->description,
                "class" => "form-control",
                "placeholder" => lang('description'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>

    <?php /*<div class="form-group">
        <label for="created_date" class=" col-md-3"><?php echo lang('created_date'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "created_date",
                "name" => "created_date",
                "value" => $model_info->created_date?$model_info->created_date:date('Y-m-d H:i:s'),
                "class" => "form-control",
                "placeholder" => lang('created_date'),
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>*/?>

    
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#pr-cat-form").appForm({
            onSuccess: function (result) {
                $("#cat-table").appTable({newData: result.data, dataId: result.id});
                //$("#pr-total-section").html(result.pr_total_view);
                /*if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    //window.location = "<?php echo site_url('materialrequests/category_form'); ?>/" + result.id;
                    window.location = "<?php echo site_url('materialrequests/categories'); ?>";
                }*/
            }
        });
        //setDatePicker("#created_date");
    });
</script>