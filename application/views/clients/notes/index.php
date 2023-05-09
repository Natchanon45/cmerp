<div class="panel">
    <div class="tab-title clearfix">
        <h4><?php echo lang('notes'); ?></h4>
        <div class="title-button-group">
            <?php echo modal_anchor(get_uri("notes/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_note'), array("class" => "btn btn-default", "title" => lang('add_note'), "data-post-client_id" => $client_id)); ?>           
        </div>
    </div>
    <div class="table-responsive">
        <table id="note-table" class="display" cellspacing="0" width="100%">            
        </table>
    </div>
</div>

<style>
.fit-content-15p  {
    width: 15%;
}
.fit-content-20p  {
    width: 20%;
}
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $("#note-table").appTable({
            source: '<?php echo_uri("notes/list_data_leads/" . $client_id) ?>',
            order: [[0, 'desc']],
            columns: [
                // { targets: [1], visible: false },
                { title: '<?php echo lang("created_date"); ?>', "class": "fit-content-15p" },
                { title: '<?php echo lang("title"); ?>' },
                { title: "<?php echo lang("created_by"); ?>", "class": "fit-content-20p" },
                { title: "<?php echo lang("file"); ?>" },
                { title: "<i class='fa fa-bars'></i>", "class": "text-center option w120" }
            ]
        });
    });
</script>