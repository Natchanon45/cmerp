<div id="page-content" class="p20 clearfix">
    <?php if (true || @$cart_materials_count) { ?>
        <div class="process-pr-preview">
            <div class="panel panel-default">

                <?php echo form_open(get_uri("materialrequests/place_order"), array("id" => "place-pr-form", "class" => "general-form", "role" => "form")); ?>

                <div class="page-title clearfix">
                    <h1> <?php echo lang('process_request_order'); ?></h1>
                    <div class="title-button-group">
                       <a href="<?php echo get_uri("materialrequests");?>" class="btn btn-default"><?php echo lang('back_to_list');?></a>
                    </div>
                </div>

                <div class="p20">
                    <div class="mb20 ml15 mr15"><?php echo lang("process_pj_info_message"); ?></div>
                    
                    <div class="m15 pb15 mb30">
                        <div class="table-responsive">
                            <table id="pr-item-table" class="display mt0" width="100%">            
                            </table>
                        </div>
                        <div class="clearfix">
                            <div class="col-sm-8">

                            </div>
                            <div class="pull-right" id="pr-total-section">
                                <?php $this->load->view("materialrequests/processing_pj_total_section"); ?>
                            </div>
                        </div>
                    </div>

                    <?php if (false && isset($clients_dropdown) && $clients_dropdown) { ?>
                        <div class="form-group mt15 clearfix">
                            <div class="col-md-12">
                                <?php
                                echo form_dropdown("buyer_id", $clients_dropdown, $this->login_user->id, "class='select2 validate-hidden' id='buyer_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'");
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="form-group clearfix">
                        <div class=" col-md-12">
                            <?php
                            echo form_textarea(array(
                                "id" => "pr_note",
                                "name" => "pr_note",
                                "class" => "form-control",
                                "placeholder" => lang('note'),
                                "data-rich-text-editor" => true
                            ));
                            ?>
                        </div>
                    </div>
                </div>

                <div class="panel-footer clearfix">
                    <?php if(!$pr_id) {?>
                    <button type="submit" class="btn btn-primary pull-right ml10"><span class="fa fa-check-circle"></span> <?php echo lang('place_order1'); ?></button>
                    <?php }else{ ?>
                    <?php echo anchor( get_uri("materialrequests/view/$pr_id"), "<i class='fa fa-check-circle'></i> " . lang('place_order'), array("class" => "btn btn-primary pull-right ml10")); ?> 
                    <?php } ?>
                    <?php echo modal_anchor(get_uri("materialrequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_more_items'), array("class" => "btn btn-default pull-right", "title" => lang('add_more_items'), "data-post-id" => 0, "data-post-pr_id" => $pr_id,'data-post-item_type'=>'oth'));?>
                    <?php echo anchor(get_uri("pr_items/grid_view?pr_id=$pr_id"), "<i class='fa fa-plus-circle'></i> " . lang('add_internal_items1'), array("class" => "btn btn-default pull-right")); ?> 
                    <?php echo modal_anchor(get_uri("materialrequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_materials'), array("class" => "btn btn-default pull-right", "title" => lang('add_materials'), "data-post-id" => 0, "data-post-pr_id" => $pr_id,'data-post-item_type'=>'mtr'));?>
                </div>

                <?php echo form_close(); ?>

            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#place-pr-form").appForm({
                    isModal: false,
                    onSubmit: function () {
                        appLoader.show();
                        $("#place-pr-form").find('[type="submit"]').attr('disabled', 'disabled');
                    },
                    onSuccess: function (result) {
                        appLoader.hide();
                        window.location = result.redirect_to;
                    },
                    onError: function (result) {
                        appLoader.hide();
                    }
                });

                $("#buyer_id").select2();

                $("#pr-item-table").appTable({
                    source: '<?php echo_uri("purchaserequests/item_list_data_of_login_user".($pr_id?"/".$pr_id:"")) ?>',
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
                        <?php /*$("#pr-item-table").find("tbody").attr("id", "pr-item-table-sortable");
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
                        });*/?>
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

    <?php } else { ?>
        <div class="text-center box" style="height: 400px;">
            <div class="box-content" style="vertical-align: middle"> 
                <span class="fa fa-shopping-basket" style="font-size: 1400%; color:#d8d8d8"></span>
                <div class="mt15"><?php echo lang("no_items_text"); ?></div>
            </div>
        </div>  
    <?php } ?>

</div>