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
    width: 38%;
}

.popup .product td.product_supplier .supplier_name{
    display: inline-block;
    margin-top: 2px;
}

.popup .product td.instock{
    width: 14%;
    text-align: right;
}

.popup .product td.total_used{
    width: 14%;
    text-align: right;
}

.popup .product td.total_submit{
    width: 14%;
    text-align: right;
}

.popup .product td.reference_number{
    width: 20%;
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
                        <td class="custom-bg product_name">สินค้า</td>
                        <td class="custom-bg instock">คงเหลือ</td>
                        <td class="custom-bg total_used">ต้องใช้</td>
                        <td class="custom-bg total_submit">เบิกได้</td>
                        <td class="custom-bg reference_number">ใบขอเบิก</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
        <button type="button" id="btnSubmit" class="btn btn-primary" <?php if($can_make_mr != true) echo "disabled"; ?>><span class="fa fa-check-circle"></span>สร้างใบขอเบิก</button>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    getProducts('<?php echo $doc_id; ?>');
    
    $('#ajaxModal').on('hidden.bs.modal', function (e) {
        parent.updateRow("<?php echo $doc_id; ?>");
    });

    $("#btnSubmit").click(function() {
        axios.post('<?php echo current_url(); ?>', {
            task: 'do_make_material_request',
            sales_order_id: '<?php echo $doc_id; ?>'
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