<div class="tab-content">
  <?php echo form_open(current_url(), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
  <div class="panel post-dropzone" id="items-dropzone">
    <div class="panel-body">
      <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

      <div class="form-group">
        <label for="item_code" class="col-md-2">รหัสสินค้ากึ่งสำเร็จ</label>
        <div class="col-md-10">
          <?php
            echo form_input(array(
                "id" => "item_code",
                "name" => "item_code",
                "value" => $model_info->item_code,
                "class" => "form-control validate-hidden",
                "placeholder" => "รหัสสินค้ากึ่งสำเร็จ",
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
          ?>
        </div>
      </div>      

      <div class="form-group">
        <label for="title" class="col-md-2">ชื่อสินค้ากึ่งสำเร็จ</label>
        <div class="col-md-10">
          <?php
            echo form_input(array(
              "id" => "title",
              "name" => "title",
              "value" => $model_info->title,
              "class" => "form-control validate-hidden",
              "placeholder" => "ชื่อสินค้ากึ่งสำเร็จ",
              "autofocus" => true,
              "data-rule-required" => true,
              "data-msg-required" => lang("field_required"),
            ));
          ?>
        </div>
      </div>

      <div class="form-group">
          <label for="item_rate" class="col-md-2">ราคา</label>
          <div class="col-md-10">
              <?php
              echo form_input(
                  array(
                      "id" => "item_rate",
                      "name" => "item_rate",
                      "value" => $model_info->rate,
                      "class" => "form-control",
                      "placeholder" => "ราคา"
                  )
              );
              ?>
          </div>
      </div>

      <div class="form-group">
        <label for="category_id" class="col-md-2">หมวดหมู่</label>
        <div class="col-md-10">
          <?php $mcrows = $this->Material_categories_m->getRows("SFG"); ?>
          <select name="category_id" class="form-control">
            <option>- หมวดหมู่ -</option>
            <?php if(!empty($mcrows)): ?>
              <?php foreach($mcrows as $mcrow): ?>
                <option value="<?php echo $mcrow->id; ?>" <?php if($mcrow->id == $model_info->category_id) echo "selected";?>><?php echo $this->Material_categories_m->getTitle($mcrow->id); ?></option>
              <?php endforeach;?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="account_id" class="col-md-2">หมวดบัญชี</label>
        <div class="col-md-10">
          <?php
            echo form_input(
                array(
                    "id" => "account_id",
                    "name" => "account_id",
                    "value" => $model_info->account_id ? $model_info->account_id : null,
                    "class" => "form-control",
                    "placeholder" => "หมวดบัญชี"
                )
            );
          ?>
        </div>
      </div>

      


      <div class="form-group">
        <label for="description" class="col-md-2"><?php echo lang('description'); ?></label>
        <div class="col-md-10">
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
        <label for="unit_type" class="col-md-2">หน่วย</label>
        <div class="col-md-10">
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
        <label for="unit_type" class="col-md-2">รหัสบาร์โค้ด</label>
        <div class="col-md-10">
          <?php
            echo form_input(
                array(
                    "id" => "barcode",
                    "name" => "barcode",
                    "value" => @$model_info->barcode,
                    "class" => "form-control",
                    "placeholder" => lang('stock_item_barcode')
                )
            );
          ?>
        </div>
      </div>

      <div class="form-group">
        <label for="unit_type" class="col-md-2">จำนวนเตือนขั้นต่ำ</label>
        <div class="col-md-10">
          <input
            type="number" name="noti_threshold" class="form-control" min="0" step="0.0001" required 
            name="noti_threshold" value="<?php echo @$model_info->noti_threshold; ?>" 
            placeholder="<?php echo lang('stock_item_noti_threshold'); ?>" data-rule-required = "true" 
            data-msg-required="<?php echo lang("field_required"); ?>"/>
        </div>
      </div>

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
    var uploadUrl = "<?php echo get_uri("sfg/upload_photo/upload_file") ?>";
    var validationUri = "<?php echo get_uri("sfg/upload_photo/validate_file") ?>";

    var dropzone = attachDropzoneWithForm("#items-dropzone", uploadUrl, validationUri);

    $("#item-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        appAlert.success(result.message, {duration: 10000});
      }
    });
    
    //$("#item-form .select2").select2();
    $('#category_id').select2({data: <?php echo json_encode($category_dropdown); ?>});
    $('#account_id').select2({ data: <?php echo json_encode($account_category); ?> });
    
  });
</script>