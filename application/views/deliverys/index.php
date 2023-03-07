<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="delivery-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang('deliverys'); ?></h4></li>
            <li><a id="monthly-delivery-button" class="active" role="presentation" href="javascript:;" data-target="#monthly-deliverys"><?php echo lang("monthly"); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("deliverys/yearly/"); ?>" data-target="#yearly-deliverys"><?php echo lang('yearly'); ?></a></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("deliverys/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_delivery'), array("class" => "btn btn-default", "title" => lang('add_delivery'))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-deliverys">
                <div class="table-responsive">
                    <table id="monthly-delivery-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-deliverys"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loaddeliverysTable = function (selector, dateRange) {
        $(selector).appTable({
            source: '<?php echo_uri("deliverys/list_data") ?>',
            order: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [{name: "status", class: "w150", options: <?php $this->load->view("deliverys/delivery_statuses_dropdown"); ?>}],
            columns: [
                {title: "<?php echo lang("delivery") ?> ", "class": "w15p"},
                {title: "<?php echo lang("client") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo lang("delivery_date") ?>", "iDataSort": 2, "class": "w20p"},
                {title: "<?php echo lang("status") ?>", "class": "text-center"}
                <?php echo $custom_field_headers; ?>,
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>')
        });
    };

    $(document).ready(function () {
        loaddeliverysTable("#monthly-delivery-table", "monthly");
    });

</script>