<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Share extends MY_Controller2 {
    function __construct() {
        parent::__construct();
    }

    function quotation(){
        $data = $this->Quotations_m->getEdoc(null, $this->uri->segment(5));
        $this->load->view('edocs/quotation', $data);
    }

    
}