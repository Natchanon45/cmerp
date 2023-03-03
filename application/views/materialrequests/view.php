<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt15" style="padding-top:12px; padding-bottom: 12px;">
            <h1 style="padding-top: 8px; padding-bottom:0;"><?php echo $mr_info->doc_no?$mr_info->doc_no:lang('no_have_doc_no').':'.$mr_info->id; ?></h1>

            <div class="title-button-group">
                <?php //\\echo $proveButton ?>

                <a href="<?php echo base_url('index.php/materialrequests'); ?>" style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn"><i class="fa fa-hand-o-left" aria-hidden="true"></i>ย้อนกลับไปตารางรายการ</a>
                <?php if($this->Permission_m->approve_material_request == true): ?>
                    <?php if($mr_info->status_id == 1 || $mr_info->status_id == 2): ?>
                        <a class="btn btn-info mt0 mb0 approval-btn approve-btn"  href="<?php echo base_url('index.php/materialrequests/approve/'.$mr_info->id); ?>">อนุมัติ </a>
                        <a class="btn btn-danger mt0 mb0 approval-btn reject-btn"  href="<?php echo base_url('index.php/materialrequests/disapprove/'.$mr_info->id); ?>">ไม่อนุมัติ </a>
                    <?php endif; ?>
                <?php endif; ?>

                <span class="dropdown inline-block">
                    <button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class='fa fa-cogs'></i> <?php echo lang('actions'); ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li role="presentation"><?php echo anchor(get_uri("pdf_export/materialrequests_pdf/" . $mr_info->id), "<i class='fa fa-download'></i> ดาวน์โหลด PDF ใบขอเบิก", array("title" => lang('download_pr_pdf'),)); ?> </li>
                        
                        
                        
                        <li role="presentation"><?php echo anchor( get_uri("materialrequests/preview/" . $mr_info->id . "/1"), "<i class='fa fa-search'></i> ".lang('preview_mr'), array("title" => lang('preview_mr')), array("target" => "_blank")); ?> </li>
                       
                        <!--<li role="presentation" class="divider"></li>-->

                        <?php if($this->Permission_m->update_material_request == true): ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("materialrequests/modal_form"), "<i class='fa fa-edit'></i> " . lang('edit_materialrequest'), array("title" => lang('edit_materialrequest'), "data-post-id" => $mr_info->id, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("materialrequests/modal_form"), "<i class='fa fa-copy'></i> " . lang('clone_mr'), array("title" => lang('clone_mr'), "data-post-id" => $mr_info->id, "data-post-is_clone"=>1, "role" => "menuitem", "tabindex" => "-1")); ?> </li>
                        <?php endif; ?>


                    </ul>
                </span>
                
                <?php //echo anchor(get_uri("materialrequests/process_pr/".$mr_info->id), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item'), "data-post-pr_id" => $mr_info->id)); ?>
                
            </div>
        </div>
		 
		
		<?php //echo $this->dao->getDocLabels( $mr_info->id  ); ?>
        <div class="panel panel-default  p15 no-border m0">
            <b>สถานะ:</b>
            <?php if($mr_info->status_id == 1): ?>
                <span class="mr10"><span class="mt0 label label-default large">new</span></span>
            <?php elseif($mr_info->status_id == 3): ?>
                <span class="mr10"><span class="mt0 label label-default large" style="background-color: #83c340">อนุมัติ</span></span>
            <?php elseif($mr_info->status_id == 4): ?>
                <span class="mr10"><span class="mt0 label label-default large" style="background-color: red">ไม่อนุมัติ</span></span>
            <?php endif; ?>
        </div>
        
	
        <div class="mt15">
            <div class="panel panel-default p15 b-t">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css"> .invoice-meta {font-size: 100% !important;}</style>
                    <?php if(@$_SESSION['error']){
                        echo '<div class="alert alert-danger" role="alert">'.@$_SESSION['error'].'</div>';
                        unset($_SESSION['error']);
                    }?>
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
                        "pr_info" => $mr_info
                    );
                    if ($style === "style_2") {
                        $this->load->view('materialrequests/mr_parts/header_style_2.php', $data);
                    } else {
                        $this->load->view('materialrequests/mr_parts/header_style_1.php', $data);
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
                    <!-- <div class="pull-right pr15" id="pr-total-section">
                        <?php //$this->load->view("materialrequests/pr_total_section"); ?>
                    </div> -->
                </div>

                <p class="b-t b-info pt10 m15"><?php echo nl2br($mr_info->note); ?></p>
				
				
				 

            </div>
        </div>

    </div>
</div>

<style>
    .unapprove-btn{
        display: none;
    }
</style>

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
                    url: "<?php echo get_uri('materialrequests/updatestatus/'.$mr_info->id) ?>",
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
            source: '<?php echo_uri("materialrequests/item_list_data/" . $mr_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo lang("item") ?> ", "bSortable": false},
                {title: "<?php echo lang("quantity") ?>", "class": "text-right w10p", "bSortable": false},
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100", "bSortable": false}
            ],

            onInitComplete: function () {
                //apply sortable
                <?php if($this->Permission_m->update_material_request == true):?>
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
                            url: '<?php echo_uri("materialrequests/update_item_sort_values") ?>',
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

<?php if($prove_row) $this->load->view("materialrequests/update_pr_status_script", array("details_view" => true)); ?>