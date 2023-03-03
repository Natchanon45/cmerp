<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "purchaserequest";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_pr_settings"), array("id" => "purchaserequest-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="panel panel-default">

                <ul data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" class="active" href="javascript:;" data-target="#purchaserequest-settings-tab"> <?php echo lang('purchaserequest_settings'); ?></a></li>
                    <!-- <li><a role="presentation" href="<?php //echo_uri("settings/purchaserequest_request_settings/"); ?>" data-target="#purchaserequest-request-settings-tab"><?php echo lang('purchaserequest_request_settings'); ?></a></li> -->
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="purchaserequest-settings-tab">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="logo" class=" col-md-2"><?php echo lang('purchaserequest_logo'); ?> (300x100) </label>
                                <div class=" col-md-10">
                                    <div class="pull-left mr15">
                                        <?php
                                        $purchaserequest_logo = "purchaserequest_logo";
                                        if (!get_setting($purchaserequest_logo)) {
                                            $purchaserequest_logo = "po_logo";
                                        }
                                        ?>
                                        <img id="purchaserequest-logo-preview" src="<?php echo get_file_from_setting($purchaserequest_logo); ?>" alt="..." />
                                    </div>
                                    <div class="pull-left file-upload btn btn-default btn-xs">
                                        <i class="fa fa-upload"></i> <?php echo lang("upload_and_crop"); ?>
                                        <input id="purchaserequest_logo_file" class="cropbox-upload upload" name="purchaserequest_logo_file" type="file" data-height="100" data-width="300" data-preview-container="#purchaserequest-logo-preview" data-input-field="#purchaserequest_logo" />
                                    </div>
                                    <div class="mt10 ml10 pull-left">
                                        <?php
                                        echo form_upload(array(
                                            "id" => "purchaserequest_logo_file_upload",
                                            "name" => "purchaserequest_logo_file",
                                            "class" => "no-outline hidden-input-file"
                                        ));
                                        ?>
                                        <label for="purchaserequest_logo_file_upload" class="btn btn-default btn-xs">
                                            <i class="fa fa-upload"></i> <?php echo lang("upload"); ?>
                                        </label>
                                    </div>
                                    <input type="hidden" id="purchaserequest_logo" name="purchaserequest_logo" value=""  />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="purchaserequest_prefix" class=" col-md-2"><?php echo lang('estimate_prefix'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "purchaserequest_prefix",
                                        "name" => "purchaserequest_prefix",
                                        "value" => get_setting("purchaserequest_prefix"),
                                        "class" => "form-control",
                                        "placeholder" => strtoupper(lang("purchaserequest")) . " #"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="purchaserequest_color" class=" col-md-2"><?php echo lang('purchaserequest_color'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "purchaserequest_color",
                                        "name" => "purchaserequest_color",
                                        "value" => get_setting("purchaserequest_color"),
                                        "class" => "form-control",
                                        "placeholder" => "Ex. #e2e2e2"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="send_purchaserequest_bcc_to" class=" col-md-2"><?php echo lang('send_purchaserequest_bcc_to'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "send_purchaserequest_bcc_to",
                                        "name" => "send_purchaserequest_bcc_to",
                                        "value" => get_setting("send_purchaserequest_bcc_to"),
                                        "class" => "form-control",
                                        "placeholder" => lang("email")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="hidden" id="last_purchaserequest_id" name="last_purchaserequest_id" value="<?php echo $last_id; ?>" />
                                <label for="initial_number_of_the_purchaserequest" class="col-md-2"><?php echo lang('initial_number_of_the_purchaserequest'); ?></label>
                                <div class="col-md-3">
                                    <?php
                                    echo form_input(array(
                                        "id" => "initial_number_of_the_purchaserequest",
                                        "name" => "initial_number_of_the_purchaserequest",
                                        "type" => "number",
                                        "value" => $last_id + 1,
                                        "class" => "form-control mini",
                                        "data-rule-greaterThan" => "#last_purchaserequest_id",
                                        "data-msg-greaterThan" => lang("the_purchaserequests_id_must_be_larger_then_last_purchaserequest_id")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="purchaserequest_footer" class="col-md-2"><?php echo lang('purchaserequest_footer') ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "purchaserequest_footer",
                                        "name" => "purchaserequest_footer",
                                        "value" => get_setting('purchaserequest_footer'),
                                        "class" => "form-control"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create_new_projects_automatically_when_purchaserequests_gets_accepted" class="col-md-2"><?php echo lang("create_new_projects_automatically_when_purchaserequests_gets_accepted"); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_checkbox("create_new_projects_automatically_when_purchaserequests_gets_accepted", "1", get_setting("create_new_projects_automatically_when_purchaserequests_gets_accepted") ? true : false, "id='create_new_projects_automatically_when_purchaserequests_gets_accepted'");
                                    ?> 
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="purchaserequest-request-settings-tab"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php $this->load->view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#purchaserequest-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "purchaserequest_logo") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                    if (obj.name === "purchaserequest_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#purchaserequest_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }

                if ($("#purchaserequest_logo").val() || result.reload_page) {
                    location.reload();
                }
            }
        });
        $("#purchaserequest-settings-form .select2").select2();

        initWYSIWYGEditor("#purchaserequest_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>