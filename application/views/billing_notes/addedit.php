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
    width: 14%;
}

.popup .invoice td:nth-child(2){
    width: 11%;
}

.popup .invoice td:nth-child(3){
    width: 11%;
    text-align: left;
}

.popup .invoice td:nth-child(4){
    width: 14%;
    text-align: right;
}

.popup .invoice td:nth-child(5){
    width: 14%;
    text-align: right;
}

.popup .invoice td:nth-child(6){
    width: 14%;
    text-align: right;
}

.popup .invoice td:nth-child(7){
    width: 14%;
    text-align: right;
}

.popup .invoice td:nth-child(8){
    width: 8%;
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
                        <td class="custom-bg">เลขที่เอกสาร</td>
                        <td class="custom-bg">วันที่ออก</td>
                        <td class="custom-bg">วันที่ครบกำหนด</td>
                        <td class="custom-bg">มูลค่าสุทธิ</td>
                        <td class="custom-bg">มูลค่าที่ต้องชำระ</td>
                        <td class="custom-bg">ภาษีหัก ณ ที่จ่าย</td>
                        <td class="custom-bg">จำนวนเงินวางบิล</td>
                        <td class="custom-bg"></td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>สร้างใบวางบิล</button>
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
    $("#btnSubmit").unbind('click');

    axios.post('<?php echo current_url(); ?>', {
        task: 'get_invs',
        customer_id: customer_id
    }).then(function (response) {
        let data = response.data;
        $(".invoice table tbody").empty().append(data.html);

        $("#btnSubmit").click(function() {
            ids = $("[name='invoice_numbers[]']");
            invoice_ids = [];
            for(i = 0; i < ids.length; i++){
                if(ids[i].checked == true){
                    invoice_ids.push(ids[i].value);
                }
            }

            axios.post('<?php echo current_url(); ?>', {
                task: 'create_doc',
                invoice_ids: JSON.stringify(invoice_ids)
            }).then(function (response) {
                data = response.data;

                alert(data.message);
                //alert(data.test);
                /*let data = response.data;
                if(data.status == "success"){
                    location.href = data.url;
                }else{
                    alert(data.message);
                }*/
            }).catch(function (error) {});


        });
    }).catch(function (error) {});
}
</script>