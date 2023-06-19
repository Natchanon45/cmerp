<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Share extends MY_Controller2 {
    function __construct() {
        parent::__construct();
    }

    function quotation($sharekey){
        $data = $this->Quotations_m->getDocBySharekey($sharekey);

        $this->load->view('edocs/quotation', $data);
    }

    
}