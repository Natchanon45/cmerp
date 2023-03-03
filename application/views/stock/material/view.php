<div class="page-title clearfix no-border bg-off-white">
  <h1>
    <a class="title-back" href="<?php echo get_uri('stock/materials'); ?>">
      <i class="fa fa-chevron-left" aria-hidden="true"></i>
    </a>
    <?php echo lang('stock_material_details') . " - " . $material_info->name ?>
  </h1>
</div>
<div id="page-content" class="clearfix">

  <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist" style="background:#ffffff;">
    <li><a role="presentation" href="<?php echo_uri("stock/material_info/".$material_info->id); ?>" data-target="#material-info">
      <?php echo lang('stock_material_info'); ?>
    </a></li>
    <?php if(!isset($hidden_menu) || !in_array('material-mixing', $hidden_menu)){?>
      <li><a role="presentation" href="<?php echo_uri("stock/material_mixings/".$material_info->id); ?>" data-target="#material-mixing">
        <?php echo lang('stock_material_mixing'); ?>
      </a></li>
    <?php }?>
    <?php if(!isset($hidden_menu) || !in_array('material-pricings', $hidden_menu)){?>
      <li><a role="presentation" href="<?php echo_uri("stock/material_pricings/".$material_info->id); ?>" data-target="#material-pricings">
        <?php echo lang('stock_material_pricings'); ?>
      </a></li>
    <?php }?>
    <li><a role="presentation" href="<?php echo_uri("stock/material_files/".$material_info->id); ?>" data-target="#material-files">
      <?php echo lang('files'); ?>
    </a></li>
    <?php if(!isset($hidden_menu) || !in_array('material-remaining', $hidden_menu)){?>
      <li><a role="presentation" href="<?php echo_uri("stock/material_remainings/".$material_info->id); ?>" data-target="#material-remaining">
        <?php echo lang('stock_restock_list'); ?>
      </a></li>
    <?php }?>
    <?php if(!isset($hidden_menu) || !in_array('material-used', $hidden_menu)){?>
      <li><a role="presentation" href="<?php echo_uri("stock/material_used/".$material_info->id); ?>" data-target="#material-used">
        <?php echo lang('stock_restock_used_list'); ?>
      </a></li>
    <?php }?>
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade" id="material-info"></div>
    <div role="tabpanel" class="tab-pane fade" id="material-mixing"></div>
    <div role="tabpanel" class="tab-pane fade" id="material-pricings"></div>
    <div role="tabpanel" class="tab-pane fade" id="material-files"></div>
    <div role="tabpanel" class="tab-pane fade" id="material-remaining"></div>
    <div role="tabpanel" class="tab-pane fade" id="material-used"></div>
  </div>

</div>

<script type="text/javascript">
  $(document).ready(function () {

    setTimeout(function () {
      var tab = "<?php echo $tab; ?>";
      if (tab === "info") {
        $("[data-target=#material-info]").trigger("click");
      } else if (tab === "mixing") {
        $("[data-target=#material-mixing]").trigger("click");
      } else if (tab === "pricings") {
        $("[data-target=#material-pricings]").trigger("click");
      } else if (tab === "files") {
        $("[data-target=#material-files]").trigger("click");
      } else if (tab === "remaining") {
        $("[data-target=#material-remaining]").trigger("click");
      } else if (tab === "used") {
        $("[data-target=#material-used]").trigger("click");
      }
    }, 210);

  });
</script>
