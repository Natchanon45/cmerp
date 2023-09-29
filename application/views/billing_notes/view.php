<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
<style>
#printd .docitem td:nth-child(1){
    width: 40px;
    text-align: center;
}

#printd .docitem td:nth-child(2){
    width: calc(25% - 40px);
}

#printd .docitem td:nth-child(3){
    width: 15%;
    text-align: left;
}

#printd .docitem td:nth-child(4){
    width: 15%;
    text-align: left;
}

#printd .docitem td:nth-child(5){
    width: 15%;
}

#printd .docitem td:nth-child(6){
    width: 15%;
}

#printd .docitem td:nth-child(7){
    width: 15%;
    text-align: right;
}

#printd .summary .c2{
    padding-right: 8px;
}
</style>
<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15">
        <h1>ใบวางบิล <?php echo $doc_number;?></h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn"  href="<?php echo get_uri("accounting/sell/billing-notes")?>" ><i class="fa fa-hand-o-left" aria-hidden="true"></i> ย้อนกลับไปตารางรายการ</a>
            <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="แชร์เอกสาร <?php echo $doc_number; ?>" data-action-url="<?php echo get_uri("/billing-notes/share"); ?>">แชร์</a>
            <a onclick="window.open('<?php echo $print_url;?>', '' ,'width=980,height=720');" class="btn btn-default">พิมพ์</a>
        </div>
    </div>
</div><!--#dcontroller-->
<div id="printd" class="clear">
    <div class="docheader clear">
        <div class="l">
            <div class="logo">
                <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].get_file_from_setting("estimate_logo", true)) != false): ?>
                    <img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" />
                <?php else: ?>
                    <span class="nologo">&nbsp;</span>
                <?php endif; ?>
            </div>
            <div class="company">
                <p class="company_name"><?php echo get_setting("company_name"); ?></p>
                <p><?php echo nl2br(get_setting("company_address")); ?></p>
                <?php if(trim(get_setting("company_phone")) != ""): ?>
                    <p><?php echo lang("phone") . ": ".get_setting("company_phone"); ?></p>
                <?php endif;?>
                <?php if(trim(get_setting("company_website")) != ""): ?>
                    <p><?php echo lang("website") . ": ".get_setting("company_website"); ?></p>
                <?php endif;?>
                <?php if(trim(get_setting("company_vat_number")) != ""): ?>
                    <p><?php echo lang("vat_number") . ": ".get_setting("company_vat_number"); ?></p>
                <?php endif;?>
            </div><!-- .company -->
            <div class="customer">
                <p class="custom-color"><?php echo lang("client"); ?></p>
                <?php if($client != null): ?>
                    <p class="customer_name"><?php echo $client["company_name"] ?></p>
                    <p><?php if($client != null) echo nl2br($client["address"]); ?></p>
                    <p>
                        <?php
                            $client_address = $client["city"];
                            if($client_address != "" && $client["state"] != "")$client_address .= ", ".$client["state"];
                            elseif($client_address == "" && $client["state"] != "")$client_address .= $client["state"];
                            if($client_address != "" && $client["zip"] != "") $client_address .= " ".$client["zip"];
                            elseif($client_address == "" && $client["zip"] != "") $client_address .= $client["zip"];
                            echo $client_address;
                        ?>    
                    </p>
                    <?php if(trim($client["country"]) != ""): ?>
                        <p><?php echo $client["country"]; ?></p>
                    <?php endif; ?>
                    <?php if(trim($client["vat_number"]) != ""): ?>
                        <p><?php echo lang("vat_number") . ": " . $client["vat_number"]; ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->
        <div class="r">
            <h1 class="document_name custom-color">ใบวางบิล<!--<span class="note custom-color">ต้นฉบับ (เอกสารออกเป็นชุด)</span>--></h1>
            <div class="about_company">
                <table>
                    <tr>
                        <td class="custom-color">เลขที่</td>
                        <td><?php echo $doc_number; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">วันที่</td>
                        <td><?php echo convertDate($doc_date, true); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">ครบกำหนด</td>
                        <td><?php echo convertDate($due_date, true); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">ผู้ขาย</td>
                        <td><?php if($created != null) echo $created["first_name"]." ".$created["last_name"]; ?></td>
                    </tr>
                    <?php if(trim($reference_number) != ""): ?>
                        <tr>
                            <td class="custom-color">เลขที่อ้างอิง</td>
                            <td><?php echo $reference_number; ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="about_customer">
                <table>
                    <tr>
                        <td class="custom-color">ผู้ติดต่อ</td>
                        <td><?php if(isset($client_contact)) echo $client_contact["first_name"]." ".$client_contact["last_name"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">เบอร์โทร</td>
                        <td><?php if(isset($client_contact)) echo $client_contact["phone"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">อีเมล์</td>
                        <td><?php if(isset($client_contact)) echo $client_contact["email"]; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div><!--.docheader-->
    <div class="docitem">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>เลขที่เอกสาร</td>
                    <td>วันที่ออก</td>
                    <td>วันที่ครบกำหนด</td>
                    <td>มูลค่าสุทธิรวม</td>
                    <td>จำนวนเงินวางบิล</td>
                    <td>หัก ณ ที่จ่าย</td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3">
                        <p><input type="text" id="total_in_text" readonly></p>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-sub-total">
                            <span class="c1"><i class="custom-color t1">รวมเป็นเงิน</i></span>
                            <span class="c2"><input type="text" id="sub_total" value="<?php echo $sub_total; ?>" readonly></span>
                            <!--<span class="c3"><span class="currency">บาท</span></span>-->
                        </p>
                        <?php if($billing_type == "1" || $billing_type == "2" || $billing_type == "3"): ?>
                            <p id="s-vat">
                                <span class="c1 custom-color">ภาษีมูลค่าเพิ่ม <?php echo $vat_percent."%"; ?></span>
                                <span class="c2"><input type="text" id="vat_value" value="<?php echo number_format($vat_value, 2); ?>" readonly></span>
                                <!--<span class="c3"><span class="currency">บาท</span></span>-->
                            </p>
                        <?php endif; ?>
                        <p id="s-total">
                            <span class="c1 custom-color">มูลค่าสุทธิรวม</span>
                            <span class="c2"><input type="text" id="total" value="<?php echo number_format($total, 2); ?>" readonly ></span>
                            <!--<span class="c3"><span class="currency">บาท</span></span>-->
                        </p>
                        <?php if($wht_value > 0): ?>
                            <p id="s-wht">
                                <span class="c1 custom-color">จำนวนเงินที่ถูกหัก ณ ที่จ่าย</span>
                                <span class="c2 wht"><input type="text" id="wht_value" value="<?php echo number_format($wht_value, 2); ?>" readonly ></span>
                                <!--<span class="c3 wht"><span class="currency">บาท</span></span>-->
                            </p>
                            <p id="s-payment-amount">
                                <span class="c1 custom-color wht">จำนวนเงินที่จะต้องชำระ</span>
                                <span class="c2 wht"><input type="text" id="payment_amount" value="<?php echo number_format($payment_amount, 2); ?>" readonly></span>
                                <!--<span class="c3 wht"><span class="currency">บาท</span></span>-->
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if(trim($remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color">หมายเหตุ</p>
                <p><?php echo nl2br($remark); ?></p>
            </div><!--.remark-->
        <?php endif; ?>
    </div><!--.docitem-->
    <div class="docsignature clear">
        <div class="customer">
            <div class="company_stamp"></div>
            <div class="on_behalf_of">ในนาม <?php echo $client["company_name"] ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1"></span>
                    <span class="l2">ผู้รับสินค้า / บริการ</span>
                </div>
                <div class="date">
                    <span class="l1"></span>
                    <span class="l2">วันที่</span>
                </div>
            </div>
        </div><!--.customer -->
        <div class="company">
            <div class="on_behalf_of">ในนาม <?php echo get_setting("company_name"); ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if($approved_by != null): ?>
                                <?php if(null != $signature = $this->Users_m->getSignature($approved_by)): ?>
                                    <img src='<?php echo "/".$signature; ?>'>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">ผู้อนุมัติ</span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if($approved_by != null): ?>
                            <span class="approved_date"><?php echo convertDate($approved_datetime, true); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="l2">วันที่</span>
                </div>
            </div>
        </div><!--.company-->
    </div><!--.docsignature-->
</div><!--#printd-->
<script type="text/javascript">
window.addEventListener('keydown', function(event) {
    if (event.keyCode === 80 && (event.ctrlKey || event.metaKey) && !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
        event.preventDefault();
        if (event.stopImmediatePropagation)event.stopImmediatePropagation();
        else event.stopPropagation();
        return;
    }
}, true);

$(document).ready(function() {
    loadItems();
});

function loadItems(){
    axios.post('<?php echo current_url(); ?>', {
        task: 'load_items',
        doc_id: '<?php echo $doc_id; ?>'
    }).then(function (response) {
        data = response.data;
        if(data.status == "notfound"){
            $(".docitem tbody").empty().append("<tr><td colspan='7' class='notfound'>"+data.message+"</td></tr>");
        }else if(data.status == "success"){
            tbody = "";
            items = data.items;

            for(let i = 0; i < items.length; i++){
                tbody += "<tr>"; 
                    tbody += "<td>"+(i+1)+"</td>";
                    tbody += "<td>"+items[i]["invoice_number"]+"</td>";
                    tbody += "<td>"+items[i]["invoice_date"]+"</td>"; 
                    tbody += "<td>"+items[i]["invoice_due_date"]+"</td>"; 
                    tbody += "<td>"+items[i]["net_total"]+"</td>";
                    tbody += "<td>"+items[i]["billing_amount"]+"</td>";
                    tbody += "<td>"+items[i]["wht_value"]+"</td>";
                tbody += "</tr>";
            }

            $(".docitem tbody").empty().append(tbody);
            $(".edititem .delete").click(function() {
                deleteItem($(this).data("item_id"));
            });
        }

        //loadSummary();

    }).catch(function (error) {
        console.log(error);
    });
}

/*function loadSummary(){
    let data = {
                    task: "update_doc",
                    doc_id: "<?php echo $doc_id; ?>"
                };

    axios.post('<?php echo current_url(); ?>', data).then(function(response) {
        let data = response.data;

        $("#sub_total").val(data.sub_total);
        $("#vat_value").val(data.vat_value);

        $("#wht_value").val(data.wht_value);

        $("#total").val(data.total);
        $("#total_in_text").val("("+data.total_in_text+")");

        $("#payment_amount").val(data.payment_amount);


    }).catch(function (error) {
        alert(error);
    });
}*/

function deleteItem(item_id){
    axios.post('<?php echo current_url(); ?>', {
        task: 'delete_item',
        doc_id: '<?php echo $doc_id; ?>',
        item_id: item_id
    }).then(function (response) {
        loadItems();
    });
}
</script>