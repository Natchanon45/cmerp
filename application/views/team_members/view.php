<?php $this->load->view("includes/cropbox"); ?>
<div id="page-content" class="clearfix">
    <div class="bg-success clearfix">
        <div class="col-md-6">
            <div class="row p20">
                <?php $this->load->view("users/profile_image_section"); ?>
            </div>
        </div>

        <div class="col-md-6 text-center cover-widget">
            <div class="row p20">
                <?php
                if ($show_projects_count) {
                    count_project_status_widget($user_info->id);
                }

                count_total_time_widget($user_info->id);
                ?> 
            </div>
        </div>
    </div>

  
    <ul id="team-member-view-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist"><?php echo $tabs ?></ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade active pl15 pr15 mb15" id="tab-timeline">
            <?php timeline_widget(array("limit" => 20, "offset" => 0, "is_first_load" => true, "user_id" => $user_info->id)); ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="tab-general-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-files"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-social-links"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-job-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-account-settings"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-my-preferences"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-user-left-menu"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-projects-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-attendance-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-leave-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="tab-expense-info"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".upload").change(function () {
            if (typeof FileReader == 'function' && !$(this).hasClass("hidden-input-file")) {
                showCropBox(this);
            } else {
                $("#profile-image-form").submit();
            }
        });
        $("#profile_image").change(function () {
            $("#profile-image-form").submit();
        });


        $("#profile-image-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "profile_image") {
                        var profile_image = replaceAll(":", "~", data[index]["value"]);
                        data[index]["value"] = profile_image;
                    }
                });
            },
            onSuccess: function (result) {
                if (typeof FileReader == 'function' && !result.reload_page) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    location.reload();
                }
            }
        });

        setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab === "general") {
                $("[data-target=#tab-general-info]").trigger("click");
            } else if (tab === "account") {
                $("[data-target=#tab-account-settings]").trigger("click");
            } else if (tab === "social") {
                $("[data-target=#tab-social-links]").trigger("click");
            } else if (tab === "job_info") {
                $("[data-target=#tab-job-info]").trigger("click");
            } else if (tab === "my_preferences") {
                $("[data-target=#tab-my-preferences]").trigger("click");
            } else if (tab === "left_menu") {
                $("[data-target=#tab-user-left-menu]").trigger("click");
            }
        }, 210);

    });
</script>