<div id="page-content" class="p20 clearfix">
	<div class="panel panel-default">
		<div class="page-title clearfix">
			<h1>
				<a class="title-back" href="<?php echo get_uri('stock'); ?>">
					<i class="fa fa-chevron-left" aria-hidden="true"></i>
				</a>
				<?php echo lang('stock_materials'); ?>
			</h1>
			<div class="title-button-group">
				<?php
				if ($can_create && $can_update) {
					echo modal_anchor(
						get_uri("stock/material_import_modal"),
						"<i class='fa fa-upload'></i> " . lang('stock_material_import'),
						array("class" => "btn btn-default", "title" => lang('stock_material_import'))
					);
					echo modal_anchor(
						get_uri("stock/material_category_modal"),
						"<i class='fa fa-tags'></i> " . lang('add_category'),
						array("class" => "btn btn-default", "title" => lang('add_category'), "data-post-type" => "material")
					);
				}
				if ($can_create) {
					echo modal_anchor(
						get_uri("stock/material_modal"),
						"<i class='fa fa-plus-circle'></i> " . lang('stock_material_add'),
						array("class" => "btn btn-default", "title" => lang('stock_material_add'))
					);
				}
				?>
			</div>
		</div>
		<div class="table-responsive">
			<table id="material-table" class="display" cellspacing="0" width="100%"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$("#material-table").appTable({
			source: '<?php echo_uri("stock/material_list") ?>',
			filterDropdown: [
				{ name: "category_id", class: "w200", options: <?php echo json_encode($category_dropdown); ?> }
			],
			columns: [
				{ title: "<?php echo lang("id") ?>", "class": "text-center w50" },
				{ title: "<?php echo lang('preview_image') ?> ", "class": "w100" },
				{ title: '<?php echo lang("stock_material_name"); ?>', "class": "w200" },
				<?php if ($can_read_production_name) :?>
					{ title: '<?php echo lang("stock_material_production_name"); ?>', "class": "w200" },
				<?php endif; ?>
				{ title: '<?php echo lang("stock_material_barcode"); ?>', "class": "w200" },
				{ title: '<?php echo lang("stock_material_category"); ?>', "class": "w150" },
				{ title: '<?php echo lang("description"); ?>' },
				{ title: '<?php echo lang("stock_material_remaining"); ?>', "class": "text-center w150" },
				{ title: '<?php echo lang("stock_material_unit"); ?>', "class": "w50 text-center" },
				{ title: '<i class="fa fa-bars"></i>', "class": "text-center option w100" }
			],
			<?php if (isset($is_admin) && $is_admin) { ?>
				<?php if ($can_read_production_name) { ?>
					printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
					xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
				<?php } else { ?>
					printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
					xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
				<?php } ?>
			<?php } ?>
		});
	});
</script>