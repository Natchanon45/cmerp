<?php load_css(array("assets/css/printd.css")); ?>
<?php load_css(array("assets/css/printd-quotation.css")); ?>

<div id="docheader" class="clearfix">
    <div class="page-title clearfix mt15">
        <h1><?php echo get_estimate_id(' ' . $estimate_info->doc_no); ?></h1>
        <div class="title-button-group">
            <?php echo $proveButton ?>
            <a class="btn btn-default" onclick="window.print();">พิมพ์</a>
        </div>
    </div>
    <?php echo $this->dao->getDocLabels($estimate_info->id, $estimate_status_label); ?>
</div><!--#docheader-->
<div id="printd" class="clear">
    <div class="page clear">
        <div class="head clear">
            <div class="l">
                <div class="logo"><img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" /></div>
                <div class="company">
                    <p><?php echo get_setting("company_name"); ?></p>
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
                    <p><?php echo $client_info->company_name; ?></p>
                    <p><?php echo nl2br($client_info->address); ?></p>
                        <?php
                            $client_address2 = $client_info->city;
                            if($client_address2 != "" && $client_info->state != "")$client_address2 .= ", ".$client_info->city;
                            elseif($client_address2 == "" && $client_info->state != "")$client_address2 .= $client_info->city;
                            if($client_address2 != "" && $client_info->zip != "") $client_address2 .= " ".$client_info->zip;
                            elseif($client_address2 == "" && $client_info->zip != "") $client_address2 .= $client_info->zip;
                            echo $client_address2;
                        ?>    
                    </p>
                    <?php if(trim($client_info->country) != ""): ?>
                        <p><?php echo $client_info->country; ?></p>
                    <?php endif; ?>
                    <?php if(trim($client_info->vat_number) != ""): ?>
                        <p><?php echo lang("vat_number") . ": " . $client_info->vat_number; ?></p>
                    <?php endif; ?>
                </div><!-- .company -->
            </div><!--.l-->
            <div class="r">
                <h1 class="document_name custom-color">ใบเสนอราคา</h1>
                <div class="about_company">
                    <table>
                        <tr>
                            <td class="custom-color">เลขที่</td>
                            <td><?php echo $estimate_info->doc_no; ?></td>
                        </tr>
                        <tr>
                            <td class="custom-color">วันที่</td>
                            <td><?php echo format_to_date($estimate_info->estimate_date, false); ?></td>
                        </tr>
                        <tr>
                            <td class="custom-color">ผู้ขาย</td>
                            <td><?php echo $users_info->first_name; ?><?php echo $users_info->last_name != ""?" ".$users_info->last_name:""; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="about_customer">
                    <table>
                        <tr>
                            <td class="custom-color">ผู้ติดต่อ</td>
                            <td>
                                <?php
                                    if($client_contact != null){
                                        echo $client_contact->first_name; ?><?php echo $client_contact->last_name != ""?" ".$client_contact->last_name:"";
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="custom-color">เบอร์โทร</td>
                            <td>
                                <?php
                                    if($client_contact != null){
                                        echo $client_contact->phone;
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="custom-color">อีเมล์</td>
                            <td>
                                <?php
                                    if($client_contact != null){
                                        echo $client_contact->email;
                                    }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div><!--.head-->
        <div class="body clear">
            <table>
                <thead>
                    <tr>
                        <td>#</td>
                        <td>รายละเอียด</td>
                        <td>จำนวน</td>
                        <td>ราคาต่อหน่วย</td>
                        <td>ยอดรวม</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <p class="description1">ค่าซองอลูมิเนียมฟอยล์</p>
                            <p class="description2">Spec. Oppmatt12/ALU7/LLDPE60 หนารวม 80 ไมครอน +-5ค่ะ (1 ม้วน ใช้ได้ 4,000-4,100 ซอง)ม้วนซอง หน้ากว้าง 80 mm ยาว 500 m ระยะตัด 12 cm (ระยะเวลาผลิตประมาณ 45 วัน ทำการหลังจากอนุมัติแบบผลิต)</p>
                        </td>
                        <td>30 ม้วน</td>
                        <td>2,600.00</td>
                        <td>78,000.00</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <p class="description1">ค่าซองอลูมิเนียมฟอยล์</p>
                            <p class="description2">Spec. Oppmatt12/ALU7/LLDPE60 หนารวม 80 ไมครอน +-5ค่ะ (1 ม้วน ใช้ได้ 4,000-4,100 ซอง)ม้วนซอง หน้ากว้าง 80 mm ยาว 500 m ระยะตัด 12 cm (ระยะเวลาผลิตประมาณ 45 วัน ทำการหลังจากอนุมัติแบบผลิต)</p>
                        </td>
                        <td>30 ม้วน</td>
                        <td>2,600.00 </td>
                        <td>78,000.00</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <p class="description1">ค่าซองอลูมิเนียมฟอยล์</p>
                            <p class="description2">Spec. Oppmatt12/ALU7/LLDPE60 หนารวม 80 ไมครอน +-5ค่ะ (1 ม้วน ใช้ได้ 4,000-4,100 ซอง)ม้วนซอง หน้ากว้าง 80 mm ยาว 500 m ระยะตัด 12 cm (ระยะเวลาผลิตประมาณ 45 วัน ทำการหลังจากอนุมัติแบบผลิต)</p>
                        </td>
                        <td>30 ม้วน</td>
                        <td>2,600.00 </td>
                        <td>78,000.00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="5"><?php echo modal_anchor(get_uri("estimates/item_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item_product'), array("id"=>"add_item", "class" => "btn btn-default", "title" => lang('add_item_product'), "data-post-estimate_id" => $estimate_info->id)); ?></td></tr>
                    <tr>
                        <td rowspan="5" class="inchar">&nbsp;</td>
                        <td colspan="3" class="l custom-color">ราคาก่อนหักส่วนลด</td>
                        <td class="r">139,500.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="l custom-color">หัก ส่วนลด</td>
                        <td class="r">3,000.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="l custom-color">ราคาหลังส่วนลด</td>
                        <td class="r">136,500.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="l custom-color">ภาษีมูลค่าเพิ่ม 7%</td>
                        <td class="r">9,555.00</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="l custom-color">จำนวนเงินรวมทั้งสิ้น</td>
                        <td class="r">146,055.00</td>
                    </tr>
                </tfoot>
            </table>
        </div><!--.body-->
        <div class="remark clear">
            <p class="custom-color">หมายเหตุ</p>
            <p><?php echo nl2br($estimate_info->note); ?></p>
        </div><!--.remark-->
    </div><!--.page-->
</div><!--#printd--> 