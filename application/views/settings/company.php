<style type="text/css">
#company-stamp-preview img{
    height: 100%;
    max-height: 120px;
    width: auto;
}

#company-stamp-empty{
    display: block;
    border: 4px double #ff0000;
    padding: 10px 28px;
    border-radius: 4px;
    color: #ff0000;
    font-weight: bold;transform: rotate(5deg);
}
</style>
<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "company";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_company_settings"), array("id" => "company-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="panel">
                <div class="panel-default panel-heading">
                    <h4><?php echo lang("company_settings"); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="company_name" class=" col-md-2"><?php echo lang('company_name'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_input(array(
                                "id" => "company_name",
                                "name" => "company_name",
                                "value" => get_setting("company_name"),
                                "class" => "form-control",
                                "placeholder" => lang('company_name'),
                                "data-rule-required" => true,
                                "data-msg-required" => lang("field_required")
                            ));
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_vat_registered" class=" col-md-2"><?php echo lang('company_vat_registered'); ?></label>
                        <div class=" col-md-10">
                            <select id="company_vat_registered" name="company_vat_registered" class="form-control">
                                <option value="Y" <?php if(get_setting("company_vat_registered") == "Y") echo 'selected'; ?>><?php echo lang('company_vat_registered_y'); ?></option>
                                <option value="N" <?php if(get_setting("company_vat_registered") == "N") echo 'selected'; ?>><?php echo lang('company_vat_registered_n'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_address" class=" col-md-2"><?php echo lang('address'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_textarea(array(
                                "id" => "company_address",
                                "name" => "company_address",
                                "value" => get_setting("company_address"),
                                "class" => "form-control",
                                "placeholder" => lang('address'),
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="company_phone" class=" col-md-2"><?php echo lang('phone'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_input(array(
                                "id" => "company_phone",
                                "name" => "company_phone",
                                "value" => get_setting("company_phone"),
                                "class" => "form-control",
                                "placeholder" => lang('phone')
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="company_email" class=" col-md-2"><?php echo lang('email'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_input(array(
                                "id" => "company_email",
                                "name" => "company_email",
                                "value" => get_setting("company_email"),
                                "class" => "form-control",
                                "placeholder" => lang('email')
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="company_website" class=" col-md-2"><?php echo lang('website'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_input(array(
                                "id" => "company_website",
                                "name" => "company_website",
                                "value" => get_setting("company_website"),
                                "class" => "form-control",
                                "placeholder" => lang('website')
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="company_vat_number" class=" col-md-2"><?php echo lang('vat_number'); ?></label>
                        <div class=" col-md-10">
                            <?php
                            echo form_input(array(
                                "id" => "company_vat_number",
                                "name" => "company_vat_number",
                                "value" => get_setting("company_vat_number"),
                                "class" => "form-control",
                                "placeholder" => lang('vat_number')
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="company_receipt_type" class=" col-md-2">ประเภทใบเสร็จรับเงิน</label>
                        <div class=" col-md-10">
                            <select id="company_receipt_type" name="company_receipt_type" class="form-control">
                                <option value="1" <?php if(get_setting("company_receipt_type") == "1") echo 'selected'; ?>>ใบเสร็จรับงิน</option>
                                <option value="2" <?php if(get_setting("company_receipt_type") == "2") echo 'selected'; ?>>ใบเสร็จรับงิน / ใบกำกับภาษี</option>
                                <option value="3" <?php if(get_setting("company_receipt_type") == "3") echo 'selected'; ?>>ใบส่งของ / ใบเสร็จรับงิน / ใบกำกับภาษี</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group company-stamp">
                        <label for="company_stamp_file_upload" class=" col-md-2">ตราประทับองค์กร</label>
                        <div class=" col-md-10">
                            <div id="company-stamp-preview" class="pull-left mr15">
                                <?php if($company_setting["company_stamp"] != ""): ?>
                                    <img src="<?php echo get_file_from_setting("company_stamp"); ?>" />
                                <?php else: ?>
                                    <span id="company-stamp-empty">Your Company</span>
                                <?php endif;?>
                            </div>
                            <div class="mt10 ml10 pull-left">
                                <?php
                                echo form_upload(array(
                                    "id" => "company_stamp_file_upload",
                                    "name" => "company_stamp_file",
                                    "class" => "no-outline hidden-input-file"
                                ));
                                ?>
                                <label for="company_stamp_file_upload" class="btn btn-default btn-xs">
                                    <i class="fa fa-upload"></i> <?php echo lang("upload"); ?>
                                </label>
                            </div>
                            <input type="hidden" id="company_stamp" name="company_stamp"  />
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#company-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "company_stamp") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                });
            },
            onSuccess: function (result) {
                if(result.success){
                    appAlert.success(result.message, {duration: 10000});
                }else{
                    appAlert.error(result.message);
                }

                $("#company_stamp").val("");

                if ($("#company_stamp").val() || result.reload_page) {
                    location.reload();
                }
            }
        });
        
        $("#company_stamp_file_upload").change(function () {
            file = this.files ? this.files[0] : "";
            var fileTypes = ["image/jpeg", "image/png", "image/gif"];
            if (file) {
                if (fileTypes.indexOf(file.type) === -1) {
                    appAlert.error("<?php echo lang("invalid_file_type"); ?>");
                    appLoader.hide();
                    return false;
                } else if (file.size / 1024 > 3072) {
                    appAlert.error("<?php echo lang("max_file_size_3mb_message"); ?>");
                    appLoader.hide();
                    return false;
                }
            }

            $("#company-stamp-preview").empty().append("<img src='"+URL.createObjectURL(file)+"'>");
        });
    });
</script>