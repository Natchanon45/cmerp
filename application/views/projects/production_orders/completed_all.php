<style type="text/css">
    .pill {
        display: inline;
        outline: none;
        padding: .5em .65em;
        font-size: 90%;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border: none;
        border-radius: .65rem;
    }

    .pill-success {
        color: #fff;
        background-color: #28a745;
    }
</style>

<div class="modal-body clearfix">
    <input type="hidden" id="project_id" value="<?php echo $project_id; ?>">
    <input type="hidden" id="target_url" value="<?php echo get_uri("projects/production_order_change_to_completed_all_post"); ?>">

    <div class="p3">
        <p style="font-size: 110%;">ต้องการเปลี่ยนสถานะการผลิตเป็น <span class="pill pill-success">ผลิตเสร็จแล้ว</span> ทุกรายการใช่หรือไม่?</p>
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
const targetUrl = document.querySelector("#target_url").value;

async function producingAll() {
    let url = targetUrl;
    let req = {
        projectId: projectId
    };

    await axios.post(url, req).then(res => {
        // console.log(res);
        window.parent.loadProductionOrderList();
    }).catch(err => {
        console.log(err);
    });
}

$(document).ready(function () {
    $("#btn-submit").on("click", async function () {
        await producingAll();
    });
});
</script>
