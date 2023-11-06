<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sfg extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("Account_category_model");
    }

    function index(){
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->indexDataSet()]);
            return;
        }

        $this->template->rander("sfg/index", []);
    }

    function addedit(){
        /*if(isset($this->json->task)){
            jout($this->Sfg_m->saveDoc());
            return;   
        }*/
        //$this->load->library("uri");
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save")jout($this->Sfg_m->saveDoc());
            if($task == "upload_file") upload_file_to_temp();
            if($task == "validate_items_file"){
                $file_name = $this->input->post("file_name");
                if (!is_valid_file_to_upload($file_name)) {
                    echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
                    exit();
                }
                if (is_image_file($file_name)) {
                    echo json_encode(array("success" => true));
                } else {
                    echo json_encode(array("success" => false, 'message' => lang('please_upload_valid_image_files')));
                }
            }

            return;
        }

        

        $view_data['model_info'] = $this->Items_model->get_one($this->input->post('id'));
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));
        $view_data["account_category"] = $this->Account_category_model->get_list_dropdown();

        $this->load->view('sfg/addedit', $view_data);
    }
}