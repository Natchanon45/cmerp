<style>
  #alert-message {
    box-shadow: none;
    color: #ec5855;
    margin: 0 1rem 0 1rem !important;
  }
</style>

<?php echo form_open(get_uri("stock/item_category_save"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix label-modal-body">
  <input type="hidden" id="category_id" name="id">
  <input type="hidden" id="category_type" name="type" value="<?php if (isset($type) && !empty($type)) { echo $type; } ?>">

  <div class="add-label clearfix">
    <div class="col-md-9">
      <div class="form-group">
        <div class=" col-md-12">
          <?php
          echo form_input(
            array(
              "id" => "title",
              "name" => "title",
              "value" => "",
              "class" => "form-control",
              "placeholder" => lang('stock_material_category_title'),
              "autofocus" => true,
              "autocomplete" => "off",
              "data-rule-required" => true,
              "data-msg-required" => lang("field_required"),
            )
          );
          ?>
        </div>
        <p id="alert-message" class="hide">
          <span>
            <?php echo lang('item_cate_duplicate'); ?>
          </span>
        </p>
      </div>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-default">
        <span class="fa fa-check-circle"></span>
        <?php echo lang('save'); ?>
      </button>
    </div>
  </div>
  <div id="category-show-area" class="p15 b-t">
    <?php foreach ($existing_categories as $category_item): ?>
      <span data-act="category-edit-delete" data-id="<?php echo $category_item->id; ?>" class="label label-material cate-large mr5 clickable"><?php echo $category_item->title; ?></span>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal-footer">
  <button id="category-delete-btn" type="button" class="btn btn-default hide pull-left">
    <span class="fa fa-close"></span>
    <?php echo lang('delete'); ?>
  </button>
  <button id="cancel-edit-btn" type="button" class="btn btn-default ml10 hide pull-left">
    <span class="fa fa-close"></span>
    <?php echo lang('cancel'); ?>
  </button>
  <button type="button" class="btn btn-default" data-dismiss="modal">
    <span class="fa fa-close"></span>
    <?php echo lang('close'); ?>
  </button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
  $(document).ready(function () {
    var $categoryShowArea = $("#category-show-area");

    $("#category-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        if (result.post) {
          $('#alert-message').removeClass('hide');

          setTimeout((e) => {
            $('#alert-message').addClass('hide');
          }, 3000);
        } else {
          if (result.success) {
            $('#alert-message').addClass('hide');

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
      }
    });

    $('body').on('click', "[data-act='category-edit-delete']", function (e) {
      showEditMode($(this));
    });

    $('body').on('click', "#title", function (e) {
      e.target.select();
    });

    function showEditMode($selector) { // MARK
      let url = "<?php echo get_uri('item_categories/dev2_countItemCateById/'); ?>" + $selector.data().id;
      $.ajax({
        url: url,
        type: 'GET',
        success: function (result) {
          if (parseInt(result) > 0) {
            $("#category-delete-btn").addClass("hide");
          } else {
            $("#category-delete-btn").removeClass("hide");
          }
        }
      });

      $("#title").val($selector.text()).focus();
      $("#category_id").val($selector.attr("data-id"));
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
      appLoader.show({ container: ".label-modal-body", css: "left: 0;" });
      $.ajax({
        url: "<?php echo get_uri('stock/item_category_delete'); ?>",
        type: 'POST',
        dataType: 'json',
        data: { id: $("#category_id").val() },
        success: function (result) {
          appLoader.hide();
          if (result.label_exists) {
            appAlert.error(result.message, { container: '.modal-body', animate: false });
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