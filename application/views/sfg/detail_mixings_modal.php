<?php echo form_open(current_url(), array("id" => "mixing-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
	<?php $this->load->view("items/detail/form_mixing"); ?>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<span class="fa fa-close"></span>
		<?php echo lang("close"); ?>
	</button>

	<?php if (@$model_info->id): ?>
		<button type="button" class="btn btn-primary" onclick="javascript:duplicate_new_mixing();">
			<span class="fa fa-check-circle"></span>
			<?php echo lang("duplicate_to_new_mixing"); ?>
		</button>
		<button type="button" class="btn btn-primary" onclick="javascript:duplicate_to_new_item();">
			<span class="fa fa-check-circle"></span>
			<?php echo lang("duplicate_to_new_item"); ?>
		</button>
	<?php endif; ?>

	<button type="submit" class="btn btn-primary">
		<span class="fa fa-check-circle"></span>
		<?php echo lang("save"); ?>
	</button>
</div>

<?php echo form_close(); ?>

<style>
	@media (min-width: 992px) {
		.modal-dialog {
			width: 900px;
		}
	}
</style>

<script type="text/javascript">
	function duplicate_new_mixing() {
		jQuery('#id').val(0);
		jQuery('#mixing-form').submit();
	}

	function duplicate_to_new_item() {
		jQuery('#id').val(0);
		jQuery('#clone_to_new_item').val(1);
		jQuery('#mixing-form').submit();
	}

	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();

		$("#mixing-form").appForm({
			onSuccess: function (result) {
				console.log(result);
				
				if (result.view === "details") {
					appAlert.success(result.message, { duration: 10000 });
					setTimeout(function () {
						location.reload();
					}, 500);
				} else {
					$("#mixing-table").appTable({ newData: result.data, dataId: result.id });
				}
			}
		});
	});
</script>