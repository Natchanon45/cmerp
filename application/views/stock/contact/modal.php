<?php echo form_open(get_uri("stock/supplier_contact_save"), array("id" => "contact-form", "class" => "general-form", "role" => "form", "autocomplete" => "false")); ?>
<div class="modal-body clearfix">
  <input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
  <input type="hidden" name="supplier_id" value="<?php echo isset($model_info->supplier_id)? $model_info->supplier_id: ''; ?>" />

  <?php
    $readonly = false;
    if(empty($model_info->id)) {
      $readonly = isset($can_create) && !$can_create;
    } else {
      $readonly = isset($can_update) && !$can_update;
    }

    $label_column = isset($label_column) ? $label_column : "col-md-3";
    $field_column = isset($field_column) ? $field_column : "col-md-9";
  ?>
  <div class="form-group">
    <label for="first_name" class="<?php echo $label_column; ?>"><?php echo lang('first_name'); ?></label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "first_name",
          "name" => "first_name",
          "value" => $model_info->first_name,
          "class" => "form-control",
          "placeholder" => lang('first_name'),
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
          "readonly" => $readonly
        ));
      ?>
    </div>
  </div>
  <div class="form-group">
    <label for="last_name" class="<?php echo $label_column; ?>"><?php echo lang('last_name'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
          echo form_input(array(
            "id" => "last_name",
            "name" => "last_name",
            "value" => $model_info->last_name,
            "class" => "form-control",
            "placeholder" => lang('last_name'),
            "data-rule-required" => true,
            "data-msg-required" => lang("field_required"),
            "readonly" => $readonly
          ));
        ?>
    </div>
  </div>
  <div class="form-group">
    <label for="email" class="<?php echo $label_column; ?>"><?php echo lang('email'); ?></label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "email",
          "name" => "email",
          "value" => $model_info->email,
          "class" => "form-control",
          "placeholder" => lang('email'),
          "data-rule-email" => true,
          "data-msg-email" => lang("enter_valid_email"),
          "autocomplete" => "off",
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
          "value" => $model_info->phone ? $model_info->phone : "",
          "class" => "form-control",
          "placeholder" => lang('phone'),
          "readonly" => $readonly
        ));
      ?>
    </div>
  </div>
  <div class="form-group ">
    <label for="is_primary" class="<?php echo $label_column; ?>"><?php echo lang('primary_contact'); ?></label>
    <div class="<?php echo $field_column; ?>">
      <?php
        $disable = "";
        if ($model_info->is_primary || $readonly) {
          $disable = "disabled='disabled'";
        }
        echo form_checkbox(
          "is_primary", "1", 
          $model_info->is_primary, 
          "id='is_primary' $disable"
        );
      ?>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
  <?php if($can_update){?>
    <button type="submit" class="btn btn-primary">
      <span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
    </button>
  <?php }?>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
  $(document).ready(function() {
    $("#contact-form").appForm({
      onSuccess: function(result) {
        $("#contact-table").appTable({ newData: result.data, dataId: result.id });
        appAlert.success(result.message, {duration: 10000});
        setTimeout(function () {
            location.reload();
        }, 500);
      }
    });
    $("#first_name").focus();
  });
</script>    