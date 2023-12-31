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

    // Service/Wage
    public function services()
    {
        $view_data["primary_dropdown"] = json_encode($this->Account_category_model->get_primary_dropdown());
        $view_data["secondary_dropdown"] = json_encode($this->Account_category_model->get_secondary_dropdown());

        $view_data["expense_dropdown"] = json_encode($this->Account_category_model->get_expense_dropdown());
        $view_data["income_dropdown"] = json_encode($this->Account_category_model->get_income_dropdown());

        // var_dump(arr($view_data)); exit();
        $this->template->rander("account_category/services", $view_data);
    }

    private function prepare_services_display($item) : array
    {
        $expense_account = "-";
        $income_account = "-";
        $button = "";

        $prepare_expense = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_category", "id", $item->expense_acct_cate_id);
        if (!empty($prepare_expense[0])) {
            $expense_account = $prepare_expense[0]->account_code . " - " . $prepare_expense[0]->thai_name;
        }

        $prepare_income = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_category", "id", $item->income_acct_cate_id);
        if (!empty($prepare_income[0])) {
            $income_account = $prepare_income[0]->account_code . " - " . $prepare_income[0]->thai_name;
        }

        $prepare_button = modal_anchor(
            get_uri("account_category/services_modal"),
            '<i class="fa fa-pencil"></i>',
            array(
                "title" => lang("service_wage"),
                "data-post-id" => $item->id
            )
        );
        if (!empty($prepare_button)) {
            $button = $prepare_button;
        }

        $data = [
            $item->id,
            $item->service_name,
            $expense_account,
            $income_account,
            $button
        ];

        return $data;
    }

    public function display_services_list()
    {
        $options = array();
        $post = $this->input->post();

        if (!empty($post["income_acct_cate_id"])) {
            $options["income_acct_cate_id"] = $post["income_acct_cate_id"];
        }

        if (!empty($post["expense_acct_cate_id"])) {
            $options["expense_acct_cate_id"] = $post["expense_acct_cate_id"];
        }

        $data = array();
        $list = $this->Account_category_model->dev2_getAccountServicesList($options);
        if (sizeof($list)) {
            foreach ($list as $li) {
                $data[] = $this->prepare_services_display($li);
            }
        }

        // var_dump(arr($list)); exit();
        echo json_encode(array("data" => $data));
    }

    public function services_modal()
    {
        $post = $this->input->post();
        
        $view_data["model_info"] = new stdClass();
        if (isset($post["id"]) && !empty($post["id"])) {
            $view_data["model_info"] = $this->Account_category_model->dev2_getAccountServiceById($post["id"]);
        }

        // Expenses account list
        $view_data["expense_account_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["expense_account_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());

        // Revenues account list
        $view_data["income_account_secondary"] = $this->Account_category_model->dev2_getIncomeSecondaryList();
        $view_data["income_account_category"] = json_encode($this->Account_category_model->dev2_getIncomeCategoryList());

        // var_dump(arr($view_data)); exit();
        $this->load->view("account_category/services_modal", $view_data);
    }

    public function services_modal_post()
    {
        validate_submitted_data(
            array(
                "service_name" => "required",
                "expense_acct_sec_id" => "required",
                "expense_acct_cate_id" => "required",
                "income_acct_sec_id" => "required",
                "income_acct_cate_id" => "required"
            )
        );
        
        $post = $this->input->post();
        
        $post_id = (isset($post["post_id"]) && !empty($post["post_id"])) ? $post["post_id"] : 0;
        $data = [
            "service_name" => $post["service_name"],
            "expense_acct_sec_id" => $post["expense_acct_sec_id"],
            "expense_acct_cate_id" => $post["expense_acct_cate_id"],
            "income_acct_sec_id" => $post["income_acct_sec_id"],
            "income_acct_cate_id" => $post["income_acct_cate_id"]
        ];
        $insert_id = $this->Account_category_model->dev2_postAccountService($data, $post_id);
        
        $result = array();
        if ($insert_id) {
            $insert_info = $this->Account_category_model->dev2_getAccountServiceById($insert_id);

            $result = array(
                "success" => true,
                "data" => $this->prepare_services_display($insert_info),
                "id" => $insert_id,
                "message" => lang("service_saved_sucess")
            );
        } else {
            $result = array(
                "success" => false,
                "data" => array(),
                "id" => 0,
                "message" => lang("service_saved_failure")
            );
        }

        echo json_encode($result);
    }
    
}
