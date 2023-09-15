<div class="modal-body clearfix">
    <input type="hidden" id="project_id" value="<?php echo $project_id; ?>">
    <input type="hidden" id="project_name" value="<?php echo $project_name; ?>">
    <input type="hidden" id="post_url" value="<?php echo get_uri("projects/production_order_mr_creation_all_post"); ?>">
    <div class="p3">
        <p style="font-size: 110%;">ต้องการสร้างใบเบิกวัตถุดิบสำหรับทุกรายการที่มีสต๊อกใช่หรือไม่?</p>
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
const projectName = document.querySelector("#project_name").value;
const postUrl = document.querySelector("#post_url").value;

async function mrCreationAll() {
    let url = postUrl;
    let req = {
        projectId: projectId,
        projectName: projectName
    };

    await axios.post(url, req).then(res => {
        const { success, target } = res.data;
        
        if (success) {
            window.open(target, "_blank");
            window.parent.loadProductionOrderList();
        } else {
            window.parent.loadProductionOrderList();
        }
    }).catch(err => {
        console.log(err);
    });
}

$(document).ready(function () {
    $("#btn-submit").on("click", async function () {
        await mrCreationAll();
    });
});
</script>
