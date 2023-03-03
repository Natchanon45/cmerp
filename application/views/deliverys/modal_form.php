<?php


echo form_open(get_uri("deliverys/save"), array("id" => "delivery-form", "class" => "general-form", "role" => "form", "method" => "post"));


//echo form_open( '', array("id" => "delivery-form", "class" => "general-form", "role" => "form")); 
?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="delivery_request_id" value="<?php echo $delivery_request_id; ?>" />

    <?php if ($is_clone) { ?>
        <input type="hidden" name="is_clone" value="1" />
        <input type="hidden" name="discount_amount" value="<?php echo $model_info->discount_amount; ?>" />
        <input type="hidden" name="discount_amount_type" value="<?php echo $model_info->discount_amount_type; ?>" />
        <input type="hidden" name="discount_type" value="<?php echo $model_info->discount_type; ?>" />
    <?php } ?>

    <?php //echo $this->dao->getBombInputs() ?>


    <?php echo $gogo ?>
    <!-- <div class="form-group">
        <label for="valid_until" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9">
            <?php
            // echo form_input(array(
            //     "id" => "valid_until",
            //     "name" => "valid_until",
            //     "value" => $model_info->valid_until,
            //     "class" => "form-control",
            //     "placeholder" => lang('valid_until'),
            //     "autocomplete" => "off",
            //     "data-rule-required" => true,
            //     "data-msg-required" => lang("field_required"),
            //     "data-rule-greaterThanOrEqual" => "#delivery_date",
            //     "data-msg-greaterThanOrEqual" => lang("end_date_must_be_equal_or_greater_than_start_date")
            // ));
            ?>
        </div>
    </div> -->
    <?php if ($client_id) { ?>
        <input type="hidden" name="delivery_client_id" value="<?php echo $client_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="delivery_client_id" class=" col-md-3"><?php echo lang('client'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("delivery_client_id", $clients_dropdown, array($model_info->client_id), "class='select2 validate-hidden' id='delivery_client_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($project_id) { ?>
        <input type="hidden" name="delivery_project_id" value="<?php echo $project_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="delivery_project_id" class=" col-md-3"><?php echo lang('project'); ?></label>
            <div class="col-md-9" id="delivery-porject-dropdown-section">
                <?php
                echo form_input(array(
                    "id" => "delivery_project_id",
                    "name" => "delivery_project_id",
                    "value" => $model_info->project_id,
                    "class" => "form-control",
                    "placeholder" => lang('project')
                ));
                ?>
            </div>
        </div>
    <?php } ?>

    <!-- <div class="form-group">
        <label for="tax_id" class=" col-md-3"><?php //echo lang('tax'); ?></label>
        <div class="col-md-9">
            <?php
            //echo form_dropdown("tax_id", $taxes_dropdown, array($model_info->tax_id), "class='select2 tax-select2'");
            ?>
        </div>
    </div> -->
    <!-- <div class="form-group">
        <label for="tax_id" class=" col-md-3"><?php //echo lang('second_tax'); ?></label>
        <div class="col-md-9">
            <?php
            //echo form_dropdown("tax_id2", $taxes_dropdown, array($model_info->tax_id2), "class='select2 tax-select2'");
            ?>
        </div>
    </div> -->
    <!-- <div class="form-group">
        <label for="delivery_note" class=" col-md-3"><?php //echo lang('note_new'); ?></label>
        <div class=" col-md-9">
            <?php
            // echo form_textarea(array(
            //     "id" => "delivery_note",
            //     "name" => "delivery_note",
            //     "value" => $model_info->note ? $model_info->note : "",
            //     "class" => "form-control",
            //     "placeholder" => lang('note_new'),
            //     "data-rich-text-editor" => true
            // ));
            ?>
        </div>
    </div> -->

    <?php $this->load->view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?>

    <?php if ($is_clone) { ?>
        <div class="form-group">
            <label for="copy_items" class=" col-md-12">
                <?php
                echo form_checkbox("copy_items", "1", true, "id='copy_items' disabled='disabled' class='pull-left mr15'");
                ?>
                <?php echo lang('copy_items'); ?>
            </label>
        </div>
        <div class="form-group">
            <label for="copy_discount" class=" col-md-12">
                <?php
                echo form_checkbox("copy_discount", "1", true, "id='copy_discount' disabled='disabled' class='pull-left mr15'");
                ?>
                <?php echo lang('copy_discount'); ?>
            </label>
        </div>
    <?php } ?>

    <?php if ($order_id) { ?>
        <div class="form-group">
            <label for="order_id_checkbox" class=" col-md-12">
                <input type="hidden" name="copy_items_from_order" value="<?php echo $order_id; ?>" />
                <?php
                echo form_checkbox("order_id_checkbox", $order_id, true, " class='pull-left' disabled='disabled'");
                ?>
                <span class="pull-left ml15"> <?php echo lang('include_all_items_of_this_order'); ?> </span>
            </label>
        </div>
    <?php } ?>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script src="<?php echo base_url('assets/js/jquery.add-input-area.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#delivery-form").appForm({
            onSuccess: function(result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    window.location = "<?php echo site_url('deliverys/view'); ?>/" + result.id;
                }
            }
        });
        $("#delivery-form .tax-select2").select2();
        $("#delivery_client_id").select2();

        $('#delivery_project_id').select2({
            data: <?php echo json_encode($projects_suggestion); ?>
        });

        if ("<?php echo $project_id; ?>") {
            $("#delivery_client_id").select2("readonly", true);
        }

        setDatePicker("#delivery_date, #valid_until");


    });
</script>