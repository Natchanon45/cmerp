<?php
$label_text = lang("label_raw_material");

if (isset($doc_type) && !empty($doc_type)) {
    if ($doc_type == 3) {
        $label_text = lang("label_finished_goods");
    }

    if ($doc_type == 5) {
        $label_text = lang("label_expense");
    }
}
?>

<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="product_name" class=" col-md-3"><?php echo $label_text; ?></label>
        <div class="col-md-9">
            <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">
            <input type="text" id="product_name" value="<?php echo $product_name; ?>" placeholder="<?php echo lang('select_or_create_new_item'); ?>" class="form-control" <?php if (isset($product_id) && !empty($product_id)) echo 'style="pointer-events: none;"'; ?>>
        </div>
    </div>
    <div class="form-group">
        <label for="product_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <textarea id="product_description" class="form-control" <?php if ($doc_type != 5) echo "readonly"; ?>><?php echo $product_description; ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="quantity" class=" col-md-3"><?php echo lang('quantity'); ?></label>
        <div class="col-md-9">
            <input type="text" id="quantity" value="<?php echo $quantity; ?>" placeholder="<?php echo lang('quantity'); ?>" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="unit" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
        <div class="col-md-9">
            <input type="text" id="unit" value="<?php echo $unit; ?>" placeholder="<?php echo lang('stock_material_unit'); ?>" class="form-control" <?php if ($doc_type != 5) { echo "readonly"; } ?>>
        </div>
    </div>
    <div class="form-group">
        <label for="price" class=" col-md-3"><?php echo lang('rate'); ?></label>
        <div class="col-md-9">
            <input type="text" id="price" value="<?php echo $price; ?>" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="total_price" class=" col-md-3"><?php echo lang('total_price'); ?></label>
        <div class="col-md-9">
            <input type="text" id="total_price" value="<?php echo $total_price; ?>" class="form-control">
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" id="x" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>
    <button type="button" id="btnSubmit" class="btn btn-primary">
        <span class="fa fa-check-circle"></span>
        <?php echo lang("save"); ?>
    </button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#btnSubmit").click(function () {
            let url = '<?php echo current_url(); ?>';
            let request = {
                task: 'save',
                doc_id: '<?php echo $doc_id; ?>',
                item_id: '<?php if (isset($item_id) && !empty(isset($item_id))) { echo $item_id; } ?>',
                product_id: $("#product_id").val(),
                product_name: $("#product_name").val(),
                product_description: $("#product_description").val(),
                quantity: $("#quantity").val(),
                unit: $("#unit").val(),
                price: $("#price").val(),
                total_price: $("#total_price").val(),
            }

            axios.post(url, request).then(function (response) {
                let data = response.data;
                $(".fnotvalid").remove();

                if (data.status == "validate") {
                    for (let key in data.messages) {
                        if (data.messages[key] != "") {
                            $(`<span class="fnotvalid">${data.messages[key]}</span>`).insertAfter(`#${key}`);
                        }
                    }
                } else if (data.status == "success") {
                    window.parent.loadItems();
                    $("#ajaxModal").modal("hide");
                } else {
                    alert(data.message);
                }
            }).catch(function (error) {
                console.log(error);
            });
        });

        $("#product_name").select2({
            showSearchBox: true,
            ajax: {
                url: "<?php echo current_url(); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (keyword, page) {
                    return {
                        keyword: keyword,
                        task: "suggest_products",
                        type: '<?php echo $doc_type; ?>'
                    };
                },
                results: function (data, page) {
                    return { results: data };
                }
            }
        }).change(function (e) {
            if (e.val === "+") {
                $("#product_name").select2("destroy").val("").focus();
                $("#product_id").val("");
            } else if (e.val) {
                $("#product_id").val(e.added.id);
                $("#product_name").val(e.added.text);
                $("#product_description").val(e.added.description);
                $("#quantity").val("1");
                $("#unit").val(e.added.unit);
                $("#price").val(e.added.price);
            }
        });
        
        <?php if (isset($item_id)): ?>
            $("#product_name").select2('data', {
                id: "<?php echo $product_name; ?>",
                text: "<?php echo $product_name; ?>"
            });
        <?php endif; ?>

        $("#quantity, #price, #total_price").on('click', function (e) {
            e.target.select();
        });

        $("#price").on('blur', function (e) {
            e.preventDefault();

            let quantity = tonum($("#quantity").val(), 2);
            let price = tonum($("#price").val(), 4);
            let total = 0;

            if (quantity < 0) { quantity = 0; }
            if (price < 0) { price = 0; }
            total = quantity * price;
            
            $("#quantity").val($.number(quantity, 2));
            $("#price").val($.number(price, 4));
            $("#total_price").val($.number(total, 4));
        });

        $("#total_price").on('blur', function (e) {
            e.preventDefault();

            let quantity = tonum($("#quantity").val(), 2);
            let total = tonum($("#total_price").val(), 4);
            let price = 0;

            if (quantity < 0) { quantity = 0; }
            if (total < 0) { total = 0; }
            price = total / quantity;
            
            $("#quantity").val($.number(quantity, 2));
            $("#price").val($.number(price, 4));
            $("#total_price").val($.number(total, 4));
        });
    });
</script>
