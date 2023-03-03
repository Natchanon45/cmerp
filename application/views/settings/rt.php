<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "receipt_taxinvoices";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_receipt_taxinvoice_settings"), array("id" => "receipt_taxinvoice-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="panel panel-default">

                <ul data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" class="active" href="javascript:;" data-target="#receipt_taxinvoice-settings-tab"> <?php echo lang('receipt_taxinvoice_settings'); ?></a></li>
                    <!-- <li><a role="presentation" href="<?php //echo_uri("settings/receipt_taxinvoice_request_settings/"); ?>" data-target="#receipt_taxinvoice-request-settings-tab"><?php echo lang('receipt_taxinvoice_request_settings'); ?></a></li> -->
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="receipt_taxinvoice-settings-tab">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="logo" class=" col-md-2"><?php echo lang('receipt_taxinvoice_logo'); ?> (300x100) </label>
                                <div class=" col-md-10">
                                    <div class="pull-left mr15">
                                        <?php
                                        $rt_logo = "rt_logo";
                                        if (!get_setting($rt_logo)) {
                                            $rt_logo = "po_logo";
                                        }
                                        ?>
                                        <img id="rt-logo-preview" src="<?php echo get_file_from_setting($rt_logo); ?>" alt="..." />
                                    </div>
                                    <div class="pull-left file-upload btn btn-default btn-xs">
                                        <i class="fa fa-upload"></i> <?php echo lang("upload_and_crop"); ?>
                                        <input id="rt_logo_file" class="cropbox-upload upload" name="rt_logo_file" type="file" data-height="100" data-width="300" data-preview-container="#rt-logo-preview" data-input-field="#rt_logo" />
                                    </div>
                                    <div class="mt10 ml10 pull-left">
                                        <?php
                                        echo form_upload(array(
                                            "id" => "rt_logo_file_upload",
                                            "name" => "rt_logo_file",
                                            "class" => "no-outline hidden-input-file"
                                        ));
                                        ?>
                                        <label for="rt_logo_file_upload" class="btn btn-default btn-xs">
                                            <i class="fa fa-upload"></i> <?php echo lang("upload"); ?>
                                        </label>
                                    </div>
                                    <input type="hidden" id="rt_logo" name="rt_logo" value=""  />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rt_prefix" class=" col-md-2"><?php echo lang('receipt_taxinvoice_prefix'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "rt_prefix",
                                        "name" => "rt_prefix",
                                        "value" => get_setting("rt_prefix"),
                                        "class" => "form-control",
                                        "placeholder" => strtoupper(lang("rt")) . " #"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rt_color" class=" col-md-2"><?php echo lang('receipt_taxinvoice_color'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "rt_color",
                                        "name" => "rt_color",
                                        "value" => get_setting("rt_color"),
                                        "class" => "form-control",
                                        "placeholder" => "Ex. #e2e2e2"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="send_rt_bcc_to" class=" col-md-2"><?php echo lang('send_rt_bcc_to'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "send_rt_bcc_to",
                                        "name" => "send_rt_bcc_to",
                                        "value" => get_setting("send_rt_bcc_to"),
                                        "class" => "form-control",
                                        "placeholder" => lang("email")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="hidden" id="last_rt_id" name="last_rt_id" value="<?php echo $last_id; ?>" />
                                <label for="initial_number_of_the_rt" class="col-md-2"><?php echo lang('initial_number_of_the_rt'); ?></label>
                                <div class="col-md-3">
                                    <?php
                                    echo form_input(array(
                                        "id" => "initial_number_of_the_rt",
                                        "name" => "initial_number_of_the_rt",
                                        "type" => "number",
                                        "value" => $last_id + 1,
                                        "class" => "form-control mini",
                                        "data-rule-greaterThan" => "#last_rt_id",
                                        "data-msg-greaterThan" => lang("the_rts_id_must_be_larger_then_last_rt_id")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rt_footer" class="col-md-2"><?php echo lang('receipt_taxinvoice_footer') ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "rt_footer",
                                        "name" => "rt_footer",
                                        "value" => get_setting('rt_footer'),
                                        "class" => "form-control"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create_new_projects_automatically_when_rt_gets_accepted" class="col-md-2"><?php echo lang("create_new_projects_automatically_when_rt_gets_accepted"); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_checkbox("create_new_projects_automatically_when_rt_gets_accepted", "1", get_setting("create_new_projects_automatically_when_rt_gets_accepted") ? true : false, "id='create_new_projects_automatically_when_rts_gets_accepted'");
                                    ?> 
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="rt-request-settings-tab"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php $this->load->view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#receipt_taxinvoice-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "rt_logo") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                    if (obj.name === "rt_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#rt_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }

                if ($("#rt_logo").val() || result.reload_page) {
                    location.reload();
                }
            }
        });
        $("#receipt_taxinvoice-settings-form .select2").select2();

        initWYSIWYGEditor("#rt_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>