<style>
    .total {
        position: absolute;
        top: 0;
        right: 0;
        text-align: right;
    }

    ul {
        list-style-type: none;
    }
</style>

<div id="page-content" class="p20 clearfix">
    <h4 class="m0 fw-500"><?php echo lang("accounting"); ?></h4>
   
    <div class="grids mt-2">
        <?php //if ($access_restock) { 
        ?>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('orders'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/or_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('orders'); ?></h4>

            </a>
            <a href="<?php echo_uri('orders'); ?>">
                <?php echo getCountTotal('orders') ?>
            </a>
        </div>
        <?php //} 
        ?>

        <?php //if ($access_material && $access_restock) { 
            if(!empty($view_rows['estimates']) && $view_rows['estimates'] != 0 || $this->login_user->is_admin == 1){
        ?>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('estimates'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/estimate_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('estimates'); ?></h4>

            </a>
            <a href="<?php echo_uri('estimates'); ?>">
                <?php echo getCountTotal('estimates') ?>
            </a>

        </div>
        <?php } ?>
      

        <?php //if ($access_material && $access_restock) {
            if(!empty($view_rows['invoices']) && $view_rows['invoices'] != 0 || $this->login_user->is_admin == 1){
        ?>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('invoices'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/bl_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('invoices'); ?></h4>

            </a>
            <a href="<?php echo_uri('invoices'); ?>">
                <?php echo getCountTotal('invoices') ?>
            </a>
        </div>
        <?php } ?>

        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('receipt_taxinvoices'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/INV_icon.png" style="width: 80px;">

                </div>
                <h4 class="fw-500"><?php echo lang('Receipt_Taxinvoice'); ?></h4>

            </a>
            <a href="<?php echo_uri('receipt_taxinvoices'); ?>">
                <?php echo getCountTotal('Receipt_Taxinvoice') ?>
            </a>
        </div>
        
        <?php if ($this->Permission_m->access_material_request == true): ?>
            <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
                <a class="box-card-01 box-shadow" href="<?php echo_uri('materialrequests'); ?>">
                    <div class="icon"><img src="<?php echo base_url();?>/assets/Icons/pv_icon.png" style="width: 80px;"></div>
                    <h4 class="fw-500">ใบขอเบิก</h4>
                </a>
                <a href="<?php echo_uri('materialrequests'); ?>"><?php echo getCountTotal('materialrequests') ?></a>
            </div>
        <?php endif; ?>

        <?php if($this->Permission_m->access_purchase_request == true): ?>
            <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
                <a class="box-card-01 box-shadow" href="<?php echo_uri('purchaserequests'); ?>">
                    <div class="icon"><img src="<?php echo base_url();?>/assets/Icons/pr_icon.png" style="width: 80px;"></div>
                    <h4 class="fw-500"><?php echo lang('purchaserequests'); ?></h4>
                </a>
                <a href="<?php echo_uri('purchaserequests'); ?>"><?php echo getCountTotal('purchaserequests') ?></a>
            </div>
        <?php endif; ?>

        <?php //if ($access_supplier) {             
        if(!empty($view_rows['purchaserequests']) && $view_rows['purchaserequests'] != 0 || $this->login_user->is_admin == 1){
        ?>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('purchaserequests/PO'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/po_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('po'); ?></h4>

            </a>
            <a href="<?php echo_uri('purchaserequests/PO'); ?>">
                <?php echo getCountTotal('purchaserequests') ?>
            </a>
        </div>
        
        <?php } ?>

        <?php //if ($access_calculator) { 
        ?>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('payment_vouchers'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/pv_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('payment_vouchers'); ?></h4>

            </a>
            <a href="<?php echo_uri('payment_vouchers'); ?>">
                <?php echo getCountTotal('payment_vouchers') ?>
            </a>

        </div>
        <div class="grid xl-25 lg-30 md-1-3" style="position:relative">
            <a class="box-card-01 box-shadow" href="<?php echo_uri('deliverys'); ?>">
                <div class="icon">
                    <img src="<?php echo base_url();?>/assets/Icons/pv_icon.png" style="width: 80px;">
                </div>
                <h4 class="fw-500"><?php echo lang('deliverys'); ?></h4>

            </a>
            <a href="<?php echo_uri('deliverys'); ?>">
                <?php echo getCountTotal('deliverys') ?>
            </a>

        </div>
        <?php //} ?>
    </div>
</div>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>