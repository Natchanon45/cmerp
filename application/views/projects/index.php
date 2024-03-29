<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1><?php echo lang('projects'); ?></h1>
            <div class="title-button-group">
                <?php
                    if(get_array_value($this->login_user->permissions, "can_create_projects") == true || $this->login_user->is_admin == "1") {
                        if ($can_edit_projects) {
                            echo modal_anchor(
                                get_uri("labels/modal_form"), 
                                "<i class='fa fa-tags'></i> " . lang('manage_labels'), 
                                array(
                                    "class" => "btn btn-default", 
                                    "title" => lang('manage_labels'), 
                                    "data-post-type" => "project"
                                )
                            );
                        }

                        echo modal_anchor(
                            get_uri("projects/modal_form"), 
                            "<i class='fa fa-plus-circle'></i> " . lang('add_project'), 
                            array(
                                "class" => "btn btn-default", 
                                "title" => lang('add_project')
                            )
                        );
                    }
                ?>
            </div>
        </div>

        <div class="table-responsive">
            <table id="project-table" class="display" cellspacing="0" width="100%"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if (<?php echo intval($can_edit_projects || $can_delete_projects); ?>) {
            optionVisibility = true;
        }

        var selectOpenStatus = true, selectCompletedStatus = false;
        <?php if (isset($status) && $status == "completed") { ?>
            selectOpenStatus = false;
            selectCompletedStatus = true;
        <?php } ?>

        filterDropdown = [];
        filterDropdown.push({name: "project_label", class: "w150", options: <?php echo $project_labels_dropdown; ?>});

        <?php if(isset($project_type_dropdown)):?>
            filterDropdown.push({name: "project_type", class: "w150", options: <?php echo $project_type_dropdown; ?>});
        <?php endif;?>


        $("#project-table").appTable({
            source: '<?php echo_uri("projects/list_data") ?>',
            multiSelect: [{
                name: "status",
                text: "<?php echo lang('status'); ?>",
                options: [
                    { text: '<?php echo lang("open") ?>', value: "open", isChecked: selectOpenStatus },
                    { text: '<?php echo lang("completed") ?>', value: "completed", isChecked: selectCompletedStatus },
                    { text: '<?php echo lang("hold") ?>', value: "hold" },
                    { text: '<?php echo lang("canceled") ?>', value: "canceled" }
                ]
            }],
            filterDropdown: filterDropdown,
            singleDatepicker: [{
                                name: "deadline", defaultText: "<?php echo lang('deadline') ?>",
                                options: [
                                    { value: "expired", text: "<?php echo lang('expired') ?>" },
                                    { value: moment().format("YYYY-MM-DD"), text: "<?php echo lang('today') ?>" },
                                    { value: moment().add(1, 'days').format("YYYY-MM-DD"), text: "<?php echo lang('tomorrow') ?>" },
                                    { value: moment().add(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(lang('in_number_of_days'), 7); ?>" },
                                    { value: moment().add(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(lang('in_number_of_days'), 15); ?>" }
                                ]
            }],
            columns: [
                { title: '<?php echo lang("id") ?>', "class": "w5p text-center" },
                { title: '<?php echo lang("title") ?>', "class": "w15p" },
                { title: '<?php echo lang("client") ?>', "class": "w15p" },
                { title: '<?php echo lang("owner") ?>', "class": "w15p" },
                { visible: optionVisibility, title: '<?php echo lang("price") ?>', "class": "w10p" },
                { visible: false, searchable: false },
                { title: '<?php echo lang("start_date") ?>', "class": "w10p", "iDataSort": 4 },
                { visible: false, searchable: false },
                { title: '<?php echo lang("deadline") ?>', "class": "w10p", "iDataSort": 6 },
                { title: '<?php echo lang("progress") ?>', "class": "w10p" },
                { title: '<?php echo lang("status") ?>', "class": "w5p" }
                <?php echo $custom_field_headers; ?>,
                { visible: optionVisibility, title: '<i class="fa fa-bars"></i>', "class": "text-center option w120" }
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 7, 8, 9, 10], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>

<style type="text/css">
.w120 {
    width: 120px !important;
    min-width: 120px !important;
}
    
table#project-table > thead > tr > th:nth-child(2), table#project-table > tbody > tr > td:nth-child(2) {
    display: table-cell !important;
}
</style>
