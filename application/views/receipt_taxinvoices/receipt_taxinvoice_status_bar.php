<div class="panel panel-default  p15 no-border m0">
    <span class="mr10"><?php echo $receipt_taxinvoice_status_label; ?></span>

    <?php echo make_labels_view_data($receipt_taxinvoice_info->labels_list, "", true); ?>

    <?php if ($receipt_taxinvoice_info->project_id) { ?>
        <span class="ml15"><?php echo lang("project") . ": " . anchor(get_uri("projects/view/" . $receipt_taxinvoice_info->project_id), $receipt_taxinvoice_info->project_title); ?></span>
    <?php } ?>

    <span class="ml15"><?php
        echo lang("client") . ": ";
        echo (anchor(get_uri("clients/view/" . $receipt_taxinvoice_info->client_id), $receipt_taxinvoice_info->company_name));
        ?>
    </span> 

    <span class="ml15"><?php
        echo lang("last_email_sent") . ": ";
        echo (is_date_exists($receipt_taxinvoice_info->last_email_sent_date)) ? format_to_date($receipt_taxinvoice_info->last_email_sent_date, FALSE) : lang("never");
        ?>
    </span>
    <?php if ($receipt_taxinvoice_info->recurring_receipt_taxinvoice_id) { ?>
        <span class="ml15">
            <?php
            echo lang("created_from") . ": ";
            echo anchor(get_uri("receipt_taxinvoices/view/" . $receipt_taxinvoice_info->recurring_receipt_taxinvoice_id), get_receipt_taxinvoice_id($receipt_taxinvoice_info->recurring_receipt_taxinvoice_id));
            ?>
        </span>
    <?php } ?>

    <?php if ($receipt_taxinvoice_info->cancelled_at) { ?>
        <span class="ml15"><?php echo lang("cancelled_at") . ": " . format_to_relative_time($receipt_taxinvoice_info->cancelled_at); ?></span>
    <?php } ?>

    <?php if ($receipt_taxinvoice_info->cancelled_by) { ?>
        <span class="ml15"><?php echo lang("cancelled_by") . ": " . get_team_member_profile_link($receipt_taxinvoice_info->cancelled_by, $receipt_taxinvoice_info->cancelled_by_user); ?></span>
    <?php } ?>

</div>