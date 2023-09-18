<style type="text/css">
.modal-dialog {
    width: 100%;
    max-width: 980px;
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
    line-break: anywhere;
    vertical-align: top;
    padding: 12px 12px;
    text-align: left;
}

.popup .product td a{
    display: inline-block;
    margin-top: 3px;
}

.popup .product td.product_name{
    padding-left: 8px;
    width: 40%;
    padding-top: 15px;
}

.popup .product td.product_supplier{
    width: 40%;
}

.popup .product td.product_supplier .supplier_not_found{
    display: inline-block;
    margin-top: 3px;
}

.popup .product td.product_supplier .supplier_name{
    display: inline-block;
    margin-top: 3px;
}

.popup .product td.reference_number{
    width: 20%;
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
                        <td class="custom-bg product_name">สินค้า</td>
                        <td class="custom-bg product_supplier">ผู้จัดจำหน่าย</td>
                        <td class="custom-bg reference_number">เลขที่ใบขอซื้อ</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>สร้างใบขอซื้อ</button>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    getProducts('<?php echo $doc_id; ?>');
    
    $('#ajaxModal').on('hidden.bs.modal', function (e) {
        parent.updateRow("<?php echo $doc_id; ?>");
    });

    $("#btnSubmit").click(function() {
        sales_order_items = [];
        $(".sales_order_items").each(function(i, obj) {
            supplier_id = $(obj).find(".suppliers").val();
            if(supplier_id === undefined) supplier_id = null;
            sales_order_items.push({sales_order_item_id:$(obj).data("id"), supplier_id:supplier_id});
        });

        axios.post('<?php echo current_url(); ?>', {
            task: 'do_make_purchase_requisition',
            sales_order_id: '<?php echo $doc_id; ?>',
            sales_order_items: JSON.stringify(sales_order_items)
        }).then(function (response) {
            data = response.data;
            alert(data.message);
            
            if(data.status == "success"){
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
    }).catch(function (error) {});
}
</script>