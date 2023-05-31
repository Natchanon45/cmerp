<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accounting extends MY_Controller {
    function __construct() {
        parent::__construct();
    }

    function index(){
        redirect("/accounting/sell");
        //$this->template->rander("accounting/index");
    }

    //ผังบัญชี
    function chart(){
        $this->template->rander("accounting/chart");
    }

    //บัญชีขาย
    function sell(){
        $this->template->rander("accounting/sell");
    }

    //บัญชีซื้อ
    function buy(){
        $this->template->rander("accounting/buy");
    }
}