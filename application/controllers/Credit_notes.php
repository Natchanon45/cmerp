<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Credit_notes extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        if($this->Permission_m->accounting["credit_note"]["access"] != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/sell"));
        }
    }

    function index() {
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Credit_notes_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Credit_notes_m->updateStatus());
            return;    
        }

        redirect("/accounting/sell/credit_notes");
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "get_invs"){
                jout($this->Credit_notes_m->getHTMLInvoices($this->input->post("customer_id")));
            }elseif($this->json->task == "create_doc"){
                jout($this->Credit_notes_m->createDocByInvoiceId($this->json->invoice_id));
            }

            return;
        }

        $data = $this->Credit_notes_m->getDoc($this->input->post("id"));
        $data["cusrows"] = $this->Customers_m->getRows();

        $this->load->view('credit_notes/addedit', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Credit_notes_m->items());
            if($this->json->task == "update_doc") jout($this->Credit_notes_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Credit_notes_m->deleteItem());
            return;
        }

        $data = $this->Credit_notes_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect(get_uri("accounting/sell"));
            return;
        }

        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["client"] = $this->Customers_m->getInfo($data["customer_id"]);
        $data["client_contact"] = $this->Customers_m->getContactInfo($data["client_id"]);
        $data["print_url"] = get_uri("credit_notes/print/".str_replace("=", "", base64_encode($data['doc_id'].':'.$data['doc_number'])));

        $this->template->rander("credit_notes/view", $data);
    }

    function print(){
        $this->data["doc"] = $doc = $this->Credit_notes_m->getEdoc($this->uri->segment(3), null);
        if($doc["status"] != "success") redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/credit_note', $this->data);
    }

    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Credit_notes_m->undoDoc());
            return;
        }

        jout($this->Credit_notes_m->deleteDoc());
    }

    function items(){
        jout($this->Credit_notes_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") jout($this->Credit_notes_m->saveItem());
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

        $data = $this->Credit_notes_m->item();

        $this->load->view('credit_notes/item', $data);
    }

    function share(){
        if(isset($this->json->task)){
            if($this->json->task == "gen_sharekey") jout($this->Credit_notes_m->genShareKey());
            return;   
        }
        
        $data = $this->Credit_notes_m->getDoc($this->input->post("doc_id"));
        $this->load->view('credit_notes/share', $data);
    }
}