<style type="text/css">
.popup .tabs{
    margin-bottom: 12px;
}

.popup .tabs li{
    float: left;
    width: fit-content;
    border: 1px solid #ccc;
    border-radius: 22px;
}

.popup .tabs li a{
    display: block;
    padding: 4px 16px;
    padding-top: 5px;
    color: #333;
}

.popup .tabs li.active a{
    cursor: default !important;
}

.popup .tabs li a:hover{
    cursor: pointer;
}

.popup .link{
    margin-top: 28px;
}

.popup .link label,
.popup .link input,
.popup .link #generate_link{
    display: inline-block;
}

#generate_link{
    width: 90px;
    position: relative;
    top: 3px;
    text-align: right;
}

.popup .link label{
    width: 90px;
}

.popup .link input[type=text]{
    width: calc(100% - 190px);
    background: #f6f7fb;
}

.popup .link #generate_link i{
    font-style: normal;
    margin-left: 4px;
    position: relative;
    top: -2px;
}

</style>
<div class="popup">
    <div class="container">
        <ul class="tabs clear">
            <li class="active custom-bg01"><a class="custom-color">แชร์เอกสาร</a></li>
        </ul>
        <div class="link">
            <p>
                <label>ลิงก์เอกสาร:</label>
                <input type="text" class="custom-color-input" readonly>
                <span id="generate_link"><input type="checkbox"><i>สร้างลิงก์</i></span>
            </p>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#generate_link input").change(function() {
        axios.post("<?php echo current_url(); ?>", {
            task: "gen_key",
            doc_id: "<?php if(isset($doc_id)) echo $doc_id; ?>",
            gen_key: this.checked
        }).then(function (response) {
            data = response.data;
            alert(data.message);
        }).catch(function (error) {});
    });
});
</script>