<div id="page-content" class="clearfix p20">
    <div class="panel clearfix">
        <ul id="expenses-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab">
                <h4 class="pl15 pt10 pr15">
                    <?php echo lang("expenses"); ?>
                </h4>
            </li>
            <li>
                <a id="monthly-expenses-button" role="presentation" class="active" href="javascript:void(0);" data-target="#monthly-expenses">
                    <?php echo lang("monthly"); ?>
                </a>
            </li>
            <li>
                <a role="presentation" href="<?php echo_uri("expenses/yearly/"); ?>" data-target="#yearly-expenses">
                    <?php echo lang("yearly"); ?>
                </a>
            </li>
            <li>
                <a role="presentation" href="<?php echo_uri("expenses/custom/"); ?>" data-target="#custom-expenses">
                    <?php echo lang("custom"); ?>
                </a>
            </li>
            <li>
                <a role="presentation" href="<?php echo_uri("expenses/recurring/"); ?>" data-target="#recurring-expenses">
                    <?php echo lang("recurring"); ?>
                </a>
            </li>
            <li>
                <a role="presentation" href="<?php echo_uri("expenses/yearly_chart/"); ?>" data-target="#yearly-chart">
                    <?php echo lang("chart"); ?>
                </a>
            </li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php echo $buttonTop; ?>
                    <?php echo modal_anchor(get_uri("expenses/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang("add_expense"), array("class" => "btn btn-default mb0", "title" => lang("add_expense"))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-expenses">
                <div class="table-responsive">
                    <table id="monthly-expense-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-expenses"></div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-chart"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadExpensesTable = function (selector, dateRange) {
        var customDatePicker = "", recurring = "0";

        if (dateRange === "custom" || dateRange === "recurring") {
            customDatePicker = [
                {
                    startDate: { name: "start_date", value: moment().format("YYYY-MM-DD") },
                    endDate: { name: "end_date", value: moment().format("YYYY-MM-DD") },
                    showClearButton: true
                }
            ];

            if (dateRange === "recurring") {
                recurring = "1";
            }
            dateRange = "";
        }

        $(selector).appTable({
            source: '<?php echo_uri("expenses/list_data") ?>/' + recurring,
            dateRangeType: dateRange,
            filterDropdown: [
                { name: "acct_category_id", class: "w200", options: <?php echo $account_category_dropdown; ?> },
                { name: "acct_secondary_id", class: "w200", options: <?php echo $account_secondary_dropdown; ?> },
                { name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?> },
                { name: "user_id", class: "w200", options: <?php echo $members_dropdown; ?> },
                { name: "supplier_id", class: "w200", options: <?php echo $supplier_dropdown; ?> },
                <?php if ($projects_dropdown): ?>
                        { name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?> }
                <?php endif; ?>
            ],
            order: [[0, "asc"]],
            rangeDatepicker: customDatePicker,
            columns: [
                { visible: false, searchable: false },
                { title: '<?php echo lang("date") ?>', "iDataSort": 0 },
                { title: '<?php echo lang("title") ?>' },
                { title: 'ผู้จัดจำหน่าย' },
                { title: 'คำบรรยาย' },
                { title: 'ลูกค้า' },
                { title: '<?php echo lang("account_sub_type") ?>' },
                { title: '<?php echo lang("account_expense") ?>' },
                { title: '<?php echo lang("files") ?>' },
                { title: '<?php echo lang("amount") ?>', "class": "text-right" },
                { title: '<?php echo lang("total") ?>', "class": "text-right" },
                { title: '<?php echo lang("currency") ?>', "class": "text-right" }
                <?php echo $custom_field_headers; ?>,
                { title: '<i class="fa fa-bars"></i>', "class": "text-center option w100" }
            ],
            printColumns: [1, 2, 3, 4, 6, 7, 8],
            xlsColumns: [1, 2, 3, 4, 6, 7, 8],
            summation: [
                { column: 9, dataType: 'number' },
                { column: 10, dataType: 'number' }
            ]
        });
    };

    $(document).ready(function () {
        loadExpensesTable("#monthly-expense-table", "monthly");
    });
</script>