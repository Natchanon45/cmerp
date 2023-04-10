<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="product_name" class=" col-md-3"><?php echo lang('item'); ?></label>
        <div class="col-md-9">
            <input type="hidden" id="product_id" value="<?php echo !empty($qirow)?$qirow->product_id:''; ?>" />
            <input type="text" id="product_name" value="<?php echo !empty($qirow)?$qirow->product_name:''; ?>" placeholder="<?php echo lang('select_or_create_new_item'); ?>" class="form-control" >
            <!--<a id="product_name_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>-->
        </div>
    </div>
    <div class="form-group">
        <label for="product_description" class="col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <textarea id="product_description" class="form-control"><?php echo !empty($qirow)?$qirow->product_description:''; ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="quantity" class=" col-md-3"><?php echo lang('quantity'); ?></label>
        <div class="col-md-9">
            <input type="text" id="quantity" value="<?php echo !empty($qirow)?$qirow->quantity:''; ?>" placeholder="<?php echo lang('quantity'); ?>" class="form-control" >
        </div>
    </div>
    <div class="form-group">
        <label for="unit" class=" col-md-3"><?php echo lang('unit_type'); ?></label>
        <div class="col-md-9">
            <input type="text" id="unit" value="<?php echo !empty($qirow)?$qirow->unit:''; ?>" placeholder="<?php echo lang('quantity'); ?>" class="form-control" >
        </div>
    </div>
    <div class="form-group">
        <label for="price" class=" col-md-3"><?php echo lang('rate'); ?></label>
        <div class="col-md-9">
            <input type="text" id="price" value="<?php echo !empty($qirow)?to_decimal_format($qirow->price):'0.00'; ?>" placeholder="<?php echo lang('rate'); ?>" class="form-control" >
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
            doc_id : "<?php echo (!empty($qirow)?$qirow->id:'')?>",
            item_id : "<?php echo (!empty($qirow)?$qirow->item_id:'')?>",
            product_id:$("#product_id").val(),
            product_name:$("#product_name").val(),
            product_description: $("#product_description").val(),
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
                window.location = data.target;
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
        $("#product_id").val(e.added.id);
        $("#product_name").val(e.added.text);
        $("#product_description").val(e.added.description);
    });
});
</script>