<div class="panel">
	<div class="tab-title clearfix">
		<h4>
			<?php echo lang('stock_supplier_fg_pricings'); ?>
		</h4>

		<div class="title-button-group">
			<?php
			if ((isset($can_update_supplier) && !empty($can_update_supplier)) && (isset($can_update_material) && !empty($can_update_material))) {
				echo modal_anchor(
					get_uri("stock/supplier_fg_pricing_modal"),
					"<i class='fa fa-plus-circle'></i> " . lang('stock_supplier_fg_pricing_add'),
					array(
						"class" => "btn btn-default",
						"title" => lang('stock_supplier_fg_pricing_add'),
						"data-post-supplier_id" => $supplier_id
					)
				);
			}
			?>
		</div>
	</div>

	<div class="table-responsive">
		<table id="fg-pricing-table" class="display" width="100%"></table>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$("#fg-pricing-table").appTable({
			source: '<?php echo_uri("stock/supplier_fg_pricing_list/" . $supplier_id) ?>',
			order: [[0, 'desc']],
			filterDropdown: [
				{ name: "category_id", class: "w200", options: <?php echo json_encode($category_dropdown); ?> }
			],
			columns: [
				{ title: '<?php echo lang("id") ?>', "class": "text-center w50" },
				{ title: '<?php echo lang("stock_item_name"); ?>', "class": "w250" },
				{ title: '<?php echo lang("stock_material_category"); ?>', "class": "w150" },
				{ title: '<?php echo lang("description"); ?>' },
				{ title: '<?php echo lang("stock_material_quantity"); ?>', "class": "w125 text-right" },
                { title: '<?php echo lang("stock_material_unit"); ?>' },
				{ title: '<?php echo lang("price"); ?>', "class": "w125 text-right" },
				{ title: '<i class="fa fa-bars"></i>', "class": "text-center option w100" }
			],
			<?php if (isset($is_admin) && !empty($is_admin)): ?>
				printColumns : combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
				xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5])
			<?php endif; ?>
		});
	});
</script>