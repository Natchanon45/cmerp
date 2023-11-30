
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
          <h4 class="fw-500"><?php echo lang('raw_mats'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/restocks'); ?>">
          <div class="icon">
            <i class="fa fa-database" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('raw_mats_restock'); ?></h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('sfg'); ?>">
          <div class="icon">
            <i class="fa fa-tags" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500">สินค้ากึ่งสำเร็จ</h4> 
        </a>
      </div>
    <?php }?>

    <?php if($access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('sfg/restock'); ?>">
          <div class="icon">
            <i class="fa fa-database" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500">นำเข้าสินค้ากึ่งสำเร็จ</h4>
        </a>
      </div>
    <?php }?>

    <?php if($access_material){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/items'); ?>">
          <div class="icon">
            <i class="fa fa-tags" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('finished_goods'); ?></h4> 
        </a>
      </div>
    <?php }?>

    <?php if($access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/restocks_item'); ?>">
          <div class="icon">
            <i class="fa fa-database" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('finished_goods_restock'); ?></h4>
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
        <a class="box-card-01 box-shadow" href="<?php echo_uri('sfg/report'); ?>">
          <div class="icon">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500">รายงานสินค้ากึ่งสำเร็จ</h4>
        </a>
      </div>
    <?php } ?>

    <?php if($access_material && $access_restock){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('stock/item_report'); ?>">
          <div class="icon">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('finished_goods_report'); ?></h4>
        </a>
      </div>
    <?php }?>
    
    <?php if($this->Permission_m->access_material_request == TRUE){?>
      <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('materialrequests'); ?>">
          <div class="icon">
          <i class="fa fa-cubes" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('materialrequests'); ?></h4>
        </a>
      </div>
    <?php }?>

    <div class="grid xl-20 lg-30 md-1-3">
        <a class="box-card-01 box-shadow" href="<?php echo_uri('purchaserequests'); ?>">
          <div class="icon">
          <i class="fa fa-cubes" aria-hidden="true"></i>
          </div>
          <h4 class="fw-500"><?php echo lang('material_shortage'); ?></h4>
        </a>
      </div>
  </div>
</div>
