<div class="page-title clearfix no-border bg-off-white">
  <h1>
    <a class="title-back" href="<?php echo get_uri('stock/restocks_item'); ?>">
      <i class="fa fa-chevron-left" aria-hidden="true"></i>
    </a>
    <?php echo lang('stock_restock_item') . " - " . $restock_item_info->name ?>
  </h1>
</div>

<div id="page-content" class="clearfix">
  <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist" style="background: #ffffff;">
    <li>
      <a role="presentation" href="<?php echo_uri("stock/restock_item_info/" . $restock_item_info->id); ?>" data-target="#restock-info">
        <?php echo lang('stock_restock_item_info'); ?>
      </a>
    </li>
    <li><a role="presentation" href="<?php echo_uri("stock/restock_item_details/" . $restock_item_info->id); ?>" data-target="#restock-details">
        <?php echo lang('stock_restock_item_list'); ?>
      </a>
    </li>
    <li><a role="presentation" href="<?php echo_uri("stock/restock_item_used/" . $restock_item_info->id); ?>" data-target="#restock-used">
        <?php echo lang('stock_restock_item_used_list'); ?>
      </a>
    </li>
  </ul>
  
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade" id="restock-info"></div>
    <div role="tabpanel" class="tab-pane fade" id="restock-details"></div>
    <div role="tabpanel" class="tab-pane fade" id="restock-used"></div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    setTimeout(function () {
      var tab = "<?php echo $tab; ?>";
      if (tab === "info") {
        $("[data-target=#restock-info]").trigger("click");
      } else if (tab === "details") {
        $("[data-target=#restock-details]").trigger("click");
      } else if (tab === "used") {
        $("[data-target=#restock-used]").trigger("click");
      }
    }, 210);
  });
</script>