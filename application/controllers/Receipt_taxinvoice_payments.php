<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Receipt_taxinvoice_payments extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        // $this->init_permission_checker("receipt_taxinvoice");
        $this->load->model('Receipt_taxinvoices_model');
        $this->load->model('Receipt_taxinvoice_items_model');
        $this->load->model('Receipt_taxinvoice_payments_model');
    }

    /* load receipt_taxinvoice list view */

    function index()
    {
        if ($this->login_user->user_type === "staff") {
            $view_data['payment_method_dropdown'] = $this->get_payment_method_dropdown();
            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
            $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_epxenses("payments");
            $this->template->rander("receipt_taxinvoices/payment_received", $view_data);
        } else {
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";
            $this->template->rander("clients/payments/index", $view_data);
        }
    }

    function get_payment_method_dropdown()
    {
        $this->access_only_team_members();

        $payment_methods = $this->Payment_methods_model->get_all_where(array("deleted" => 0))->result();

        $payment_method_dropdown = array(array("id" => "", "text" => "- " . lang("payment_methods") . " -"));
        foreach ($payment_methods as $value) {
            $payment_method_dropdown[] = array("id" => $value->id, "text" => $value->title);
        }

        return json_encode($payment_method_dropdown);
    }

    //load the payment list yearly view
    function yearly()
    {
        $this->load->view("receipt_taxinvoices/yearly_payments");
    }

    //load custom payment list
    function custom()
    {
        $this->load->view("receipt_taxinvoices/custom_payments_list");
    }

    /* load payment modal */


    /* add or edit a payment */

    function save_payment()
    {
        /*
		$request = $this->input->post();
		
		
		arr($request);
		
		exit;
		*/
        /*		
		Array
(
    [id] => 
    [receipt_taxinvoice_id] => 39
    [receipt_taxinvoice_payment_method_id] => 6
    [receipt_taxinvoice_payment_date] => 2022-02-24
    [receipt_taxinvoice_payment_amount] => 33443
    [receipt_taxinvoice_payment_note] => dfsdfsfds
)

*/
        ///$this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "receipt_taxinvoice_id" => "required|numeric",
            "receipt_taxinvoice_payment_method_id" => "required|numeric",
            "receipt_taxinvoice_payment_date" => "required",
            "receipt_taxinvoice_payment_amount" => "required",
            "receipt_taxinvoice_payment_namebank" => "required",
            "receipt_taxinvoice_payment_runnumber" => "required"
        ));

        $id = $this->input->post('id');
        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');

        $receipt_taxinvoice_payment_data = array(
            "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
            "payment_date" => $this->input->post('receipt_taxinvoice_payment_date'),
            "payment_method_id" => $this->input->post('receipt_taxinvoice_payment_method_id'),
            "note" => $this->input->post('receipt_taxinvoice_payment_note'),
            "amount" => unformat_currency($this->input->post('receipt_taxinvoice_payment_amount')),
            "namebank" => $this->input->post('receipt_taxinvoice_payment_namebank'),
            "runnumber" => unformat_currency($this->input->post('receipt_taxinvoice_payment_runnumber')),
            "created_at" => get_current_utc_time(),
            "created_by" => $this->login_user->id,
        );

        $receipt_taxinvoice_payment_id = $this->Receipt_taxinvoice_payments_model->save($receipt_taxinvoice_payment_data, $id);


        if ($receipt_taxinvoice_payment_id) {

            //As receiving payment for the receipt_taxinvoice, we'll remove the 'draft' status from the receipt_taxinvoice 
            $this->Receipt_taxinvoices_model->update_receipt_taxinvoice_status($receipt_taxinvoice_id);

            if (!$id) { //show payment confirmation for new payments only
                log_notification("receipt_taxinvoice_payment_confirmation", array("receipt_taxinvoice_payment_id" => $receipt_taxinvoice_payment_id, "receipt_taxinvoice_id" => $receipt_taxinvoice_id), "0");
            }


            //echo 'dfsdfsasdf';

            //exit;
            //get payment data
            $options = array("id" => $receipt_taxinvoice_payment_id);

            $item_info = $this->Receipt_taxinvoice_payments_model->get_details($options)->row();

            echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "data" => $this->_make_payment_row($item_info), "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), 'id' => $receipt_taxinvoice_payment_id, 'message' => lang('record_saved')));
        } else {


            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo a payment */

    function delete_payment()
    {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Receipt_taxinvoice_payments_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Receipt_taxinvoice_payments_model->get_details($options)->row();
                echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "data" => $this->_make_payment_row($item_info), "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Receipt_taxinvoice_payments_model->delete($id)) {
                $item_info = $this->Receipt_taxinvoice_payments_model->get_one($id);
                echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of receipt_taxinvoice payments, prepared for datatable  */


    /* list of receipt_taxinvoice payments, prepared for datatable  */

    function payment_list_data_of_client($client_id = 0)
    {
        if (!$this->can_view_receipt_taxinvoices($client_id)) {
            redirect("forbidden");
        }

        $options = array("client_id" => $client_id);
        $list_data = $this->Receipt_taxinvoice_payments_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of receipt_taxinvoice payments, prepared for datatable  */

    function payment_list_data_of_project($project_id = 0)
    {
        $options = array("project_id" => $project_id);

        $list_data = $this->Receipt_taxinvoice_payments_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of receipt_taxinvoice payment list table */

    private function _make_payment_row($data)
    {
        $receipt_taxinvoice_url = "";
        if (!$this->can_view_receipt_taxinvoices($data->client_id)) {
            ///redirect("forbidden");
        }

        if ($this->login_user->user_type == "staff") {
            $receipt_taxinvoice_url = anchor(get_uri("receipt_taxinvoices/view/" . $data->receipt_taxinvoice_id), get_receipt_taxinvoice_id($data->receipt_taxinvoice_id));
        } else {
            $receipt_taxinvoice_url = anchor(get_uri("receipt_taxinvoices/preview/" . $data->receipt_taxinvoice_id), get_receipt_taxinvoice_id($data->receipt_taxinvoice_id));
        }
        return array(
            $receipt_taxinvoice_url,
            $data->payment_date,
            format_to_date($data->payment_date, false),
            $data->payment_method_title,
            $data->note,
            to_currency($data->amount, $data->currency_symbol),
            modal_anchor(get_uri("receipt_taxinvoice_payments/payment_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_payment'), "data-post-id" => $data->id, "data-post-receipt_taxinvoice_id" => $data->receipt_taxinvoice_id,))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("receipt_taxinvoice_payments/delete_payment"), "data-action" => "delete"))
        );
    }

    /* receipt_taxinvoice total section */

    private function _get_receipt_taxinvoice_total_view($receipt_taxinvoice_id = 0)
    {
        $view_data["receipt_taxinvoice_total_summary"] = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($receipt_taxinvoice_id);
        $view_data["receipt_taxinvoice_id"] = $receipt_taxinvoice_id;
        $view_data["can_edit_receipt_taxinvoices"] = $this->can_edit_receipt_taxinvoices();
        return $this->load->view('receipt_taxinvoices/receipt_taxinvoice_total_section', $view_data, true);
    }

    //load the expenses yearly chart view
    function yearly_chart()
    {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        $this->load->view("receipt_taxinvoices/yearly_payments_chart", $view_data);
    }

    function yearly_chart_data()
    {

        $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
        $result = array();

        $year = $this->input->post("year");
        if ($year) {
            $currency = $this->input->post("currency");
            $payments = $this->Receipt_taxinvoice_payments_model->get_yearly_payments_chart($year, $currency);
            $values = array();
            foreach ($payments as $value) {
                $values[$value->month - 1] = $value->total; //in array the month january(1) = index(0)
            }

            foreach ($months as $key => $month) {
                $value = get_array_value($values, $key);
                $result[] = array(lang("short_" . $month), $value ? $value : 0);
            }

            echo json_encode(array("data" => $result, "currency_symbol" => $currency));
        }
    }

    function get_paytm_checksum_hash()
    {
        $this->load->library("paytm");
        $payment_data = $this->paytm->get_paytm_checksum_hash($this->input->post("input_data"), $this->input->post("verification_data"));

        if ($payment_data) {
            echo json_encode(array("success" => true, "checksum_hash" => get_array_value($payment_data, "checksum_hash"), "payment_verification_code" => get_array_value($payment_data, "payment_verification_code")));
        } else {
            echo json_encode(array("success" => false, "message" => lang("paytm_checksum_hash_error_message")));
        }
    }

    function get_stripe_payment_intent_session()
    {
        $this->access_only_clients();
        $this->load->library("stripe");
        try {
            $session = $this->stripe->get_stripe_payment_intent_session($this->input->post("input_data"), $this->login_user->id);
            if ($session->id) {
                echo json_encode(array("success" => true, "session_id" => $session->id, "publishable_key" => $this->stripe->get_publishable_key()));
            } else {
                echo json_encode(array('success' => false, 'message' => lang('error_occurred')));
            }
        } catch (Exception $ex) {
            echo json_encode(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    function payment_list_data($receipt_taxinvoice_id = 0)
    {


        ///  if (!$this->can_view_receipt_taxinvoices()) {
        //redirect("forbidden");
        //// }

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $payment_method_id = $this->input->post('payment_method_id');
        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
            "payment_method_id" => $payment_method_id,
            "currency" => $this->input->post("currency"),
            "project_id" => $this->input->post("project_id"),
        );



        $list_data = $this->Receipt_taxinvoice_payments_model->get_details($options)->result();



        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    function payment_modal_form($id = NULL)
    {

        /*
        validate_submitted_data( array(
            "id" => "numeric",
            "receipt_taxinvoice_id" => "numeric"
        ));*/

        $view_data['model_info'] = $this->Receipt_taxinvoice_payments_model->get_one($this->input->post('id'));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id') ? $this->input->post('receipt_taxinvoice_id') : $view_data['model_info']->receipt_taxinvoice_id;
        $receipt_taxinvoices_dropdown = array();
        $view_data['receipt_taxinvoices_dropdown'] = "";

        if (!$receipt_taxinvoice_id) {
            //prepare receipt_taxinvoices dropdown
            $receipt_taxinvoices = $this->Receipt_taxinvoices_model->get_receipt_taxinvoices_dropdown_list()->result();





            foreach ($receipt_taxinvoices as $receipt_taxinvoice) {
                $receipt_taxinvoices_dropdown[$receipt_taxinvoice->id] = get_receipt_taxinvoice_id($receipt_taxinvoice->id);
            }

            $view_data['receipt_taxinvoices_dropdown'] = array("" => "-") + $receipt_taxinvoices_dropdown;
        }

        // var_dump($view_data['receipt_taxinvoices_dropdown']);exit;

        $view_data['payment_methods_dropdown'] = $this->Payment_methods_model->get_dropdown_list(array("title"), "id", array("online_payable" => 0, "deleted" => 0));
        $view_data['receipt_taxinvoice_id'] = $receipt_taxinvoice_id;

        $view_data['dsdfsdsfads'] = form_dropdown("receipt_taxinvoice_id", $view_data['receipt_taxinvoices_dropdown'], "", "class='select2 validate-hidden' id='receipt_taxinvoice_id' data-rule-required='true' data-msg-required='" . lang('field_required') . "' ");


        $this->load->view('receipt_taxinvoices/payment_modal_form', $view_data);
    }
}

/* End of file payments.php */
/* Location: ./application/controllers/payments.php */