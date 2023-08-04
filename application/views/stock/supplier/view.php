<div class="page-title clearfix no-border bg-off-white">
    <h1>
        <a class="title-back" href="<?php echo get_uri('stock/suppliers'); ?>">
            <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_supplier_details') . " - " . $supplier_info->company_name; ?>
    </h1>
</div>

<div id="page-content" class="clearfix">
    <ul id="client-tabs" data-toggle="ajax-tab" class="nav nav-tabs" role="tablist" style="background: #ffffff;">
        <li>
            <a role="presentation" href="<?php echo_uri("stock/supplier_contacts/" . $supplier_info->id); ?>" data-target="#supplier-contacts">
                <?php echo lang('stock_contacts'); ?>
            </a>
        </li>
        <li>
            <a role="presentation" href="<?php echo_uri("stock/supplier_info/" . $supplier_info->id); ?>" data-target="#supplier-info">
                <?php echo lang('stock_supplier_info'); ?>
            </a>
        </li>
        <?php if (!isset($hidden_menu) || !in_array('supplier-pricings', $hidden_menu)): ?>
            <li>
                <a role="presentation" href="<?php echo_uri("stock/supplier_pricings/" . $supplier_info->id); ?>" data-target="#supplier-pricings">
                    <?php echo lang('stock_supplier_pricings'); ?>
                </a>
            </li>
            <li>
                <a role="presentation" href="<?php echo_uri("stock/supplier_fg_pricings/" . $supplier_info->id); ?>" data-target="#supplier-fg-pricings">
                    <?php echo lang('stock_supplier_fg_pricings'); ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a role="presentation" href="<?php echo_uri("stock/supplier_files/" . $supplier_info->id); ?>" data-target="#supplier-files">
                <?php echo lang('files'); ?>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="supplier-contacts"></div>
        <div role="tabpanel" class="tab-pane fade" id="supplier-info"></div>
        <div role="tabpanel" class="tab-pane fade" id="supplier-pricings"></div>
        <div role="tabpanel" class="tab-pane fade" id="supplier-fg-pricings"></div>
        <div role="tabpanel" class="tab-pane fade" id="supplier-files"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        setTimeout(function () {
            let tab = "<?php echo $tab; ?>";
            if (tab === "info") {
                $("[data-target=#supplier-info]").trigger("click");
            } else if (tab === "contacts") {
                $("[data-target=#supplier-contacts]").trigger("click");
            } else if (tab === "pricings") {
                $("[data-target=#supplier-pricings]").trigger("click");
            } else if (tab === "files") {
                $("[data-target=#supplier-files]").trigger("click");
            } else if (tab === "fg-pricings") {
                $("[data-target=#supplier-fg-pricings]").trigger("click");
            }
        }, 210);
    });
</script>