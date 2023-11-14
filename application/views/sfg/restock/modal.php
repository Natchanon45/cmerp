<?php echo form_open(get_uri("sfg/restock_addedit_modal/save"), array("id" => "restock-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
	<?php $this->load->view("sfg/restock/form"); ?>
</div>

<style type="text/css">
	.dev2-alert {
		display: none;
		color: #ff4500;
		padding-left: 1.2rem;
	}

	.input-suffix > .input-tag-2 {
		position: absolute;
		top: 0;
		right: 0;
		padding: 6px 8px;
		font-size: 15px;
	}

	.modal-body {
		overflow-x: auto;
	}
</style>

<p class="dev2-alert">
	<?php echo lang('serial_number_duplicate'); ?>
</p>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<span class="fa fa-close"></span><?php echo ' ' . lang('close'); ?>
	</button>

	<button id="btn-submit" type="button" class="btn btn-primary">
		<span class="fa fa-check-circle"></span> 
		<?php echo lang('save'); ?>
	</button>
	<button id="btn-post" type="submit" style="display: none;"></button>
</div>

<?php echo form_close(); ?>

<style type="text/css">
	@media (min-width: 999px) {
		.modal-dialog {
			width: 90%;
		}
	}
</style>
<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();

		$("#restock-form").appForm({
			onSuccess: function (result) {
				// console.log(result);

				setTimeout(function () {
					location.reload();
				}, 500);
			}
		});

		$("#company_name").focus();
	});

	const btnPost = document.querySelector('#btn-post');
	const btnSubmit = document.querySelector('#btn-submit');
	const dev2Alert = document.querySelector('.dev2-alert');

	btnSubmit.addEventListener('click', (e) => {
		e.preventDefault();

        let inputSern = document.querySelectorAll('.data-sern');
        let listSern = [];
        inputSern.forEach((item) => {
            listSern.push(item.value);
        });

        const toFindDuplicates = arry => arry.filter((item, index) => arry.indexOf(item) !== index);
        const duplicates = toFindDuplicates(listSern);
        
        if (duplicates.length == 0) {
            btnPost.click();
        } else {
			dev2Alert.style.display = 'block';

			setTimeout(function () {
				dev2Alert.style.display = 'none';
			}, 5000);
        }
	});
</script>

<!-- done -->