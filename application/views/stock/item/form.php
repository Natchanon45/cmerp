
<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<?php
    $readonly = false;
    if(empty($model_info->id)) {
        $readonly = isset($can_create) && !$can_create;
    } else {
        $readonly = isset($can_update) && !$can_update;
    }
?>
<div class="form-group">
    <label for="item_code" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item_code'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
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
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
            <?php echo lang('stock_item_name'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "name",
                "name" => "name",
                "value" => $model_info->title,
                "class" => "form-control validate-hidden",
                "placeholder" => lang('title'),
                "type" => "text",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
           <a id="pr_supplier_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
        </div>
 </div>
<?php if(isset($can_read_production_name) && $can_read_production_name){?>
    <div class="form-group">
        <label for="production_name" class="<?php echo $label_column; ?>">
            <?php echo lang('stock_item_rate'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
            <?php
                echo form_input(array(
                    "id" => "production_name",
                    "name" => "production_name",
                    "value" => $model_info->rate,
                    "class" => "form-control",
                    "placeholder" => lang('stock_item_rate'),
                    "readonly" => $readonly
                ));
            ?>
        </div>
    </div>
<?php }?>
<div class="form-group">
    <label for="category_id" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item_category'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "category_id",
                "name" => "category_id",
                "value" => $model_info->category_id? $model_info->category_id: null,
                "class" => "form-control",
                "placeholder" => lang('stock_item_category'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>

<div class="form-group">
	<label for="account_id" class="<?php echo $label_column; ?>">
		<?php echo lang('account_category'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_input(
			array(
				"id" => "account_id",
				"name" => "account_id",
				"value" => $model_info->account_id ? $model_info->account_id : null,
				"class" => "form-control",
				"placeholder" => lang('account_category'),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<div class="form-group">
    <label for="description" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item_description'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_textarea(array(
                "id" => "description",
                "name" => "description",
                "value" => $model_info->description? $model_info->description: '',
                "class" => "form-control",
                "placeholder" => lang('stock_item_description'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="unit" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_iteml_unit'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "unit",
                "name" => "unit",
                "value" => $model_info->unit_type,
                "class" => "form-control",
                "placeholder" => lang('stock_item_unit'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="barcode" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item_barcode'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "barcode",
                "name" => "barcode",
                "value" => @$model_info->barcode,
                "class" => "form-control",
                "placeholder" => lang('stock_item_barcode'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="noti_threshold" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item_noti_threshold'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
        <input 
            type="number" name="noti_threshold" class="form-control" min="0" step="0.0001" required 
            name="noti_threshold" value="<?= $model_info->noti_threshold ?>" 
            placeholder="<?php echo lang('stock_item_noti_threshold'); ?>" 
            data-rule-required="true" data-msg-required="<?= lang("field_required") ?>" 
            <?php if($readonly)echo 'readonly'; ?> 
        />
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('#category_id').select2({data: <?php echo json_encode($category_dropdown); ?>});

        var isUpdate = "<?php echo $model_info->id; ?>";
        if (!isUpdate) {
            applySelectSupplierName();
        }
        
        // re-initialize item suggestion dropdown on request
        $("#pr_supplier_title_dropdwon_icon").click(function () {
            applySelectSupplierName();
            $('#name').select2('readonly', false);
        });
        
        $('#account_id').select2({ data: <?php echo json_encode($account_category); ?> });
    });

    function applySelectSupplierName() {
        $("#name").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("stock/get_item_suggestion"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term, // search term,
                        id:<?php echo intval($model_info->id); ?>,
                        item_id: parseInt(jQuery('#item_id').val())
                    };
                },
                results: function (data, page) {
                    return {results: data};
                }
            }
        }).change(function (e) { //alert('change supplier');
            if (e.val === "+") {
                //show simple textbox to input the new item
                $("#name").select2("destroy").val("").focus();
                $("#add_new_supplier_to_library").val(1); //set the flag to add new item in library

                //jQuery('#item_id').val(0);
                jQuery('#material_id').val(0);
                jQuery('#supplier_id').val(0);
                jQuery('#name').select2('readonly', false);
                jQuery('#currency').removeAttr('readonly');
                jQuery('#currency_symbol').removeAttr('readonly');
                jQuery('#address').removeAttr('readonly');
                jQuery('#city').removeAttr('readonly');
                jQuery('#state').removeAttr('readonly');
                jQuery('#zip').removeAttr('readonly');
                jQuery('#country').removeAttr('readonly');
                jQuery('#website').removeAttr('readonly');
                jQuery('#phone').removeAttr('readonly');
                jQuery('#vat_number').removeAttr('readonly');
            } else if (e.val) {
                //get existing item info
                $("#add_new_supplier_to_library").val(""); //reset the flag to add new item in library
                $.ajax({
                    url: "<?php echo get_uri("stock/get_item_info_suggestion"); ?>",
                    data: {supplier_name: e.val,supplier_id:e.added.supplier_id, material_id:parseInt(jQuery('#material_id').val())},
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        //auto fill the description, unit type and rate fields.
                        if (response) {
                            jQuery('#supplier_id').val(response.supplier_info.id);
                            jQuery('#name').val(response.supplier_info.supplier_name);
                            if(parseFloat(response.supplier_info.price)!=0 && parseFloat(jQuery('#pr_item_rate').val())==0)
                                jQuery('#pr_item_rate').val(response.supplier_info.price);
                            jQuery("#pr_item_rate").val(parseInt(response.supplier_info.ratio)?Math.abs(response.supplier_info.price/response.supplier_info.ratio):0);
                            jQuery('#currency').val(response.supplier_info.currency);
                            jQuery('#currency_symbol').val(response.supplier_info.currency_symbol);
                            jQuery('#address').val(response.supplier_info.address);
                            jQuery('#city').val(response.supplier_info.city);
                            jQuery('#state').val(response.supplier_info.state);
                            jQuery('#zip').val(response.supplier_info.zip);
                            jQuery('#country').val(response.supplier_info.country);
                            jQuery('#website').val(response.supplier_info.website);
                            jQuery('#phone').val(response.supplier_info.phone);
                            jQuery('#vat_number').val(response.supplier_info.vat_number);

                            //jQuery('#supplier_name').select2('readonly', true);
                            jQuery('#currency').attr('readonly', true);
                            jQuery('#currency_symbol').attr('readonly', true);
                            jQuery('#address').attr('readonly', true);
                            jQuery('#city').attr('readonly', true);
                            jQuery('#state').attr('readonly', true);
                            jQuery('#zip').attr('readonly', true);
                            jQuery('#country').attr('readonly', true);
                            jQuery('#website').attr('readonly', true);
                            jQuery('#phone').attr('readonly', true);
                            jQuery('#vat_number').attr('readonly', true);
                        }
                    }
                });
            }
        });
    }
</script>
