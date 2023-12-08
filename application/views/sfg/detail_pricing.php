<div class="panel">
	<div class="tab-title clearfix">
		<h4><?php echo lang('stock_item_pricings'); ?></h4>
		<div class="title-button-group">
		<?php
			echo modal_anchor(
				get_uri('sfg/detail_pricing_modal'),
				'<i class="fa fa-plus-circle"></i> ' . lang('stock_item_pricing_add'),
				array(
					'class' => 'btn btn-default',
					'title' => lang('stock_item_pricing_add'),
					'data-title' => lang('stock_item_pricing_add'),
					'data-post-item_id' => $item_id
				)
			);
		?>
		</div>
	</div>
	<div class="table-responsive">
		<table id="item-pricing-table" class="display" width="100%"></table>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function () {
	<?php if($this->Permission_m->bom_supplier_read == true): ?>
		$("#item-pricing-table").appTable({
			source: '<?php echo current_url(); ?>',
			order: [[0, 'desc']],
			columns: [
				{ title: '<?php echo lang('id'); ?>', class: 'text-center w50' },
				{ title: '<?php echo lang('stock_supplier_name'); ?>' },
				{ title: '<?php echo lang('stock_supplier_contact_name'); ?>' },
				{ title: '<?php echo lang('stock_supplier_contact_phone'); ?>' },
				{ title: '<?php echo lang('stock_supplier_contact_email'); ?>' },
				{ title: '<?php echo lang('stock_material_quantity'); ?>', class: 'w125 text-right' },
				{ title: '<?php echo lang('stock_material_unit'); ?>', class: 'w70' },
				{ title: '<?php echo lang('price'); ?>', class: 'w125 text-right' },
				{ title: '<?php echo lang('currency'); ?>', class: 'w70' },
				{ title: '<i class="fa fa-bars"></i>', class: 'text-center option w100' }
			],
			printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
			xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7])
	    });
    <?php else: ?>
     	location.href = "<?php echo get_uri(); ?>";
    <?php endif; ?>
});
</script>