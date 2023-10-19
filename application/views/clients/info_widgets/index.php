<div class="clearfix">
    <div class="col-md-3 col-sm-6 widget-container">
        <div class="panel panel-sky">
            <a href="<?php echo get_uri('projects/index'); ?>" class="white-link">
                <div class="panel-body ">
                    <div class="widget-icon">
                        <i class="fa fa-th-large"></i>
                    </div>
                    <div class="widget-details">
                        <h1><?php echo to_decimal_format(0); ?></h1>
                        <?php echo lang("projects"); ?>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6  widget-container">
        <div class="panel panel-primary">
            <a href="<?php echo get_uri('invoices/index'); ?>" class="white-link">
                <div class="panel-body ">
                    <div class="widget-icon">
                        <i class="fa fa-file-text"></i>
                    </div>
                    <div class="widget-details">
                        <h1><?php echo to_currency(0); ?></h1>
                        <?php echo lang("invoice_value"); ?>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6  widget-container">
        <div class="panel panel-success">
            <a href="<?php echo get_uri('invoice_payments/index'); ?>" class="white-link">
                <div class="panel-body ">
                    <div class="widget-icon">
                        <i class="fa fa-check-square"></i>
                    </div>
                    <div class="widget-details">
                        <h1><?php echo to_currency(0); ?></h1>
                        <?php echo lang("payments"); ?>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6  widget-container">
        <div class="panel panel-coral">
            <a href="<?php echo get_uri('invoices/index'); ?>" class="white-link">
                <div class="panel-body ">
                    <div class="widget-icon">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="widget-details">
                        <h1><?php echo to_currency(0); ?></h1>
                        <?php echo lang("due"); ?>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>