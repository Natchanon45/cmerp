<style type="text/css">
.permission-list fieldset{
    border: 1px solid silver;
    padding: 6px 12px;
    margin-bottom: 22px;
}

.permission-list legend{
    padding: 0;
    margin: 0;
    font-size: 1.1em;
    width: auto;
    border: 0;
}

.permission-list ol, .permission-list ol li{
    list-style: none;
    padding: 0;
    margin: 0;
    border: 0;
    line-height: 26px;
}

.permission-list ol li.n{
    line-height: 8px;
}

.permission-list ol li label, .permission-list ol li input{
    display: inline-block;
    margin: 0;
    padding: 0;
}

.permission-list ol li input{
    position: relative;
    top: 1px;
}

.permission-list ol li label{
    margin-left: 4px;
}
</style>

<div class="tab-content">
    <?php echo form_open(get_uri("roles/save_permissions"), array("id" => "permissions-form", "class" => "general-form dashed-row", "role" => "form")); ?>
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <div class="panel">
        <div class="panel-default panel-heading">
            <h4><?php echo lang('permissions') . ": " . $model_info->title; ?></h4>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-primary mr10"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
        </div>
        <div class="panel-body">
            <ul class="permission-list">
                <li>
                    <h5><?php echo lang("set_project_permissions"); ?>:</h5>
                    <div>
                        <?php
                        echo form_checkbox("can_manage_all_projects", "1", $can_manage_all_projects ? true : false, "id='can_manage_all_projects' class='manage_project_section'");
                        ?>
                        <label for="can_manage_all_projects"><?php echo lang("can_manage_all_projects"); ?></label>
                    </div>
                    <div id="show_assigned_tasks_only_section">
                        <?php
                        echo form_checkbox("show_assigned_tasks_only", "1", $show_assigned_tasks_only ? true : false, "id='show_assigned_tasks_only'");
                        ?>
                        <label for="show_assigned_tasks_only"><?php echo lang("show_assigned_tasks_only"); ?></label>
                    </div>
                    <div id="can_update_only_assigned_tasks_status_section">
                        <?php
                        echo form_checkbox("can_update_only_assigned_tasks_status", "1", $can_update_only_assigned_tasks_status ? true : false, "id='can_update_only_assigned_tasks_status'");
                        ?>
                        <label for="can_update_only_assigned_tasks_status"><?php echo lang("can_update_only_assigned_tasks_status"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_create_projects", "1", $can_create_projects ? true : false, "id='can_create_projects' class='manage_project_section'");
                        ?>
                        <label for="can_create_projects"><?php echo lang("can_create_projects"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_edit_projects", "1", $can_edit_projects ? true : false, "id='can_edit_projects' class='manage_project_section'");
                        ?>
                        <label for="can_edit_projects"><?php echo lang("can_edit_projects"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_delete_projects", "1", $can_delete_projects ? true : false, "id='can_delete_projects' class='manage_project_section'");
                        ?>
                        <label for="can_delete_projects"><?php echo lang("can_delete_projects"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_add_remove_project_members", "1", $can_add_remove_project_members ? true : false, "id='can_add_remove_project_members' class='manage_project_section'");
                        ?>
                        <label for="can_add_remove_project_members"><?php echo lang("can_add_remove_project_members"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_create_tasks", "1", $can_create_tasks ? true : false, "id='can_create_tasks' class='manage_project_section'");
                        ?>
                        <label for="can_create_tasks"><?php echo lang("can_create_tasks"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_edit_tasks", "1", $can_edit_tasks ? true : false, "id='can_edit_tasks'");
                        ?>
                        <label for="can_edit_tasks"><?php echo lang("can_edit_tasks"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_delete_tasks", "1", $can_delete_tasks ? true : false, "id='can_delete_tasks'");
                        ?>
                        <label for="can_delete_tasks"><?php echo lang("can_delete_tasks"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_comment_on_tasks", "1", $can_comment_on_tasks ? true : false, "id='can_comment_on_tasks'");
                        ?>
                        <label for="can_comment_on_tasks"><?php echo lang("can_comment_on_tasks"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_create_milestones", "1", $can_create_milestones ? true : false, "id='can_create_milestones'");
                        ?>
                        <label for="can_create_milestones"><?php echo lang("can_create_milestones"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_edit_milestones", "1", $can_edit_milestones ? true : false, "id='can_edit_milestones'");
                        ?>
                        <label for="can_edit_milestones"><?php echo lang("can_edit_milestones"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_delete_milestones", "1", $can_delete_milestones ? true : false, "id='can_delete_milestones'");
                        ?>
                        <label for="can_delete_milestones"><?php echo lang("can_delete_milestones"); ?></label>
                    </div>

                    <div>
                        <?php
                        echo form_checkbox("can_delete_files", "1", $can_delete_files ? true : false, "id='can_delete_files'");
                        ?>
                        <label for="can_delete_files"><?php echo lang("can_delete_files"); ?></label>
                    </div>

                </li>
                <li>
                    <h5><?php echo lang("set_team_members_permission"); ?>:</h5>
                    <div>
                        <?php
                        echo form_checkbox("hide_team_members_list", "1", $hide_team_members_list ? true : false, "id='hide_team_members_list'");
                        ?>
                        <label for="hide_team_members_list"><?php echo lang("hide_team_members_list"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_view_team_members_contact_info", "1", $can_view_team_members_contact_info ? true : false, "id='can_view_team_members_contact_info'");
                        ?>
                        <label for="can_view_team_members_contact_info"><?php echo lang("can_view_team_members_contact_info"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_checkbox("can_view_team_members_social_links", "1", $can_view_team_members_social_links ? true : false, "id='can_view_team_members_social_links'");
                        ?>
                        <label for="can_view_team_members_social_links"><?php echo lang("can_view_team_members_social_links"); ?></label>
                    </div>
                    <div>
                        <label for="can_update_team_members_general_info_and_social_links"><?php echo lang("can_update_team_members_general_info_and_social_links"); ?></label>
                        <div class="ml15">
                            <div>
                                <?php
                                echo form_radio(array(
                                    "id" => "team_member_update_permission_no",
                                    "name" => "team_member_update_permission",
                                    "value" => "",
                                    "class" => "team_member_update_permission toggle_specific",
                                        ), $team_member_update_permission, ($team_member_update_permission === "") ? true : false);
                                ?>
                                <label for="team_member_update_permission_no"><?php echo lang("no"); ?></label>
                            </div>
                            <div>
                                <?php
                                echo form_radio(array(
                                    "id" => "team_member_update_permission_all",
                                    "name" => "team_member_update_permission",
                                    "value" => "all",
                                    "class" => "team_member_update_permission toggle_specific",
                                        ), $team_member_update_permission, ($team_member_update_permission === "all") ? true : false);
                                ?>
                                <label for="team_member_update_permission_all"><?php echo lang("yes_all_members"); ?></label>
                            </div>
                            <div class="form-group">
                                <?php
                                echo form_radio(array(
                                    "id" => "team_member_update_permission_specific",
                                    "name" => "team_member_update_permission",
                                    "value" => "specific",
                                    "class" => "team_member_update_permission toggle_specific",
                                        ), $team_member_update_permission, ($team_member_update_permission === "specific") ? true : false);
                                ?>
                                <label for="team_member_update_permission_specific">
                                    <?php echo lang("yes_specific_members_or_teams"); ?>
                                </label>
                                <div class="specific_dropdown">
                                    <input type="text" value="<?php echo $team_member_update_permission_specific; ?>" name="team_member_update_permission_specific" id="team_member_update_permission_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_members_and_or_teams'); ?>"  />    
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <h5><?php echo lang("set_message_permissions"); ?>:</h5>
                    <div>
                        <?php
                        echo form_checkbox("message_permission_no", "1", ($message_permission == "no") ? true : false, "id='message_permission_no'");
                        ?>
                        <label for="message_permission_no"><?php echo lang("cant_send_any_messages"); ?></label>
                    </div>
                    <div id="message_permission_specific_area" class="form-group <?php echo ($message_permission == "no") ? "hide" : ""; ?>">
                        <?php
                        echo form_checkbox("message_permission_specific_checkbox", "1", ($message_permission == "specific") ? true : false, "id='message_permission_specific_checkbox' class='message_permission_specific toggle_specific'");
                        ?>
                        <label for="message_permission_specific_checkbox"><?php echo lang("can_send_messages_to_specific_members_or_teams"); ?></label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $message_permission_specific; ?>" name="message_permission_specific" id="message_permission_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_members_and_or_teams'); ?>"  />    
                        </div>
                    </div>
                </li>
                <li>
                    <h5>ตั้งค่าการเข้าถึงเอกสาร:</h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "access_note_no",
                            "name" => "access_note",
                            "value" => false,
                            "class" => "note_permission toggle_specific",
                                ), $access_note, ($access_note == "") ? true : false);
                        ?>
                        <label for="access_note_no">ไม่ใช่</label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "access_note_all",
                            "name" => "access_note",
                            "value" => "all",
                            "class" => "note_permission toggle_specific",
                                ), $access_note, ($access_note === "all") ? true : false);
                        ?>
                        <label for="access_note_all">ใช่ เอกสารทั้งหมด</label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "access_note_assigned_only",
                            "name" => "access_note",
                            "value" => "assigned_only",
                            "class" => "note_permission toggle_specific",
                                ), $access_note, ($access_note === "assigned_only") ? true : false);
                        ?>
                        <label for="access_note_assigned_only">ใช่ เอกสารของคุณเท่านั้น</label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "access_note_specific",
                            "name" => "access_note",
                            "value" => "specific",
                            "class" => "note_permission toggle_specific",
                                ), $access_note, ($access_note === "specific") ? true : false);
                        ?>
                        <label for="access_note_specific">ใช่เฉพาะประเภทของเอกสาร:</label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $access_note_specific; ?>" name="access_note_specific" id="note_types_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="Choose note types"  />
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <div>
                            <?php echo form_checkbox("add_note", "Y", $add_note ? true : false, "id='add_note'"); ?>
                            <label for="add_note">สามารถเพิ่มเอกสารได้</label>
                        </div>
                        <div>
                            <?php echo form_checkbox("update_note", "Y", $update_note ? true : false, "id='update_note'"); ?>
                            <label for="update_note">สามารถแก้ไขเอกสารได้</label>
                        </div>
                    </div>
                </li>
                <li>
                    <h5>สามารถเข้าถึงค่าใช้จ่าย:</h5>
                    <div>
                        <input type="radio" name="access_expenses" value="" <?php if($access_expenses == false) echo "checked"; ?> >
                        <label for="access_expenses">ไม่ใช่</label>
                    </div>
                    <div>
                        <input type="radio" name="access_expenses" value="own" <?php if($access_expenses == "own") echo "checked"; ?> >
                        <label for="access_expenses"> ใช่ ค่าใช้จ่ายของตัวเองเท่านัน</label>
                    </div>
                    <div>
                        <input type="radio" name="access_expenses" value="all" <?php if($access_expenses == "all") echo "checked"; ?> >
                        <label for="access_expenses"> ใช่ ค่าใช้จ่ายทั้งหมด</label>
                    </div>
                </li>
                <li>
                    <h5><?php echo lang("set_event_permissions"); ?>:</h5>
                    <div>
                        <?php
                        echo form_checkbox("disable_event_sharing", "1", $disable_event_sharing ? true : false, "id='disable_event_sharing'");
                        ?>
                        <label for="disable_event_sharing"><?php echo lang("disable_event_sharing"); ?></label>
                    </div>
                </li>
                <li>
                    <h5><?php echo lang("can_manage_team_members_leave"); ?> <span class="help" data-toggle="tooltip" title="Assign, approve or reject leave applications"><i class="fa fa-question-circle"></i></span> </h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "leave_permission_no",
                            "name" => "leave_permission",
                            "value" => "",
                            "class" => "leave_permission toggle_specific",
                                ), $leave, ($leave === "") ? true : false);
                        ?>
                        <label for="leave_permission_no"><?php echo lang("no"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "leave_permission_all",
                            "name" => "leave_permission",
                            "value" => "all",
                            "class" => "leave_permission toggle_specific",
                                ), $leave, ($leave === "all") ? true : false);
                        ?>
                        <label for="leave_permission_all"><?php echo lang("yes_all_members"); ?></label>
                    </div>
                    <div class="form-group pb0 mb0 no-border">
                        <?php
                        echo form_radio(array(
                            "id" => "leave_permission_specific",
                            "name" => "leave_permission",
                            "value" => "specific",
                            "class" => "leave_permission toggle_specific",
                                ), $leave, ($leave === "specific") ? true : false);
                        ?>
                        <label for="leave_permission_specific">
                            <!-- <?php echo lang("yes_specific_members_or_teams") . " (" . lang("excluding_his_her_leaves") . ")"; ?> -->
                            <?php echo lang("yes_specific_members_or_teams"); ?>
                        </label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $leave_specific; ?>" name="leave_permission_specific" id="leave_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_members_and_or_teams'); ?>"  />    
                        </div>

                    </div>
                    <div class="form-group">
                        <div>
                            <?php
                            echo form_checkbox("can_delete_leave_application", "1", $can_delete_leave_application ? true : false, "id='can_delete_leave_application'");
                            ?>
                            <label for="can_delete_leave_application"><?php echo lang("can_delete_leave_application"); ?> <span class="help" data-toggle="tooltip" title="Can delete based on his/her access permission"><i class="fa fa-question-circle"></i></span></label>
                        </div>
                    </div>
                </li>
                <li>
                    <h5><?php echo lang("can_manage_team_members_timecards"); ?> <span class="help" data-toggle="tooltip" title="Add, edit and delete time cards"><i class="fa fa-question-circle"></i></span></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "attendance_permission_no",
                            "name" => "attendance_permission",
                            "value" => "",
                            "class" => "attendance_permission toggle_specific",
                                ), $attendance, ($attendance === "") ? true : false);
                        ?>
                        <label for="attendance_permission_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "attendance_permission_all",
                            "name" => "attendance_permission",
                            "value" => "all",
                            "class" => "attendance_permission toggle_specific",
                                ), $attendance, ($attendance === "all") ? true : false);
                        ?>
                        <label for="attendance_permission_all"><?php echo lang("yes_all_members"); ?></label>
                    </div>
                    <div class="form-group">
                        <?php
                        echo form_radio(array(
                            "id" => "attendance_permission_specific",
                            "name" => "attendance_permission",
                            "value" => "specific",
                            "class" => "attendance_permission toggle_specific",
                                ), $attendance, ($attendance === "specific") ? true : false);
                        ?>
                        <label for="attendance_permission_specific">
                            <!-- <?php echo lang("yes_specific_members_or_teams") . " (" . lang("excluding_his_her_time_cards") . ")"; ?> -->
                            <?php echo lang("yes_specific_members_or_teams"); ?>
                        </label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $attendance_specific; ?>" name="attendance_permission_specific" id="attendance_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_members_and_or_teams'); ?>"  />
                        </div>
                    </div>

                </li>
                <li>
                    <h5><?php echo lang("can_manage_team_members_project_timesheet"); ?></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "timesheet_manage_permission_no",
                            "name" => "timesheet_manage_permission",
                            "value" => "",
                            "class" => "timesheet_manage_permission toggle_specific",
                                ), $timesheet_manage_permission, ($timesheet_manage_permission === "") ? true : false);
                        ?>
                        <label for="timesheet_manage_permission_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "timesheet_manage_permission_all",
                            "name" => "timesheet_manage_permission",
                            "value" => "all",
                            "class" => "timesheet_manage_permission toggle_specific",
                                ), $timesheet_manage_permission, ($timesheet_manage_permission === "all") ? true : false);
                        ?>
                        <label for="timesheet_manage_permission_all"><?php echo lang("yes_all_members"); ?></label>
                    </div>
                    <div class="form-group">
                        <?php
                        echo form_radio(array(
                            "id" => "timesheet_manage_permission_specific",
                            "name" => "timesheet_manage_permission",
                            "value" => "specific",
                            "class" => "timesheet_manage_permission toggle_specific",
                                ), $timesheet_manage_permission, ($timesheet_manage_permission === "specific") ? true : false);
                        ?>
                        <label for="timesheet_manage_permission_specific"><?php echo lang("yes_specific_members_or_teams"); ?></label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $timesheet_manage_permission_specific; ?>" name="timesheet_manage_permission_specific" id="timesheet_manage_permission_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_members_and_or_teams'); ?>"  />
                        </div>
                    </div>
                </li>
               
              
               
                <li>
                    <h5><?php echo lang("can_access_clients_information"); ?> <span class="help" data-toggle="tooltip" title="Hides all information of clients except company name."><i class="fa fa-question-circle"></i></span></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "client_no",
                            "name" => "client_permission",
                            "value" => "",
                                ), $client, ($client === "") ? true : false);
                        ?>
                        <label for="client_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "client_yes",
                            "name" => "client_permission",
                            "value" => "all",
                                ), $client, ($client === "all") ? true : false);
                        ?>
                        <label for="client_yes"><?php echo lang("yes_all_clients"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "client_yes_own",
                            "name" => "client_permission",
                            "value" => "own",
                                ), $client, ($client === "own") ? true : false);
                        ?>
                        <label for="client_yes_own"><?php echo lang("yes_only_own_clients"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "client_read_only",
                            "name" => "client_permission",
                            "value" => "read_only",
                                ), $client, ($client === "read_only") ? true : false);
                        ?>
                        <label for="client_read_only"><?php echo lang("read_only"); ?></label>
                    </div>
                </li>
                <li>
                    <h5><?php echo lang("can_access_leads_information"); ?></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "lead_no",
                            "name" => "lead_permission",
                            "value" => "",
                                ), $lead, ($lead === "") ? true : false);
                        ?>
                        <label for="lead_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "lead_yes",
                            "name" => "lead_permission",
                            "value" => "all",
                                ), $lead, ($lead === "all") ? true : false);
                        ?>
                        <label for="lead_yes"><?php echo lang("yes_all_leads"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "lead_yes_own",
                            "name" => "lead_permission",
                            "value" => "own",
                                ), $lead, ($lead === "own") ? true : false);
                        ?>
                        <label for="lead_yes_own"><?php echo lang("yes_only_own_leads"); ?></label>
                    </div>
                </li>

                <li>
                    <h5><?php echo lang("can_access_tickets"); ?></h5>       
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "ticket_permission_no",
                            "name" => "ticket_permission",
                            "value" => "",
                            "class" => "ticket_permission toggle_specific",
                                ), $ticket, ($ticket === "") ? true : false);
                        ?>
                        <label for="ticket_permission_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "ticket_permission_all",
                            "name" => "ticket_permission",
                            "value" => "all",
                            "class" => "ticket_permission toggle_specific",
                                ), $ticket, ($ticket === "all") ? true : false);
                        ?>
                        <label for="ticket_permission_all"><?php echo lang("yes_all_tickets"); ?></label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "ticket_permission_assigned_only",
                            "name" => "ticket_permission",
                            "value" => "assigned_only",
                            "class" => "ticket_permission toggle_specific",
                                ), $ticket, ($ticket === "assigned_only") ? true : false);
                        ?>
                        <label for="ticket_permission_assigned_only"><?php echo lang("yes_assigned_tickets_only"); ?></label>
                    </div>
                    <div class="">
                        <?php
                        echo form_radio(array(
                            "id" => "ticket_permission_specific",
                            "name" => "ticket_permission",
                            "value" => "specific",
                            "class" => "ticket_permission toggle_specific",
                                ), $ticket, ($ticket === "specific") ? true : false);
                        ?>
                        <label for="ticket_permission_specific"><?php echo lang("yes_specific_ticket_types"); ?>:</label>
                        <div class="specific_dropdown">
                            <input type="text" value="<?php echo $ticket_specific; ?>" name="ticket_permission_specific" id="ticket_types_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" placeholder="<?php echo lang('choose_ticket_types'); ?>"  />
                        </div>
						
                    </div>
					
					
					<?php echo  $ticketReadonly ?>
 				
					
                </li>
                <li>
                    <h5><?php echo lang("can_manage_announcements"); ?></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "announcement_no",
                            "name" => "announcement_permission",
                            "value" => "",
                                ), $announcement, ($announcement === "") ? true : false);
                        ?>
                        <label for="announcement_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "announcement_yes",
                            "name" => "announcement_permission",
                            "value" => "all",
                                ), $announcement, ($announcement === "all") ? true : false);
                        ?>
                        <label for="announcement_yes"><?php echo lang("yes"); ?></label>
                    </div>
                </li>
                
                <li>
                    <h5><?php echo lang("can_manage_help_and_knowledge_base"); ?></h5>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "help_no",
                            "name" => "help_and_knowledge_base",
                            "value" => "",
                                ), $help_and_knowledge_base, ($help_and_knowledge_base === "") ? true : false);
                        ?>
                        <label for="help_no"><?php echo lang("no"); ?> </label>
                    </div>
                    <div>
                        <?php
                        echo form_radio(array(
                            "id" => "help_yes",
                            "name" => "help_and_knowledge_base",
                            "value" => "all",
                                ), $help_and_knowledge_base, ($help_and_knowledge_base === "all") ? true : false);
                        ?>
                        <label for="help_yes"><?php echo lang("yes"); ?></label>
                    </div>
                </li>

                <!-- START: BOM -->                
                <li>
                    <h5><?php echo lang("stock_supplier_permissions"); ?>:</h5>
                    <?php
                        foreach([
                            [
                                'name' => 'bom_supplier_read_self',
                                'value' => $bom_supplier_read_self,
                                'text' => 'stock_supplier_can_read_self'
                            ], [
                                'name' => 'bom_supplier_read',
                                'value' => $bom_supplier_read,
                                'text' => 'stock_supplier_can_read'
                            ], [
                                'name' => 'bom_supplier_create',
                                'value' => $bom_supplier_create,
                                'text' => 'stock_supplier_can_create'
                            ], [
                                'name' => 'bom_supplier_update',
                                'value' => $bom_supplier_update,
                                'text' => 'stock_supplier_can_update'
                            ], [
                                'name' => 'bom_supplier_delete',
                                'value' => $bom_supplier_delete,
                                'text' => 'stock_supplier_can_delete'
                            ]
                        ] as $i=>$d){
                    ?>
                        <div>
                            <?php echo form_checkbox($d['name'], "1", $d['value']? true: false, "id='".$d['name']."' class='".$d['name']."'"); ?>
                            <label for="<?= $d['name'] ?>"><?php echo lang($d['text']); ?></label>
                        </div>
                    <?php }?>
                </li>
                <li>
                    <h5><?php echo lang("stock_material_permissions"); ?>:</h5>
                    <?php
                        foreach([
                            [
                                'name' => 'bom_material_read',
                                'value' => $bom_material_read,
                                'text' => 'stock_material_can_read'
                            ], [
                                'name' => 'bom_material_read_production_name',
                                'value' => $bom_material_read_production_name,
                                'text' => 'stock_material_can_read_production_name'
                            ], [
                                'name' => 'bom_material_create',
                                'value' => $bom_material_create,
                                'text' => 'stock_material_can_create'
                            ], [
                                'name' => 'bom_material_update',
                                'value' => $bom_material_update,
                                'text' => 'stock_material_can_update'
                            ], [
                                'name' => 'bom_material_delete',
                                'value' => $bom_material_delete,
                                'text' => 'stock_material_can_delete'
                            ]
                        ] as $i=>$d){
                    ?>
                        <div>
                            <?php echo form_checkbox($d['name'], "1", $d['value']? true: false, "id='".$d['name']."' class='".$d['name']."'"); ?>
                            <label for="<?= $d['name'] ?>"><?php echo lang($d['text']); ?></label>
                        </div>
                    <?php }?>
                </li>
                <li>
                    <h5><?php echo lang("stock_restock_permissions"); ?>:</h5>
                    <?php
                        foreach([
                            [
                                'name' => 'bom_restock_read_self',
                                'value' => $bom_restock_read_self,
                                'text' => 'stock_restock_can_read_self'
                            ], [
                                'name' => 'bom_restock_read',
                                'value' => $bom_restock_read,
                                'text' => 'stock_restock_can_read'
                            ], [
                                'name' => 'bom_restock_read_price',
                                'value' => $bom_restock_read_price,
                                'text' => 'stock_restock_can_read_price'
                            ], [
                                'name' => 'bom_restock_create',
                                'value' => $bom_restock_create,
                                'text' => 'stock_restock_can_create'
                            ], [
                                'name' => 'bom_restock_update',
                                'value' => $bom_restock_update,
                                'text' => 'stock_restock_can_update'
                            ], [
                                'name' => 'bom_restock_delete',
                                'value' => $bom_restock_delete,
                                'text' => 'stock_restock_can_delete'
                            ]
                        ] as $i=>$d){
                    ?>
                        <div>
                            <?php echo form_checkbox($d['name'], "1", $d['value']? true: false, "id='".$d['name']."' class='".$d['name']."'"); ?>
                            <label for="<?= $d['name'] ?>"><?php echo lang($d['text']); ?></label>
                        </div>
                    <?php }?>
                </li>
                <li>
                    <h5>ตั้งค่าสิทธิ์การเข้าถึงรายการสินค้า:</h5>
                    <div>
                        <input type="radio" name="access_product_item" value="" <?php if($access_product_item == false) echo "checked"; ?> >
                        <label for="access_product_item">ไม่ใช่</label>
                    </div>
                    <div>
                        <input type="radio" name="access_product_item" value="own" <?php if($access_product_item == "own") echo "checked"; ?> >
                        <label for="access_product_item">ใช่เฉพาะรายการสินค้าของตัวเองเท่านัน</label>
                    </div>
                    <div>
                        <input type="radio" name="access_product_item" value="all" <?php if($access_product_item == "all") echo "checked"; ?> >
                        <label for="access_product_item">ใช่รายการสินค้าทั้งหมด</label>
                    </div>
                    <div>
                        <input type="checkbox" name="access_product_item_formula" value="Y" <?php if($access_product_item_formula == true) echo "checked"; ?> >
                        <label for="access_product_item_formula">สามารถเห็นส่วนประกอบสินค้า</label>
                    </div>

                    <div>
                        <input type="checkbox" name="create_product_item" value="Y" <?php if($create_product_item == true) echo "checked"; ?> >
                        <label for="create_product_item">สามารถเพิ่มรายการสินค้า</label>
                    </div>

                    <div>
                        <input type="checkbox" name="access_product_category" value="Y" <?php if($access_product_category == true) echo "checked"; ?> >
                        <label for="access_product_category">สามารถจัดการหมวดหมู่สินค้า</label>
                    </div>
                </li>
                <li>
                    <h5>ตั้งค่าสิทธิ์การเข้าถึงรายการสินค้ากึ่งสำเร็จ:</h5>
                    <div>
                        <input type="radio" name="access_semi_product_item" value="" <?php if($access_semi_product_item == false) echo "checked"; ?> >
                        <label for="access_semi_product_item">ไม่ใช่</label>
                    </div>
                    <div>
                        <input type="radio" name="access_semi_product_item" value="own" <?php if($access_semi_product_item == "own") echo "checked"; ?> >
                        <label for="access_semi_product_item">ใช่เฉพาะรายการสินค้ากึ่งสำเร็จของตัวเองเท่านัน</label>
                    </div>
                    <div>
                        <input type="radio" name="access_semi_product_item" value="all" <?php if($access_semi_product_item == "all") echo "checked"; ?> >
                        <label for="access_semi_product_item">ใช่รายการสินค้ากึ่งสำเร็จทั้งหมด</label>
                    </div>
                    <div>
                        <input type="checkbox" name="access_semi_product_item_formula" value="Y" <?php if($access_semi_product_item_formula == true) echo "checked"; ?> >
                        <label for="access_semi_product_item_formula">สามารถเห็นส่วนประกอบสินค้ากึ่งสำเร็จ</label>
                    </div>

                    <div>
                        <input type="checkbox" name="create_semi_product_item" value="Y" <?php if($create_semi_product_item == true) echo "checked"; ?> >
                        <label for="create_semi_product_item">สามารถเพิ่มรายการสินค้ากึ่งสำเร็จ</label>
                    </div>

                    <div>
                        <input type="checkbox" name="access_semi_product_category" value="Y" <?php if($access_semi_product_category == true) echo "checked"; ?> >
                        <label for="access_semi_product_category">สามารถจัดการหมวดหมู่สินค้ากึ่งสำเร็จ</label>
                    </div>
                </li>
                <li class="accounting">
                    <h5><?php echo lang("setting_account_management"); ?>:</h5>
                    <fieldset>
                        <legend><?php lang("setting_sale_order"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_sales_order_access" value="Y" <?php if($accounting['sales_order']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_sales_order_access"><?php echo lang("setting_sale_order_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_quotation"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_quotation_access" value="Y" <?php if($accounting['quotation']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_quotation_access"><?php echo lang("setting_quotation_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_invoice"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_invoice_access" value="Y" <?php if($accounting['invoice']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_invoice_access"><?php echo lang("setting_invoice_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_tax_invoice"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_tax_invoice_access" value="Y" <?php if($accounting['tax_invoice']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_tax_invoice_access"><?php echo lang("setting_tax_invoice_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_billing_note"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_billing_note_access" value="Y" <?php if($accounting['billing_note']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_billing_note_access"><?php echo lang("setting_billing_note_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_receipt"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_receipt_access" value="Y" <?php if($accounting['receipt']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_receipt_access"><?php echo lang("setting_receipt_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_credit_note"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_credit_note_access" value="Y" <?php if($accounting['credit_note']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_credit_note_access"><?php echo lang("setting_credit_note_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_debit_note"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_debit_note_access" value="Y" <?php if($accounting['debit_note']['access'] == true) echo "checked"; ?> >
                            <label for="accounting_debit_note_access"><?php echo lang("setting_debit_note_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_purchase_request"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_purchase_request_access" value="Y" <?php if ($accounting["purchase_request"]["access"]) { echo "checked"; } ?>>
                            <label for="accounting_purchase_request"><?php echo lang("setting_purchase_request_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_purchase_order"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_purchase_order_access" value="Y" <?php if ($accounting["purchase_order"]["access"]) { echo "checked"; } ?>>
                            <label for="accounting_purchase_order"><?php echo lang("setting_purchase_order_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_payment_voucher"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_payment_voucher_access" value="Y" <?php if ($accounting["payment_voucher"]["access"]) { echo "checked"; } ?>>
                            <label for="accounting_payment_voucher"><?php echo lang("setting_payment_voucher_access"); ?></label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo lang("setting_goods_receipt"); ?></legend>
                        <div>
                            <input type="checkbox" name="accounting_goods_receipt_access" value="Y" <?php if ($accounting["goods_receipt"]["access"]) { echo "checked"; } ?>>
                            <label for="accounting_goods_receipt"><?php echo lang("setting_goods_receipt_access"); ?></label>
                        </div>
                    </fieldset>
                </li>
                <li>
                    <h5>ตั้งค่าสิทธิ์การเข้าถึงใบขอเบิก:</h5>
                    <div>
                        <input type="checkbox" name="access_material_request" value="Y" <?php if($access_material_request == true) echo "checked"; ?> >
                        <label for="access_material_request">สามารถเห็นใบขอเบิก</label>
                    </div>
                    <div>
                        <input type="checkbox" name="create_material_request" value="Y" <?php if($create_material_request == true) echo "checked"; ?> >
                        <label for="create_material_request">สามารถสร้างใบขอเบิก</label>
                    </div>
                    <div>
                        <input type="checkbox" name="update_material_request" value="Y" <?php if($update_material_request == true) echo "checked"; ?> >
                        <label for="update_material_request">สามารถแก้ไข (ก่อนอนุมัติ)</label>
                    </div>
                    <div>
                        <input type="checkbox" name="delete_material_request" value="Y" <?php if($delete_material_request == true) echo "checked"; ?> >
                        <label for="delete_material_request">สามารถลบ (ก่อนอนุมัติ)</label>
                    </div>
                    <div>
                        <input type="checkbox" name="approve_material_request" value="Y" <?php if($approve_material_request == true) echo "checked"; ?> >
                        <label for="approve_material_request">สิทธิ์อนุมัติใบขอเบิก</label>
                    </div>
                </li>
                <li>
                    <h5>ตั้งค่าสิทธิ์การเข้าถึงใบขอซื้อ:</h5>
                    <div>
                        <input type="checkbox" name="access_purchase_request" value="Y" <?php if($access_purchase_request == true) echo "checked"; ?> >
                        <label for="access_purchase_request">สามารถเห็นใบขอซื้อ</label>
                    </div>
                    <div>
                        <input type="checkbox" name="create_purchase_request" value="Y" <?php if($create_purchase_request == true) echo "checked"; ?> >
                        <label for="create_purchase_request">สามารถสร้างใบขอซื้อ</label>
                    </div>
                    <div>
                        <input type="checkbox" name="update_purchase_request" value="Y" <?php if($update_purchase_request == true) echo "checked"; ?> >
                        <label for="update_purchase_request">สามารถแก้ไข (ก่อนอนุมัติ)</label>
                    </div>
                    <div>
                        <input type="checkbox" name="delete_purchase_request" value="Y" <?php if($delete_purchase_request == true) echo "checked"; ?> >
                        <label for="delete_purchase_request">สามารถลบ (ก่อนอนุมัติ)</label>
                    </div>
                    <div>
                        <input type="checkbox" name="approve_purchase_request" value="Y" <?php if($approve_purchase_request == true) echo "checked"; ?> >
                        <label for="approve_purchase_request">สิทธิ์อนุมัติใบขอซื้อ</label>
                    </div>
                </li>
				<?php echo $lis ?>      
                <!-- END: BOM -->
            </ul>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-primary mr10"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#permissions-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});
            }
        });

        $("#leave_specific_dropdown, #attendance_specific_dropdown, #timesheet_manage_permission_specific_dropdown,  #team_member_update_permission_specific_dropdown, #message_permission_specific_dropdown").select2({
            multiple: true,
            formatResult: teamAndMemberSelect2Format,
            formatSelection: teamAndMemberSelect2Format,
            data: <?php echo ($members_and_teams_dropdown); ?>
        });

        $("#note_types_specific_dropdown").select2({
            multiple: true,
            data: <?php echo ($note_types_dropdown); ?>
        });

        $("#ticket_types_specific_dropdown").select2({
            multiple: true,
            data: <?php echo ($ticket_types_dropdown); ?>
        });

        $('[data-toggle="tooltip"]').tooltip();

        $(".toggle_specific").click(function () {
            toggle_specific_dropdown();
        });

        toggle_specific_dropdown();

        function toggle_specific_dropdown() {
            var selectors = [".note_permission", ".leave_permission", ".attendance_permission", ".timesheet_manage_permission", ".team_member_update_permission", ".ticket_permission", ".message_permission_specific"];
            $.each(selectors, function (index, element) {
                var $element = $(element + ":checked");
                if ((element !== ".message_permission_specific" && $element.val() === "specific") || (element === ".message_permission_specific" && $element.is(":checked") && !$("#message_permission_specific_area").hasClass("hide"))) {
                    $element.closest("li").find(".specific_dropdown").show().find("input").addClass("validate-hidden");
                } else {
                    //console.log($element.closest("li").find(".specific_dropdown"));
                    $(element).closest("li").find(".specific_dropdown").hide().find("input").removeClass("validate-hidden");
                }
            });

        }

        //show/hide message permission checkbox
        $("#message_permission_no").click(function () {
            if ($(this).is(":checked")) {
                $("#message_permission_specific_area").addClass("hide");
            } else {
                $("#message_permission_specific_area").removeClass("hide");
            }
            toggle_specific_dropdown();
        });

        var manageProjectSection = "#can_manage_all_projects, #can_create_projects, #can_edit_projects, #can_delete_projects, #can_add_remove_project_members, #can_create_tasks";
        var manageAssignedTasks = "#show_assigned_tasks_only, #can_update_only_assigned_tasks_status";
        var manageAssignedTasksSection = "#show_assigned_tasks_only_section, #can_update_only_assigned_tasks_status_section";

        if ($(manageProjectSection).is(':checked')) {
            $(manageAssignedTasksSection).addClass("hide");
        }

        $(manageProjectSection).click(function () {
            if ($(this).is(":checked")) {
                $(manageAssignedTasks).prop("checked", false);
                $(manageAssignedTasksSection).addClass("hide");
            } else {
                $(manageAssignedTasksSection).removeClass("hide");
            }
        });

        $('.manage_project_section').change(function () {
            var checkedStatus = $('.manage_project_section:checkbox:checked').length > 0;
            if (!checkedStatus) {
                $(manageAssignedTasksSection).removeClass("hide");
            }
        }).change();

    });
</script>
