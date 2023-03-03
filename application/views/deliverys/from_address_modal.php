<?php echo form_open(get_uri("deliverys/save_item"), array("id" => "delivery-item-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="text" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="text" name="delivery_id" value="<?php echo $delivery_id; ?>" />
    <input type="text" name="add_new_item_to_library" value="" id="add_new_item_to_library" />
    <div class="form-group">
        <label for="delivery_item_title" class=" col-md-3"><?php echo lang('item'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "delivery_item_title",
                "name" => "delivery_item_title",
                "value" => $model_info->title,
                "class" => "form-control validate-hidden",
                "placeholder" => lang('select_or_create_new_item'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>