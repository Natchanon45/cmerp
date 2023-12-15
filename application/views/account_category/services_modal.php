<style type="text/css">
    .modal-dialog {
        width: 720px;
    }
    .pointer-none {
        pointer-events: none;
        appearance: none;
    }
</style>

<?php echo form_open(get_uri("account_category/services_modal_post"), array("id" => "services-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
    <?php if (isset($model_info->id) && !empty($model_info->id)): ?>
        <input type="hidden" name="post_id" id="post_id" value="<?php echo $model_info->id; ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("service_wage"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" value="<?php echo (isset($model_info->service_name) && !empty($model_info->service_name)) ? $model_info->service_name : ''; ?>" name="service_name" id="service_name" class="form-control" placeholder="<?php echo lang("service_wage_name"); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("expense_account_category"); ?>
        </label>
        <div class="col-md-9">
            <div style="margin-bottom: 5px;">
                <select name="expense_acct_sec_id" id="expense_acct_sec_id" class="form-control" required>
                    <option value=""><?php echo "-- " . lang("account_sub_type_select") . " --"; ?></option>
                    <?php if (sizeof($expense_account_secondary)): ?>
                        <?php if (isset($model_info->expense_acct_sec_id) && !empty($model_info->expense_acct_sec_id)): ?>
                            <?php foreach ($expense_account_secondary as $secondary): ?>
                                <option value="<?php echo $secondary->id; ?>" <?php if ($model_info->expense_acct_sec_id == $secondary->id) { echo "selected"; } ?>>
                                    <?php echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($expense_account_secondary as $secondary): ?>
                                <option value="<?php echo $secondary->id; ?>">
                                    <?php echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <select name="expense_acct_cate_id" id="expense_acct_cate_id" class="form-control pointer-none" required>
                    <?php if (isset($model_info->expense_acct_cate_id) && !empty($model_info->expense_acct_cate_id)): ?>
                        <option value="<?php echo $model_info->expense_acct_cate_id; ?>"><?php echo $model_info->expense_acct_cate_name; ?></option>
                    <?php else: ?>
                        <option value=""><?php echo "-- " . lang("account_expense_select") . " --"; ?></option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("income_account_category"); ?>
        </label>
        <div class="col-md-9">
            <div style="margin-bottom: 5px;">
                <select name="income_acct_sec_id" id="income_acct_sec_id" class="form-control" required>
                    <option value=""><?php echo "-- " . lang("account_sub_type_select") . " --"; ?></option>
                    <?php if (sizeof($income_account_secondary)): ?>
                        <?php if (isset($model_info->income_acct_sec_id) && !empty($model_info->income_acct_sec_id)): ?>
                            <?php foreach ($income_account_secondary as $secondary): ?>
                                <option value="<?php echo $secondary->id; ?>" <?php if ($model_info->income_acct_sec_id == $secondary->id) { echo "selected"; } ?>>
                                    <?php echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($income_account_secondary as $secondary): ?>
                                <option value="<?php echo $secondary->id; ?>">
                                    <?php echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <select name="income_acct_cate_id" id="income_acct_cate_id" class="form-control pointer-none" required>
                    <?php if (isset($model_info->income_acct_cate_id) && !empty($model_info->income_acct_cate_id)): ?>
                        <option value="<?php echo $model_info->income_acct_cate_id; ?>"><?php echo $model_info->income_acct_cate_name; ?></option>
                    <?php else: ?>
                        <option value=""><?php echo "-- " . lang("account_income_select") . " --"; ?></option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
        <?php echo lang("save"); ?>
    </button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    const expenseCategoryList = JSON.parse('<?php echo $expense_account_category; ?>');
    const expenseCategorySelected = '<option value=""><?php echo "-- " . lang("account_expense_select") . " --"; ?></option>';
    
    const incomeCategoryList = JSON.parse('<?php echo $income_account_category; ?>');
    const incomeCategorySelected = '<option value=""><?php echo "-- " . lang("account_income_select") . " --"; ?></option>';

    $(document).ready(function () {
        
        // Expenses controller
        $("#expense_acct_sec_id").select2();
        $("#expense_acct_sec_id").on("change", function (e) {
            e.preventDefault();

            let self = $(this);
            let categoryOption = expenseCategoryList.filter(i => i.secondary_id == self.val());
            let categorySelection = $("#expense_acct_cate_id");

            categorySelection.val('');
            categorySelection.find('option').remove();
            categorySelection.append(expenseCategorySelected);

            if (categoryOption.length) {
                categoryOption.map((i) => {
                    categorySelection.append(`<option value="${i.id}">${i.account_code} - ${i.thai_name}</option>`);
                });

                categorySelection.removeClass('pointer-none');
                categorySelection.select2();
            } else {
                categorySelection.addClass('pointer-none');
                categorySelection.select2();
            }
        });

        // Revenues controller
        $("#income_acct_sec_id").select2();
        $("#income_acct_sec_id").on("change", function (e) {
            e.preventDefault();

            let self = $(this);
            let categoryOption = incomeCategoryList.filter(i => i.secondary_id == self.val());
            let categorySelection = $("#income_acct_cate_id");

            categorySelection.val('');
            categorySelection.find('option').remove();
            categorySelection.append(expenseCategorySelected);

            if (categoryOption.length) {
                categoryOption.map((i) => {
                    categorySelection.append(`<option value="${i.id}">${i.account_code} - ${i.thai_name}</option>`);
                });

                categorySelection.removeClass('pointer-none');
                categorySelection.select2();
            } else {
                categorySelection.addClass('pointer-none');
                categorySelection.select2();
            }
        });
        
        $("#services-form").appForm({
            onSuccess: function (result) {
                // console.log(result);

                if (result.id != 0) {
                    $("#services-table").appTable(
                        { newData: result.data, dataId: result.id }
                    );
                }
            }
        });
    });
</script>