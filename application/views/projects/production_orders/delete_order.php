<div class="modal-body clearfix">
    <input type="hidden" id="project_id" value="<?php echo $project_id; ?>">
    <input type="hidden" id="production_id" value="<?php echo $id; ?>">
    <input type="hidden" id="post_url" value="<?php echo get_uri("projects/production_order_delete_post"); ?>">
    <div class="p3">
        <p style="font-size: 110%;"><?php echo lang("production_order_delete_question"); ?></p>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("no"); ?>
    </button>

    <button type="button" class="btn btn-primary" id="btn-submit" data-dismiss="modal">
        <span class="fa fa-check-circle"></span> 
        <?php echo lang("yes"); ?>
    </button>
</div>

<script type="text/javascript">
const projectId = document.querySelector("#project_id").value;
const productionId = document.querySelector("#production_id").value;
const postUrl = document.querySelector("#post_url").value;

async function mrCreationAll() {
    let url = postUrl;
    let req = {
        projectId: projectId,
        productionId: productionId
    };

    await axios.post(url, req).then(res => {
        const { success } = res.data;
        
        if (success) {
            appAlert.success("Order has been deleted.", { duration: 3001 });
        } else {
            appAlert.error("Something went wrong!", { duration: 3001 });
        }
        window.parent.loadProductionOrderList();
    }).catch(err => {
        console.log(err);

        appAlert.error("500 Internal Server Error.", { duration: 3001 });
        window.parent.loadProductionOrderList();
    });
}

$(document).ready(function () {
    $("#btn-submit").on("click", async function () {
        await mrCreationAll();
    });
});
</script>
