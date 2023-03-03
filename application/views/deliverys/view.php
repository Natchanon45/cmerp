 <div id="page-content" class="clearfix">
     <div style="max-width: 1000px; margin: auto;">
         <div class="page-title clearfix mt15">
             <h1><?php echo get_delivery_id(' ' . $delivery_info->doc_no); ?></h1>
             <div class="title-button-group">
                 <?php echo $proveButton ?>
                 <span class="dropdown inline-block">
                     <button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                         <i class='fa fa-cogs'></i> <?php echo lang('actions'); ?>
                         <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu" role="menu">
                         <li role="presentation"><?php echo anchor(get_uri("pdf_export/deliverys_pdf/" . $delivery_info->id), "<i class='fa fa-download'></i> " . lang('download_pdf')); ?> </li>
                         <!-- <li role="presentation"><?php echo anchor(get_uri("deliverys/download_pdf/" . $delivery_info->id . "/view"), "<i class='fa fa-file-pdf-o'></i> " . lang('view_pdf'), array("title" => lang('view_pdf'), "target" => "_blank")); ?> </li>  -->
                         <li role="presentation"><?php echo anchor(get_uri("deliverys/preview/" . $delivery_info->id . "/1"), "<i class='fa fa-search'></i> " . lang('delivery_preview'), array("title" => lang('delivery_preview')), array("target" => "_blank")); ?> </li>
                         <li role="presentation" class="divider"></li>
                         <li role="presentation"><?php echo modal_anchor(get_uri("deliverys/modal_form"), "<i class='fa fa-edit'></i> " . lang('edit_delivery'), array("title" => lang('edit_delivery'), "data-post-id" => $delivery_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                         <li role="presentation"><?php echo modal_anchor(get_uri("deliverys/modal_form"), "<i class='fa fa-copy'></i> " . lang('clone_delivery'), array("data-post-is_clone" => true, "data-post-id" => $delivery_info->id, "title" => lang('clone_delivery'))); ?></li>
                         <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i class='fa fa-refresh'></i> " . lang('create_invoice'), array("title" => lang("create_invoice"), "data-post-delivery_id" => $delivery_info->id)); ?> </li>

                         <!--don't show status changing option for leads-->
                         <?php
                            if (!$client_info->is_lead) {
                                if ($delivery_status == "draft") {
                            ?>
                                 <li role="presentation"><?php echo modal_anchor(get_uri("deliverys/send_delivery_modal_form/" . $delivery_info->id), "<i class='fa fa-send'></i> " . lang('send_to_client'), array("title" => lang('send_to_client'), "data-post-id" => $delivery_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                             <?php } else if ($delivery_status == "sent") { ?>
                                 <li role="presentation"><?php echo modal_anchor(get_uri("deliverys/send_delivery_modal_form/" . $delivery_info->id), "<i class='fa fa-send'></i> " . lang('send_to_client'), array("title" => lang('send_to_client'), "data-post-id" => $delivery_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                                 <li role="presentation"><?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/" . $delivery_info->id . "/accepted"), "<i class='fa fa-check-circle'></i> " . lang('mark_as_accepted'), array("data-reload-on-success" => "1")); ?> </li>
                                 <li role="presentation"><?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/" . $delivery_info->id . "/declined"), "<i class='fa fa-times-circle-o'></i> " . lang('mark_as_declined'), array("data-reload-on-success" => "1")); ?> </li>
                             <?php } else if ($delivery_status == "accepted") { ?>
                                 <li role="presentation"><?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/" . $delivery_info->id . "/declined"), "<i class='fa fa-times-circle-o'></i> " . lang('mark_as_declined'), array("data-reload-on-success" => "1")); ?> </li>
                             <?php } else if ($delivery_status == "declined") { ?>
                                 <li role="presentation"><?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/" . $delivery_info->id . "/accepted"), "<i class='fa fa-check-circle'></i> " . lang('mark_as_accepted'), array("data-reload-on-success" => "1")); ?> </li>
                         <?php
                                }
                            }
                            ?>

                         <?php if ($client_info->is_lead) { ?>
                             <li role="presentation"><?php echo modal_anchor(get_uri("deliverys/send_delivery_modal_form/" . $delivery_info->id), "<i class='fa fa-send'></i> " . lang('send_to_lead'), array("title" => lang('send_to_lead'), "data-post-id" => $delivery_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                         <?php } ?>

                         <?php
                            // var_dump($delivery_status);
                            if ($delivery_status == "accepted") { ?>
                             <li role="presentation" class="divider"></li>
                             <?php if ($can_create_projects && !$delivery_info->project_id) { ?>
                                 <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i class='fa fa-plus'></i> " . lang('create_project'), array("data-post-delivery_id" => $delivery_info->id, "title" => lang('create_project'), "data-post-client_id" => $delivery_info->client_id)); ?> </li>
                             <?php } ?>

                             <!-- <?php if ($show_invoice_option) { ?> -->
                             <!-- <?php } ?> -->
                         <?php } ?>
                     </ul>
                 </span>

                 <?php echo modal_anchor(get_uri("deliverys/item_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item'), "data-post-delivery_id" => $delivery_info->id)); ?>
                 <!-- <?php echo modal_anchor(get_uri("deliverys/from_address_modal"), "<i class='fa fa-plus-circle'></i> " . 'เปลี่ยนที่อยู่ผู้ส่ง', array("class" => "btn btn-default", "title" => 'เปลี่ยนที่อยู่ผู้ส่ง', "data-post-delivery_id" => $delivery_info->id)); ?> -->

             </div>
         </div>

         <?php echo $this->dao->getDocLabels($delivery_info->id, $delivery_status_label); ?>



         <!--
<div id="pr-status-bar">
            <div class="panel panel-default  p15 no-border m0">
    <span>สถานะ: <a href="#" style="background-color: #83c340" class="label" data-id="13" data-value="3" data-act="update-pr-status" data-original-title="" title="" popover-opened="true" data-hasppover="1">Approved</a></span>
 
    <span class="ml15">
        สร้างโดย: <a href="http://cosmatch/index.php/team_members/view/2">ERP Admin</a>    </span>
</div>        </div>	-->



         <script type="text/javascript">
             $(document).ready(function() {


                 var detailsView = false;
                 detailsView = true;

                 $('[data-act=update-pr-status]').click(function() {


                     var $instance = $(this);

                     $(this).appModifier({
                         value: $(this).attr('data-value'),
                         actionUrl: 'http://cosmatch/index.php/purchaserequests/save_pr_status/' + $(this).attr('data-id'),
                         placement: detailsView ? "right" : "auto",
                         select2Option: {
                             data: [{
                                 "id": "1",
                                 "text": "New"
                             }, {
                                 "id": "2",
                                 "text": "Request Approval"
                             }, {
                                 "id": "3",
                                 "text": "Approved"
                             }, {
                                 "id": "4",
                                 "text": "Rejected"
                             }]
                         },
                         onSuccess: function(response, newValue) {
                             if (response.success) {
                                 if (detailsView) {
                                     $instance.css("background-color", response.pr_status_color);
                                 } else {
                                     $(".dataTable:visible").appTable({
                                         newData: response.data,
                                         dataId: response.id
                                     });
                                 }
                             }
                         }
                     });

                     return false;
                 });
             });
         </script>




         <div class="mt15">
             <div class="panel panel-default p15 b-t">
                 <div class="clearfix p20">
                     <!-- small font size is required to generate the pdf, overwrite that for screen -->
                     <style type="text/css">
                         .invoice-meta {
                             font-size: 100% !important;
                         }
                     </style>

                     <?php
                        $color = get_setting("delivery_color");
                        if (!$color) {
                            $color = get_setting("invoice_color");
                        }
                        $style = get_setting("invoice_style");
                        ?>

                     <?php
                        $data = array(
                            "client_info" => $client_info,
                            "color" => $color ? $color : "#2AA384",
                            "delivery_info" => $delivery_info
                        );
                        if ($style === "style_2") {
                            $this->load->view('deliverys/delivery_parts/header_style_2.php', $data);
                        } else {
                            $this->load->view('deliverys/delivery_parts/header_style_1.php', $data);
                        }
                        ?>
                 </div>

                 <div class="table-responsive mt15 pl15 pr15">
                     <table id="delivery-item-table" class="display" width="100%">
                     </table>
                 </div>

                 <div class="clearfix">
                     <div class="col-sm-8">
                     </div>
                     <div class="pull-right pr15" id="delivery-total-section">
                         <table id="delivery-item-table" class="table display dataTable text-right strong table-responsive">

                         </table>
                     </div>
                 </div>

                 <p class="b-t b-info pt10 m15"><?php echo nl2br($delivery_info->note); ?></p>

             </div>

         </div>

     </div>
 </div>



 <script type="text/javascript">
     //RELOAD_VIEW_AFTER_UPDATE = true;
     $(document).ready(function() {
         $("#delivery-item-table").appTable({
             source: '<?php echo_uri("deliverys/item_list_data/" . $delivery_info->id . "/") ?>',
             order: [
                 [0, "asc"]
             ],
             hideTools: true,
             displayLength: 100,
             columns: [{
                     visible: false,
                     searchable: false
                 },
                 {
                     title: "<?php echo lang("item") ?> ",
                     "bSortable": false
                 },
                 {
                     title: "<?php echo lang("quantity") ?>",
                     "class": "text-right w15p",
                     "bSortable": false
                 },
                 {
                     title: "<i class='fa fa-bars'></i>",
                     "class": "text-center option w100",
                     "bSortable": false
                 }
             ],

             onInitComplete: function() {
                 //apply sortable
                 $("#delivery-item-table").find("tbody").attr("id", "delivery-item-table-sortable");
                 var $selector = $("#delivery-item-table-sortable");

                 Sortable.create($selector[0], {
                     animation: 150,
                     chosenClass: "sortable-chosen",
                     ghostClass: "sortable-ghost",
                     onUpdate: function(e) {
                         appLoader.show();
                         //prepare sort indexes 
                         var data = "";
                         $.each($selector.find(".item-row"), function(index, ele) {
                             if (data) {
                                 data += ",";
                             }

                             data += $(ele).attr("data-id") + "-" + index;
                         });

                         //update sort indexes
                         $.ajax({
                             url: '<?php echo_uri("deliverys/update_item_sort_values") ?>',
                             type: "POST",
                             data: {
                                 sort_values: data
                             },
                             success: function() {
                                 appLoader.hide();
                             }
                         });
                     }
                 });

             },

             onDeleteSuccess: function(result) {
                 $("#delivery-total-section").html(result.delivery_total_view);
                 if (typeof updateInvoiceStatusBar == 'function') {
                     updateInvoiceStatusBar(result.delivery_id);
                 }
             },
             onUndoSuccess: function(result) {
                 $("#delivery-total-section").html(result.delivery_total_view);
                 if (typeof updateInvoiceStatusBar == 'function') {
                     updateInvoiceStatusBar(result.delivery_id);
                 }
             }
         });
     });

     updateInvoiceStatusBar = function(deliveryId) {
         $.ajax({
             url: "<?php echo get_uri("deliverys/get_delivery_status_bar"); ?>/" + deliveryId,
             success: function(result) {
                 if (result) {
                     $("#delivery-status-bar").html(result);
                 }
             }
         });
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