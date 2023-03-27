<?php echo form_open(get_uri("stock/material_save"), array("id" => "material-form", "class" => "general-form", "role" => "form")); ?>
<div id="material-dropzone" class="post-dropzone">
	<div class="modal-body clearfix">
		<?php $this->load->view("stock/material/form"); ?>
	</div>

	<div class="form-group">
		<div class="col-md-12 row pr0">
			<?php
			$this->load->view("includes/file_list", array("files" => $model_info->files, "image_only" => true));
			?>
		</div>
	</div>

	<?php $this->load->view("includes/dropzone_preview"); ?>

	<div class="modal-footer">
		<button class="btn btn-default upload-file-button pull-left btn-sm round" type="button"
			style="color:#7988a2"><i class="fa fa-camera"></i>
			<?php echo lang("upload_image"); ?>
		</button>
		<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
			<?php echo lang('close'); ?>
		</button>
		<?php if ((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)) { ?>
			<button type="submit" class="btn btn-primary">
				<span class="fa fa-check-circle"></span>
				<?php echo lang('save'); ?>
			</button>
		<?php } ?>
	</div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	$(document).ready(function () {
		let uploadUrl = "<?php echo get_uri("stock/upload_material_file"); ?>";
		let validationUri = "<?php echo get_uri("stock/validate_material_file"); ?>";

		let dropzone = attachDropzoneWithForm("#material-dropzone", uploadUrl, validationUri);

		$('[data-toggle="tooltip"]').tooltip();
		$("#material-form").appForm({
			onSuccess: function (result) {
				if (result.view === "details") {
					appAlert.success(result.message, { duration: 10000 });
					setTimeout(function () {
						location.reload();
					}, 500);
				} else {
					$("#material-table").appTable({ newData: result.data, dataId: result.id });
				}
			}
		});
	});
</script>