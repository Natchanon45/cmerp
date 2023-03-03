<?php echo form_open(get_uri("purchaserequests/save"), array("id" => "pr-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <?php if ($is_clone) { ?>
        <input type="hidden" name="is_clone" value="1" />
    <?php } ?>
	
	 <?php echo $this->dao->getBombInputs() ?>


    <div class="form-group">
        <label for="catid" class="col-md-3"><?php echo lang('category_name'); ?></label>
        <div class="col-md-9">
             <?php
             $cats = [];
            foreach ( $categories as $cat ) {
                $cats[$cat->id] = $cat->title;
            }

            echo form_dropdown("catid", $cats, array( $model_info->catid ), "class='select2'");
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="project_name" class="col-md-3"><?php echo lang('project_name'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "project_name",
                    "name" => "project_name",
                    "value" => $model_info->project_name,
                    "class" => "form-control",
                    "placeholder" => lang('project_name'),
                    //"data-rule-required" => true,
                    //"data-msg-required" => lang("field_required"),
                ));
            ?>
            <a id="pr_project_name_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
        </div>
    </div>

    <div class="form-group">
        <label for="payment_type" class="col-md-3"><?php echo lang('payment_type'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "payment_type",
                    "name" => "payment",
                    "value" => $model_info->payment,
                    "class" => "form-control",
                    "placeholder" => lang('payment_type_placeholdere'),
                    //"data-rule-required" => true,
                    //"data-msg-required" => lang("field_required"),
                ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="credit" class="col-md-3"><?php echo lang('credit'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "credit",
                    "name" => "credit",
                    "value" => $model_info->credit,
                    "class" => "form-control",
                    "type"=>"number",
                    "data-act"=>"datepicker",
                    //"placeholder" => lang('payment_type_placeholdere'),
                    //"data-rule-required" => true,
                    //"data-msg-required" => lang("field_required"),
                ));
            ?>
        </div>
    </div>

    <?php /*<div class="form-group">
        <label for="expired" class="col-md-3"><?php echo lang('credit_expired'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "expired",
                    "name" => "expired",
                    "value" => $model_info->expired,
                    "class" => "form-control",
                    //"placeholder" => lang('payment_type_placeholdere'),
                    //"data-rule-required" => true,
                    //"data-msg-required" => lang("field_required"),
                ));
            ?>
        </div>
    </div>*/?>

    <div class="form-group">
        <label for="pr_date" class=" col-md-3"><?php echo lang('pr_date'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "pr_date",
                "name" => "pr_date",
                "value" => $model_info->pr_date,
                "class" => "form-control",
                "placeholder" => lang('pr_date'),
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>

    
    <?php if ($buyer_id) { ?>
        <input type="hidden" name="pr_buyer_id" value="<?php echo $buyer_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="pr_buyer_id" class=" col-md-3"><?php echo lang('buyer_org'); ?></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown("pr_buyer_id", $buyers_dropdown, array($model_info->buyer_id), "class='select2 validate-hidden' id='pr_buyer_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
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
        <label for="pr_note" class=" col-md-3"><?php echo lang('pr_note'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "pr_note",
                "name" => "pr_note",
                "value" => $model_info->note ? $model_info->note : "",
                "class" => "form-control",
                "placeholder" => lang('pr_note'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>

    <?php $this->load->view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#pr-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    window.location = "<?php echo site_url('purchaserequests/view'); ?>/" + result.id;
                }
            }
        });

        $("#pr-form .select2").select2();

        var isUpdate = "<?php echo $model_info->id; ?>";
        if (!isUpdate) {
            applySelect2OnProjectName();
        }
        $("#pr_project_name_dropdwon_icon").click(function () {
            applySelect2OnProjectName();
        });
        
        //setDatePicker("#expired");
        setDatePicker("#pr_date");

    });

    function applySelect2OnProjectName() {
        $("#project_name").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("projects/list_data_options"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term // search term
                    };
                },
                results: function (data, page) {
                    return {results: data};
                }
            }
        }).change(function (e) {//alert('change mtr title'); //on select an option
            if (e.val === "#") {
                $("#project_name").select2("destroy").val("").focus();
            }
            //console.log(jQuery('#project_name').val());
            //jQuery('#project_name').val(e.added.text);
            //get existing item info
            <?php /*$.ajax({
                url: "<?php echo get_uri("purchaserequests/get_material_info_suggestion"); ?>",
                data: {matrial_id: e.val},
                cache: false,
                type: 'POST',
                dataType: "json",
                success: function (response) {
                    //auto fill the description, unit type and rate fields.
                    if (response && response.success) {
                        $("#item_id").val(0);
                        $("#code").val(response.item_info.name);
                        $("#material_id").val(response.item_info.id);
                        $("#pr_item_mtr_title").val(response.item_info.name+' : '+response.item_info.production_name);
                        $("#pr_item_description").val(response.item_info.description);
                        $("#pr_unit_type").val(response.item_info.unit);
                        $("#pr_item_rate").val(0);
                    }
                }
            });*/?>
        });
    }
</script>