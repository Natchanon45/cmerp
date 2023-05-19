
<div id="page-content" class="p20 clearfix">
  <h4 class="m0 fw-500"><?php echo lang("stock_management"); ?></h4>
  <div class="grids mt-2">

    <?php if($access_supplier){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/suppliers'); ?>">
          <div class="icon">
            <i class="fa fa-truck" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_suppliers'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/materials'); ?>">
          <div class="icon">
            <i class="fa fa-tags" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_materials'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/restocks'); ?>">
          <div class="icon">
            <i class="fa fa-database" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_restocks'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/items'); ?>">
          <div class="icon">
            <i class="fa fa-tags" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_item'); ?></h4> 
        </a>
      </div>
    <?php }?>

    <?php if($access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/restocks_item'); ?>">
          <div class="icon">
            <i class="fa fa-database" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_restocks_item'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_calculator){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/calculator'); ?>">
          <div class="icon">
            <i class="fa fa-calculator" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_calculator'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material && $access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/material_report'); ?>">
          <div class="icon">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_material_report'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material && $access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/item_report'); ?>">
          <div class="icon">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('stock_item_report'); ?></h4>
        </a>
      </div>
    <?php }?>
    
    <?php if($this->Permission_m->access_material_request == TRUE){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('materialrequests'); ?>">
          <div class="icon">
            <img src="<?php echo base_url();?>assets/Icons/rq.png" style="width: 27px;">
          </div>
          <h4 class="fw-500">ใบขอเบิก</h4>
        </a>
      </div>
    <?php }?>
  </div>
</div>
