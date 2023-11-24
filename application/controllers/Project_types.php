<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Project_types extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin();
    }

    function index($tab = "") {
        $view_data["tab"] = $tab;
        $this->template->rander("project_types/index", $view_data);
    }

    function modal_form() {
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data = null;

        if($this->input->post("id") != null) $view_data['prow'] = $this->Projects_m->getTypeRow($this->input->post("id"));

        $this->load->view('project_types/modal_form', $view_data);
    }

    function save() {
        /*validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));*/


        $id = $this->input->post('id');
        $data = array("title" => $this->input->post('title'));
        $save_id = $this->Projects_m->saveType($data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function delete() {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        $undo = $this->input->post('undo');

        if($undo == true){
            /*if ($this->Projects_m->deleteType($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }*/


            $this->db->where("id", $id);
            $this->db->update("project_types", ["deleted"=>0]);
            jout(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            return;

        }else{
            /*if ($this->Projects_m->deleteType($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }*/

            $this->db->where("id", $id);
            $this->db->update("project_types", ["deleted"=>1]);
            jout(array("success" => true, 'message' => lang('record_deleted')));
        }

        return;
    }

    function list_data() {
        $list_data = $this->Projects_m->getTypeRows();
        $result = array();

        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id) {
        $data = $this->Projects_m->getTypeRow($id);
        return $this->_make_row($data);
    }

    private function _make_row($data) {
        return array($data->title,
            modal_anchor(get_uri("project_types/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => "แก้ไขประเภทโปรเจค", "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('project_types'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("project_types/delete"), "data-action" => "delete"))
        );
    }

}