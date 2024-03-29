<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Payment_vouchers extends MY_Controller {


    /* load invoice list view */


    //load the yearly view of invoice list 
    function yearly() {
        $this->load->view("payment_vouchers/yearly_invoices");
    }

    //load the recurring view of invoice list 
    function recurring() {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();
        $this->load->view("payment_vouchers/recurring_invoices_list", $view_data);
    }

    //load the custom view of invoice list 
    function custom() {
        $this->load->view("payment_vouchers/custom_invoices_list");
    }

    /* load new invoice modal */

	
	/* prepare project dropdown based on this suggestion */

    function get_project_suggestion($client_id = 0) {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $client_id));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        echo json_encode($suggestion);
    }

    /* add or edit an invoice */

	private function _copy_estimate_or_order_items_to_invoice($copy_items_from_order, $invoice_id) {
        if (!$copy_items_from_order) {
            return false;
        }

        $items = null;
        // if ($copy_items_from_estimate) {
        //     $items = $this->Estimate_items_model->get_details(array("estimate_id" => $copy_items_from_estimate))->result();
        // } else if ($copy_items_from_order) {
            // $items = $this->Payment_voucher_payments_model->get_details(array("order_id" => $copy_items_from_order))->result();
         if ($copy_items_from_order) {
            $items = $this->Payment_voucher_payments_model->get_details(array("payment_vouchers_id" => $copy_items_from_order))->result();
        }

        if (!$items) {
            return false;
        }

        foreach ($items as $data) {
            $invoice_item_data = array(
                "payment_vouchers_id" => $invoice_id,
                "amount" => $data->amount ? $data->amount : 0,
                "payment_date" => $data->payment_date ? $data->payment_date : "",
                "payment_method_id" => $data->payment_method_id ? $data->payment_method_id : "",
                "note" => $data->note ? $data->note : "",
                "invoice_id" => $data->invoice_id ? $data->invoice_id : "",
                "invoice1_id" => $data->invoice1_id ? $data->invoice1_id : "",
            );
            $this->Payment_voucher_payments_model->save($invoice_item_data);
        }
    }

    /* delete or undo an invoice */

    function delete() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');

        $invoice_info = $this->Payment_vouchers_model->get_one($id);

        if ($this->Payment_vouchers_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($invoice_info->files) {
                $files = unserialize($invoice_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }

	

    /* list of invoice of a specific client, prepared for datatable  */

    function invoice_list_data_of_client($client_id) {
        if (!$this->can_view_invoices($client_id)) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );

        //don't show draft payment_vouchers to client
        if ($this->login_user->user_type == "client") {
            $options["exclude_draft"] = true;
        }


        $list_data = $this->Payment_vouchers_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of invoice of a specific project, prepared for datatable  */

    function invoice_list_data_of_project($project_id) {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "project_id" => $project_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );
        $list_data = $this->Payment_vouchers_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* show sub payment_vouchers tab  */

    function sub_invoices($recurring_invoice_id) {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }
        $view_data["recurring_invoice_id"] = $recurring_invoice_id;
        $this->load->view("payment_vouchers/sub_invoices", $view_data);
    }

    /* list of sub payment_vouchers of a recurring invoice, prepared for datatable  */

    function sub_invoices_list_data($recurring_invoice_id) {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields,
            "recurring_invoice_id" => $recurring_invoice_id
        );

        $list_data = $this->Payment_vouchers_model->get_details($options)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* return a row of invoice list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Payment_vouchers_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }


    //prepare invoice status label 
    private function _get_invoice_status_label($data, $return_html = true) {
        return get_invoice_status_label($data, $return_html);
    }

    // list of recurring payment_vouchers, prepared for datatable
    function recurring_list_data() {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }


        $options = array(
            "recurring" => 1,
            "next_recurring_start_date" => $this->input->post("next_recurring_start_date"),
            "next_recurring_end_date" => $this->input->post("next_recurring_end_date"),
            "currency" => $this->input->post("currency")
        );

        $list_data = $this->Payment_vouchers_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_recurring_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of recurring invoice list table */

    private function _make_recurring_row($data) {

        $invoice_url = anchor(get_uri("payment_vouchers/view/" . $data->id), get_payment_voucher_id($data->id));

        $cycles = $data->no_of_cycles_completed . "/" . $data->no_of_cycles;
        if (!$data->no_of_cycles) { //if not no of cycles, so it's infinity
            $cycles = $data->no_of_cycles_completed . "/&#8734;";
        }

        $status = "active";
        $invoice_status_class = "label-success";
        $cycle_class = "";

        //don't show next recurring if recurring is completed
        $next_recurring = format_to_date($data->next_recurring_date, false);

        //show red color if any recurring date is past
        if ($data->next_recurring_date < get_today_date()) {
            $next_recurring = "<span class='text-danger'>" . $next_recurring . "</span>";
        }


        $next_recurring_date = $data->next_recurring_date;
        if ($data->no_of_cycles_completed > 0 && $data->no_of_cycles_completed == $data->no_of_cycles) {
            $next_recurring = "-";
            $next_recurring_date = 0;
            $status = "stopped";
            $invoice_status_class = "label-danger";
            $cycle_class = "text-danger";
        }

        return array(
            $invoice_url,
            anchor(get_uri("clients/view/" . $data->client_id), $data->company_name),
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $next_recurring_date,
            $next_recurring,
            $data->repeat_every . " " . lang("interval_" . $data->repeat_type),
            "<span class='$cycle_class'>" . $cycles . "</span>",
            "<span class='label $invoice_status_class large'>" . lang($status) . "</span>",
            to_currency($data->invoice_value, $data->currency_symbol),
            $this->_make_options_dropdown($data->id)
        );
    }


    /* invoice total section */

    private function _get_invoice_total_view($invoice_id = 0) {
        $view_data["invoice_total_summary"] = $this->Payment_vouchers_model->get_invoice_total_summary($invoice_id);
        $view_data["invoice_id"] = $invoice_id;
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();
        return $this->load->view("payment_vouchers/invoice_total_section", $view_data, true);
    }

    /* load item modal */

    function item_modal_form() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $view_data['model_info'] = $this->Payment_voucher_items_model->get_one($this->input->post('id'));
        if (!$invoice_id) {
            $invoice_id = $view_data['model_info']->invoice_id;
        }
        $view_data['invoice_id'] = $invoice_id;
        $this->load->view("payment_vouchers/item_modal_form", $view_data);
    }

    /* add or edit an invoice item */

    function save_item() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric",
            "invoice_id" => "required|numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $id = $this->input->post('id');
        $rate = unformat_currency($this->input->post('invoice_item_rate'));
        $quantity = unformat_currency($this->input->post('invoice_item_quantity'));

        $invoice_item_data = array(
            "invoice_id" => $invoice_id,
            "title" => $this->input->post('invoice_item_title'),
            "description" => $this->input->post('invoice_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->input->post('invoice_unit_type'),
            "rate" => unformat_currency($this->input->post('invoice_item_rate')),
            "total" => $rate * $quantity,
        );

        $invoice_item_id = $this->Payment_voucher_items_model->save($invoice_item_data, $id);
        if ($invoice_item_id) {

            //check if the add_new_item flag is on, if so, add the item to libary. 
            $add_new_item_to_library = $this->input->post('add_new_item_to_library');
            if ($add_new_item_to_library) {
                $library_item_data = array(
                    "title" => $this->input->post('invoice_item_title'),
                    "description" => $this->input->post('invoice_item_description'),
                    "unit_type" => $this->input->post('invoice_unit_type'),
                    "rate" => unformat_currency($this->input->post('invoice_item_rate'))
                );
                $this->Items_model->save($library_item_data);
            }

            $options = array("id" => $invoice_item_id);
            $item_info = $this->Payment_voucher_items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_item_row($item_info), "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), 'id' => $invoice_item_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an invoice item */

    function delete_item() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Payment_voucher_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Payment_voucher_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_item_row($item_info), "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Payment_voucher_items_model->delete($id)) {
                $item_info = $this->Payment_voucher_items_model->get_one($id);
                echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of invoice items, prepared for datatable  */

    function item_list_data($invoice_id = 0) {
        if(!$this->cp('payment_vouchers', 'view_row')) {
            //redirect("forbidden");
        }

        $list_data = $this->Payment_voucher_items_model->get_details(array("invoice_id" => $invoice_id))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of invoice item list table */

    private function _make_item_row($data) {
        $move_icon = "";
        $desc_style = "";
        if ($this->can_edit_invoices()) {
            $move_icon = "<i class='fa fa-bars pull-left move-icon'></i>";
            $desc_style = "style='margin-left:25px'";
        }
        $item = "<div class='item-row strong mb5' data-id='$data->id'>$move_icon $data->title</div>";
        if ($data->description) {
            $item .= "<span $desc_style>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("payment_vouchers/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_payment_voucher'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("payment_vouchers/delete_item"), "data-action" => "delete"))
        );
    }

    //update the sort value for the item
    function update_item_sort_values($id = 0) {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        $sort_values = $this->input->post("sort_values");
        if ($sort_values) {

            //extract the values from the comma separated string
            $sort_array = explode(",", $sort_values);


            //update the value in db
            foreach ($sort_array as $value) {
                $sort_item = explode("-", $value); //extract id and sort value

                $id = get_array_value($sort_item, 0);
                $sort = get_array_value($sort_item, 1);

                $data = array("sort" => $sort);
                $this->Payment_voucher_items_model->save($data, $id);
            }
        }
    }

    /* prepare suggestion of invoice item */

    function get_invoice_item_suggestion() {
        $key = @$_GET["q"];
        $suggestion = array();

        $items = $this->Payment_voucher_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_invoice_item_info_suggestion() {
        $item = $this->Payment_voucher_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($invoice_id = 0, $show_close_preview = false) {
        if ($invoice_id) {
            $view_data = get_payment_voucher_making_data($invoice_id);

            $this->_check_invoice_access_permission($view_data);

            $view_data['invoice_preview'] = prepare_payment_voucher_pdf($view_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['invoice_id'] = $invoice_id;
            $view_data['payment_methods'] = $this->Payment_methods_model->get_available_online_payment_methods();

            $this->load->library("paypal");
            $view_data['paypal_url'] = $this->paypal->get_paypal_url();

            $this->load->library("paytm");
            $view_data['paytm_url'] = $this->paytm->get_paytm_url();

            $this->template->rander("payment_vouchers/invoice_preview", $view_data);
        } else {
            show_404();
        }
    }

    //print invoice
    function print_invoice($invoice_id = 0) {
        if ($invoice_id) {
            $view_data = get_payment_voucher_making_data($invoice_id);

            $this->_check_invoice_access_permission($view_data);

            $view_data['invoice_preview'] = prepare_payment_voucher_pdf($view_data, "html");

            echo json_encode(array("success" => true, "print_view" => $this->load->view("payment_vouchers/print_invoice", $view_data, true)));
        } else {
            echo json_encode(array("success" => false, lang('error_occurred')));
        }
    }

    function download_pdf($invoice_id = 0, $mode = "download") {
        if ($invoice_id) {
            $invoice_data = get_payment_voucher_making_data($invoice_id);
            $this->_check_invoice_access_permission($invoice_data);

            prepare_payment_voucher_pdf($invoice_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_invoice_access_permission($invoice_data) {
        //check for valid invoice
        if (!$invoice_data) {
            show_404();
        }

        //check for security
        $invoice_info = get_array_value($invoice_data, "invoice_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $invoice_info->client_id) {
                //redirect("forbidden");
            }
        } else {
            if (!$this->can_view_invoices()) {
                //redirect("forbidden");
            }
        }
    }

    function send_invoice_modal_form($invoice_id) {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if ($invoice_id) {
            $options = array("id" => $invoice_id);
            $invoice_info = $this->Payment_vouchers_model->get_details($options)->row();
            $view_data['invoice_info'] = $invoice_info;

            $contacts_options = array("user_type" => "client", "client_id" => $invoice_info->client_id);
            $contacts = $this->Users_model->get_details($contacts_options)->result();

            $primary_contact_info = "";
            $contacts_dropdown = array();
            foreach ($contacts as $contact) {
                if ($contact->is_primary_contact) {
                    $primary_contact_info = $contact;
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name . " (" . lang("primary_contact") . ")";
                }
            }

            $cc_contacts_dropdown = array();

            foreach ($contacts as $contact) {
                if (!$contact->is_primary_contact) {
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name;
                }

                $cc_contacts_dropdown[] = array("id" => $contact->id, "text" => $contact->first_name . " " . $contact->last_name);
            }

            $view_data['contacts_dropdown'] = $contacts_dropdown;
            $view_data['cc_contacts_dropdown'] = $cc_contacts_dropdown;

            $template_data = $this->get_send_invoice_template($invoice_id, 0, "", $invoice_info, $primary_contact_info);
            $view_data['message'] = get_array_value($template_data, "message");
            $view_data['subject'] = get_array_value($template_data, "subject");

            $this->load->view("payment_vouchers/send_invoice_modal_form", $view_data);
        } else {
            show_404();
        }
    }

    function get_send_invoice_template($invoice_id = 0, $contact_id = 0, $return_type = "", $invoice_info = "", $contact_info = "") {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if (!$invoice_info) {
            $options = array("id" => $invoice_id);
            $invoice_info = $this->Payment_vouchers_model->get_details($options)->row();
        }

        if (!$contact_info) {
            $contact_info = $this->Users_model->get_one($contact_id);
        }

        $email_template = $this->Email_templates_model->get_final_template("send_invoice");

        $invoice_total_summary = $this->Payment_vouchers_model->get_invoice_total_summary($invoice_id);

        $parser_data["INVOICE_ID"] = $invoice_info->id;
        $parser_data["CONTACT_FIRST_NAME"] = $contact_info->first_name;
        $parser_data["CONTACT_LAST_NAME"] = $contact_info->last_name;
        $parser_data["BALANCE_DUE"] = to_currency($invoice_total_summary->balance_due, $invoice_total_summary->currency_symbol);
        $parser_data["DUE_DATE"] = format_to_date($invoice_info->due_date, false);
        $parser_data["PROJECT_TITLE"] = $invoice_info->project_title;
        $parser_data["INVOICE_URL"] = get_uri("payment_vouchers/preview/" . $invoice_info->id);
        $parser_data['SIGNATURE'] = $email_template->signature;
        $parser_data["LOGO_URL"] = get_logo_url();

        //add public pay invoice url 
        if (get_setting("client_can_pay_invoice_without_login") && strpos($email_template->message, "PUBLIC_PAY_INVOICE_URL")) {
            $verification_data = array(
                "type" => "invoice_payment",
                "code" => make_random_string(),
                "params" => serialize(array(
                    "invoice_id" => $invoice_id,
                    "client_id" => $contact_info->client_id,
                    "contact_id" => $contact_info->id
                ))
            );

            $save_id = $this->Verification_model->save($verification_data);

            $verification_info = $this->Verification_model->get_one($save_id);

            $parser_data["PUBLIC_PAY_INVOICE_URL"] = get_uri("pay_invoice/index/" . $verification_info->code);
        }

        $message = $this->parser->parse_string($email_template->message, $parser_data, TRUE);
        $subject = $email_template->subject;

        if ($return_type == "json") {
            echo json_encode(array("success" => true, "message_view" => $message));
        } else {
            return array(
                "message" => $message,
                "subject" => $subject
            );
        }
    }

    function send_invoice() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $invoice_id = $this->input->post('id');

        $contact_id = $this->input->post('contact_id');

        $cc_array = array();
        $cc = $this->input->post('invoice_cc');

        if ($cc) {
            $cc = explode(',', $cc);

            foreach ($cc as $cc_value) {
                if (is_numeric($cc_value)) {
                    //selected a client contact
                    array_push($cc_array, $this->Users_model->get_one($cc_value)->email);
                } else {
                    //inputted an email address
                    array_push($cc_array, $cc_value);
                }
            }
        }

        $custom_bcc = $this->input->post('invoice_bcc');
        $subject = $this->input->post('subject');
        $message = decode_ajax_post_data($this->input->post('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $invoice_data = get_invoice_making_data($invoice_id);
        $attachement_url = prepare_invoice_pdf($invoice_data, "send_email");

        $default_bcc = get_setting('send_bcc_to'); //get default settings
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        //add uploaded files
        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "invoice");
        $attachments = prepare_attachment_of_files(get_setting("timeline_file_path"), $files_data);

        //add invoice pdf
        array_unshift($attachments, array("file_path" => $attachement_url));

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => $attachments, "cc" => $cc_array, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "not_paid", "last_email_sent_date" => get_my_local_time());
            if ($this->Payment_vouchers_model->save($status_data, $invoice_id)) {
                echo json_encode(array('success' => true, 'message' => lang("payment_voucher_sent_message"), "invoice_id" => $invoice_id));
            }

            // delete the temp invoice
            if (file_exists($attachement_url)) {
                unlink($attachement_url);
            }

            //delete attachments
            if ($files_data) {
                $files = unserialize($files_data);
                foreach ($files as $file) {
                    delete_app_files($target_path, array($file));
                }
            }
        } else {
            echo json_encode(array('success' => false, 'message' => lang('error_occurred')));
        }
    }

    function get_invoice_status_bar($invoice_id = 0) {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $view_data["invoice_info"] = $this->Payment_vouchers_model->get_details(array("id" => $invoice_id))->row();
       

//arr($view_data["invoice_info"]);
	   $view_data['invoice_status_label'] = $this->_get_invoice_status_label($view_data["invoice_info"]);
        $this->load->view("payment_vouchers/invoice_status_bar", $view_data);
    }

    function update_invoice_status($invoice_id = 0, $status = "") {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if ($invoice_id && $status) {
            //change the draft status of the invoice
            $this->Payment_vouchers_model->update_invoice_status($invoice_id, $status);

            //save extra information for cancellation
            if ($status == "cancelled") {
                $data = array(
                    "cancelled_at" => get_current_utc_time(),
                    "cancelled_by" => $this->login_user->id
                );

                $this->Payment_vouchers_model->save($data, $invoice_id);
            }

            echo json_encode(array("success" => true, 'message' => lang('record_saved')));
        }

        return "";
    }

    /* load discount modal */

    function discount_modal_form() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "invoice_id" => "required|numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $view_data['model_info'] = $this->Payment_vouchers_model->get_one($invoice_id);

        $this->load->view("payment_vouchers/discount_modal_form", $view_data);
    }

    /* save discount */

    function save_discount() {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "invoice_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Payment_vouchers_model->save($data, $invoice_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "invoice_total_view" => $this->_get_invoice_total_view($invoice_id), 'message' => lang('record_saved'), "invoice_id" => $invoice_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function load_statistics_of_selected_currency($currency = "") {
        if ($currency) {
            $statistics = invoice_statistics_widget(true, array("currency" => $currency));

            if ($statistics) {
                echo json_encode(array("success" => true, "statistics" => $statistics));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
        }
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for payment_vouchers */

    function validate_invoices_file() {
        return validate_post_file($this->input->post("file_name"));
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            $invoice_info = $this->Payment_vouchers_model->get_one($id);
            $files = unserialize($invoice_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

            $this->load->view("payment_vouchers/file_preview", $view_data);
        } else {
            show_404();
        }
    }
	

	

    //prepare options dropdown for payment_vouchers list
    private function _make_options_dropdown( $invoice_id = 0 ) {
		
		
		///if( !empty( $this->getRolePermission['edit_row'] ) ) {
			$buttons[] = '<li role="presentation">' . modal_anchor(get_uri("payment_vouchers/modal_form"), "<i class='fa fa-pencil'></i> " . lang('edit'), array("title" => lang('edit_payment_voucher'), "data-post-id" => $invoice_id)) . '</li>';
		//}
		
		//if( !empty( $this->getRolePermission['delete_row'] ) ) {
			$buttons[] = '<li role="presentation">' . js_anchor("<i class='fa fa-times fa-fw'></i>" . lang('delete'), array('title' => lang('delete_payment_voucher'), "class" => "delete", "data-id" => $invoice_id, "data-action-url" => get_uri("payment_vouchers/delete"), "data-action" => "delete-confirmation")) . '</li>';
		//}


        $buttons[] = '<li role="presentation">' . modal_anchor(get_uri("payment_voucher_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("title" => lang('add_payment'), "data-post-invoice_id" => $invoice_id)) . '</li>';

        return '
                <span class="dropdown inline-block">
                    <button class="btn btn-default dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class="fa fa-cogs"></i>&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">'. implode( '', $buttons ) .'</ul>
                </span>';
    }
	
    /* load invoice details view */

	
	


    function save() {
		
		// $post = $this->input->post();
		// arr( $post );
		// exit;
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric",
            "invoice_client_id" => "required|numeric",
            "invoice_bill_date" => "required",
            "invoice_due_date" => "required"
        ));

        $client_id = $this->input->post('invoice_client_id');
        $id = $this->input->post('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "invoice");
        $new_files = unserialize($files_data);

        $recurring = $this->input->post('recurring') ? 1 : 0;
        $bill_date = $this->input->post('invoice_bill_date');
        $repeat_every = $this->input->post('repeat_every');
        $repeat_type = $this->input->post('repeat_type');
        $no_of_cycles = $this->input->post('no_of_cycles');


        $invoice_data = array(
            "client_id" => $client_id,
            "project_id" => $this->input->post('invoice_project_id') ? $this->input->post('invoice_project_id') : 0,
            "bill_date" => $bill_date,
            "due_date" => $this->input->post('invoice_due_date'),
            "tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
            "tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
            "tax_id3" => $this->input->post('tax_id3') ? $this->input->post('tax_id3') : 0,
            "recurring" => $recurring,
            "repeat_every" => $repeat_every ? $repeat_every : 0,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => $no_of_cycles ? $no_of_cycles : 0,
            "note" => $this->input->post('invoice_note'),
            "project_name" => $this->input->post('project_name'),
            "labels" => $this->input->post('labels')
        );

        if ($id) {
            $invoice_info = $this->Payment_vouchers_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $invoice_info->files, $new_files);
        }

        $invoice_data["files"] = serialize($new_files);

        $is_clone = $this->input->post('is_clone');
        $estimate_id = $this->input->post('estimate_id');

        $main_invoice_id = "";
        if (($is_clone && $id ) || $estimate_id ) {
            if ($is_clone && $id) {
                $main_invoice_id = $id; //store main invoice id to get items later
                $id = ""; //one cloning invoice, save as new
            }

            // save discount when cloning and creating from estimate
            $invoice_data["discount_amount"] = $this->input->post('discount_amount') ? $this->input->post('discount_amount') : 0;
            $invoice_data["discount_amount_type"] = $this->input->post('discount_amount_type') ? $this->input->post('discount_amount_type') : "percentage";
            $invoice_data["discount_type"] = $this->input->post('discount_type') ? $this->input->post('discount_type') : "before_tax";
        }


        if ( $recurring ) {
            //set next recurring date for recurring payment_vouchers

            if ($id) {
                //update
                if ($this->input->post('next_recurring_date')) { //submitted any recurring date? set it.
                    $invoice_data['next_recurring_date'] = $this->input->post('next_recurring_date');
                } else {
                    //re-calculate the next recurring date, if any recurring fields has changed.
                    $invoice_info = $this->Payment_vouchers_model->get_one($id);
                    if ($invoice_info->recurring != $invoice_data['recurring'] || $invoice_info->repeat_every != $invoice_data['repeat_every'] || $invoice_info->repeat_type != $invoice_data['repeat_type'] || $invoice_info->bill_date != $invoice_data['bill_date']) {
                        $invoice_data['next_recurring_date'] = add_period_to_date($bill_date, $repeat_every, $repeat_type);
                    }
                }
            } else {
                //insert new
                $invoice_data['next_recurring_date'] = add_period_to_date($bill_date, $repeat_every, $repeat_type);
            }


            //recurring date must have to set a future date
            if (get_array_value($invoice_data, "next_recurring_date") && get_today_date() >= $invoice_data['next_recurring_date']) {
                echo json_encode(array("success" => false, 'message' => lang('past_recurring_date_error_message_title'), 'next_recurring_date_error' => lang('past_recurring_date_error_message'), "next_recurring_date_value" => $invoice_data['next_recurring_date']));
                return false;
            }
        }
		
		//arr( $_SESSION );
		//$invoice_data['created_by'] = $_SESSION['user_id']; 
        $invoice_id = $this->Payment_vouchers_model->save($invoice_data, $id);
		

		
        if ($invoice_id) {

            if ($is_clone && $main_invoice_id) {
                //add invoice items

                save_custom_fields("payment_vouchers", $invoice_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a invoice

                $invoice_items = $this->Payment_voucher_payments_model->get_all_where(array("payment_vouchers_id" => $main_invoice_id, "deleted" => 0))->result();

                foreach ($invoice_items as $invoice_item) {
                    //prepare new invoice item data
                    $invoice_item_data = (array) $invoice_item;
                    unset($invoice_item_data["id"]);
                    $invoice_item_data['payment_vouchers_id'] = $invoice_id;

                    // arr($invoice_item_data);
                    // exit;

                    $invoice_item = $this->Payment_voucher_payments_model->save($invoice_item_data);
                }
            } else {
                save_custom_fields("payment_vouchers", $invoice_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_estimate/copy_items_from_order? copy all items from the associated one
            $copy_items_from_estimate = $this->input->post("copy_items_from_estimate");
            $copy_items_from_order = $this->input->post("copy_items_from_order");
            $this->_copy_estimate_or_order_items_to_invoice( $copy_items_from_estimate, $copy_items_from_order, $invoice_id );

            // $post = $this->input->post();
            // arr( $post );
            // exit;

            echo json_encode(array("success" => true, "data" => $this->_row_data( $invoice_id ), 'id' => $invoice_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

	//
	//
    function __construct() {
		
        parent::__construct();
		$this->className = 'payment_vouchers';
		
		//$type = 'payment_vouchers';
       // $this->init_permission_checker( $type );
		
		$this->load->model( 'Db_model' );
		
		//
		//$param['table_name'] = $type;
		//$this->getRolePermission = $this->Db_model->getRolePermission( $param );
		//arr( $this->getRolePermission );
		//exit;
    }
	
 

	
    function index() {
		 
        $this->check_module_availability( "module_invoice" );

		$view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table( "payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type );



 
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();

//arr($this->getRolePermission);
//exit;
		$buttonTop = array();
		if( !empty( $this->getRolePermission['admin'] ) ) {
			$buttonTop[] = modal_anchor(get_uri("labels/modal_form"), "<i class='fa fa-tags'></i> " . lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => lang('manage_labels'), "data-post-type" => "payment_vouchers"));
		}
		/*
		if( !empty( $this->getRolePermission['edit_row'] ) ) {
			$buttonTop[] = modal_anchor(get_uri("payment_voucher_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("class" => "btn btn-default mb0", "title" => lang('add_payment'))); 
		}
		*/
		
		///if( !empty( $this->getRolePermission['add_row'] ) ) {
		
			$buttonTop[] = modal_anchor(get_uri("payment_vouchers/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment_voucher'), array("class" => "btn btn-default mb0", "title" => lang('add_payment_voucher')));
		
		///}
		
		$view_data["buttonTop"] = implode( '', $buttonTop );



		$view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

		$this->template->rander("payment_vouchers/index", $view_data );

		return;
	 
        if ( $this->login_user->user_type === "staff") {
			
            if ( !$this->can_view_invoices() ) {
                //redirect("forbidden");
            }

            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

            $this->template->rander("payment_vouchers/index", $view_data);
        } else {
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";
            $this->template->rander("clients/invoices/index", $view_data);
        }
    }
	
    function view( $invoice_id = 0 ) {
		
    
        if ( $invoice_id ) {
			

			$invoice_info = $this->Payment_vouchers_model->get_details( array( "id" => $invoice_id ) )->row();
			
	// arr( $invoice_info);exit;
			if ( $invoice_info ) {
			
				$data['invoice_info'] = $invoice_info;
				$data['client_info'] = $this->Clients_model->get_one($data['invoice_info']->client_id);
				
				
				
				$data['invoice_items'] = $this->Payment_voucher_payments_model->get_details( array("invoice_id" => $invoice_id ) )->result();
				
	//echo 'sdfasddfs';
				
			///	exit;			
				
				$data['invoice_status_label'] = get_invoice_status_label($invoice_info);
				//$data["invoice_total_summary"] = $this->Payment_vouchers_model->get_invoice_total_summary($invoice_id);
	
				
				$data['invoice_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "payment_vouchers", "show_in_invoice" => true, "related_to_id" => $invoice_id))->result();
				$data['client_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_invoice" => true, "related_to_id" => $data['invoice_info']->client_id))->result();
				
				$view_data = $data;
				
                $view_data['invoice_status'] = $this->_get_invoice_status_label($view_data["invoice_info"], false);
                $view_data["can_edit_invoices"] = $this->can_edit_invoices();

				$view_data['getRolePermission'] = $this->getRolePermission;



				$param['id'] = $invoice_id;
				$param['tbName'] = $this->className;
				$view_data["proveButton"] = $this->dao->getProveButton( $param );

                $this->template->rander( "payment_vouchers/view", $view_data );
			}
			else {
                show_404();
            }
        }
    }
	
    function list_data() {
		
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table( "payment_vouchers", $this->login_user->is_admin, $this->login_user->user_type );

        $options = array(
            "status" => $this->input->post( "status" ),
            "start_date" => $this->input->post( "start_date" ),
            "end_date" => $this->input->post( "end_date" ),
            "currency" => $this->input->post( "currency" ),
            "custom_fields" => $custom_fields
        );
		
		$options = array();
		
		//arr($this->getRolePermission);
		
	//	exit;

        $list_data = $this->Payment_vouchers_model->get_details( $options, $this->getRolePermission )->result();
        $result = array();
        // var_dump($list_data);exit;
        foreach ( $list_data as $data ) {
			
 
			//arr( );
            $result[] = $this->_make_row( $data, $custom_fields );
			
			
			//array( 'a', 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'H' , 'i' , 'j' , 'dd' );
			
			//$this->_make_row($data, $custom_fields);
        }

        echo json_encode( array( "data" => $result ) );
    }
	
    private function _make_row( $data, $custom_fields ) {
		
		//arr( $data );
        $invoice_url = "";
        if ($this->login_user->user_type == "staff") {
            $invoice_url = anchor( get_uri( "payment_vouchers/view/" . $data->id ), $data->doc_no );
        } else {
            $invoice_url = anchor(get_uri("payment_vouchers/preview/" . $data->id), $data->doc_no);
        }
		
		
		//$invoice_url = anchor( get_uri( "payment_vouchers/view/" . $data->id ), get_payment_voucher_id($data->id ) );
		
        $invoice_labels = make_labels_view_data( $data->labels_list, true, true);

        $row_data = array( 
			$invoice_url,
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $data->bill_date,
            format_to_date($data->bill_date, false),
            $data->due_date,
            format_to_date($data->due_date, false),
           
            
            $this->_get_invoice_status_label( $data ),
			$this->_make_options_dropdown( $data->id )
        );

        

		 

        return $row_data;
    }
	
	
	


    function modal_form() {
		
		$request = $this->input->post();
		
        $client_id = $this->input->post('client_id');
	
        $project_id = $this->input->post('project_id');
        $model_info = $this->Payment_vouchers_model->get_one($this->input->post('id'));


        $estimate_id = $this->input->post('estimate_id');
        $order_id = $this->input->post('order_id');

        validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));

        $client_id = $this->input->post('client_id');
	
        $project_id = $this->input->post('project_id');
        $model_info = $this->Payment_vouchers_model->get_one($this->input->post('id'));


    
        $estimate_id = $this->input->post('estimate_id');
        $order_id = $this->input->post('order_id');
		
        $view_data['estimate_id'] = $estimate_id;
        $view_data['order_id'] = $order_id;

        if ($estimate_id || $order_id ) {
			
            $info = null;
            if ($estimate_id) {
                $info = $this->Estimates_model->get_one($estimate_id);
            } else if ($order_id) {
                $info = $this->Orders_model->get_one($order_id);
            }

            if ($info) {
                $now = get_my_local_time("Y-m-d");
                $model_info->bill_date = $now;
                $model_info->due_date = $now;
                $model_info->client_id = $info->client_id;
                $model_info->tax_id = $info->tax_id;
                $model_info->tax_id2 = $info->tax_id2;
                $model_info->discount_amount = $info->discount_amount;
                $model_info->discount_amount_type = $info->discount_amount_type;
                $model_info->discount_type = $info->discount_type;
            }
        }

        //here has a project id. now set the client from the project
        if ($project_id) {
            $client_id = $this->Projects_model->get_one($project_id)->client_id;
            $model_info->client_id = $client_id;
        }


        $project_client_id = $client_id;
        if ($model_info->client_id) {
            $project_client_id = $model_info->client_id;
        }

        $view_data['model_info'] = $model_info;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = array("" => "-") + $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
		
        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $project_client_id));
		
        $suggestion = array(array("id" => "", "text" => "-"));
		
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        $view_data['projects_suggestion'] = $suggestion;

        $view_data['client_id'] = $client_id;
        $view_data['project_id'] = $project_id;


        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown("invoice", $model_info->labels);

        //clone invoice
        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;


        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("payment_vouchers", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->result();


        $this->load->view("payment_vouchers/modal_form", $view_data);
    }

    
	

	
 
	
	
}

/* End of file payment_vouchers.php */
/* Location: ./application/controllers/payment_vouchers.php */