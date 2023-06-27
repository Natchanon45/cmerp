<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accounting extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        /*if($this->Permission_m->canAccessAccounting() != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("/");
        }*/
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
        if($this->Permission_m->accounting["quotation"]["access"] == true){
            $this->data["module"] = "quotations";
        }elseif($this->Permission_m->accounting["billing_note"]["access"] == true){
            $this->data["module"] = "billing-notes";
        }elseif($this->Permission_m->accounting["invoice"]["access"] == true){
            $this->data["module"] = "invoices";
        }elseif($this->Permission_m->accounting["receipt"]["access"] == true){
            $this->data["module"] = "receipts";
        }else{
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("/");
            return;
        }

        if($this->uri->segment(3) != null) $this->data["module"] = $this->uri->segment(3);

        $cusrows = $this->Customers_m->getRows(["id", "company_name"]);
        $client_ids[] = ["id"=>"", "text"=>"-- ลูกค้า --"];
        if(!empty($cusrows)){
            foreach($cusrows as $cusrow){
                $client_ids[] = ["id"=>$cusrow->id, "text"=>$cusrow->company_name];
            }
        }

        $this->data["client_ids"] = json_encode($client_ids);

        $this->template->rander("accounting/sell", $this->data);
    }

    //บัญชีซื้อ
    function buy(){
        $this->template->rander("accounting/buy");
    }
}