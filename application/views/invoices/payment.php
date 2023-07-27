<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
<style type="text/css">
#payment_header{
    background: #fff;
    padding: 14px 18px;
    padding-bottom: 0;
    margin-top: 14px;
    margin-bottom: 14px;
    box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
}

#payment_header .docnumber{
    float: left;
    width: 50%;
    font-size: 1.4em;
}

#payment_header .docnumber h2{
    font-size: 0.98em;
}

#payment_header .docnumber .docstaus{
    display: block;
    font-size: 0.8em;
}

#payment_header .docnumber .docstaus i{
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-right: 8px;
    border-radius: 50%;
    position: relative;
    top: 1px;
}

#payment_header .docnumber .docstaus.outstanding i{
    background: #3297ff;
}

#payment_header .docnumber .docstaus.paid i{
    background: #44bf76;
}

#payment_header .docnumber .docstaus.outstanding{
    color: #3297ff;
}

#payment_header .docnumber .docstaus.paid{
    color: #44bf76;
}

#payment_header .buttons{
    float: right;
    width: 50%;
    text-align: right;
}

#payment_header .title{
    margin-bottom: 12px;
}

#payment_header table{
    width: 100%;
}

#payment_header td{
    vertical-align: top;
    line-height: 38px;
}

#payment_header td:nth-child(1){
    text-align: left;
    width: 50%;
}

#payment_header td:nth-child(2){
    text-align: right;
    width: 50%;
}

#payment_header .info{
    border-top: 1px solid #ccc;
}

#payment_header .info .l{
    float: left;
    width: 50%;
    border-right: 1px solid #ccc;
    padding: 12px;
    padding-left: 0;
}

#payment_header .info .r{
    float: right;
    width: 50%;
    padding: 12px;
    padding-right: 0;
}

#payment_header .payment_receive_amount{
    border-radius: 8px;
    position: relative;
    background: #ececff;
    color: #6c6df2;
    display: block;
    width: 100%;
    text-align: right;
    font-size: 17px;
    padding: 8px;
    padding-bottom: 6px;
}

#payment_header .payment_receive_amount strong{
    font-size: 1.2em;
}

#payment_header .info .r span{
    display: block;
}
</style>
<div id="dcontroller" class="clearfix">
    <div id="payment_header">
        <div class="title clear">
            <div class="docnumber">
                <h2>ข้อมูลการชำระใบแจ้งหนี้ #<?php echo $payment["doc_number"];?></h2>
                <?php if($payment["doc_status"] == "O"): ?>
                    <span class="docstaus outstanding"><i></i>รอรับชำระ</span>
                <?php endif;?>
                <?php if($payment["doc_status"] == "P"): ?>
                    <span class="docstaus paid"><i></i>รับชำระแล้ว</span>
                <?php endif;?>
            </div>
            <div class="buttons"><a class="btn btn-default back-to-index-btn" href="<?php echo get_uri("invoices/view/".$payment["doc_id"])?>"><i class="fa fa-hand-o-left" aria-hidden="true"></i> ข้อมูลเอกสาร</a></div>
        </div>
        <div class="info clear">
            <div class="l">
                <table>
                    <tr>
                        <td><strong>มูลค่าเต็มใบแจ้งหนี้ :</strong></td>
                        <td><?php echo number_format($payment["invoice_full_payment_amount"], 2); ?> บาท</td>
                    </tr>
                    <tr>
                        <td><strong>มูลค่าสุทธิที่ต้องรับชำระทั้งสิ้น :</strong></td>
                        <td><?php echo number_format($payment["total_net_amount_to_receive_payment"], 2); ?> บาท</td>
                    </tr>
                    <tr>
                        <td><strong>มูลค่าที่รับชำระแล้ว <?php echo count($payment["payment_records"]); ?> รายการ :</strong></td>
                        <td><?php echo number_format($payment["total_paid"], 2); ?> บาท</td>
                    </tr>
                </table>
            </div>
            <div class="r">
                <table>
                    <tr>
                        <td><strong>มูลค่าที่รอรับชำระอีก</strong></td>
                        <td>
                            <span class="payment_receive_amount"><strong><?php echo number_format($payment["net_await_payment_receive_amount"], 2); ?></strong> บาท</span>
                            <span class="due_date"><strong>วันที่ครบกำหนด</strong> : <?php echo convertDate($payment["due_date"], true); ?></span>
                            <span><a class="receive_button custom-color-button" data-post-doc_id="<?php echo $payment["doc_id"]; ?>" data-act="ajax-modal" data-title="รับชำระเงิน #<?php echo $payment["doc_number"]; ?>" data-action-url="<?php echo get_uri("/invoices/payment-receive"); ?>">รับชำระ</a></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div><!--#info-->
    </div><!--#payment_header-->

    <?php if(count($payment["payment_records"]) > 0): ?>
        <?php foreach($payment["payment_records"] as $pr): ?>
            <div class="payment_record clear">
                <div class="payment_receive_method">
                    <table>
                        <tr>
                            <td><strong>รับชำระเงินครั้งที่ 1</strong></td>
                            <td><strong>รับชำระเมื่อวันที่</strong> : 19/07/2023</td>
                        </tr>
                        <tr><td colspan="2"><strong>ช่องทางการรับชำระเงิน :</strong></td></tr>
                        <tr>
                            <td colspan="2">อื่นๆ ออมทรัพย์ - 99999999999999 ธนาคาร ทดสอบบัญชี</td>
                        </tr>
                    </table>
                </div>
                <div class="total_payment_amount">
                    <table>
                        <tr>
                            <td><strong>มูลค่าที่รับชำระรวม :</strong></td>
                            <td><span class="total_pay"><i class="fa fa-check-circle" aria-hidden="true"></i><strong>40,332.00</strong> บาท</span></td>
                        </tr>
                        <tr>
                            <td class="issued_receipt">
                                <span class="green">ออกใบเสร็จแล้ว</span>
                                <!--<span class="orange">ยังไม่ออกใบเสร็จ</span>-->
                            </td>
                            <td class="issued_receipt">
                                <span class="receipt">เลขที่ใบเสร็จรับเงิน : RE-20230700002</span>
                                <!--<a class="issue_receipt_button custom-color-button">ออกใบเสร็จ</a>-->
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div><!--#dcontroller-->
</script>