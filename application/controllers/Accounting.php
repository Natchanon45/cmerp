<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accounting extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        /*if($this->Permission_m->canAccessAccounting() != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("/");
        }*/
        $this->load->model('Purchaserequest_m');

        $this->data["company_setting"] = $this->Settings_m->getCompany();
    }

    function index(){
        redirect("/accounting/sell");
    }

    //ผังบัญชี
    function chart(){
        $this->template->rander("accounting/chart");
    }

    //บัญชีขาย
    function sell(){
        if($this->Permission_m->accounting["sales_order"]["access"] == true){
            $this->data["module"] = "sales-orders";
        }elseif($this->Permission_m->accounting["quotation"]["access"] == true){
            $this->data["module"] = "quotations";
        }elseif($this->Permission_m->accounting["invoice"]["access"] == true){
            $this->data["module"] = "invoices";
        }elseif($this->Permission_m->accounting["tax_invoice"]["access"] == true){
            $this->data["module"] = "tax-invoices";
        }elseif($this->Permission_m->accounting["billing_note"]["access"] == true){
            $this->data["module"] = "billing-notes";
        }elseif($this->Permission_m->accounting["receipt"]["access"] == true){
            $this->data["module"] = "receipts";
        }elseif($this->Permission_m->accounting["credit_note"]["access"] == true){
            $this->data["module"] = "credit-notes";
        }else{
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("/");
            return;
        }

        if($this->uri->segment(3) != null) $this->data["module"] = $this->uri->segment(3);
        else $this->data["module"] = "sales-orders";

        $cusrows = $this->Customers_m->getRows(["id", "company_name"]);
        $client_ids[] = ["id"=>"", "text"=>"-- ลูกค้า --"];
        if(!empty($cusrows)){
            foreach($cusrows as $cusrow){
                $client_ids[] = ["id"=>$cusrow->id, "text"=>$cusrow->company_name];
            }
        }

        /*$pmrows = $this->Payments_m->getRows();
        $payment_method_ids[] = ["id"=>"", "text"=>"-- การชำระเงิน --"];
        if(!empty($pmrows)){
            foreach($pmrows as $pmrow){
                $payment_method_ids[] = ["id"=>$pmrow->id, "text"=>$pmrow->title];
            }
        }*/

        $this->data["client_ids"] = json_encode($client_ids);
        $this->data["billing_type"] = $this->data["company_setting"]["company_billing_type"];

        $this->template->rander("accounting/sell", $this->data);
    }

    //บัญชีซื้อ
    function buy() {
        if ($this->check_permission('access_purchase_request')) {
            $this->data['module'] = 'purchase_request';
        } elseif ($this->check_permission('access_purchase_order')) {
            $this->data['module'] = 'purchase_order';
        } elseif ($this->check_permission('access_goods_receipt')) {
            $this->data['module'] = 'goods_receipt';
        }elseif ($this->check_permission('access_purchase_order')) {
            $this->data['module'] = 'payment_voucher';
        }else {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect('/');
            return;
        }

        if($this->uri->segment(3) != null) $this->data["module"] = $this->uri->segment(3);
        $this->data['permissions'] = $this->Permission_m->permissions;

        // Supplier Dropdown
        $this->data['supplier_dropdown'] = json_encode($this->Bom_suppliers_model->dev2_getSupplierDropdownWithCode());
        // PR Status Dropdown
        $this->data['status_dropdown'] = json_encode($this->Purchaserequest_m->dev2_getPrStatusDropdown());
        // PR Type Dropdown
        $this->data['type_dropdown'] = json_encode($this->Purchaserequest_m->dev2_getPrTypeDropdown());

        // var_dump(arr($this->data)); exit();
        $this->template->rander("accounting/buy", $this->data);
    }

}
