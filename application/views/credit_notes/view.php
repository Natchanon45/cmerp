<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15">
        <h1>ใบลดหนี้ <?php echo $doc_number;?></h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn"  href="<?php echo get_uri("accounting/sell/credit-notes")?>" ><i class="fa fa-hand-o-left" aria-hidden="true"></i> ย้อนกลับไปตารางรายการ</a>
            <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="แชร์เอกสาร <?php echo $doc_number; ?>" data-action-url="<?php echo get_uri("/credit-notes/share"); ?>">แชร์</a>
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
                        <p><?php //echo $client["country"]; ?></p>
                    <?php endif; ?>
                    <?php if(trim($client["vat_number"]) != ""): ?>
                        <p><?php echo lang("vat_number") . ": " . $client["vat_number"]; ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->
        <div class="r">
            <h1 class="document_name custom-color">ใบลดหนี้<!--<span class="note custom-color">ต้นฉบับ (เอกสารออกเป็นชุด)</span>--></h1>
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
                        <td class="custom-color">เครดิต (วัน)</td>
                        <td><?php echo $credit; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">ครบกำหนด</td>
                        <td><?php echo convertDate($due_date, true); ?></td>
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
                    <td>รายละเอียด</td>
                    <td>จำนวน</td>
                    <td>หน่วย</td>
                    <td>ราคาต่อหน่วย</td>
                    <td>ยอดรวม</td>
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3">
                        <?php if($doc_status == "W"): ?>
                            <p><?php echo modal_anchor(get_uri("credit-notes/item"), "<i class='fa fa-plus-circle'></i> " . lang('add_item_product'), array("id"=>"add_item_button", "class" => "btn btn-default", "title" => lang('add_item_product'), "data-post-doc_id" => $doc_id)); ?></p>
                        <?php endif; ?>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-original-amount">
                            <span class="c1 custom-color">มูลค่าตามเอกสารเดิม</span>
                            <span class="c2"><input type="text" id="original_amount" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-corrected-amount">
                            <span class="c1 custom-color">มูลค่าที่ถูกต้อง</span>
                            <span class="c2"><input type="text" id="corrected_amount" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-difference">
                            <span class="c1 custom-color">ผลต่าง</span>
                            <span class="c2"><input type="text" id="difference" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-sub-total-before-discount">
                            <span class="c1 custom-color">รวมเป็นเงิน</span>
                            <span class="c2"><input type="text" id="sub_total_before_discount" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-discount">
                            <span class="c1 custom-color">
                                ส่วนลด&nbsp;<input type="number" id="discount_percent" value="<?php echo $discount_percent; ?>" <?php if($doc_status != "W") echo "disabled"; ?>>
                                <select id="discount_type" <?php if($doc_status != "W") echo "disabled"; ?>>
                                    <option value="P" <?php if($discount_type == "P") echo "selected";?>>%</option>
                                    <option value="F" <?php if($discount_type == "F") echo "selected";?>>฿</option>
                                </select>
                            </span>
                            <span class="c2"><input type="text" id="discount_amount" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-sub-total">
                            <span class="c1"><i class="custom-color t1">ราคาหลังหักส่วนลด</i><i class="custom-color t2">รวมเป็นเงิน</i></span>
                            <span class="c2"><input type="text" id="sub_total" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-vat">
                            <span class="c1 custom-color"><input type="checkbox" id="vat_inc" <?php if($vat_inc == "Y") echo "checked" ?> <?php if($doc_status != "W") echo "disabled"; ?>>ภาษีมูลค่าเพิ่ม <?php echo $this->Taxes_m->getVatPercent()."%"; ?></span>
                            <span class="c2"><input type="text" id="vat_value" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-total">
                            <span class="c1 custom-color">จำนวนเงินรวมทั้งสิ้น</span>
                            <span class="c2"><input type="text" id="total" readonly ></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-wht">
                            <span class="c1 custom-color">
                                <input type="checkbox" id="wht_inc" <?php if($wht_inc == "Y") echo "checked" ?> <?php if($doc_status != "W") echo "disabled"; ?>>หักภาษี ณ ที่จ่าย
                                <select id="wht_percent" class="wht custom-color <?php echo $wht_inc == "Y"?"v":"h"; ?>" <?php if($doc_status != "W") echo "disabled"; ?>>
                                    <option value="3">3%</option>
                                    <option value="5">5%</option>
                                    <option value="0.50">0.5%</option>
                                    <option value="0.75">0.75%</option>
                                    <option value="1">1%</option>
                                    <option value="1.50">1.5%</option>
                                    <option value="2">2%</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                </select>
                            </span>
                            <span class="c2 wht <?php echo $wht_inc == "Y"?"v":"h"; ?>"><input type="text" id="wht_value" readonly ></span>
                            <span class="c3 wht <?php echo $wht_inc == "Y"?"v":"h"; ?>"><span class="currency">บาท</span></span>
                        </p>
                        <p id="s-payment-amount">
                            <span class="c1 custom-color wht <?php echo $wht_inc == "Y"?"v":"h"; ?>">ยอดชำระ</span>
                            <span class="c2 wht <?php echo $wht_inc == "Y"?"v":"h"; ?>"><input type="text" id="payment_amount" readonly></span>
                            <span class="c3 wht <?php echo $wht_inc == "Y"?"v":"h"; ?>"><span class="currency">บาท</span></span>
                        </p>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if(trim($remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color">หมายเหตุ</p>
                <p><?php echo nl2br($remark); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->
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
    $("#discount_percent, #discount_amount").blur(function(){
        loadSummary();
    });

    $("#discount_type, #vat_inc, #wht_inc, #wht_percent").change(function() {
        loadSummary();
    });
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
                    tbody += "<td>";
                        tbody += "<p class='desc1'>"+items[i]["product_name"]+"</p>";
                        tbody += "<p class='desc2'>"+items[i]["product_description"]+"</p>";
                    tbody += "</td>";
                    tbody += "<td>"+items[i]["quantity"]+"</td>"; 
                    tbody += "<td>"+items[i]["unit"]+"</td>"; 
                    tbody += "<td>"+items[i]["price"]+"</td>";
                    tbody += "<td>"+items[i]["total_price"]+"</td>";
                    tbody += "<td class='edititem'>";
                        if(data.doc_status == "W"){
                            tbody += "<a class='edit' data-post-doc_id='<?php echo $doc_id; ?>' data-post-item_id='"+items[i]["id"]+"' data-act='ajax-modal' data-action-url='<?php echo_uri("credit-notes/item"); ?>' ><i class='fa fa-pencil'></i></a>";
                            tbody += "<a class='delete' data-item_id='"+items[i]["id"]+"'><i class='fa fa-times fa-fw'></i></a>";
                        }
                    tbody += "</td>";
                tbody += "</tr>";
            }

            $(".docitem tbody").empty().append(tbody);
            $(".edititem .delete").click(function() {
                deleteItem($(this).data("item_id"));
            });
        }

        loadSummary();

    }).catch(function (error) {
        console.log(error);
    });
}

function loadSummary(){
    let discount_type = $("#discount_type").val();
    let discount_percent = 0;
    let discount_value = 0;

    if(discount_type == "P") discount_percent = tonum($("#discount_percent").val());
    else discount_value = tonum($("#discount_amount").val());

    axios.post('<?php echo current_url(); ?>', {
        task: 'update_doc',
        doc_id: '<?php echo $doc_id; ?>',
        discount_type: discount_type,
        discount_percent: discount_percent,
        discount_value: discount_value,
        vat_inc: $("#vat_inc").is(":checked"),
        wht_inc: $("#wht_inc").is(":checked"),
        wht_percent: $("#wht_percent").val()
    }).then(function(response) {
        data = response.data;

        $("#original_amount").val(data.original_amount);
        $("#corrected_amount").val(data.corrected_amount);
        $("#difference").val(data.difference);

        $("#sub_total_before_discount").val(data.sub_total_before_discount);
        $("#discount_percent").val(data.discount_percent);
        $("#discount_amount").val(data.discount_amount);
        $("#sub_total").val(data.sub_total);

        $("#wht_percent").val(data.wht_percent);
        $("#wht_value").val(data.wht_value);

        $("#total").val(data.total);
        $("#total_in_text").val("("+data.total_in_text+")");

        $("#payment_amount").val(data.payment_amount);

         if(data.discount_type == "P"){
            $("#discount_type").val("P");
            $("#discount_percent").removeClass("h").addClass("v");
            $("#discount_amount").removeClass("f").addClass("p");
            $("#discount_amount").prop("readonly", true);
            discount_value = $("#discount_percent").val();
        }else{
            $("#discount_type").val("F");
            $("#discount_percent").removeClass("v").addClass("h");
            $("#discount_amount").removeClass("p").addClass("f");
            $("#discount_amount").prop("readonly", false);
            discount_value = $("#discount_amount").val();
        }

        if(discount_value > 0){
            $("#s-sub-total-before-discount, #s-discount").removeClass("h").addClass("v");
            $("#s-sub-total .t1").removeClass("h").addClass("v");
            $("#s-sub-total .t2").removeClass("v").addClass("h");
            
        }else{
            $("#s-sub-total-before-discount, #s-discount").removeClass("v").addClass("h");
            $("#s-sub-total .t1").removeClass("v").addClass("h");
            $("#s-sub-total .t2").removeClass("h").addClass("v");
        }
        
        if(data.vat_inc == "Y"){
            $("#vat_inc").prop("checked", true);
            $("#vat_value").val(data.vat_value);
            $("#s-vat .vat_percent").removeClass("h").addClass("v");
            $("#s-vat .vat_percent_zero").removeClass("v").addClass("h");
            
        }else{
            $("#vat_inc").prop("checked", false);
            $("#vat_value").val(data.vat_value);
            $("#s-vat .vat_percent").removeClass("v").addClass("h");
            $("#s-vat .vat_percent_zero").removeClass("h").addClass("v");
        }

        if(data.wht_inc == "Y"){
            $("#wht_inc").prop("checked", true);
            $("#s-wht").removeClass("h").addClass("v");
            $(".wht").removeClass("h").addClass("v");
        }else{
            $("#wht_inc").prop("checked", false);
            $("#s-wht").removeClass("v").addClass("h");
            $(".wht").removeClass("v").addClass("h");
        }

    }).catch(function (error) {
        alert(error);
    });
}

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