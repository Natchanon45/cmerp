<?php
$type = lang('stock_material');
if ($mat_req_info?->mr_type == 2) {
    $type = lang('stock_item');
}
?>

<input type="hidden" id="mr_id" name="mr_id" value="<?php echo @$mat_req_info?->id; ?>" />

<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="material_id" class=" col-md-3"><?php echo $type; ?></label>
        <div class="col-md-9">
            <?php $material_list = $this->Materialrequests_model->getMaterialListByRequestType($mat_req_info?->mr_type); ?>
            <select id="material_id" name="material_id" class="form-control">
                <?php foreach ($material_list as $type): ?>
                    <option value="<?php echo $type["id"]; ?>" <?php if ($type["id"] == @$mat_item_info?->material_id) echo "selected"; ?>><?php echo $type["text"]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="stock_id" class=" col-md-3"><?php echo lang('stock_restock_name'); ?></label>
        <div class="col-md-9">
            <select id="stock_id" name="stock_id" class="form-control">
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remaining_quantity" class="col-md-3"><?php echo lang('stock_restock_remaining'); ?></label>
        <div class="col-md-9">
            <input type="decimal" id="remaining_quantity" name= "remaining_quantity" class="form-control" readonly>
        </div>
    </div>

    <input type="hidden" id="max_quantity" name="max_quantity">
    <div class="form-group">
        <label for="quantity" class="col-md-3"><?php echo lang('request_quantity'); ?></label>
        <div class="col-md-9">
            <input type="decimal" id="quantity" name= "quantity" class="form-control" value="<?php echo @$mat_item_info?->quantity ? $mat_item_info?->quantity : 0; ?>" required>
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

    $('#material_id').on('change', function() {
        getStockMaterialList();
    });

    $('#stock_id').on('change', function() {
        let selected = $('option:selected', this).attr('remain');
        $('#remaining_quantity').val(selected);
        $('#quantity').attr('max', selected);
    });

    $('#quantity').on('click', function() {
        $(this).select();
    });

    $('#btnSubmit').on('click', function(event) {
        event.preventDefault();

        let url = '<?php echo_uri('materialrequests/item_add_save'); ?>';
        let req = {
            item_id: '<?php echo @$mat_item_info?->id ? $mat_item_info?->id : 0; ?>',
            bpim_id: '<?php echo @$mat_item_info?->bpim_id ? $mat_item_info?->bpim_id : 0; ?>',
            mr_id: '<?php echo $mat_req_info?->id; ?>',
            mr_type: '<?php echo $mat_req_info?->mr_type; ?>',
            project_id: '<?php echo $mat_req_info?->project_id; ?>',
            project_name: '<?php echo $mat_req_info?->project_name; ?>',
            material_id: $('#material_id').val(),
            stock_id: $('#stock_id').val(),
            quantity: parseFloat($('#quantity').val()),
            remaining_quantity: parseFloat($('#remaining_quantity').val())
        };

        if (req.quantity === 0 || req.quantity > req.remaining_quantity) {
            return;
        } else {
            axios.post(url, req).then((res) => {
                if (res.data) {
                    window.parent.loadItems();
                    $("#ajaxModal").modal("hide");
                }
            });
        }
    });

    getStockMaterialList();
});

function getStockMaterialList() {
    let url = '<?php echo_uri('materialrequests/stock_material_list/'); ?>' + $('#material_id').val();
    axios.get(url).then((response) => {
        let options = "";
        let cur_actual = 0;
        let cur_stock = '<?php echo @$mat_item_info?->stock_id; ?>';
        let cur_quantity = '<?php echo @$mat_item_info?->quantity ? $mat_item_info?->quantity : 0; ?>';
        
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

function getStockFinishedGoodsList() {
    let url = '<?php echo_uri('materialrequests/stock_material_list/'); ?>' + $('#material_id').val();

    axios.get(url).then((response) => {
        console.log(response.data);
    });
}
</script>