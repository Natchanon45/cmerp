<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="product_name" class=" col-md-3"><?php echo lang('item'); ?></label>
        <div class="col-md-9">
            <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">
            <input type="text" id="product_name" value="<?php echo $product_name; ?>" placeholder="<?php echo lang('select_or_create_new_item'); ?>" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="product_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <textarea id="product_description" class="form-control"><?php echo $product_description; ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="quantity" class=" col-md-3"><?php echo lang('quantity'); ?></label>
        <div class="col-md-9">
            <input type="text" id="quantity" value="<?php echo $quantity; ?>" placeholder="<?php echo lang('quantity'); ?>" class="form-control numb" >
        </div>
    </div>
    <div class="form-group">
        <label for="unit" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
        <div class="col-md-9">
            <input type="text" id="unit" value="<?php echo $unit; ?>" placeholder="หน่วย" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="price" class=" col-md-3"><?php echo lang('rate'); ?></label>
        <div class="col-md-9">
            <input type="text" id="price" value="<?php echo $price; ?>" placeholder="<?php echo lang('rate'); ?>" class="form-control numb">
        </div>
    </div>
    <div class="form-group">
        <label for="item_discount_value" class=" col-md-3">ส่วนลดต่อหน่วย</label>
        <div class="col-md-9">
            <input type="text" id="item_discount_value" value="<?php echo $discount_value; ?>" placeholder="ส่วนลดต่อหน่วย" class="form-control numb" style="width: calc(100% - 90px); float: left; margin-right: 8px;">
            <select id="item_discount_type" class="form-control" style="width:82px;">
                <option value="P" <?php if($discount_type == "P") echo "selected"; ?>>%</option>
                <option value="F" <?php if($discount_type == "F") echo "selected"; ?>>฿</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="total_price" class=" col-md-3">ราคารวม</label>
        <div class="col-md-9">
            <input type="text" id="total_price" value="<?php echo $total_price; ?>" class="form-control numb" readonly>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<script type="text/javascript">
$(document).ready(function () {

    $("#btnSubmit").click(function() {
        axios.post('<?php echo current_url(); ?>', {
            task: 'save',
            doc_id : "<?php echo $doc_id; ?>",
            item_id : "<?php if(isset($item_id)) echo $item_id; ?>",
            product_id:$("#product_id").val(),
            product_name:$("#product_name").val(),
            product_description: $("#product_description").val(),
            quantity: $("#quantity").val(),
            unit: $("#unit").val(),
            price: $("#price").val(),
            discount_type: $("#item_discount_type").val(),
            discount_value: $("#item_discount_value").val()

        }).then(function (response) {
            data = response.data;
            $(".fnotvalid").remove();

            if(data.status == "validate"){
                for(var key in data.messages){
                    if(data.messages[key] != ""){
                        $("<span class='fnotvalid'>"+data.messages[key]+"</span>").insertAfter("#"+key);
                    }
                }
            }else if(data.status == "success"){
                window.parent.loadItems();
                $("#ajaxModal").modal("hide");
            }else{
                alert(data.message);
            }
        }).catch(function (error) {

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
                    task: "suggest_products"
                };
            },
            results: function (data, page) {
                return {results: data};
            }
        }
    }).change(function (e) {
        if (e.val === "+") {
            $("#product_name").select2("destroy").val("").focus();
            $("#product_id").val(""); //set the flag to add new item in library
        } else if (e.val) {
            $("#product_id").val(e.added.id);
            $("#product_name").val(e.added.text);
            $("#product_description").val(e.added.description);
            $("#quantity").val("1");
            $("#unit").val(e.added.unit);
            $("#price").val(e.added.price);

            calculatePrice();
        }
    });

    <?php if(isset($item_id)): ?>
        $("#product_name").select2('data', {
                                                id:"<?php echo $product_name; ?>",
                                                text: "<?php echo $product_name; ?>"
                                            });
    <?php endif; ?>

    $(".numb").blur(function(){
        calculatePrice();
    });

    $("#item_discount_type").change(function(){
        calculatePrice();
    }); 
});

function calculatePrice(){
    let quantity = tonum($("#quantity").val(), <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>);
    let price = tonum($("#price").val(), 2);
    let price_after_discount = price;
    let discount_type = $("#item_discount_type").val();
    let discount_value = tonum($("#item_discount_value").val(), 2);

    if(discount_type == "P"){
        if(discount_value < 0) discount_value = 0;
        if(discount_value >= 100) discount_value = 100;
        price_after_discount = price - tonum((price * discount_value)/100, 2);
        /*if(discount_value >= 100){
            discount_value = 100;
            price_after_discount = 0;
        }*/

    }else{
        if(discount_value < 0) discount_value = 0;
        if(discount_value > price) discount_value = price;
        price_after_discount = price - discount_value;
    }

    if(quantity < 0 ) quantity = 0;
    if(price_after_discount < 0 ) price_after_discount = 0;
    let total_price = price_after_discount * quantity;
    
    $("#quantity").val($.number(quantity, <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>));
    $("#price").val($.number(price, 2));
    $("#item_discount_value").val($.number(discount_value, 2));
    $("#total_price").val($.number(total_price, 2));
}
</script>