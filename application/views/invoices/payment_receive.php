<style type="text/css">
.modal-dialog{
    width: 520px;
}

.popup td{
    vertical-align: top;
    padding: 8px 2px;
    padding-right: 0px;
}

.popup td:nth-child(1){
    width: 100px;
    padding-top: 13px;
}

.popup td:nth-child(2){
    width: calc(100% - 100px);
}

.popup select, .popup input, .popup textarea{
    width: 100%;
    height: 34px !important;
    padding-left: 8px !important;
}

.popup select{
    padding-left: 5px !important;
}

.popup #pay_date[readonly]{
    background: #fff;
}

.popup td.summary{
    text-align: right;
}

.popup td.summary span{
    display: inline-block;
    padding-right: 12px;
}

.popup td.summary span input{
    width: 140px;
    padding: 0 !important;
    text-align: right !important;
    border: 0;
    margin-right: 8px;
}

.popup td.summary span input:focus{
    outline: none;
}

.popup td.summary input#total_payment_receive{
    background: none;
    font-size: 1.4em;
    font-weight: bold;
}

.popup #wht_inc{
    width: 14px;
    height: 14px;
}

</style>
<div class="popup">
    <div class="container">
        <p>มูลค่าลูกหนี้ที่สามารถรับชำระได้ทั้งสิ้น <?php echo number_format($doc["net_receivable_await_payment_amount"], 2); ?> บาท</p>
        <table width="100%">
            <tr>
                <td><label>รับเงินโดย</label></td>
                <td>
                    <select name="payment_methods" class="form-control">
                        <option value="-1">ไม่ระบุช่องทาง</option>
                        <?php foreach($payment_methods as $method): ?>
                            <option value="<?php echo $method->id?>"><?php echo $method->title; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label>วันที่</label></td>
                <td><input type="text" id="pay_date" class="form-control" autocomplete="off" readonly></td>
            </tr>
            <tr>
                <td><label>จำนวนเงิน</label></td>
                <td><input type="text" id="payment_amount" class="form-control numb"></td>
            </tr>
            <tr>
                <td><label>หมายเหตุ</label></td>
                <td><input type="text" id="remark" class="form-control"></td>
            </tr>
            <tr>
                <td>
                    <label>หัก ณ ที่จ่าย</label>
                    <select id="withholding_tax_percent">
                        <option value="0">ไม่มี</option>
                        <option value="3">3%</option>
                        <option value="5">5%</option>
                        <option value="0.5">0.5%</option>
                        <option value="0.75">0.75%</option>
                        <option value="1">1%</option>
                        <option value="1.5">1.5%</option>
                        <option value="2">2%</option>
                        <option value="10">10%</option>
                        <option value="15">15%</option>
                    </select>
                </td>
                <td colspan="4" style="text-align: right;" class="summary">
                    <span>รับชำระด้วยเงินรวม<input type="text" id="money_payment_receive" readonly>บาท</span>
                    <span>ถูกหัก ณ ที่จ่าย<input type="text" id="withholding_tax_value" readonly>บาท</span>
                    <span style="background:#4cc681; color:#fff; padding:6px 12px; border-radius: 4px; font-size: 1.1em;">รับชำระรวมทั้งสิ้น<input type="text" id="total_payment_receive" readonly>บาท</span>
                    <span>ต้องรับชำระเงินอีก<input type="text" id="remaining_amount" readonly>บาท</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>รับชำระเงิน</button>
    </div>
</div>

<script type="text/javascript">

var net_receivable_await_payment_amount = tonum(<?php echo $doc["net_receivable_await_payment_amount"]; ?>);//มูลค่าลูกหนี้ที่สามารถรับชำระได้ทั้งสิ้น
var payment_amount = net_receivable_await_payment_amount;//ชำระจำนวน
var money_payment_receive = payment_amount;//รับชำระด้วยเงินรวม
var withholding_tax_value = 0;//หัก ณ ที่จ่าย
var remaining_amount = net_receivable_await_payment_amount - payment_amount;//ต้องรับชำระเงินอีก

$(document).ready(function() {
    $("#payment_amount").val($.number(payment_amount, 2));
    $("#money_payment_receive").val($.number(money_payment_receive, 2));
    $("#withholding_tax_percent").val("0");
    $("#withholding_tax_value").val($.number(withholding_tax_value, 2));
    $("#total_payment_receive").val($.number(payment_amount, 2));
    $("#remaining_amount").val($.number(remaining_amount, 2));

    pay_date = $("#pay_date").datepicker({
        yearRange: "<?php echo date('Y'); ?>",
        format: 'dd/mm/yyyy',
        changeMonth: true,
        changeYear: true,
        autoclose: true
    }).on("changeDate", function (e) {
        
    });

    pay_date.datepicker("setDate", "<?php echo date('d/m/Y', time()); ?>");

    $("#withholding_tax_percent").on("change", function() { 
        calculatePayment();
    });

    $("#payment_amount, #money_payment_receive, #total_payment_receive, #remaining_amount").blur(function(){
        calculatePayment();
    });

    $("#btnSubmit").click(function() {
        axios.post('<?php echo current_url(); ?>', {
            task: 'add_payment',
            doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
            doc_date:$("#doc_date").val()
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
        }).catch(function (error) {});
    });
});

function calculatePayment(){
    payment_amount = tonum($("#payment_amount").val());
    withholding_tax_percent = tonum($("#withholding_tax_percent").val());
    withholding_tax_value = (withholding_tax_percent * payment_amount)/100;
    money_payment_receive = payment_amount - withholding_tax_value;
    remaining_amount = net_receivable_await_payment_amount - payment_amount;
    
    $("#payment_amount").val($.number(payment_amount, 2));
    $("#money_payment_receive").val($.number(money_payment_receive, 2));
    $("#withholding_tax_value").val($.number(withholding_tax_value, 2));
    $("#total_payment_receive").val($.number(payment_amount, 2));
    $("#remaining_amount").val($.number(remaining_amount, 2));
}
</script>