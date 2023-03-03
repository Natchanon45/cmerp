<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "delivery";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_delivery_settings"), array("id" => "delivery-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="panel panel-default">

                <ul data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" class="active" href="javascript:;" data-target="#delivery-settings-tab"> <?php echo lang('delivery_settings'); ?></a></li>
                    <!-- <li><a role="presentation" href="<?php //echo_uri("settings/po_request_settings/"); ?>" data-target="#po-request-settings-tab"><?php //echo lang('po_request_settings'); ?></a></li> -->
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="delivery-settings-tab">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="logo" class=" col-md-2"><?php echo lang('delivery_logo'); ?> (300x100) </label>
                                <div class=" col-md-10">
                                    <div class="pull-left mr15">
                                        <?php
                                        $delivery_logo = "delivery_logo";
                                        if (!get_setting($delivery_logo)) {
                                            $delivery_logo = "pr_logo";
                                        }
                                        ?>
                                        <img id="delivery-logo-preview" src="<?php echo get_file_from_setting($delivery_logo); ?>" alt="..." />
                                    </div>
                                    <div class="pull-left file-upload btn btn-default btn-xs">
                                        <i class="fa fa-upload"></i> <?php echo lang("upload_and_crop"); ?>
                                        <input id="delivery_logo_file" class="cropbox-upload upload" name="delivery_logo_file" type="file" data-height="100" data-width="300" data-preview-container="#delivery-logo-preview" data-input-field="#delivery_logo" />
                                    </div>
                                    <div class="mt10 ml10 pull-left">
                                        <?php
                                        echo form_upload(array(
                                            "id" => "delivery_logo_file_upload",
                                            "name" => "delivery_logo_file",
                                            "class" => "no-outline hidden-input-file"
                                        ));
                                        ?>
                                        <label for="delivery_logo_file_upload" class="btn btn-default btn-xs">
                                            <i class="fa fa-upload"></i> <?php echo lang("upload"); ?>
                                        </label>
                                    </div>
                                    <input type="hidden" id="delivery_logo" name="delivery_logo" value=""  />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="delivery_prefix" class=" col-md-2"><?php echo lang('estimate_prefix'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "delivery_prefix",
                                        "name" => "delivery_prefix",
                                        "value" => get_setting("delivery_prefix"),
                                        "class" => "form-control",
                                        "placeholder" => strtoupper(lang("delivery")) . " #"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="delivery_color" class=" col-md-2"><?php echo lang('delivery_color'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "delivery_color",
                                        "name" => "delivery_color",
                                        "value" => get_setting("delivery_color"),
                                        "class" => "form-control",
                                        "placeholder" => "Ex. #e2e2e2"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="send_delivery_bcc_to" class=" col-md-2"><?php echo lang('send_delivery_bcc_to'); ?></label>
                                <div class=" col-md-10">
                                    <?php
                                    echo form_input(array(
                                        "id" => "send_delivery_bcc_to",
                                        "name" => "send_delivery_bcc_to",
                                        "value" => get_setting("send_delivery_bcc_to"),
                                        "class" => "form-control",
                                        "placeholder" => lang("email")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="hidden" id="last_delivery_id" name="last_delivery_id" value="<?php echo $last_id; ?>" />
                                <label for="initial_number_of_the_delivery" class="col-md-2"><?php echo lang('initial_number_of_the_delivery'); ?></label>
                                <div class="col-md-3">
                                    <?php
                                    echo form_input(array(
                                        "id" => "initial_number_of_the_delivery",
                                        "name" => "initial_number_of_the_delivery",
                                        "type" => "number",
                                        "value" => $last_id + 1,
                                        "class" => "form-control mini",
                                        "data-rule-greaterThan" => "#last_delivery_id",
                                        "data-msg-greaterThan" => lang("the_deliverys_id_must_be_larger_then_last_delivery_id")
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="delivery_footer" class="col-md-2"><?php echo lang('delivery_footer') ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "delivery_footer",
                                        "name" => "delivery_footer",
                                        "value" => get_setting('delivery_footer'),
                                        "class" => "form-control"
                                    ));
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create_new_projects_automatically_when_delivery_gets_accepted" class="col-md-2"><?php echo lang("create_new_projects_automatically_when_delivery_gets_accepted"); ?></label>
                                <div class="col-md-10">
                                    <?php
                                    echo form_checkbox("create_new_projects_automatically_when_delivery_gets_accepted", "1", get_setting("create_new_projects_automatically_when_delivery_gets_accepted") ? true : false, "id='create_new_projects_automatically_when_deliverys_gets_accepted'");
                                    ?> 
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="delivery-request-settings-tab"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php $this->load->view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#delivery-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "delivery_logo") {
                        var image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = image;
                    }
                    if (obj.name === "delivery_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#delivery_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }

                if ($("#delivery_logo").val() || result.reload_page) {
                    location.reload();
                }
            }
        });
        $("#delivery-settings-form .select2").select2();

        initWYSIWYGEditor("#delivery_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>