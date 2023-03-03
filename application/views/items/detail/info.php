<div class="tab-content">
  <?php echo form_open(get_uri("items/save"), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
  <div class="panel post-dropzone" id="items-dropzone">
    <div class="panel-body">
      <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

      <?php if ($model_info->id) { ?>
        <div class="form-group">
          <div class="col-md-12 text-off"> <?php echo lang('item_edit_instruction'); ?></div>
        </div>
      <?php } ?>

      <div class="form-group">
        <label for="title" class="<?= $label_column ?>"><?php echo lang('title'); ?></label>
        <div class="<?= $field_column ?>">
          <?php
            echo form_input(array(
              "id" => "title",
              "name" => "title",
              "value" => $model_info->title,
              "class" => "form-control validate-hidden",
              "placeholder" => lang('title'),
              "autofocus" => true,
              "data-rule-required" => true,
              "data-msg-required" => lang("field_required"),
            ));
          ?>
        </div>
      </div>
      <div class="form-group">
        <label for="description" class="<?= $label_column ?>"><?php echo lang('description'); ?></label>
        <div class="<?= $field_column ?>">
          <?php
            echo form_textarea(array(
              "id" => "description",
              "name" => "description",
              "value" => $model_info->description ? $model_info->description : "",
              "class" => "form-control",
              "placeholder" => lang('description'),
              "data-rich-text-editor" => false
            ));
          ?>
        </div>
      </div>
      <div class="form-group">
        <label for="category_id" class="<?= $label_column ?>"><?php echo lang('category'); ?></label>
        <div class="<?= $field_column ?>">
          <?php
            echo form_dropdown("category_id", $categories_dropdown, $model_info->category_id, "class='select2 validate-hidden' id='category_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
          ?>
        </div>
      </div>
      <div class="form-group">
        <label for="unit_type" class="<?= $label_column ?>"><?php echo lang('unit_type'); ?></label>
        <div class="<?= $field_column ?>">
          <?php
            echo form_input(array(
              "id" => "unit_type",
              "name" => "unit_type",
              "value" => $model_info->unit_type,
              "class" => "form-control",
              "placeholder" => lang('unit_type') . ' (Ex: hours, pc, etc.)'
            ));
          ?>
        </div>
      </div>
      <div class="form-group">
        <label for="item_rate" class="<?= $label_column ?>"><?php echo lang('rate'); ?></label>
      <div class="<?= $field_column ?>">
          <?php
            echo form_input(array(
              "id" => "item_rate",
              "name" => "item_rate",
              "value" => $model_info->rate ? $model_info->rate : "",
              "class" => "form-control",
              "placeholder" => lang('rate'),
              "data-rule-required" => true,
              "data-msg-required" => lang("field_required"),
            ));
          ?>
        </div>
      </div>

      <?php if ($this->login_user->is_admin && get_setting("module_order")) { ?>
        <div class="form-group">
          <label for="show_in_client_portal" class="<?= $label_column ?> col-xs-5 col-sm-4"><?php echo lang('show_in_client_portal'); ?></label>
          <div class=" <?= $field_column ?> col-xs-7 col-sm-8">
            <?php
              echo form_checkbox("show_in_client_portal", "1", $model_info->show_in_client_portal ? true : false, "id='show_in_client_portal'");
            ?>                       
          </div>
        </div>
      <?php } ?>

      <div class="form-group">
        <div class="col-md-12 row pr0">
          <?php
            $this->load->view("includes/file_list", array("files" => $model_info->files, "image_only" => true));
          ?>
        </div>
      </div>

      <?php $this->load->view("includes/dropzone_preview"); ?>
    </div>

    <div class="modal-footer" style="text-align:left;">
      <button class="btn btn-default upload-file-button pull-left btn-sm round" type="button" style="color:#7988a2; margin-right:10px;">
        <i class="fa fa-camera"></i> <?php echo lang("upload_image"); ?>
      </button>
      <button type="submit" class="btn btn-primary">
        <span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
      </button>
    </div>
  </div>
  <?php echo form_close(); ?>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    var uploadUrl = "<?php echo get_uri("items/upload_file"); ?>";
    var validationUri = "<?php echo get_uri("items/validate_items_file"); ?>";

    var dropzone = attachDropzoneWithForm("#items-dropzone", uploadUrl, validationUri);

    $("#item-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        appAlert.success(result.message, {duration: 10000});
      }
    });
    
    $("#item-form .select2").select2();
  });
</script>