<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Withholding_tax extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("Withholding_tax_model");
    }

    public function index()
    {
        if ($this->input->post("datatable")) {
            jout(array("data" => $this->Withholding_tax_model->indexDataSet()));
            return;
        }

        redirect("accounting/buy/withholding_tax");
    }

    public function wht_form($id = 0)
    {
        $post = $this->input->post();

        $view_data["wht_info"] = array();

        // var_dump(arr($post)); exit();
        $this->template->rander("withholding_tax/form", $view_data);
    }
}