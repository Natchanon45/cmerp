<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Warehouse_category extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->access_only_admin();

        $this->load->model("Warehouse_category_model");
        $this->load->model("Account_category_model");
    }

    function index()
    {
        $this->template->rander("warehouse_category/index");
    }

    function modal_form()
    {
        if ($this->input->post('id')) {
            $data['model_info'] = $this->Warehouse_category_model->get($this->input->post('id'))[0];
            $data['model_info']->can_update = $this->dev2_canUpdate($this->input->post('id'));

            $this->load->view("warehouse_category/modal_form", $data);
        } else {
            $this->load->view("warehouse_category/modal_form");
        }
    }

    function dev2_getCategoryList()
    {
        $lists = $this->Warehouse_category_model->get();
        $data = array();

        if (sizeof($lists)) {
            foreach ($lists as $list) {
                if ($this->login_user->is_admin) {
                    $button = modal_anchor(
                        get_uri("warehouse_category/modal_form"), 
                        "<i class='fa fa-pencil'></i>", 
                        array(
                            "class" => "edit", 
                            "title" => lang('edit_warehouse_category'), 
                            "data-post-id" => $list->id
                        )
                    );
                }

                $data[] = array(
                    $list->id,
                    $list->location_code,
                    lang($list->location_name) ? lang($list->location_name) : $list->location_name,
                    $list->created_by == 0 ? "System" : $this->Account_category_model->created_by($list->created_by),
                    format_to_date($list->created_date),
                    $button
                );
            }
        }

        echo json_encode(array("data" => $data));
    }

    function dev2_postCategory()
    {
        validate_submitted_data(array(
            "code" => "required",
            "title" => "required"
        ));

        $data = array(
            "location_code" => $this->input->post("code"),
            "location_name" => $this->input->post("title"),
            "created_by" => $this->login_user->id
        );

        $id = $this->input->post("id");
        if (!$id) {
            $is_duplicated = $this->dev2_isDuplicatedByCode($this->input->post("code"));
            if ($is_duplicated) {
                echo json_encode(array("success" => false, "data" => $data, "id" => 0, "type" => "insert", "message" => lang("cant_duplicated_code")));
                exit();
            }

            $insert_id = $this->Warehouse_category_model->post($data);
            $insert_result = $this->Warehouse_category_model->get($insert_id);

            $button = "";
            if ($this->login_user->is_admin) {
                $button = modal_anchor(
                    get_uri("warehouse_category/modal_form"),
                    "<i class='fa fa-pencil'></i>",
                    array(
                        "class" => "edit",
                        "title" => lang('edit_warehouse_category'),
                        "data-post-id" => $insert_result[0]->id
                    )
                );
            }

            $result = array(
                $insert_result[0]->id,
                $insert_result[0]->location_code,
                lang($insert_result[0]->location_name) ? lang($insert_result[0]->location_name) : $insert_result[0]->location_name,
                $insert_result[0]->created_by == 0 ? "System" : $this->Account_category_model->created_by($insert_result[0]->created_by),
                format_to_date($insert_result[0]->created_date),
                $button
            );

            echo json_encode(array("success" => true, "data" => $result, "id" => $insert_id, "type" => "insert", "message" => lang("record_saved")));
        } else {
            $is_duplicated = $this->dev2_isDuplicatedByCodeWithId($this->input->post("code"), $id);
            if ($is_duplicated) {
                echo json_encode(array("success" => false, "data" => $data, "id" => $id, "type" => "updated", "message" => lang("cant_duplicated_code")));
                exit();
            }

            $this->Warehouse_category_model->put($data, $id);
            $update_result = $this->Warehouse_category_model->get($id);

            $button = "";
            if ($this->login_user->is_admin) {
                $button = modal_anchor(
                    get_uri("warehouse_category/modal_form"),
                    "<i class='fa fa-pencil'></i>",
                    array(
                        "class" => "edit",
                        "title" => lang('edit_warehouse_category'),
                        "data-post-id" => $update_result[0]->id
                    )
                );
            }

            $result = array(
                $update_result[0]->id,
                $update_result[0]->location_code,
                lang($update_result[0]->location_name) ? lang($update_result[0]->location_name) : $update_result[0]->location_name,
                $update_result[0]->created_by == 0 ? "System" : $this->Account_category_model->created_by($update_result[0]->created_by),
                format_to_date($update_result[0]->created_date),
                $button
            );

            echo json_encode(array("success" => true, "data" => $result, "id" => $id, "type" => "updated", "message" => lang("record_saved")));
        }
    }

    function dev2_canUpdate($id = 0)
    {
        $count = $this->Bom_materials_model->dev2_getCountWarehouseById($id);
        $can_update = true;
        if ($count > 0) {
            $can_update = false;
        }

        return $can_update;
    }

    function dev2_isDuplicatedByCode($code)
    {
        $rows = $this->Warehouse_category_model->dev2_getCountWarehouseCateByCode($code);
        $is_duplicated = false;
        if ($rows > 0) {
            $is_duplicated = true;
        }

        return $is_duplicated;
    }

    function dev2_isDuplicatedByCodeWithId($code, $id)
    {
        $rows = $this->Warehouse_category_model->dev2_getCountWarehouseCateByCodeWithId($code, $id);
        $is_duplicated = false;
        if ($rows > 0) {
            $is_duplicated = true;
        }

        return $is_duplicated;
    }

}

?>