<style type="text/css">
    .pointer-none {
        pointer-events: none;
        appearance: none;
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
        width: 90%;
        height: 30px;
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

    .pill-warning-dark {
        color: #fff;
        background-color: rgba(255, 165, 0, 1);
    }

    .pill-info {
        color: #fff;
        background-color: #17a2b8;
    }

    .text-color-fg {
        color: rgba(0, 83, 156, .83);
        font-weight: bolder;
    }

    .text-color-sfg {
        color: rgba(255, 165, 0, .83);
        font-weight: bolder;
    }

    .btn-custom-bag {
        border: 1px solid #148f77;
        border-radius: 100%;
        padding: 5px 8px;
        font-size: 120%;
    }
</style>

<div class="panel">
    <input type="hidden" name="authReadCostAmount" id="authReadCostAmount" value="<?php echo $auth_read_cost; ?>">
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
            ?>
            
            <!-- btn-create-material-request -->
            <span class="dropdown inline-block">
                <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-plus-circle"></i>
                    <?php echo lang("production_order_create_mr_all"); ?>
                </button>
                <ul class="dropdown-menu dropdown-left rounded" role="menu" aria-labelledby="dropdownMenuButton">
                    <li role="presentation">
                        <?php
                            echo modal_anchor(
                                get_uri("projects/production_order_mr_creation_all"),
                                "<i class='fa fa-plus-circle'></i> " . lang("production_order_create_mr_all_both"),
                                array(
                                    "class" => "dropdown-item",
                                    "title" => lang("mr_creation_all_production"),
                                    "data-title" => lang("mr_creation_all_production"),
                                    "data-post-project_id" => $project_info["id"],
                                    "data-post-project_name" => $project_info["title"],
                                    "data-post-item_type" => "BOTH"
                                )
                            );
                        ?>
                    </li>
                    <li role="presentation">
                        <?php
                            echo modal_anchor(
                                get_uri("projects/production_order_mr_creation_all"),
                                "<i class='fa fa-plus-circle'></i> " . lang("production_order_create_mr_all_fg"),
                                array(
                                    "class" => "dropdown-item",
                                    "title" => lang("mr_creation_fg_production"),
                                    "data-title" => lang("mr_creation_fg_production"),
                                    "data-post-project_id" => $project_info["id"],
                                    "data-post-project_name" => $project_info["title"],
                                    "data-post-item_type" => "FG"
                                )
                            );
                        ?>
                    </li>
                    <li role="presentation">
                        <?php
                            echo modal_anchor(
                                get_uri("projects/production_order_mr_creation_all"),
                                "<i class='fa fa-plus-circle'></i> " . lang("production_order_create_mr_all_sfg"),
                                array(
                                    "class" => "dropdown-item",
                                    "title" => lang("mr_creation_sfg_production"),
                                    "data-title" => lang("mr_creation_sfg_production"),
                                    "data-post-project_id" => $project_info["id"],
                                    "data-post-project_name" => $project_info["title"],
                                    "data-post-item_type" => "SFG"
                                )
                            );
                        ?>
                    </li>
                </ul>
            </span>

            <!-- btn-create-production -->
            <span class="dropdown inline-block">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-info-circle"></i>
                    <?php echo lang("production_order_add"); ?>
                </button>
                <ul class="dropdown-menu dropdown-left rounded" role="menu" aria-labelledby="dropdownMenuButton">
                    <li role="presentation">
                        <?php
                            echo modal_anchor(
                                get_uri("projects/production_order_modal_form"),
                                "<i class='fa fa-info-circle'></i> " . lang("fg"),
                                array(
                                    "class" => "dropdown-item",
                                    "title" => lang("production_order_add") . lang("fg"),
                                    "data-title" => lang("production_order_add") . lang("fg"),
                                    "data-post-project_id" => $project_info["id"],
                                    "data-post-item_type" => "FG"
                                )
                            );
                        ?>
                    </li>
                    <li role="presentation">
                        <?php
                            echo modal_anchor(
                                get_uri("projects/production_order_modal_form"),
                                "<i class='fa fa-info-circle'></i> " . lang("sfg"),
                                array(
                                    "class" => "dropdown-item",
                                    "title" => lang("production_order_add") . lang("sfg"),
                                    "data-title" => lang("production_order_add") . lang("sfg"),
                                    "data-post-project_id" => $project_info["id"],
                                    "data-post-item_type" => "SFG"
                                )
                            );
                        ?>
                    </li>
                </ul>
            </span>

            <span>
                <?php
                echo modal_anchor(
                    get_uri("projects/production_order_all_bag_modal"),
                    "<i class='fa fa-shopping-bag'></i> ",
                    array(
                        "class" => "btn btn-custom-bag",
                        "title" => lang("production_order_all_of_material_used"),
                        "data-title" => lang("production_order_all_of_material_used"),
                        "data-post-project_id" => $project_info["id"],
                        "data-post-project_name" => $project_info["title"]
                    )
                );
                ?>
            </span>
        </div>
    </div>
    <div id="table-wrapper" class="table-responsive"></div>
</div>

<?php
echo modal_anchor(
    get_uri("projects/production_order_modal_error"),
    "production_order_modal_error",
    array(
        "class" => "btn btn-default hide",
        "title" => lang("production_order_state_change"),
        "data-title" => lang("production_order_state_change"),
        "data-post-message" => lang("production_order_cannot_change_status"),
        "id" => "btn-failure"
    )
);
?>

<script type="text/javascript">
const btnFailure = document.querySelector("#btn-failure");
const authReadCostAmount = document.querySelector("#authReadCostAmount");
const source = '<?php echo_uri("projects/production_order_list/" . $project_info["id"]); ?>';

const mrStatus = {
    name: 'mr_status', class: 'w200',
    options: [
        { id: 0, text: '<?php echo "-- " . lang("production_order_mr_status_select") . " --"; ?>' },
        { id: 1, text: '<?php echo lang("production_order_not_yet_withdrawn"); ?>' },
        { id: 2, text: '<?php echo lang("production_order_partially_withdrawn"); ?>' },
        { id: 4, text: '<?php echo lang("production_order_created_withdrawn"); ?>' },
        { id: 3, text: '<?php echo lang("production_order_completed_withdrawal"); ?>' }
    ]
};

const produceStatus = {
    name: 'produce_status',
    class: 'w200',
    options: [
        { id: 0, text: '<?php echo "-- " . lang("production_order_produce_status_select") . " --"; ?>' },
        { id: 1, text: '<?php echo lang("production_order_not_yet_produce"); ?>' },
        { id: 2, text: '<?php echo lang("production_order_producing"); ?>' },
        { id: 3, text: '<?php echo lang("production_order_produced_completed"); ?>' }
    ]
};

let printColumns = [0, 1, 2, 3, 5, 6, 7];
let xlsColumns = [0, 1, 2, 3, 5, 6, 7];
let summation = [];
let columns = [
    { title: '<?php echo lang("id"); ?>', class: 'text-center' },
    { title: '<?php echo lang("item") . "/" . lang("sfg"); ?>' },
    { title: '<?php echo lang("item_mixing_name"); ?>' },
    { title: '<?php echo lang("quantity"); ?>', class: 'text-right' },
    { title: '<?php echo lang("stock_material_unit"); ?>', class: 'text-left' },
    { title: '<?php echo lang("production_order_produce_in"); ?>', class: 'text-center' },
    { title: '<?php echo lang("status") . '<br>' . lang("production_order_produce_status"); ?>', class: 'text-center' },
    { title: '<?php echo lang("mr_percentage"); ?>', class: 'text-center' },
    { title: '<i class="fa fa-bars"></i>', class: 'text-center option' }
];

if (authReadCostAmount.value) {
    columns = [
        { title: '<?php echo lang("id"); ?>', class: 'text-center' },
        { title: '<span class="text-color-fg"><?php echo lang("item"); ?></span>/<span class="text-color-sfg"><?php echo lang("sfg"); ?></span>' },
        { title: '<?php echo lang("item_mixing_name"); ?>' },
        { title: '<?php echo lang("quantity"); ?>', class: 'text-right' },
        { title: '<?php echo lang("stock_material_unit"); ?>', class: 'text-left' },
        { title: '<?php echo lang("production_order_rm_cost"); ?>', class: 'text-right' },
        { title: '<?php echo lang("currency"); ?>', class: 'text-left' },
        { title: '<?php echo lang("production_order_produce_in"); ?>', class: 'text-center' },
        { title: '<?php echo lang("status") . '<br>' . lang("production_order_produce_status"); ?>', class: 'text-center' },
        { title: '<?php echo lang("mr_percentage"); ?>', class: 'text-center' },
        { title: '<i class="fa fa-bars"></i>', class: 'text-center option' }
    ];
    summation = [
        { column: 5, dataType: 'currency' }
    ];
}

async function loadProductionOrderList() {
    let ajaxTable = '<table id="production-order-table" class="display" width="100%"></table>';
    let ajaxApp = {
        source: source,
        order: [[0, 'asc']],
        filterDropdown: [mrStatus, produceStatus],
        destroy: true,
        columns: columns,
        summation: summation,
        printColumns: printColumns,
        xlsColumns: xlsColumns
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
            // console.log(res);

            if (res.status === 'failure') {
                btnFailure.click();
            }

            $("#production-order-table").appTable({
                newData: res.data,
                dataId: res.info.id
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        }
    });
};

function UnderstandingOfOverridingObject() {
    let x = {
        value: 1,
        valueOf: function () {
            return this.value;
        }
    };

    if (x == 1 && x == 2 && x == 3) {
        console.log("True");
    } else {
        console.log("False");
    }
}

$(document).ready(function () {
    loadProductionOrderList();
});
</script>
