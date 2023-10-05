<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Receipts extends MY_Controller {
    function __construct() {
        parent::__construct();

        if($this->Permission_m->accounting["receipt"]["access"] != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/sell"));
        }

        $this->data["company_setting"] = $this->Settings_m->getCompany();
    }

    function index() {
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Receipts_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Receipts_m->updateStatus());
            return;    
        }

        redirect("/accounting/sell/receipts");
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "save_doc") jout($this->Receipts_m->saveDoc());
            return;   
        }

        $data = $this->Receipts_m->getDoc($this->input->post("id"));

        $this->load->view( 'receipts/addedit', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Receipts_m->items());
            if($this->json->task == "update_doc") jout($this->Receipts_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Receipts_m->deleteItem());
            return;
        }

        if(empty($this->uri->segment(3))){
            redirect(get_uri("accounting/sell"));
            return;
        }

        $data = $this->Receipts_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect(get_uri("accounting/sell"));
            return;
        }

        $data["company_setting"] = $this->Settings_m->getCompany();
        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["approved"] = $this->Users_m->getInfo($data["approved_by"]);
        $data["client"] = $this->Customers_m->getInfo($data["customer_id"]);
        $data["client_contact"] = $this->Customers_m->getContactInfo($data["client_id"]);
        $data["print_url"] = get_uri("receipts/print/".str_replace("=", "", base64_encode($data['doc_id'].':'.$data['doc_number'])));

        $this->template->rander("receipts/view", $data);
    }

    function print(){
        $this->data["doc"] = $doc = $this->Receipts_m->getEdoc($this->uri->segment(3), null);
        if($doc["status"] != "success") redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/receipt', $this->data);
    }

    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Receipts_m->undoDoc());
            return;
        }

        jout($this->Receipts_m->deleteDoc());
    }

    function items(){
        jout($this->Receipts_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") jout($this->Receipts_m->saveItem());
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

        $data = $this->Receipts_m->item();

        $this->load->view('receipts/item', $data);
    }

    function share(){
        if(isset($this->json->task)){
            if($this->json->task == "gen_sharekey") jout($this->Receipts_m->genShareKey());
            return;   
        }
        
        $data = $this->Receipts_m->getDoc($this->input->post("doc_id"));
        $this->load->view('receipts/share', $data);
    }
}