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
.popup .link input{
    display: inline-block;
}

.popup .link input[type=text]{
    width: calc(100% - 90px);
    background: #f6f7fb;
    margin-left: 4px;
}

.popup .link .buttons{
    display: block;
    margin-left: 81px;
    margin-top: 12px;
}

#generate_link{
    display: block;
    float: left;
    width: 50%;
}

#copy_button{
    display: block;
    float: right;
    width: 50%;
    text-align: right;
}

.popup .link #generate_link i{
    font-style: normal;
    margin-left: 4px;
    position: relative;
    top: -2px;
}


#generate_link{
    margin-top: 6px;
}

#copy_button a{
    position: relative;
    display: inline-block;
    padding: 5px 10px;
    border-radius: 16px;
}

#copy_button a:active{
    top: 1px;
}
</style>
<div class="popup">
    <div class="container">
        <ul class="tabs clear">
            <li class="active custom-bg01"><a class="custom-color">แชร์เอกสาร</a></li>
        </ul>
        <div class="link">
            <label>ลิงก์เอกสาร:</label>
            <input type="text" id="share_link" class="custom-color-input" value="<?php echo $share_link; ?>" readonly>
            <div class='buttons clear'>
                <span id="generate_link"><input type="checkbox" <?php if($share_link != null) echo "checked"; ?>><i>สร้างลิงก์และคัดลอกลิงก์</i></span>
                <span id="copy_button"><a class="custom-color-button">คัดลอกลิงค์</a></span>
            </div>
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
            task: "gen_sharekey",
            doc_id: "<?php if(isset($doc_id)) echo $doc_id; ?>",
            gen_key: this.checked
        }).then(function (response) {
            data = response.data;
            if(typeof data.sharelink != "undefined"){
                $("#share_link").val(data.sharelink);
            }else{
                $("#share_link").val("");
            }
        }).catch(function (error) {});
    });

    $("#copy_button").click(function() {
        if($("#share_link").val() != ""){
            $("#share_link").select();
            document.execCommand('copy');
        }
    });
});
</script>