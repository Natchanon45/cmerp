<div class="panel panel-default  p15 no-border m0">
    <span><?php echo lang("status") . ": " . $delivery_status_label; ?></span>
    <span class="ml15">
        <?php
        if ($delivery_info->is_lead) {
            echo lang("lead") . ": ";
            echo (anchor(get_uri("leads/view/" . $delivery_info->client_id), $delivery_info->company_name));
        } else {
            echo lang("client") . ": ";
            echo (anchor(get_uri("clients/view/" . $delivery_info->client_id), $delivery_info->company_name));
        }
        ?>
    </span>
    <span class="ml15"><?php
        echo lang("last_email_sent") . ": ";
        echo (is_date_exists($delivery_info->last_email_sent_date)) ? format_to_date($delivery_info->last_email_sent_date, FALSE) : lang("never");
        ?>
    </span>
    <?php if (!$delivery_info->delivery_request_id == 0) {
        ?>
        <span class="ml15">
            <?php
            echo lang("delivery_request") . ": ";
            echo (anchor(get_uri("delivery_requests/view_delivery_request/" . $delivery_info->delivery_request_id), lang('delivery_request') . " - " . $delivery_info->delivery_request_id));
            ?>
        </span>
        <?php
    }
    ?>
    <span class="ml15"><?php
        if ($delivery_info->project_id) {
            echo lang("project") . ": ";
            echo (anchor(get_uri("projects/view/" . $delivery_info->project_id), $delivery_info->project_title));
        }
        ?>
    </span>
</div>