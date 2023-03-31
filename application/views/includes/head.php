<head>
<script src="https://unpkg.com/axios@1.1.2/dist/axios.min.js"></script>
<script src="/assets/js/util.js"></script>
<?php $this->load->view('includes/meta'); ?>
<?php $this->load->view('includes/helper_js'); ?>
<?php $this->load->view('includes/plugin_language_js'); ?>

<?php

$css_files = array(
    "assets/bootstrap/css/bootstrap.min.css",
    "assets/js/font-awesome/css/font-awesome.min.css", //don't combine this css because of the fonts path
    "assets/js/datatable/css/jquery.dataTables.min.css", //don't combine this css because of the images path
    "assets/js/select2/select2.css", //don't combine this css because of the images path
    "assets/js/select2/select2-bootstrap.min.css",
    "assets/css/app.all.css"
    
);

if(!isset($kpage)){
    array_push($css_files, "assets/css/grids.css");
}


if (get_setting("rtl")) {
    array_push($css_files, "assets/css/rtl.css");
}

array_push($css_files, "assets/css/custom-style.css"); //add to last. custom style should not be merged


load_css($css_files);

load_js(array(
    "assets/js/app.all.js",
    "assets/js/custom.js"
));
?>

<?php $this->load->view("includes/csrf_ajax"); ?>
<?php $this->load->view("includes/custom_head"); ?>
</head>