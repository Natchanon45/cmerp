<?php echo form_open(get_uri("items/save"), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
<div id="items-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">

        <input type="hidden" name="id" id="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="oid" id="oid" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="is_duplicate" id="is_duplicate" value="0" />

        <?php if ($model_info->id): ?>
            <div class="form-group">
                <div class="col-md-12 text-off">
                    <?php echo lang('item_edit_instruction'); ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="form-group">
            <label for="item_code" class="col-md-3"><?php echo lang('stock_item_code'); ?></label>
            <div class="col-md-9">
                <?php
                    echo form_input(array(
                        "id" => "item_code",
                        "name" => "item_code",
                        "value" => $model_info->item_code,
                        "class" => "form-control",
                        "placeholder" => lang('stock_item_code'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    ));
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="title" class=" col-md-3">
                <?php echo lang('stock_item_name'); ?>
            </label>
            <div class="col-md-9">
                <?php
                echo form_input(
                    array(
                        "id" => "title",
                        "name" => "title",
                        "value" => $model_info->title,
                        "class" => "form-control validate-hidden",
                        "placeholder" => lang('title'),
                        "autofocus" => true,
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    )
                );
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="description" class="col-md-3">
                <?php echo lang('description'); ?>
            </label>
            <div class=" col-md-9">
                <?php
                echo form_textarea(
                    array(
                        "id" => "description",
                        "name" => "description",
                        "value" => $model_info->description ? $model_info->description : "",
                        "class" => "form-control",
                        "placeholder" => lang('description'),
                        "data-rich-text-editor" => true
                    )
                );
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="category_id" class=" col-md-3">
                <?php echo lang('category'); ?>
            </label>
            <div class=" col-md-9">
                <?php
                echo form_dropdown("category_id", $categories_dropdown, $model_info->category_id, "class='select2 validate-hidden' id='category_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="account_id" class="col-md-3">
                <?php echo lang("account_category"); ?>
            </label>
            <div class="col-md-9">
                <?php echo form_input(array(
                    "id" => "account_id",
                    "name" => "account_id",
                    "value" => $model_info->account_id ? $model_info->account_id : null,
                    "class" => "form-control",
                    "placeholder" => lang('account_category'),
                    "readonly" => false
                )); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="unit_type" class=" col-md-3">
                <?php echo lang('unit_type'); ?>
            </label>
            <div class="col-md-9">
                <?php
                echo form_input(
                    array(
                        "id" => "unit_type",
                        "name" => "unit_type",
                        "value" => $model_info->unit_type,
                        "class" => "form-control",
                        "placeholder" => lang('unit_type') . ' (Ex: hours, pc, etc.)'
                    )
                );
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="item_rate" class=" col-md-3">
                <?php echo lang('rate'); ?>
            </label>
            <div class="col-md-9">
                <?php
                echo form_input(
                    array(
                        "id" => "item_rate",
                        "name" => "item_rate",
                        "value" => $model_info->rate ? $model_info->rate : 0,
                        "class" => "form-control",
                        "placeholder" => lang('rate'),
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    )
                );
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="barcode" class=" col-md-3">
                <?php echo lang('stock_material_barcode'); ?>
            </label>
            <div class="col-md-9">
                <?php
                echo form_input(
                    array(
                        "id" => "barcode",
                        "name" => "barcode",
                        "value" => @$model_info->barcode,
                        "class" => "form-control",
                        "placeholder" => lang('stock_material_barcode')
                    )
                );
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="noti_threshold" class="col-md-3">
                <?php echo lang('stock_item_noti_threshold'); ?>
            </label>
            <div class="col-md-9">
                <input
                    type="number" name="noti_threshold" class="form-control" min="0" step="0.0001" required 
                    name="noti_threshold" value="<?php echo @$model_info->noti_threshold; ?>" 
                    placeholder="<?php echo lang('stock_item_noti_threshold'); ?>" data-rule-required = "true"
                    data-msg-required="<?php echo lang("field_required"); ?>"/>
            </div>
        </div>

        <?php if ($this->login_user->is_admin && get_setting("module_order")): ?>
            <div class="form-group">
                <label for="show_in_client_portal" class=" col-md-3 col-xs-5 col-sm-4">
                    <?php echo lang('show_in_client_portal'); ?>
                </label>
                <div class=" col-md-9 col-xs-7 col-sm-8">
                    <?php
                    echo form_checkbox("show_in_client_portal", "1", $model_info->show_in_client_portal ? true : false, "id='show_in_client_portal'");
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <div class="col-md-12 row pr0">
                <?php
                $this->load->view("includes/file_list", array("files" => $model_info->files, "image_only" => true));
                ?>
            </div>
        </div>

        <?php $this->load->view("includes/dropzone_preview"); ?>

    </div>

    <div class="modal-footer">
        <button class="btn btn-default upload-file-button pull-left btn-sm round" type="button" style="color:#7988a2">
            <i class="fa fa-camera"></i>
            <?php echo lang("upload_image"); ?>
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">
            <span class="fa fa-close"></span>
            <?php echo lang('close'); ?>
        </button>
        <?php if (@$model_info->id): ?>
            <button type="button" class="btn btn-primary" onclick="javascript:duplicate();">
                <span class="fa fa-check-circle"></span>
                <?php echo lang('duplicate'); ?>
            </button>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
            <?php echo lang('save'); ?>
        </button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    function duplicate() {
        jQuery('#id').val(0);
        jQuery('#is_duplicate').val(1);
        jQuery('#item-form').submit();
    }

    $(document).ready(function () {
        var uploadUrl = "<?php echo get_uri("items/upload_file"); ?>";
        var validationUri = "<?php echo get_uri("items/validate_items_file"); ?>";

        var dropzone = attachDropzoneWithForm("#items-dropzone", uploadUrl, validationUri);

        $("#item-form").appForm({
            onSuccess: function (result) {
                if (window.refreshAfterUpdate) {
                    window.refreshAfterUpdate = false;
                    location.reload();
                } else {
                    $("#item-table").appTable({ newData: result.data, dataId: result.id });
                }
            }
        });

        $("#item-form .select2").select2();
        $('#account_id').select2({ data: <?php echo json_encode($account_category); ?> });
    });
</script>