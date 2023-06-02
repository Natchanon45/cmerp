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
        $data["module"] = "quotations";

        if($this->uri->segment(3) != null) $data["module"] = $this->uri->segment(3);

        $cusrows = $this->Customers_m->getRows(["id", "company_name"]);
        $client_ids[] = ["id"=>"", "text"=>"-- ลูกค้า --"];
        if(!empty($cusrows)){
            foreach($cusrows as $cusrow){
                $client_ids[] = ["id"=>$cusrow->id, "text"=>$cusrow->company_name];
            }
        }

        $data["client_ids"] = json_encode($client_ids);

        $this->template->rander("accounting/sell", $data);
    }

    //บัญชีซื้อ
    function buy(){
        $this->template->rander("accounting/buy");
    }
}