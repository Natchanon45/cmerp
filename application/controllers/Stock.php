<?php

use Laminas\Barcode\Barcode;

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

class Stock extends MY_Controller
{

        function __construct()
        {
                parent::__construct();
                require_once(APPPATH . "third_party/php-excel-writer/src/ExcelWriter.php");

                $this->load->model("Permission_m");
                $this->load->model("Account_category_model");
        }

        function index()
        {
                $view_data['access_supplier'] = $this->bom_can_access_supplier();
                $view_data['access_material'] = $this->bom_can_access_material();
                $view_data['access_restock'] = $this->bom_can_access_restock();
                $view_data['access_calculator'] = $this->bom_can_access_calculator();
                $this->template->rander("stock/index", $view_data);
        }

        // START: Files
        function upload_file()
        {
                upload_file_to_temp();
        }

        function upload_material_file()
        {
                upload_file_to_temp();
        }

        function validate_material_file()
        {
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

        function validate_file()
        {
                return validate_post_file($this->input->post("file_name"));
        }

        function validate_modal_file()
        {
                $file_name = $this->input->post("file_name");

                if (!is_valid_file_to_upload($file_name)) {
                        echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
                        exit();
                } else {
                        echo json_encode(array("success" => true));
                }
        }
        // END: Files


        // START: Supplier
        function suppliers()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_supplier_create');
                $view_data['can_update'] = $this->check_permission('bom_supplier_update');
                $view_data['can_delete'] = $this->check_permission('bom_supplier_delete');

                if ($this->login_user->is_admin || $this->check_permission('bom_supplier_read')) {
                        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);
                }

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->template->rander("stock/supplier/index", $view_data);
        }

        function supplier_list()
        {
                $this->check_module_availability("module_stock");

                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $options = array(
                        "owner_id" => $this->input->post("owner_id")
                );

                if ($this->check_permission('bom_supplier_read_self') && !$this->check_permission('bom_supplier_read')) {
                        $options['owner_id'] = $this->login_user->id;
                }

                $list_data = $this->Bom_suppliers_model->get_details($options)->result();
                $result = array();

                foreach ($list_data as $data) {
                        $result[] = $this->_supplier_make_row($data);
                }

                echo json_encode(array("data" => $result));
        }

        private function _supplier_make_row($data)
        {
                $contact_name = '';
                if (!empty($data->contact_first_name) || !empty($data->contact_last_name)) {
                        $contact_name = $data->contact_first_name . ' ' . $data->contact_last_name;
                }

                $row_data = array(
                        $data->id,
                        $data->code_supplier,
                        anchor(get_uri('stock/supplier_view/' . $data->id), $data->company_name),
                        $data->address ? $data->address : '-',
                        $contact_name ? $contact_name : '-',
                        $data->contact_phone ? $data->contact_phone : '-',
                        $data->contact_email ? $data->contact_email : '-'
                );

                $options = '';

                if ($this->check_permission('bom_supplier_update')) {
                        $options .= modal_anchor(get_uri("stock/supplier_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_supplier_edit'), "data-post-id" => $data->id));
                } else {
                        $options .= modal_anchor(get_uri("stock/supplier_modal"), "<i class='fa fa-eye'></i>", array("class" => "view", "title" => lang('stock_supplier_view'), "data-post-id" => $data->id));
                }
                if ($this->check_permission('bom_supplier_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_supplier_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/supplier_delete"), "data-action" => "delete-confirmation"));
                }

                $row_data[] = $options;

                return $row_data;
        }

        private function _supplier_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_suppliers_model->get_details($options)->row();
                return $this->_supplier_make_row($data);
        }

        function supplier_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_supplier_create');
                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                $supplier_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";
                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_suppliers_model->get_one($supplier_id);

                if (empty($view_data['model_info']->id)) {
                        if (!$this->check_permission('bom_supplier_create'))
                                redirect("forbidden");
                } else {
                        $owner_id = $view_data['model_info']->owner_id;
                        if (!$this->bom_can_read_supplier($owner_id))
                                redirect("forbidden");
                }

                $view_data["currency_dropdown"] = $this->_get_currency_dropdown_select2_data();
                $view_data["team_members_dropdown"] = $this->get_team_members_dropdown();
                
                $this->load->view('stock/supplier/modal', $view_data);
        }

        function supplier_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $supplier_id = $this->input->post('id');
                if (empty($supplier_id)) {
                        if (!$this->check_permission('bom_supplier_create')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                } else {
                        if (!$this->check_permission('bom_supplier_update')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                }

                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "company_name" => "required"
                        )
                );

                $company_name = $this->input->post('company_name');
                $data = array(
                        "company_name" => $company_name,
                        "code_supplier" => $this->input->post('code_supplier'),
                        "address" => $this->input->post('address'),
                        "city" => $this->input->post('city'),
                        "state" => $this->input->post('state'),
                        "zip" => $this->input->post('zip'),
                        "country" => $this->input->post('country'),
                        "phone" => $this->input->post('phone'),
                        "website" => $this->input->post('website'),
                        "vat_number" => $this->input->post('vat_number')
                );

                if (!$supplier_id) {
                        $data["created_by"] = $this->login_user->id;
                        $data["created_date"] = get_current_utc_time();
                }

                if ($this->login_user->is_admin) {
                        $data["currency_symbol"] = $this->input->post('currency_symbol') ? $this->input->post('currency_symbol') : "";
                        $data["currency"] = $this->input->post('currency') ? $this->input->post('currency') : "";
                }

                if ($this->login_user->is_admin) {
                        $data["owner_id"] = $this->input->post('owner_id') ? $this->input->post('owner_id') : $this->login_user->id;
                } else if (!$supplier_id) {
                        $data["owner_id"] = $this->login_user->id;
                }

                $data = clean_data($data);

                $save_id = $this->Bom_suppliers_model->save($data, $supplier_id);
                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->_supplier_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function supplier_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_delete')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                $id = $this->input->post('id');

                if ($this->Bom_suppliers_model->delete_supplier_and_sub_items($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function get_login_user()
        {
                var_dump(arr($this->Permission_m->login_user_test()));
        }

        function supplier_view($supplier_id = 0, $tab = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_supplier_create');
                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                if ($supplier_id) {
                        $options = array("id" => $supplier_id);
                        $supplier_info = $this->Bom_suppliers_model->get_details($options)->row();
                        if ($supplier_info) {
                                $view_data['supplier_info'] = $supplier_info;

                                $view_data["tab"] = $tab;
                                $view_data["view_type"] = "";

                                $owner_id = $view_data['supplier_info']->owner_id;
                                if (!$this->bom_can_read_supplier($owner_id))
                                        redirect("forbidden");

                                if (!$this->bom_can_access_material()) {
                                        $view_data["hidden_menu"] = array("supplier-pricings");
                                }

                                $this->template->rander("stock/supplier/view", $view_data);
                        } else {
                                show_404();
                        }
                } else {
                        show_404();
                }
        }

        function supplier_info($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_supplier_create');
                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                if ($supplier_id) {
                        $view_data['model_info'] = $this->Bom_suppliers_model->get_one($supplier_id);
                        $view_data["currency_dropdown"] = $this->_get_currency_dropdown_select2_data();
                        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown();
                        $view_data['label_column'] = "col-md-2";
                        $view_data['field_column'] = "col-md-10";

                        $owner_id = $view_data['model_info']->owner_id;

                        if (!$this->bom_can_read_supplier($owner_id))
                                redirect("forbidden");

                        $this->load->view('stock/supplier/info', $view_data);
                }
        }
        // END: Supplier

        // START: Supplier Contact
        function supplier_contacts($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                if ($supplier_id) {
                        $view_data["supplier_id"] = $supplier_id;
                        $view_data["view_type"] = "";
                } else {
                        $view_data["supplier_id"] = "";
                        $view_data["view_type"] = "list_view";
                }

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->load->view("stock/contact/index", $view_data);
        }

        function supplier_contact_list($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $options = array(
                        "supplier_id" => $supplier_id
                );
                $list_data = $this->Bom_supplier_contacts_model->get_details($options)->result();
                $result = array();

                foreach ($list_data as $data) {
                        $result[] = $this->_contact_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _contact_make_row($data)
        {
                $full_name = $data->first_name . " " . $data->last_name . " ";
                $primary_contact = "";
                if ($data->is_primary == "1") {
                        $primary_contact = "<span class='label-info label'>" . lang('primary_contact') . "</span>";
                }

                $row_data = array(
                        $data->id,
                        $full_name . $primary_contact,
                        anchor(get_uri("stock/supplier_view/" . $data->supplier_id), $data->company_name),
                        $data->email,
                        $data->phone ? $data->phone : "-"
                );

                $options = '';
                if ($this->check_permission('bom_supplier_update')) {
                        $options .= modal_anchor(get_uri("stock/supplier_contact_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_contact'), "data-post-id" => $data->id))
                                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_contact'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/supplier_contact_delete"), "data-action" => "delete-confirmation"));
                } else {
                        $options .= modal_anchor(get_uri("stock/supplier_contact_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('edit_contact'), "data-post-id" => $data->id));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _contact_row_data($id)
        {
                $options = array(
                        "id" => $id
                );
                $data = $this->Bom_supplier_contacts_model->get_details($options)->row();
                return $this->_contact_make_row($data);
        }

        function supplier_contact_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                $contact_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_supplier_contacts_model->get_one($contact_id);

                $supplier_id = $this->input->post('supplier_id');
                if ($supplier_id && empty($view_data['model_info']->supplier_id)) {
                        $view_data['model_info']->supplier_id = $supplier_id;
                }

                $this->load->view('stock/contact/modal', $view_data);
        }

        function supplier_contact_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "supplier_id" => "required|numeric",
                                "first_name" => "required",
                                "last_name" => "required"
                        )
                );

                $data = array(
                        "supplier_id" => $this->input->post('supplier_id'),
                        "first_name" => $this->input->post('first_name'),
                        "last_name" => $this->input->post('last_name'),
                        "email" => $this->input->post('email'),
                        "phone" => $this->input->post('phone'),
                        "is_primary" => $this->input->post('is_primary') ? 1 : 0
                );
                if ($data['is_primary']) {
                        $this->Bom_supplier_contacts_model->clear_primary($data['supplier_id']);
                }

                if (!$id) {
                        $data["created_date"] = get_current_utc_time();
                }

                $data = clean_data($data);

                $save_id = $this->Bom_supplier_contacts_model->save($data, $id);
                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->_contact_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function supplier_contact_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_supplier_contacts_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }
        // END: Supplier Contact

        // START: Supplier Pricing
        function supplier_pricings($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $view_data['can_update_supplier'] = $this->check_permission('bom_supplier_update');

                $view_data['can_access_material'] = $this->bom_can_access_material();
                $view_data['can_update_material'] = $this->check_permission('bom_material_update');

                $view_data['supplier_id'] = $supplier_id;
                $view_data["category_dropdown"] = $this->Bom_materials_model->get_category_dropdown();

                $view_data['is_admin'] = $this->login_user->is_admin;

                // var_dump(arr($view_data)); exit;
                $this->load->view("stock/supplier/pricing", $view_data);
        }

        function supplier_pricing_list($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $list_data = $this->Bom_materials_model->get_pricings([
                        'supplier_id' => $supplier_id,
                        "category_id" => $this->input->post("category_id")
                ])->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_supplier_pricing_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _supplier_pricing_make_row($data)
        {
                $material_name = $data->material_name;
                if ($this->check_permission("bom_material_read_production_name") == true)
                        $material_name .= " - " . $data->production_name;
                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/material_view/' . $data->material_id), $material_name),
                        $data->category ? $data->category : '-',
                        $data->description ? $data->description : '-',
                        to_decimal_format2($data->ratio) . ' ' . $data->unit,
                        to_currency($data->price)
                );

                $options = '';
                if (
                        $this->bom_can_access_supplier() && $this->check_permission('bom_supplier_update')
                        && $this->bom_can_access_material() && $this->check_permission('bom_material_update')
                ) {
                        $options .= modal_anchor(get_uri("stock/supplier_pricing_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_supplier_pricing_edit'), "data-post-id" => $data->id, "data-post-supplier_id" => $data->supplier_id))
                                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_supplier_pricing_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/supplier_pricing_delete"), "data-action" => "delete-confirmation"));
                } else {
                        $options .= modal_anchor(get_uri("stock/supplier_pricing_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_supplier_pricing_edit'), "data-post-id" => $data->id, "data-post-supplier_id" => $data->supplier_id));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _supplier_pricing_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_materials_model->get_pricings($options)->row();
                return $this->_supplier_pricing_make_row($data);
        }

        function supplier_pricing_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $view_data['can_update'] = $this->check_permission('bom_supplier_update')
                        && $this->check_permission('bom_material_update');

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_material_pricings_model->get_one($id);

                $supplier_id = $this->input->post('supplier_id');
                if ($supplier_id && empty($view_data['model_info']->supplier_id)) {
                        $view_data['model_info']->supplier_id = $supplier_id;
                        $view_data['material_dropdown'] = $this->Bom_suppliers_model->get_material_pricing_dropdown($supplier_id);
                } else {
                        $view_data['material_dropdown'] = $this->Bom_suppliers_model->get_material_pricing_dropdown();
                }
                $view_data['material'] = $this->Bom_materials_model->get_one($view_data['model_info']->material_id);

                // var_dump(arr($view_data)); exit;

                $this->load->view('stock/supplier/modal_pricing', $view_data);
        }

        function supplier_pricing_save()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')
                        || !$this->bom_can_access_material() || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "material_id" => "required|numeric",
                                "supplier_id" => "required|numeric",
                                "ratio" => "required|numeric",
                                "price" => "required|numeric"
                        )
                );

                $data = array(
                        "material_id" => $this->input->post('material_id'),
                        "supplier_id" => $this->input->post('supplier_id'),
                        "ratio" => $this->input->post('ratio'),
                        "price" => $this->input->post('price')
                );
                $data = clean_data($data);
                $save_id = $this->Bom_material_pricings_model->save($data, $id);

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->_supplier_pricing_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }
        
        function supplier_pricing_delete()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')
                        || !$this->bom_can_access_material() || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_material_pricings_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }
        // END: Supplier Pricing

        // START: Supplier Files
        function supplier_files($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $view_data['can_update'] = $this->check_permission('bom_supplier_update');

                $view_data['supplier_id'] = $supplier_id;
                $this->load->view("stock/supplier/files", $view_data);
        }

        function supplier_file_list($supplier_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $options = array("supplier_id" => $supplier_id);
                $list_data = $this->Bom_supplier_files_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_supplier_file_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _supplier_file_make_row($data)
        {
                $file_icon = get_file_icon(strtolower(pathinfo($data->file_name, PATHINFO_EXTENSION)));

                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $data->user_name";
                if ($data->user_type == "staff") {
                        $uploaded_by = get_team_member_profile_link($data->uploaded_by, $user_name);
                } else {
                        $uploaded_by = get_client_contact_profile_link($data->uploaded_by, $user_name);
                }

                $description = "<div class='pull-left'>"
                        . js_anchor(remove_file_prefix($data->file_name), array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("stock/supplier_view_file/" . $data->id)));
                if ($data->description) {
                        $description .= "<br /><span>" . $data->description . "</span></div>";
                } else {
                        $description .= "</div>";
                }

                $options = anchor(get_uri("stock/supplier_download_file/" . $data->id), "<i class='fa fa fa-cloud-download'></i>", array("title" => lang("download")));
                if ($this->check_permission('bom_supplier_update')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_file'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/supplier_delete_file"), "data-action" => "delete-confirmation"));
                }

                return array(
                        $data->id,
                        "<div class='fa fa-$file_icon font-22 mr10 pull-left'></div>" . $description,
                        convert_file_size($data->file_size),
                        $uploaded_by,
                        format_to_datetime($data->created_at),
                        $options
                );
        }

        function supplier_view_file($file_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $file_info = $this->Bom_supplier_files_model->get_details(array("id" => $file_id))->row();

                if ($file_info) {
                        if (!$file_info->supplier_id)
                                redirect("forbidden");

                        $view_data['can_comment_on_files'] = false;
                        $file_url = get_source_url_of_file(make_array_of_file($file_info), get_general_file_path("client", $file_info->supplier_id));

                        $view_data["file_url"] = $file_url;
                        $view_data["is_image_file"] = is_image_file($file_info->file_name);
                        $view_data["is_google_preview_available"] = is_google_preview_available($file_info->file_name);
                        $view_data["is_viewable_video_file"] = is_viewable_video_file($file_info->file_name);
                        $view_data["is_google_drive_file"] = ($file_info->file_id && $file_info->service_type == "google") ? true : false;

                        $view_data["file_info"] = $file_info;
                        $view_data['file_id'] = $file_id;
                        $this->load->view("stock/supplier/view_file", $view_data);
                } else {
                        show_404();
                }
        }

        function supplier_download_file($id)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier())
                        redirect("forbidden");

                $file_info = $this->Bom_supplier_files_model->get_one($id);
                if (!$file_info->supplier_id)
                        redirect("forbidden");

                $file_data = serialize(array(make_array_of_file($file_info)));

                download_app_files(get_general_file_path("client", $file_info->supplier_id), $file_data);
        }

        function supplier_delete_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                $info = $this->Bom_supplier_files_model->get_one($id);

                if ($this->Bom_supplier_files_model->delete_one($id)) {
                        delete_app_files(get_general_file_path("client", $info->supplier_id), array(make_array_of_file($info)));
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function supplier_file_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')) {
                        redirect("forbidden");
                }

                $view_data['model_info'] = $this->Bom_supplier_files_model->get_one($this->input->post('id'));
                $supplier_id = $this->input->post('supplier_id')
                        ? $this->input->post('supplier_id')
                        : $view_data['model_info']->supplier_id;

                $view_data['supplier_id'] = $supplier_id;
                $this->load->view('stock/supplier/modal_file', $view_data);
        }

        function supplier_save_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $supplier_id = $this->input->post('supplier_id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "supplier_id" => "required|numeric"
                        )
                );

                $files = $this->input->post("files");
                $success = false;
                $now = get_current_utc_time();

                $target_path = getcwd() . "/" . get_general_file_path("client", $supplier_id);

                if ($files && get_array_value($files, 0)) {
                        foreach ($files as $file) {
                                $file_name = $this->input->post('file_name_' . $file);
                                $file_info = move_temp_file($file_name, $target_path);
                                if ($file_info) {
                                        $data = array(
                                                "supplier_id" => $supplier_id,
                                                "file_name" => get_array_value($file_info, 'file_name'),
                                                "file_id" => get_array_value($file_info, 'file_id'),
                                                "service_type" => get_array_value($file_info, 'service_type'),
                                                "description" => $this->input->post('description_' . $file),
                                                "file_size" => $this->input->post('file_size_' . $file),
                                                "created_at" => $now,
                                                "uploaded_by" => $this->login_user->id
                                        );
                                        $success = $this->Bom_supplier_files_model->save($data);
                                } else {
                                        $success = false;
                                }
                        }
                }

                if ($success) {
                        echo json_encode(array("success" => true, 'message' => lang('record_saved')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }
        // END: Supplier Files


        // START: Material
        function materials()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_material_create');
                $view_data['can_update'] = $this->check_permission('bom_material_update');
                $view_data['can_delete'] = $this->check_permission('bom_material_delete');
                $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                $view_data["category_dropdown"] = $this->Bom_materials_model->get_category_dropdown();

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->template->rander("stock/material/index", $view_data);
        }

        function material_list()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "category_id" => $this->input->post("category_id")
                );
                $list_data = $this->Bom_materials_model->get_details($options)->result();
                // var_dump(arr($list_data)); exit;
                
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_make_row($data);
                }

                // var_dump(arr($result)); exit;
                echo json_encode(array("data" => $result));
        }

        public function barcode($barcode)
        {
                $databarcode = ['text' => $barcode, 'drawText' => true,];
                $rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
                Barcode::render('code128', 'image', $databarcode, $rendererOptions);
        }

        private function _material_make_row($data)
        {
                $src = @$data->barcode;
                if ($src) {
                        /* $barcodeOptions = [
                        'text' => $check['text'],
                        'barHeight' => 50,
                        'drawText' => true,
                        'withChecksum' => $check['checksum'],
                        'withChecksumInText' => $check['checksum']
                        ]; */
                        //$databarcode = ['text' => $data->barcode,'drawText' => true,];
                        //$rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
                        /* ob_start();
                        Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );
                        $src = ob_get_contents();
                        ob_end_clean(); */
                        $src = base_url('/stock/barcode/' . $src);

                        //$src = Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );
                }
                //Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );exit;
                //var_dump($src);exit;
                //echo $src;
                //exit;

                // Image checker
                $preview = '<img class="product-preview" src="' . base_url('assets/images/file_preview.jpg') . '" />';
                if ($data->files) {
                        $images = @unserialize($data->files);
                        if (is_array($images) && sizeof($images)) {
                                $preview = '<img class="product-preview" src="' . base_url('files/timeline_files/' . $images[sizeof($images) - 1]['file_name']) . '" />';
                        }
                }

                if ($this->check_permission('bom_material_read_production_name')) {
                        $row_data = array(
                                anchor(get_uri('stock/material_view/' . $data->id), $data->id),
                                $preview,
                                anchor(get_uri('stock/material_view/' . $data->id), $data->name),
                                $data->production_name ? $data->production_name : '-',
                                $data->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                                // $data->barcode? Barcode::render('code128', 'image', @$databarcode, @$rendererOptions ): '-',
                                $data->category ? $data->category : '-',
                                // $data->account_id ? $this->Account_category_model->account_by($data->account_id) : "-",
                                $data->description ? $data->description : '-',
                                $data->remaining ? to_decimal_format3($data->remaining) : 0,
                                $data->unit ? $data->unit : '-'
                        );
                } else {
                        $row_data = array(
                                anchor(get_uri('stock/material_view/' . $data->id), $data->id),
                                $preview,
                                anchor(get_uri('stock/material_view/' . $data->id), $data->name),
                                $data->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                                $data->category ? $data->category : '-',
                                // $data->account_id ? $this->Account_category_model->account_by($data->account_id) : "-",
                                $data->description ? $data->description : '-',
                                $data->remaining ? to_decimal_format3($data->remaining) : 0,
                                $data->unit ? $data->unit : '-'
                        );
                }

                $options = '';
                if ($this->check_permission('bom_material_update')) {
                        $options .= modal_anchor(get_uri("stock/material_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_material_edit'), "data-post-id" => $data->id));
                } else {
                        $options .= modal_anchor(get_uri("stock/material_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_material_edit'), "data-post-id" => $data->id));
                }
                if ($this->check_permission('bom_material_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_material_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/material_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _material_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_materials_model->get_details($options)->row();
                return $this->_material_make_row($data);
        }

        function material_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_material_create');
                $view_data['can_update'] = $this->check_permission('bom_material_update');
                $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                $material_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_materials_model->get_one($material_id);
                $view_data["category_dropdown"] = $this->Bom_materials_model->get_category_dropdown();
                $view_data["account_category"] = $this->Account_category_model->get_list_dropdown();

                if (empty($view_data['model_info']->id)) {
                        if (!$this->check_permission('bom_material_create'))
                                redirect("forbidden");
                }

                // var_dump(arr($view_data["category_dropdown"])); var_dump(arr($view_data["account_category"])); exit;
                $this->load->view('stock/material/modal', $view_data);
        }

        function material_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $material_id = $this->input->post('id');
                if (empty($material_id)) {
                        if (!$this->check_permission('bom_material_create')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                } else {
                        if (!$this->check_permission('bom_material_update')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                }

                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "name" => "required",
                                "unit" => "required"
                        )
                );

                $id = $this->input->post('id');
                $category_id = $this->input->post('category_id');
                $account_id = $this->input->post('account_id');
                
                $data = array(
                        "name" => $this->input->post('name'),
                        "category_id" => $category_id ? $category_id : null,
                        "account_id" => $account_id ? $account_id : null,
                        "description" => $this->input->post('description') ? $this->input->post('description') : '',
                        "type" => $this->input->post('type') ? $this->input->post('type') : '',
                        "unit" => $this->input->post('unit'),
                        "barcode" => $this->input->post('barcode'),
                        "noti_threshold" => $this->input->post('noti_threshold')
                );

                $new_files = [];
                $target_path = get_setting("timeline_file_path");
                $timeline_file_path = get_setting("timeline_file_path");

                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "material");
                $new_files = unserialize($files_data);
                if ($id) {
                        $mat_info = $this->Bom_materials_model->get_one($id);
                        $new_files = update_saved_files($timeline_file_path, $mat_info->files, $new_files);
                }
                // ALTER TABLE `bom_materials` ADD COLUMN `files` MEDIUMTEXT AFTER `unit`;
                // ALTER TABLE `bom_materials` ADD `account_id` INT NULL AFTER `category_id`; 

                if (!$material_id) {
                        $data["created_date"] = get_current_utc_time();
                }

                if ($this->check_permission('bom_material_read_production_name')) {
                        $data["production_name"] = $this->input->post('production_name') ? $this->input->post('production_name') : '';
                }

                $data["files"] = serialize($new_files);
                $data = clean_data($data);

                $save_id = $this->Bom_materials_model->save($data, $material_id);
                // var_dump(arr($save_id)); exit;

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->_material_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function material_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_delete')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_materials_model->delete_material_and_sub_items($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function material_view($material_id = 0, $tab = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                if ($material_id) {
                        $options = array("id" => $material_id);
                        $material_info = $this->Bom_materials_model->get_details($options)->row();
                        if ($material_info) {

                                $view_data['material_info'] = $material_info;

                                $view_data["tab"] = $tab;
                                $view_data["view_type"] = "";

                                $view_data["hidden_menu"] = array("material-mixing");
                                if (!$this->bom_can_access_supplier()) {
                                        $view_data["hidden_menu"][] = "material-pricings";
                                }
                                if (!$this->bom_can_access_restock()) {
                                        $view_data["hidden_menu"][] = "material-remaining";
                                        $view_data["hidden_menu"][] = "material-used";
                                }

                                $this->template->rander("stock/material/view", $view_data);
                        } else {
                                show_404();
                        }
                } else {
                        show_404();
                }
        }

        function material_info($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                if ($material_id) {
                        $view_data['can_create'] = $this->check_permission('bom_material_create');
                        $view_data['can_update'] = $this->check_permission('bom_material_update');
                        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                        $view_data['model_info'] = $this->Bom_materials_model->get_one($material_id);
                        $view_data["category_dropdown"] = json_encode($this->Bom_materials_model->get_category_dropdown());

                        $view_data['label_column'] = "col-md-2";
                        $view_data['field_column'] = "col-md-10";

                        $this->load->view('stock/material/info', $view_data);
                }
        }
        // END: Material

        // START: Material Import
        function material_import_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }
                $this->load->view('stock/material/modal_import');
        }

        function material_sample_excel_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                // $file_name = "import-materials-sample.xlsx";
                // if($this->check_permission("bom_material_read_production_name") == true) $file_name = "import_inc_name-materials-sample.xlsx";
                // download_app_files(get_setting("system_file_path"),serialize(array(array("file_name" => $file_name))));

                $file_name = "import-materials-sample-new.xlsx";
                download_app_files("assets/", serialize(array(array("file_name" => $file_name))));
        }

        function material_upload_excel_file()
        {
                upload_file_to_temp(true);
        }

        function material_validate_import_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $file_name = $this->input->post("file_name");
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!is_valid_file_to_upload($file_name)) {
                        echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
                        exit();
                }

                if ($file_ext == "xlsx") {
                        echo json_encode(array("success" => true));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('please_upload_a_excel_file') . " (.xlsx)"));
                }
        }

        function material_validate_import_file_data($check_on_submit = false)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $table_data = "";
                $error_message = "";
                $headers = array();
                $got_error_header = false; // We've to check the valid headers first, and a single header at a time
                $got_error_table_data = false;

                $file_name = $this->input->post("file_name");
                require_once(APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php");

                $temp_file_path = get_setting("temp_file_path");
                $excel_file = new SpreadsheetReader($temp_file_path . $file_name);

                $table_data .= '<table class="table table-responsive table-bordered table-hover" style="width: 100%; color: #444;">';

                $table_data_header_array = array();
                $table_data_body_array = array();

                foreach ($excel_file as $row_key => $value) {
                        if ($row_key == 0) { // Validate headers
                                $headers = $this->_material_store_headers_position($value);

                                foreach ($headers as $row_data) {
                                        $has_error_class = false;
                                        if (get_array_value($row_data, "has_error") && !$got_error_header) {
                                                $has_error_class = true;
                                                $got_error_header = true;

                                                if (get_array_value($row_data, "custom_field")) {
                                                        $error_message = lang("no_such_custom_field_found");
                                                } else {
                                                        $error_message = sprintf(lang("import_client_error_header"), lang(get_array_value($row_data, "key_value")));
                                                }
                                        }

                                        array_push($table_data_header_array, array("has_error_class" => $has_error_class, "value" => get_array_value($row_data, "value")));
                                }
                        } else { // Validate data
                                $error_message_on_this_row = "<ol class='pl15'>";
                                $has_contact_first_name = get_array_value($value, 1) ? true : false;

                                foreach ($value as $key => $row_data) {
                                        $has_error_class = false;

                                        if (!$got_error_header) {
                                                $row_data_validation = $this->_material_row_data_validation_and_get_error_message($key, $row_data, $has_contact_first_name);
                                                if ($row_data_validation) {
                                                        $has_error_class = true;
                                                        $error_message_on_this_row .= "<li>" . $row_data_validation . "</li>";
                                                        $got_error_table_data = true;
                                                }
                                        }

                                        $table_data_body_array[$row_key][] = array("has_error_class" => $has_error_class, "value" => $row_data);
                                }

                                $error_message_on_this_row .= "</ol>";

                                //error messages for this row
                                if ($got_error_table_data) {
                                        $table_data_body_array[$row_key][] = array("has_error_text" => true, "value" => $error_message_on_this_row);
                                }
                        }
                }

                //return false if any error found on submitting file
                if ($check_on_submit) {
                        return ($got_error_header || $got_error_table_data) ? false : true;
                }

                //add error header if there is any error in table body
                if ($got_error_table_data) {
                        array_push($table_data_header_array, array("has_error_text" => true, "value" => lang("error")));
                }

                //add headers to table
                $table_data .= "<tr>";
                foreach ($table_data_header_array as $table_data_header) {
                        $error_class = get_array_value($table_data_header, "has_error_class") ? "error" : "";
                        $error_text = get_array_value($table_data_header, "has_error_text") ? "text-danger" : "";
                        $value = get_array_value($table_data_header, "value");
                        $table_data .= "<th class='$error_class $error_text'>" . $value . "</th>";
                }
                $table_data .= "<tr>";

                //add body data to table
                foreach ($table_data_body_array as $table_data_body_row) {
                        $table_data .= "<tr>";

                        foreach ($table_data_body_row as $table_data_body_row_data) {
                                $error_class = get_array_value($table_data_body_row_data, "has_error_class") ? "error" : "";
                                $error_text = get_array_value($table_data_body_row_data, "has_error_text") ? "text-danger" : "";
                                $value = get_array_value($table_data_body_row_data, "value");
                                $table_data .= "<td class='$error_class $error_text'>" . $value . "</td>";
                        }

                        $table_data .= "<tr>";
                }

                //add error message for header
                if ($error_message) {
                        $total_columns = count($table_data_header_array);
                        $table_data .= "<tr><td class='text-danger' colspan='$total_columns'><i class='fa fa-warning'></i> " . $error_message . "</td></tr>";
                }

                $table_data .= "</table>";

                echo json_encode(array("success" => true, 'table_data' => $table_data, 'got_error' => ($got_error_header || $got_error_table_data) ? true : false));
        }

        private function _material_get_allowed_headers()
        {
                return array(
                        "material_code",
                        "material_name",
                        "description",
                        "unit_name"
                );
        }

        private function _material_store_headers_position($headers_row = array())
        {
                $allowed_headers = $this->_material_get_allowed_headers();

                //check if all headers are correct and on the right position
                $final_headers = array();
                foreach ($headers_row as $key => $header) {
                        $key_value = str_replace(' ', '_', strtolower($header));
                        $header_on_this_position = get_array_value($allowed_headers, $key);
                        $header_array = array("key_value" => $header_on_this_position, "value" => $header);

                        if ($header_on_this_position == $key_value) {
                                //allowed headers
                                //the required headers should be on the correct positions
                                //the rest headers will be treated as custom fields
                                //pushed header at last of this loop
                        } else if ((count($allowed_headers) - 1) < $key) {
                                //custom fields headers
                                //check if there is any existing custom field with this title
                                if (!$this->_get_existing_custom_field_id($header)) {
                                        $header_array["has_error"] = true;
                                        $header_array["custom_field"] = true;
                                }
                        } else { //invalid header, flag as red
                                $header_array["has_error"] = true;
                        }

                        array_push($final_headers, $header_array);
                }
                return $final_headers;
        }

        private function _material_row_data_validation_and_get_error_message($key, $data, $has_contact_first_name)
        {
                $allowed_headers = $this->_material_get_allowed_headers();
                $header_value = get_array_value($allowed_headers, $key);

                //company name field is required
                if ($header_value == "company_name" && !$data) {
                        return lang("import_client_error_company_name_field_required");
                }

                //if there is contact first name then the contact last name and email is required
                //the email should be unique then
                if ($has_contact_first_name) {
                        if ($header_value == "contact_last_name" && !$data) {
                                return lang("import_client_error_contact_name");
                        }

                        if ($header_value == "contact_email") {
                                if ($data) {
                                        if ($this->Users_model->is_email_exists($data)) {
                                                return lang("duplicate_email");
                                        }
                                } else {
                                        return lang("import_client_error_contact_email");
                                }
                        }
                }
        }

        function material_save_client_from_excel_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $file_name = $this->input->post('file_name');
                require_once(APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php");

                $temp_file_path = get_setting("temp_file_path");
                $excel_file = new SpreadsheetReader($temp_file_path . $file_name);
                $allowed_headers = $this->_material_get_allowed_headers();
                $now = get_current_utc_time();

                foreach ($excel_file as $key => $value) { //rows
                        if ($key === 0) { // first line is headers, modify this for custom fields and continue for the next loop
                                continue;
                        }

                        $material = [
                                'name' => $value[0],
                                'production_name' => $value[1],
                                'description' => $value[2],
                                'unit' => $value[3],
                                'created_date' => $now
                        ];
                        // Save material data
                        if (!$this->Bom_materials_model->duplicated_name($material['name'])) {
                                $material_id = $this->Bom_materials_model->save($material);
                                if (!$material_id) {
                                        continue;
                                }
                        }
                }

                delete_file_from_directory($temp_file_path . $file_name); // delete temp file
                echo json_encode(array('success' => true, 'message' => lang("record_saved")));
        }
        // END: Material Import

        // START: Material Mixing
        function material_mixings($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        redirect("forbidden");
                }

                if ($material_id) {
                        $view_data['material_info'] = $this->Bom_materials_model->get_one($material_id);
                        $view_data['material_dropdown'] = $this->Bom_materials_model->get_details([
                                'except_id' => $material_id
                        ])->result();
                        $view_data['material_mixing'] = $this->Bom_materials_model->get_mixings([
                                'id' => $material_id
                        ])->result();

                        $this->load->view('stock/material/mixing', $view_data);
                }
        }

        function material_mixing_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $material_id = $this->input->post('id');
                $type = $this->input->post('type');

                $data = array("type" => $type);
                $data = clean_data($data);
                $save_id = $this->Bom_materials_model->save($data, $material_id);

                if ($type) {
                        $this->Bom_materials_model->mixing_save(
                                $material_id,
                                $this->input->post('ratio'),
                                $this->input->post('using_material_id[]'),
                                $this->input->post('using_ratio[]')
                        );
                }

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }
        // END: Material Mixing

        // START: Material Pricing
        function material_pricings($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_supplier()) {
                        redirect("forbidden");
                }

                $view_data['can_update'] = $this->check_permission('bom_material_update');
                $view_data['can_update_supplier'] = $this->bom_can_access_supplier()
                        && $this->check_permission('bom_supplier_update');

                $view_data['material_id'] = $material_id;

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->load->view("stock/material/pricing", $view_data);
        }

        function material_pricing_list($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_supplier()) {
                        redirect("forbidden");
                }

                $list_data = $this->Bom_materials_model->get_pricings([
                        'material_id' => $material_id
                ])->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_pricing_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _material_pricing_make_row($data)
        {
                $contact_name = '';
                if (!empty($data->contact_first_name) || !empty($data->contact_last_name)) {
                        $contact_name = $data->contact_first_name . ' ' . $data->contact_last_name;
                }

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/supplier_view/' . $data->supplier_id), $data->company_name),
                        $contact_name ? $contact_name : '-',
                        $data->contact_phone ? $data->contact_phone : '-',
                        $data->contact_email ? $data->contact_email : '-',
                        to_decimal_format2($data->ratio) . ' ' . $data->unit,
                        to_currency($data->price)
                );

                $options = '';
                if (
                        $this->bom_can_access_supplier() && $this->check_permission('bom_supplier_update')
                        && $this->bom_can_access_material() && $this->check_permission('bom_material_update')
                ) {
                        $options .= modal_anchor(get_uri("stock/material_pricing_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_material_pricing_edit'), "data-post-id" => $data->id, "data-post-material_id" => $data->material_id))
                                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_material_pricing_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/material_pricing_delete"), "data-action" => "delete-confirmation"));
                } else {
                        $options .= modal_anchor(get_uri("stock/material_pricing_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_material_pricing_edit'), "data-post-id" => $data->id, "data-post-material_id" => $data->material_id));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _material_pricing_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_materials_model->get_pricings($options)->row();
                return $this->_material_pricing_make_row($data);
        }

        function material_pricing_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_supplier() || !$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $view_data['can_update'] = $this->check_permission('bom_supplier_update')
                        && $this->check_permission('bom_material_update');

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_material_pricings_model->get_one($id);

                $material_id = $this->input->post('material_id');
                if ($material_id && empty($view_data['model_info']->material_id)) {
                        $view_data['model_info']->material_id = $material_id;
                        $view_data['material'] = $this->Bom_materials_model->get_one($material_id);
                        $view_data['supplier_dropdown'] = $this->Bom_suppliers_model->get_supplier_pricing_dropdown($material_id);
                } else {
                        $view_data['material'] = $this->Bom_materials_model->get_one($material_id);
                        $view_data['supplier_dropdown'] = $this->Bom_suppliers_model->get_supplier_pricing_dropdown();
                }

                $this->load->view('stock/material/modal_pricing', $view_data);
        }

        function material_pricing_save()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')
                        || !$this->bom_can_access_material() || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "material_id" => "required|numeric",
                                "supplier_id" => "required|numeric",
                                "ratio" => "required|numeric",
                                "price" => "required|numeric"
                        )
                );

                $data = array(
                        "material_id" => $this->input->post('material_id'),
                        "supplier_id" => $this->input->post('supplier_id'),
                        "ratio" => $this->input->post('ratio'),
                        "price" => $this->input->post('price')
                );
                $data = clean_data($data);
                $save_id = $this->Bom_material_pricings_model->save($data, $id);

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->_material_pricing_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function material_pricing_delete()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_supplier() || !$this->check_permission('bom_supplier_update')
                        || !$this->bom_can_access_material() || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_material_pricings_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }
        // END: Material Pricing

        // START: Material Files
        function material_files($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_update'] = $this->check_permission('bom_material_update');

                $view_data['material_id'] = $material_id;
                $this->load->view("stock/material/files", $view_data);
        }

        function material_file_list($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array("material_id" => $material_id);
                $list_data = $this->Bom_material_files_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_file_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _material_file_make_row($data)
        {
                $file_icon = get_file_icon(strtolower(pathinfo($data->file_name, PATHINFO_EXTENSION)));

                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $data->user_name";
                if ($data->user_type == "staff") {
                        $uploaded_by = get_team_member_profile_link($data->uploaded_by, $user_name);
                } else {
                        $uploaded_by = get_client_contact_profile_link($data->uploaded_by, $user_name);
                }

                $description = "<div class='pull-left'>"
                        . js_anchor(remove_file_prefix($data->file_name), array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("stock/material_view_file/" . $data->id)));
                if ($data->description) {
                        $description .= "<br /><span>" . $data->description . "</span></div>";
                } else {
                        $description .= "</div>";
                }

                $options = anchor(get_uri("stock/material_download_file/" . $data->id), "<i class='fa fa fa-cloud-download'></i>", array("title" => lang("download")));
                if ($this->check_permission('bom_material_update')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_file'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/material_delete_file"), "data-action" => "delete-confirmation"));
                }

                return array(
                        $data->id,
                        "<div class='fa fa-$file_icon font-22 mr10 pull-left'></div>" . $description,
                        convert_file_size($data->file_size),
                        $uploaded_by,
                        format_to_datetime($data->created_at),
                        $options
                );
        }

        function material_view_file($file_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $file_info = $this->Bom_material_files_model->get_details(array("id" => $file_id))->row();
                if ($file_info) {
                        if (!$file_info->material_id)
                                redirect("forbidden");

                        $view_data['can_comment_on_files'] = false;
                        $file_url = get_source_url_of_file(make_array_of_file($file_info), get_general_file_path("client", $file_info->material_id));

                        $view_data["file_url"] = $file_url;
                        $view_data["is_image_file"] = is_image_file($file_info->file_name);
                        $view_data["is_google_preview_available"] = is_google_preview_available($file_info->file_name);
                        $view_data["is_viewable_video_file"] = is_viewable_video_file($file_info->file_name);
                        $view_data["is_google_drive_file"] = ($file_info->file_id && $file_info->service_type == "google") ? true : false;

                        $view_data["file_info"] = $file_info;
                        $view_data['file_id'] = $file_id;
                        $this->load->view("stock/material/view_file", $view_data);
                } else {
                        show_404();
                }
        }

        function material_download_file($id)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $file_info = $this->Bom_material_files_model->get_one($id);
                if (!$file_info->material_id)
                        redirect("forbidden");

                $file_data = serialize(array(make_array_of_file($file_info)));

                download_app_files(get_general_file_path("client", $file_info->material_id), $file_data);
        }

        function material_delete_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                $info = $this->Bom_material_files_model->get_one($id);

                if ($this->Bom_material_files_model->delete_one($id)) {
                        delete_app_files(get_general_file_path("client", $info->material_id), array(make_array_of_file($info)));
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function material_file_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        redirect("forbidden");
                }

                $view_data['model_info'] = $this->Bom_material_files_model->get_one($this->input->post('id'));
                $material_id = $this->input->post('material_id')
                        ? $this->input->post('material_id')
                        : $view_data['model_info']->material_id;

                $view_data['material_id'] = $material_id;
                $this->load->view('stock/material/modal_file', $view_data);
        }

        function material_save_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $material_id = $this->input->post('material_id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "material_id" => "required|numeric"
                        )
                );

                $files = $this->input->post("files");
                $success = false;
                $now = get_current_utc_time();

                $target_path = getcwd() . "/" . get_general_file_path("client", $material_id);

                if ($files && get_array_value($files, 0)) {
                        foreach ($files as $file) {
                                $file_name = $this->input->post('file_name_' . $file);
                                $file_info = move_temp_file($file_name, $target_path);
                                if ($file_info) {
                                        $data = array(
                                                "material_id" => $material_id,
                                                "file_name" => get_array_value($file_info, 'file_name'),
                                                "file_id" => get_array_value($file_info, 'file_id'),
                                                "service_type" => get_array_value($file_info, 'service_type'),
                                                "description" => $this->input->post('description_' . $file),
                                                "file_size" => $this->input->post('file_size_' . $file),
                                                "created_at" => $now,
                                                "uploaded_by" => $this->login_user->id
                                        );
                                        $success = $this->Bom_material_files_model->save($data);
                                } else {
                                        $success = false;
                                }
                        }
                }

                if ($success) {
                        echo json_encode(array("success" => true, 'message' => lang('record_saved')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }
        // END: Material Files

        // START: Material Remaining
        function material_remainings($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                if ($material_id) {
                        $view_data['material_id'] = $material_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/material/remaining', $view_data);
                }
        }

        function material_remaining_list($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "material_id" => $material_id
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_stock_groups_model->get_restocks($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_remaining_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _material_remaining_make_row($data)
        {
                $remaining_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $remaining_value = $data->price * $data->remaining / $data->stock;
                }

                $user_name = $data->user_first_name . ' ' . $data->user_last_name;
                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $user_name";

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/restock_view/' . $data->group_id), $data->group_name),
                        anchor(get_uri('team_members/view/' . $data->user_id), $user_name),
                        format_to_date($data->created_date),
                        is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
                        to_decimal_format2($data->stock) . ' ' . $data->material_unit,
                        to_decimal_format2($data->remaining) . ' ' . $data->material_unit
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_currency($data->price);
                        $row_data[] = to_currency($remaining_value);
                }

                $options = '';
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) {
                        $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"))
                                . modal_anchor(get_uri("stock/restock_withdraw_modal"), "<i class='fa fa-share-square-o'></i>", array("class" => "edit", "title" => lang('stock_restock_withdraw'), "data-post-id" => $data->id, "data-post-view" => "material"));
                } else {
                        $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"));
                }
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_view_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _material_remaining_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_stock_groups_model->get_restocks($options)->row();
                return $this->_material_remaining_make_row($data);
        }
        // END: Material Remaining

        // START: Material Used History
        function material_used($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                if ($material_id) {
                        $view_data['material_id'] = $material_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/material/used_list', $view_data);
                }
        }

        function material_used_list($material_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "material_id" => $material_id
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_project_item_materials_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_used_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _material_used_make_row($data)
        {
                $used_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $used_value = $data->price * $data->ratio / $data->stock;
                }

                $row_data = array(
                        $data->id,
                        !empty($data->stock_name) ? anchor(get_uri('stock/restock_view/' . $data->group_id), $data->stock_name) : '-',
                        !empty($data->project_title) ? anchor(get_uri('projects/view/' . $data->project_id), $data->project_title) : '-',
                        is_date_exists($data->created_at) ? format_to_date($data->created_at, false) : '-',
                        !empty($data->note) ? $data->note : '-',
                        to_decimal_format2($data->ratio) . ' ' . $data->material_unit,
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_currency($used_value);
                }

                return $row_data;
        }

        private function _material_used_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_project_item_materials_model->get_details($options)->row();
                return $this->_material_used_make_row($data);
        }
        // END: Material Used History

        // START: Material Category
        function material_category_modal()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        redirect("forbidden");
                }

                $type = $this->input->post("type");
                if ($type) {
                        $model_info = new stdClass();
                        $model_info->color = "";

                        $view_data["type"] = $type;
                        $view_data["model_info"] = $model_info;

                        $view_data["existing_categories"] = $this->Bom_materials_model->get_categories()->result();

                        $this->load->view("stock/material/modal_category", $view_data);
                }
        }

        function material_category_save()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "title" => "required"
                        )
                );

                $data = array(
                        "id" => $id,
                        "title" => $this->input->post('title')
                );
                if (!$id) {
                        $save_id = $this->Bom_materials_model->category_create($data);
                } else {
                        $save_id = $this->Bom_materials_model->category_update($data);
                }

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->Bom_materials_model->get_categories(['id' => $save_id])->row(),
                                        'id' => $save_id,
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function material_category_delete()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_materials_model->category_delete($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted'), 'id' => $id));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }
        // END: Material Category

        // START: Material Reports
        function material_report()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_update'] = $this->check_permission('bom_material_update')
                        && $this->check_permission('bom_restock_update');
                $view_data['can_delete'] = $this->check_permission('bom_material_delete')
                        && $this->check_permission('bom_restock_delete');
                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                $view_data['is_admin'] = $this->login_user->is_admin;
                $view_data['add_pr_row'] = $this->cp('purchaserequests', 'add_row');
                $this->template->rander("stock/material/report", $view_data);
        }

        function material_report_list()
        {
                $is_zero = $this->input->post("is_zero");

                // $this->check_module_availability("module_stock");
                if (!$this->cop('view_row') || !$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $options = array();
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }

                if (isset($is_zero) && !empty($is_zero)) {
                        $options["is_zero"] = $is_zero;
                }

                $list_data = $this->Bom_stock_groups_model->get_restocks2($options)->result();
                // var_dump(arr($list_data)); exit;
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_material_report_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _material_report_make_row($data)
        {
                $remaining_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $remaining_value = $data->price * $data->remaining / $data->stock;
                }
                $material_name = $data->material_name;

                if ($this->check_permission("bom_material_read_production_name") == true) {
                        $material_name .= " - " . $data->production_name;
                        $lack = $data->noti_threshold - $data->remaining;
                        $is_lack = $lack > 0 ? true : false;
                        $row_data = array(
                                $data->id,
                                anchor(get_uri('stock/restock_view/' . $data->group_id), $data->group_name),
                                anchor(get_uri('stock/material_view/' . $data->material_id), $material_name),
                                format_to_date($data->created_date),
                                is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
                                to_decimal_format2($data->stock),
                                '<span class="' . ($is_lack ? 'lacked_material' : '') . '" data-material-id="' . $data->id . '" data-lacked-amount="' . ($is_lack ? $lack : 0) . '" data-unit="' . $data->material_unit . '" data-supplier-id="' . $data->supplier_id . '" data-supplier-name="' . $data->supplier_name . '" data-price="' . $data->price . '" data-currency="' . $data->currency . '" data-currency-symbol="' . $data->currency_symbol . '">' . to_decimal_format2($data->remaining) . '</span>',
                                strtoupper($data->material_unit)
                        );
                }
                
                if ($this->check_permission('bom_restock_read_price')) {
                        $price_per_stock = 0;
                        if ($data->stock != 0) {
                                $price_per_stock = $data->price / $data->stock;
                        }

                        $row_data[] = to_decimal_format3($data->price, 2);
                        $row_data[] = to_decimal_format3($price_per_stock);
                        $row_data[] = to_decimal_format3($remaining_value, 2);
                        $row_data[] = !empty($data->currency) && isset($data->currency) ? lang($data->currency) : lang("THB");
                }

                // $options = '';
                // if($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) 
                //   $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"))
                //     . modal_anchor(get_uri("stock/restock_withdraw_modal"), "<i class='fa fa-share-square-o'></i>", array("class" => "edit", "title" => lang('stock_restock_withdraw'), "data-post-id" => $data->id, "data-post-view" => "material"));
                // } else {
                //   $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"));
                // }
                // if($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                //   $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_view_delete"), "data-action" => "delete-confirmation"));
                // }
                // $row_data[] = $options;

                // var_dump(arr($row_data)); exit;

                return $row_data;
        }
        // END: Material Reports

        // START: Restock
        function restocks()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read'] = $this->check_permission('bom_restock_read');
                $view_data['can_create'] = $this->check_permission('bom_restock_create');

                $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->template->rander("stock/restock/index", $view_data);
        }

        function restock_list()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $options = array(
                        "created_by" => $this->input->post("created_by")
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_stock_groups_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_restock_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _restock_make_row($data)
        {
                $user_name = $data->user_first_name . ' ' . $data->user_last_name;
                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $user_name";

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/restock_view/' . $data->id), $data->name),
                        $data->po_no ? $data->po_no : '-',
                        anchor(get_uri('team_members/view/' . $data->user_id), $user_name),
                        format_to_date($data->created_date)
                );

                $options = '';
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) {
                        $options .= modal_anchor(get_uri("stock/restock_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id));
                } else {
                        $options .= modal_anchor(get_uri("stock/restock_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id));
                }
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _restock_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_stock_groups_model->get_details($options)->row();
                return $this->_restock_make_row($data);
        }

        function restock_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
                $view_data['can_create'] = $this->check_permission('bom_restock_create');
                $view_data['can_update'] = $this->check_permission('bom_restock_update');

                $group_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_stock_groups_model->get_one($group_id);

                if (empty($view_data['model_info']->id)) {
                        if (!$this->check_permission('bom_restock_create'))
                                redirect("forbidden");
                } else {
                        $created_by = $view_data['model_info']->created_by;
                        if (!$this->bom_can_read_restock($created_by))
                                redirect("forbidden");
                }

                $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

                $view_data['can_read_material_name'] = $this->dev2_canReadMaterialName();
                $view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();
                
                $view_data['material_restocks'] = $this->Bom_stock_groups_model->get_restocks([
                        'group_id' => $group_id ? $group_id : -1
                ])->result();

                $this->load->view('stock/restock/modal', $view_data);
        }

        function restock_save()
        {
            $this->check_module_availability("module_stock");
            if (!$this->bom_can_access_restock()) {
                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                exit;
            }

            $id = $this->input->post('id');
            if (empty($id)) {
                if (!$this->check_permission('bom_restock_create')) {
                    echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                    exit;
                }
            } else {
                if (!$this->check_permission('bom_restock_update')) {
                    echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                    exit;
                }
            }
            
            validate_submitted_data(array(
                "id" => "numeric"
            ));
            
            $data = array(
                "name" => $this->input->post('name'),
                "created_by" => $this->input->post('created_by'),
                "created_date" => $this->input->post('created_date'),
                "po_no" => $this->input->post('po_no')
            );
            if (!$this->login_user->is_admin || empty($data["created_by"])) {
                $data["created_by"] = $this->login_user->id;
            }
            $data = clean_data($data);

            $save_id = $this->Bom_stock_groups_model->save($data, $id);
            if ($save_id) {
                $restock_ids = $this->input->post('restock_id[]');
                $material_ids = $this->input->post('material_id[]');
                $stocks = $this->input->post('stock[]');
                $prices = $this->input->post('price[]');
                $serial_numbers = $this->input->post('restock_serial[]');
                
                if (isset($restock_ids) && isset($material_ids) && isset($stocks)) {
                    $this->Bom_stock_groups_model->restock_save(
                        $save_id,
                        $restock_ids,
                        $material_ids,
                        $stocks,
                        $prices,
                        $serial_numbers
                    );
                }

                $data = array();
                $new_source_data = $this->Bom_stocks_model->dev2_getRestockingById($save_id);
                foreach ($new_source_data as $item) {
                    $data[] = $this->dev2_rowDataList($item);
                }
                
                echo json_encode(array(
                    'success' => true,
                    'data' => $data,
                    'id' => $save_id,
                    'view' => $this->input->post('view'),
                    'message' => lang('record_saved')
                ));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
        }

        function restock_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock() || !$this->check_permission('bom_restock_delete')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_stock_groups_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }
        // END: Restock

        // START: Restock item View
        function restock_view($restock_id = 0, $tab = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                if ($restock_id) {
                        $options = array("id" => $restock_id);
                        $restock_info = $this->Bom_stock_groups_model->get_details($options)->row();
                        if ($restock_info) {

                                $view_data['restock_info'] = $restock_info;

                                $created_by = $view_data['restock_info']->created_by;
                                if (!$this->bom_can_read_restock($created_by))
                                        redirect("forbidden");

                                $view_data["tab"] = $tab;
                                $view_data["view_type"] = "";

                                $view_data['hidden_menu'] = array("");

                                $this->template->rander("stock/restock/view", $view_data);
                        } else {
                                show_404();
                        }
                } else {
                        show_404();
                }
        }

        function restock_info($restock_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
                $view_data['can_create'] = $this->check_permission('bom_restock_create');
                $view_data['can_update'] = $this->check_permission('bom_restock_update');

                if ($restock_id) {
                        $view_data['model_info'] = $this->Bom_stock_groups_model->get_one($restock_id);
                        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

                        $created_by = $view_data['model_info']->created_by;
                        if (!$this->bom_can_read_restock($created_by))
                                redirect("forbidden");

                        $view_data['label_column'] = "col-md-2";
                        $view_data['field_column'] = "col-md-10";

                        $this->load->view('stock/restock/info', $view_data);
                }
        }

        function restock_details($restock_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
                $view_data['can_update'] = $this->check_permission('bom_restock_update');

                if ($restock_id) {
                        $view_data['restock_id'] = $restock_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/restock/details', $view_data);
                }
        }

        function restock_view_list($group_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $options = array(
                        "group_id" => $group_id
                );
                $list_data = $this->Bom_stock_groups_model->get_restocks($options)->result();
                $result = array();
                //echo "<xmp>";var_dump($list_data);echo "</xmp>";exit;
                foreach ($list_data as $data) {
                        $result[] = $this->_restock_view_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _restock_view_make_row($data)
        {
                $remaining_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $remaining_value = $data->price * $data->remaining / $data->stock;
                }

                $files_link = "";
                if ($data->files) {
                        $files = unserialize($data->files);
                        if (count($files)) {
                                foreach ($files as $key => $value) {
                                        $file_name = get_array_value($value, "file_name");
                                        $link = " fa fa-" . get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                                        $files_link .= js_anchor(
                                                " ",
                                                array(
                                                        'title' => "",
                                                        "data-toggle" => "app-modal",
                                                        "data-sidebar" => "0",
                                                        "class" => "pull-left font-22 mr10 $link",
                                                        "title" => remove_file_prefix($file_name),
                                                        "data-url" => get_uri("stock/restock_file_preview/" . $data->id . "/" . $key)
                                                )
                                        );
                                }
                        }
                }

                $material_name = $data->material_name;
                if ($this->check_permission("bom_material_read_production_name") == true) {
                        $material_name .= " - " . $data->production_name;
                }

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/material_view/' . $data->material_id), $material_name),
                        $files_link,
                        is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
                        to_decimal_format2($data->stock),
                        to_decimal_format2($data->remaining),
                        $data->material_unit
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_decimal_format3($data->price);
                        $row_data[] = to_decimal_format3($remaining_value);
                        $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
                }

                $options = '';
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) {
                        $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id))
                                . modal_anchor(get_uri("stock/restock_withdraw_modal"), "<i class='fa fa-share-square-o'></i>", array("class" => "edit", "title" => lang('stock_restock_withdraw'), "data-post-id" => $data->id, "data-post-view" => "restock"));
                } else {
                        $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id));
                }
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_view_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _restock_view_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_stock_groups_model->get_restocks($options)->row();
                return $this->_restock_view_make_row($data);
        }

        function restock_view_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
                $view_data['can_create'] = $this->check_permission('bom_restock_create');
                $view_data['can_update'] = $this->check_permission('bom_restock_update');
                $view_data['bom_material_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                $restock_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_stocks_model->get_one($restock_id);

                if (empty($view_data['model_info']->id)) {
                        if (!$this->check_permission('bom_restock_create'))
                                redirect("forbidden");
                } else {
                        $group_info = $this->Bom_stock_groups_model->get_one($view_data['model_info']->group_id);
                        $created_by = $group_info->created_by;
                        if (!$this->bom_can_read_restock($created_by))
                                redirect("forbidden");
                }

                $group_id = $this->input->post('group_id');
                if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
                        $view_data['model_info']->group_id = $group_id;
                }

                $view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();

                $this->load->view('stock/restock/modal_view', $view_data);
        }

        function restock_view_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                if (empty($id)) {
                        if (!$this->check_permission('bom_restock_create')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                } else {
                        if (!$this->check_permission('bom_restock_update')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                }

                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "group_id" => "required|numeric"
                        )
                );

                $data = array(
                        "group_id" => $this->input->post('group_id'),
                        "material_id" => $this->input->post('material_id'),
                        "stock" => $this->input->post('stock'),
                        "remaining" => $this->input->post('remaining'),
                        "note" => $this->input->post('note'),
                        "expiration_date" => $this->input->post('expiration_date')
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $data["price"] = $this->input->post('price');
                }

                $target_path = get_setting("timeline_file_path");
                $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "material");
                $new_files = unserialize($files_data);
                if ($id) {
                        $model_info = $this->Bom_stocks_model->get_one($id);
                        $timeline_file_path = get_setting("timeline_file_path");
                        $new_files = update_saved_files($timeline_file_path, $model_info->files, $new_files);
                }
                $data["files"] = serialize($new_files);

                $data = clean_data($data);

                $save_id = $this->Bom_stocks_model->save($data, $id);
                if ($save_id) {
                        $view = $this->input->post('view');
                        if (isset($view) && $view == 'material') {
                                echo json_encode(
                                        array(
                                                "success" => true,
                                                "data" => $this->_material_remaining_row_data($save_id),
                                                'id' => $save_id,
                                                'view' => $this->input->post('view'),
                                                'message' => lang('record_saved')
                                        )
                                );
                        } else {
                                echo json_encode(
                                        array(
                                                "success" => true,
                                                "data" => $this->_restock_view_row_data($save_id),
                                                'id' => $save_id,
                                                'view' => $this->input->post('view'),
                                                'message' => lang('record_saved')
                                        )
                                );
                        }
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function restock_view_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                // if (!$this->can_edit_clients()) {
                //   redirect("forbidden");
                // }
                // $this->access_only_allowed_members();

                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                $id = $this->input->post('id');
                if ($this->Bom_stocks_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function restock_withdraw_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                // $this->access_only_allowed_members();
                // if (!$this->can_edit_clients()) {
                //   redirect("forbidden");
                // }

                $restock_id = $this->input->post('id');
                // $this->can_access_this_client($group_id);
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_stocks_model->get_one($restock_id);

                $group_id = $this->input->post('group_id');
                if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
                        $view_data['model_info']->group_id = $group_id;
                }

                $view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();

                $this->load->view('stock/restock/modal_withdraw', $view_data);
        }

        function restock_withdraw_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');

                validate_submitted_data(
                        array(
                                "id" => "required|numeric",
                                "material_id" => "required|numeric",
                                "ratio" => "required|numeric"
                        )
                );

                $ratio = $this->input->post('ratio');
                $data = array(
                        "material_id" => $this->input->post('material_id'),
                        "stock_id" => $id,
                        "ratio" => $ratio,
                        "note" => $this->input->post('note') ? $this->input->post('note') : ''
                );
                $data = clean_data($data);

                $save_id = $this->Bom_project_item_materials_model->save($data, null);
                if ($save_id) {
                        $this->Bom_stocks_model->reduce_material($id, $ratio);

                        $view = $this->input->post('view');
                        if (isset($view) && $view == 'material') {
                                echo json_encode(
                                        array(
                                                "success" => true,
                                                "data" => $this->_material_remaining_row_data($id),
                                                'id' => $id,
                                                'view' => $this->input->post('view'),
                                                'message' => lang('record_saved')
                                        )
                                );
                        } else {
                                echo json_encode(
                                        array(
                                                "success" => true,
                                                "data" => $this->_restock_view_row_data($id),
                                                'id' => $id,
                                                'view' => $this->input->post('view'),
                                                'message' => lang('record_saved')
                                        )
                                );
                        }
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function restock_file_preview($id = "", $key = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                if ($id) {
                        $model_info = $this->Bom_stocks_model->get_one($id);
                        $files = unserialize($model_info->files);
                        $file = get_array_value($files, $key);

                        $file_name = get_array_value($file, "file_name");
                        $file_id = get_array_value($file, "file_id");
                        $service_type = get_array_value($file, "service_type");

                        $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
                        $view_data["is_image_file"] = is_image_file($file_name);
                        $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
                        $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
                        $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

                        $this->load->view("stock/restock/view_file", $view_data);
                } else {
                        show_404();
                }
        }
        // END: Restock View

        // START: Restock Used History
        function restock_used($restock_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                if ($restock_id) {
                        $view_data['restock_id'] = $restock_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/restock/used_list', $view_data);
                }
        }

        function restock_used_list($restock_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $options = array(
                        "restock_id" => $restock_id
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_project_item_materials_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_restock_used_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _restock_used_make_row($data)
        {
                // var_dump(arr($data));
                $used_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $used_value = $data->price * $data->ratio / $data->stock;
                }

                $material_name = $data->material_name;
                if ($this->check_permission("bom_material_read_production_name") == true) {
                        $material_name .= " - " . $data->production_name;
                }

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/material_view/' . $data->material_id), $material_name),
                        !empty($data->project_title) ? anchor(get_uri('projects/view/' . $data->project_id), $data->project_title) : '-',
                        is_date_exists($data->created_at) ? format_to_date($data->created_at, false) : '-',
                        !empty($data->note) ? $data->note : '-',
                        to_decimal_format2($data->ratio),
                        $data->material_unit
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_decimal_format2($used_value);
                        $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
                }

                return $row_data;
        }

        private function _restock_used_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_project_item_materials_model->get_details($options)->row();
                return $this->_restock_used_make_row($data);
        }
        // END: Restock Used History

        // START: Calculator
        function calculator()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_calculator())
                        redirect("forbidden");

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                $view_data['items'] = $this->Items_model->get_items([])->result();
                foreach ($view_data['items'] as $item) {
                        unset($item->files);
                        unset($item->description);
                }

                $view_data['item_mixings'] = $this->Bom_item_mixing_groups_model->get_detail_items([])->result();

                $items = $this->input->post('item_id[]');
                $item_mixings = $this->input->post('item_mixing[]');
                $quantities = $this->input->post('quantity[]');
                if (!empty($items) && !empty($item_mixings) && !empty($quantities)) {
                        $view_data['project_materials'] = $this->Bom_item_mixing_groups_model->calculate($items, $item_mixings, $quantities);
                }

                $this->template->rander("stock/calculator/index", $view_data);
        }

        function calculator_create_excel()
        {
                $this->remove_excel_file();

                $data = $this->input->post('data');
                $filename = 'downloads_' . date('is') . $data['id'] . '.xlsx';

                $header = [
                        lang('item') => 'text',
                        lang('item_mixing_name') => 'text',
                        lang('quantity') => 'text',
                        '!' => 'text',
                        '/' => 'text',
                        '[' => 'text'
                ];

                $headerData = [
                        'title' => $data['title'],
                        'mixing_name' => $data['mixing_name'],
                        'quantity' => $data['quantity'] . ' ' . $data['unit_type']
                ];

                $headerSpace = [
                        'title' => '',
                        'mixing_name' => '',
                        'quantity' => ''
                ];

                $detail = [
                        'stock_material' => lang('stock_material'),
                        'stock_restock_name' => lang('stock_restock_name'),
                        'quantity' => lang('quantity'),
                        'unit' => lang('stock_material_unit'),
                        'stock_calculator_value' => lang('stock_calculator_value'),
                        'currency' => lang('currency')
                ];

                $wExcel = new Ellumilel\ExcelWriter();
                $wExcel->writeSheetHeader('Sheet1', $header);
                $wExcel->writeSheetRow('Sheet1', $headerData);
                $wExcel->writeSheetRow('Sheet1', $headerSpace);
                $wExcel->writeSheetRow('Sheet1', $detail);

                for ($i = 0; $i < sizeof($data['result']); $i++) {
                        $item = $data['result'][$i];
                        $wExcel->writeSheetRow('Sheet1', [
                                $item['material_name'],
                                !empty($item['stock_name']) ? $item['stock_name'] : '-',
                                !empty($item['stock']) ? to_decimal_format2($item['stock']) : '0.00',
                                !empty($item['material_unit']) ? strtoupper($item['material_unit']) : '-',
                                !empty($item['value']) ? to_decimal_format3($item['value']) : '0.00',
                                !empty($item['currency']) ? lang($item['currency']) : lang('THB')
                        ]);
                }

                $wExcel->writeToFile($filename);

                $result = array(
                        'file' => BASE_URL . '/' . $filename
                );
                echo json_encode($result);
        }

        private function remove_excel_file()
        {
                $mask = 'downloads_*.xlsx';
                array_map('unlink', glob($mask));
        }
        // End: Calculator

    function restocks_item()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read'] = $this->check_permission('bom_restock_read');
        $view_data['can_create'] = $this->check_permission('bom_restock_create');
        $view_data['can_read_self'] = $this->check_permission('bom_restock_read_self');

        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);
        $view_data['is_admin'] = $this->login_user->is_admin;

        $this->template->rander("stock/restock_item/index", $view_data);
    }

        function restock_item_list()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                $options = array(
                        "created_by" => $this->input->post("created_by")
                );
                
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }

                $list_data = $this->Bom_item_groups_model->get_details($options)->result();
                // var_dump(arr($list_data)); exit;

                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_restock_item_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _restock_item_make_row($data)
        {
                $user_name = $data->user_first_name . ' ' . $data->user_last_name;
                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $user_name";

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/restock_item_view/' . $data->id), $data->name),
                        $data->po_no ? $data->po_no : '-',
                        anchor(get_uri('team_members/view/' . $data->user_id), $user_name),
                        format_to_date($data->created_date)
                );

                $options = '';
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_item_update')) {
                        $options .= modal_anchor(get_uri("stock/restock_item_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id));
                } else {
                        $options .= modal_anchor(get_uri("stock/restock_item_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id));
                }
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_item_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_item_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _restock_item_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_item_groups_model->get_details($options)->row();
                return $this->_restock_item_make_row($data);
        }

    function restock_item_modal()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['can_create'] = $this->check_permission('bom_restock_create');
        $view_data['can_update'] = $this->check_permission('bom_restock_update');
        $view_data['can_delete'] = $this->check_permission('bom_restock_delete');

        $group_id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";

        $view_data["view"] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_groups_model->get_one($group_id);

        if (empty($view_data['model_info']->id)) {
            if (!$this->check_permission('bom_restock_create'))
                redirect("forbidden");
        } else {
            $created_by = $view_data['model_info']->created_by;
            if (!$this->bom_can_read_restock($created_by))
                redirect("forbidden");
        }

        $view_data["team_members_dropdown"] = $this->get_team_members_dropdown(true);

        $view_data['item_dropdown'] = $this->Items_model->get_details([])->result();
        $view_data['item_restocks'] = $this->Bom_item_groups_model->get_restocks(['group_id' => $group_id ? $group_id : -1])->result();

        foreach ($view_data['item_restocks'] as $key => $value) {
            $view_data['item_restocks'][$key]->can_delete = $this->dev2_canDeleteRestockItem($value->id);
        }

        // var_dump(arr($view_data)); exit;
        $this->load->view('stock/restock_item/modal', $view_data);
    }

        function restock_item_save()
        {
            $this->check_module_availability("module_stock");
            if (!$this->bom_can_access_restock()) {
                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                exit;
            }

            $id = $this->input->post('id');
            if (empty($id)) {
                if (!$this->check_permission('bom_restock_create')) {
                    echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                    exit;
                }
            } else {
                if (!$this->check_permission('bom_restock_update')) {
                    echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                    exit;
                }
            }

            validate_submitted_data(
                array(
                    "id" => "numeric"
                )
            );

            $data = array(
                "name" => $this->input->post('name'),
                "created_by" => $this->input->post('created_by'),
                "created_date" => $this->input->post('created_date'),
                "po_no" => $this->input->post('po_no')
            );

            if (!$this->login_user->is_admin || empty($data["created_by"])) {
                $data["created_by"] = $this->login_user->id;
            }

            $data = clean_data($data);

            $save_id = $this->Bom_item_groups_model->save($data, $id);

            if ($save_id) {
                $restock_ids = $this->input->post('restock_id[]');
                $item_ids = $this->input->post('item_id[]');
                $stocks = $this->input->post('stock[]');
                $prices = $this->input->post('price[]');
                $serns = $this->input->post('sern[]');
                if (isset($restock_ids) && isset($item_ids) && isset($stocks)) {
                    $this->Bom_item_groups_model->restock_item_save(
                        $save_id,
                        $restock_ids,
                        $item_ids,
                        $stocks,
                        $prices,
                        $serns
                    );
                }

                echo json_encode(
                    array(
                        "success" => true,
                        "data" => $this->_restock_item_row_data($save_id),
                        'id' => $save_id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    )
                );
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
        }

        function restock_item_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock() || !$this->check_permission('bom_restock_delete')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                echo json_encode(array('success' => true, 'message' => $id));

                // if ($this->Bom_item_groups_model->delete_one($id)) {
                //         echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                // } else {
                //         echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                // }
        }

        // Restock Item view
        // START: Restock item View
    function restock_item_view($restock_id = 0, $tab = "")
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        if ($restock_id) {
            $options = array("id" => $restock_id);
            $restock_item_info = $this->Bom_item_groups_model->get_details($options)->row();
            if ($restock_item_info) {

                $view_data['restock_item_info'] = $restock_item_info;

                $created_by = $view_data['restock_item_info']->created_by;
                if (!$this->bom_can_read_restock($created_by))
                    redirect("forbidden");
                
                $view_data['can_read'] = $this->check_permission('bom_restock_read');
                $view_data['can_create'] = $this->check_permission('bom_restock_create');
                $view_data['can_read_self'] = $this->check_permission('bom_restock_read_self');

                $view_data['tab'] = $tab;
                $view_data['view_type'] = "";
                $view_data['hidden_menu'] = array("");

                $this->template->rander("stock/restock_item/view", $view_data);
            } else {
                show_404();
            }
        } else {
            show_404();
        }
    }

    function restock_item_info($restock_id = 0)
    {
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

            $this->load->view('stock/restock_item/inforestock', $view_data);
        }
    }

    function restock_item_details($restock_id = 0)
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['can_update'] = $this->check_permission('bom_restock_update');

        if ($restock_id) {
            $view_data['restock_id'] = $restock_id;
            $view_data['is_admin'] = $this->login_user->is_admin;
            $this->load->view('stock/restock_item/details', $view_data);
        }
    }

    function restock_item_view_list($group_id = 0)
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $options = array(
            "group_id" => $group_id
        );

        $list_data = $this->Bom_item_groups_model->get_restocks($options)->result();
        
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_restock_item_view_make_row($data);
        }

        // var_dump(arr($result)); exit;
        echo json_encode(array("data" => $result));
    }

    private function _restock_item_view_make_row($data)
    {
        $remaining_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $remaining_value = $data->price * $data->remaining / $data->stock;
        }

        $can_delete = $this->dev2_canDeleteRestockItem($data->id);

        $files_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = " fa fa-" . get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $files_link .= js_anchor(
                        " ",
                        array(
                            "data-toggle" => "app-modal",
                            "data-sidebar" => "0",
                            "class" => "pull-left font-22 mr10 $link",
                            "title" => remove_file_prefix($file_name),
                            "data-url" => get_uri("stock/restock_file_preview/" . $data->id . "/" . $key)
                        )
                    );
                }
            }
        }

        $row_data = array(
            $data->id,
            $this->check_permission('bom_material_read_production_name') 
            ? anchor(get_uri('items/detail/' . $data->item_id), $data->item_code . ' - ' . $data->item_name) 
            : anchor(get_uri('items/detail/' . $data->item_id), $data->item_code), 
            $data->serial_number ? $data->serial_number : '-', 
            $files_link ? $files_link : '-', 
            is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-', 
            to_decimal_format3($data->stock), 
            to_decimal_format3($data->remaining), 
            $data->item_unit
        );

        if ($this->check_permission('bom_restock_read_price')) {
            $row_data[] = to_decimal_format3($data->price);
            $row_data[] = to_decimal_format3($remaining_value);
            $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
        }

        $options = '';
        if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_update') && $data->remaining > 0) {
            $options .= modal_anchor(
                get_uri("stock/restock_item_view_modal"), 
                "<i class='fa fa-pencil'></i>", 
                array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id)
            ); // btn-update
            $options .= modal_anchor(
                get_uri("stock/restock_item_withdraw_modal"), 
                "<i class='fa fa-share-square-o'></i>", 
                array("class" => "edit", "title" => lang('stock_restock_item_withdraw'), "data-post-id" => $data->id, "data-post-view" => "restock")
            ); // btn-withdraw
        } else {
            $options .= modal_anchor(
                get_uri("stock/restock_item_view_modal"), 
                "<i class='fa fa-eye'></i>", 
                array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id)
            ); // btn-view
        }

        if ($can_delete && $this->check_permission('bom_restock_delete')) {
            $options .= js_anchor(
                "<i class='fa fa-times fa-fw'></i>", 
                array(
                    "title" => lang('stock_restock_item_delete'), 
                    "class" => "delete", "data-id" => $data->id, 
                    "data-action-url" => get_uri('stock/restock_item_view_delete'), 
                    "data-action" => "delete-confirmation"
                )
            ); // btn-delete
        }

        $row_data[] = $options;
        return $row_data;
    }

        private function _restock_item_view_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_item_groups_model->get_restocks($options)->row();
                //var_dump($data);exit;
                return $this->_restock_item_view_make_row($data);
        }

    function restock_item_view_modal()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');
        $view_data['can_create'] = $this->check_permission('bom_restock_create');
        $view_data['can_update'] = $this->check_permission('bom_restock_update');

        $restock_id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        $view_data['view'] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_stocks_model->get_one($restock_id);

        if ($view_data['model_info']->stock > 0 && $view_data['model_info']->price > 0) {
            $view_data['model_info']->priceunit = $view_data['model_info']->price / $view_data['model_info']->stock;
            $view_data['model_info']->priceunit = to_decimal_format3($view_data['model_info']->priceunit);
        }

        if (!empty($view_data['model_info']->id)) {
            $view_data['model_info']->can_delete = $this->dev2_canDeleteRestockItem($view_data['model_info']->id);
        }
        
        if (empty($view_data['model_info']->id)) {
            if (!$this->check_permission('bom_restock_update'))
                redirect("forbidden");
        } else {
            $group_info = $this->Bom_item_groups_model->get_one($view_data['model_info']->group_id);
            $created_by = $group_info->created_by;
            if (!$this->bom_can_read_restock($created_by))
                redirect("forbidden");
        }

        $group_id = $this->input->post('group_id');
        if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
            $view_data['model_info']->group_id = $group_id;
        }

        $view_data['item_dropdown'] = $this->Items_model->get_details([])->result();
        $this->load->view('stock/restock_item/modal_view', $view_data);
    }

    function restock_item_view_save()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock()) {
            echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
            exit;
        }

        $id = $this->input->post('id');
        if (empty($id)) {
            if (!$this->check_permission('bom_restock_create')) {
                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                exit;
            }
        } else {
            if (!$this->check_permission('bom_restock_update')) {
                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                exit;
            }
        }

        validate_submitted_data(
            array(
                "id" => "numeric",
                "group_id" => "required|numeric",
                "sern" => "required"
            )
        );

        $serial_number = $this->input->post('sern');
        if (!$id) {
            $serials = $this->Bom_item_stocks_model->dev2_getSerialNumByGroupId($this->input->post('group_id'));
            $is_duplicate = in_array($serial_number, $serials);

            if ($is_duplicate) {
                echo json_encode(array('success' => false, 'message' => lang('serial_number_duplicate')));
                exit();
            }
        } else {
            $serials = $this->Bom_item_stocks_model->dev2_getSerialNumByGroupIdWithoutSelf($this->input->post('group_id'), $this->input->post('id'));
            $is_duplicate = in_array($serial_number, $serials);

            if ($is_duplicate) {
                echo json_encode(array('success' => false, 'message' => lang('serial_number_duplicate')));
                exit();
            }
        }

        $data = array(
            "group_id" => $this->input->post('group_id'),
            "item_id" => $this->input->post('item_id'),
            "serial_number" => $serial_number,
            "stock" => $this->input->post('stock'),
            "remaining" => $this->input->post('remaining'),
            "note" => $this->input->post('note'),
            "expiration_date" => $this->input->post('expiration_date')
        );
        if ($this->check_permission('bom_restock_read_price')) {
            $data["price"] = $this->input->post('price');
        }

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "material");
        $new_files = unserialize($files_data);
        if ($id) {
            $model_info = $this->Bom_item_stocks_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");
            $new_files = update_saved_files($timeline_file_path, $model_info->files, $new_files);
        }
        $data["files"] = serialize($new_files);
        $data = clean_data($data);

        $save_id = $this->Bom_item_stocks_model->save($data, $id);
        if ($save_id) {
            $view = $this->input->post('view');
            if (isset($view) && $view == 'item') {
                echo json_encode(
                    array(
                        "success" => true,
                        "data" => $this->_item_remaining_row_data($save_id),
                        'id' => $save_id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    )
                );
            } else {
                echo json_encode(
                    array(
                        "success" => true,
                        "data" => $this->_restock_item_view_row_data($save_id),
                        'id' => $save_id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    )
                );
            }
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

        function restock_item_view_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                // if (!$this->can_edit_clients()) {
                //   redirect("forbidden");
                // }
                // $this->access_only_allowed_members();

                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                $id = $this->input->post('id');
                if ($this->Bom_item_stocks_model->delete_one($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

    function restock_item_withdraw_modal()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $restock_id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";

        $view_data["view"] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_stocks_model->get_one($restock_id);

        $group_id = $this->input->post('group_id');
        if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
            $view_data['model_info']->group_id = $group_id;
        }
        $view_data['item_dropdown'] = $this->Items_model->get_details([])->result();

        $this->load->view('stock/restock_item/modal_withdraw', $view_data);
    }

    function restock_item_withdraw_save()
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock()) {
            echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
            exit;
        }

        $id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "required|numeric",
                "item_id" => "required|numeric",
                "ratio" => "required|numeric"
            )
        );

        $ratio = $this->input->post('ratio');
        $data = array(
            "item_id" => $this->input->post('item_id'),
            "stock_id" => $id,
            "ratio" => $ratio,
            "note" => $this->input->post('note') ? $this->input->post('note') : ''
        );
        $data = clean_data($data);

        $save_id = $this->Bom_project_item_items_model->save($data, null);
        if ($save_id) {
            $this->Bom_item_stocks_model->reduce_material($id, $ratio);
            $view = $this->input->post('view');
            if (isset($view) && $view == 'item') {
                echo json_encode(
                    array(
                        "success" => true,
                        "data" => $this->_item_remaining_row_data($id),
                        'id' => $id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    )
                );
            } else {
                echo json_encode(
                    array(
                        "success" => true,
                        "data" => $this->_restock_item_view_row_data($id),
                        'id' => $id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    )
                );
            }
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

        function restock_item_file_preview($id = "", $key = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_restock())
                        redirect("forbidden");

                if ($id) {
                        $model_info = $this->Bom_item_stocks_model->get_one($id);
                        $files = unserialize($model_info->files);
                        $file = get_array_value($files, $key);

                        $file_name = get_array_value($file, "file_name");
                        $file_id = get_array_value($file, "file_id");
                        $service_type = get_array_value($file, "service_type");

                        $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
                        $view_data["is_image_file"] = is_image_file($file_name);
                        $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
                        $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
                        $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

                        $this->load->view("stock/restock/view_file", $view_data);
                } else {
                        show_404();
                }
        }

    // START: Restock Used History
    function restock_item_used($restock_id = 0)
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

        if ($restock_id) {
            $view_data['restock_id'] = $restock_id;
            $view_data['is_admin'] = $this->login_user->is_admin;
            $this->load->view('stock/restock_item/used_list', $view_data);
        }
    }

    function restock_item_used_list($restock_id = 0)
    {
        $this->check_module_availability("module_stock");
        if (!$this->bom_can_access_restock())
            redirect("forbidden");

        $options = array(
            "restock_id" => $restock_id
        );
        if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
            $options['created_by'] = $this->login_user->id;
        }
        $list_data = $this->Bom_project_item_items_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_restock_item_used_make_row($data);
        }
        
        echo json_encode(array("data" => $result));
    }

    private function _restock_item_used_make_row($data)
    {
        $used_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $used_value = $data->price * $data->ratio / $data->stock;
        }

        $row_data = array(
            $data->id,
            anchor(get_uri('items/detail/' . $data->item_id), $data->item_name),
            !empty($data->project_title) ? anchor(get_uri('projects/view/' . $data->project_id), $data->project_title) : '-',
            is_date_exists($data->created_at) ? format_to_date($data->created_at, false) : '-',
            !empty($data->created_by) ? $this->Account_category_model->created_by($data->created_by) : '-',
            !empty($data->note) ? $data->note : '-',
            to_decimal_format3($data->ratio),
            $data->item_unit
        );

        if ($this->check_permission('bom_restock_read_price')) {
            $row_data[] = to_decimal_format3($used_value);
            $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
        }

        return $row_data;
    }

        private function _restock_item_used_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_project_item_items_model->get_details($options)->row();
                return $this->_restock_item_used_make_row($data);
        }
        // END: Restock Used History

        // START: Items
        function items()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_material_create');
                $view_data['can_update'] = $this->check_permission('bom_material_update');
                $view_data['can_delete'] = $this->check_permission('bom_material_delete');
                $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                $view_data["category_dropdown"] = $this->Bom_item_model->get_category_dropdown();

                $view_data['is_admin'] = $this->login_user->is_admin;
                $this->template->rander("stock/item/index", $view_data);
        }

        function item_list()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "category_id" => $this->input->post("category_id")
                );
                $list_data = $this->Bom_item_model->get_details($options)->result();
                // var_dump(arr($list_data)); exit;
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_item_make_row($data);
                }
                // var_dump(arr($result)); exit;
                echo json_encode(array("data" => $result));
        }

        public function item_barcode($barcode)
        {
                $databarcode = ['text' => $barcode, 'drawText' => true,];
                $rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
                Barcode::render('code128', 'image', $databarcode, $rendererOptions);
        }

        private function _item_make_row($data)
        {
                $src = @$data->barcode;
                if ($src) {
                        /* $barcodeOptions = [
                        'text' => $check['text'],
                        'barHeight' => 50,
                        'drawText' => true,
                        'withChecksum' => $check['checksum'],
                        'withChecksumInText' => $check['checksum']
                        ]; */
                        //$databarcode = ['text' => $data->barcode,'drawText' => true,];
                        //$rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
                        /* ob_start();
                        Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );
                        $src = ob_get_contents();
                        ob_end_clean(); */
                        $src = base_url('/stock/item_barcode/' . $src);
                        //$src = Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );
                }
                //Barcode::render( 'code128', 'image', $databarcode, $rendererOptions );exit;
                //var_dump($src);exit;
                //echo $src;
                //exit;

                $preview = '<img class="product-preview" src="' . base_url('assets/images/file_preview.jpg') . '" />';
                if ($data->files) {
                        $images = @unserialize($data->files);
                        if (is_array($images) && sizeof($images)) {
                                $preview = '<img class="product-preview" src="' . base_url('files/timeline_files/' . $images[sizeof($images) - 1]['file_name']) . '" />';
                        }
                }

                if ($this->check_permission('bom_material_read_production_name')) {
                        $row_data = array(
                                $data->id,
                                $preview,
                                $data->item_code ? $data->item_code : '-',
                                anchor(get_uri('stock/item_view/' . $data->id), $data->title),
                                $data->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                                $data->rate ? to_decimal_format3($data->rate) : '-',
                                $data->category ? $data->category : '-',
                                $data->description ? $data->description : '-',
                                $data->remaining ? to_decimal_format($data->remaining) : 0,
                                $data->unit_type ? $data->unit_type : '-'
                        );
                } else {
                        $row_data = array(
                                $data->id,
                                $preview,
                                $data->item_code ? $data->item_code : '-',
                                // anchor(get_uri('stock/item_view/' . $data->id), $data->title),
                                $data->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                                $data->rate ? to_decimal_format3($data->rate) : '-',
                                $data->category ? $data->category : '-',
                                $data->description ? $data->description : '-',
                                $data->remaining ? to_decimal_format($data->remaining) : 0,
                                $data->unit_type ? $data->unit_type : '-'
                        );
                }

                $options = '';
                if ($this->check_permission('bom_material_update')) {
                        $options .= modal_anchor(get_uri("stock/item_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_item_edit'), "data-post-id" => $data->id));
                } else {
                        $options .= modal_anchor(get_uri("stock/item_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_item_edit'), "data-post-id" => $data->id));
                }
                if ($this->check_permission('bom_material_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_item_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/item_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _item_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_item_model->get_details($options)->row();
                return $this->_item_make_row($data);
        }

        function item_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_create'] = $this->check_permission('bom_material_create');
                $view_data['can_update'] = $this->check_permission('bom_material_update');
                $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                $item_id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric"
                        )
                );

                $view_data['label_column'] = "col-md-3";
                $view_data['field_column'] = "col-md-9";

                $view_data["view"] = $this->input->post('view');
                $view_data['model_info'] = $this->Bom_item_model->get_one($item_id);
                $view_data["category_dropdown"] = $this->Bom_item_model->get_category_dropdown();
                $view_data["account_category"] = $this->Account_category_model->get_list_dropdown();

                if (empty($view_data['model_info']->id)) {
                        if (!$this->check_permission('bom_material_create'))
                                redirect("forbidden");
                }

                $this->load->view('stock/item/modal', $view_data);
        }

        function item_save()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $item_id = $this->input->post('id');
                if (empty($item_id)) {
                        if (!$this->check_permission('bom_material_create')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                } else {
                        if (!$this->check_permission('bom_material_update')) {
                                echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                                exit;
                        }
                }

                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "name" => "required",
                                "unit" => "required"
                        )
                );

                $data = array(
                        "title" => $this->input->post('name'),
                        "rate" => $this->input->post('item_rate') ? $this->input->post('item_rate') : '0',
                        "category_id" => $this->input->post('category_id') ? $this->input->post('category_id') : 0,
                        "account_id" => $this->input->post('account_id') ? $this->input->post('account_id') : 0,
                        "description" => $this->input->post('description') ? $this->input->post('description') : null,
                        "unit_type" => $this->input->post('unit'),
                        "barcode" => $this->input->post('barcode'),
                        "noti_threshold" => $this->input->post('noti_threshold'),
                        "item_code" => $this->input->post('item_code')
                );

                // if ($this->check_permission('bom_material_read_production_name')) {
                //         $data["rate"] = $this->input->post('item_rate') ? $this->input->post('item_rate') : '0';
                // }

                $data = clean_data($data);
                $save_id = $this->Bom_item_model->save($data, $item_id);
                if ($save_id) {
                        echo json_encode(
                                array(
                                        'success' => true,
                                        'data' => $this->_item_row_data($save_id),
                                        'id' => $save_id,
                                        'view' => $this->input->post('view'),
                                        'message' => lang('record_saved')

                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function item_delete()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_delete')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_item_model->delete_material_and_sub_items($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function item_view($item_id = 0, $tab = "")
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                if ($item_id) {
                        $options = array("id" => $item_id);
                        $item_info = $this->Bom_item_model->get_details($options)->row();
                        if ($item_info) {

                                $view_data['item_info'] = $item_info;

                                $view_data["tab"] = $tab;
                                $view_data["view_type"] = "";

                                $view_data["hidden_menu"] = array("item-mixing");
                                if (!$this->bom_can_access_supplier()) {
                                        $view_data["hidden_menu"][] = "material-pricings";
                                }
                                if (!$this->bom_can_access_restock()) {
                                        $view_data["hidden_menu"][] = "material-remaining";
                                        $view_data["hidden_menu"][] = "material-used";
                                }

                                $this->template->rander("stock/item/view", $view_data);
                        } else {
                                show_404();
                        }
                } else {
                        show_404();
                }
        }

        function item_info($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                if ($item_id) {
                        $view_data['can_create'] = $this->check_permission('bom_material_create');
                        $view_data['can_update'] = $this->check_permission('bom_material_update');
                        $view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

                        $view_data['model_info'] = $this->Bom_item_model->get_one($item_id);
                        $view_data["category_dropdown"] = json_encode($this->Bom_item_model->get_category_dropdown());

                        $view_data['label_column'] = "col-md-2";
                        $view_data['field_column'] = "col-md-10";

                        $this->load->view('stock/item/iteminfo', $view_data);
                }
        }
        
        // START: Item Import
        // START: Material Import
        function item_import_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }
                $this->load->view('stock/item/modal_import');
        }

        function item_sample_excel_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                // download_app_files(
                //         get_setting("system_file_path"),
                //         serialize(array(array("file_name" => "import-item-sample.xlsx")))
                // );

                $file_name = "import-item-sample-new.xlsx";
                download_app_files("assets/", serialize(array(array("file_name" => $file_name))));
        }

        function item_upload_excel_file()
        {
                upload_file_to_temp(true);
        }

        function item_validate_import_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $file_name = $this->input->post("file_name");
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!is_valid_file_to_upload($file_name)) {
                        echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
                        exit();
                }

                if ($file_ext == "xlsx") {
                        echo json_encode(array("success" => true));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('please_upload_a_excel_file') . " (.xlsx)"));
                }
        }

        function item_validate_import_file_data($check_on_submit = false)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $table_data = "";
                $error_message = "";
                $headers = array();
                $got_error_header = false; //we've to check the valid headers first, and a single header at a time
                $got_error_table_data = false;

                $file_name = $this->input->post("file_name");

                require_once(APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php");

                $temp_file_path = get_setting("temp_file_path");
                $excel_file = new SpreadsheetReader($temp_file_path . $file_name);

                $table_data .= '<table class="table table-responsive table-bordered table-hover" style="width: 100%; color: #444;">';

                $table_data_header_array = array();
                $table_data_body_array = array();

                foreach ($excel_file as $row_key => $value) {
                        if ($row_key == 0) { //validate headers
                                $headers = $this->_item_store_headers_position($value);

                                foreach ($headers as $row_data) {
                                        $has_error_class = false;
                                        if (get_array_value($row_data, "has_error") && !$got_error_header) {
                                                $has_error_class = true;
                                                $got_error_header = true;

                                                if (get_array_value($row_data, "custom_field")) {
                                                        $error_message = lang("no_such_custom_field_found");
                                                } else {
                                                        $error_message = sprintf(lang("import_client_error_header"), lang(get_array_value($row_data, "key_value")));
                                                }
                                        }

                                        array_push($table_data_header_array, array("has_error_class" => $has_error_class, "value" => get_array_value($row_data, "value")));
                                }
                        } else { //validate data
                                $error_message_on_this_row = "<ol class='pl15'>";
                                $has_contact_first_name = get_array_value($value, 1) ? true : false;

                                foreach ($value as $key => $row_data) {
                                        $has_error_class = false;

                                        if (!$got_error_header) {
                                                $row_data_validation = $this->_item_row_data_validation_and_get_error_message($key, $row_data, $has_contact_first_name);
                                                if ($row_data_validation) {
                                                        $has_error_class = true;
                                                        $error_message_on_this_row .= "<li>" . $row_data_validation . "</li>";
                                                        $got_error_table_data = true;
                                                }
                                        }

                                        $table_data_body_array[$row_key][] = array("has_error_class" => $has_error_class, "value" => $row_data);
                                }

                                $error_message_on_this_row .= "</ol>";

                                //error messages for this row
                                if ($got_error_table_data) {
                                        $table_data_body_array[$row_key][] = array("has_error_text" => true, "value" => $error_message_on_this_row);
                                }
                        }
                }

                //return false if any error found on submitting file
                if ($check_on_submit) {
                        return ($got_error_header || $got_error_table_data) ? false : true;
                }

                //add error header if there is any error in table body
                if ($got_error_table_data) {
                        array_push($table_data_header_array, array("has_error_text" => true, "value" => lang("error")));
                }

                //add headers to table
                $table_data .= "<tr>";
                foreach ($table_data_header_array as $table_data_header) {
                        $error_class = get_array_value($table_data_header, "has_error_class") ? "error" : "";
                        $error_text = get_array_value($table_data_header, "has_error_text") ? "text-danger" : "";
                        $value = get_array_value($table_data_header, "value");
                        $table_data .= "<th class='$error_class $error_text'>" . $value . "</th>";
                }
                $table_data .= "<tr>";

                //add body data to table
                foreach ($table_data_body_array as $table_data_body_row) {
                        $table_data .= "<tr>";
                        foreach ($table_data_body_row as $table_data_body_row_data) {
                                $error_class = get_array_value($table_data_body_row_data, "has_error_class") ? "error" : "";
                                $error_text = get_array_value($table_data_body_row_data, "has_error_text") ? "text-danger" : "";
                                $value = get_array_value($table_data_body_row_data, "value");
                                $table_data .= "<td class='$error_class $error_text'>" . $value . "</td>";
                        }
                        $table_data .= "<tr>";
                }

                //add error message for header
                if ($error_message) {
                        $total_columns = count($table_data_header_array);
                        $table_data .= "<tr><td class='text-danger' colspan='$total_columns'><i class='fa fa-warning'></i> " . $error_message . "</td></tr>";
                }
                $table_data .= "</table>";

                echo json_encode(array("success" => true, 'table_data' => $table_data, 'got_error' => ($got_error_header || $got_error_table_data) ? true : false));
        }

        private function _item_get_allowed_headers()
        {
                return array(
                        "item_code",
                        "title",
                        "description",
                        "rate",
                        "unit_name"
                );
        }

        private function _item_store_headers_position($headers_row = array())
        {
                $allowed_headers = $this->_item_get_allowed_headers();

                //check if all headers are correct and on the right position
                $final_headers = array();
                foreach ($headers_row as $key => $header) {
                        $key_value = str_replace(' ', '_', strtolower($header));
                        $header_on_this_position = get_array_value($allowed_headers, $key);
                        $header_array = array("key_value" => $header_on_this_position, "value" => $header);

                        if ($header_on_this_position == $key_value) {
                                //allowed headers
                                //the required headers should be on the correct positions
                                //the rest headers will be treated as custom fields
                                //pushed header at last of this loop
                        } else if ((count($allowed_headers) - 1) < $key) {
                                //custom fields headers
                                //check if there is any existing custom field with this title
                                if (!$this->_get_existing_custom_field_id($header)) {
                                        $header_array["has_error"] = true;
                                        $header_array["custom_field"] = true;
                                }
                        } else { //invalid header, flag as red
                                $header_array["has_error"] = true;
                        }

                        array_push($final_headers, $header_array);
                }
                return $final_headers;
        }

        private function _item_row_data_validation_and_get_error_message($key, $data, $has_contact_first_name)
        {
                $allowed_headers = $this->_item_get_allowed_headers();
                $header_value = get_array_value($allowed_headers, $key);

                //company name field is required
                if ($header_value == "company_name" && !$data) {
                        return lang("import_client_error_company_name_field_required");
                }

                //if there is contact first name then the contact last name and email is required
                //the email should be unique then
                if ($has_contact_first_name) {
                        if ($header_value == "contact_last_name" && !$data) {
                                return lang("import_client_error_contact_name");
                        }

                        if ($header_value == "contact_email") {
                                if ($data) {
                                        if ($this->Users_model->is_email_exists($data)) {
                                                return lang("duplicate_email");
                                        }
                                } else {
                                        return lang("import_client_error_contact_email");
                                }
                        }
                }
        }

        function item_save_client_from_excel_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material()) {
                        redirect("forbidden");
                }

                $file_name = $this->input->post('file_name');
                require_once(APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php");

                $temp_file_path = get_setting("temp_file_path");
                $excel_file = new SpreadsheetReader($temp_file_path . $file_name);
                $allowed_headers = $this->_item_get_allowed_headers();

                foreach ($excel_file as $key => $value) { // rows
                        if ($key === 0) { // first line is headers, modify this for custom fields and continue for the next loop
                                continue;
                        }

                        $item = [
                                'item_code' => $value[0],
                                'title' => $value[1],
                                'description' => $value[2],
                                'rate' => $value[3],
                                'unit_type' => $value[4]
                        ];

                        // Save material data
                        if (!$this->Bom_item_model->duplicated_name($item['title']) && !$this->Bom_item_model->duplicated_code($item['item_code'])) {
                                $item_id = $this->Bom_item_model->save($item);
                                if (!$item_id) {
                                        continue;
                                }
                        }
                }

                delete_file_from_directory($temp_file_path . $file_name); // delete temp file
                echo json_encode(array('success' => true, 'message' => lang("record_saved")));
        }
        // END: Material Import

        // START:item Remaining
        function item_remainings($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                if ($item_id) {
                        $view_data['item_id'] = $item_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/item/remaining', $view_data);
                }
        }

        function item_remaining_list($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "item_id" => $item_id
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_item_groups_model->get_restocks($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_item_remaining_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _item_remaining_make_row($data)
        {
                $remaining_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $remaining_value = $data->price * $data->remaining / $data->stock;
                }

                $user_name = $data->user_first_name . ' ' . $data->user_last_name;
                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $user_name";

                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/restock_item_view/' . $data->group_id), $data->group_name),
                        anchor(get_uri('team_members/view/' . $data->user_id), $user_name),
                        format_to_date($data->created_date),
                        is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
                        to_decimal_format2($data->stock) . ' ' . $data->item_unit,
                        to_decimal_format2($data->remaining) . ' ' . $data->item_unit
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_currency($data->price);
                        $row_data[] = to_currency($remaining_value);
                }

                $options = '';
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) {
                        $options .= modal_anchor(get_uri("stock/restock_item_view_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id, "data-post-view" => "item"))
                                . modal_anchor(get_uri("stock/restock_item_withdraw_modal"), "<i class='fa fa-share-square-o'></i>", array("class" => "edit", "title" => lang('stock_restock_item_withdraw'), "data-post-id" => $data->id, "data-post-view" => "item"));
                } else {
                        $options .= modal_anchor(get_uri("stock/restock_item_view_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id, "data-post-view" => "item"));
                }
                if ($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_item_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_item_view_delete"), "data-action" => "delete-confirmation"));
                }
                $row_data[] = $options;

                return $row_data;
        }

        private function _item_remaining_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_item_groups_model->get_restocks($options)->row();
                return $this->_item_remaining_make_row($data);
        }
        // END: Material Remaining

        // START: Material Used History
        function item_used($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                if ($item_id) {
                        $view_data['item_id'] = $item_id;
                        $view_data['is_admin'] = $this->login_user->is_admin;
                        $this->load->view('stock/item/used_list', $view_data);
                }
        }
        function item_used_list($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array(
                        "item_id" => $item_id
                );
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }
                $list_data = $this->Bom_project_item_items_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_item_used_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _item_used_make_row($data)
        {
                $used_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $used_value = $data->price * $data->ratio / $data->stock;
                }

                $row_data = array(
                        $data->id,
                        !empty($data->stock_name) ? anchor(get_uri('stock/restock_item_view/' . $data->group_id), $data->stock_name) : '-',
                        !empty($data->project_title) ? anchor(get_uri('projects/view/' . $data->project_id), $data->project_title) : '-',
                        is_date_exists($data->created_at) ? format_to_date($data->created_at, false) : '-',
                        !empty($data->note) ? $data->note : '-',
                        to_decimal_format2($data->ratio) . ' ' . $data->item_unit,
                );
                if ($this->check_permission('bom_restock_read_price')) {
                        $row_data[] = to_currency($used_value);
                }

                return $row_data;
        }

        private function _item_used_row_data($id)
        {
                $options = array(
                        'id' => $id
                );
                $data = $this->Bom_project_item_items_model->get_details($options)->row();
                return $this->_item_used_make_row($data);
        }
        // END: Material Used History

        // START: Material Files
        function item_files($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $view_data['can_update'] = $this->check_permission('bom_material_update');

                $view_data['item_id'] = $item_id;
                $this->load->view("stock/item/files", $view_data);
        }

        function item_file_list($item_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $options = array("item_id" => $item_id);
                $list_data = $this->Bom_item_files_model->get_details($options)->result();
                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_item_file_make_row($data);
                }
                echo json_encode(array("data" => $result));
        }

        private function _item_file_make_row($data)
        {
                $file_icon = get_file_icon(strtolower(pathinfo($data->file_name, PATHINFO_EXTENSION)));

                $user_image = get_avatar($data->user_image);
                $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $data->user_name";
                if ($data->user_type == "staff") {
                        $uploaded_by = get_team_member_profile_link($data->uploaded_by, $user_name);
                } else {
                        $uploaded_by = get_client_contact_profile_link($data->uploaded_by, $user_name);
                }

                $description = "<div class='pull-left'>"
                        . js_anchor(remove_file_prefix($data->file_name), array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("stock/item_view_file/" . $data->id)));
                if ($data->description) {
                        $description .= "<br /><span>" . $data->description . "</span></div>";
                } else {
                        $description .= "</div>";
                }

                $options = anchor(get_uri("stock/item_download_file/" . $data->id), "<i class='fa fa fa-cloud-download'></i>", array("title" => lang("download")));
                if ($this->check_permission('bom_material_update')) {
                        $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_file'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/item_delete_file"), "data-action" => "delete-confirmation"));
                }

                return array(
                        $data->id,
                        "<div class='fa fa-$file_icon font-22 mr10 pull-left'></div>" . $description,
                        convert_file_size($data->file_size),
                        $uploaded_by,
                        format_to_datetime($data->created_at),
                        $options
                );
        }

        function item_view_file($file_id = 0)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $file_info = $this->Bom_item_files_model->get_details(array("id" => $file_id))->row();
                if ($file_info) {
                        if (!$file_info->item_id)
                                redirect("forbidden");

                        $view_data['can_comment_on_files'] = false;
                        $file_url = get_source_url_of_file(make_array_of_file($file_info), get_general_file_path("client", $file_info->item_id));

                        $view_data["file_url"] = $file_url;
                        $view_data["is_image_file"] = is_image_file($file_info->file_name);
                        $view_data["is_google_preview_available"] = is_google_preview_available($file_info->file_name);
                        $view_data["is_viewable_video_file"] = is_viewable_video_file($file_info->file_name);
                        $view_data["is_google_drive_file"] = ($file_info->file_id && $file_info->service_type == "google") ? true : false;

                        $view_data["file_info"] = $file_info;
                        $view_data['file_id'] = $file_id;
                        $this->load->view("stock/item/view_file", $view_data);
                } else {
                        show_404();
                }
        }

        function item_download_file($id)
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material())
                        redirect("forbidden");

                $file_info = $this->Bom_item_files_model->get_one($id);
                if (!$file_info->item_id)
                        redirect("forbidden");

                $file_data = serialize(array(make_array_of_file($file_info)));

                download_app_files(get_general_file_path("client", $file_info->item_id), $file_data);
        }

        function item_delete_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                $info = $this->Bom_item_files_model->get_one($id);

                if ($this->Bom_item_files_model->delete_one($id)) {
                        delete_app_files(get_general_file_path("client", $info->item_id), array(make_array_of_file($info)));
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        function item_file_modal()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        redirect("forbidden");
                }

                $view_data['model_info'] = $this->Bom_item_files_model->get_one($this->input->post('id'));
                $item_id = $this->input->post('material_id')
                        ? $this->input->post('item_id')
                        : $view_data['model_info']->item_id;

                $view_data['item_id'] = $item_id;
                $this->load->view('stock/material/modal_file', $view_data);
        }

        function item_save_file()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->check_permission('bom_material_update')) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $item_id = $this->input->post('item_id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "item_id" => "required|numeric"
                        )
                );

                $files = $this->input->post("files");
                $success = false;
                $now = get_current_utc_time();

                $target_path = getcwd() . "/" . get_general_file_path("client", $item_id);

                if ($files && get_array_value($files, 0)) {
                        foreach ($files as $file) {
                                $file_name = $this->input->post('file_name_' . $file);
                                $file_info = move_temp_file($file_name, $target_path);
                                if ($file_info) {
                                        $data = array(
                                                "item_id" => $item_id,
                                                "file_name" => get_array_value($file_info, 'file_name'),
                                                "file_id" => get_array_value($file_info, 'file_id'),
                                                "service_type" => get_array_value($file_info, 'service_type'),
                                                "description" => $this->input->post('description_' . $file),
                                                "file_size" => $this->input->post('file_size_' . $file),
                                                "created_at" => $now,
                                                "uploaded_by" => $this->login_user->id
                                        );
                                        $success = $this->Bom_item_files_model->save($data);
                                } else {
                                        $success = false;
                                }
                        }
                }

                if ($success) {
                        echo json_encode(array("success" => true, 'message' => lang('record_saved')));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }
        // END: Material Files

        // START: Material Category
        function item_category_modal()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        redirect("forbidden");
                }

                $type = $this->input->post("type");
                if ($type) {
                        $model_info = new stdClass();
                        $model_info->color = "";

                        $view_data["type"] = $type;
                        $view_data["model_info"] = $model_info;

                        $view_data["existing_categories"] = $this->Bom_item_model->get_categories()->result();

                        $this->load->view("stock/item/modal_category", $view_data);
                }
        }

        function item_category_save()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "numeric",
                                "title" => "required"
                        )
                );

                $data = array(
                        "id" => $id,
                        "title" => $this->input->post('title')
                );
                if (!$id) {
                        $save_id = $this->Bom_item_model->category_create($data);
                } else {
                        $save_id = $this->Bom_item_model->category_update($data);
                }

                if ($save_id) {
                        echo json_encode(
                                array(
                                        "success" => true,
                                        "data" => $this->Bom_item_model->get_categories(['id' => $save_id])->row(),
                                        'id' => $save_id,
                                        'message' => lang('record_saved')
                                )
                        );
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
                }
        }

        function item_category_delete()
        {
                $this->check_module_availability("module_stock");
                if (
                        !$this->bom_can_access_material() || !$this->check_permission('bom_material_create')
                        || !$this->check_permission('bom_material_update')
                ) {
                        echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
                        exit;
                }

                $id = $this->input->post('id');
                validate_submitted_data(
                        array(
                                "id" => "required|numeric"
                        )
                );

                if ($this->Bom_item_model->category_delete($id)) {
                        echo json_encode(array("success" => true, 'message' => lang('record_deleted'), 'id' => $id));
                } else {
                        echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
                }
        }

        // END: Material Category
        function get_item_suggestion()
        {
                $key = $this->input->get("q");
                $suggestion = array();

                $items = $this->Bom_item_model->get_item_suggestion($key);

                foreach ($items as $item) {
                        $suggestion[] = array("id" => $item->id, "text" => $item->text);
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"), 'currency' => 'THB', 'currency_symbol' => '฿');

                echo json_encode($suggestion);
        }

        function get_item_info_suggestion()
        {
                $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
                if ($item) {
                        echo json_encode(array("success" => true, "item_info" => $item));
                } else {
                        echo json_encode(array("success" => false));
                }
        }

        // START: Material Reports
        function item_report()
        {
                $this->check_module_availability("module_stock");
                if (!$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $view_data['can_update'] = $this->check_permission('bom_material_update')
                        && $this->check_permission('bom_restock_update');
                $view_data['can_delete'] = $this->check_permission('bom_material_delete')
                        && $this->check_permission('bom_restock_delete');
                $view_data['can_read_price'] = $this->check_permission('bom_restock_read_price');

                $view_data['is_admin'] = $this->login_user->is_admin;
                $view_data['add_pr_row'] = $this->cp('purchaserequests', 'add_row');
                $this->template->rander("stock/item/report", $view_data);
        }

        function item_report_list()
        {
                $is_zero = $this->input->post("is_zero");
                //$this->check_module_availability("module_stock");
                if (!$this->cop('view_row') || !$this->bom_can_access_material() || !$this->bom_can_access_restock()) {
                        redirect("forbidden");
                }

                $options = array();
                if ($this->check_permission('bom_restock_read_self') && !$this->check_permission('bom_restock_read')) {
                        $options['created_by'] = $this->login_user->id;
                }

                if (isset($is_zero) && !empty($is_zero)) {
                        $options["is_zero"] = $is_zero;
                }

                $list_data = $this->Bom_item_groups_model->get_restocks2($options)->result();
                // var_dump(arr($list_data)); exit;

                $result = array();
                foreach ($list_data as $data) {
                        $result[] = $this->_item_report_make_row($data);
                }

                echo json_encode(array("data" => $result));
        }

        private function _item_report_make_row($data)
        {
                $remaining_value = 0;
                if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
                        $remaining_value = $data->price * $data->remaining / $data->stock;
                }

                $lack = $data->noti_threshold - $data->remaining;
                $is_lack = $lack > 0 ? true : false;
                $row_data = array(
                        $data->id,
                        anchor(get_uri('stock/restock_item_view/' . $data->group_id), $data->group_name),
                        anchor(get_uri('stock/item_view/' . $data->item_id), $data->item_name),
                        $data->item_desc,
                        format_to_date($data->created_date),
                        is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
                        to_decimal_format2($data->stock),
                        '<span class="' . ($is_lack ? 'lacked_material' : '') . '" data-item-id="' . $data->item_id . '" data-lacked-amount="' . ($is_lack ? $lack : 0) . '" data-unit="' . $data->item_unit . '" data-supplier-id="' . $data->supplier_id . '" data-supplier-name="' . $data->supplier_name . '" data-price="' . $data->price . '" data-currency="' . $data->currency . '" data-currency-symbol="' . $data->currency_symbol . '">' . to_decimal_format2($data->remaining) . '</span>',
                        strtoupper($data->item_unit)
                );

                if ($this->check_permission('bom_restock_read_price')) {
                        $price_per_stock = 0;
                        if ($data->stock != 0) {
                                $price_per_stock = $data->price / $data->stock;
                        }

                        $row_data[] = to_decimal_format3($data->price, 2);
                        $row_data[] = to_decimal_format3($price_per_stock);
                        $row_data[] = to_decimal_format3($remaining_value, 2);
                        $row_data[] = !empty($data->currency) && isset($data->currency) ? lang($data->currency) : lang("THB");
                }

                // $options = '';
                // if($this->bom_can_access_restock() && $this->check_permission('bom_restock_update')) {
                //   $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"))
                //     . modal_anchor(get_uri("stock/restock_withdraw_modal"), "<i class='fa fa-share-square-o'></i>", array("class" => "edit", "title" => lang('stock_restock_withdraw'), "data-post-id" => $data->id, "data-post-view" => "material"));
                // } else {
                //   $options .= modal_anchor(get_uri("stock/restock_view_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $data->id, "data-post-view" => "material"));
                // }
                // if($this->bom_can_access_restock() && $this->check_permission('bom_restock_delete')) {
                //   $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/restock_view_delete"), "data-action" => "delete-confirmation"));
                // }
                // $row_data[] = $options;

                return $row_data;
        }

        public function dev2_restockingList()
        {
            $post = $this->input->post('created_by') ? $this->input->post('created_by') : '';
            $result = $this->Bom_stocks_model->dev2_getRestockingList($post);
            
            $data = array();
            foreach ($result as $item) {
                $data[] = $this->dev2_rowDataList($item);
            }

            echo json_encode(array("data" => $data));
        }

        public function dev2_restockingItemList()
        {
            $can_read_self = $this->check_permission('bom_restock_read_self');
            $can_read = $this->check_permission('bom_restock_read');

            if ($can_read) {
                $post = $this->input->post('created_by') ? $this->input->post('created_by') : '';
                $result = $this->Bom_item_groups_model->dev2_getRestockingItemList($post);
            } else {
                if ($can_read_self) {
                    $post = $this->login_user->id;
                    $result = $this->Bom_item_groups_model->dev2_getRestockingItemList($post);
                }
            }

            $data = array();
            foreach ($result as $item) {
                $data[] = $this->dev2_rowDataItemList($item);
            }
            
            echo json_encode(array("data" => $data));
        }

        private function dev2_rowDataList($item)
        {
            $button = "";

            $stock_diff = $item->stock_qty == $item->stock_remain ? true : false;

            if ($this->dev2_canUpdateRestock()) {
                $button .= modal_anchor(get_uri('stock/restock_modal'), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $item->group_id));
            } else {
                $button .= modal_anchor(get_uri("stock/restock_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_edit'), "data-post-id" => $item->group_id));
            }

            if ($this->dev2_canDeleteRestock() && $stock_diff) {
                $button .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $item->stock_id, "data-action-url" => get_uri("stock/restock_item_delete"), "data-action" => "delete-confirmation"));
            }

            return array(
                $item->stock_id,
                anchor(get_uri('stock/restock_view/' . $item->group_id), $item->stock_name),
                $item->serial_number ? $item->serial_number : '-',
                $this->dev2_canReadMaterialName() 
                ? anchor(get_uri('stock/material_view/' . $item->material_id), strtoupper($item->material_code) . ' - ' . ucwords(strtolower($item->material_name))) 
                : anchor(get_uri('stock/material_view/' . $item->material_id), strtoupper($item->material_code)),
                to_decimal_format3($item->stock_qty),
                to_decimal_format3($item->stock_remain),
                strtoupper($item->material_unit),
                $item->create_by ? anchor(get_uri('team_members/view/' . $item->create_by), $this->Account_category_model->created_by($item->create_by)) : '',
                format_to_date($item->create_date),
                $button
            );
        }

        private function dev2_rowDataItemList($item)
        {
            $button = "";

            $stock_diff = $this->dev2_canDeleteRestockItem($item->id);

            if ($this->dev2_canUpdateRestock()) {
                $button .= modal_anchor(get_uri('stock/restock_item_modal'), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $item->group_id));
            } else {
                $button .= modal_anchor(get_uri("stock/restock_item_modal"), "<i class='fa fa-eye'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $item->group_id));
            }

            if ($this->dev2_canDeleteRestock() && $stock_diff) {
                $button .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $item->id, "data-action-url" => get_uri("stock/dev2_restock_item_delete"), "data-action" => "delete-confirmation"));
            }

            return array(
                $item->id,
                anchor(get_uri('stock/restock_item_view/' . $item->group_id), $item->group_name),
                $item->sern ? $item->sern : '-',
                $this->dev2_canReadMaterialName() 
                ? anchor(get_uri('items/detail/' . $item->item_id), strtoupper($item->item_code) . ' - ' . ucwords(strtolower($item->item_name))) 
                : anchor(get_uri('items/detail/' . $item->item_id), strtoupper($item->item_code)),
                to_decimal_format3($item->stock_qty),
                to_decimal_format3($item->remain_qty),
                strtoupper($item->item_unit),
                $item->create_by ? anchor(get_uri('team_members/view/' . $item->create_by), $this->Account_category_model->created_by($item->create_by)) : '',
                format_to_date($item->create_date),
                $button
            );
        }

        private function dev2_canReadMaterialName()
        {
            if ($this->login_user->is_admin) {
                return true;
            } else {
                if ($this->login_user->permissions['bom_material_read_production_name']) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        private function dev2_canUpdateRestock()
        {
            if ($this->login_user->is_admin) {
                return true;
            } else {
                if ($this->Permission_m->permissions->bom_restock_update) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        private function dev2_canDeleteRestock()
        {
            if ($this->login_user->is_admin) {
                return true;
            } else {
                if ($this->Permission_m->permissions->bom_restock_delete) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        private function dev2_canDeleteRestockItem($id)
        {
            $rows = $this->Bom_project_item_items_model->getCountStockUsedById($id);

            if ($rows == 0) {
                return true;
            } else {
                return false;
            }
        }

        public function dev2_restock_item_delete()
        {
            $id = $this->input->post('id');

            if ($this->dev2_canDeleteRestock() && $this->dev2_canDeleteRestockItem($id)) {
                $this->Bom_item_groups_model->dev2_deleteRestockingItemById($id);
                echo json_encode(array(
                    'success' => true, 
                    'message' => lang('record_deleted')
                ));
            } else {
                echo json_encode(array(
                    'success' => false, 
                    'message' => lang('no_permissions')
                ));
            }
        }

// END: Material Reports
}