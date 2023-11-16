<style type="text/css">
	.hide_head {
		height: 0 !important;
		line-height: 0 !important;
		padding: 0 !important;
		margin: 0 !important;
	}

	.form-header {
		width: 100%;
		max-width: 600px;
	}

	.pointer-none {
		pointer-events: none;
	}
</style>

<input type="hidden" name="id" id="id" value="<?php echo isset($model_info->id) ? $model_info->id : ""; ?>" />
<input type="hidden" name="clone_to_new_item" id="clone_to_new_item" value="0" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<div class="form-header" id="form-header">
	<div class="form-group">
		<label for="name" class="<?php echo $label_column; ?>">
			<?php echo lang("item_mixing_name"); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
			echo form_input(
				array(
					"id" => "name",
					"name" => "name",
					"value" => $model_info->name,
					"class" => "form-control",
					"placeholder" => lang("item_mixing_names"),
					"autofocus" => true,
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
				)
			);
			?>
		</div>
	</div>

	<div class="form-group">
		<label for="item_id" class="<?php echo $label_column; ?>">
			<?php echo lang("items"); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
			echo form_dropdown(
				"item_id",
				$items_dropdown,
				array($model_info->item_id),
				"class='select2 validate-hidden pointer-none'"
			);
			?>
		</div>
	</div>

	<div class="form-group">
		<label for="ratio" class="<?php echo $label_column; ?>">
			<?php echo lang("item_mixing_ratio"); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<div class="input-suffix">
				<?php
				echo form_input(array(
					"type" => "number",
					"name" => "ratio",
					"class" => "form-control",
					"value" => (isset($model_info->ratio) && $model_info->ratio > 0) ? to_decimal_format2($model_info->ratio) : 1,
					"required" => true,
					"readonly" => true
				));
				?>
				<div class="input-tag"><?php echo mb_strtoupper(@$item->unit_type); ?></div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label for="is_public" class="<?php echo $label_column; ?>">
			<?php echo lang("item_mixing_is_public"); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
			echo form_checkbox(
				"is_public",
				"1",
				$model_info->is_public ? true : false,
				"id='is_public'"
			);
			?>
		</div>
	</div>

	<div class="form-group" id="client-form-group">
		<label for="for_client_id" class="<?php echo $label_column; ?>">
			<?php echo lang("item_mixing_for_client"); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
			echo form_dropdown(
				"for_client_id",
				$clients_dropdown,
				array($model_info->for_client_id),
				"class='select2 validate-hidden'"
			);
			?>
		</div>
	</div>
</div>

<div id="type-container">
	<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
		<thead>
			<tr role="row">
				<th>
					<?php echo lang("stock_material"); ?>
				</th>
				<th class="w200">
					<?php echo lang("item_mixing_ratio"); ?>
				</th>	
				<th class="w70">
					<a href="javascript:void(0);" id="btn-add-category" class="btn btn-primary">
						<span class="fa fa-plus-circle"></span>
						<?php echo lang("add_category"); ?>
					</a>
				</th>
			</tr>
		</thead>
	
		<tbody id="table-body">
			<?php if (isset($material_cat_mixings) && sizeof($material_cat_mixings)): ?>
				<?php foreach ($material_cat_mixings as $id => $name): ?>
					<?php $temp_cat_id = "cat_" . uniqid(); ?>
					<tr>
						<td colspan="2">
							<select name="cat_id[<?php echo $temp_cat_id; ?>]" temp-cat-id="<?php echo $temp_cat_id; ?>" id="<?php echo $temp_cat_id; ?>" class="form-control select-category" required>
								<option value="" data-unit=""><?php echo lang("select_category"); ?></option>
								<?php foreach ($categories_dropdown as $key => $value): ?>
									<option value="<?php echo $key; ?>" <?php if ($id == $key) { echo "selected"; } ?>><?php echo $value; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<a href="javascript:void(0);" class="btn btn-primary btn-add-material" temp-cat-id="<?php echo $temp_cat_id; ?>">
								<span class="fa fa-plus-circle"></span>
								<?php echo lang("add"); ?>
							</a>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
								<thead class="hide_head">
									<tr class="hide_head">
										<td class="hide_head"></td>
										<td class="hide_head w200"></td>
										<td class="hide_head w70"></td>
									</tr>
								</thead>
								<tbody class="table-body2">
									<?php foreach ($material_cat_mixings[$id] as $material): ?>
										<tr>
											<td>
												<input type="hidden" name="item_type[<?php echo $temp_cat_id; ?>][]" value="RM">
												<select name="material_id[<?php echo $temp_cat_id; ?>][]" class="form-control select-material" required>
													<option value="" data-unit=""><?php echo lang("select_material"); ?></option>
													<?php foreach ($material_dropdown as $dropdown): ?>
														<option value="<?php echo $dropdown->id; ?>" data-unit="<?php echo $dropdown->unit; ?>" <?php if ($material->material_id == $dropdown->id) { echo "selected"; } ?>>
															<?php echo (isset($bom_material_read_production_name) && $bom_material_read_production_name) ? $dropdown->name . " - " . $dropdown->production_name : $dropdown->name; ?>
														</option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<div class="input-suffix">
													<input type="number" name="mixing_ratio[<?php echo $temp_cat_id; ?>][]" class="form-control select-number-ratio" value="<?php echo $material->ratio; ?>" min="0" step="0.000001" required>
													<div class="input-tag"><?php echo $material->material_unit; ?></div>
												</div>
											</td>
											<td>
												<a href="javascript:void(0);" class="btn btn-danger btn-delete-material">
													<span class="fa fa-trash"></span>
													<?php echo lang("delete"); ?>
												</a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div><br>

<div id="type-container-sfg">
	<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
		<thead>
			<tr role="row">
				<th>
					<?php echo lang("sfg_column_header"); ?>
				</th>
				<th class="w200">
					<?php echo lang("item_mixing_ratio"); ?>
				</th>	
				<th class="w70">
					<a href="javascript:void(0);" id="btn-add-category" class="btn btn-info">
						<span class="fa fa-plus-circle"></span>
						<?php echo lang("add_category"); ?>
					</a>
				</th>
			</tr>
		</thead>
	
		<tbody id="table-body">
			<?php if (isset($sfg_cat_mixings) && sizeof($sfg_cat_mixings)): ?>
				<?php foreach ($sfg_cat_mixings as $id => $name): ?>
					<?php $sfg_temp_cat_id = "cat_" . uniqid(); ?>
					<tr>
						<td colspan="2">
							<select name="cat_id[<?php echo $sfg_temp_cat_id; ?>]" temp-cat-id="<?php echo $sfg_temp_cat_id; ?>" id="<?php echo $sfg_temp_cat_id; ?>" class="form-control select-category" required>
								<option value="" data-unit=""><?php echo lang("select_category"); ?></option>
								<?php foreach ($sfg_categories_dropdown as $key => $value): ?>
									<option value="<?php echo $key; ?>" <?php if ($id == $key) { echo "selected"; } ?>><?php echo $value; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<a href="javascript:void(0);" class="btn btn-primary btn-add-material" temp-cat-id="<?php echo $sfg_temp_cat_id; ?>">
								<span class="fa fa-plus-circle"></span>
								<?php echo lang("add"); ?>
							</a>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
								<thead class="hide_head">
									<tr class="hide_head">
										<td class="hide_head"></td>
										<td class="hide_head w200"></td>
										<td class="hide_head w70"></td>
									</tr>
								</thead>
								<tbody class="table-body2">
									<?php foreach ($sfg_cat_mixings[$id] as $material): ?>
										<tr>
											<td>
												<input type="hidden" name="item_type[<?php echo $sfg_temp_cat_id; ?>][]" value="SFG">
												<select name="material_id[<?php echo $sfg_temp_cat_id; ?>][]" class="form-control select-material" required>
													<option value="" data-unit=""><?php echo lang("select_material"); ?></option>
													<?php foreach ($sfg_dropdown as $dropdown): ?>
														<option value="<?php echo $dropdown->id; ?>" data-unit="<?php echo $dropdown->unit_type; ?>" <?php if ($material->material_id == $dropdown->id) { echo "selected"; } ?>>
															<?php echo (isset($bom_material_read_production_name) && $bom_material_read_production_name) ? $dropdown->item_code . " - " . $dropdown->title : $dropdown->item_code; ?>
														</option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<div class="input-suffix">
													<input type="number" name="mixing_ratio[<?php echo $sfg_temp_cat_id; ?>][]" class="form-control select-number-ratio" value="<?php echo $material->ratio; ?>" min="0" step="0.000001" required>
													<div class="input-tag"><?php echo $material->material_unit; ?></div>
												</div>
											</td>
											<td>
												<a href="javascript:void(0);" class="btn btn-danger btn-delete-material">
													<span class="fa fa-trash"></span>
													<?php echo lang("delete"); ?>
												</a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$("#form-header .select2").select2();

		var clientFormGroup = $("#client-form-group");
		var publicSelect = $("#is_public");

		toggleClient();

		publicSelect.change(function () {
			toggleClient();
		});

		function toggleClient () {
			if (publicSelect.prop("checked")) {
				clientFormGroup.css("display", "none");
			} else {
				clientFormGroup.css("display", "block");
			}
		}

		var typeContainer = $("#type-container");
		var tableBody = typeContainer.find("#table-body");
		var btnAddCat = typeContainer.find("#btn-add-category");

		var typeContainerSfg = $("#type-container-sfg")
		var tableBodySfg = typeContainerSfg.find("#table-body");
		var btnAddCatSfg = typeContainerSfg.find("#btn-add-category");

		btnAddCat.click(function (e) {
			e.preventDefault();

			let temp_cat_id = `cat_${$.now()}`;
			
			tableBody.append(`
				<tr>
					<td colspan="2">
						<select name="cat_id[${temp_cat_id}]" temp-cat-id="${temp_cat_id}" class="form-control select-category" required>
							<option value="" data-unit=""><?php echo lang("select_category"); ?></option>
							<?php foreach ($categories_dropdown as $key => $value): ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<a href="javascript:void(0);" temp-cat-id="${temp_cat_id}" class="btn btn-primary btn-add-material hide">
							<span class="fa fa-plus-circle"></span>
							<?php echo lang("add"); ?>
						</a>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
							<thead class="hide_head">
								<tr class="hide_head">
									<td class="hide_head"></td>
									<td class="hide_head w200"></td>
									<td class="hide_head w70"></td>
								</tr>
							</thead>
							<tbody class="table-body2"></tbody>
						</table>
					</td>
				</tr>
			`);

			processBindingCat();
		});

		btnAddCatSfg.click(function (e) {
			e.preventDefault();

			let sfg_temp_cat_id = `cat_${$.now()}`;
			
			tableBodySfg.append(`
				<tr>
					<td colspan="2">
						<select name="cat_id[${sfg_temp_cat_id}]" temp-cat-id="${sfg_temp_cat_id}" class="form-control select-category" required>
							<option value="" data-unit=""><?php echo lang("select_category"); ?></option>
							<?php foreach ($sfg_categories_dropdown as $key => $value): ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<a href="javascript:void(0);" temp-cat-id="${sfg_temp_cat_id}" class="btn btn-primary btn-add-material hide">
							<span class="fa fa-plus-circle"></span>
							<?php echo lang("add"); ?>
						</a>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
							<thead class="hide_head">
								<tr class="hide_head">
									<td class="hide_head"></td>
									<td class="hide_head w200"></td>
									<td class="hide_head w70"></td>
								</tr>
							</thead>
							<tbody class="table-body2"></tbody>
						</table>
					</td>
				</tr>
			`);

			processBindingCatSfg();
		});

		function processBindingCat() {
			typeContainer.find(".select-category").select2("destroy");
			typeContainer.find(".select-category").select2();
			typeContainer.find(".select-category").change(function (e) {
				e.preventDefault();

				let self = $(this);
				let btn = self.closest("tr").find(".btn-add-material");

				if (self.val() === null || self.val() === '') {
					// console.log('Value is either null or empty.');
					btn.addClass("hide");
				} else {
					// console.log('Value is not null or empty.');
					btn.removeClass("hide");
				}
			});

			typeContainer.find(".btn-add-material").unbind();
			typeContainer.find(".btn-add-material").click(function (e) {
				e.preventDefault();

				let self = $(this);
				let dropdown = self.closest("tr").find(".select-category");

				dropdown.addClass("pointer-none");
				addMaterialRow(
					$(this).attr("temp-cat-id"),
					$(this).closest("tr").next().find(".table-body2")
				);
			});
		}

		function processBindingCatSfg() {
			typeContainerSfg.find(".select-category").select2("destroy");
			typeContainerSfg.find(".select-category").select2();
			typeContainerSfg.find(".select-category").change(function (e) {
				e.preventDefault();

				let self = $(this);
				let btn = self.closest("tr").find(".btn-add-material");

				if (self.val() === null || self.val() === '') {
					// console.log('Value is either null or empty.');
					btn.addClass("hide");
				} else {
					// console.log('Value is not null or empty.');
					btn.removeClass("hide");
				}
			});

			typeContainerSfg.find(".btn-add-material").unbind();
			typeContainerSfg.find(".btn-add-material").click(function (e) {
				e.preventDefault();

				let self = $(this);
				let dropdown = self.closest("tr").find(".select-category");

				dropdown.addClass("pointer-none");
				addMaterialRowSfg(
					$(this).attr("temp-cat-id"),
					$(this).closest("tr").next().find(".table-body2")
				);
			});
		}

		processBindingCat();
		processBindingCatSfg();

		function addMaterialRow(rowCate, rowBody) {
			rowBody.append(`
				<tr>
					<td>
						<input type="hidden" name="item_type[${rowCate}][]" value="RM">
						<select name="material_id[${rowCate}][]" class="form-control select-material" required>
							<option value="" data-unit=""><?php echo lang("select_material"); ?></option>
							<?php foreach ($material_dropdown as $dropdown): ?>
								<option value="<?php echo $dropdown->id; ?>" data-unit="<?php echo $dropdown->unit; ?>">
									<?php echo (isset($bom_material_read_production_name) && $bom_material_read_production_name) ? $dropdown->name . " - " . $dropdown->production_name : $dropdown->name; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<div class="input-suffix">
							<input type="number" name="mixing_ratio[${rowCate}][]" class="form-control select-number-ratio" value="0" min="0" step="0.000001" required>
							<div class="input-tag"></div>
						</div>
					</td>
					<td>
						<a href="javascript:void(0);" class="btn btn-danger btn-delete-material">
							<span class="fa fa-trash"></span> 
							<?php echo lang("delete"); ?>
						</a>
					</td>
				</tr>
			`);

			processBinding();
		}

		function addMaterialRowSfg(rowCate, rowBody) {
			rowBody.append(`
				<tr>
					<td>
						<input type="hidden" name="item_type[${rowCate}][]" value="SFG">
						<select name="material_id[${rowCate}][]" class="form-control select-material" required>
							<option value="" data-unit=""><?php echo lang("select_material"); ?></option>
							<?php foreach ($sfg_dropdown as $dropdown): ?>
								<option value="<?php echo $dropdown->id; ?>" data-unit="<?php echo $dropdown->unit_type; ?>">
									<?php echo (isset($bom_material_read_production_name) && $bom_material_read_production_name) ? $dropdown->item_code . " - " . $dropdown->title : $dropdown->item_code; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<div class="input-suffix">
							<input type="number" name="mixing_ratio[${rowCate}][]" class="form-control select-number-ratio" value="0" min="0" step="0.000001" required>
							<div class="input-tag"></div>
						</div>
					</td>
					<td>
						<a href="javascript:void(0);" class="btn btn-danger btn-delete-material">
							<span class="fa fa-trash"></span> 
							<?php echo lang("delete"); ?>
						</a>
					</td>
				</tr>
			`);

			processBindingSfg();
		}

		function processBinding() {
			typeContainer.find(".btn-delete-material").unbind();
			typeContainer.find(".btn-delete-material").click(function (e) {
				e.preventDefault();

				const self = $(this);
				
				let trCount = self.closest("tbody").find("tr");
				let dropdownCategory = self.closest("table").closest("tr").prev("tr").find(".select-category");
				let trActual = trCount.length - 1;
				
				if (trActual <= 0) {
					dropdownCategory.removeClass("pointer-none");
				}
				
				self.closest("tr").remove();
				processBinding();
			});

			typeContainer.find(".select-material").select2("destroy");
			typeContainer.find(".select-material").select2();
			typeContainer.find(".select-material").unbind();
			typeContainer.find(".select-material").change(function (e) {
				e.preventDefault();

				let self = $(this);
				let option = self.find(`[value="${self.val()}"]`);

				self.closest("tr").find(".input-tag").html(option.data("unit"));
			});

			typeContainer.find(".select-number-ratio").unbind();
			typeContainer.find(".select-number-ratio").click(function (e) {
				e.preventDefault()
				e.target.select();
			});
		}

		function processBindingSfg() {
			typeContainerSfg.find(".btn-delete-material").unbind();
			typeContainerSfg.find(".btn-delete-material").click(function (e) {
				e.preventDefault();

				const self = $(this);
				
				let trCount = self.closest("tbody").find("tr");
				let dropdownCategory = self.closest("table").closest("tr").prev("tr").find(".select-category");
				let trActual = trCount.length - 1;
				
				if (trActual <= 0) {
					dropdownCategory.removeClass("pointer-none");
				}
				
				self.closest("tr").remove();
				processBindingSfg();
			});

			typeContainerSfg.find(".select-material").select2("destroy");
			typeContainerSfg.find(".select-material").select2();
			typeContainerSfg.find(".select-material").unbind();
			typeContainerSfg.find(".select-material").change(function (e) {
				e.preventDefault();

				let self = $(this);
				let option = self.find(`[value="${self.val()}"]`);

				self.closest("tr").find(".input-tag").html(option.data("unit"));
			});

			typeContainerSfg.find(".select-number-ratio").unbind();
			typeContainerSfg.find(".select-number-ratio").click(function (e) {
				e.preventDefault()
				e.target.select();
			});
		}

		processBinding();
		processBindingSfg();
	});
</script>