<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Account_category extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->access_only_admin();
        $this->load->model("Account_category_model");
    }

    function index()
    {
        $view_data["primary_dropdown"] = json_encode($this->Account_category_model->get_primary_dropdown());
        $view_data["secondary_dropdown"] = json_encode($this->Account_category_model->get_secondary_dropdown());

        // var_dump(arr($view_data)); exit();
        $this->template->rander("account_category/index", $view_data);
    }

    function get_category_list()
    {
        $list = $this->Account_category_model->get();
        $data = array();

        foreach ($list as $item) {
            $data[] = array(
                $item->id,
                $item->account_code,
                lang($item->account_name) ? lang($item->account_name) : $item->account_name,
                $item->created_by == 0 ? "System" : $this->Account_category_model->created_by($item->created_by),
                format_to_date($item->created_date)
            );
        }

        echo json_encode(array("data" => $data));
    }

    function post_category()
    {
        validate_submitted_data(
            array(
                "account_type" => "required",
                "account_sub_type" => "required",
                "account_code" => "required",
                "th_description" => "required",
                "en_description" => "required"
            )
        );

        $data = array(
            "primary_id" => $this->input->post("account_type"),
            "secondary_id" => $this->input->post("account_sub_type"),
            "thai_name" => $this->input->post("th_description"),
            "english_name" => $this->input->post("en_description"),
            "account_code" => $this->input->post("account_code")
        );

        $insert_id = $this->Account_category_model->set($data);
        $insert_result = $this->Account_category_model->get($insert_id);

        $result = array();
        if ($insert_id != 0) {
            $result = array(
                "success" => true,
                "data" => $this->prepare_item_display($insert_result[0]),
                "id" => $insert_id,
                "message" => lang("account_code_save_success")
            );
        } else {
            $result = array(
                "success" => false,
                "data" => array(),
                "id" => 0,
                "message" => lang("account_code_save_failure")
            );
        }
        
        echo json_encode($result);
    }

    function modal_form()
    {
        $view_data["account_primary"] = $this->Account_category_model->dev2_selectDataListByTableName("account_primary");
        $view_data["account_secondary"] = json_encode($this->Account_category_model->dev2_selectDataListByTableName("account_secondary"));

        $this->load->view("account_category/modal_form", $view_data);
    }

    // Start new account category
    private function prepare_item_display($item)
    {
        $account_type["name"] = "-";
        if (!empty($item->primary_id)) {
            $account_type["info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_primary", "id", $item->primary_id)[0];
            if (!empty($account_type["info"])) {
                $account_type["name"] = $account_type["info"]->thai_name . " (" . $account_type["info"]->account_code . ")";
            }
        }

        $sub_account_type["name"] = "-";
        if (!empty($item->secondary_id)) {
            $sub_account_type["info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_secondary", "id", $item->secondary_id)[0];
            if (!empty($sub_account_type["info"])) {
                $sub_account_type["name"] = $sub_account_type["info"]->thai_name . " (" . $sub_account_type["info"]->account_code . ")";
            }
        }

        $data = [
            $item->id,
            $item->account_code,
            $item->thai_name,
            $account_type["name"],
            $sub_account_type["name"]
        ];

        return $data;
    }

    public function display_category_list()
    {
        $options = [];
        $post = $this->input->post();

        if (!empty($post["primary_id"])) {
            $options["primary_id"] = $post["primary_id"];
        }

        if (!empty($post["secondary_id"])) {
            $options["secondary_id"] = $post["secondary_id"];
        }

        $list = $this->Account_category_model->get_list($options);
        $data = array();

        if (sizeof($list)) {
            foreach ($list as $item) {
                $data[] = $this->prepare_item_display($item);
            }
        }

        echo json_encode(array("data" => $data));
    }
    
}
