<span class="sharefrom">ผู้ใช้งาน <?php echo $this->Users_m->getInfo($doc["sharekey_by"])["email"]; ?> ได้แชร์เอกสารนี้ให้คุณจาก <?php echo get_setting("company_name"); ?>, </span>
<span class="copyright">&copy; <?php echo date("Y") ?> Copyright Cosmatch Inter Group all rights reserved</span>

<script type="text/javascript">
window.addEventListener('keydown', function(event) {
    if (event.keyCode === 80 && (event.ctrlKey || event.metaKey) && !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
        event.preventDefault();
        if (event.stopImmediatePropagation)event.stopImmediatePropagation();
        else event.stopPropagation();
        return;
    }
}, true);
</script>