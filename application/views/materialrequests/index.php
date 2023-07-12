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
					<?php if ($create_material_request)
						echo $buttonTops; ?>
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

	const loadPrTableList = (selector, range) => {
		$(selector).appTable({
			source: '<?php echo_uri("materialrequests/material_request_list"); ?>',
			order: [[0, 'asc']],
			dateRangeType: range,
			filterDropdown: [
				{
					name: "status_id",
					class: "w170px",
					options: [
						{ "id": 0, "text": "<?php echo lang("status"); ?>" },
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
				{ data: "project_name", title: "<?php echo lang("project_name"); ?>" },
				{ data: "client_name", title: "<?php echo lang("client_name"); ?>" },
				{ data: "user_name", title: "<?php echo lang("material_request_person"); ?>" },
				{ data: "request_date", title: "<?php echo lang("material_request_date"); ?>" },
				{ data: "status", title: "<?php echo lang("status"); ?>", class: "text-center" },
				{ data: "operation", title: "<i class='fa fa-bars'></i>", class: "text-center option w10p" }
			],
			xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5, 6, 7])
		});
	};

	loadPrTable = function (selector, dateRange) {
		$(selector).appTable({
			source: '<?php echo_uri("materialrequests/list_data") ?>',
			order: [[0, "desc"]],
			dateRangeType: dateRange,
			filterDropdown: [
				{ name: "status_id", class: "w150", options: <?php $this->load->view("materialrequests/pr_statuses_dropdown"); ?>},
				{ name: "supplier_id", class: "w200", options: <?php $this->load->view("materialrequests/pr_suppliers_dropdown"); ?>},
				{ name: "limit", class: "w100", options: [{ "id": 10, "text": "รายการ" }, { "id": 10, "text": "10" }, { "id": 25, "text": "25" }, { "id": 50, "text": "50" }, { "id": 100, "text": "100" }, { "id": 500, "text": "500" }] }
			],
			// filterParams: {datatable: true, test:5}, // work
			// stateSave: true, // work
			// displayLength:25, // work
			// lengthMenu: [[10, 25, 50, 500], [10, 25, 50, 500]], // not work
			columns: [
				{ title: "<?php echo lang("materialrequests") ?>" },
				{ title: "<?php echo lang("category_name") ?>", "class": "text-left w15p" },
				{ title: "<?php echo lang("project_name") ?>", "class": "text-left w10p" },
				{ title: "<?php echo lang("buyer_org") ?>", "class": "text-left w10p" },
				// {visible: false, searchable: false},
				{ title: "<?php echo lang("pr_date") ?>", "iDataSort": 2, "class": "w10p" },
				{ title: "<?php echo lang("status") ?>", "class": "text-center w10p" },
				{ title: "<i class='fa fa-bars'></i>", "class": "text-center option w10p" }
			],
			printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
			xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
			summation: [{ column: 4, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol }]
		});
	};

	<?php if ($create_material_request): ?>
			loadLowProjectTable = function(selector) {
				$(selector).appTable({
					source: '<?php echo_uri("materialrequests/list_lacked_project_materials") ?>',
					order: [[0, "desc"]],
					columns: [
						{ title: "<?php echo lang("ID") ?> ", "class": "w5p" },
						{ title: "<?php echo lang("project_name") ?> " },
						{ title: "<?php echo lang("material_amount") ?>", "class": "w15p" },
						{ title: "<?php echo lang("action") ?>", "class": "w20p" }
					],
					sDefaultContent: "Not found any data",
					searchPanes: {
						controls: false
					},
					dom: 'Plfrtip'
				});
			};

		loadLowStockTable = function (selector) {
			$(selector).appTable({
				source: '<?php echo_uri("materialrequests/list_lacked_stock_materials") ?>',
				order: [[0, "desc"]],
				columns: [
					{ title: "<?php echo lang("ID") ?> ", "class": "w5p" },
					{ title: "<?php echo lang("import_name") ?> " },
					{ title: "<?php echo lang("material_amount") ?>", "class": "w15p" },
					{ title: "<?php echo lang("action") ?>", "class": "w20p" }
				],
				sDefaultContent: "Not found any data",
				searchPanes: {
					controls: false
				},
				dom: 'Plfrtip'
			});
		};

		loadLowStockTableItem = function (selector) {
			$(selector).appTable({
				source: '<?php echo_uri("materialrequests/list_lacked_stock_item") ?>',
				order: [[0, "desc"]],
				columns: [
					{ title: "<?php echo lang("ID") ?> ", "class": "w5p" },
					{ title: "<?php echo lang("stock_restock_item_name") ?> " },
					{ title: "<?php echo lang("item_amount") ?>", "class": "w15p" },
					{ title: "<?php echo lang("action") ?>", "class": "w20p" }
				],
				sDefaultContent: "Not found any data",
				searchPanes: {
					controls: false
				},
				dom: 'Plfrtip'
			});
		};

		function purchaseRequest(btn_selector, prefix) {
			var clicked_btn = jQuery(btn_selector);
			var materials = [];
			var lacked_materials = jQuery('.' + prefix + 'lacked_material');

			for (let i = 0; i < lacked_materials.length; i++) {
				var material_id = jQuery(lacked_materials[i]).attr('data-material-id');
				var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
				var unit = jQuery(lacked_materials[i]).attr('data-unit');
				var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
				var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
				var project_id = parseInt(jQuery(lacked_materials[i]).attr('data-project-id'));
				var project_name = jQuery(lacked_materials[i]).attr('data-project-name');
				project_name = project_name ? project_name : "";
				var price = jQuery(lacked_materials[i]).attr('data-price');
				var currency = jQuery(lacked_materials[i]).attr('data-currency');
				var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency_symbol');
				if (materials[material_id] != undefined) {
					materials[material_id].amount = amount;
				} else if (material_id) {
					materials[material_id] = { 'id': material_id, 'amount': amount, 'unit': unit, 'supplier_id': supplier_id, 'supplier_name': supplier_name, 'project_id': project_id, 'project_name': project_name, 'price': price, 'currency': currency, 'currency_symbol': currency_symbol };
				}
			}

			materials = materials.filter(function (ele) {
				if (ele != null) return ele;
			});
			// alert(JSON.stringify(materials));

			var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri("materialrequests/add_pr_material_to_cart"); ?>"></form>');
			jQuery.each(materials, function (key, material) {
				form.append('<input type="hidden" name="materials[' + key + '][id]" value="' + material.id + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][amount]" value="' + material.amount + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][unit]" value="' + material.unit + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][supplier_id]" value="' + material.supplier_id + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][supplier_name]" value="' + material.supplier_name + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][project_id]" value="' + material.project_id + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][project_name]" value="' + material.project_name + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][price]" value="' + material.price + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][currency]" value="' + material.currency + '" />');
				form.append('<input type="hidden" name="materials[' + key + '][currency_symbol]" value="' + material.currency_symbol + '" />');
			});
			form.append('<?php
			$CI =& get_instance();
			echo sprintf(
				'<input type="hidden" name="%s" value="%s" />',
				$CI->security->get_csrf_token_name(),
				$CI->security->get_csrf_hash(),
			);
			?> ');
			var parentform = jQuery(clicked_btn).closest('form');
			if (parentform.length > 0) {
				parentform.after(form);
			} else {
				jQuery(clicked_btn).after(form);
			}
			jQuery('#add-pr-form').submit();
		};

		function purchaseRequestItem(btn_selector, prefix) {
			var clicked_btn = jQuery(btn_selector);
			var item = [];
			var lacked_materials = jQuery('.' + prefix + 'lacked_material');
			for (let i = 0; i < lacked_materials.length; i++) {
				var item_id = jQuery(lacked_materials[i]).attr('data-item-id');
				var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
				var unit = jQuery(lacked_materials[i]).attr('data-unit');
				var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
				var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
				var project_id = parseInt(jQuery(lacked_materials[i]).attr('data-project-id'));
				var project_name = jQuery(lacked_materials[i]).attr('data-project-name');
				project_name = project_name ? project_name : "";
				var price = jQuery(lacked_materials[i]).attr('data-price');
				var currency = jQuery(lacked_materials[i]).attr('data-currency');
				var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency_symbol');
				if (item[item_id] != undefined) {
					item[item_id].amount = amount;
				} else if (item_id) {
					item[item_id] = { 'id': item_id, 'amount': amount, 'unit': unit, 'supplier_id': supplier_id, 'supplier_name': supplier_name, 'project_id': project_id, 'project_name': project_name, 'price': price, 'currency': currency, 'currency_symbol': currency_symbol };
				}
			}
			item = item.filter(function (ele) {
				if (ele != null) return ele;
			});
			// alert(JSON.stringify(materials));
			var form = jQuery('<form id="add-pr-form1" method="post" action="<?php echo_uri("purchaserequests/add_pr_item_to_cart"); ?>"></form>');
			jQuery.each(item, function (key, item) {
				form.append('<input type="hidden" name="item[' + key + '][id]" value="' + item.id + '" />');
				form.append('<input type="hidden" name="item[' + key + '][amount]" value="' + item.amount + '" />');
				form.append('<input type="hidden" name="item[' + key + '][unit]" value="' + item.unit + '" />');
				form.append('<input type="hidden" name="item[' + key + '][supplier_id]" value="' + item.supplier_id + '" />');
				form.append('<input type="hidden" name="item[' + key + '][supplier_name]" value="' + item.supplier_name + '" />');
				form.append('<input type="hidden" name="item[' + key + '][project_id]" value="' + item.project_id + '" />');
				form.append('<input type="hidden" name="item[' + key + '][project_name]" value="' + item.project_name + '" />');
				form.append('<input type="hidden" name="item[' + key + '][price]" value="' + item.price + '" />');
				form.append('<input type="hidden" name="item[' + key + '][currency]" value="' + item.currency + '" />');
				form.append('<input type="hidden" name="item[' + key + '][currency_symbol]" value="' + item.currency_symbol + '" />');
			});
			form.append('<?php
			$CI =& get_instance();
			echo sprintf(
				'<input type="hidden" name="%s" value="%s" />',
				$CI->security->get_csrf_token_name(),
				$CI->security->get_csrf_hash(),
			);
			?> ');
			var parentform = jQuery(clicked_btn).closest('form');
			if (parentform.length > 0) {
				parentform.after(form);
			} else {
				jQuery(clicked_btn).after(form);
			}
			jQuery('#add-pr-form1').submit();
		};
	<?php endif; ?>

	$(document).ready(function () {
		loadPrTableList("#monthly-mr-table", "monthly");
		loadPrTableList("#yearly-mr-table", "yearly");

		<?php // if ($create_material_request): ?>
			// loadLowProjectTable("#low-project-table");
			// loadLowStockTable("#low-stock-table");
			// loadLowStockTableItem("#low-stock-table-item");
		<?php // endif; ?>

		$("#add-pr-btn").click(function () {
			window.location.href = "<?php echo get_uri("materialrequests/process_pr"); ?>";
		});

		$("#cat-mng-btn").click(function () {
			window.location.href = '<?php echo get_uri("materialrequests/categories"); ?>';
		});

		$('#back-to-stock').on('click', function () {
            window.location = '<?php echo_uri('stock'); ?>';
        });
	});

</script>

<?php $this->load->view("materialrequests/update_pr_status_script"); ?>