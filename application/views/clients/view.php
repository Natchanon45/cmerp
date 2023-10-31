<style type="text/css">
.widget-icon{
    font-size: 52px;
}

.widget-details h1{
    font-size: 28px;
}
</style>
<div class="page-title clearfix no-border bg-off-white">
    <h1>
        <?php echo lang('client_details') . " - " . $crow->company_name ?>
        <span id="star-mark">
            <?php
            /*if ($is_starred) {
                $this->load->view('clients/star/starred', array("client_id" => $crow->id));
            } else {
                $this->load->view('clients/star/not_starred', array("client_id" => $crow->id));
            }*/
            ?>
        </span>
    </h1>
</div>

<div id="page-content" class="clearfix">
    <div class="mt15 clearfix">
        <div class="col-md-6  widget-container">
            <div class="panel panel-sky">
                <a id="summary_project" class="white-link" style="cursor: pointer;">
                    <div class="panel-body ">
                        <div class="widget-icon">
                            <i class="fa fa-th-large"></i>
                        </div>
                        <div class="widget-details">
                            <h1><?php echo to_decimal_format($summary["total_projects"]); ?></h1>
                            <?php echo lang("projects"); ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-6  widget-container">
            <div class="panel panel-primary">
                <a class="white-link">
                    <div class="panel-body ">
                        <div class="widget-icon">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <div class="widget-details">
                            <h1><?php echo to_currency($summary["total_invoice_amounts"]); ?></h1>
                            <?php echo lang("invoice_value"); ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-6  widget-container">
            <div class="panel panel-success">
                <a class="white-link">
                    <div class="panel-body ">
                        <div class="widget-icon">
                            <i class="fa fa-check-square"></i>
                        </div>
                        <div class="widget-details">
                            <h1><?php echo to_currency($summary["total_payment_receives"]); ?></h1>
                            <?php echo lang("payments"); ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-6  widget-container">
            <div class="panel panel-coral">
                <a class="white-link">
                    <div class="panel-body ">
                        <div class="widget-icon">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="widget-details">
                            <h1><?php echo to_currency($summary["total_due_date_invoice_amount"]); ?></h1>
                            <?php echo lang("due"); ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div><!--.mt15-->

    <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist">
        <li><a role="presentation" href="<?php echo_uri("clients/contacts/" . $crow->id); ?>" data-target="#client-contacts"> <?php echo lang('contacts'); ?></a></li>
        <li><a role="presentation" href="<?php echo_uri("clients/company_info_tab/" . $crow->id); ?>" data-target="#client-info"> <?php echo lang('client_info'); ?></a></li>
        <li><a id="tab_project" role="presentation" href="<?php echo_uri("clients/projects/" . $crow->id); ?>" data-target="#client-projects"><?php echo lang('projects'); ?></a></li>
        
        <!--<li><a  role="presentation" href="<?php echo_uri("clients/invoices/" . $crow->id); ?>" data-target="#client-invoices"> <?php echo lang('invoices'); ?></a></li>
        <li><a  role="presentation" href="<?php echo_uri("clients/payments/" . $crow->id); ?>" data-target="#client-payments"> <?php echo lang('payments'); ?></a></li>-->
        
        <?php if ($show_estimate_info) { ?>
            <!--<li><a  role="presentation" href="<?php echo_uri("clients/estimates/" . $crow->id); ?>" data-target="#client-estimates"> <?php echo lang('estimates'); ?></a></li>-->
        <?php } ?>
        <?php if ($show_order_info) { ?>
            <li><a  role="presentation" href="<?php echo_uri("clients/orders/" . $crow->id); ?>" data-target="#client-orders"> <?php echo lang('orders'); ?></a></li>
        <?php } ?>
        <?php if ($show_estimate_request_info) { ?>
            <!--<li><a  role="presentation" href="<?php echo_uri("clients/estimate_requests/" . $crow->id); ?>" data-target="#client-estimate-requests"> <?php echo lang('estimate_requests'); ?></a></li>-->
        <?php } ?>
        <?php if ($show_ticket_info) { ?>
            <li><a  role="presentation" href="<?php echo_uri("clients/tickets/" . $crow->id); ?>" data-target="#client-tickets"> <?php echo lang('tickets'); ?></a></li>
        <?php } ?>
        <?php if ($show_note_info) { ?>
            <li><a  role="presentation" href="<?php echo_uri("clients/notes/" . $crow->id); ?>" data-target="#client-notes"> <?php echo lang('notes'); ?></a></li>
        <?php } ?>
        <li><a  role="presentation" href="<?php echo_uri("clients/files/" . $crow->id); ?>" data-target="#client-files"><?php echo lang('files'); ?></a></li>

        <?php if ($show_event_info) { ?>
            <li><a  role="presentation" href="<?php echo_uri("clients/events/" . $crow->id); ?>" data-target="#client-events"> <?php echo lang('events'); ?></a></li>
        <?php } ?>

        <?php if ($show_expense_info) { ?>
            <!--<li><a  role="presentation" href="<?php echo_uri("clients/expenses/" . $crow->id); ?>" data-target="#client-expenses"> <?php echo lang('expenses'); ?></a></li>-->
        <?php } ?>
        
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="client-projects"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-files"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-contacts"></div>
        <!--<div role="tabpanel" class="tab-pane fade" id="client-invoices"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-payments"></div>-->
        <div role="tabpanel" class="tab-pane fade" id="client-estimates"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-orders"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-estimate-requests"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-tickets"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-notes"></div>
        <div role="tabpanel" class="tab-pane" id="client-events" style="min-height: 300px"></div>
        <div role="tabpanel" class="tab-pane fade" id="client-expenses"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $("#summary_project").on("click", function(){
        $("#tab_project").trigger("click");
    });
    setTimeout(function () {
        var tab = "<?php echo $tab; ?>";
        if (tab === "info") {
            $("[data-target=#client-info]").trigger("click");
        } else if (tab === "projects") {
            $("[data-target=#client-projects]").trigger("click");
        } else if (tab === "invoices") {
            $("[data-target=#client-invoices]").trigger("click");
        } else if (tab === "payments") {
            $("[data-target=#client-payments]").trigger("click");
        }
    }, 210);
});
</script>
