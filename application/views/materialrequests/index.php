<div id="page-content" class="p20 clearfix">

	<div class="panel clearfix">
		<ul id="pr-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
			<li class="title-tab">
				<h4 class="pl15 pt10 pr15">
					<?php echo lang('materialrequests'); ?>
				</h4>
			</li>
			<li><a id="monthly-mr-button" class="active" role="presentation" href="javascript:void();"
					data-target="#monthly-materialrequests">
					<?php echo lang("monthly"); ?>
				</a></li>
			<li><a id="yearly-mr-button" role="presentation" href="javascript:void();"
					data-target="#yearly-materialrequests">
					<?php echo lang("yearly"); ?>
				</a></li>
			<div class="tab-title clearfix no-border">
				<div class="title-button-group">
					<?php echo @$buttonTops; ?>
				</div>
			</div>
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane fade" id="monthly-materialrequests">
				<div class="table-responsive">
					<table id="monthly-mr-table" class="display" cellspacing="0" width="100%">
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="yearly-materialrequests">
				<div class="table-responsive">
					<table id="yearly-mr-table" class="display" cellspacing="0" width="100%">
					</table>
				</div>
			</div>
		</div>
	</div>

</div>

<style type="text/css">
	#low-project {
		padding-left: 10px;
		padding-right: 10px;
		padding-bottom: 0;
		margin-bottom: 0;
	}

	#low-project-table .col-name-danger {
		color: red;
		font-weight: bold;
	}

	#low-project-table .col-name-warning {
		color: orange;
		font-weight: bold;
	}

	#low-stock {
		padding: 10px;
		padding-bottom: 5px;
		margin-bottom: 5px;
	}

	#low-stock-table .col-name-danger {
		color: red;
		font-weight: bold;
	}

	#low-stock-table .col-name-warning {
		color: orange;
		font-weight: bold;
	}

	#low-project-table_wrapper .datatable-tools,
	#low-stock-table_wrapper .datatable-tools,
	#low-stock-table-item_wrapper .datatable-tools {
		display: none;
	}

	#yearly-pr-table_length {
		display: none;
	}

	.w170px {
		width: 170px;
	}
</style>

<script type="text/javascript">
	const loadMrTableList = (selector, range) => {
		$(selector).appTable({
			source: '<?php echo_uri("materialrequests/material_request_list"); ?>',
			order: [[0, 'desc']],
			dateRangeType: range,
			filterDropdown: [
				{
					name: "status_id",
					class: "w170px",
					options: [
						{ "id": 0, "text": "-- <?php echo lang("status"); ?> --" },
						{ "id": 1, "text": "<?php echo lang("status_waiting_for_approve"); ?>" },
						{ "id": 3, "text": "<?php echo lang("status_already_approved"); ?>" },
						{ "id": 4, "text": "<?php echo lang("status_already_rejected"); ?>" }
					]
				}
			],
			columns: [
				{ data: "id", title: "<?php echo lang("id"); ?>", class: "text-center" },
				{ data: "doc_no", title: "<?php echo lang("document_number"); ?>" },
				{ data: "category_name", title: "<?php echo lang("category_name"); ?>" },
				{ data: "reference_number", title: "<?php echo lang("reference_number"); ?>" },
				{ data: "project_name", title: "<?php echo lang("project_refer"); ?>" },
				{ data: "client_name", title: "<?php echo lang("client_name"); ?>" },
				{ data: "user_name", title: "<?php echo lang("material_request_person"); ?>" },
				{ data: "request_date", title: "<?php echo lang("material_request_date"); ?>" },
				{ data: "status", title: "<?php echo lang("status"); ?>", class: "text-center" },
				{ data: "operation", title: "<i class='fa fa-bars'></i>", class: "text-center option w10p" }
			],
			xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5, 6, 7])
		});
	};

	$(document).ready(function () {
		loadMrTableList("#monthly-mr-table", "monthly");
		loadMrTableList("#yearly-mr-table", "yearly");

		$("#cat-mng-btn").click(function () {
			window.location.href = '<?php echo get_uri("materialrequests/categories"); ?>';
		});

		$('#back-to-stock').on('click', function () {
            window.location = '<?php echo_uri('stock'); ?>';
        });
	});
</script>
