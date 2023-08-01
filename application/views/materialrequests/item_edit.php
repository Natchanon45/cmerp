<?php
// Setting request type
$type = lang('stock_material');
if (!empty($mat_req_info->mr_type) && $mat_req_info->mr_type == 2) {
    $type = lang('stock_item');
}

// Setting start quantity
$start_quantity = 0;
if (isset($mat_item_info->quantity) && !empty($mat_item_info->quantity)) {
    $start_quantity = $mat_item_info->quantity;
}

// Setting should be remaining quantity
$should_remain = 0;
if ($mat_stock_info->stock_remain >= $start_quantity) {
    $should_remain = $mat_stock_info->stock_remain;
}
?>

<style type="text/css">
.pointer-none {
    pointer-events: none;
}

.alert-failure {
    color: red;
}
</style>

<input type="hidden" id="mr_id" name="mr_id" value="<?php echo $mat_req_info->id; ?>" />

<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="material_id" class=" col-md-3"><?php echo $type; ?></label>
        <div class="col-md-9">
            <select id="material_id" name="material_id" class="form-control pointer-none" readonly>
                <option value="<?php echo $mat_item_info->material_id; ?>"><?php echo $mat_item_info->code . ' - ' . $mat_item_info->title; ?></option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="stock_id" class=" col-md-3"><?php echo lang('stock_restock_name'); ?></label>
        <div class="col-md-9">
            <select id="stock_id" name="stock_id" class="form-control pointer-none" readonly>
                <option value="<?php echo $mat_stock_info->stock_id; ?>"><?php echo $mat_stock_info->stock_name . ' - (' . number_format($should_remain, 2) . ')'; ?></option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remaining_quantity" class="col-md-3"><?php echo lang('stock_restock_remaining'); ?></label>
        <div class="col-md-9">
            <input type="decimal" id="remaining_quantity" name= "remaining_quantity" value="<?php echo $should_remain; ?>" class="form-control" readonly>
        </div>
    </div>

    <input type="hidden" id="max_quantity" name="max_quantity">
    <div class="form-group">
        <label for="quantity" class="col-md-3"><?php echo lang('request_quantity'); ?></label>
        <div class="col-md-9">
            <input type="decimal" id="quantity" name= "quantity" class="form-control" value="<?php echo $start_quantity; ?>" required>
        </div>
    </div>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<span class="fa fa-close"></span> 
		<?php echo lang('close'); ?>
	</button>
    <button type="submit" id="btnSubmit" class="btn btn-primary">
        <span class="fa fa-check-circle"></span> 
        <?php echo lang('save'); ?>
    </button>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $('#material_id').select2();
    $('#stock_id').select2();

    $('#quantity').on('click', function() {
        $(this).select();
        $(this).removeClass('alert-failure');
    });

    $('#btnSubmit').on('click', function(event) {
        event.preventDefault();
        postStockMaterialToList();
    });
});

function postStockMaterialToList() {
    let url = '<?php echo_uri('materialrequests/item_add_save'); ?>';
    let req = {
        item_id: '<?php echo (isset($mat_item_info->id) && !empty($mat_item_info->id)) ? $mat_item_info->id : '0'; ?>',
        bpim_id: '<?php echo (isset($mat_item_info->bpim_id) && !empty($mat_item_info->bpim_id)) ? $mat_item_info->bpim_id : '0'; ?>',
        mr_id: '<?php echo (isset($mat_req_info->id) && !empty($mat_req_info->id)) ? $mat_req_info->id : '0'; ?>',
        mr_type: '<?php echo (isset($mat_req_info->mr_type) && !empty($mat_req_info->mr_type)) ? $mat_req_info->mr_type : '0'; ?>',
        project_id: '<?php echo (isset($mat_req_info->project_id) && !empty($mat_req_info->project_id)) ? $mat_req_info->project_id : '0'; ?>',
        project_name: '<?php echo (isset($mat_req_info->project_name) && !empty($mat_req_info->project_name)) ? $mat_req_info->project_name : '0'; ?>',
        material_id: $('#material_id').val(),
        stock_id: $('#stock_id').val(),
        quantity: parseFloat($('#quantity').val()),
        remaining_quantity: parseFloat($('#remaining_quantity').val())
    };
    
    if (req.quantity === 0 || req.quantity > req.remaining_quantity) {
        $('#quantity').addClass('alert-failure');
        return;
    } else {
        axios.post(url, req).then((res) => {
            if (res.data) {
                window.parent.loadItems();
                $("#ajaxModal").modal("hide");
            }
        });
    }
}
</script>
