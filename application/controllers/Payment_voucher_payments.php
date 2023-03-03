<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Payment_voucher_payments extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->init_permission_checker("payment_voucher");
        $this->load->model('Db_model');
    }

    /* load invoice list view */

    function index()
    {
        if ($this->login_user->user_type === "staff") {
            $view_data['payment_method_dropdown'] = $this->get_payment_method_dropdown();
            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
            $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_epxenses("payments");
            $this->template->rander("payment_vouchers/payment_received", $view_data);
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
        $this->load->view("payment_vouchers/yearly_payments");
    }

    //load custom payment list
    function custom()
    {
        $this->load->view("payment_vouchers/custom_payments_list");
    }

    /* load payment modal */


    /* add or edit a payment */


    /* delete or undo a payment */

    function delete_payment()
    {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Payment_voucher_payments_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Payment_voucher_payments_model->get_details($options)->row();
                echo json_encode(

                    array(
                        "success" => true,
                        "invoice_id" => $item_info->payment_vouchers_id,
                        "message" => lang('record_undone')
                    )
                );
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Payment_voucher_payments_model->delete($id)) {
                $item_info = $this->Payment_voucher_payments_model->get_one($id);
                echo json_encode(array("success" => true, "invoice_id" => $item_info->payment_vouchers_id, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of invoice payments, prepared for datatable  */


    /* list of invoice payments, prepared for datatable  */

    function payment_list_data_of_client($client_id = 0)
    {
        if (!$this->cp('payment_vouchers', 'view_row')) {
            //redirect("forbidden");
        }

        $options = array("client_id" => $client_id);
        $list_data = $this->Payment_voucher_payments_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of invoice payments, prepared for datatable  */

    function payment_list_data_of_project($project_id = 0)
    {
        $options = array("project_id" => $project_id);

        $list_data = $this->Payment_voucher_payments_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_payment_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of invoice payment list table */


    /* invoice total section */

    private function _get_invoice_total_view($invoice_id = 0)
    {
        $view_data["invoice_total_summary"] = $this->Payment_vouchers_model->get_invoice_total_summary($invoice_id);
        $view_data["invoice_id"] = $invoice_id;
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();
        return $this->load->view('payment_vouchers/invoice_total_section', $view_data, true);
    }

    //load the expenses yearly chart view
    function yearly_chart()
    {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        $this->load->view("payment_vouchers/yearly_payments_chart", $view_data);
    }

    function yearly_chart_data()
    {

        $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
        $result = array();

        $year = $this->input->post("year");
        if ($year) {
            $currency = $this->input->post("currency");
            $payments = $this->Payment_voucher_payments_model->get_yearly_payments_chart($year, $currency);
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

    private function _make_payment_row($data)
    {

        //arr( $data );
        $invoice_url = "";
        if (!$this->cp('payment_vouchers', 'view_row')) {
            //redirect("forbidden");
        }

        if ($this->login_user->user_type == "staff") {
            $invoice_url = anchor(get_uri("payment_vouchers/view/" . $data->invoice_id), get_payment_voucher_id($data->invoice_id));
        } else {
            $invoice_url = anchor(get_uri("payment_vouchers/preview/" . $data->invoice_id), get_payment_voucher_id($data->invoice_id));
        }
        return array(
            $invoice_url,
            $data->payment_date,
            format_to_date($data->payment_date, false),
            $data->payment_method_title,
            get_order_id($data->invoice_id),
            $data->company_name,
            to_currency($data->amount, ' '),
            $data->currency_symbol,

            modal_anchor(get_uri("payment_voucher_payments/payment_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_payment'), "data-post-id" => $data->id, "data-post-invoice_id" => $data->payment_vouchers_id,))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("payment_voucher_payments/delete_payment"), "data-action" => "delete"))
        );
    }

    function payment_list_data($invoice_id = 0)
    {


        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $payment_method_id = $this->input->post('payment_method_id');
        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "invoice_id" => $invoice_id,
            "payment_method_id" => $payment_method_id,
            "currency" => $this->input->post("currency"),
            "project_id" => $this->input->post("project_id"),
        );

        $list_data = $this->Payment_voucher_payments_model->get_details($options)->result();

        //arr($list_data);
        $result = array();

        foreach ($list_data as $data) {
			
			//arr( $data );

            $invoice_url = "";


            if ($this->login_user->user_type == "staff") {
                $invoice_url = anchor(get_uri("payment_vouchers/view/" . $data->invoice_id), get_payment_voucher_id($data->invoice_id));
            } else {
                $invoice_url = anchor(get_uri("payment_vouchers/preview/" . $data->invoice_id), get_payment_voucher_id($data->invoice_id));
            }

            $result[] =  array(
                $invoice_url,
                $data->payment_date,
                format_to_date($data->payment_date, false),
                $data->payment_method_title,
                get_order_id($data->invoice_id),
                $data->company_name,
                to_currency($data->amount, ' '),
                $data->currency_symbol,

                modal_anchor(get_uri("payment_voucher_payments/payment_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_payment'), "data-post-id" => $data->id, "data-post-invoice_id" => $data->payment_vouchers_id,))
                    . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("payment_voucher_payments/delete_payment"), "data-action" => "delete"))
            );
        }

        echo json_encode(array("data" => $result));
    }
	
	

    function payment_modal_form() {
		
//$model_info->tax_id ? to_decimal_format($model_info->tax_id) : ""

//$model_info->tax_date
		


        $request = $this->input->post();
		
//	arr(  $request);	
		$id = $this->input->post( 'id' );
		$sql = "
			SELECT * FROM `payment_voucher_payments` WHERE `id` = ". $id ."
		";
	//arr( $sql );
		$view_data['model_info'] = new stdClass;
		$view_data['model_info']->bank_id = NULL;
		$view_data['model_info']->invoice_id = NULL;
		$view_data['model_info']->invoice_id1 = NULL;
		$view_data['model_info']->id = NULL;
		$view_data['model_info']->invoice1_id = NULL;
		$view_data['model_info']->payment_date = NULL;
		$view_data['model_info']->payment_method_id = NULL;
		$view_data['model_info']->number_bank = NULL;
		$view_data['model_info']->amount = 0;
		$view_data['model_info']->note = NULL;
        $view_data['model_info']->taxnumber_id =  NULL;
        $view_data['model_info']->tax_date =  NULL;
		
		
		foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {
		//	arr( $va );
			$view_data['model_info'] = $va;
		}
		
		
 

        $invoice_id = $this->input->post('invoice_id') ? $this->input->post('invoice_id') : $view_data['model_info']->invoice_id;
        $invoice_id1 = $this->input->post('invoice_id1') ? $this->input->post('invoice_id1') : $view_data['model_info']->invoice_id1;
        $view_data['payment_vouchers_id'] = $invoice_id;


        $invoices = $this->Orders_model->get_orders_dropdown_list()->result();
        $invoices1 = $this->Purchaserequests_model->get_purchaserequests_dropdown_list()->result();


        $sql = "SELECT * FROM bank";
      
        foreach ( $this->db_model->fetchAll( $sql ) as $kb => $vb ) {
             
			
            $banks[$vb->id] = $vb->name;
        }




       // var_dump($a);


        $invoices_dropdown = array();
        $invoices_dropdown1 = array();

        foreach ($invoices as $invoice) {

            if (empty($request['id'])) {
                $order = $this->Orders_model->get_order_total_summary($invoice->id);
            } else {
                $order = $this->Orders_model->get_order_total_summary($invoice->id, $request['id']);
            }

            if ($order->showOnPaymen == 0) {

                continue;
            }

            $invoices_dropdown[$invoice->id] = get_order_id($invoice->id) . ' ' . $order->paymentStatus . ' ' . $order->notPaid;
        
        }

        foreach ($invoices1 as $invoice1) {

            $invoices_dropdown1[$invoice1->id] = get_purchaserequests_id($invoice1->id);

        }


        //
        $view_data['invoices_dropdown'] = array("" => "-") + $invoices_dropdown;
        $view_data['invoices_dropdown1'] = array("" => "-") + $invoices_dropdown1;


        $view_data['dsfaadfs'] = form_dropdown("invoice_id", $view_data['invoices_dropdown'], "", "class='select2 validate-hidden' id='invoice_id' data-rule-required='true' data-msg-required='" . lang('field_required') . "' ");


        $view_data['payment_methods_dropdown'] = $this->Payment_methods_model->get_dropdown_list(array("title"), "id", array("online_payable" => 0, "deleted" => 0));


        $view_data['invoice_id'] = $invoice_id;
        $view_data['invoice_id1'] = $invoice_id1;

        $val = $view_data['model_info']->invoice_id;


        $view_data['invoice_id_s'] =  ' 
			<div class="form-group">
			<label for="invoice_id" class=" col-md-3">' . lang('order_pay') . '</label>
			<div class="col-md-9">
			' . form_dropdown("invoice_id1", $view_data['invoices_dropdown1'], $val, "class='select2 validate-hidden' id='invoice_id1' data-rule-required='true' data-msg-required='" . lang('field_required') . "' ") . '
			</div>
			</div>
		';

        $view_data['invoice_id_s1'] =  ' 
			<div class="form-group">
			<label for="invoice_id1" class=" col-md-3">ใบรับสินค้า</label>
			<div class="col-md-9">
			' . form_dropdown("invoice_id", $view_data['invoices_dropdown'], $val, "class='select2 validate-hidden' id='invoice_id' data-rule-required='true' data-msg-required='" . lang('field_required') . "' ") . '
			</div>
			</div>
		';

        $view_data['select_bank'] =  ' 
        	<div class="form-group">
        	<label for="bank" class=" col-md-3">ธนาคาร</label>
        	<div class="col-md-9">
        	' . form_dropdown("banks_data", $banks, $view_data['model_info']->bank_id, " class='select2 validate-hidden' id='name_bank' name='name_bank' data-rule-required='true' data-msg-required='" . lang('field_required') . "' ") . '
        	</div>
        	</div>
        ';


        if ($invoice_id) {
            ///$view_data['invoice_id_s'] =  '<input type="hidden" name="invoice_id" value="'. $invoice_id .'" />';
        } else {
			
        }





        $this->load->view('payment_vouchers/payment_modal_form', $view_data);
    }

    function save_payment() {
        ///$this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "invoice_payment_invoices" => "numeric",
            "invoice_payment_method_id" => "required|numeric",
            "invoice_payment_amount" => "required"
        ));

        $id = $this->input->post('id');
		$invoice_id = $this->input->post('invoice_id');
        $sql = "SELECT * FROM `bank` WHERE bank.id = " . $this->input->post('banks_data') . " ";
       // arr($sql);
        $res = $this->db->query($sql)->row();
      //  var_dump($res);
      //  exit;

        $invoice_payment_data = array(
            "invoice_id" => $invoice_id,
            "invoice_id1" => $this->input->post('invoice_id1'),
            "invoice1_id" => $this->input->post('invoice_payment_invoices'),
            "payment_date" => $this->input->post('invoice_payment_date'),
            "taxnumber_id" => $this->input->post('taxnumber_id'),
            "tax_date" => $this->input->post('tax_date'),
            "payment_method_id" => $this->input->post('invoice_payment_method_id'),
            "bank_id" => $this->input->post('banks_data'),
            "number_bank" => $this->input->post('number_bank'),
            "name_bank" => $res->name,
            "note" => $this->input->post('invoice_payment_note'),
            "amount" => unformat_currency($this->input->post('invoice_payment_amount')),
            "payment_vouchers_id" => $this->input->post('payment_vouchers_id'),
            "created_at" => get_current_utc_time(),
            "created_by" => $this->login_user->id,
        );
		
		if( !empty( $id ) ) {
			
			$this->dao->update( 'payment_voucher_payments',  $invoice_payment_data, "id = ". $id ."" );
			
			$invoice_payment_id = $id;
		}
		else {
			
			$this->dao->insert( 'payment_voucher_payments',  $invoice_payment_data );
			
			
			$invoice_payment_id = $this->db->insert_id(); 
		}

//exit;
        // var_dump($this->input->post());exit;

       // $invoice_payment_id = $this->Payment_voucher_payments_model->save($invoice_payment_data, $id);
	   
		
	 //  exit;
	   
        if ( true ) {

            //As receiving payment for the invoice, we'll remove the 'draft' status from the invoice 
            $this->Payment_vouchers_model->update_invoice_status($invoice_id);

            if (!$id) { //show payment confirmation for new payments only
                log_notification("invoice_payment_confirmation", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), "0");
            }
            //get payment data
            $options = array("id" => $invoice_payment_id);
            $item_info = $this->Payment_voucher_payments_model->get_details($options)->row();

            // arr($item_info);
            // $item_info->invoice_id = 11;
            /// echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_payment_row($item_info), "invoice_total_view" => $this->_get_invoice_total_view( $item_info->payment_vouchers_id ), 'id' => $invoice_payment_id, 'message' => lang('record_saved')));


            echo json_encode(array("success" => true, "invoice_id" => $invoice_payment_id, 'id' => $invoice_payment_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }
	
}

/* End of file payments.php */
/* Location: ./application/controllers/payments.php */