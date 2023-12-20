<style type="text/css">
    .text-center {
        text-align: center;
    }

    .head {
        margin: 1rem auto;
        padding: 1rem 1rem;
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

    .income-table {
        width: 100%;
        margin: 20px auto;
    }
</style>

<div class="head">
    <h4>
        <?php echo lang("withholding_tax"); ?>
    </h4>
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
    <table class="income-table">
        <thead>
            <tr>
                <th>
                    <?php echo "ประเภทเงินเดือนพึงประเมินที่จ่าย"; ?>
                </th>
                <th>
                    <?php echo "วัน เดือน หรือปีภาษีที่จ่าย"; ?>
                </th>
                <th>
                    <?php echo " จำนวนเงินที่จ่าย"; ?>
                </th>
                <th>
                    <?php echo "ภาษีที่หักและนำส่งไว้"; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?php echo "เงินเดือน ค่าจ้าง เบี้ยเลี้ยง โบนัส ฯลฯ ตามมาตรา 40 (1)"; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    const pndType = document.querySelectorAll(`[name="flexRadioDefault"]`);

    pndType.forEach(i => {
        i.addEventListener("change", (e) => {
            e.preventDefault();
            console.log(i.value);
        });
    });

    $(document).ready(function () {
        $("#payer_tax_number").on("click", function (e) {
            e.preventDefault();

            let self = $(this);
            console.log(self.val());
        });
    });
</script>