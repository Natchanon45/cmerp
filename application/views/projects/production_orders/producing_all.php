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

    .pill-warning {
        color: #fff;
        background-color: #ffc107;
    }
</style>

<div class="modal-body clearfix">
    <input type="hidden" id="project_id" value="<?php echo $project_id; ?>">
    <input type="hidden" id="target_url" value="<?php echo get_uri("projects/production_order_change_to_producing_all_post"); ?>">

    <div class="p3">
        <p style="font-size: 110%;"><?php echo lang("production_order_want_to_chage"); ?> <span class="pill pill-warning"><?php echo lang("production_order_producing"); ?></span> <?php echo lang("production_order_all_of_order"); ?></p>
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

<?php
echo modal_anchor(
    get_uri("projects/production_order_modal_error"),
    "production_order_modal_error",
    array(
        "class" => "btn btn-default hide",
        "title" => lang("production_order_state_change"),
        "data-title" => lang("production_order_state_change"),
        "data-post-message" => lang("production_order_cannot_change_status"),
        "id" => "btn-failure"
    )
);
?>

<script type="text/javascript">
const btnFailure = document.querySelector("#btn-failure");
const projectId = document.querySelector("#project_id").value;
const targetUrl = document.querySelector("#target_url").value;

async function producingAll() {
    let url = targetUrl;
    let req = {
        projectId: projectId
    };

    await axios.post(url, req).then(res => {
        const { success } = res.data;
        // console.log(success);
        
        if (!success) {
            btnFailure.click();
        }
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
