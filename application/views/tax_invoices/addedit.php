<style type="text/css">
.modal-dialog {
    width: 100%;
    max-width: 980px;
}

.popup .customer{
    margin-bottom: 14px;
}
   
.popup .customer label{
    display: block;
    float: left;
    width: 100px;
    padding-top: 6px;
}

.popup .customer select{
    display: block;
    float: right;
    width: calc(100% - 100px);
}

.popup .invoice {
    height: 280px;
    border: 1px solid #ccc;
    overflow: auto;
}

.popup .invoice table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.popup .invoice table tr.norecord td{
    text-align: center;
    vertical-align: middle;
    padding-top: 100px;
}

.popup .invoice table tr.norecord:hover{
    background: #fff;
}

.popup .invoice td{
    line-break: anywhere;
    vertical-align: top;
    padding: 6px 3px;
    text-align: left;
}

.popup .invoice td:nth-child(1){
    padding-left: 8px;
    width: 15%;
}

.popup .invoice td:nth-child(2){
    width: 18%;
}

.popup .invoice td:nth-child(3){
    width: 25%;
}

.popup .invoice td:nth-child(4){
    width: 15%;
    text-align: right;
}

.popup .invoice td:nth-child(5){
    width: 15%;
    text-align: center;
}

.popup .invoice td:nth-child(6){
    width: 12%;
    text-align: center;
}

.popup .invoice thead tr td {
    position: sticky;
    top: 0;
    font-weight: 500;
    border-bottom: 1px solid #e8e8e8;
    text-align: left;
    color: #fff;
}

.popup .invoice tbody tr {
    border-bottom: 1px solid #e8e8e8;
}

.popup .invoice tbody tr:hover {
    background: #e6f7ff;
}

.popup .invoice tbody tr:last-child{
    border-bottom: 0;
}

.popup .invoice .choose-inv-button{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
}

.popup .invoice .choose-inv-button:hover{
    background: #fff !important;
}

.popup .invoice .choose-inv-button:active{
    position: relative;
    top: 1px;
}
</style>
<div class="popup">
    <div class="container">
        <div class="customer clear">
            <label>ชื่อลูกค้า / คู่ค้า :</label>
            <select id="customer_id" class="form-control">
                <option value="">-</option>
                <?php foreach($cusrows as $cusrow): ?>
                    <option value="<?php echo $cusrow->id; ?>"><?php echo $cusrow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="invoice">
            <table>
                <thead>
                    <tr>
                        <td class="custom-bg">วันที่</td>
                        <td class="custom-bg">เลขที่เอกสาร</td>
                        <td class="custom-bg">ชื่อลูกค้า</td>
                        <td class="custom-bg">จำนวนเงิน</td>
                        <td class="custom-bg">สถานะ</td>
                        <td class="custom-bg"></td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    getInvs(null);
    $("#customer_id").select2().on("change", function (e) {
        getInvs($(this).select2("val"));
    });
});

function getInvs(customer_id){
    axios.post('<?php echo current_url(); ?>', {
        task: 'get_invs',
        customer_id: customer_id
    }).then(function (response) {
        let data = response.data;
        $(".invoice table tbody").empty().append(data.html);

        $("a.choose-inv-button").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'create_doc',
                invoice_id: $(this).data("invoice_id")
            }).then(function (response) {
                let data = response.data;
                
                if(data.status == "success"){
                    location.href = data.url;
                }else{
                    alert(data.message);
                }
            }).catch(function (error) {});
        });
    }).catch(function (error) {});
}
</script>