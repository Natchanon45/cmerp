<style type="text/css">
    .pointer-none {
        pointer-events: none;
    }

    .text-center {
        text-align: center;
    }

    .head {
        margin: 1rem auto;
        padding-top: .2rem;
        width: min(1000px, 90%);
        background-color: #ffffff;
        box-shadow: 7px 7px 7px #aaaaaa;
        border-radius: .2rem;
    }

    .head h4 {
        font-size: 140%;
    }

    .custom-container {
        margin: 1rem auto;
        padding: 1rem 3rem;
        width: min(1000px, 90%);
        background-color: #ffffff;
        box-shadow: 7px 7px 7px #aaaaaa;
        border-radius: .2rem;
    }

    .custom-form-group {
        height: 40px;
    }

    .pnd-type-container {
        display: grid;
        grid-template-columns: auto auto auto auto;
    }

    .pay-type-container {
        display: grid;
        grid-template-columns: auto auto;
    }

    .income-table {
        width: 100%;
        margin: 20px auto;
    }

    .income-table input {
        text-align: center;
    }

    .fixed-border th, .fixed-border td {
        border: 1px solid #cccccc;
    }

    .text-hide {
        color: inherit;
    }

    .border-bottom-hide {
        border-bottom: none !important;
    }

    .table-form-margin {
        margin: 5px 5px;
    }

    .table-form-padding {
        padding: 5px 5px;
    }
</style>

<div class="head">
    <div class="page-title clearfix clear">
        <h4>
            <?php echo lang("withholding_tax"); ?>
        </h4>

        <div class="title-button-group">
            <a class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("withholding_tax"); ?>">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i>
                <?php echo lang("back_to_table"); ?>
            </a>

            <button type="submit" id="btn-submit" class="btn btn-primary">
                <span class="fa fa-check-circle"></span>
                <?php echo lang("save"); ?>
            </button>

            <?php if (true): // if (isset($doc_info["status"]) && !empty($doc_info["status"])): ?>
                <?php if (true): // if ($doc_info["status"] == "0"): ?>
                    <a href="javascript:void(0);" id="btn-approval" class="btn btn-primary">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <?php echo lang("withholding_tax_approve"); ?>
                    </a>
                <?php endif; ?>

                <?php if (true): // if ($doc_info["status"] == "1"): ?>
                    <a href="javascript:void(0);" class="btn btn-warning"> 
                        <i class="fa fa-print" aria-hidden="true"></i>
                        <?php echo lang("withholding_tax_print"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payer -->
<div class="custom-container clearfix">
    <h4 style="margin-bottom: 20px;">
        <?php echo "ผู้มีหน้าที่หักภาษี ณ ที่จ่าย"; ?>
    </h4>

    <?php $company_vat_number = get_setting("company_vat_number"); ?>
    <div class="form-group custom-form-group">
        <label for="payer_tax_number" class="col-md-4">
            <?php echo "เลขประจำตัวผู้เสียภาษีอากร (13 หลัก)"; ?>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payer_tax_number",
                    "class" => "form-control",
                    "maxlength" => "13",
                    "value" => (!empty($company_vat_number) && $company_vat_number != '') ? trim($company_vat_number) : '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <?php $company_name = get_setting("company_name"); ?>
    <div class="form-group custom-form-group">
        <label for="payer_name" class="col-md-4">
            <span>ชื่อ <i>(ให้ระบุว่าเป็น บุคคล นิติบุคคล บริษัท สมาคม หรือคณะบุคคล)</i></span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payer_name",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => (!empty($company_name) && $company_name != '') ? trim($company_name) : '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <?php $company_address = nl2br(get_setting("company_address")); ?>
    <div class="form-group custom-form-group">
        <label for="payer_address" class="col-md-4">
            <span>ที่อยู่ <i>(ให้ระบุ ชื่ออาคาร/หมู่บ้าน ห้องเลขที่ ชั้นที่ เลขที่ ตรอก/ซอย หมู่ที่ ถนน ตำบล/แขวง
                    อำเภอ/เขต จังหวัด)</i></span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payer_address",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => (!empty($company_address) && $company_address != '') ? trim($company_address) : '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>
</div>

<!-- Payee -->
<div class="custom-container clearfix">
    <h4 style="margin-bottom: 20px;">
        <?php echo "ผู้ถูกหักภาษี ณ ที่จ่าย"; ?>
    </h4>

    <div class="form-group custom-form-group">
        <label for="payee_tax_number" class="col-md-4">
            <?php echo "เลขประจำตัวผู้เสียภาษีอากร (13 หลัก)"; ?>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payee_tax_number",
                    "class" => "form-control",
                    "maxlength" => "13",
                    "value" => '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <?php $company_name = get_setting("company_name"); ?>
    <div class="form-group custom-form-group">
        <label for="payee_name" class="col-md-4">
            <span>ชื่อ <i>(ให้ระบุว่าเป็น บุคคล นิติบุคคล บริษัท สมาคม หรือคณะบุคคล)</i></span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payee_name",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <?php $company_address = nl2br(get_setting("company_address")); ?>
    <div class="form-group custom-form-group">
        <label for="payee_address" class="col-md-4">
            <span>ที่อยู่ <i>(ให้ระบุ ชื่ออาคาร/หมู่บ้าน ห้องเลขที่ ชั้นที่ เลขที่ ตรอก/ซอย หมู่ที่ ถนน ตำบล/แขวง อำเภอ/เขต จังหวัด)</i></span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "text",
                    "id" => "payee_address",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => '',
                    "required" => true
                )
            );
            ?>
        </span>
    </div>
</div>

<!-- PND Type -->
<div class="custom-container clearfix">
    <h4>ประเภทแบบ</h4>

    <div class="pnd-type-container">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="1" id="flexRadioDefault1" checked>
            <label class="form-check-label" for="flexRadioDefault1">
                <span>(1) ภ.ง.ด.1ก</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="2" id="flexRadioDefault2">
            <label class="form-check-label" for="flexRadioDefault2">
                <span>(2) ภ.ง.ด.1ก พิเศษ</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="3" id="flexRadioDefault3">
            <label class="form-check-label" for="flexRadioDefault3">
                <span>(3) ภ.ง.ด.2</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="4" id="flexRadioDefault4">
            <label class="form-check-label" for="flexRadioDefault4">
                <span>(4) ภ.ง.ด.3</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="5" id="flexRadioDefault5">
            <label class="form-check-label" for="flexRadioDefault5">
                <span>(5) ภ.ง.ด.2ก</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="6" id="flexRadioDefault6">
            <label class="form-check-label" for="flexRadioDefault6">
                <span>(6) ภ.ง.ด.3ก</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="flexRadioDefault" value="7" id="flexRadioDefault7">
            <label class="form-check-label" for="flexRadioDefault7">
                <span>(7) ภ.ง.ด.53</span>
            </label>
        </div>
    </div>
</div>

<!-- Type of assessable income -->
<div class="custom-container clearfix">
    <table class="income-table fixed-border">
        <thead>
            <tr style="height: 50px;">
                <th class="text-center" style="width: 50%;">
                    <?php echo "ประเภทเงินเดือนพึงประเมินที่จ่าย"; ?>
                </th>
                <th class="text-center">
                    <?php echo "วัน เดือน หรือปีภาษีที่จ่าย"; ?>
                </th>
                <th class="text-center">
                    <?php echo " จำนวนเงินที่จ่าย"; ?>
                </th>
                <th class="text-center">
                    <?php echo "ภาษีที่หักและนำส่งไว้"; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-form-padding">
                    <?php echo "1. เงินเดือน ค่าจ้าง เบี้ยเลี้ยง โบนัส ฯลฯ ตามมาตรา 40 (1)"; ?>
                </td>
                <td class="table-form-padding"><input type="text" name="income_40_1_period" id="income_40_1_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_1_value" id="income_40_1_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_1_tax" id="income_40_1_tax" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding">
                    <?php echo "2. ค่าธรรมเนียม ค่านายหน้า ฯลฯ ตามมาตรา 40 (2)"; ?>
                </td>
                <td class="table-form-padding"><input type="text" name="income_40_2_period" id="income_40_2_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_2_value" id="income_40_2_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_2_tax" id="income_40_2_tax" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding">
                    <?php echo "3. ค่าแห่งลิขสิทธิ์ ฯลฯ ตามมาตรา 40 (3)"; ?>
                </td>
                <td class="table-form-padding"><input type="text" name="income_40_3_period" id="income_40_3_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_3_value" id="income_40_3_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_3_tax" id="income_40_3_tax" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding">4. (ก) ดอกเบี้ย ฯลฯ ตามมาตรา 40 (4) (ก)</td>
                <td class="table-form-padding"><input type="text" name="income_40_4A_period" id="income_40_4A_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4A_value" id="income_40_4A_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4A_tax" id="income_40_4A_tax" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 4); ?>(ข) เงินปันผล ส่วนแบ่งกำไร ฯลฯ ตามมาตรา 40 (4) (ข)</p></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 9); ?>(1) กรณีผู้ได้รับเงินปันผลได้รับเครดิตภาษี โดยจ่ายจาก</p></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>กำไรสุทธิของกิจการที่ต้องเสียภาษีเงินได้นิติบุคคลในอัตราดังนี้</p></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(1.1) อัตราร้อยละ 30 ของกำไรสุทธิ</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_11" id="income_40_4B_period_11" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_11" id="income_40_4B_value_11" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_11" id="income_40_4B_tax_11" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(1.2) อัตราร้อยละ 25 ของกำไรสุทธิ</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_12" id="income_40_4B_period_12" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_12" id="income_40_4B_value_12" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_12" id="income_40_4B_tax_12" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(1.3) อัตราร้อยละ 20 ของกำไรสุทธิ</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_13" id="income_40_4B_period_13" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_13" id="income_40_4B_value_13" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_13" id="income_40_4B_tax_13" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(1.4) อัตราอื่น ๆ (ระบุ) <input type="number" style="width: 50px; display: inline;" name="income_40_4B_percentage_14" id="income_40_4B_percentage_14" min="0" max="100" class="form-control"> ของกำไรสุทธิ</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_14" id="income_40_4B_period_14" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_14" id="income_40_4B_value_14" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_14" id="income_40_4B_tax_14" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 9); ?>(2) กรณีผู้ได้รับเงินปันผลไม่ได้รับเครดิตภาษี เนื่องจากจ่ายจาก</p></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
                <td class="table-form-padding"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(2.1) กำไรสุทธิของกิจการที่ได้รับยกเว้นภาษีเงินได้นิติบุคคล</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_21" id="income_40_4B_period_21" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_21" id="income_40_4B_value_21" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_21" id="income_40_4B_tax_21" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(2.2) เงินปันผลหรือเงินส่วนแบ่งของกำไรที่ได้รับการยกเว้นไม่ต้องนำมารวมคำนวณเป็นรายได้เพื่อเสียภาษีเงินได้นิติบุคคล</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_22" id="income_40_4B_period_22" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_22" id="income_40_4B_value_22" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_22" id="income_40_4B_tax_22" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(2.3) กำไรสุทธิส่วนที่ได้หักผลขาดทุนสุทธิยกมาไม่เกิน 5 ปี ก่อนรอบระยะเวลาบัญชีปีปัจจุบัน</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_23" id="income_40_4B_period_23" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_23" id="income_40_4B_value_23" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_23" id="income_40_4B_tax_23" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(2.4) กำไรที่รับรู้ทางบัญชีโดยวิธีส่วนได้เสีย (equity method)</p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_24" id="income_40_4B_period_24" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_24" id="income_40_4B_value_24" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_24" id="income_40_4B_tax_24" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><p><?php echo str_repeat("&nbsp;", 14); ?>(2.5) อื่น ๆ (ระบุ) <input type="text" style="width: 250px; display: inline;" name="income_40_4B_text_25" id="income_40_4B_text_25" class="form-control"></p></td>
                <td class="table-form-padding"><input type="text" name="income_40_4B_period_25" id="income_40_4B_period_25" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_value_25" id="income_40_4B_value_25" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_4B_tax_25" id="income_40_4B_tax_25" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><?php echo "5. การจ่ายเงินได้ที่ต้องหักภาษี ณ ที่จ่าย ตามคำสั่งกรมสรรพกรที่ออกตามมาตรา 3 เตรส เช่น รางวัล ส่วนลดหรือประโยชน์ใด ๆ เนื่องจากการส่งเสริมการขาย รางวัลในการประกวด การแข่งขัน การชิงโชค ค่าแสดงของนักแสดงสาธารณะ ค่าจ้างทำของ ค่าโฆษณา ค่าเช่า ค่าขนส่ง ค่าบริการ ค่าเบี้ยประกันวินาศภัย ฯลฯ"; ?></td>
                <td class="table-form-padding"><input type="text" name="income_40_8_period" id="income_40_8_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_8_value" id="income_40_8_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_8_tax" id="income_40_8_tax" class="form-control"></td>
            </tr>
            <tr>
                <td class="table-form-padding"><?php echo "6. อื่น ๆ (ระบุ)"; ?> <input type="text" style="width: 250px; display: inline;" name="income_40_other_text" id="income_40_other_text" class="form-control"></td>
                <td class="table-form-padding"><input type="text" name="income_40_other_period" id="income_40_other_period" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_other_value" id="income_40_other_value" class="form-control"></td>
                <td class="table-form-padding"><input type="number" min="0" value="0" name="income_40_other_tax" id="income_40_other_tax" class="form-control"></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="custom-container clearfix">
    <h4 style="margin-bottom: 20px;">
        <?php echo "เงินที่จ่ายเข้า"; ?>
    </h4>

    <div class="form-group custom-form-group">
        <label for="voluntary_value_1" class="col-md-4">
            <?php echo "กบข.กสจ. กองทุนสงเคราะห์ครูโรงเรียนเอกชน"; ?>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "number",
                    "id" => "voluntary_value_1",
                    "class" => "form-control",
                    "maxlength" => "13",
                    "value" => 0,
                    "min" => 0,
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <div class="form-group custom-form-group">
        <label for="voluntary_value_2" class="col-md-4">
            <span>กองทุนประกันสังคม</span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "number",
                    "id" => "voluntary_value_2",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => 0,
                    "min" => 0,
                    "required" => true
                )
            );
            ?>
        </span>
    </div>

    <div class="form-group custom-form-group">
        <label for="voluntary_value_3" class="col-md-4">
            <span>กองทุนสำรองเลี้ยงชีพ</span>
        </label>
        <span class="col-md-8">
            <?php
            echo form_input(
                array(
                    "type" => "number",
                    "id" => "voluntary_value_3",
                    "class" => "form-control",
                    "maxlength" => "256",
                    "value" => 0,
                    "min" => 0,
                    "required" => true
                )
            );
            ?>
        </span>
    </div>
</div>

<div class="custom-container clearfix">
    <h4>ผู้จ่ายเงิน</h4>

    <div class="pay-type-container">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payer_condition" value="1" id="payer_condition1" checked>
            <label class="form-check-label" for="payer_condition1">
                <span>(1) หัก ณ ที่จ่าย</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payer_condition" value="2" id="payer_condition2">
            <label class="form-check-label" for="payer_condition2">
                <span>(2) ออกให้ตลอดไป</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payer_condition" value="3" id="payer_condition3">
            <label class="form-check-label" for="payer_condition3">
                <span>(3) ออกให้ครั้งเดียว</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payer_condition" value="4" id="payer_condition4">
            <label class="form-check-label" for="payer_condition4">
                <span>(4) อื่น ๆ (ระบุ) </span> 
            </label>
            <input style="width: 250px; display: inline;" type="text" name="payer_condition_specify" id="payer_condition_specify" class="form-control pointer-none">
        </div>
    </div>
</div>

<div class="custom-container clearfix modal-footer">
    <button type="submit" id="btn-submit" class="btn btn-primary">
        <span class="fa fa-check-circle"></span>
        <?php echo lang("save"); ?>
    </button>

    <?php if (true): // if (isset($doc_info["status"]) && !empty($doc_info["status"])): ?>
        <?php if (true): // if ($doc_info["status"] == "0"): ?>
            <a href="javascript:void(0);" id="btn-approval" class="btn btn-primary">
                <i class="fa fa-check" aria-hidden="true"></i> 
                <?php echo lang("withholding_tax_approve"); ?>
            </a>
        <?php endif; ?>
        
        <?php if (true): // if ($doc_info["status"] == "1"): ?>
            <a href="javascript:void(0);" class="btn btn-warning">
                <i class="fa fa-print" aria-hidden="true"></i> 
                <?php echo lang("withholding_tax_print"); ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#payer_tax_number").on("click", function (e) {
            e.preventDefault();
            // console.log($(this).val());
        });

        $(`[name="flexRadioDefault"]`).on("change", function (e) {
            e.preventDefault();
            // console.log($(this).val());
        });

        $(`[name="payer_condition"]`).on("change", function (e) {
            e.preventDefault();
            // console.log($(this).val());

            if ($(this).val() == '4') {
                $("#payer_condition_specify").removeClass("pointer-none");
                $("#payer_condition_specify").focus();
            } else {
                $("#payer_condition_specify").addClass("pointer-none");
            }
        });
    });
</script>