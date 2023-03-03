
<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="item_id" value="<?php echo @$item_id; ?>" />

<div style="width:100%; max-width:600px;">
  <div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
      <?php echo lang('file_name'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "name",
          "name" => "name",
          "value" => @$model_info->name,
          "class" => "form-control",
          "placeholder" => lang('file_name'),
          "autofocus" => true,
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
        ));
      ?>
    </div>
  </div>
  <div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
      <?php echo lang('description'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_textarea(array(
          "id" => "description",
          "name" => "description",
          "value" => @$model_info->description,
          "class" => "form-control",
          "placeholder" => lang('description'),
          "autofocus" => true,
          "data-rule-required" => true,
          "data-msg-required" => lang("description"),
        ));
      ?>
    </div>
  </div>
  <div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
      <?php echo lang('file'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <input type="file" name="file" />
      <?php echo @$model_info->id?anchor(get_uri("items/file_download/".$model_info->id), lang('download'), array("class" => "download", "title" => lang('download'))):'';?>
    </div>
  </div>
</div>