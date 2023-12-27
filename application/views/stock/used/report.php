<style type="text/css">
    .lacked_material {
        margin: 0;
        padding: 0;
        display: inline-block;
        color: orange;
        font-weight: bold;
    }

    #report-table {
        font-size: small;
    }

    .mw250 {
        min-width: 250px;
    }
</style>

<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1>
                <a class="title-back" href="<?php echo_uri("stock"); ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
                <span>
                    <?php echo lang("stock_used_report"); ?>
                </span>
            </h1>
        </div>
        <div class="table-responsive">
            <table id="report-table" class="display" width="100%"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    const source = '<?php echo_uri("stock/used_report_list"); ?>';
    const sourceType = {
        name: 'source_type',
        class: 'w150',
        options: [
            { 'id': 1, 'text': '<?php echo "- " . lang("entries") . " -"; ?>' },
            { 'id': 2, 'text': '<?php echo lang("stock_used_rm"); ?>' },
            { 'id': 3, 'text': '<?php echo lang("stock_used_fg"); ?>' },
            { 'id': 4, 'text': '<?php echo lang("stock_used_sfg"); ?>' }
        ]
    };

    const dateCreated = [{
        startDate: {
            name: 'start_date'
            // value: '<?php // echo date('Y-m-01'); ?>'
        },
        endDate: {
            name: 'end_date'
            // value: '<?php // echo date("Y-m-d", strtotime('last day of this month', time())); ?>'
        }
    }];

    let columns = [
        { title: '<?php echo lang("id"); ?>', class: 'text-center w10' },
        { title: '<?php echo lang("stock_restock_name"); ?>', class: 'w150' },
        { title: '<?php echo lang("entries"); ?>', class: '' },
        { title: '<?php echo lang("stock_item_description"); ?>', class: 'w200' },
        { title: '<?php echo lang("reference_number"); ?>', class: 'w100' },
        { title: '<?php echo lang("used_date"); ?>', class: 'w100' },
        { title: '<?php echo lang("used_quantity"); ?>', class: 'w100 text-right' },
        { title: '<?php echo lang("stock_item_unit"); ?>', class: 'w10' },
        // { title: '<?php // echo lang("used_price"); ?>', class: 'w90 text-right' },
        { title: '<?php echo lang("rate"); ?>', class: 'w90 text-right' },
        { title: '<?php echo lang("used_value"); ?>', class: 'w90 text-right' },
        { title: '<?php echo lang("currency"); ?>', class: 'w100 text-right' }
    ];

    let printColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
    let xlsColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
    let summation = [
        { column: 6, dataType: 'number' },
        { column: 9, dataType: 'number' }
    ];

    async function loadReportTable() {
        await $("#report-table").appTable({
            source: source,
            rangeDatepicker: dateCreated,
            filterDropdown: [sourceType],
            columns: columns,
            printColumns: printColumns,
            xlsColumns: xlsColumns,
            summation: summation
        });
    };

    $(document).ready(function () {
        loadReportTable();
    });
</script>