<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
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
                        <?php if($payment["doc_status"] == "P"): ?>
                            <td colspan="2" class="invoice_is_fully_paid">
                                <span><i class="fa fa-check-circle" aria-hidden="true"></i>รับชำระเต็มจำนวนแล้ว</span>
                                <span><strong>รับชำระครบเมื่อวันที่</strong> : <?php echo convertDate($payment["fully_paid_datetime"], true); ?></span>
                            </td>
                        <?php else: ?>
                            <td><strong>มูลค่าที่รอรับชำระอีก</strong></td>
                            <td>
                                <span class="payment_receive_amount"><strong><?php echo number_format($payment["net_await_payment_receive_amount"], 2); ?></strong> บาท</span>
                                <span class="due_date"><strong>วันที่ครบกำหนด</strong> : <?php echo convertDate($payment["due_date"], true); ?></span>
                                <span><a class="receive_button custom-color-button" data-post-invoice_id="<?php echo $payment["doc_id"]; ?>" data-act="ajax-modal" data-title="รับชำระเงิน #<?php echo $payment["doc_number"]; ?>" data-action-url="<?php echo get_uri("/invoices/payment-receive"); ?>">รับชำระ</a></span>
                            </td>
                        <?php endif; ?>
                    </tr>
                </table>
            </div>
        </div><!--#info-->
    </div><!--#payment_header-->
    <?php if(count($payment["payment_records"]) > 0): ?>
        <?php foreach($payment["payment_records"] as $payment_record): ?>
            <div class="payment_record clear">
                <div class="payment_receive_method">
                    <table>
                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <td colspan="2" class="clear">
                                <span style="float:left; width:50%;"><strong style="color: #4bc17a">รับชำระเงินครั้งที่ <?php echo $payment_record["record_number"]; ?></strong></span>
                                <span style="float:right; width:50%; text-align:right;"><strong>รับชำระเมื่อวันที่</strong> : <?php echo convertDate($payment_record["payment_date"], true); ?></span>
                            </td>
                        </tr>
                        <tr><td colspan="2"><strong>ช่องทางการรับชำระเงิน :</strong></td></tr>
                        <tr>
                            <td style="width:72%;"><a><?php echo $this->Payments_m->getPaymentMethodName($payment_record["payment_method_id"]); ?></a></td>
                            <td style="width:28%;"><?php echo number_format($payment_record["money_payment_receive"], 2); ?> บาท</td>
                        </tr>
                        <?php if($payment_record["wht_inc"] == "Y"): ?>
                            <tr>
                                <td>ภาษีหัก ณ ที่จ่าย</td>
                                <td><?php echo number_format($payment_record["wht_value"], 2); ?> บาท</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="total_payment_amount">
                    <table>
                        <tr>
                            <td colspan="2">
                                <?php if($payment_record["issued_receipt"] != "Y"): ?>
                                    <a class="void_payment" title="ยกเลิกการชำระเงิน" data-payment_id="<?php echo $payment_record["payment_id"]; ?>"><i class="fa fa-minus-square-o" aria-hidden="true"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>มูลค่าที่รับชำระรวม :</strong></td>
                            <td style="line-height: 38px;">
                                <span class="total_pay">
                                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                                    <strong><?php echo number_format($payment_record["payment_amount"], 2); ?></strong> บาท
                                </span>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height:30px"></td></tr>
                        <tr>
                            <td class="clear">
                                <?php if($payment_record["issued_receipt"] == "Y"): ?>
                                    <span class="green">ออกใบเสร็จแล้ว</span>
                                <?php else: ?>
                                    <span class="orange">ยังไม่ออกใบเสร็จ</span>
                                <?php endif;?>
                            </td>
                            <td>
                                <?php if($payment_record["issued_receipt"] == "Y"): ?>
                                    <span class="receipt">เลขที่ใบเสร็จรับเงิน : <a href="<?php echo get_uri("receipts/view/".$payment_record["receipt_id"]); ?>"><?php echo $payment_record["receipt_number"]; ?></a></span>
                                <?php else: ?>
                                    <a class="issue_receipt_button custom-color-button" data-payment_id="<?php echo $payment_record["payment_id"]; ?>">ออกใบเสร็จ</a>
                                <?php endif;?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div><!--#dcontroller-->
<script type="text/javascript">
$(document).ready(function() {
    $(".issue_receipt_button").click(function() {
        axios.post('<?php echo current_url(); ?>', {
            task: 'receipt',
            invoice_id: "<?php echo $payment["doc_id"]; ?>",
            payment_id: $(this).data("payment_id")
        }).then(function (response) {
            data = response.data;
            if(data.status == "success"){
                location.reload();
            }else{
                alert(data.message);
            }
        }).catch(function (error) {});
    });

    $(".void_payment").click(function() {
        if(!confirm("ยืนยันยกเลิกการชำระเงิน")) return false;
        axios.post('<?php echo current_url(); ?>', {
            task: 'void',
            invoice_id: "<?php echo $payment["doc_id"]; ?>",
            payment_id: $(this).data("payment_id")
        }).then(function (response) {
            data = response.data;
            if(data.status == "success"){
                location.reload();
            }else{
                alert(data.message);
            }
        }).catch(function (error) {});
    });
});
</script>