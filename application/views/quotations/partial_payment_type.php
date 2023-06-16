<style type="text/css">
.modal-content{
    width: 320px;
    margin: 40% auto;
}

.pt{
    padding-top: 30px;
}

.pt1{
    text-align: right;
}

</style>
<div class="pt general-form modal-body clearfix">
    <div class="form-group">
        <div class="col-md-2 pt1"><input type="radio" name="patials_type" value="P" checked></div>
        <div class="col-md-10 pt2">แบ่งจ่ายใบวางบิลเป็นเปอร์เซ็นต์</div>
    </div>
    <div class="form-group">
        <div class="col-md-2 pt1"><input type="radio" name="patials_type" value="A"></div>
        <div class="col-md-10 pt2">แบ่งจ่ายใบวางบิลเป็นจำนวนเงิน</div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> ยกเลิก</button>
    <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> สร้างเอกสาร</button>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#btnSubmit").click(function() {
        axios.post("<?php echo current_url(); ?>", {
            task: "update_doc_status",
            doc_id: "<?php if(isset($doc_id)) echo $doc_id; ?>",
            update_status_to: "P",
            patials_type: $("input[name='patials_type']:checked").val()
        }).then(function (response) {
            data = response.data;
            if(data.status == "success"){
                window.location = data.url;
            }else{
                alert(data.message);
            }
        }).catch(function (error) {});
    });
});


</script>