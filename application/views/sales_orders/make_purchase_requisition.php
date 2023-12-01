<style type="text/css">
.modal-dialog {
    width: 100%;
    max-width: 1024px;
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
    padding: 10px;
    text-align: left;
}

.popup .product td.product_name{
    padding-left: 8px;
    width: 21%;
}

.popup .product td.product_supplier{
    width: 21%;
    padding-top: 8px;
}

.popup .product td.product_supplier .supplier_not_found{
    display: inline-block;
    margin-top: 2px;
}

.popup .product td.product_supplier .supplier_name{
    display: inline-block;
    margin-top: 2px;
}

.popup .product td.unit{
    width: 11%;
    text-align: left;
}

.popup .product td.instock{
    width: 11%;
    text-align: right;
}

.popup .product td.quantity{
    width: 11%;
    text-align: right;
}

.popup .product td.topurchase{
    width: 11%;
    text-align: right;
}

.popup .product td.reference_number{
    width: 14%;
    text-align: center;
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
</style>
<div class="popup">
    <div class="container">
        <div class="product">
            <table>
                <thead>
                    <tr>
                        <td class="custom-bg product_name"><?php echo lang("account_so_product"); ?></td>
                        <td class="custom-bg product_supplier"><?php echo lang("account_so_supplier"); ?></td>
                        <td class="custom-bg unit"><?php echo lang("account_so_unit"); ?></td>
                        <td class="custom-bg instock"><?php echo lang("account_so_in_stock"); ?></td>
                        <td class="custom-bg quantity"><?php echo lang("account_so_order_qty"); ?></td>
                        <td class="custom-bg topurchase"><?php echo lang("account_so_pr_qty"); ?></td>
                        <td class="custom-bg reference_number"><?php echo lang("account_so_pr_no"); ?></td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span><?php echo lang("account_button_close"); ?></button>
        <?php if($this->Permission_m->bom_supplier_read == "1"): ?>
            <button type="button" id="btnSubmit" class="btn btn-primary" style="display: <?php echo $can_make_pr == true ? 'inline-block':'none'; ?>;"><span class="fa fa-check-circle"></span><?php echo lang("account_button_create_pr"); ?></button>
        <?php endif; ?>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    getProducts('<?php echo $doc_id; ?>');
    
    $('#ajaxModal').on('hidden.bs.modal', function (e) {
        parent.updateRow("<?php echo $doc_id; ?>");
    });

    <?php if($this->Permission_m->bom_supplier_read == "1"): ?>
        $("#btnSubmit").click(function() {
            sales_order_items = [];
            $(".sales_order_items").each(function(i, obj) {
                let sales_order_item_id = $(obj).data("id");
                if(sales_order_item_id === undefined) return;

                supplier_id = $(obj).find(".suppliers").val();
                if(supplier_id === undefined) supplier_id = null;
                sales_order_items.push({sales_order_item_id:sales_order_item_id, supplier_id:supplier_id});
            });

            axios.post('<?php echo current_url(); ?>', {
                task: 'do_make_purchase_requisition',
                sales_order_id: '<?php echo $doc_id; ?>',
                sales_order_items: JSON.stringify(sales_order_items)
            }).then(function (response) {
                data = response.data;
                alert(data.message);
                
                if(data.status == "success"){
                    if(data.can_make_pr == true) $("#btnSubmit").css("display", "inline-block");
                    else $("#btnSubmit").css("display", "none");
                    getProducts("<?php echo $doc_id; ?>");
                }
                
            }).catch(function (error) {});
        });
    <?php endif; ?>
});

function getProducts(sales_order_id){
    axios.post('<?php echo current_url(); ?>', {
        task: 'get_products',
        doc_id: sales_order_id
    }).then(function (response) {
        let data = response.data;
        $(".product table tbody").empty().append(data.html);
    }).catch(function (error) {});
}
</script>