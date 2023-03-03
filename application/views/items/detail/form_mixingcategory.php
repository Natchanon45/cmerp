
<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />

<div style="width:100%; max-width:600px;">
  <div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
      <?php echo lang('category_name'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "title",
          "name" => "title",
          "value" => @$model_info->title,
          "class" => "form-control",
          "placeholder" => lang('category_name'),
          "autofocus" => true,
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
        ));
      ?>
    </div>
  </div>
</div>