<style type="text/css">
.modal-dialog{
    width: 480px;
}

td{
    vertical-align: top;
    padding: 8px 2px;
    padding-right: 0px;
}

td:nth-child(1){
    width: 100px;
    padding-top: 13px;
}

td:nth-child(2){
    width: calc(100% - 100px);
}

select, input, textarea{
    width: 100%;
    height: 34px !important;
    padding-left: 8px !important;
}

select{
    padding-left: 5px !important;
}

#pay_date[readonly]{
    background: #fff;
}



td.summary{
    text-align: right;
}

td.summary span{
    display: inline-block;
    padding-right: 12px;
}

td.summary span input{
    width: 130px;
    padding: 0 !important;
    text-align: right !important;
    border: 0;
    margin-right: 8px;
}

td.summary span input:focus{
    outline: none;
}

/*td.summary span#total_payment_receive input{
    font-weight: bold;
}*/

td.summary span#total_payment_receive{
    background: #9d9eb1;
    color: #fff;
    width: 290px;
    padding: 6px 12px;
    border-radius: 4px;
    
}

td.summary span#total_payment_receive input{
    background: none;
    font-size: 1.4em;
    font-weight: bold;
}

#wht_inc{
    width: 14px;
    height: 14px;
}

</style>
<div class="popup">
    <div class="container">
        <table width="100%">
            <tr>
                <td><label>รับเงินโดย</label></td>
                <td>
                    <select name="payment_methods" class="form-control">
                        <?php foreach($payment["methods"] as $method): ?>
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
                <td><label>จำนวน</label></td>
                <td><input type="text" id="payment_amount" name="payment_amount" class="form-control numb"></td>
            </tr>
            <tr>
                <td><label>หมายเหตุ</label></td>
                <td><input type="text" id="remark" class="form-control"></td>
            </tr>
            <tr>
                <td>
                    <label>หัก ณ ที่จ่าย</label>
                    <select>
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
                    <span id="money_payment_receive">รับชำระด้วยเงินรวม<input type="text" value="100.00" readonly>บาท</span>
                    <span id="tax_withheld">ถูกหัก ณ ที่จ่าย<input type="text" value="100.00" readonly>บาท</span>
                    <span id="total_payment_receive">รับชำระรวมทั้งสิ้น<input type="text" value="122,200.00" readonly>บาท</span>
                    <span id="remaining_amount">ต้องรับชำระเงินอีก<input type="text" value="100.00" readonly>บาท</span>
                </td>
            </tr>
        </table>
        <style type="text/css">
            
        </style>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>รับชำระเงิน</button>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    pay_date = $("#pay_date").datepicker({
        yearRange: "<?php echo date('Y'); ?>",
        format: 'dd/mm/yyyy',
        changeMonth: true,
        changeYear: true,
        autoclose: true
    }).on("changeDate", function (e) {
        
    });

    pay_date.datepicker("setDate", "<?php echo date('d/m/Y', time()); ?>");

    $("#payment_amount").blur(function(){
        payment_amount = tonum($(this).val());
        $(this).val($.number(payment_amount, 2));
    });
});

/*function calculatePrice(){
    if(quantity < 0 ) quantity = 0;
    if(price < 0 ) price = 0;
}*/
</script>