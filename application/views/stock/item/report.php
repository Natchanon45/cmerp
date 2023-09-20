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
</style>

<div id="page-content" class="p20 clearfix">
	<div class="panel panel-default">
		<div class="page-title clearfix">
			<h1>
				<a class="title-back" href="<?php echo_uri("stock"); ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
				<span><?php echo lang("stock_item_report"); ?></span>
			</h1>

			<?php // if ($add_pr_row): ?>
				<!-- <button type="button" class="btn btn-warning pull-right" id="btn-pr-create">
					<i class="fa fa-shopping-cart"></i> <?php // echo lang("request_low_item"); ?>
				</button> -->
			<?php // endif; ?>
		</div>
		<div class="table-responsive">
			<table id="report-table" class="display" width="100%"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
const source = '<?php echo_uri("stock/item_report_list") ?>';
const isZero = {
	name: 'is_zero',
	class: 'w150',
	options: [
		{ 'id': 0, 'text': '<?php echo lang("remain_only"); ?>' },
		{ 'id': 1, 'text': '<?php echo lang("all_add_stock"); ?>' }
	]
};

let columns = [
	{ title: '<?php echo lang("id"); ?>', class: 'text-center w10' },
	{ title: '<?php echo lang("stock_restock_item_name"); ?>' },
	{ title: '<?php echo lang("items_fg"); ?>' },
	{ title: '<?php echo lang("stock_item_description"); ?>' },
	{ title: '<?php echo lang("created_date"); ?>', class: 'w10' },
	{ title: '<?php echo lang("expiration_date"); ?>', class: 'w90' },
	{ title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w10 text-right' },
	{ title: '<?php echo lang("stock_item_remaining"); ?>', class: 'w10 text-right' },
	{ title: '<?php echo lang("stock_item_unit"); ?>', class: 'w10 text-right' },
	<?php if ($can_read_price): ?>
		{ title: '<?php echo lang("stock_restock_price"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("rate"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("currency"); ?>', class: 'w50 text-right' },
	<?php endif; ?>
];

let printColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]);
let xlsColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]);
let summation = [
	{ column: 6, dataType: 'number' }
];

<?php if ($can_read_price): ?>
	printColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
	xlsColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
	summation = [
		{ column: 6, dataType: 'number' },
		{ column: 11, dataType: 'currency' }
	];
<?php endif; ?>

async function loadReportTable() {
	await $("#report-table").appTable({
		source: source,
		filterDropdown: [isZero],
		columns: columns,
		printColumns: printColumns,
		xlsColumns: xlsColumns,
		summation: summation
	});
};

function testCasePromise() {
	console.log("Start");

	setTimeout(() => {
		console.log("Timeout 1");
	}, 0);

	Promise.resolve().then(() => {
		console.log("Promise 1");
	}).then(() => {
		console.log("Promise 2");
	});

	setTimeout(() => {
		console.log("Timeout 2");
	}, 0);

	Promise.resolve().then(() => {
		console.log("Promise 3");
	});

	console.log("End");
}

$(document).ready(function () {
	loadReportTable();
});
</script>
