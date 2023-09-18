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
        width: 90%;
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
        color: #fff;
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
            <?php echo lang("production_order"); ?>
        </h4>
        <div class="title-button-group">
            <?php
            echo modal_anchor(
                get_uri("projects/production_order_change_to_completed_all"),
                "<i class='fa fa-check-square'></i> " . lang("production_order_change_to_completed"),
                array(
                    "class" => "btn btn-success",
                    "title" => lang("production_order_change_to_completed"),
                    "data-title" => lang("production_order_change_to_completed"),
                    "data-post-project_id" => $project_info["id"]
                )
            );

            echo modal_anchor(
                get_uri("projects/production_order_change_to_producing_all"),
                "<i class='fa fa-check-square-o'></i> " . lang("production_order_change_to_producing"),
                array(
                    "class" => "btn btn-warning",
                    "title" => lang("production_order_change_to_producing"),
                    "data-title" => lang("production_order_change_to_producing"),
                    "data-post-project_id" => $project_info["id"]
                )
            );

            echo modal_anchor(
                get_uri("projects/production_order_mr_creation_all"),
                "<i class='fa fa-plus-circle'></i> " . lang("production_order_create_mr_all"),
                array(
                    "class" => "btn btn-info",
                    "title" => lang("production_order_create_mr_all"),
                    "data-title" => lang("production_order_create_mr_all"),
                    "data-post-project_id" => $project_info["id"],
                    "data-post-project_name" => $project_info["title"]
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
    <div id="table-wrapper" class="table-responsive"></div>
</div>

<script type="text/javascript">
async function loadProductionOrderList() {
    let ajaxTable = '<table id="production-order-table" class="display" width="100%"></table>';
    let ajaxApp = {
        source: '<?php echo_uri("projects/production_order_list/" . $project_info["id"]); ?>',
        order: [[0, 'desc']],
        filterDropdown: [
            {
                name: 'mr_status',
                class: 'w200',
                options: [
                    { id: 0, text: '<?php echo "-- " . lang("production_order_mr_status_select") . " --"; ?>' },
                    { id: 1, text: '<?php echo lang("production_order_not_yet_withdrawn"); ?>' },
                    { id: 2, text: '<?php echo lang("production_order_partially_withdrawn"); ?>' },
                    { id: 3, text: '<?php echo lang("production_order_completed_withdrawal"); ?>' }
                ]
            },
            {
                name: 'produce_status',
                class: 'w200',
                options: [
                    { id: 0, text: '<?php echo "-- " . lang("production_order_produce_status_select") . " --"; ?>' },
                    { id: 1, text: '<?php echo lang("production_order_not_yet_produce"); ?>' },
                    { id: 2, text: '<?php echo lang("production_order_producing"); ?>' },
                    { id: 3, text: '<?php echo lang("production_order_produced_completed"); ?>' }
                ]
            }
        ],
        destroy: true,
        columns: [
            { title: '<?php echo lang("id"); ?>', class: 'text-center' },
            { title: '<?php echo lang("item"); ?>' },
            { title: '<?php echo lang("item_mixing_name"); ?>' },
            { title: '<?php echo lang("quantity"); ?>', class: 'text-right' },
            { title: '<?php echo lang("stock_material_unit"); ?>', class: 'text-left' },
            { title: '<?php echo lang("production_order_rm_cost"); ?>', class: 'text-right' },
            { title: '<?php echo lang("currency"); ?>', class: 'text-left' },
            { title: '<?php echo lang("status") . '<br>' . lang("production_order_produce_status"); ?>', class: 'text-center' },
            { title: '<?php echo lang("status") . '<br>' . lang("production_order_mr_status"); ?>', class: 'text-center' },
            { title: '<i class="fa fa-bars"></i>', class: 'text-center option' }
        ],
        summation: [
            { column: 5, dataType: 'currency' }
        ]
    };

    await $("#table-wrapper").empty();
    await $("#table-wrapper").append(ajaxTable);

    await $("#production-order-table").appTable(ajaxApp);
    await $("#production-order-table").on("draw.dt", async function () {
        $(".produce-status").on("change", async function (e) {
            e.preventDefault();

            if ($(this).val()) {
                let url = '<?php echo get_uri("projects/production_order_state_change/" . $project_info["id"]); ?>';
                let req = {
                    id: $(this).data("id"),
                    state: $(this).val()
                };
                await produceStateChange(url, req);
            }
        });
    });
}

async function produceStateChange (url = "", req = {}) {
    await $.ajax({
        type: "POST",
        url: url,
        data: req,
        success: function (data, status) {
            let res = JSON.parse(data);
            if (status === "success") {
                $("#production-order-table").appTable({
                    newData: res.data,
                    dataId: res.info.id
                });
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        }
    });
};

$(document).ready(function () {
    loadProductionOrderList();
});
</script>
