<div class="page-title clearfix no-border bg-off-white">
    <h1><a class="title-back" href="<?php echo get_uri('items'); ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a><?php echo lang('item') . " - " . $model_info->title ?></h1>
</div>
<div id="page-content" class="clearfix">
    <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist" style="background:#ffffff;">
        <li><a role="presentation" href="<?php echo_uri("items/detail_info/".$model_info->id); ?>" data-target="#item-info"><?php echo lang('item_details'); ?></a></li>
        <?php if($this->Permission_m->access_product_item_formula == true): ?>
            <li><a role="presentation" href="<?php echo_uri("items/detail_mixings/".$model_info->id); ?>" data-target="#item-mixing"><?php echo lang('item_mixings'); ?></a></li>
        <?php endif; ?>
        <li><a role="presentation" href="<?php echo_uri("items/files/".$model_info->id); ?>" data-target="#item-mixing-file"><?php echo lang('file'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="item-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="item-mixing"></div>
        <div role="tabpanel" class="tab-pane fade" id="item-mixing-file"></div>
    </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {

    setTimeout(function () {
      var tab = "<?php echo $tab; ?>";
      if (tab === "info") {
        $("[data-target=#item-info]").trigger("click");
      } else if (tab === "mixing") {
        $("[data-target=#item-mixing]").trigger("click");
      }
    }, 210);

  });
</script>
