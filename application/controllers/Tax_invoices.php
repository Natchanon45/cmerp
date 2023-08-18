<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tax_invoices extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        if($this->Permission_m->accounting["invoice"]["access"] != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/sell"));
        }

        $this->data["company_setting"] = $this->Settings_m->getCompany();
    }

    function index() {
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Tax_invoices_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "get_doc") jout($this->Tax_invoices_m->getDoc($this->json->doc_id));
            if($this->json->task == "update_doc_status") jout($this->Tax_invoices_m->updateStatus());
            return;    
        }

        redirect("/accounting/sell/invoices");
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "get_invs"){
                jout($this->Tax_invoices_m->getHTMLInvoices($this->input->post("customer_id")));
            }elseif($this->json->task == "create_doc"){
                jout($this->Tax_invoices_m->createDocByInvoiceId($this->json->invoice_id));
            }

            return;
        }

        $data = $this->Tax_invoices_m->getDoc($this->input->post("id"));
        $data["cusrows"] = $this->Customers_m->getRows();

        $this->load->view('tax_invoices/addedit', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Tax_invoices_m->items());
            if($this->json->task == "update_doc") jout($this->Tax_invoices_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Tax_invoices_m->deleteItem());
            return;
        }

        if(empty($this->uri->segment(3))){
            redirect(get_uri("accounting/sell"));
            return;
        }

        $data = $this->Tax_invoices_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect(get_uri("accounting/sell"));
            return;
        }

        $data["company_setting"] = $this->Settings_m->getCompany();
        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["client"] = $this->Customers_m->getInfo($data["customer_id"]);
        $data["client_contact"] = $this->Customers_m->getContactInfo($data["client_id"]);
        $data["print_url"] = get_uri("invoices/print/".str_replace("=", "", base64_encode($data['doc_id'].':'.$data['doc_number'])));

        $this->template->rander("tax_invoices/view", $data);
    }

    function print(){
        $this->data["doc"] = $doc = $this->Tax_invoices_m->getEdoc($this->uri->segment(3), null);
        if($doc["status"] != "success") redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/invoice', $this->data);
    }

    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Tax_invoices_m->undoDoc());
            return;
        }

        jout($this->Tax_invoices_m->deleteDoc());
    }

    function items(){
        jout($this->Tax_invoices_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") jout($this->Tax_invoices_m->saveItem());
            return;   
        }

        if($this->input->get("task") != null){
            if($this->input->get("task") == "suggest_products"){
                $sprows = $this->Products_m->getRows();
                if(!empty($sprows)){
                    foreach($sprows as $sprow){
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description"=>$sprow->description, "unit"=>$sprow->unit_type, "price"=>$sprow->rate];
                    }
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Tax_invoices_m->item();

        $this->load->view('tax_invoices/item', $data);
    }

    function share(){
        if(isset($this->json->task)){
            if($this->json->task == "gen_sharekey") jout($this->Tax_invoices_m->genShareKey());
            return;   
        }
        
        $data = $this->Tax_invoices_m->getDoc($this->input->post("doc_id"));
        $this->load->view('tax_invoices/share', $data);
    }
}