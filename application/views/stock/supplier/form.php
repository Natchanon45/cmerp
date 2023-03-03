
<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="created_by" value="<?php echo isset($model_info->created_by)? $model_info->created_by: ''; ?>" />
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
    <label for="company_name" class="<?php echo $label_column; ?>"><?php echo lang('company_name'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "company_name",
                "name" => "company_name",
                "value" => $model_info->company_name,
                "class" => "form-control",
                "placeholder" => lang('company_name'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="code_supplier" class="<?php echo $label_column; ?>"><?php //echo lang('code_supplier'); ?>รหัสบริษัท</label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "code_supplier",
                "name" => "code_supplier",
                "value" => $model_info->code_supplier,
                "class" => "form-control",
                "placeholder" => lang('code_supplier'),
                "autofocus" => true,
                "data-rule-required" => false,
                "data-msg-required" => lang("field_required"),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<?php if($this->login_user->is_admin){?>
    <div class="form-group">
        <label for="owner_id" class="<?php echo $label_column; ?>">
            <?php echo lang('owner'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
            <?php
                echo form_input(array(
                    "id" => "owner_id",
                    "name" => "owner_id",
                    "value" => $model_info->owner_id ? $model_info->owner_id : $this->login_user->id,
                    "class" => "form-control",
                    "placeholder" => lang('owner'),
                    "data-rule-required" => true,
                    "data-msg-required" => lang("field_required"),
                    "readonly" => $readonly
                ));
            ?>
        </div>
    </div>
<?php }?>
<div class="form-group">
    <label for="address" class="<?php echo $label_column; ?>"><?php echo lang('address'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_textarea(array(
                "id" => "address",
                "name" => "address",
                "value" => $model_info->address ? $model_info->address : "",
                "class" => "form-control",
                "placeholder" => lang('address'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="city" class="<?php echo $label_column; ?>"><?php echo lang('city'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "city",
                "name" => "city",
                "value" => $model_info->city,
                "class" => "form-control",
                "placeholder" => lang('city'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="state" class="<?php echo $label_column; ?>"><?php echo lang('state'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "state",
                "name" => "state",
                "value" => $model_info->state,
                "class" => "form-control",
                "placeholder" => lang('state'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="zip" class="<?php echo $label_column; ?>"><?php echo lang('zip'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "zip",
                "name" => "zip",
                "value" => $model_info->zip,
                "class" => "form-control",
                "placeholder" => lang('zip'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="country" class="<?php echo $label_column; ?>"><?php echo lang('country'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "country",
                "name" => "country",
                "value" => $model_info->country,
                "class" => "form-control",
                "placeholder" => lang('country'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="phone" class="<?php echo $label_column; ?>"><?php echo lang('phone'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "phone",
                "name" => "phone",
                "value" => $model_info->phone,
                "class" => "form-control",
                "placeholder" => lang('phone'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="website" class="<?php echo $label_column; ?>"><?php echo lang('website'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "website",
                "name" => "website",
                "value" => $model_info->website,
                "class" => "form-control",
                "placeholder" => lang('website'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <label for="vat_number" class="<?php echo $label_column; ?>"><?php echo lang('vat_number'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
            echo form_input(array(
                "id" => "vat_number",
                "name" => "vat_number",
                "value" => $model_info->vat_number,
                "class" => "form-control",
                "placeholder" => lang('vat_number'),
                "readonly" => $readonly
            ));
        ?>
    </div>
</div>

<?php if ($this->login_user->is_admin && get_setting("module_invoice")) { ?>
    <div class="form-group">
        <label for="currency" class="<?php echo $label_column; ?>"><?php echo lang('currency'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
                echo form_input(array(
                    "id" => "currency",
                    "name" => "currency",
                    "value" => $model_info->currency,
                    "class" => "form-control",
                    "placeholder" => lang('keep_it_blank_to_use_default') . " (" . get_setting("default_currency") . ")",
                    "readonly" => $readonly
                ));
            ?>
        </div>
    </div>    
    <div class="form-group">
        <label for="currency_symbol" class="<?php echo $label_column; ?>"><?php echo lang('currency_symbol'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
                echo form_input(array(
                    "id" => "currency_symbol",
                    "name" => "currency_symbol",
                    "value" => $model_info->currency_symbol,
                    "class" => "form-control",
                    "placeholder" => lang('keep_it_blank_to_use_default') . " (" . get_setting("currency_symbol") . ")",
                    "readonly" => $readonly
                ));
            ?>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        <?php if (isset($currency_dropdown)) { ?>
            if ($('#currency').length) {
                $('#currency').select2({data: <?php echo json_encode($currency_dropdown); ?>});
            }
        <?php } ?>
        <?php if ($this->login_user->is_admin){?>
            $('#owner_id').select2({data: <?php echo $team_members_dropdown; ?>});
        <?php }?>
    });
</script>
