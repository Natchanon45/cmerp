<?php load_css(array("assets/css/kpage.css")); ?>
<?php load_css(array("assets/css/kpage-quotation.css")); ?>

<div id="kpage">
    <div class="buttons"></div>
    <div class="page clear">
        <div class="head clear">
            <div class="l">
                <div class="logo"><img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" /></div>
                <div class="from">
                    <?php
                        $company_address = nl2br(get_setting("company_address"));
                        $company_phone = get_setting("company_phone");
                        $company_website = get_setting("company_website");
                        $company_vat_number = get_setting("company_vat_number");
                    ?>
                    <p class="company_name"><?php echo get_setting("company_name"); ?></p>
                    <p><?php echo nl2br(get_setting("company_address")); ?></p>
                    <p><?php echo lang("phone") . ": ".get_setting("company_phone"); ?></p>
                    <p><?php echo lang("website").": ".get_setting("company_website"); ?></p>
                    <p><?php echo lang("vat_number").": ".get_setting("company_vat_number"); ?></p>
                </div><!-- .from -->
            </div><!--.l-->
            <div class="r">
                <div class="document_name">
                    <p><span class="docno"><?php echo get_setting("estimate_prefix").$estimate_info->doc_no; ?></span></p>
                    <p><?php echo lang("estimate_date") . ": " . format_to_date($estimate_info->estimate_date, false); ?></p>
                    <p><?php echo lang("valid_until") . ": " . format_to_date($estimate_info->valid_until, false); ?></p>
                </div>
                <div class="to">
                    <p class="clabel"><?php echo lang("client"); ?></p>
                    <p class="customer_name"><?php echo $client_info->company_name; ?></p>
                    <p><?php echo nl2br($client_info->address); ?></p>
                    <p><?php echo $client_info->city.", ".$client_info->state." ".$client_info->zip; ?></p>
                    <p><?php echo $client_info->country; ?></p>
                    <p><?php echo lang("vat_number") . ": " . $client_info->vat_number; ?></p>
                </div>
            </div><!--.r-->
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
                    <tr>
                        <td>4</td>
                        <td>
                            <p class="description1">ค่าซองอลูมิเนียมฟอยล์</p>
                            <p class="description2">Spec. Oppmatt12/ALU7/LLDPE60 หนารวม 80 ไมครอน +-5ค่ะ (1 ม้วน ใช้ได้ 4,000-4,100 ซอง)ม้วนซอง หน้ากว้าง 80 mm ยาว 500 m ระยะตัด 12 cm (ระยะเวลาผลิตประมาณ 45 วัน ทำการหลังจากอนุมัติแบบผลิต)</p>
                        </td>
                        <td>30 ม้วน</td>
                        <td>2,600.00</td>
                        <td>78,000.00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="5">&nbsp;</td></tr>
                    <tr>
                        <td colspan="3" rowspan="5" class="inchar">&nbsp;</td>
                        <td class="l">ราคาก่อนหักส่วนลด</td>
                        <td class="r">139,500.00</td>
                    </tr>
                    <tr>
                        <td class="l">หัก ส่วนลด</td>
                        <td class="r">3,000.00</td>
                    </tr>
                    <tr>
                        <td class="l">ราคาหลังส่วนลด</td>
                        <td class="r">136,500.00</td>
                    </tr>
                    <tr>
                        <td class="l">ภาษีมูลค่าเพิ่ม 7%</td>
                        <td class="r">9,555.00</td>
                    </tr>
                    <tr>
                        <td class="l">จำนวนเงินรวมทั้งสิ้น</td>
                        <td class="r">146,055.00</td>
                    </tr>
                </tfoot>
            </table>
        </div><!--.body-->
        <div class="remark clear">
            <p><?php echo nl2br($estimate_info->note); ?></p>
        </div><!--.remark-->
        <div class="foot clear">

        </div><!--.foot-->
    </div><!--.page-->
</div><!--#kprint--> 
