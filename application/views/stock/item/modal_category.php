<?php echo form_open(get_uri("stock/item_category_save"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix label-modal-body">
  <input type="hidden" id="type" name="type" value="<?php echo $type; ?>" />
  <input type="hidden" id="category_id" name="id" value="" />

  <div class="add-label clearfix pb10">
    <div class="col-md-9">
      <div class="form-group">
        <div class=" col-md-12">
          <?php
            echo form_input(array(
              "id" => "title",
              "name" => "title",
              "value" => "",
              "class" => "form-control",
              "placeholder" => lang('stock_material_category_title'),
              "autofocus" => true,
              "autocomplete" => "off",
              "data-rule-required" => true,
              "data-msg-required" => lang("field_required"),
            ));
          ?>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-default">
        <span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
      </button>
    </div>
  </div>
  <div id="category-show-area" class="p15 b-t">
    <?php foreach($existing_categories as $d){?>
      <span data-act="category-edit-delete" data-id="<?= $d->id ?>" class="label label-material cate-large mr5 clickable"><?= $d->title ?></span>
    <?php }?>
  </div>
</div>

<div class="modal-footer">
  <button id="category-delete-btn" type="button" class="btn btn-default hide pull-left">
    <span class="fa fa-close"></span> <?php echo lang('delete'); ?>
  </button>
  <button id="cancel-edit-btn" type="button" class="btn btn-default ml10 hide pull-left">
    <span class="fa fa-close"></span> <?php echo lang('cancel'); ?>
  </button>
  <button type="button" class="btn btn-default" data-dismiss="modal">
    <span class="fa fa-close"></span> <?php echo lang('close'); ?>
  </button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
  $(document).ready(function () {
    var $categoryShowArea = $("#category-show-area");

    $("#category-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        if (result.success) {
          if ($("#category_id").val()) {
            var $selector = $categoryShowArea.find("[data-id='" + result.id + "']");
            $selector.html(result.data.title);
            hideEditMode();
          } else {
            $categoryShowArea.prepend(`<span data-act="category-edit-delete" data-id="${result.id}" class="label label-material cate-large mr5 clickable">${result.data.title}</span>`);
          }
          $("#title").val("").focus();
        }
      }
    });

    //update/delete
    $('body').on('click', "[data-act='category-edit-delete']", function () {
      showEditMode($(this));
    });

    function showEditMode($selector) {
      $("#title").val($selector.text()).focus();
      $("#category_id").val($selector.attr("data-id"));
      $("#category-delete-btn").removeClass("hide");
      $("#cancel-edit-btn").removeClass("hide");
    }
    function hideEditMode() {
      $("#title").val('').focus();
      $("#category_id").val('');
      $("#category-delete-btn").addClass("hide");
      $("#cancel-edit-btn").addClass("hide");
    }

    $("#cancel-edit-btn").click(function () {
      hideEditMode();
    });

    $("#category-delete-btn").click(function () {
      appLoader.show({container: ".label-modal-body", css: "left:0;"});
      $.ajax({
        url: "<?php echo get_uri('stock/item_category_delete') ?>",
        type: 'POST',
        dataType: 'json',
        data: { id: $("#category_id").val() },
        success: function (result) {
          appLoader.hide();
          if (result.label_exists) {
            appAlert.error(result.message, {container: '.modal-body', animate: false});
          } else if (result.success) {
            var $selector = $categoryShowArea.find("[data-id='" + result.id + "']");
            $selector.fadeOut(100, function () {
              $selector.remove();
            });
            hideEditMode();
          }
        }
      });
    });

    $("#title").focus();
  });
</script>
