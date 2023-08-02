<?php
$type = lang('stock_material');
if ($mat_req_info->mr_type == 2) {
    $type = lang('stock_item');
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
            <?php $material_list = $this->Materialrequests_model->getMaterialListByRequestType($mat_req_info->mr_type); ?>
            <select id="material_id" name="material_id" class="form-control">
                <option value="">-</option>
                <?php foreach ($material_list as $item): ?>
                    <option value="<?php echo $item["id"]; ?>"><?php echo $item["text"]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="stock_id" class=" col-md-3"><?php echo lang('stock_restock_name'); ?></label>
        <div class="col-md-9">
            <select id="stock_id" name="stock_id" class="form-control" required>
                <option value="">-</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remaining_quantity" class="col-md-3"><?php echo lang('stock_restock_remaining'); ?></label>
        <div class="col-md-9">
            <input type="decimal" id="remaining_quantity" name= "remaining_quantity" value="0" class="form-control" readonly>
        </div>
    </div>

    <input type="hidden" id="max_quantity" name="max_quantity">
    <div class="form-group">
        <label for="quantity" class="col-md-3"><?php echo lang('request_quantity'); ?></label>
        <?php
            $start_quantity = 0;
            if (isset($mat_item_info->quantity) && !empty($mat_item_info->quantity)) {
                $start_quantity = $mat_item_info->quantity;
            }
        ?>
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

    $('#material_id').on('change', function() {
        getStockRemainingList();
    });

    $('#stock_id').on('change', function() {
        let selected = $('option:selected', this).attr('remain');
        $('#remaining_quantity').val(selected);
        $('#quantity').attr('max', selected);
    });

    $('#quantity').on('click', function() {
        $(this).select();
        $(this).removeClass('alert-failure');
    });

    $('#btnSubmit').on('click', function(event) {
        event.preventDefault();
        postStockMaterialToList();
    });
});

function getStockRemainingList() {
    if (!$('#material_id').val()) {
        let blank_option = '<option value="">-</option>';

        $('#stock_id').empty().append(blank_option);
        $('#stock_id').select2();

        $('#remaining_quantity').val(0);
        return;
    }

    let url = '<?php echo_uri('materialrequests/stock_material_list/'); ?>' + $('#material_id').val();
    <?php if ($mat_req_info->mr_type == 2): ?>
        url = '<?php echo_uri('materialrequests/stock_item_list/'); ?>' + $('#material_id').val();
    <?php endif; ?>

    axios.get(url).then((response) => {
        let options = "";
        let cur_actual = 0;
        let cur_stock = '<?php echo (isset($mat_item_info->stock_id) && !empty($mat_item_info->stock_id)) ? $mat_item_info->stock_id : null; ?>';
        let cur_quantity = '<?php echo (isset($mat_item_info->quantity) && !empty($mat_item_info->quantity)) ? $mat_item_info->quantity : 0; ?>';
        
        response.data.map((item, index) => {
            if (item.id == cur_stock) {
                cur_actual = parseFloat(item.actual_remain) + parseFloat(cur_quantity);
                options += `
                    <option value="${item.id}" remain="${item.actual_remain}" selected>${item.name} - (${item.remaining})</option>
                `;
            } else {
                options += `
                    <option value="${item.id}" remain="${item.actual_remain}">${item.name} - (${item.remaining})</option>
                `;
            }
        });

        $('#stock_id').empty().append(options);
        $('#stock_id').select2();
        
        if (cur_actual > 0) {
            $('#remaining_quantity').val(cur_actual);
            $('#quantity').attr('max', cur_actual);
        } else {
            $('#remaining_quantity').val(response.data[0].actual_remain);
            $('#quantity').attr('max', response.data[0].actual_remain);
        }
    });
}

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
