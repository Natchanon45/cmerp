<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt15">
            <h1><?php echo $pr_info->doc_no?$pr_info->doc_no:lang('no_have_doc_no').':'.$pr_info->id; ?></h1>
            <div class="title-button-group">
                <?php //echo $proveButton ?>
                <a href="<?php echo base_url('index.php/purchaserequests'); ?>" style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn"><i class="fa fa-hand-o-left" aria-hidden="true"></i>ย้อนกลับไปตารางรายการ</a>
                <?php if($this->Permission_m->approve_purchase_request == true): ?>
                    <?php if($pr_info->status_id == 1 || $pr_info->status_id == 2): ?>
                        <a class="btn btn-info mt0 mb0 approval-btn approve-btn"  href="<?php echo base_url('index.php/purchaserequests/approve/'.$pr_info->id); ?>">อนุมัติ </a>
                        <a class="btn btn-danger mt0 mb0 approval-btn reject-btn"  href="<?php echo base_url('index.php/purchaserequests/disapprove/'.$pr_info->id); ?>">ไม่อนุมัติ </a>
                    <?php endif; ?>
                <?php endif; ?>

               
                <span class="dropdown inline-block">
                    <button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class='fa fa-cogs'></i> <?php echo lang('actions'); ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        
                        <?php if($prove_row && $is_approved) {?>
                        <li role="presentation"><?php echo anchor(get_uri("pdf_export/pr_pdf/" . $pr_info->id), "<i class='fa fa-download'></i> " . lang('download_pr_pdf'), array("title" => lang('download_pr_pdf'),)); ?> </li>
                        
                        <?php }?>
                        
                        <li role="presentation"><?php echo anchor( get_uri("purchaserequests/preview/" . $pr_info->id . "/1"), "<i class='fa fa-search'></i> ".lang('preview_pr'), array("title" => lang('preview_pr')), array("target" => "_blank")); ?> </li>
                        
                        <?php if($prove_row && $is_approved) {?>
                        <li role="presentation"><?php echo anchor(get_uri("pdf_export/po_pdf/" . $pr_info->id), "<i class='fa fa-download'></i> " . lang('download_po_pdf'), array("title" => lang('download_po_pdf'),)); ?> </li>
                        <li role="presentation"><?php echo anchor( get_uri("purchaserequests/preview_po/" . $pr_info->id . "/1"), "<i class='fa fa-search'></i> ".lang('preview_po'), array("title" => lang('preview_po')), array("target" => "_blank")); ?> </li>
                        <?php }?>
                       

                        <li role="presentation" class="divider"></li>
                        <?php if( ($edit_row && !$is_approved) || $prove_row) {?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("purchaserequests/modal_form"), "<i class='fa fa-edit'></i> " . lang('edit_purchaserequest'), array("title" => lang('edit_purchaserequest'), "data-post-id" => $pr_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                        <?php } ?>
                        <?php if($add_row) {?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("purchaserequests/modal_form"), "<i class='fa fa-copy'></i> " . lang('clone_pr'), array("title" => lang('clone_pr'), "data-post-id" => $pr_info->id, "data-post-is_clone"=>1, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                        <?php } ?>

                        <li role="presentation" class="divider"></li>
                        <?php /*if ($show_estimate_option) { ?>
                            <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form/"), "<i class='fa fa-file'></i> " . lang('create_estimate'), array("title" => lang("create_estimate"), "data-post-pr_id" => $pr_info->id)); ?> </li>
                        <?php } ?>
                        <?php if ($show_invoice_option) { ?>
                            <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i class='fa fa-file-text'></i> " . lang('create_invoice'), array("title" => lang("create_invoice"), "data-post-pr_id" => $pr_info->id)); ?> </li>
                        <?php }*/ ?>

                    </ul>
                </span>
                <?php if($pr_info->status_id == 1): ?>
                    <?php if($this->Permission_m->update_purchase_request == true): ?>
                        <?php echo anchor(get_uri("purchaserequests/process_pr/".$pr_info->id), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item'), "data-post-pr_id" => $pr_info->id)); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
		 
		
		<?php //echo $this->dao->getDocLabels( $pr_info->id  ); ?>
         <?
        /*
        1 = New = #f1c40f
        2 = Request Approval = #29c2c2
        3 = Approved = #83c340
        4 = Rejected = red
        */
        ?>
        <div class="panel panel-default  p15 no-border m0">
            <b>สถานะ:</b>
            <?php if($pr_info->status_id == 1): ?>
                <span class="mr10"><span class="mt0 label label-default large" >new</span></span>
            <?php elseif($pr_info->status_id == 3): ?>
                <span class="mr10"><span class="mt0 label label-default large" style="background-color: #83c340">อนุมัติ</span></span>
            <?php elseif($pr_info->status_id == 4): ?>
                <span class="mr10"><span class="mt0 label label-default large" style="background-color: red">ไม่อนุมัติ</span></span>
            <?php endif; ?>
        </div>
		
		
	
        <div class="mt15">
            <div class="panel panel-default p15 b-t">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css"> .invoice-meta {font-size: 100% !important;}</style>

                    <?php
                    $color = get_setting("pr_color");
                    if (!$color) {
                        $color = get_setting("invoice_color");
                    }
                    $style = get_setting("invoice_style");
                    ?>
                    <?php
                    $data = array(
                        "client_info" => $client_info,
                        "color" => $color ? $color : "#2AA384",
                        "pr_info" => $pr_info
                    );
                    if ($style === "style_2") {
                        $this->load->view('purchaserequests/pr_parts/header_style_2.php', $data);
                    } else {
                        $this->load->view('purchaserequests/pr_parts/header_style_1.php', $data);
                    }
                    ?>

                </div>

                <div class="table-responsive mt15 pl15 pr15">
                    <table id="pr-item-table" class="display" width="100%">            
                    </table>
                </div>

                <div class="clearfix">
                    <div class="col-sm-8">

                    </div>
                    <div class="pull-right pr15" id="pr-total-section">
                        <?php $this->load->view("purchaserequests/pr_total_section"); ?>
                    </div>
                </div>

                <p class="b-t b-info pt10 m15"><?php echo nl2br($pr_info->note); ?></p>
				
				
				 

            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    //RELOAD_VIEW_AFTER_UPDATE = true;
    $(document).ready(function () {
        /*jQuery('.approval-btn').each(function(idx, ele) {
            ele = jQuery(ele);
            ele.attr('a-href', ele.attr('href'));
            ele.attr('href', 'javascript:;');
            ele.on('click', function(){
                let is_approve = jQuery(this).hasClass('approve-btn');
                let is_unapprove = jQuery(this).hasClass('unapprove-btn');
                let is_reject = jQuery(this).hasClass('reject-btn');
                let is_requestapprove = jQuery(this).hasClass('requestapprove-btn');
                let status_id = 1;
                if(is_unapprove)
                    status_id = 1;
                if(is_requestapprove)
                    status_id = 2;
                if(is_approve)
                    status_id = 3;
                if(is_reject)
                    status_id = 4;
                jQuery.ajax({
                    url: "<?php echo get_uri('purchaserequests/updatestatus/'.$pr_info->id) ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {status_id: status_id},
                    success: function (result) {
                        if(result.success==true)
                            window.location.href = ele.attr('a-href');
                        if(result.success==false)
                            alert(result.message)
                    }
                });
            });
        });*/
        $("#pr-item-table").appTable({
            source: '<?php echo_uri("purchaserequests/item_list_data/" . $pr_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo lang("item") ?> ", "bSortable": false},
                {title: "<?php echo lang("project_name") ?>", "class": "text-center w20p", "bSortable": false},
                {title: "<?php echo lang("supplier_name") ?>", "class": "text-center w20p", "bSortable": false},
                {title: "<?php echo lang("quantity") ?>", "class": "text-right w10p", "bSortable": false},
                {title: "<?php echo lang("rate") ?>", "class": "text-right w10p", "bSortable": false},
                {title: "<?php echo lang("total") ?>", "class": "text-right w10p", "bSortable": false},
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100", "bSortable": false}
            ],

            onInitComplete: function () {
                //apply sortable
                <?php if($this->Permission_m->update_purchase_request == true):?>
                $("#pr-item-table").find("tbody").attr("id", "pr-item-table-sortable");
                var $selector = $("#pr-item-table-sortable");

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
                            url: '<?php echo_uri("purchaserequests/update_item_sort_values") ?>',
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });
                <?php endif; ?>
            },

            onDeleteSuccess: function (result) {
                $("#pr-total-section").html(result.pr_total_view);
            },
            onUndoSuccess: function (result) {
                $("#pr-total-section").html(result.pr_total_view);
            }
        });
    });

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

<?php if($prove_row) $this->load->view("purchaserequests/update_pr_status_script", array("details_view" => true)); ?>