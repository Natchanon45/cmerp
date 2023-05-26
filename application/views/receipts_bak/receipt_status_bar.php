<div class="panel panel-default  p15 no-border m0">
    <span><?php echo lang("status") . ": " . js_anchor($receipt_info->receipt_status_title, array("style" => "background-color: $receipt_info->receipt_status_color", "class" => "label", "data-id" => $receipt_info->id, "data-value" => $receipt_info->status_id, "data-act" => "update-receipt-status")); ?></span>

    <span class="ml15">
        <?php
        echo lang("client") . ": ";
        echo (anchor(get_uri("clients/view/" . $receipt_info->client_id), $receipt_info->company_name));
        ?>
    </span>
    <span class="ml15">
        <?php
        echo lang("created_by") . ": ";
        $created_by = $receipt_info->created_by_user;
        if ($receipt_info->created_by_user_type == "staff") {
            echo get_team_member_profile_link($receipt_info->created_by, $created_by);
        } else {
            echo get_client_contact_profile_link($receipt_info->created_by, $created_by);
        }
        ?>
    </span>
</div>