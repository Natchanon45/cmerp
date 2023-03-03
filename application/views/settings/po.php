<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "po";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_po_settings"), array("id" => "po-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="panel panel-default">

                <ul data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" class="active" href="javascript:;" data-target="#po-settings-tab"> <?php echo lang('po_settings'); ?></a></li>
                    <!-- <li><a role="presentation" href="<?php //echo_uri("settings/po_request_settings/"); ?>" data-target="#po-request-settings-tab"><?php //echo lang('po_request_settings'); ?></a></li> -->
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="po-settings-tab">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="logo" class=" col-md-2"><?php echo lang('po_logo'); ?> (300x100) </label>
                                <div class=" col-md-10">
                                    <div class="pull-left mr15">
                                        <?php
                                        $po_logo = "po_logo";
                                        if (!get_setting($po_logo)) {
                                            $po_logo = "pr_logo";
                                        }
                                        ?>
                                        <img id="po-logo-preview" src="<?php echo get_file_from_setting($po_logo); ?>" alt="..." />
                                    </div>
                                    <div class="pull-left file-upload btn btn-default btn-xs">
                                        <i class="fa fa-upload"></i> <?php echo lang("upload_and_crop"); ?>
                                        <input id="po_logo_file" class="cropbox-upload upload" name="po_logo_file" type="file" data-height="100" data-width="300" data-preview-container="#po-logo-preview" data-input-field="#po_logo" />
                                    </div>
                                    <div class="mt10 ml10 pull-left">
                                        <?php
                                        echo form_upload(array(
                                            "id" => "po_logo_file_upload",
                                            "name" => "po_logo_file",
                                            "class" => "no-outline hidden-input-file"
                                        ));
                                        ?>
                                        <label for="po_logo_file_upload" class="btn btn-default btn-xs">
                                            <i class="fa fa-upload"></i> <?php echo lang("upload"); ?>
                                        </label>
                                    </div>
                                    <input type="hidden" id="po_logo" name="po_logo" value=""  />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="po_prefix" class=" col-md-2"><?php echo lang('estimate_prefix'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "po_prefix",
                                        "name" => "po_prefix",
                                        "value" => get_setting("po_prefix"),
                                        "class" => "form-control",
                                        "placeholder" => strtoupper(lang("po")) . " #"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="po_color" class=" col-md-2"><?php echo lang('po_color'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "po_color",
                                        "name" => "po_color",
                                        "value" => get_setting("po_color"),
                                        "class" => "form-control",
                                        "placeholder" => "Ex. #e2e2e2"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="send_po_bcc_to" class=" col-md-2"><?php echo lang('send_po_bcc_to'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "send_po_bcc_to",
                                        "name" => "send_po_bcc_to",
                                        "value" => get_setting("send_po_bcc_to"),
                                        "class" => "form-control",
                                        "placeholder" => lang("email")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="hidden" id="last_po_id" name="last_po_id" value="<?php echo $last_id; ?>" />
                                <label for="initial_number_of_the_po" class="col-md-2"><?php echo lang('initial_number_of_the_po'); ?></label>
                                <div class="col-md-3">
                                    <?php
                                    echo form_input(array(
                                        "id" => "initial_number_of_the_po",
                                        "name" => "initial_number_of_the_po",
                                        "type" => "number",
                                        "value" => $last_id + 1,
                                        "class" => "form-control mini",
                                        "data-rule-greaterThan" => "#last_po_id",
                                        "data-msg-greaterThan" => lang("the_pos_id_must_be_larger_then_last_po_id")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="po_footer" class="col-md-2"><?php echo lang('po_footer') ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "po_footer",
                                        "name" => "po_footer",
                                        "value" => get_setting('po_footer'),
                                        "class" => "form-control"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create_new_projects_automatically_when_po_gets_accepted" class="col-md-2"><?php echo lang("create_new_projects_automatically_when_po_gets_accepted"); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_checkbox("create_new_projects_automatically_when_po_gets_accepted", "1", get_setting("create_new_projects_automatically_when_po_gets_accepted") ? true : false, "id='create_new_projects_automatically_when_pos_gets_accepted'");
                                    ?> 
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="po-request-settings-tab"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php $this->load->view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#po-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "po_logo") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                    if (obj.name === "po_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#po_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }

                if ($("#po_logo").val() || result.reload_page) {
                    location.reload();
                }
            }
        });
        $("#po-settings-form .select2").select2();

        initWYSIWYGEditor("#po_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>