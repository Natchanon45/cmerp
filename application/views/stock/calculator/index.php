<div id="page-content" class="p20 clearfix">
	<div class="panel panel-default">

		<div class="page-title clearfix">
			<h1>
				<a class="title-back" href="<?php echo get_uri('stock'); ?>">
					<i class="fa fa-chevron-left" aria-hidden="true"></i>
				</a>
				<?php echo lang('stock_calculator'); ?>
			</h1>
			<div class="title-button-group">
				<a href="" class="btn btn-default" title="<?php echo lang('restart_calc'); ?>">
					<i class="fa fa-refresh" aria-hidden="true"></i>
					<?php echo lang('restart_calc'); ?>
				</a>
			</div>
		</div>

		<div class="calculator-container">
			<?php echo form_open(get_uri("stock/calculator"), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
				<div id="type-container">
					<table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
						<thead>
							<tr role="row" style="height: 70px;">
								<th style="background: #f5f5f5 !important; width: 25px;"></th>
								<th style="background: #f5f5f5 !important; width: 350px;">
									<?php echo lang('item'); ?>
								</th>
								<th style="background: #f5f5f5 !important;">
									<?php echo lang('item_mixing_name'); ?>
								</th>
								<th style="background: #f5f5f5 !important; width: 150px;">
									<?php echo lang('quantity'); ?>
								</th>
								<th style="background: #f5f5f5 !important; width: 80px; text-align: center;">
									<a href="javascript:void(0);" id="btn-add-material" class="btn btn-primary w100p">
										<span class="fa fa-plus-circle"></span>
										<?php echo lang('add'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody id="table-body">
							<?php
							if (isset($project_materials) && sizeof($project_materials)): 
								$i_btn = 0;
								foreach ($project_materials as $n => $k):
									?>
									<tr>
										<td style="text-align: center; padding: 0 !important;">
											<?php if (isset($k->result) && sizeof($k->result)): ?>
												<a class="btn-expand" href="javascript:void(0);" data-row="<?php echo $n; ?>">
													<em class="fa fa-plus-circle"></em>
												</a>
											<?php endif; ?>
										</td>
										<td>
											<input type="hidden" name="item_id[]" value="<?php echo (isset($k->id) && !empty($k->id)) ? $k->id : ''; ?>" />
											<?php echo $k->title; ?>
										</td>
										<td>
											<input type="hidden" name="item_mixing[]" value="<?php echo (isset($k->mixing_id) && !empty($k->mixing_id)) ? $k->mixing_id : ''; ?>" />
											<?php echo (isset($k->mixing_name) && !empty($k->mixing_name)) ? $k->mixing_name : ''; ?>
										</td>
										<td>
											<input type="hidden" name="quantity[]" value="<?php echo (isset($k->quantity) && !empty($k->quantity)) ? $k->quantity : ''; ?>" />
											<?php echo (isset($k->quantity) && !empty($k->quantity)) ? to_decimal_format2($k->quantity) . ' ' . $k->unit_type : ''; ?>
										</td>
										<td>
											<button type="button" class="btn btn-warning w100p" id="btn-excel-<?php echo $i_btn; ?>">
												<span class="fa fa-table"></span>
												<?php echo lang('excel'); ?>
											</button>
										</td>
									</tr>
									<?php if (isset($k->result) && sizeof($k->result)): ?>
										<tr class="row-target" data-row="<?php echo $n; ?>">
											<td colspan="5">
												<div class="toggle-container">
													<table class="display dataTable" cellspacing="0" width="100%">
														<thead>
															<tr role="row">
																<th class="w250">
																	<?php echo lang('stock_material'); ?>
																</th>
																<th class="w200">
																	<?php echo lang('stock_restock_name'); ?>
																</th>
																<th class="w125 text-right">
																	<?php echo lang('quantity'); ?>
																</th>
																<th class="w125 text-right">
																	<?php echo lang('stock_material_unit'); ?>
																</th>
																<?php if ($can_read_price): ?>
																	<th class="w150 text-right">
																		<?php echo lang('stock_calculator_value'); ?>
																	</th>
																	<th class="w100 text-right">
																		<?php echo lang('currency'); ?>
																	</th>
																<?php endif; ?>
															</tr>
														</thead>
														<tbody>
															<?php $total = 0; foreach ($k->result as $s): ?>
																<tr>
																	<td><?php echo (isset($s->material_name) && !empty($s->material_name)) ? $s->material_name : ''; ?></td>
																	<td>
																		<?php echo (isset($s->stock_name) && !empty($s->stock_name)) ? $s->stock_name : ''; ?>
																	</td>
																	<td class="w125 text-right">
																		<?php
																			$s->ratio = floatval($s->ratio); 
																			$classer = 'color-red'; 
																			
																			if ($s->ratio > 0) $classer = 'color-green';
																			
																			echo '<div class="' . $classer . '">' 
																			. to_decimal_format2($s->ratio) 
																			. '</div>'; 
																		?>
																	</td>
																	<td class="text-right">
																		<?php echo (isset($s->material_unit) && !empty($s->material_unit)) ? mb_strtoupper($s->material_unit) : '-'; ?>
																	</td>
																	<?php if ($can_read_price): ?>
																		<td class="w150 text-right">
																			<?php
																			if (!empty($s->value)) {
																				$total += $s->value;
																				echo to_decimal_format3($s->value);
																			} else
																				echo '-';
																			?>
																		</td>
																		<td class="text-right">
																			<?php echo (isset($s->currency) && !empty($s->currency)) ? lang($s->currency) : lang("THB"); ?>
																		</td>
																	<?php endif; ?>
																</tr>
															<?php endforeach; ?>
														</tbody>
														<?php if ($can_read_price): ?>
															<tfoot>
																<tr>
																	<td colspan="3"></td>
																	<th class="text-right">
																		<?php echo lang('total'); ?>
																	</th>
																	<th class="text-right">
																		<?php echo to_decimal_format3($total); ?>
																	</th>
																	<th class="text-right">
																		<?php echo lang("THB"); ?>
																	</th>
																</tr>
															</tfoot>
														<?php endif; ?>
													</table>
												</div>
											</td>
										</tr>
									<?php endif; ?>
									<?php
									$i_btn++;
								endforeach;
							endif;
							?>
						</tbody>
					</table>
				</div>
				<div class="btns text-center">
					<button type="submit" class="btn btn-primary" id="btn-submit">
						<span class="fa fa-check-circle"></span>
						<?php echo lang('stock_calculator_submit'); ?>
					</button>
				</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<style type="text/css">
	.calculator-container {
		width: 100%;
		padding: 20px;
		margin: 0 auto;
	}

	.calculator-container .btns {
		margin: 10px 0 0 0;
	}

	table.dataTable > thead > tr:hover > th,
	table.dataTable > thead > tr > th,
	table.dataTable > tbody > tr:hover > td,
	table.dataTable > tbody > tr > td {
		background-color: #ffffff !important;
	}

	table.dataTable tr > th {
		border-top: 1px solid #f2f2f2 !important;
	}

	.color-red {
		font-weight: 600;
		color: red;
	}

	.color-green {
		font-weight: 600;
		color: green;
	}

	select.inactive {
		opacity: 0 !important;
		pointer-events: none !important;
	}

	.btn-expand {
		font-size: 18px;
	}

	tr.row-target {
		pointer-events: none;
	}

	tr.row-target > td {
		padding: 0 !important
	}

	tr.row-target .toggle-container {
		display: block;
		padding: 15px 15px 15px 15px;
		background: #f8f8f8;
	}
</style>

<script type="text/javascript">
	$(document).ready(function () {
		var items = <?php echo json_encode($items); ?>;
		var itemMixings = <?php echo json_encode($item_mixings); ?>;
		var typeContainer = $('#type-container');
		var tableBody = typeContainer.find('#table-body'), btnAdd = typeContainer.find('#btn-add-material');
		
		btnAdd.click(function (e) {
			e.preventDefault();

			btnAdd.addClass('hide');
			tableBody.prepend(`
				<tr>
					<td></td>
					<td>
						<select name="item_id[]" class="form-control select-material" required>
							<option value="" data-unit=""></option>
							${items.map(function (d) {
								return `<option value="${d.id}" data-unit="${d.unit_type}">${d.title}</option>`;
							})}
						</select>
					</td>
					<td>
						<select name="item_mixing[]" class="form-control inactive">
						</select>
					</td>
					<td>
						<div class="input-suffix">
							<input type="number" name="quantity[]" required class="form-control" min="0" step="0.0001" value="0" />
							<div class="input-tag"></div>
						</div>
					</td>
					<td>
						<a href="javascript:void(0);" class="btn btn-danger btn-delete-material w100p">
							<span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
						</a>
					</td>
				</tr>
			`);
			processBinding();
		});
		processBinding();

		function processBinding() {
			typeContainer.find('.btn-delete-material').unbind();
			typeContainer.find('.btn-delete-material').click(function (e) {
				e.preventDefault();

				btnAdd.removeClass('hide');
				$(this).closest('tr').remove();
				processBinding();
			});

			typeContainer.find('[name="quantity[]"]').on('click', function (e) {
				e.preventDefault();
				$(this).select();
			});

			typeContainer.find('.select-material').select2('destroy');
			typeContainer.find('.select-material').select2();
			typeContainer.find('.select-material').unbind();
			typeContainer.find('.select-material').change(function () {
				let self = $(this);
				let option = $(this).find('[value="' + this.value + '"]');
				
				let parent = self.closest('tr');
				parent.find('.input-tag').html(option.data('unit'));

				let options = itemMixings.filter(d => d.item_id == this.value);
				let mixingSelect = parent.find('[name="item_mixing[]"]');

				mixingSelect.val('');
				mixingSelect.find('option').remove();
				mixingSelect.append('<option value=""></option>');
				if (options.length) {
					options.map(function (d) {
						mixingSelect.append(`<option value="${d.id}">${d.name}</option>`);
					});
					mixingSelect.removeClass('inactive');
					mixingSelect.select2();
				} else {
					mixingSelect.addClass('inactive');
				}
			});
		}

		<?php if (isset($project_materials) && sizeof($project_materials)): ?>
			for (let i = 0; i < <?php echo count($project_materials); ?>; i++) {
				$(`#btn-excel-${i}`).click(function (e) {
					e.preventDefault();

					let projectMaterial = <?php echo json_encode($project_materials); ?>;
					let url = '<?php echo get_uri("stock/calculator_create_excel"); ?>';
					// console.log(projectMaterial[i]);

					$.ajax({
						url: url,
						type: 'POST',
						dataType: 'json',
						data: { data: projectMaterial[i] },
						success: function (result) {
							window.location.assign(result.file);
						},
						error: function (error) {
							console.log(error);
						}
					});
				});
			}
		<?php endif; ?>

		typeContainer.find('.btn-expand').click(function (e) {
			e.preventDefault();

			var rowId = $(this).data('row');
			var row = typeContainer.find('tr[data-row="' + rowId + '"]');
			if (row.length) row.find('.toggle-container').slideToggle();
		});

		$('#btn-restock').click(function (e) {
			e.preventDefault();

			$('[name="restock_process"]').val(1);
			$('#btn-submit').click();
		});
	});
</script>
