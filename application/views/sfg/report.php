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
				<span>รายงานสินค้ากึ่งสำเร็จ</span>
			</h1>
		</div>
		<div class="table-responsive">
			<table id="report-table" class="display" width="100%"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
const source = '<?php echo current_url(); ?>';
const isZero = {
	name: 'is_zero',
	class: 'w150',
	options: [
		{ 'id': 0, 'text': '<?php echo lang("remain_only"); ?>' },
		{ 'id': 1, 'text': '<?php echo lang("all_add_stock"); ?>' }
	]
};

const dateCreated = [{
	startDate: {
		name: 'start_date'
		// value: '<?php echo date('Y-m-01'); ?>'
	},
	endDate: {
		name: 'end_date'
		// value: '<?php echo date("Y-m-d", strtotime('last day of this month', time())); ?>'
	}
}];

let columns = [
	{ title: '<?php echo lang("id"); ?>', class: 'text-center w10' },
	{ title: '<?php echo lang("stock_restock_name"); ?>', class: 'w150' },
	{ title: '<?php echo lang("items_fg"); ?>', class: 'mw250' },
	{ title: '<?php echo lang("stock_item_description"); ?>', class: 'w150' },
	{ title: '<?php echo lang("stock_restock_date"); ?>', class: '' },
	{ title: '<?php echo lang("expiration_date"); ?>', class: '' },
	{ title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w100 text-right' },
	{ title: '<?php echo lang("stock_item_remaining"); ?>', class: 'w100 text-right' },
	{ title: '<?php echo lang("stock_item_unit"); ?>', class: 'w10' },
	<?php if ($can_read_price): ?>
		{ title: '<?php echo lang("stock_restock_price"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("rate"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("currency"); ?>', class: 'w100 text-right' },
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
		rangeDatepicker: dateCreated,
		filterDropdown: [isZero],
		columns: columns,
		printColumns: printColumns,
		xlsColumns: xlsColumns,
		summation: summation
	});
};

const promiseTestCases = async () => {
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
};

$(document).ready(function () {
	loadReportTable();
});
</script>
