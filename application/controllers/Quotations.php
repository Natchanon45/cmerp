<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quotations extends MY_Controller {
    function __construct() {
        parent::__construct();
    }

    function index() {
        if($this->uri->segment(3) == "igrid"){
            echo json_encode(["data"=>$this->Quotations_m->igrid()]);
            return;
        }

        
        $this->template->rander("quotations/index");
    }

    function doc(){
        if(isset($this->json->task)){
            if($this->json->task == "save") echo json_encode($this->Quotations_m->saveDoc());
            return;   
        }

        $data = $this->Quotations_m->doc($this->input->post("id"));

        $this->load->view( 'quotations/doc', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") echo json_encode($this->Quotations_m->items());
            return;
        }

        $data = $this->Quotations_m->doc($this->uri->segment(3));
        if ($data["status"] == "success") {
            $data["created"] = $this->Users_m->getInfo($data["qrow"]->created_by);
            $data["client"] = $this->Clients_m->getInfo($data["qrow"]->client_id);
            $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);
            if($data["client"] != null) $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);

            $this->template->rander("quotations/view", $data );
            return;
        }

        redirect('/quotations');
    }


    function delete_doc() {
        if($this->input->post('undo') == true){
            echo json_encode($this->Quotations_m->undoDoc());
            return;
        }

        echo json_encode($this->Quotations_m->deleteDoc());
        return;
    }

    function items(){
        echo json_encode($this->Quotations_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") echo json_encode($this->Quotations_m->saveItem());
            return;   
        }

        if($this->input->get("task") != null){
            if($this->input->get("task") == "suggest_products"){
                $sprows = $this->Products_m->getRows();
                if(!empty($sprows)){
                    foreach($sprows as $sprow){
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description"=>$sprow->description];
                    }
                }
                //$suggestion[] = array("id" => "", "text" => "+ " . lang("create_new_item"));
                echo json_encode($suggestion);
            }
            return;
        }

        $data = $this->Quotations_m->item();

        $this->load->view('quotations/item', $data);
    }


    

    function jdoc(){
        echo json_encode($this->Quotations_m->jDoc());
    }

    

    

    function jdelete_item(){
        echo json_encode($this->Estimate_m->deleteItem());
    }
	
	
}