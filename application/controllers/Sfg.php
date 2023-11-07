<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
use Laminas\Barcode\Barcode;

class Sfg extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("Account_category_model");
    }

    function barcode($barcode){
        $databarcode = ['text' => $barcode, 'drawText' => true,];
        $rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
        Barcode::render('code128', 'image', $databarcode, $rendererOptions);
    }

    function index(){
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->indexDataSet()]);
            return;
        }

        $this->template->rander("sfg/index", []);
    }

    function addedit(){
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save")jout($this->Sfg_m->saveDoc());
            if($task == "upload_file") upload_file_to_temp();
            if($task == "validate_file") jout($this->Sfg_m->validateFile());
            return;
        }

        $view_data['model_info'] = $this->Items_model->get_one($this->input->post('id'));
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));
        $view_data["account_category"] = $this->Account_category_model->get_list_dropdown();

        $this->load->view('sfg/addedit', $view_data);
    }

    function detail(){
        $model_info = $this->Sfg_m->getRow($this->uri->segment(3));

        if(empty($model_info)){
            show_404();
            return;
        }

        $this->data["model_info"] = $model_info;

        $this->template->rander("sfg/detail", $this->data);
        
    }

    function detail_info($item_id = 0){
        $view_data['model_info'] = $this->Items_model->get_one($item_id);
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

        $view_data['label_column'] = "col-md-2";
        $view_data['field_column'] = "col-md-10";

        $this->load->view('sfg/detail_info', $view_data);
        
    }

    function detail_mixings(){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            $result = array();
            $list_data = $this->Bom_item_mixing_groups_model->get_details(['item_id' => $docId])->result();

            foreach ($list_data as $data) {
                $result[] = $this->_detail_mixing_make_row($data);
            }

            echo json_encode(array("data" => $result));
            return;
        }

        $this->data["item_id"] = $docId;
        $this->load->view("sfg/detail_mixings", $this->data);
    }

    function detail_files($item_id = 0){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            $options = array("item_id" => $item_id);
            $list_data = $this->Bom_item_files_model->get_details($options)->result();

            $result = array();
            foreach ($list_data as $data) {
                $result[] = $this->_item_file_make_row($data);
            }

            echo json_encode(array("data" => $result));
            return;
        }

        $view_data['item_id'] = $item_id;
        $this->load->view("sfg/detail_files", $this->data);
    }






    
}