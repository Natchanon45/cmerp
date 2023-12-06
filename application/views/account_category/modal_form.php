<style type="text/css">
    .pointer-none {
        pointer-events: none;
        appearance: none;
    }
</style>

<?php echo form_open(get_uri("account_category/post_category"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("account_type"); ?>
        </label>
        <div class="col-md-9">
            <select name="account_type" id="account_type" class="form-control" required>
                <option value="0"><?php echo "-- " . lang("account_type_select") . " --"; ?></option>
                <?php if (!empty($account_primary)): ?>
                    <?php foreach ($account_primary as $primary): ?>
                        <option value="<?php echo $primary->id; ?>"><?php echo $primary->thai_name . " (" . $primary->account_code . ")"; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("account_sub_type"); ?>
        </label>
        <div class="col-md-9">
            <select name="account_sub_type" id="account_sub_type" class="form-control pointer-none" required>
                <option value="0"><?php echo "-- " . lang("account_sub_type_select") . " --"; ?></option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("account_code"); ?>
        </label>
        <div class="col-md-9">
            <input type="number" name="account_code" id="account_code" class="form-control" required>
        </div>
    </div>

    <div class="form-group">
        <label for="title" class="col-md-3">
            <?php echo lang("account_description"); ?>
        </label>
        <div class="col-md-9">
            <div class="input-suffix" style="margin-bottom: 5px;">
                <input type="text" name="th_description" id="account_description" class="form-control" required>
                <div class="input-tag-2"><?php echo "(" . lang("thai") . ")"; ?></div>
            </div>
            <div class="input-suffix">
                <input type="text" name="en_description" id="account_description" class="form-control" required>
                <div class="input-tag-2"><?php echo "(" . lang("english") . ")"; ?></div>
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
    const secondaryList = JSON.parse('<?php echo $account_secondary; ?>');
    const secondaryTopSelect = '<?php echo "-- " . lang("account_sub_type_select") . " --"; ?>';

    $(document).ready(function () {
        $("#account_type").select2();
        $("#account_type").on("change", function (e) {
            e.preventDefault();

            let self = $(this);
            let secondaryOption = secondaryList.filter(i => i.primary_id == self.val());
            let secondarySelect = $("#account_sub_type");

            if (secondaryOption.length) {
                secondarySelect.val('');
                secondarySelect.find('option').remove();
                secondarySelect.append(`<option value="0">${secondaryTopSelect}</option>`);

                secondaryOption.map((i) => {
                    secondarySelect.append(`<option value="${i.id}" data-code="${i.account_code}">${i.thai_name} (${i.account_code})</option>`);
                });

                secondarySelect.removeClass('pointer-none');
                secondarySelect.select2();
            }
        });
        
        $("#category-form").appForm({
            onSuccess: function (result) {
                // console.log(result);

                if (result.id != 0) {
                    $("#category-table").appTable(
                        { newData: result.data, dataId: result.id }
                    );
                }
            }
        });
    });
</script>