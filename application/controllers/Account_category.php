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
        $this->template->rander("account_category/index");
    }

    function get_category_list()
    {
        $list = $this->Account_category_model->get();
        $data = array();

        foreach ($list as $item) {
            $data[] = array(
                $item->id,
                lang($item->title) ? lang($item->title) : $item->title,
                $item->created_by == 0 ? "System" : $this->Account_category_model->created_by($item->created_by),
                format_to_date($item->created_date)
            );
        }

        echo json_encode(array("data" => $data));
    }

    function post_category()
    {
        validate_submitted_data(array(
            "title" => "required"
        ));

        $data = array(
            "title" => $this->input->post("title"),
            "created_by" => $this->login_user->id
        );

        $insert_id = $this->Account_category_model->set($data);
        $insert_result = $this->Account_category_model->get($insert_id);

        $result = array(
            $insert_result[0]->id,
            lang($insert_result[0]->title) ? lang($insert_result[0]->title) : $insert_result[0]->title,
            $insert_result[0]->created_by == 0 ? "System" : $this->Account_category_model->created_by($insert_result[0]->created_by),
            format_to_date($insert_result[0]->created_date)
        );


        echo json_encode(array("success" => true, "data" => $result, "id" =>$insert_id, "message" => lang("record_saved")));
    }

    function modal_form()
    {
        $this->load->view("account_category/modal_form");
    }
}

?>