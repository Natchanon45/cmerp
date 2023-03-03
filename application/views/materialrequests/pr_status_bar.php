<div class="panel panel-default  p15 no-border m0">
    <span><?php echo lang("status") . ": " . js_anchor($pr_info->pr_status_title, array("style" => "background-color: $pr_info->pr_status_color", "class" => "label", "data-id" => $pr_info->id, "data-value" => $pr_info->status_id, "data-act" => "update-pr-status")); ?></span>
 
    <span class="ml15">
        <?php
        echo lang("created_by") . ": ";
        $created_by = $pr_info->created_by_user;
        if ($pr_info->created_by_user_type == "staff") {
            echo get_team_member_profile_link($pr_info->created_by, $created_by);
        } else {
            echo get_client_contact_profile_link($pr_info->created_by, $created_by);
        }
        ?>
    </span>
</div>