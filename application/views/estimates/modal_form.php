<?php


echo form_open(get_uri("estimates/save"), array("id" => "estimate-form", "class" => "general-form", "role" => "form", "method" => "post"));


//echo form_open( '', array("id" => "estimate-form", "class" => "general-form", "role" => "form")); 
?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="estimate_request_id" value="<?php echo $estimate_request_id; ?>" />

    <?php if ($is_clone) { ?>
        <input type="hidden" name="is_clone" value="1" />
        <input type="hidden" name="discount_amount" value="<?php echo $model_info->discount_amount; ?>" />
        <input type="hidden" name="discount_amount_type" value="<?php echo $model_info->discount_amount_type; ?>" />
        <input type="hidden" name="discount_type" value="<?php echo $model_info->discount_type; ?>" />
    <?php } ?>

    <?php echo $this->dao->getBombInputs() ?>


    <?php echo $gogo ?>
    <div class="form-group">
        <label for="valid_until" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "valid_until",
                "name" => "valid_until",
                "value" => $model_info->valid_until,
                "class" => "form-control",
                "placeholder" => lang('valid_until'),
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "data-rule-greaterThanOrEqual" => "#estimate_date",
                "data-msg-greaterThanOrEqual" => lang("end_date_must_be_equal_or_greater_than_start_date")
            ));
            ?>
        </div>
    </div>
    <?php if ($client_id) { ?>
        <input type="hidden" name="estimate_client_id" value="<?php echo $client_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="estimate_client_id" class=" col-md-3"><?php echo lang('client'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("estimate_client_id", $clients_dropdown, array($model_info->client_id), "class='select2 validate-hidden' id='estimate_client_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($project_id) { ?>
        <input type="hidden" name="estimate_project_id" value="<?php echo $project_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="estimate_project_id" class=" col-md-3"><?php echo lang('project'); ?></label>
            <div class="col-md-9" id="estimate-porject-dropdown-section">
                <?php
                echo form_input(array(
                    "id" => "estimate_project_id",
                    "name" => "estimate_project_id",
                    "value" => $model_info->project_id,
                    "class" => "form-control",
                    "placeholder" => lang('project')
                ));
                ?>
            </div>
        </div>
    <?php } ?>

    <div class="form-group">
        <label for="tax_id" class=" col-md-3"><?php echo lang('tax'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_dropdown("tax_id", $taxes_dropdown, array($model_info->tax_id), "class='select2 tax-select2'");
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="tax_id" class=" col-md-3"><?php echo lang('second_tax'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_dropdown("tax_id2", $taxes_dropdown, array($model_info->tax_id2), "class='select2 tax-select2'");
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="estimate_note" class=" col-md-3"><?php echo lang('note_new'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "estimate_note",
                "name" => "estimate_note",
                "value" => $model_info->note ? $model_info->note : "",
                "class" => "form-control",
                "placeholder" => lang('note_new'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>

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
        var payTypeOnload = document.getElementById("pay_type").value
        if (payTypeOnload == 'time') {
            $("#check_show").css("display", "block");
            // console.log(document.getElementById("sp_deposit").checked);
            if (document.getElementById("sp_deposit").checked == true) {
                $("#form_show").css("display", "block");
            } 
                $("#sp_deposit").click(function() {

                    var checked = document.getElementById("sp_deposit").checked

                    if (checked == true) {
                        $("#form_show").css("display", "block");
                    } else if (checked == false) {
                        $("#form_show").css("display", "none");
                    }
                });
            

        }

        $("#pay_type").click(function() {
            var val_change = document.getElementById("pay_type").value
            if (val_change == "time") {
                $("#check_show").css("display", "block");
                $("#sp_deposit").click(function() {

                    var checked = document.getElementById("sp_deposit").checked

                    if (checked == true) {
                        $("#form_show").css("display", "block");
                    } else if (checked == false) {
                        $("#form_show").css("display", "none");
                    }
                });
            } else {
                $("#check_show").css("display", "none");
            }
        });

        $("#pay_type").change(function() {
            var time = $(this).val();
            if (time == "time") {                
                document.getElementById("pay_sp").value = 12;
                
                

                $("#pay_sp").keyup(function() {
                    var value = $(this).val();
                    if (value > 12) {
                        document.getElementById("timeChecker").innerHTML = "จำนวนงวดสูงสุดที่กรอกได้คือ 12 งวด";
                        document.getElementById("btnSubmit").disabled = true;
                    } else {
                        document.getElementById("timeChecker").innerHTML = "";
                        document.getElementById("btnSubmit").disabled = false;
                    }

                });

            }

        });

        // $("#pay_sp").keyup(function(){

        //     q = {};
        //     q.val = document.getElementById("pay_sp").value;
        //     q.pay_type = $('[name="pay_type"]').val();

        //     if(q.pay_type == "time"){
        //         for(i=1;i<=q.val;i++){
        //             console.log(i);
        //         }

        //         // $.getJSON('<?php //echo get_uri('estimates/modal_form/')
                                    ?>',q,function(data){
        //         // // alert(data.vat_B);
        //         // // alert(data.after_vat);
        //         // $('.load_pay_vat').html(data.text);
        //         // });

        //     }


        //     });



        $("#estimate-form").appForm({
            onSuccess: function(result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    window.location = "<?php echo site_url('estimates/view'); ?>/" + result.id;
                }
            }
        });
        $("#estimate-form .tax-select2").select2();
        $("#estimate_client_id").select2();

        $('#estimate_project_id').select2({
            data: <?php echo json_encode($projects_suggestion); ?>
        });

        if ("<?php echo $project_id; ?>") {
            $("#estimate_client_id").select2("readonly", true);
        }

        setDatePicker("#estimate_date, #valid_until");


    });
</script>