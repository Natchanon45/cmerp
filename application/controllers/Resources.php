<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Resources extends MY_Controller {
    private $className = 'resources';
    function __construct() {
        parent::__construct();
        $this->init_permission_checker("order");
    }

    protected function validate_access_to_resources() {
        $access_invoice = $this->get_access_info("invoice");
        $access_estimate = $this->get_access_info("estimate");

        //don't show the resources if invoice/estimate module is not enabled
        if (!(get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1" )) {
            redirect("forbidden");
        }

        if ($this->login_user->is_admin) {
            return true;
        } else if ($access_invoice->access_type === "all" || $access_estimate->access_type === "all") {
            return true;
        } else {
            redirect("forbidden");
        }
    }

    function import_modal_form() {
	
		if( empty( $this->getRolePermission['resources'] ) ) {
		
			echo permissionBlock();
			return;
		}
		
         $this->load->view( "". $this->className ."/import_modal_form" );
    }

    //load resources list view
    
    function index() {
  
        $buttonTop[] = '<a href="#" class="btn btn-default mb0" title="จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'" data-post-type="'. $this->getRolePermission['table_name'] .'" data-act="ajax-modal" data-title="จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'" data-action-url="'. base_url( 'index.php/labels/modal_form' ) .'"><i class="fa fa-tags"></i> จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'</a>';
        
        
        $buttonTop[] = modal_anchor(get_uri("". $this->className ."/import_modal_form"), "<i class='fa fa-upload'></i> " . 'นำเข้าข้อมูลสินค้า', array("class" => "btn btn-default", "title" =>  'นำเข้าข้อมูลสินค้า'  ));
         
              $buttonTop[] = modal_anchor(get_uri("". $this->className ."/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item')));
      
              $view_data['buttonTop'] = implode( '', $buttonTop );
        
               $view_data['categories_dropdown'] = $this->_get_categories_dropdown();
      
              $this->template->rander("". $this->className ."/index", $view_data);
          }

    //get categories dropdown
    private function _get_categories_dropdown() {
        $categories = $this->Item_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->result();

        $categories_dropdown = array(array("id" => "", "text" => "- " . lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_dropdown);
    }

    /* load item modal */

    
    function modal_form() {
        $this->access_only_team_members();
        $this->validate_access_to_resources();

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Resources_model->get_one($this->input->post('id'));
        $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

        $this->load->view('resources/modal_form', $view_data);
    }

    /* add or edit an item */

    function save() {
        $this->access_only_team_members();
        $this->validate_access_to_resources();

        validate_submitted_data(array(
            "id" => "numeric",
            // "category_id" => "required",
        ));

        $id = $this->input->post('id');

        $item_data = array(
            "title" => $this->input->post('title'),
            "description" => $this->input->post('description'),
            // "category_id" => $this->input->post('category_id'),
            "unit_type" => $this->input->post('unit_type'),
            "rate" => unformat_currency($this->input->post('item_rate')),
            "show_in_client_portal" => $this->input->post('show_in_client_portal') ? $this->input->post('show_in_client_portal') : ""
        );

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "item");
        $new_files = unserialize($files_data);

        if ($id) {
            $item_info = $this->Resources_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $item_info->files, $new_files);
        }

        $item_data["files"] = serialize($new_files);

        $item_id = $this->Resources_model->save($item_data, $id);
        if ($item_id) {
            $options = array("id" => $item_id);
            $item_info = $this->Resources_model->get_details($options)->row();
            echo json_encode(array("success" => true, "id" => $item_info->id, "data" => $this->_make_item_row($item_info), 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an item */

    function delete() {
        $this->access_only_team_members();
        $this->validate_access_to_resources();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Resources_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Resources_model->get_details($options)->row();
                echo json_encode(array("success" => true, "id" => $item_info->id, "data" => $this->_make_item_row($item_info), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Resources_model->delete($id)) {
                $item_info = $this->Resources_model->get_one($id);
                echo json_encode(array("success" => true, "id" => $item_info->id, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of resources, prepared for datatable  */

    function list_data() {
        $this->access_only_team_members();
        $this->validate_access_to_resources();

        $category_id = $this->input->post('category_id');
        $options = array("category_id" => $category_id);

        $list_data = $this->Resources_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of item list table */

    private function _make_item_row($data) {
        $type = $data->unit_type ? $data->unit_type : "";

        $show_in_client_portal_icon = "";
        if ($data->show_in_client_portal && get_setting("module_order")) {
            $show_in_client_portal_icon = "<i title='" . lang("showing_in_client_portal") . "' class='fa fa-shopping-basket'></i> ";
        }

        $preview = '<img class="product-preview" src="'.base_url('assets/images/file_preview.jpg').'" />';
        $images = @unserialize($data->files);
        if(is_array($images) && sizeof($images)){
            $preview = '<img class="product-preview" src="'.base_url('files/timeline_files/'.$images[sizeof($images)-1]['file_name']).'" />';
        }

        return array(
            $preview,
            get_setting("module_stock") == '1'
                ? anchor(get_uri('resources/detail/' . $data->id), $data->title)
                : modal_anchor(get_uri("resources/view"), $show_in_client_portal_icon . $data->title, array("title" => lang("item_details"), "data-post-id" => $data->id)),
            nl2br($data->description),
            $data->category_title ? $data->category_title : "-",
            $type,
            $data->rate,
            modal_anchor(get_uri("resources/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("resources/delete"), "data-action" => "delete"))
        );
    }

    function upload_file() {
        $this->access_only_team_members();
        upload_file_to_temp();
    }
 
    function validate_resources_file() {
        $this->access_only_team_members();
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

    function view() {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Resources_model->get_details(array("id" => $this->input->post('id'), "login_user_id" => $this->login_user->id))->row();

        $view_data['model_info'] = $model_info;
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

        $this->load->view('resources/view', $view_data);
    }

    function save_files_sort() {
        $this->access_only_allowed_members();
        $id = $this->input->post("id");
        $sort_values = $this->input->post("sort_values");
        if ($id && $sort_values) {
            //extract the values from the :,: separated string
            $sort_array = explode(":,:", $sort_values);

            $item_info = $this->Resources_model->get_one($id);
            if ($item_info->id) {
                $updated_file_indexes = update_file_indexes($item_info->files, $sort_array);
                $item_data = array(
                    "files" => serialize($updated_file_indexes)
                );

                $this->Resources_model->save($item_data, $id);
            }
        }
    }

    /* store criteria */

    function grid_view($offset = 0, $limit = 20, $category_id = 0, $search = "") {
        $this->check_access_to_store();

        $options = array("login_user_id" => $this->login_user->id);

        $item_search = $this->input->post("item_search");
        if ($item_search) {
            $search = $this->input->post("search");
            $category_id = $this->input->post("category_id") ? $this->input->post("category_id") : 0;
        }

        if ($search) {
            $options["search"] = $search;
        }

        if ($category_id) {
            $options["category_id"] = $category_id;
        }

        if ($this->login_user->user_type == "client") {
            $options["show_in_client_portal"] = 1; //show all resources on admin side
        }

        //get all rows
        $all_resources = $this->Resources_model->get_details($options)->num_rows();

        $options["offset"] = $offset;
        $options["limit"] = $limit;

        $view_data["resources"] = $this->Resources_model->get_details($options)->result();
        $view_data["result_remaining"] = $all_resources - $limit - $offset;
        $view_data["next_page_offset"] = $offset + $limit;

        $view_data["search"] = $search;
        $view_data["category_id"] = $category_id;

        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
        $view_data['categories_dropdown'] = $this->_get_categories_dropdown();

        if ($offset) { //load more view
            $this->load->view("resources/resources_grid_data", $view_data);
        } else if ($item_search) { //search suggestions view
            echo json_encode(array("success" => true, "data" => $this->load->view("resources/resources_grid_data", $view_data, true)));
        } else { //default view
            $this->template->rander("resources/grid_view", $view_data);
        }
    }

    private function check_access_to_this_item($item_info) {
        if ($this->login_user->user_type === "client") {
            //check if the item has the availability to show on client portal
            if (!$item_info->show_in_client_portal) {
                redirect("forbidden");
            }
        }
    }

    function add_item_to_cart() {
        $this->check_access_to_store();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post("id");
        $item_info = $this->Resources_model->get_one($id);
        $this->check_access_to_this_item($item_info);

        $order_item_data = array(
            "title" => $item_info->title,
            "quantity" => 1, //add 1 item first time
            "unit_type" => $item_info->unit_type,
            "rate" => $item_info->rate,
            "total" => $item_info->rate, //since the quantity is 1
            "created_by" => $this->login_user->id,
            "item_id" => $id
        );

        $save_id = $this->Order_Resources_model->save($order_item_data);

        if ($save_id) {
            echo json_encode(array("success" => true, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function count_cart_resources() {
        $this->check_access_to_store();

        $cart_resources_count = $this->Order_Resources_model->get_all_where(array("created_by" => $this->login_user->id, "order_id" => 0, "deleted" => 0))->num_rows();

        if ($cart_resources_count) {
            echo json_encode(array("success" => true, "cart_resources_count" => $cart_resources_count));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('no_record_found')));
        }
    }

    function load_cart_resources() {
        $this->check_access_to_store();

        $view_data = get_order_making_data();

        $options = array("created_by" => $this->login_user->id, "processing" => true);
        $view_data["resources"] = $this->Order_Resources_model->get_details($options)->result();
        $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

        $this->load->view("resources/cart/cart_resources_list", $view_data);
    }

    function delete_cart_item() {
        $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "required"
        ));

        $order_item_id = $this->input->post("id");
        $order_item_info = $this->Order_Resources_model->get_one($order_item_id);
        $this->check_access_to_this_order_item($order_item_info);

        if ($this->Order_Resources_model->delete($order_item_id)) {
            echo json_encode(array("success" => true, 'message' => lang('record_deleted'), "cart_total_view" => $this->_get_cart_total_view()));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }

    function change_cart_item_quantity() {
        $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "required",
            "action" => "required"
        ));

        $id = $this->input->post("id");
        $action = $this->input->post("action");

        $item_info = $this->Order_Resources_model->get_one($id);
        $this->check_access_to_this_order_item($item_info);

        if ($item_info->id) {
            $quantity = $item_info->quantity;
            if ($action == "plus") {
                //plus quantity
                $quantity = $quantity + 1;
            } else if ($action == "minus" && $quantity > 1) {
                //minus quantity
                //shouldn't be less than one
                $quantity = $quantity - 1;
            }

            $data = array(
                "quantity" => $quantity,
                "total" => $item_info->rate * $quantity
            );
            $this->Order_Resources_model->save($data, $item_info->id);

            $options = array("id" => $id);
            $view_data["item"] = $this->Order_Resources_model->get_details($options)->row();
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

            echo json_encode(array("success" => true, 'message' => lang('record_saved'), "data" => $this->load->view("resources/cart/cart_item_data", $view_data, true), "cart_total_view" => $this->_get_cart_total_view()));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    
    private function _get_cart_total_view() {
        $view_data = get_order_making_data();
        return $this->load->view('resources/cart/cart_total_section', $view_data, true);
    }


    // BOM
    function detail($item_id = 0, $tab = "") {
        $this->check_module_availability("module_stock");
        $this->access_only_team_members();
        $this->validate_access_to_resources();
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
  
        if ($item_id) {
            $options = array(
                "id" => $item_id,
                "login_user_id" => $this->login_user->id
            );
            $model_info = $this->Resources_model->get_details($options)->row();
            if ($model_info) {
                $view_data['model_info'] = $model_info;
                $view_data["tab"] = $tab;
                $view_data["view_type"] = "";
                
                if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
                    $view_data["hidden_menu"] = array("item-mixing");
                }
        
                $this->template->rander("resources/detail/index", $view_data);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
    }
    function detail_info($resource_id = 0) {
        $this->check_module_availability("module_stock");
        $this->access_only_team_members();
        $this->validate_access_to_resources();

        if ($resource_id) {
            $this->access_only_team_members();
            $this->validate_access_to_resources();
            
            $view_data['model_info'] = $this->Resources_model->get_one($resource_id);
            // $view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));
            $view_data['label_column'] = "col-md-2";
            $view_data['field_column'] = "col-md-10";
    
            $this->load->view('resources/detail/info', $view_data);
        }
    }

    function detail_mixings($item_id = 0) {
        $this->check_module_availability("module_stock");
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

        if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
            redirect("forbidden");
        }

        $view_data['item_id'] = $item_id;
        $this->load->view("resources/detail/mixing", $view_data);
    }
    function detail_mixing_list($item_id = 0) {
        $this->check_module_availability("module_stock");
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

        if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
            redirect("forbidden");
        }

        $list_data = $this->Bom_item_mixing_groups_model->get_details([
            'item_id' => $item_id
        ])->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_detail_mixing_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }
    private function _detail_mixing_make_row($data) {
      $row_data = array(
        $data->id,
        modal_anchor(get_uri("resources/detail_mixing_modal"), $data->name, array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id)),
        to_decimal_format2($data->ratio).' '.$data->unit_type,
        $data->is_public == 1? lang('yes'): lang('no'),
        $data->is_public == 0 && !empty($data->for_client_id)
            ? anchor(get_uri("clients/view/" . $data->for_client_id), $data->company_name)
            : '-',
      );
      $row_data[] = modal_anchor(get_uri("resources/detail_mixing_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id))
        . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('item_mixing_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("resources/detail_mixing_delete"), "data-action" => "delete-confirmation"));
      return $row_data;
    }
    private function _detail_mixing_row_data($id) {
      $options = array(
        'id' => $id
      );
      $data = $this->Bom_item_mixing_groups_model->get_details($options)->row();
      return $this->_detail_mixing_make_row($data);
    }
  
    function detail_mixing_modal() {
        $this->check_module_availability("module_stock");
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

        if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
            redirect("forbidden");
        }

        $id = $this->input->post('id');
        $item_id = $this->input->post('item_id');
        validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "required|numeric"
        ));
    
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
    
        $view_data["view"] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_mixing_groups_model->get_one($id);
        $view_data['item'] = $this->Resources_model->get_one($item_id);
        $view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();
        $view_data['clients_dropdown'] = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));

        if(!empty($id)){
            $view_data['material_mixings'] = $this->Bom_item_mixing_groups_model->get_mixings([
                'group_id' => $id
            ])->result();
        }
        
        if (empty($view_data['model_info']->item_id)) {
            $view_data['model_info']->item_id = $item_id;
            $view_data['model_info']->is_public = 1;
        }
    
        $this->load->view('resources/detail/modal_mixing', $view_data);
    }
    function detail_mixing_save() {
        $this->check_module_availability("module_stock");
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

        if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
            echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
        }
            
        $id = $this->input->post('id');
        validate_submitted_data(array(
            "id" => "numeric",
            "item_id" => "required|numeric",
            "name" => "required",
            "ratio" => "required|numeric"
        ));
    
        $is_public = $this->input->post('is_public');
        $data = array(
            "item_id" => $this->input->post('item_id'),
            "name" => $this->input->post('name'),
            "ratio" => $this->input->post('ratio'),
            "is_public" => $is_public,
            "for_client_id" => $is_public == 1? null: $this->input->post('for_client_id')
        );
        $data = clean_data($data);
        $save_id = $this->Bom_item_mixing_groups_model->save($data, $id);
        
        $material_ids = $this->input->post('material_id[]');
        $ratios = $this->input->post('mixing_ratio[]');
        $this->Bom_item_mixing_groups_model->mixing_save($save_id, $material_ids, $ratios);
    
        if ($save_id) {
            echo json_encode(array(
            "success" => true, 
            "data" => $this->_detail_mixing_row_data($save_id), 
            'id' => $save_id, 
            'view' => $this->input->post('view'), 
            'message' => lang('record_saved')
            ));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }
    function detail_mixing_delete() {
        $this->check_module_availability("module_stock");
        
        $view_data['can_read'] = $this->check_permission('bom_material_read');
        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

        if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
            echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
        }
    
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));
    
        $id = $this->input->post('id');
        if ($this->Bom_item_mixing_groups_model->delete_mixing($id)) {
            echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }

}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */