<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
<link rel="stylesheet" href="/assets/css/printd-quotation.css?t=<?php echo time();?>">

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15">
        <h1><?php echo get_estimate_id(' ' . $esrow->doc_no); ?></h1>
        <div class="title-button-group">
            <?php echo $proveButton ?>
            <a class="btn btn-default" onclick="window.print();">พิมพ์</a>
        </div>
    </div>
    <?php echo $this->dao->getDocLabels($esrow->id, $estimate_status_label); ?>
</div><!--#dcontroller-->
<div id="printd" class="clear">
    <div class="docheader clear">
        <div class="l">
            <div class="logo"><img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" /></div>
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
                            $client_address2 = $client["city"];
                            if($client_address2 != "" && $client["state"] != "")$client_address2 .= ", ".$client["city"];
                            elseif($client_address2 == "" && $$client["state"] != "")$client_address2 .= $client["city"];
                            if($client_address2 != "" && $client["zip"] != "") $client_address2 .= " ".$client["zip"];
                            elseif($client_address2 == "" && $client["zip"] != "") $client_address2 .= $client["zip"];
                            echo $client_address2;
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
            <h1 class="document_name custom-color">ใบเสนอราคา</h1>
            <div class="about_company">
                <table>
                    <tr>
                        <td class="custom-color">เลขที่</td>
                        <td><?php echo $esrow->doc_no; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">เลขที่อ้างอิง</td>
                        <td><?php echo $esrow->reference_number; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">วันที่</td>
                        <td><?php echo format_to_date($esrow->estimate_date, false); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">ผู้ขาย</td>
                        <td><?php if($created != null) echo $created["first_name"]." ".$created["last_name"]; ?></td>
                    </tr>
                </table>
            </div>
            <div class="about_customer">
                <table>
                    <tr>
                        <td class="custom-color">ผู้ติดต่อ</td>
                        <td><?php if($client_contact != null) echo $client_contact["first_name"]." ".$client_contact["last_name"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">เบอร์โทร</td>
                        <td><?php if($client_contact != null) echo $client_contact["phone"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color">อีเมล์</td>
                        <td><?php if($client_contact != null) echo $client_contact["email"]; ?></td>
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
                    <td>ราคาต่อหน่วย</td>
                    <td>ยอดรวม</td>
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="6">&nbsp;</td></tr>
                <tr>
                    <td colspan="2">
                        <p><?php echo modal_anchor(get_uri("estimates/item_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item_product'), array("id"=>"add_item_button", "class" => "btn btn-default", "title" => lang('add_item_product'), "data-post-estimate_id" => $esrow->id, "data-post-doc_id" => $esrow->id)); ?></p>
                        <p><input type="text" id="total_in_text" readonly></p>
                    </td>
                    <td colspan="4" class="summary">
                        <p>
                            <span class="c1 custom-color">รวมเป็นเงิน</span>
                            <span class="c2"><input type="text" id="sub_total_before_discount" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p>
                            <span class="c1 custom-color">ส่วนลด<input id="discount_percent">%</span>
                            <span class="c2"><input type="text" id="discount_amount" readonly></span>
                            <span class="c3">
                                <span class="edit_discount"><a><i class='fa fa-pencil'></i></a></span>
                                <span class="currency">บาท</span>
                            </span>
                        </p>
                        <p>
                            <span class="c1 custom-color">ราคาหลังหักส่วนลด</span>
                            <span class="c2"><input type="text" id="sub_total" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p>
                            <span class="c1 custom-color"><input type="checkbox" id="has_vat">ภาษีมูลค่าเพิ่ม <?php echo $this->Taxes_m->getVatPercent(); ?>%</span>
                            <span class="c2"><input type="text" id="vat" readonly></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p class="total">
                            <span class="c1 custom-color">จำนวนเงินรวมทั้งสิ้น</span>
                            <span class="c2"><input type="text" id="total" readonly ></span>
                            <span class="c3"><span class="currency">บาท</span></span>
                        </p>
                        <p class="withholding_tax">
                            <span>
                                <span class="c1 custom-color"><input type="checkbox" id="has_withholding_tax">หักภาษี ณ ที่จ่าย</span>
                                <span class="c2"><input type="text" id="withholding_tax" readonly ></span>
                                <span class="c3"><span class="currency">บาท</span></span>
                            </span>
                            <span class="payment_amount">
                                <span class="c1 custom-color">ยอดชำระ</span>
                                <span class="c2"><input type="text" id="payment_amount" readonly></span>
                                <span class="c3"><span class="currency">บาท</span></span>
                            </span>
                        </p>
                    </td>
                </tr>
            </tfoot>
        </table>
        <div class="remark clear">
            <p class="custom-color">หมายเหตุ</p>
            <p><?php echo nl2br($esrow->note); ?></p>
        </div><!--.remark-->
    </div><!--.docitem-->
    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of">ในนาม <?php echo $client["company_name"] ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1"></span>
                    <span class="l2">ผู้สั่งซื้อสินค้า</span>
                </div>
                <div class="date">
                    <span class="l1"></span>
                    <span class="l2">วันที่</span>
                </div>
            </div>
        </div><!--.customer -->
        <div class="company">
            <?php $user_signature = $this->Db_model->userSignature(1, "estimates"); ?>
            <div class="on_behalf_of">ในนาม <?php echo get_setting("company_name"); ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1"><span class="signature"><img src='<?php echo base_url().$user_signature->signature ?>'></span></span>
                    <span class="l2">ผู้อนุมัติ</span>
                </div>
                <div class="date">
                    <span class="l1"></span>
                    <span class="l2">วันที่</span>
                </div>
            </div>
        </div><!--.company-->
    </div>
</div><!--#printd--> 

<script type="text/javascript">
$(document).ready(function() {
    loadItems();
    $("#discount_percent").blur(function(){
        loadDoc();
    });
});

function loadItems(){
    axios.get('<?php echo_uri("estimates/load_items") ?>', {
        params: {
            doc_id: '<?php echo $esrow->id; ?>'
        }
    }).then(function (response) {
        data = response.data;
        if(data.status == "notfound"){
            $(".docitem tbody").empty().append("<tr><td colspan='6' class='notfound'>"+data.message+"</td></tr>");
        }else if(data.status == "success"){
            tbody = "";
            items = data.items;

            for(let i = 0; i < items.length; i++){
                tbody += "<tr>"; 
                    tbody += "<td>"+(i+1)+"</td>";
                    tbody += "<td>";
                        tbody += "<p class='desc1'>"+items[i]["title"]+"</p>";
                        tbody += "<p class='desc2'>"+items[i]["description"]+"</p>";
                    tbody += "</td>";
                    tbody += "<td>"+items[i]["quantity"]+"</td>"; 
                    tbody += "<td>"+items[i]["rate"]+"</td>";
                    tbody += "<td>"+items[i]["price"]+"</td>";
                    tbody += "<td class='edititem'>";
                        tbody += "<a class='edit' data-post-id='"+items[i]["id"]+"' data-post-doc_id='<?php echo $esrow->id; ?>' data-post-item_id='"+items[i]["id"]+"' data-act='ajax-modal' data-action-url='<?php echo_uri("estimates/item_modal_form"); ?>' ><i class='fa fa-pencil'></i></a>";
                        tbody += "<a class='delete' data-id='"+items[i]["id"]+"'><i class='fa fa-times fa-fw'></i></a>";
                    tbody += "</td>";

                   
                tbody += "</tr>"; 
            }

            $(".docitem tbody").empty().append(tbody);
            loadDoc();

            $(".edititem .delete").click(function() {
                deleteItem($(this).data("id"));
            });
        }
    }).catch(function (error) {
        console.log(error);
    });
}

function loadDoc(){
    axios.post('<?php echo_uri("estimates/load_doc") ?>', {
        doc_id: '<?php echo $esrow->id; ?>',
        discount_percent: $("#discount_percent").val()
    },{
        headers: {
            'Content-Type': 'multipart/form-data'
        },
    }).then(function(response) { 
        //tonum
        data = response.data;

        $("#sub_total_before_discount").val(data.sub_total_before_discount);
        $("#discount_percent").val(data.discount_percent);
        $("#discount_amount").val(data.sub_total);
        $("#sub_total").val(data.sub_total);
        $("#total").val(data.total);
        $("#total_in_text").val(data.total_in_text);
    }).catch(function (error) {
        alert(error);
    });
}

function deleteItem(item_id){
    axios.get('<?php echo_uri("estimates/delete_item") ?>', {
        params: {
            doc_id: '<?php echo $esrow->id; ?>',
            item_id: item_id
        }
    }).then(function (response) {
        loadItems();
    });
}


</script>