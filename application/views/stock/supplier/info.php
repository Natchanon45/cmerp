<div class="tab-content">
  <?php echo form_open(get_uri("stock/supplier_save/"), array("id" => "company-form", "class" => "general-form dashed-row white", "role" => "form")); ?>
  <div class="panel">
    <div class="panel-default panel-heading">
      <h4><?php echo lang('stock_supplier_info'); ?></h4>
    </div>
    <div class="panel-body">
      <?php $this->load->view("stock/supplier/form"); ?>
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