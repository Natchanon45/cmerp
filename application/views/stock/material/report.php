<style type="text/css">
.lacked_material {
	margin: 0;
	padding: 0;
	display: inline-block;
	color: #ffa500;
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
				<a href="<?php echo get_uri("stock"); ?>" class="title-back"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
				<?php echo lang("stock_material_report"); ?>
			</h1>
		</div>

		<div class="table-responsive">
			<table id="report-table" class="display" cellspacing="0" width="100%"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
const source = '<?php echo get_uri("stock/material_report_list"); ?>';
const isZero = {
	name: 'is_zero',
	class: 'w150',
	options: [
		{ 'id': 0, 'text': '<?php echo lang("remain_only"); ?>' },
		{ 'id': 1, 'text': '<?php echo lang("all_add_stock"); ?>' }
	]
};

const warehouseId = {
	name: 'warehouse_id',
	class: 'w200',
	options: <?php echo $warehouse_dropdown; ?>
};

const dateCreated = [{
	startDate: {
		name: 'start_date',
		value: '<?php  echo date('Y-m-01'); ?>'
	},
	endDate: {
		name: 'end_date',
		value: '<?php echo date("Y-m-d", strtotime('last day of this month', time())); ?>'
	}
}];

let columns = [
	{ title: '<?php echo lang("id"); ?>', class: 'text-center w10' },
	{ title: '<?php echo lang("stock_restock_name"); ?>', class: 'w200' },
	{ title: '<?php echo lang("stock_material"); ?>', class: 'w200' },
	{ title: '<?php echo lang("stock_restock_date"); ?>', class: '' },
	{ title: '<?php echo lang("expiration_date"); ?>', class: '' },
	{ title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w100 text-right' },
	{ title: '<?php echo lang("stock_material_remaining"); ?>', class: 'w100 text-right' },
	{ title: '<?php echo lang("stock_material_unit"); ?>', class: 'w10 text-center' },
	<?php if ($can_read_price): ?>
		{ title: '<?php echo lang("stock_restock_price"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("rate"); ?>', class: 'w90 text-right' },
		{ title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w100 text-right' },
		{ title: '<?php echo lang("currency"); ?>', class: 'w100 text-right' }
	<?php endif; ?>
];

let printColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]);
let xlsColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]);
let summation = [
	{ column: 5, dataType: 'number' }
];

<?php if ($can_read_price): ?>
	printColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
	xlsColumns = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
	summation = [
		{ column: 5, dataType: 'number' },
		{ column: 10, dataType: 'currency' }
	];
<?php endif; ?>

async function loadReportTable() {
	await $("#report-table").appTable({
		source: source,
		rangeDatepicker: dateCreated,
		filterDropdown: [isZero, warehouseId],
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
