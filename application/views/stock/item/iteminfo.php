<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_item_info'); ?></h4>
  </div>
  <?php echo form_open(get_uri("stock/item_save/"), array("id" => "company-form", "class" => "general-form dashed-row white", "role" => "form")); ?>
  <div class="panel">
    <div class="panel-body">
      <?php $this->load->view("stock/item/form"); ?>
    </div>
    <?php if($can_update){?>
      <div class="panel-footer">
        <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
      </div>
    <?php }?>
  </div>
  <?php echo form_close(); ?>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#company-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        appAlert.success(result.message, {duration: 10000});
      }
    });
  });
</script>