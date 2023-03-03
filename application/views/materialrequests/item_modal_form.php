<?php echo form_open(get_uri("materialrequests/save_item"), array("id" => "pr-item-form", "class" => "general-form", "role" => "form")); ?>
<?php /*<div class="page-title clearfix">
    <h1> <?php
    switch($item_type) {
        case 'mtr':
            echo lang('add_materials');
        break;
        case 'itm':
            echo lang('add_internal_items');
        break;
        case 'itm':
        default:
            echo lang('add_more_items');
        break;
    }?></h1>
</div>*/?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="supplier_id" id="supplier_id" value="<?php echo $model_info->supplier_id;?>" />
     <input type="hidden" name="add_new_supplier_to_library" value="" id="add_new_supplier_to_library" />
    <input type="hidden" name="item_type" value="<?php echo $item_type; ?>" />
    <?php if($item_type=='oth') {?>
    <input type="hidden" name="item_id" id="item_id" value="0" />
    <input type="hidden" name="material_id" id="material_id" value="0" />
    <?php }else{ ?>
    <input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id; ?>" />
    <input type="hidden" name="material_id" id="material_id" value="<?php echo $material_id; ?>" />
    <?php }?>

    <input type="hidden" name="mr_id" value="<?php echo intval(@$mr_id); ?>" />
    <?php if($item_type=='itm') {?>
    <input type="hidden" name="mr_item_title" value="<?php echo @$model_info->title; ?>" />
    <?php }else if($item_type=='oth') {?>
    <div class="form-group">
        <label for="mr_item_oth_title" class=" col-md-3"><?php echo lang('title'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "mr_item_oth_title",
                "name" => "mr_item_title",
                "value" => $model_info->title,
                "class" => "form-control validate-hidden",
                "placeholder" => lang('title'),
                "type" => "text",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>
    <?php }elseif($item_type=='mtr') { ?>
    <div class="form-group">
        <label for="mr_item_mtr_title" class=" col-md-3"><?php echo lang('title'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "mr_item_mtr_title",
                "name" => "mr_item_title",
                "value" => $model_info->title,
                "class" => "form-control validate-hidden",
                "placeholder" => lang('title'),
                "type" => "text",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
            <a id="mr_item_mtr_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
        </div>
    </div>
    <?php } ?>
    <div class="form-group">
        <label for="code" class="col-md-3"><?php echo lang('code'); ?></label>
        <div class="col-md-9">
            <?php
                echo form_input(array(
                    "id" => "code",
                    "name" => "code",
                    "value" => $code,
                    "class" => "form-control",
                    "placeholder" => lang('code'),
                    //"readonly" => $readonly
                ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="mr_item_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "mr_item_description",
                "name" => "mr_item_description",
                "value" => $model_info->description ? $model_info->description : "",
                "class" => "form-control",
                "placeholder" => lang('description'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="mr_item_quantity" class=" col-md-3"><?php echo lang('quantity'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "mr_item_quantity",
                "name" => "mr_item_quantity",
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

    <div class="form-group">
        <label for="mr_unit_type" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "mr_unit_type",
                "name" => "mr_unit_type",
                "value" => $model_info->unit_type,
                "class" => "form-control",
                "placeholder" => lang('unit_type') . ' (Ex: hours, pc, etc.)',
            ),
            $model_info->unit_type,
            (($item_type=='oth')?'':'readonly="readonly"')
            );
            ?>
        </div>
    </div>

    <input type="text" id="mr_qty_limit" name="material_limit" hidden>
    

    <?php /* if($item_type=='mtr') {?>
    <div class="form-group">
        <label for="supplier_name" class=" col-md-3"><?php echo lang('select_a_supplier');?></label>
        <div class="col-md-9">
            <select name="supplier_name" id="supplier_name" onchange="changeSupplier(this);" class="form-control" placeholder="<?php echo lang('stock_supplier_name');?>" data-rule-required="true" data-msg-required="<?php echo lang("field_required");?>">
                <option>- <?php echo lang('select_a_supplier');?> -</option>
            <?php
            foreach($suppliers as $sup) {
                if($sup->value==$supplier_name)
                    echo '<option value="'.$sup->supplier_name.'" selected="selected" supplier_name="'.$sup->supplier_name.'" price="'.$sup->price.'" currency="'.$sup->currency.'" currency_symbol="'.$sup->currency_symbol.'">'.$sup->text.'</option>';
                else
                    echo '<option value="'.$sup->supplier_name.'" supplier_name="'.$sup->supplier_name.'" price="'.$sup->price.'" currency="'.$sup->currency.'" currency_symbol="'.$sup->currency_symbol.'">'.$sup->text.'</option>';
            }?>
            </select>
            <a id="pr_supplier_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>
        </div>
    </div>
    <?php }*/ ?>
    


</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#pr-item-form").appForm({
            onSuccess: function (result) {
                $("#pr-item-table").appTable({newData: result.data, dataId: result.id});
                $("#pr-total-section").html(result.pr_total_view);
            }
        });

        //show item suggestion dropdown when adding new item
        var isUpdate = "<?php echo $model_info->id; ?>";
        if (!isUpdate) {
            applySelect2OnItemTitle();
            applySelect2OnMTRItemTitle();
            applySelectSupplierName();
        }

        //re-initialize item suggestion dropdown on request
        $("#mr_item_title_dropdwon_icon").click(function () {
            applySelect2OnItemTitle();
        });

        //re-initialize item suggestion dropdown on request
        $("#mr_item_mtr_title_dropdwon_icon").click(function () {
            applySelect2OnMTRItemTitle();
        });

        $("#pr_supplier_title_dropdwon_icon").click(function () {
            applySelectSupplierName();
            $('#supplier_name').select2('readonly', false);
        });
    });

    /*function changeSupplier(obj) {
        let target = jQuery(obj).find('[value="'+obj.value+'"]');
        jQuery('#supplier_name').val(jQuery(target).attr('supplier_name'));
        jQuery('#mr_item_rate').val(jQuery(target).attr('price'));
        jQuery('#currency').val(jQuery(target).attr('currency'));
        jQuery('#currency_symbol').val(jQuery(target).attr('currency_symbol'));
    }*/

    function applySelect2OnItemTitle() {
        $("#mr_item_title").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("materialrequests/get_pr_item_suggestion"); ?>",
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
        }).change(function (e) {
            if (e.val === "+") {
                //show simple textbox to input the new item
                //$("#mr_item_title").select2("destroy").val("").focus();
                //$("#add_new_item_to_library").val(1); //set the flag to add new item in library
            } else if (e.val) {
                //get existing item info
                //$("#add_new_item_to_library").val(""); //reset the flag to add new item in library
                $.ajax({
                    url: "<?php echo get_uri("materialrequests/get_pr_item_info_suggestion"); ?>",
                    data: {item_name: e.val},
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        //auto fill the description, unit type and rate fields.
                        if (response && response.success) {alert('aa')
                            $("#item_id").val(response.item_info.id);
                            $("#material_id").val(0);
                            if (!$("#mr_item_description").val()) {
                                $("#mr_item_description").val(response.item_info.description);
                            }

                            if (!$("#mr_unit_type").val()) {
                                $("#mr_unit_type").val(response.item_info.unit_type);
                            }

                            if (!$("#mr_item_rate").val()) {
                                $("#mr_item_rate").val(response.item_info.rate);
                            }
                        }
                    }
                });
            }

        });
    }

    function applySelect2OnMTRItemTitle() {
        $("#mr_item_mtr_title").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("materialrequests/get_materials_suggestion"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term // search term
                    };
                },
                results: function (data, page) {
                    console.log(data)
                    return {results: data};
                }
            }
        }).change(function (e) {//alert('change mtr title'); //on select an option
            //get existing item info
            $.ajax({
                url: "<?php echo get_uri("materialrequests/get_material_info_suggestion"); ?>",
                data: {matrial_id: e.val},
                cache: false,
                type: 'POST',
                dataType: "json",
                success: function (response) {
                    console.log(response)
                    //auto fill the description, unit type and rate fields.
                    if (response && response.success) {
                        $("#item_id").val(0);
                        $("#code").val(response.item_info.name);
                        $("#material_id").val(response.item_info.id);
                        $("#mr_item_mtr_title").val(response.item_info.name+' : '+response.item_info.production_name);
                        $("#mr_item_description").val(response.item_info.description);
                        $("#mr_unit_type").val(response.item_info.unit);
                        $("#mr_qty_limit").val(response.item_info.remaining)
                    }
                }
            });
        });
    }

    function applySelectSupplierName() {
        $("#supplier_name").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo get_uri("materialrequests/get_pr_supplier_suggestion"); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term, // search term,
                        id:<?php echo intval($model_info->id); ?>,
                        material_id: parseInt(jQuery('#material_id').val())
                    };
                },
                results: function (data, page) {
                    return {results: data};
                }
            }
        }).change(function (e) {//alert('change supplier');
            if (e.val === "+") {
                //show simple textbox to input the new item
                $("#supplier_name").select2("destroy").val("").focus();
                $("#add_new_supplier_to_library").val(1); //set the flag to add new item in library

                //jQuery('#item_id').val(0);
                jQuery('#material_id').val(0);
                jQuery('#supplier_id').val(0);
                jQuery('#supplier_name').select2('readonly', false);
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
                    url: "<?php echo get_uri("materialrequests/get_pr_supplier_info_suggestion"); ?>",
                    data: {supplier_name: e.val,supplier_id:e.added.supplier_id, material_id:parseInt(jQuery('#material_id').val())},
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        //auto fill the description, unit type and rate fields.
                        if (response) {
                            jQuery('#supplier_id').val(response.supplier_info.id);
                            jQuery('#supplier_name').val(response.supplier_info.supplier_name);
                            if(parseFloat(response.supplier_info.price)!=0 && parseFloat(jQuery('#mr_item_rate').val())==0)
                                jQuery('#mr_item_rate').val(response.supplier_info.price);
                            jQuery("#mr_item_rate").val(parseInt(response.supplier_info.ratio)?Math.abs(response.supplier_info.price/response.supplier_info.ratio):0);
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