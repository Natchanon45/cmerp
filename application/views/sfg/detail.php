<div class="page-title clearfix no-border bg-off-white">
    <h1><a class="title-back" href="<?php echo get_uri('sfg'); ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>รายการสินค้ากึ่งสำเร็จ - <?php echo $model_info->title; ?></h1>
</div>
<div id="page-content" class="clearfix">
    <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist" style="background:#ffffff;">
        <li><a role="presentation" href="<?php echo_uri("sfg/detail_info/".$model_info->id); ?>" data-target="#sfg-info"><?php echo lang('item_details'); ?></a></li>
        <?php if($this->Permission_m->bom_supplier_read == true): ?>
            <li><a role="presentation" href="<?php echo_uri("sfg/detail_pricing/" . $model_info->id); ?>" data-target="#sfg-pricings"><?php echo lang('stock_item_pricings'); ?></a></li>
        <?php endif; ?>

        <?php if($this->Permission_m->access_semi_product_item_formula == true): ?>
            <li><a role="presentation" href="<?php echo_uri("sfg/detail_mixings/".$model_info->id); ?>" data-target="#sfg-mixing"><?php echo lang('item_mixings'); ?></a></li>
        <?php endif; ?>

        <li><a role="presentation" href="<?php echo_uri("sfg/detail_files/" . $model_info->id); ?>" data-target="#sfg-files"><?php echo lang('files'); ?></a></li>

        <?php if($this->Permission_m->bom_restock_read_self == true || $this->Permission_m->bom_restock_read == true): ?>
            <li><a role="presentation" href="<?php echo_uri("sfg/detail_item_remaining/" . $model_info->id); ?>" data-target="#sfg-remaining"><?php echo lang('stock_restock_list'); ?></a></li>
        <?php endif; ?>

        <li><a role="presentation" href="<?php echo_uri("sfg/detail_item_used/" . $model_info->id); ?>" data-target="#sfg-used"><?php echo lang('stock_restock_used_list'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="sfg-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="sfg-pricings"></div>
        <div role="tabpanel" class="tab-pane fade" id="sfg-mixing"></div>
        <div role="tabpanel" class="tab-pane fade" id="sfg-files"></div>
        <div role="tabpanel" class="tab-pane fade" id="sfg-remaining"></div>
        <div role="tabpanel" class="tab-pane fade" id="sfg-used"></div>
    </div>
</div>
