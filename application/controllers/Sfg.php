<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
use Laminas\Barcode\Barcode;

class Sfg extends MY_Controller {
    private $item_type = "SFG";

    function __construct() {
        parent::__construct();
        $this->load->model("Account_category_model");
        $this->load->model("Bom_item_pricings_model");
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

        $categories = $this->Material_categories_m->getRows($this->item_type);
        $categories_dropdown = array(array("id" => "", "text" => "- " . lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        $view_data['categories_dropdown'] = json_encode($categories_dropdown);

        $this->template->rander("sfg/index", $view_data);
    }

    function upload_photo(){
        $task = $this->uri->segment(3);
        if($task == "upload_file") upload_file_to_temp();
        if($task == "validate_file") jout($this->Sfg_m->validateFile());
    }

    function category_modal(){
        $task = $this->uri->segment(3);
        
        if($task != null){
            if($task == "save") jout($this->Sfg_m->saveCategory());
            if($task == "delete") jout($this->Sfg_m->deleteCategory());
            return;
        }
        
        $view_data["existing_categories"] = $this->Material_categories_m->dev2_getCategoryListByType($this->item_type);
        
        $this->load->view("sfg/category_modal", $view_data);
    }

    function addedit(){
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save") jout($this->Sfg_m->saveDetailInfo());
            if($task == "delete") jout($this->Sfg_m->deleteRow());
            return;
        }

        $view_data['model_info'] = $this->Items_model->get_one($this->input->post('id'));
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

    function detail_info(){
        $model_info = $this->Sfg_m->getRow($this->uri->segment(3));

        if($this->input->method(false) == "post"){
            jout($this->Sfg_m->saveDetailInfo());
            return;
        }

        $view_data['model_info'] = $model_info;
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

        $view_data['category_dropdown'] = $this->Bom_item_model->get_category_dropdown();
        $view_data['account_category'] = $this->Account_category_model->get_list_dropdown();

        $this->load->view('sfg/detail_info', $view_data);
        
    }

    function detail_pricing(){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->detailPricingDataSet($docId)]);
            return;
        }

        $view_data['item_id'] = $this->uri->segment(3);
        $view_data['category_dropdown'] = $this->Item_categories_model->dev2_getCategoryDropdown();
        $view_data['supplier_dropdown'] = $this->Bom_suppliers_model->dev2_getSupplierDropdown();
        $view_data['is_admin'] = $this->login_user->is_admin;

        $this->load->view('sfg/detail_pricing', $view_data);
        
    }

    function detail_pricing_modal(){
        $docId = $this->uri->segment(3);

        if($docId != null){
            jout($this->Sfg_m->saveDetailPricing());
            return;
        }

        $post = $_POST;

        if (isset($post['id']) && !empty($post['id'])) {
            $this->data['model_info'] = $this->Bom_item_pricings_model->getItemPricingById($post['id']);
        }

        $this->data['item_id'] = $post['item_id'];
        if (isset($post['item_id']) && !empty($post['item_id'])) {
            $this->data['item_data'] = $this->Items_model->get_one($post['item_id']);
        }

        $this->data['supplier_dropdown'] = $this->Bom_suppliers_model->dev2_getSupplierDropdown();

        $this->load->view("sfg/detail_pricing_modal", $this->data);
    }

    function detail_mixings(){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->detailMixingsDataSet($docId)]);
            return;
        }

        $this->data["item_id"] = $docId;
        $this->load->view("sfg/detail_mixings", $this->data);
    }

    function detail_mixings_modal(){
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save") jout($this->Sfg_m->saveDetailMixings());
            if($task == "delete") jout($this->Sfg_m->deleteDetailMixings());
            return;
        }

        $this->data = $this->Sfg_m->detailMixings();
        $this->load->view("sfg/detail_mixings_modal", $this->data);
    }

    function detail_files(){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->detailFileDataSet($docId)]);
            return;
        }

        $view_data['item_id'] = $docId;
        $this->load->view("sfg/detail_files", $view_data);
    }

    function detail_files_modal(){
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save") jout($this->Sfg_m->saveDetailFile());
            return;
        }

        $view_data = $this->Sfg_m->detailFile();

        $this->load->view('sfg/detail_files_modal', $view_data);
    }

    function detail_item_remaining(){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->detailItemRemainingDataSet($docId)]);
            return;
        }

        /*$this->check_module_availability("module_stock");
        if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
            redirect("forbidden");
        }*/

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['item_id'] = $docId;
        $view_data['is_admin'] = $this->login_user->is_admin;
        $this->load->view('sfg/detail_item_remaining', $view_data);
    }

    function detail_item_used($item_id = 0){
        $docId = $this->uri->segment(3);

        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->detailItemUsedDataSet($docId)]);
            return;
        }

        /*$this->check_module_availability("module_stock");
        if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
            redirect("forbidden");
        }*/

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['item_id'] = $item_id;
        $view_data['is_admin'] = $this->login_user->is_admin;
        $this->load->view('sfg/detail_item_used', $view_data);
        
    }

    function restock(){
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->indexRestockDataSet()]);
            return;
        }

        $view_data['team_members_dropdown'] = $this->get_team_members_dropdown(true);

        $this->template->rander("sfg/restock/index", $view_data);
    }

    function restock_view($restock_id = 0, $tab = ""){
        $options = array("id" => $restock_id);
        $restock_item_info = $this->Bom_item_groups_model->get_details($options)->row();
        if ($restock_item_info) {
            $view_data['restock_item_info'] = $restock_item_info;

            $created_by = $view_data['restock_item_info']->created_by;
            
            $view_data['can_read'] = $this->Permission_m->bom_restock_read;
            $view_data['can_create'] = $this->Permission_m->bom_restock_create;
            $view_data['can_read_self'] = $this->Permission_m->bom_restock_read_self;

            $view_data['tab'] = $tab;
            $view_data['view_type'] = "";
            $view_data['hidden_menu'] = array("");

            $this->template->rander("sfg/restock/restock_view", $view_data);
        } else {
            show_404();
        }
    }

    function restock_view_info($restock_id = 0){
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['can_create'] = $this->check_permission('bom_restock_create');
        $view_data['can_update'] = $this->check_permission('bom_restock_update');

        if ($restock_id) {
            $view_data['model_info'] = $this->Bom_item_groups_model->get_one($restock_id);
            $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

            $created_by = $view_data['model_info']->created_by;
            if (!$this->bom_can_read_restock($created_by))
                redirect("forbidden");

            $view_data['label_column'] = "col-md-2";
            $view_data['field_column'] = "col-md-10";

            $this->load->view('sfg/restock/restock_view_info', $view_data);
        }
    }

    function restock_item_details($restock_id = 0){
        if($this->input->post("datatable") == true){
            $options = array(
                "group_id" => $restock_id
            );

            $list_data = $this->Bom_item_groups_model->get_restocks($options)->result();
            
            $result = array();
            foreach ($list_data as $data) {
                $result[] = $this->Sfg_m->getRestockViewInfoDataSetHTML($data);
            }

            echo json_encode(array("data" => $result));
            return;
        }
        
        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['can_update'] = $this->check_permission('bom_restock_update');
        $view_data['can_create'] = $this->check_permission('bom_restock_create');

        if ($restock_id) {
            $view_data['restock_id'] = $restock_id;
            $view_data['is_admin'] = $this->login_user->is_admin;
            $this->load->view('sfg/restock/restock_view_details', $view_data);
        }
    }

    function restock_addedit_modal(){
        $task = $this->uri->segment(3);

        if($task != null){
            if($task == "save") jout($this->Sfg_m->saveRestock());
            if($task == "delete") jout($this->Sfg_m->deleteRestock());
            return;
        }

        $view_data = $this->Sfg_m->restock();
        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

        $this->load->view('sfg/restock/modal', $view_data);
    }

    function report(){
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sfg_m->reportDataSet()]);
            return;
        }

        $view_data = $this->Sfg_m->report();
        $this->template->rander("sfg/report", $view_data);
    }
}