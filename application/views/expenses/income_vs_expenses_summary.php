<div class="table-responsive">
    <table id="income-vs-expenses-summary-table" class="display" cellspacing="0" width="100%">
    </table>
</div>

<script>
    $("#income-vs-expenses-summary-table").appTable({
        source: '<?php echo_uri("expenses/income_vs_expenses_summary_list_data"); ?>',
        order: [[0, 'asc']],
        dateRangeType: "yearly",
        filterDropdown: [
            <?php if ($projects_dropdown) :?>
                {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>}
            <?php endif; ?>
        ],
        columns: [
            {visible: false, searchable: false}, //sorting purpose only
            {title: '<?php echo lang("month") ?>', "class": "w30p", "iDataSort": 0},
            {title: '<?php echo lang("income") ?>', "class": "w20p text-right"},
            {title: '<?php echo lang("expenses") ?>', "class": "w20p text-right"},
            {title: '<?php echo lang("profit") ?>', "class": "w20p text-right"},
            {title: '<?php echo lang("currency") ?>', "class": "w20p text-right"}
            ],
            printColumns: [1, 2, 3, 4, 5], 
            xlsColumns: [1, 2, 3, 4, 5], 
            summation: [{column:2, dataType: 'number'}, {column:3, dataType: 'number'}, {column:4, dataType: 'number'}]
    });
</script>