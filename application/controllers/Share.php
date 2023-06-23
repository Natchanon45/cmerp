<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Share extends PublicController {
    function __construct() {
        parent::__construct();
    }

    function quotation(){
        $this->data["doc"] = $doc = $this->Quotations_m->getEdoc(null, $this->uri->segment(5));
        if($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/quotation', $this->data);
    }
}