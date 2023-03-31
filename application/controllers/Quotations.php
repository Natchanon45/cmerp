<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quotations extends MY_Controller {

    function __construct() {
        parent::__construct();
       
        $this->load->model("Quotations_m");
        $this->load->model("Clients_m");
        $this->load->model("Users_m");
    }

    function index() {
        if($this->uri->segment(3) == "jisource"){
            echo $this->Quotations_m->jisource();
            return;
        }

        $this->check_module_availability("module_estimate");
        
        $this->template->rander("quotations/index");
    }

    function view($doc_id) {
        $data = $this->Quotations_m->doc($doc_id);
        if ($data["success"] == true) {
            $data["created"] = $this->Users_m->getInfo($data["qrow"]->created_by);
            $data["client"] = $this->Clients_m->getInfo($data["qrow"]->client_id);
            $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);
            if($data["client"] != null) $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);

            $this->template->rander("quotations/view", $data );
            return;
        }

        redirect('/quotations');
    }


    function delete() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Estimates_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Estimates_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    function jdoc(){
        echo $this->Estimate_m->jDoc();
    }

    function jitems(){
        echo $this->Estimate_m->jItems();
    }

    function save_item(){
        echo json_encode($this->Estimate_m->saveItem());
    }

    function jdelete_item(){
        echo json_encode($this->Estimate_m->deleteItem());
    }
	
	
}