<?php echo form_open(get_uri("receipts/save_item"), array("id" => "receipt-item-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">

    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

    <?php if ($receipt_id) { ?>
        <input type="hidden" name="receipt_id" value="<?php echo $receipt_id; ?>" />
        <input type="hidden" name="add_new_item_to_library" value="" id="add_new_item_to_library" />


        <?php if (empty($model_info->po_id)) { ?>
            <div class="form-group">
                <input type="hidden" name="po_id" value="<?php echo $model_info->po_id ?>"  />
                <label for="receipt_item_title" class=" col-md-3"><?php echo lang('item'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "receipt_item_title",
                        "name" => "receipt_item_title",
                        "value" => $model_info->title,
                        "class" => "form-control validate-hidden",
                        "placeholder" => lang('select_or_create_new_item'),
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    ));
                    ?>
                    <a id="receipt_item_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
                </div>
            </div>
        <?php } else { ?>
            <div class="form-group">
                <input type="hidden" name="po_id" value="<?php echo $model_info->po_id ?>"/>
                <label for="receipt_item_title" class=" col-md-3"><?php echo lang('item'); ?></label>
                <div class="col-md-9">
                    <?php echo $model_info->title ?>
                    <a id="receipt_item_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
                </div>
            </div>

    <?php }
    } ?>


    <div class="form-group">
        <label for="receipt_item_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "receipt_item_description",
                "name" => "receipt_item_description",
                "value" => $model_info->description ? $model_info->description : "",
                "class" => "form-control",
                "placeholder" => lang('description'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>


    <div class="form-group">
        <label for="receipt_item_quantity" class=" col-md-3"><?php echo lang('quantity'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_item_quantity",
                "name" => "receipt_item_quantity",
                "value" => $model_info->quantity,
                "class" => "form-control",
                "placeholder" => lang('quantity'),
                "type" => "number",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>


    </div>

    <?php if ($receipt_id) { ?>
        <?php if (empty($model_info->po_id)) { ?>
            <div class="form-group">
                <label for="receipt_unit_type" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "receipt_unit_type",
                        "name" => "receipt_unit_type",
                        "value" => $model_info->unit_type,
                        "class" => "form-control",
                        "placeholder" => lang('unit_type') . ' (Ex: hours, pc, etc.)'
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label for="receipt_item_rate" class=" col-md-3"><?php echo lang('rate'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "receipt_item_rate",
                        "name" => "receipt_item_rate",
                        "value" => $model_info->rate ? to_decimal_format($model_info->rate) : "",
                        "class" => "form-control",
                        "placeholder" => lang('rate'),
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="form-group">
                <label for="receipt_unit_type" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo $model_info->unit_type
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label for="receipt_item_rate" class=" col-md-3"><?php echo lang('rate'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo $model_info->rate
                    ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#receipt-item-form").appForm({
            onSuccess: function(result) {
                $("#receipt-item-table").appTable({
                    newData: result.data,
                    dataId: result.id
                });
                $("#receipt-total-section").html(result.receipt_total_view);
            }
        });

        //show item suggestion dropdown when adding new item
        var isUpdate = "<?php echo $model_info->id; ?>";
        if (!isUpdate) {
            applySelect2OnItemTitle();
            applySelect2OnPoItemTitle();
        }

        //re-initialize item suggestion dropdown on request
        $("#receipt_item_title_dropdwon_icon").click(function() {
            applySelect2OnItemTitle();
        });

        $("#po_item_title_dropdwon_icon").click(function() {
            applySelect2OnPoItemTitle();
        });

    });

    function applySelect2OnItemTitle() {
        $("#receipt_item_title").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("receipts/get_receipt_item_suggestion"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function(term, page) {
                    return {
                        q: term // search term
                    };
                },
                results: function(data, page) {
                    return {
                        results: data
                    };
                }
            }
        }).change(function(e) {
            if (e.val === "+") {
                //show simple textbox to input the new item
                $("#receipt_item_title").select2("destroy").val("").focus();
                $("#add_new_item_to_library").val(1); //set the flag to add new item in library
            } else if (e.val) {
                //get existing item info
                $("#add_new_item_to_library").val(""); //reset the flag to add new item in library
                $.ajax({
                    url: "<?php echo get_uri("receipts/get_receipt_item_info_suggestion"); ?>",
                    data: {
                        item_name: e.val
                    },
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function(response) {


                        // location.reload();
                        // return true;

                        //auto fill the description, unit type and rate fields.
                        if (response && response.success) {

                            if (!$("#receipt_item_description").val()) {
                                $("#receipt_item_description").val(response.item_info.description);
                            }

                            if (!$("#receipt_unit_type").val()) {
                                $("#receipt_unit_type").val(response.item_info.unit_type);
                            }

                            if (!$("#receipt_item_rate").val()) {
                                $("#receipt_item_rate").val(response.item_info.rate);
                            }
                        }
                    }
                });
            }

        });
    }

    function applySelect2OnPoItemTitle() {
        $("#po_item_title").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("receipts/get_po_item_suggestion"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function(term, page) {
                    return {
                        q: term // search term
                    };
                },
                results: function(data, page) {
                    return {
                        results: data
                    };
                }
            }
        }).change(function(e) {
            if (e.val === "+") {
                //show simple textbox to input the new item
                $("#po_item_title").select2("destroy").val("").focus();
                $("#add_new_item_to_library").val(1); //set the flag to add new item in library
            } else if (e.val) {
                //get existing item info
                $("#add_new_item_to_library").val(""); //reset the flag to add new item in library
                $.ajax({
                    url: "<?php echo get_uri("receipts/get_po_item_suggestion"); ?>",
                    data: {
                        item_name: e.val
                    },
                    cache: false,
                    type: 'POST',
                    dataType: "json"

                });
            }

        });
    }
</script>