<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">
<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1><?php echo $purpose == "P"?lang("account_docname_work_order"):lang("account_docname_sales_order");?> <?php echo $doc_number;?></h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("accounting/sell/sales-orders");?>" ><i class="fa fa-hand-o-left" aria-hidden="true"></i> <?php echo lang("account_button_back"); ?></a>
            <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="<?php echo lang("account_button_share"); ?> <?php echo $doc_number; ?>" data-action-url="<?php echo get_uri("/sales-orders/share"); ?>"><?php echo lang("account_button_share"); ?></a>
            <a onclick="window.open('<?php echo $print_url;?>', '' ,'width=980,height=720');" class="btn btn-default"><?php echo lang("account_button_print"); ?></a>
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
            <h1 class="document_name custom-color"><?php echo $purpose == "P"?lang("account_docname_work_order"):lang("account_docname_sales_order");?></h1>
            <div class="about_company">
                <table>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_short_document_no"); ?></td>
                        <td><?php echo $doc_number; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_date"); ?></td>
                        <td><?php echo convertDate($doc_date, true); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_seller"); ?></td>
                        <td><?php if($created != null) echo $created["first_name"]." ".$created["last_name"]; ?></td>
                    </tr>
                    <?php if(trim($reference_number) != ""): ?>
                        <tr>
                            <td class="custom-color"><?php echo lang("account_refernce_no"); ?></td>
                            <td><?php echo $reference_number; ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="about_customer">
                
                <table>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_contact"); ?></td>
                        <td><?php if(isset($client_contact)) echo $client_contact["first_name"]." ".$client_contact["last_name"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_phone"); ?></td>
                        <td><?php if(isset($client_contact)) echo $client_contact["phone"]; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("account_email"); ?></td>
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
                    <td><?php echo lang("account_item_description"); ?></td>
                    <td><?php echo lang("account_item_quantity"); ?></td>
                    <td><?php echo lang("account_item_unit"); ?></td>
                    <td><?php echo lang("account_item_unit_price"); ?></td>
                    <td><?php echo lang("account_item_total"); ?></td>
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3">
                        <?php if($doc_status == "W"): ?>
                            <p><?php echo modal_anchor(get_uri("sales-orders/item"), "<i class='fa fa-plus-circle'></i> " .lang('add_item_product'), array("id"=>"add_item_button", "class" => "btn btn-default", "title" => lang('add_item_product'), "data-post-doc_id" => $doc_id)); ?></p>
                        <?php endif; ?>
                        <p><input type="text" id="total_in_text" readonly></p>
                    </td>
                    <td colspan="4" class="summary"></td>
                </tr>
            </tfoot>
        </table>
        <?php if(trim($remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color"><?php echo lang("account_remarks"); ?></p>
                <p><?php echo nl2br($remark); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->
    <div class="docsignature clear">
        <div class="customer">
            <div class="company_stamp"></div>
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if($created_by != null): ?>
                                <?php if(null != $signature = $this->Users_m->getSignature($created_by)): ?>
                                    <img src='<?php echo "/".$signature; ?>'>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">(<?php echo $created["first_name"]." ".$created["last_name"]; ?>)</span>
                    <span class="l3"><?php echo lang("account_created_by"); ?></span>
                </div>
                <div class="date">
                    <span class="l1"><span class="created_date"><?php echo convertDate($created_datetime, true); ?></span></span>
                    <span class="l2"><?php echo lang("account_date"); ?></span>
                </div>
            </div>
        </div><!--.customer -->
        <div class="company">
            <div class="company_stamp"></div>
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if(null != $signature = $this->Users_m->getSignature($approved_by)): ?>
                                <img src='<?php echo "/".$signature; ?>'>
                            <?php endif; ?>
                        </span>
                    </span>
                    <?php if($approved != null): ?>
                        <span class="l2">
                            (<?php echo $approved["first_name"]." ".$approved["last_name"]; ?>)
                        </span>
                    <?php endif;?>
                    <span class="l3"><?php echo lang("account_approved_by"); ?></span>
                </div>
                <div class="date">
                    <span class="l1"><span class="approved_date"><?php echo convertDate($approved_datetime, true); ?></span></span>
                    <span class="l2"><?php echo lang("account_date"); ?></span>
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
                    tbody += "<td>";
                        tbody += "<p class='desc1'>"+items[i]["product_name"]+"</p>";
                        tbody += "<p class='desc2'>"+items[i]["product_description"]+"</p>";
                        tbody += "<p class='desc3'>"+items[i]["product_formula_name"]+"</p>";
                    tbody += "</td>";
                    tbody += "<td>"+items[i]["quantity"]+"</td>"; 
                    tbody += "<td>"+items[i]["unit"]+"</td>"; 
                    tbody += "<td>"+items[i]["price"]+"</td>";
                    tbody += "<td>"+items[i]["total_price"]+"</td>";
                    tbody += "<td class='edititem'>";
                        if(data.doc_status == "W"){
                            tbody += "<a class='edit' data-post-doc_id='<?php echo $doc_id; ?>' data-post-item_id='"+items[i]["id"]+"' data-act='ajax-modal' data-action-url='<?php echo_uri("sales-orders/item"); ?>' ><i class='fa fa-pencil'></i></a>";
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
    }).catch(function (error) {
        console.log(error);
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
