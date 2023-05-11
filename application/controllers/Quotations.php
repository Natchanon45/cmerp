<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quotations extends MY_Controller {
    function __construct() {
        parent::__construct();
    }

    function index() {
        
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Quotations_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Quotations_m->updateStatus());
            return;    
        }

        $this->template->rander("quotations/index");
    }

    function test(){
        //jout(["data"=>$this->Quotations_m->indexDataSet()]);
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "save_doc") jout($this->Quotations_m->saveDoc());
            return;   
        }

        $data = $this->Quotations_m->getDoc($this->input->post("id"));

        $this->load->view( 'quotations/addedit', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Quotations_m->items());
            if($this->json->task == "load_summary") jout($this->Quotations_m->summary());
            if($this->json->task == "update_doc") jout($this->Quotations_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Quotations_m->deleteItem());
            return;
        }

        if(empty($this->uri->segment(3))){
            redirect('/quotations');
            return;
        }

        $data = $this->Quotations_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect('/quotations');
            return;
        }

        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["client"] = $this->Clients_m->getInfo($data["client_id"]);
        if($data["client"] != null) $data["client_contact"] = $this->Clients_m->getContactInfo($data["client_id"]);

        $this->template->rander("quotations/view", $data);
    }


    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Quotations_m->undoDoc());
            return;
        }

        jout($this->Quotations_m->deleteDoc());
    }

    function items(){
        jout($this->Quotations_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") jout($this->Quotations_m->saveItem());
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
                //$suggestion[] = array("id" => "", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Quotations_m->item();

        $this->load->view('quotations/item', $data);
    }
}