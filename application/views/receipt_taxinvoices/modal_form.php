<?php echo form_open( get_uri("receipt_taxinvoices/save"), array( "id" => "receipt_taxinvoice-form", "class" => "general-form", "role" => "form", 'method' => 'get' )); ?>

<?php //echo $model_info->labels ; exit;?>
<div id="receipt_taxinvoices-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <?php echo $this->dao->getBombInputs( $model_info->id ,$estimate_id) ?>
        </div>
            
        
        <?php if ($is_clone || $estimate_id) { ?>
            <?php if ($is_clone) { ?>
                <input type="hidden" name="is_clone" value="1" />
            <?php } ?>
            <input type="hidden" name="discount_amount" value="<?php echo $model_info->discount_amount; ?>" />
            <input type="hidden" name="discount_amount_type" value="<?php echo $model_info->discount_amount_type; ?>" />
            <input type="hidden" name="discount_type" value="<?php echo $model_info->discount_type; ?>" />
        <?php } ?>
		
        <div class="form-group">
            <label for="receipt_taxinvoice_bill_date" class=" col-md-3"><?php echo lang('bill_date'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_input(array(
                    "id" => "receipt_taxinvoice_bill_date",
                    "name" => "receipt_taxinvoice_bill_date",
                    "value" => $model_info->bill_date ? $model_info->bill_date : get_my_local_time("Y-m-d"),
                    "class" => "form-control recurring_element",
                    "placeholder" => lang('bill_date'),
                    "autocomplete" => "off",
                    "data-rule-required" => true,
                    "data-msg-required" => lang("field_required"),
                ));
                ?>
            </div>
        </div>
		
		
		<?php echo $gogo ?>

        <?php if ($client_id && !$project_id) { ?>
            <input type="hidden" name="receipt_taxinvoice_client_id" value="<?php echo $client_id; ?>" />
        <?php } else { ?>
            
        <?php } ?>
        <?php if ($project_id) { ?>
            <input type="hidden" name="receipt_taxinvoice_project_id" value="<?php echo $project_id; ?>" />
        <?php } else { 
		
		
		
		?>
            
        <?php } ?>

        <!-- <div class="form-group">
            <label for="tax_id" class=" col-md-3"><?php echo lang('tax'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("tax_id", $taxes_dropdown, array($model_info->tax_id), "class='select2 tax-select2'");
                ?>
            </div>
        </div> -->
        <!-- <div class="form-group">
            <label for="tax_id" class=" col-md-3"><?php echo lang('second_tax'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("tax_id2", $taxes_dropdown, array($model_info->tax_id2), "class='select2 tax-select2'");
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="tax_id" class=" col-md-3"><?php echo lang('tax_deducted_at_source'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("tax_id3", $taxes_dropdown, array($model_info->tax_id3), "class='select2 tax-select2'");
                ?>
            </div>
        </div> -->
        <div class="form-group">
            <label for="receipt_taxinvoice_recurring" class=" col-md-3"><?php echo lang('recurring'); ?>  <span class="help" data-toggle="tooltip" title="<?php echo lang('cron_job_required'); ?>"><i class="fa fa-question-circle"></i></span></label>
            <div class=" col-md-9">
                <?php
                echo form_checkbox("recurring", "1", $model_info->recurring ? true : false, "id='receipt_taxinvoice_recurring'");
                ?>                       
            </div>
        </div>    
        <div id="recurring_fields" class="<?php if (!$model_info->recurring) echo "hide"; ?>"> 
            <div class="form-group">
                <label for="repeat_every" class=" col-md-3"><?php echo lang('repeat_every'); ?></label>
                <div class="col-md-4">
                    <?php
                    echo form_input(array(
                        "id" => "repeat_every",
                        "name" => "repeat_every",
                        "type" => "number",
                        "value" => $model_info->repeat_every ? $model_info->repeat_every : 1,
                        "min" => 1,
                        "class" => "form-control recurring_element",
                        "placeholder" => lang('repeat_every'),
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required")
                    ));
                    ?>
                </div>
                <div class="col-md-5">
                    <?php
                    echo form_dropdown(
                            "repeat_type", array(
                        "days" => lang("interval_days"),
                        "weeks" => lang("interval_weeks"),
                        "months" => lang("interval_months"),
                        "years" => lang("interval_years"),
                            ), $model_info->repeat_type ? $model_info->repeat_type : "months", "class='select2 recurring_element' id='repeat_type'"
                    );
                    ?>
                </div>
            </div>    

            <div class="form-group">
                <label for="no_of_cycles" class=" col-md-3"><?php echo lang('cycles'); ?></label>
                <div class="col-md-4">
                    <?php
                    echo form_input(array(
                        "id" => "no_of_cycles",
                        "name" => "no_of_cycles",
                        "type" => "number",
                        "min" => 1,
                        "value" => $model_info->no_of_cycles ? $model_info->no_of_cycles : "",
                        "class" => "form-control",
                        "placeholder" => lang('cycles')
                    ));
                    ?>
                </div>
                <div class="col-md-5 mt5">
                    <span class="help" data-toggle="tooltip" title="<?php echo lang('recurring_cycle_instructions'); ?>"><i class="fa fa-question-circle"></i></span>
                </div>
            </div>  



            <div class = "form-group hide" id = "next_recurring_date_container" >
                <label for = "next_recurring_date" class = " col-md-3"><?php echo lang('next_recurring_date'); ?>  </label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "next_recurring_date",
                        "name" => "next_recurring_date",
                        "class" => "form-control",
                        "placeholder" => lang('next_recurring_date'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    ));
                    ?>
                </div>
            </div>

        </div>  
        <div class="form-group">
            <label for="receipt_taxinvoice_note" class=" col-md-3"><?php echo lang('note_new'); ?></label>
            <div class=" col-md-9">
                <?php
                echo form_textarea(array(
                    "id" => "receipt_taxinvoice_note",
                    "name" => "receipt_taxinvoice_note",
                    "value" => $model_info->note ? $model_info->note : "",
                    "class" => "form-control",
                    "placeholder" => lang('note_new'),
                    "data-rich-text-editor" => true
                ));
                ?>
            </div>
        </div>
		
	 
       

        <?php $this->load->view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 


        <?php if ($estimate_id) { ?>
            <input type="hidden" name="estimate_id" value="<?php echo $estimate_id; ?>" />
            <div class="form-group">
                <label for="estimate_id_checkbox" class=" col-md-12">
                    <input type="hidden" name="copy_items_from_estimate" value="<?php echo $estimate_id; ?>" />
                    <?php
                    echo form_checkbox("estimate_id_checkbox", $estimate_id, true, " class='pull-left' disabled='disabled'");
                    ?>    
                    <span class="pull-left ml15"> <?php echo lang('include_all_items_of_this_estimate'); ?> </span>
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

        <?php if ($is_clone) { ?>
            <div class="form-group">
                <label for="copy_items"class=" col-md-12">
                    <?php
                    echo form_checkbox("copy_items", "1", true, "id='copy_items' disabled='disabled' class='pull-left mr15'");
                    ?>    
                    <?php echo lang('copy_items'); ?>
                </label>
            </div>
            <div class="form-group">
                <label for="copy_discount"class=" col-md-12">
                    <?php
                    echo form_checkbox("copy_discount", "1", true, "id='copy_discount' disabled='disabled' class='pull-left mr15'");
                    ?>    
                    <?php echo lang('copy_discount'); ?>
                </label>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="col-md-12">
                <?php
                $this->load->view("includes/file_list", array("files" => $model_info->files));
                ?>
            </div>
        </div>

        <?php $this->load->view("includes/dropzone_preview"); ?>
    </div>

    <div class="modal-footer">
        <button class="btn btn-default upload-file-button pull-left btn-sm round" type="button" style="color:#7988a2"><i class="fa fa-camera"></i> <?php echo lang("upload_file"); ?></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        if ("<?php echo $estimate_id; ?>" || "<?php echo $order_id; ?>") {
            RELOAD_VIEW_AFTER_UPDATE = false; //go to receipt_taxinvoice/order page
        }

        var uploadUrl = "<?php echo get_uri("receipt_taxinvoices/upload_file"); ?>";
        var validationUri = "<?php echo get_uri("receipt_taxinvoices/validate_receipt_taxinvoices_file"); ?>";

        var dropzone = attachDropzoneWithForm("#receipt_taxinvoices-dropzone", uploadUrl, validationUri);

        $("#receipt_taxinvoice-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    window.location = "<?php echo site_url('receipt_taxinvoices/view'); ?>/" + result.id;
                }
            },
            onAjaxSuccess: function (result) {
                if (!result.success && result.next_recurring_date_error) {
                    $("#next_recurring_date").val(result.next_recurring_date_value);
                    $("#next_recurring_date_container").removeClass("hide");

                    $("#receipt_taxinvoice-form").data("validator").showErrors({
                        "next_recurring_date": result.next_recurring_date_error
                    });
                }
            }
        });
        $("#receipt_taxinvoice-form .tax-select2").select2();
        $("#repeat_type").select2();

       

        setDatePicker("#receipt_taxinvoice_bill_date, #receipt_taxinvoice_due_date");

        //load all projects of selected client
        $("#receipt_taxinvoice_client_id").select2().on("change", function () {
            var client_id = $(this).val();
            if ($(this).val()) {
                $('#receipt_taxinvoice_project_id').select2("destroy");
                $("#receipt_taxinvoice_project_id").hide();
                appLoader.show({container: "#receipt_taxinvoice-porject-dropdown-section"});
                $.ajax({
                    url: "<?php echo get_uri("receipt_taxinvoices/get_project_suggestion") ?>" + "/" + client_id,
                    dataType: "json",
                    success: function (result) {
                        $("#receipt_taxinvoice_project_id").show().val("");
                        $('#receipt_taxinvoice_project_id').select2({data: result});
                        appLoader.hide();
                    }
                });
            }
        });

        $('#receipt_taxinvoice_project_id').select2({data: <?php echo json_encode($projects_suggestion); ?>});

        if ("<?php echo $project_id; ?>") {
            $("#receipt_taxinvoice_client_id").select2("readonly", true);
        }

        //show/hide recurring fields
        $("#receipt_taxinvoice_recurring").click(function () {
            if ($(this).is(":checked")) {
                $("#recurring_fields").removeClass("hide");
            } else {
                $("#recurring_fields").addClass("hide");
            }
        });

        setDatePicker("#next_recurring_date", {
            startDate: moment().add(1, 'days').format("YYYY-MM-DD") //set min date = tomorrow
        });


        $('[data-toggle="tooltip"]').tooltip();

        var defaultDue = "<?php echo get_setting('default_due_date_after_billing_date'); ?>";
        var id = "<?php echo $model_info->id; ?>";

        //disable this operation in edit mode
        if (defaultDue && !id) {
            //for auto fill the due date based on bill date
            setDefaultDueDate = function () {
                var dateFormat = getJsDateFormat().toUpperCase();

                var billDate = $('#receipt_taxinvoice_bill_date').val();
                var dueDate = moment(billDate, dateFormat).add(defaultDue, 'days').format(dateFormat);
                $("#receipt_taxinvoice_due_date").val(dueDate);

            };

            $("#receipt_taxinvoice_bill_date").change(function () {
                setDefaultDueDate();
            });

            setDefaultDueDate();
        }

    });
</script>