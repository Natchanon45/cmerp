<?php echo form_open(get_uri("items/detail_file_save"), array("id" => "mixing-form-file", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
	<?php $this->load->view("items/detail/form_file"); ?>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
	<button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<style>
  @media (min-width: 992px) { .modal-dialog { width: 900px; } }
</style>

<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
		$("#mixing-form-file").appForm({
			onSuccess: function (result) {
				if (result.view === "details") {
					appAlert.success(result.message, {duration: 10000});
					setTimeout(function () {
						location.reload();
					}, 500);
				} else {
					$("#mixing-table-file").appTable({newData: result.data, dataId: result.id});
				}
			}
		});
	});
</script>