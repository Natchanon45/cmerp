<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="product_name" class=" col-md-3"><?php echo lang('item'); ?></label>
        <div class="col-md-9">
            <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">
            <input type="text" id="product_name" value="<?php echo $product_name; ?>" placeholder="<?php echo lang('select_or_create_new_item'); ?>" class="form-control" >
        </div>
    </div>
    <div id="product_description_section" class="form-group">
        <label for="product_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <textarea id="product_description" class="form-control"><?php echo $product_description; ?></textarea>
        </div>
    </div>
    <?php if($item_mixing_groups_id != null): ?>
        <?php if(!empty($product_formulas)): ?>
            <div id="product_formula_section" class="form-group">
                <label for="product_formula" class="col-md-3">สูตรผสม</label>
                <div class=" col-md-9">
                    <select id="product_formula" class="form-control">
                        <?php foreach($product_formulas as $pf): ?>
                            <option value="<?php echo $pf['id']; ?>" <?php if($pf['id'] == $item_mixing_groups_id) echo "selected"; ?>><?php echo $pf['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
    <?php endif;?>
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
        <label for="total_price" class=" col-md-3">ราคารวม</label>
        <div class="col-md-9">
            <input type="text" id="total_price" value="<?php echo $total_price; ?>" placeholder="<?php echo lang('rate'); ?>" class="form-control numb" readonly>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" id="x" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<script type="text/javascript">
$(document).ready(function () {
    $("#btnSubmit").click(function() {
        product_formula_id = "none";
        if($("#product_formula").val() != undefined) product_formula_id = $("#product_formula").val();
        axios.post('<?php echo current_url(); ?>', {
            task: 'save',
            doc_id : "<?php echo $doc_id; ?>",
            item_id : "<?php if(isset($item_id)) echo $item_id; ?>",
            product_id:$("#product_id").val(),
            product_formula_id:product_formula_id,
            product_name:$("#product_name").val(),
            product_description:$("#product_description").val(),
            quantity: $("#quantity").val(),
            unit: $("#unit").val(),
            price: $("#price").val()
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
        axios.post('<?php echo current_url(); ?>', {
            task: 'choose_product',
            item_id: e.added.id,
        }).then(function (response) {
            data = response.data;

            $("#product_id").val(data.id);
            $("#product_name").val(data.title);
            $("#product_description").val(data.description);
            $("#quantity").val(data.quantity);
            $("#unit").val(data.unit);
            $("#price").val(data.price);
            $("#total_price").val(data.total_price);
            
            product_formulas = data.formulas;

            $("#product_description_section").next().remove("#product_formula_section");

            if(product_formulas.length >= 1){
                pf = '<div id="product_formula_section" class="form-group">';
                    pf += '<label for="product_formula" class="col-md-3">ส่วนประกอบ</label>';
                    pf += '<div class=" col-md-9">';
                        pf += '<select id="product_formula" class="form-control">';
                            for(i = 0; i < product_formulas.length; i++){
                                let obj = product_formulas[i];
                                pf += "<option value='"+obj['id']+"'>"+obj['name']+"</option>";
                            }
                        pf += '</select>';
                    pf += '</div>';
                pf += '</div>';

                $("#product_description_section").after(pf);
            }
        });
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
});

function calculatePrice(){
    let quantity = tonum($("#quantity").val(), <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>);
    let price = tonum($("#price").val(), <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>);
    let total_price = 0.00;

    if(quantity < 0 ) quantity = 0;
    if(price < 0 ) price = 0;

    total_price = price * quantity;

    $("#quantity").val($.number(quantity, <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>));
    $("#price").val($.number(price, 2));
    $("#total_price").val($.number(total_price, 2));
}
</script>