<?php echo form_open(get_uri("leads/save"), array("id" => "lead-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <?php //$this->load->view("leads/lead_form_fields"); ?>
    <?php if(isset($model_info)):?>
        <input type="hidden" name="id" value="<?php echo isset($model_info->id)?$model_info->id:''; ?>" />
    <?php endif;?>
    
    <input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />
    
    <div class="form-group">
        <label for="vat_number" class="<?php echo $label_column; ?>"><?php echo lang('vat_number'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "vat_number",
                "name" => "vat_number",
                "value" => isset($model_info)?$model_info->vat_number:'',
                "class" => "form-control",
                "placeholder" => lang('vat_number')
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="company_name" class="<?php echo $label_column; ?>"><?php echo lang('company_client_name'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(array(
                "id" => "company_name",
                "name" => "company_name",
                "value" => isset($model_info)?$model_info->company_name:'',
                "class" => "form-control",
                "placeholder" => lang('company_client_name'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="lead_status_id" class="<?php echo $label_column; ?>"><?php echo lang('status'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
                foreach ($statuses as $status) {
                    $lead_status[$status->id] = $status->title;
                }

                echo form_dropdown("lead_status_id", $lead_status, isset($model_info)?[$model_info->lead_status_id]:[], "class='select2'");
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="owner_id" class="<?php echo $label_column; ?>"><?php echo lang('owner'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
                echo form_input(array(
                    "id" => "owner_id",
                    "name" => "owner_id",
                    "value" => isset($model_info->owner_id)?$model_info->owner_id:$this->login_user->id,
                    "class" => "form-control",
                    "placeholder" => lang('owner')

                ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="lead_source_id" class="<?php echo $label_column; ?>"><?php echo lang('source'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            $lead_source = array();

            foreach ($sources as $source) {
                $lead_source[$source->id] = $source->title;
            }

            echo form_dropdown("lead_source_id", $lead_source, isset($model_info)?[$model_info->lead_source_id]:[], "class='select2'");
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="address" class="<?php echo $label_column; ?>"><?php echo lang('address'); ?></label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_textarea(array(
                "id" => "address",
                "name" => "address",
                "value" => isset($model_info->address)?$model_info->address:'',
                "class" => "form-control",
                "placeholder" => lang('address')
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
                "value" => isset($model_info->city)?$model_info->city:'',
                "class" => "form-control",
                "placeholder" => lang('city')
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
                "value" => isset($model_info->state)?$model_info->state:'',
                "class" => "form-control",
                "placeholder" => lang('state')
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
                "value" => isset($model_info->zip)?$model_info->zip:'',
                "class" => "form-control",
                "placeholder" => lang('zip')
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
                "value" => isset($model_info->country)?$model_info->country:'',
                "class" => "form-control",
                "placeholder" => lang('country')
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
                "value" => isset($model_info->phone)?$model_info->phone:'',
                "class" => "form-control",
                "placeholder" => lang('phone')
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
                "value" => isset($model_info->website)?$model_info->website:'',
                "class" => "form-control",
                "placeholder" => lang('website')
            ));
            ?>
        </div>
    </div>
    <?php if($this->login_user->is_admin && get_setting("module_invoice")): ?>
        <div class="form-group">
            <label for="currency" class="<?php echo $label_column; ?>"><?php echo lang('currency'); ?></label>
            <div class="<?php echo $field_column; ?>">
                <?php
                echo form_input(array(
                    "id" => "currency",
                    "name" => "currency",
                    "value" => isset($model_info->currency)?$model_info->currency:'',
                    "class" => "form-control",
                    "placeholder" => lang('keep_it_blank_to_use_default') . " (" . get_setting("default_currency") . ")"
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
                    "value" => isset($model_info->currency_symbol)?$model_info->currency_symbol:'',
                    "class" => "form-control",
                    "placeholder" => lang('keep_it_blank_to_use_default') . " (" . get_setting("currency_symbol") . ")"
                ));
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if(!empty($custom_fields)): ?>
        <?php foreach ($custom_fields as $field): ?>
            <?php
                $field->{"id"} = $field->code;
                
                if($field->field_type == "select" && $field->options != NULL){
                    $field->{"options"} = implode(",", json_decode($field->options, TRUE));
                }

                $field->{"value"} = isset($model_info)?$model_info->{$field->code}:'';
                $field->{"required"} = $field->required == "Y"?true:false;
            ?>
            <div class="form-group " data-field-type="<?php echo $field->field_type; ?>">
                <label for="custom_field_<?php echo $field->code; ?>" class="<?php echo $label_column; ?>"><?php echo $field->title; ?></label>
                <div class="<?php echo $field_column; ?>">
                    <?php
                        if ($this->login_user->user_type == "client"){
                            $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $field->value));
                        }else{
                            $this->load->view("custom_fields/input_" . $field->field_type, array("field_info" => $field));
                        }
                    ?> 
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div><!--.modal-body-->

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
$(document).ready(function () {
    $("#lead-form").appForm({
        onSuccess: function (result) {
            if (result.view === "details") {
                appAlert.success(result.message, {duration: 10000});
                setTimeout(function () {
                    location.reload();
                }, 500);
            } else {
                $("#lead-table").appTable({newData: result.data, dataId: result.id});
                $("#reload-kanban-button:visible").trigger("click");
            }
        }
    });
    
    $("#company_name").focus();

    $('[data-toggle="tooltip"]').tooltip();
    $(".select2").select2();

    <?php if (isset($currency_dropdown)): ?>
        if ($('#currency').length) {
            $('#currency').select2({data: <?php echo json_encode($currency_dropdown); ?>});
        }
    <?php endif; ?>

    $('#owner_id').select2({data: <?php echo json_encode($owners_dropdown); ?>});
});
</script>    