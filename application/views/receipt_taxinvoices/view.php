<?php //arr( $getRolePermission ) ?>

<div id="page-content" class="clearfix">
    <div style="max-width: 1100px; margin: auto;">
        <div class="page-title clearfix mt15">
            <h1><?php echo get_receipt_taxinvoice_id($receipt_taxinvoice_info->doc_no); ?>
                <?php
                if ($receipt_taxinvoice_info->recurring) {
                    $recurring_status_class = "text-primary";
                    if ($receipt_taxinvoice_info->no_of_cycles_completed > 0 && $receipt_taxinvoice_info->no_of_cycles_completed == $receipt_taxinvoice_info->no_of_cycles) {
                        $recurring_status_class = "text-danger";
                    }
                    ?>
                    <span class="label ml15 b-a "><span class="<?php echo $recurring_status_class; ?>"><?php echo lang('recurring'); ?></span></span>
                <?php } ?>
            </h1>
			
			
            <div class="title-button-group">
                <?php echo $proveButton ?>
                <span class="dropdown inline-block mt10">
                    <button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class='fa fa-cogs'></i> <?php echo lang('actions'); ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
					
					
                        <?php if ( $receipt_taxinvoice_status !== "cancelled" && !empty( $getRolePermission['edit_row'] ) ) { ?>
                            <!-- <li role="presentation"><?php //echo modal_anchor(get_uri("receipt_taxinvoices/send_receipt_taxinvoice_modal_form/" . $receipt_taxinvoice_info->id), "<i class='fa fa-envelope-o'></i> " . lang('email_receipt_taxinvoice_to_client'), array("title" => lang('email_receipt_taxinvoice_to_client'), "data-post-id" => $receipt_taxinvoice_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li> -->
                        <?php } ?>
                         <li role="presentation"><?php echo anchor(get_uri("pdf_export/receipt_taxinvoices_pdf/" . $receipt_taxinvoice_info->id), "<i class='fa fa-download'></i> " . lang('download_pdf'), array("title" => lang('download_pdf'))); ?> </li>
                        <!-- <li role="presentation"> -->
						
						
						<!-- <?php echo anchor(get_uri("receipt_taxinvoices/download_pdf/" . $receipt_taxinvoice_info->id . "/view"), "<i class='fa fa-file-pdf-o'></i> " . lang('view_pdf'), array("title" => lang('view_pdf'), "target" => "_blank")); ?> </li> -->
                        <li role="presentation"><?php echo anchor(get_uri("receipt_taxinvoices/preview/" . $receipt_taxinvoice_info->id . "/1"), "<i class='fa fa-search'></i> " . lang('receipt_taxinvoice_preview'), array("title" => lang('receipt_taxinvoice_preview'), "target" => "_blank")); ?> </li>
                        <!-- <li role="presentation"><?php echo js_anchor("<i class='fa fa-print'></i> " . lang('print_receipt_taxinvoice'), array('title' => lang('print_receipt_taxinvoice'), 'id' => 'print-receipt_taxinvoice-btn')); ?> </li> -->

                        <?php if ( !empty( $getRolePermission['edit_row'] ) ) { ?>
                            <!-- <li role="presentation" class="divider"></li> -->

                            <?php if ($receipt_taxinvoice_status !== "cancelled") { ?>
							
							
							
                                <!-- <li role="presentation"><?php //echo modal_anchor(get_uri("receipt_taxinvoices/modal_form"), "<i class='fa fa-edit'></i> " . lang('edit_receipt_taxinvoice'), array("title" => lang('edit_receipt_taxinvoice'), "data-post-id" => $receipt_taxinvoice_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li> -->
                           

						   <?php } ?>

                            <?php if ($receipt_taxinvoice_status == "draft" && $receipt_taxinvoice_status !== "cancelled") { ?>
                               

							   <!-- <li role="presentation"><?php //echo ajax_anchor(get_uri("receipt_taxinvoices/update_receipt_taxinvoice_status/" . $receipt_taxinvoice_info->id . "/not_paid"), "<i class='fa fa-check'></i> " . lang('mark_receipt_taxinvoice_as_not_paid'), array("data-reload-on-success" => "1")); ?> </li> -->
								
								
                            <?php } else if ($receipt_taxinvoice_status == "not_paid" || $receipt_taxinvoice_status == "overdue" || $receipt_taxinvoice_status == "partially_paid") { ?>
                                <li role="presentation"><?php echo js_anchor("<i class='fa fa-close'></i> " . lang('mark_receipt_taxinvoice_as_cancelled'), array('title' => lang('mark_receipt_taxinvoice_as_cancelled'), "data-action-url" => get_uri("receipt_taxinvoices/update_receipt_taxinvoice_status/" . $receipt_taxinvoice_info->id . "/cancelled"), "data-action" => "delete-confirmation", "data-reload-on-success" => "1")); ?> </li>
                            <?php } ?>
                            <!-- <li role="presentation"><?php //echo modal_anchor(get_uri("receipt_taxinvoices/modal_form"), "<i class='fa fa-copy'></i> " . lang('clone_receipt_taxinvoice'), array("data-post-is_clone" => true, "data-post-id" => $receipt_taxinvoice_info->id, "title" => lang('clone_receipt_taxinvoice'))); ?></li> -->
                        <?php } ?>

                    </ul>
                </span>
                <?php if ($receipt_taxinvoice_status !== "cancelled" && !empty( $getRolePermission['edit_row'] ) ) { ?>
                    <?php //echo modal_anchor(get_uri("receipt_taxinvoices/item_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item'), "data-post-receipt_taxinvoice_id" => $receipt_taxinvoice_info->id)); ?>
					
					
					
                    <?php echo modal_anchor(get_uri("receipt_taxinvoice_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("class" => "btn btn-default", "title" => lang('add_payment'), "data-post-receipt_taxinvoice_id" => $receipt_taxinvoice_info->id)); ?>
                <?php } ?>
            </div>
			
			
        </div>

<?php echo $this->dao->getDocLabels( $receipt_taxinvoice_info->id ); ?>
       

        <?php
        if ($receipt_taxinvoice_info->recurring) {
            $this->load->view("receipt_taxinvoices/receipt_taxinvoice_recurring_info_bar");
        }
        ?>

        <div class="mt15">
            <div class="panel panel-default p15 b-t">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css"> .receipt_taxinvoice-meta {font-size: 100% !important;}</style>

                    <?php
                    $color = get_setting("receipt_taxinvoice_color");
                    if (!$color) {
                        $color = "#2AA384";
                    }
                    $receipt_taxinvoice_style = get_setting("receipt_taxinvoice_style");
                    $data = array(
                        "client_info" => $client_info,
                        "color" => $color,
                        "receipt_taxinvoice_info" => $receipt_taxinvoice_info
                    );

                    if ($receipt_taxinvoice_style === "style_2") {
                        $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/header_style_2.php', $data);
                    } else {
                        $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/header_style_1.php', $data);
                    }
                    ?>
                </div>

                <div class="table-responsive mt15 pl15 pr15">
                    <table id="receipt_taxinvoice-item-table" class="display" width="100%">            
                    </table>
                </div>

                <div class="clearfix">
                    <div class="pull-right pr15" id="receipt_taxinvoice-total-section">
<?php $this->load->view( "receipt_taxinvoices/receipt_taxinvoice_total_section", array( "receipt_taxinvoice_id" => $receipt_taxinvoice_info->id, "can_edit_receipt_taxinvoices" => $can_edit_receipt_taxinvoices ) ); ?>
                    </div>
                </div>

                <?php
                $files = @unserialize($receipt_taxinvoice_info->files);
                if ($files && is_array($files) && count($files)) {
                    ?>
                    <div class="clearfix">
                        <div class="col-md-12 mt20">
                            <p class="b-t"></p>
                            <div class="mb5 strong"><?php echo lang("files"); ?></div>
                            <?php
                            foreach ($files as $key => $value) {
                                $file_name = get_array_value($value, "file_name");
                                echo "<div>";
                                echo js_anchor(remove_file_prefix($file_name), array("data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("receipt_taxinvoices/file_preview/" . $receipt_taxinvoice_info->id . "/" . $key)));
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <p class="b-t b-info pt10 m15"><?php echo nl2br($receipt_taxinvoice_info->note); ?></p>

            </div>
        </div>



        <?php if ($receipt_taxinvoice_info->recurring) { ?>
            <ul id="receipt_taxinvoice-view-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist">
                <li><a  role="presentation" href="#" data-target="#receipt_taxinvoice-payments"> <?php echo lang('payments'); ?></a></li>
                <li><a  role="presentation" href="<?php echo_uri("receipt_taxinvoices/sub_receipt_taxinvoices/" . $receipt_taxinvoice_info->id); ?>" data-target="#sub-receipt_taxinvoices"> <?php echo lang('sub_receipt_taxinvoices'); ?></a></li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade active" id="receipt_taxinvoice-payments">
                    <div class="panel panel-default">
                        <div class="tab-title clearfix">
                            <h4> <?php echo lang('receipt_taxinvoice_payment_list'); ?></h4>
                        </div>
                        <div class="table-responsive">
                            <table id="receipt_taxinvoice-payment-table" class="display" cellspacing="0" width="100%">            
                            </table>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="sub-receipt_taxinvoices"></div>
            </div>
        <?php } else { ?>

            <div class="panel panel-default">
                <div class="tab-title clearfix">
                    <h4> <?php echo lang('receipt_taxinvoice_payment_list'); ?></h4>
                </div>
                <div class="table-responsive">
                    <table id="receipt_taxinvoice-payment-table" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function () {

        $('[name="pay_sp"]').keyup(function(){
            me = $(this);
            q = {};
            q.pay_sp = me.val();
            q.pay_type = $('[name="pay_type"]').val();
            
            $.getJSON('<?php echo get_uri('receipt_taxinvoices/pay_split/').$receipt_taxinvoice_info->id?>',q,function(data){
                // alert(data.vat_B);
                // alert(data.after_vat);
                $('.load_pay_sp').html(data.after_vat);
                $('.load_pay_vat').html(data.vat_bath);
            });
        });

        $('[name="pay_type"]').change(function(){
            me = $(this);
            q = {};
            q.pay_sp = $('[name="pay_sp"]').val();
            q.pay_type = me.val();
            $.getJSON('<?php echo get_uri('receipt_taxinvoices/pay_split/').$receipt_taxinvoice_info->id?>',q,function(data){
                // alert(data.vat_B);
                // alert(data.after_vat);
                $('.load_pay_sp').html(data.after_vat);
                $('.load_pay_vat').html(data.vat_bath);
            });
        });

        var optionVisibility = false;
        if ("<?php echo $can_edit_receipt_taxinvoices ?>") {
            optionVisibility = true;
        }

        $("#receipt_taxinvoice-item-table").appTable({
            source: '<?php echo_uri("receipt_taxinvoices/item_list_data/" . $receipt_taxinvoice_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false, searchable: false},
                {title: '<?php echo lang("item") ?> ', "bSortable": false},
                {title: '<?php echo lang("quantity") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<?php echo lang("rate") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<?php echo lang("total") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100", "bSortable": false, visible: optionVisibility}
            ],
            onInitComplete: function () {
<?php if ($can_edit_receipt_taxinvoices) { ?>
                    //apply sortable
                    $("#receipt_taxinvoice-item-table").find("tbody").attr("id", "receipt_taxinvoice-item-table-sortable");
                    var $selector = $("#receipt_taxinvoice-item-table-sortable");

                    Sortable.create($selector[0], {
                        animation: 150,
                        chosenClass: "sortable-chosen",
                        ghostClass: "sortable-ghost",
                        onUpdate: function (e) {
                            appLoader.show();
                            //prepare sort indexes 
                            var data = "";
                            $.each($selector.find(".item-row"), function (index, ele) {
                                if (data) {
                                    data += ",";
                                }

                                data += $(ele).attr("data-id") + "-" + index;
                            });

                            //update sort indexes
                            $.ajax({
                                url: '<?php echo_uri("Invoices/update_item_sort_values") ?>',
                                type: "POST",
                                data: {sort_values: data},
                                success: function () {
                                    appLoader.hide();
                                }
                            });
                        }
                    });

<?php } ?>

            },
            onDeleteSuccess: function (result) {
                $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.receipt_taxinvoice_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.receipt_taxinvoice_id);
                }
            }
        });

        $("#receipt_taxinvoice-payment-table").appTable({
            source: '<?php echo_uri("receipt_taxinvoice_payments/payment_list_data/" . $receipt_taxinvoice_info->id . "/") ?>',
            order: [[0, "asc"]],
            columns: [
                {targets: [0], visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: '<?php echo lang("payment_date") ?> ', "class": "w15p", "iDataSort": 1},
                {title: '<?php echo lang("payment_method") ?>', "class": "w15p"},
                {title: '<?php echo lang("note") ?>'},
                {title: '<?php echo lang("amount") ?>', "class": "text-right w15p"},
                {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100", visible: optionVisibility}
            ],
            onDeleteSuccess: function (result) {
                $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.receipt_taxinvoice_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.receipt_taxinvoice_id);
                }
            }
        });

        //modify the delete confirmation texts
        $("#confirmationModalTitle").html("<?php echo lang('cancel') . "?"; ?>");
        $("#confirmDeleteButton").html("<i class='fa fa-times'></i> <?php echo lang("cancel"); ?>");
    });

    updateInvoiceStatusBar = function (receipt_taxinvoiceId) {
        $.ajax({
            url: "<?php echo get_uri("receipt_taxinvoices/get_receipt_taxinvoice_status_bar"); ?>/" + receipt_taxinvoiceId,
            success: function (result) {
                if (result) {
                    $("#receipt_taxinvoice-status-bar").html(result);
                }
            }
        });
    };

    //print receipt_taxinvoice
    $("#print-receipt_taxinvoice-btn").click(function () {
        appLoader.show();

        $.ajax({
            url: "<?php echo get_uri('receipt_taxinvoices/print_receipt_taxinvoice/' . $receipt_taxinvoice_info->id) ?>",
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    document.body.innerHTML = result.print_view; //add receipt_taxinvoice's print view to the page
                    $("html").css({"overflow": "visible"});

                    setTimeout(function () {
                        window.print();
                    }, 200);
                } else {
                    appAlert.error(result.message);
                }

                appLoader.hide();
            }
        });
    });

    //reload page after finishing print action
    window.onafterprint = function () {
        location.reload();
    };

</script>

<?php
//required to send email 

load_css(array(
    "assets/js/summernote/summernote.css",
));
load_js(array(
    "assets/js/summernote/summernote.min.js",
));
?>

