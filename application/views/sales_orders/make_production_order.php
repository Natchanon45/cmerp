<style type="text/css">
.modal-dialog {
    width: 100%;
    max-width: 1000px;
}

.popup .product {
    height: 280px;
    border: 1px solid #ccc;
    overflow: auto;
}

.popup .product table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.popup .product table tr.norecord td{
    text-align: center;
    vertical-align: middle;
    padding-top: 100px;
}

.popup .product table tr.norecord:hover{
    background: #fff;
}

.popup .product td{
    border-bottom: 1px solid #f2f2f2;
}

.popup .product tr:last-child td{
    border-bottom: 0;
}

.popup .product thead td{
    border-right: 1px solid #f2f2f2;
}

.popup .product thead td:last-child{
    border-right: 0;
}

.popup .product td{
    line-break: anywhere;
    vertical-align: top;
    padding: 12px 12px;
    text-align: left;
}

.popup .product td.product_name{
    padding-left: 8px;
    width: 46%;
    padding-top: 15px;
}

.popup .product td.product_supplier .supplier_name{
    display: inline-block;
    margin-top: 2px;
}

.popup .product td.instock{
    width: 18%;
    text-align: right;
    padding-top: 15px;
}

.popup .product td.total_used{
    width: 18%;
    text-align: right;
}

.popup .product td.total_submit{
    width: 18%;
    text-align: right;
}

.popup .product thead tr td {
    position: sticky;
    top: 0;
    font-weight: 500;
    border-bottom: 1px solid #e8e8e8;
    text-align: left;
    color: #fff;
}

.popup .product tbody tr {
    border-bottom: 1px solid #e8e8e8;
}

.popup .product tbody tr:hover {
    background: #e6f7ff;
}

.popup .product tbody tr:last-child{
    border-bottom: 0;
}

.popup .product .choose-inv-button{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
}

.popup .product .choose-inv-button:hover{
    background: #fff !important;
}

.popup .product .choose-inv-button:active{
    position: relative;
    top: 1px;
}

.popup .suppliers{
    width: 100%;
}

.popup .made_to_order{
    border: 1px solid #ccc;
    background: #fff;
    display: block;
    border-radius: 4px;
    padding: 0 4px;
}

.popup .made_to_order.readonly{
    border: 0;
    background: none;
}


.popup .sales_order_items input,
.popup .sales_order_items input:focus{
    width: 110px;
    text-align: right;
    border: 0;
    outline: none;
    background: none;
}
</style>
<div class="popup">
    <div class="container">
        <div class="product">
            <table>
                <thead>
                    <tr>
                        <td class="custom-bg product_name"><?php echo lang("account_so_product"); ?></td>
                        <td class="custom-bg instock"><?php echo lang("account_so_in_stock"); ?></td>
                        <td class="custom-bg total_used"><?php echo lang("account_so_order_qty"); ?></td>
                        <td class="custom-bg total_submit"><?php echo lang("account_made_to_order"); ?></td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span><?php echo lang("account_button_close"); ?></button>
        <button type="button" id="btnSubmit" class="btn btn-primary" style="display: <?php echo $can_make_project == true ? 'inline-block':'none'; ?>;"><span class="fa fa-check-circle"></span><?php echo lang("account_button_create_project"); ?></button>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    getProducts('<?php echo $doc_id; ?>');
    
    $('#ajaxModal').on('hidden.bs.modal', function (e) {
        parent.updateRow("<?php echo $doc_id; ?>");
    });

    

    $("#btnSubmit").click(function() {
        made_to_order = [];

        $(".sales_order_items").each(function(){
            made_to_order.push({"item_id": $(this).data("id"), "submit_num": $(this).find(".total_submit input").val()});
        });

        axios.post('<?php echo current_url(); ?>', {
            task: 'do_make_project',
            sales_order_id: '<?php echo $doc_id; ?>',
            made_to_order: JSON.stringify(made_to_order)
        }).then(function (response) {
            data = response.data;
            alert(data.message);
            
            if(data.status == "success"){
                $("#btnSubmit").css("display", "none");
                getProducts("<?php echo $doc_id; ?>");
            }
            
        }).catch(function (error) {});
    });
});

function getProducts(sales_order_id){
    axios.post('<?php echo current_url(); ?>', {
        task: 'get_products',
        doc_id: sales_order_id
    }).then(function (response) {
        let data = response.data;
        $(".product table tbody").empty().append(data.html);

        $(".total_submit input").blur(function(){
            let trobj = $(this).parent().parent().parent();
            let total_used_num = tonum(trobj.find(".total_used input").val(), <?php echo DEC; ?>);
            let submit_num = tonum($(this).val(), <?php echo DEC; ?>);
            if(submit_num < 0) submit_num = 0;
            if(submit_num > total_used_num) submit_num = total_used_num;

            $(this).val($.number(submit_num, <?php echo DEC; ?>));
        });
    }).catch(function (error) {});
}
</script>