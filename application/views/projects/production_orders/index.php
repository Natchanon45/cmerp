<style type="text/css">
    .pointer-none {
        pointer-events: none;
    }

    .pill {
        display: inline-block;
        outline: none;
        padding: .5em .65em;
        font-size: 90%;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border: none;
        border-radius: .65rem;
        appearance: none;
    }

    .pill-primary {
        color: #fff;
        background-color: #007bff;
    }

    .pill-secondary {
        color: #fff;
        background-color: #6c757d;
    }

    .pill-success {
        color: #fff;
        background-color: #28a745;
    }

    .pill-danger {
        color: #fff;
        background-color: #dc3545;
    }

    .pill-warning {
        color: #212529;
        background-color: #ffc107;
    }

    .pill-info {
        color: #fff;
        background-color: #17a2b8;
    }
</style>

<div class="panel">
    <div class="tab-title clearfix">
        <h4>
            <?php echo lang('production_order'); ?>
        </h4>
        <div class="title-button-group">
            <?php
            echo modal_anchor(
                get_uri("projects/task_modal_form"),
                "<i class='fa fa-plus-circle'></i> " . lang('add_multiple_tasks'),
                array(
                    "class" => "btn btn-default",
                    "title" => lang('add_multiple_tasks'),
                    "data-post-project_id" => $project_info["id"],
                    "data-post-add_type" => "multiple"
                )
            );

            echo modal_anchor(
                get_uri("projects/production_order_modal_form"),
                "<i class='fa fa-plus-circle'></i> " . lang("production_order_add"),
                array(
                    "class" => "btn btn-default",
                    "title" => lang("production_order_add"),
                    "data-title" => lang("production_order_add"),
                    "data-post-project_id" => $project_info["id"]
                )
            );
            ?>
        </div>
    </div>
    <div class="table-responsive">
        <table id="production-order-table" class="display" width="100%">
        </table>
    </div>
</div>

<script type="text/javascript">
function loadProductionOrderList() {
    $("#production-order-table").appTable({
        source: '<?php echo_uri("projects/production_order_list/" . $project_info["id"]); ?>',
        columns: [
            { title: '<?php echo lang("id"); ?>', class: 'text-center' },
            { title: '<?php echo lang("item"); ?>', class: '' },
            { title: '<?php echo lang("item_mixing_name"); ?>' },
            { title: '<?php echo lang("quantity"); ?>', class: 'text-right' },
            { title: '<?php echo lang("stock_material_unit"); ?>', class: 'text-left' },
            { title: '<?php echo lang("production_order_rm_cost"); ?>', class: 'text-right' },
            { title: '<?php echo lang("currency"); ?>', class: 'text-left' },
            { title: '<?php echo lang("status") . "<br>" . lang("production_order_produce_status"); ?>', class: 'text-center' },
            { title: '<?php echo lang("status") . "<br>" . lang("production_order_mr_status"); ?>', class: 'text-center' },
            { title: '<i class="fa fa-bars"></i>', class: 'text-center option' }
        ]
    });
}

$(document).ready(function () {
    loadProductionOrderList();
});
</script>