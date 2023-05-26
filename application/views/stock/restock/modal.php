<?php echo form_open(get_uri('stock/restock_save'), array("id" => 'restock-form', "class" => 'general-form', "role" => 'form')); ?>

<div class="modal-body clearfix">
	<?php $this->load->view('stock/restock/form'); ?>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<span class="fa fa-close"></span> <?php echo lang('close'); ?>
	</button>

	<?php if ((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)): ?>
		<button type="submit" class="btn btn-primary">
			<span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
		</button>
	<?php endif; ?>
</div>

<?php echo form_close(); ?>

<style type="text/css">
  @media (min-width: 999px){
	.modal-dialog {
		width: 1080px;
	}
  }
</style>

<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();

		$('#restock-form').appForm({
			onSuccess: function (result) {
				console.log(result);

				appAlert.success(result.message, { duration: 10000 });
				setTimeout(function () {
					location.reload();
				}, 500);
			}
		});

		$('#company_name').focus();
	});
</script>
