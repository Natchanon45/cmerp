<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Invoices extends MY_Controller
{


    /* load invoice list view */


    //load the yearly view of invoice list
    function yearly()
    {
        $this->load->view("invoices/yearly_invoices");
    }

    //load the recurring view of invoice list
    function recurring()
    {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();
        $this->load->view("invoices/recurring_invoices_list", $view_data);
    }

    //load the custom view of invoice list
    function custom()
    {
        $this->load->view("invoices/custom_invoices_list");
    }

    /* load new invoice modal */


    /* prepare project dropdown based on this suggestion */

    function get_project_suggestion($client_id = 0)
    {
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

    private function _copy_estimate_or_order_items_to_invoice($copy_items_from_estimate, $copy_items_from_order, $invoice_id)
    {
        if (!$copy_items_from_estimate && !$copy_items_from_order) {
            return false;
        }

        $items = null;
        if ($copy_items_from_estimate) {
            $items = $this->Estimate_items_model->get_details(array("estimate_id" => $copy_items_from_estimate))->result();
        } else if ($copy_items_from_order) {
            $items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->result();
        }

        if (!$items) {
            return false;
        }

        foreach ($items as $data) {
            $invoice_item_data = array(
                "invoice_id" => $invoice_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
            );
            $this->Invoice_items_model->save($invoice_item_data);
        }
    }

    /* delete or undo an invoice */

    function delete()
    {
        if (!$this->can_edit_invoices()) {
            ///  redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');

        $invoice_info = $this->Invoices_model->get_one($id);

        if ($this->Invoices_model->delete($id)) {
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

    function invoice_list_data_of_client($client_id)
    {
        if (!$this->can_view_invoices($client_id)) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );

        //don't show draft invoices to client
        if ($this->login_user->user_type == "client") {
            $options["exclude_draft"] = true;
        }


        $list_data = $this->Invoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of invoice of a specific project, prepared for datatable  */

    function invoice_list_data_of_project($project_id)
    {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "project_id" => $project_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );
        $list_data = $this->Invoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* show sub invoices tab  */

    function sub_invoices($recurring_invoice_id)
    {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }
        $view_data["recurring_invoice_id"] = $recurring_invoice_id;
        $this->load->view("invoices/sub_invoices", $view_data);
    }

    /* list of sub invoices of a recurring invoice, prepared for datatable  */

    function sub_invoices_list_data($recurring_invoice_id)
    {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields,
            "recurring_invoice_id" => $recurring_invoice_id
        );

        $list_data = $this->Invoices_model->get_details($options)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* return a row of invoice list table */

    private function _row_data($id)
    {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Invoices_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }



    // list of recurring invoices, prepared for datatable
    function recurring_list_data()
    {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }


        $options = array(
            "recurring" => 1,
            "next_recurring_start_date" => $this->input->post("next_recurring_start_date"),
            "next_recurring_end_date" => $this->input->post("next_recurring_end_date"),
            "currency" => $this->input->post("currency")
        );

        $list_data = $this->Invoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_recurring_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of recurring invoice list table */

    private function _make_recurring_row($data)
    {

        $invoice_url = anchor(get_uri("invoices/view/" . $data->id), get_invoice_id($data->id));

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

    private function _get_invoice_total_view($invoice_id = 0)
    {
        $view_data["invoice_total_summary"] = $this->Invoices_model->get_invoice_total_summary($invoice_id);
        $view_data["invoice_id"] = $invoice_id;
        $view_data["can_edit_invoices"] = $this->can_edit_invoices();
        return $this->load->view('invoices/invoice_total_section', $view_data, true);
    }

    /* load item modal */

    function item_modal_form()
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $view_data['model_info'] = $this->Invoice_items_model->get_one($this->input->post('id'));
        if (!$invoice_id) {
            $invoice_id = $view_data['model_info']->invoice_id;
        }
        $view_data['invoice_id'] = $invoice_id;
        $this->load->view('invoices/item_modal_form', $view_data);
    }

    /* add or edit an invoice item */

    function save_item()
    {
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

        $invoice_item_id = $this->Invoice_items_model->save($invoice_item_data, $id);
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
            $item_info = $this->Invoice_items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_item_row($item_info), "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), 'id' => $invoice_item_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an invoice item */

    function delete_item()
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Invoice_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Invoice_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "data" => $this->_make_item_row($item_info), "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Invoice_items_model->delete($id)) {
                $item_info = $this->Invoice_items_model->get_one($id);
                echo json_encode(array("success" => true, "invoice_id" => $item_info->invoice_id, "invoice_total_view" => $this->_get_invoice_total_view($item_info->invoice_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of invoice items, prepared for datatable  */

    function item_list_data($invoice_id = 0)
    {
        if (!$this->can_view_invoices()) {
            // redirect("forbidden");
        }

        $list_data = $this->Invoice_items_model->get_details(array("invoice_id" => $invoice_id))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of invoice item list table */

    private function _make_item_row($data)
    {
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
            modal_anchor(get_uri("invoices/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_invoice'), "data-post-id" => $data->id))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("invoices/delete_item"), "data-action" => "delete"))
        );
    }

    //update the sort value for the item
    function update_item_sort_values($id = 0)
    {
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
                $this->Invoice_items_model->save($data, $id);
            }
        }
    }

    /* prepare suggestion of invoice item */

    function get_invoice_item_suggestion()
    {
        $key = @$_REQUEST["q"];
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_invoice_item_info_suggestion()
    {
        $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($invoice_id = 0, $show_close_preview = false)
    {

        show_404();

        if ($invoice_id) {
            $view_data = get_invoice_making_data($invoice_id);

            // $this->_check_invoice_access_permission($view_data);

            $view_data['invoice_preview'] = prepare_invoice_pdf($view_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['invoice_id'] = $invoice_id;
            $view_data['payment_methods'] = $this->Payment_methods_model->get_available_online_payment_methods();

            $this->load->library("paypal");
            $view_data['paypal_url'] = $this->paypal->get_paypal_url();

            $this->load->library("paytm");
            $view_data['paytm_url'] = $this->paytm->get_paytm_url();

            $this->template->rander("invoices/invoice_preview", $view_data);
        } else {
            // show_404();
        }
    }

    //print invoice
    function print_invoice($invoice_id = 0)
    {
        if ($invoice_id) {
            $view_data = get_invoice_making_data($invoice_id);

            $this->_check_invoice_access_permission($view_data);

            $view_data['invoice_preview'] = prepare_invoice_pdf($view_data, "html");

            echo json_encode(array("success" => true, "print_view" => $this->load->view("invoices/print_invoice", $view_data, true)));
        } else {
            echo json_encode(array("success" => false, lang('error_occurred')));
        }
    }

    function download_pdf($invoice_id = 0, $mode = "download")
    {
        if ($invoice_id) {
            $invoice_data = get_invoice_making_data($invoice_id);
            $this->_check_invoice_access_permission($invoice_data);

            prepare_invoice_pdf($invoice_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_invoice_access_permission($invoice_data)
    {
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

    function send_invoice_modal_form($invoice_id)
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if ($invoice_id) {
            $options = array("id" => $invoice_id);
            $invoice_info = $this->Invoices_model->get_details($options)->row();
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

            $this->load->view('invoices/send_invoice_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function get_send_invoice_template($invoice_id = 0, $contact_id = 0, $return_type = "", $invoice_info = "", $contact_info = "")
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if (!$invoice_info) {
            $options = array("id" => $invoice_id);
            $invoice_info = $this->Invoices_model->get_details($options)->row();
        }

        if (!$contact_info) {
            $contact_info = $this->Users_model->get_one($contact_id);
        }

        $email_template = $this->Email_templates_model->get_final_template("send_invoice");

        $invoice_total_summary = $this->Invoices_model->get_invoice_total_summary($invoice_id);

        $parser_data["INVOICE_ID"] = $invoice_info->id;
        $parser_data["CONTACT_FIRST_NAME"] = $contact_info->first_name;
        $parser_data["CONTACT_LAST_NAME"] = $contact_info->last_name;
        $parser_data["BALANCE_DUE"] = to_currency($invoice_total_summary->balance_due, $invoice_total_summary->currency_symbol);
        $parser_data["DUE_DATE"] = format_to_date($invoice_info->due_date, false);
        $parser_data["PROJECT_TITLE"] = $invoice_info->project_title;
        $parser_data["INVOICE_URL"] = get_uri("invoices/preview/" . $invoice_info->id);
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

    function send_invoice()
    {
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
            if ($this->Invoices_model->save($status_data, $invoice_id)) {
                echo json_encode(array('success' => true, 'message' => lang("invoice_sent_message"), "invoice_id" => $invoice_id));
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

    function get_invoice_status_bar($invoice_id = 0)
    {
        if (!$this->can_view_invoices()) {
            //redirect("forbidden");
        }

        $view_data["invoice_info"] = $this->Invoices_model->get_details(array("id" => $invoice_id))->row();
        $view_data['invoice_status_label'] = $this->_get_invoice_status_label($view_data["invoice_info"]);
        $this->load->view('invoices/invoice_status_bar', $view_data);
    }

    function update_invoice_status($invoice_id = 0, $status = "")
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        if ($invoice_id && $status) {
            //change the draft status of the invoice
            $this->Invoices_model->update_invoice_status($invoice_id, $status);

            //save extra information for cancellation
            if ($status == "cancelled") {
                $data = array(
                    "cancelled_at" => get_current_utc_time(),
                    "cancelled_by" => $this->login_user->id
                );

                $this->Invoices_model->save($data, $invoice_id);
            }

            echo json_encode(array("success" => true, 'message' => lang('record_saved')));
        }

        return "";
    }

    function pay_modal_form()
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "invoice_id" => "required|numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');
        $view_data["invoice_total_summary"] = $this->Invoices_model->get_invoice_total_summary($invoice_id);
        $view_data['model_info'] = $this->Invoices_model->get_one($invoice_id);

        $this->load->view('invoices/pay_modal_form', $view_data);
    }


    /* load discount modal */

    function discount_modal_form()
    {
        if (!$this->can_edit_invoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "invoice_id" => "required|numeric"
        ));

        $invoice_id = $this->input->post('invoice_id');

        $view_data['model_info'] = $this->Invoices_model->get_one($invoice_id);

        $this->load->view('invoices/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount()
    {
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

        $save_data = $this->Invoices_model->save($data, $invoice_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "invoice_total_view" => $this->_get_invoice_total_view($invoice_id), 'message' => lang('record_saved'), "invoice_id" => $invoice_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function load_statistics_of_selected_currency($currency = "")
    {
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

    function upload_file()
    {
        upload_file_to_temp();
    }

    /* check valid file for invoices */

    function validate_invoices_file()
    {
        return validate_post_file($this->input->post("file_name"));
    }

    function file_preview($id = "", $key = "")
    {
        if ($id) {
            $invoice_info = $this->Invoices_model->get_one($id);
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

            $this->load->view("invoices/file_preview", $view_data);
        } else {
            show_404();
        }
    }










    //prepare options dropdown for invoices list
    private function _make_options_dropdown($invoice_id = 0)
    {


        ///if( !empty( $this->getRolePermission['edit_row'] ) ) {
        $buttons[] = '<li role="presentation">' . modal_anchor(get_uri("invoices/modal_form"), "<i class='fa fa-pencil'></i> " . lang('edit'), array("title" => lang('edit_invoice'), "data-post-id" => $invoice_id)) . '</li>';
        //}

        //if( !empty( $this->getRolePermission['delete_row'] ) ) {
        $buttons[] = '<li role="presentation">' . js_anchor("<i class='fa fa-times fa-fw'></i>" . lang('delete'), array('title' => lang('delete_invoice'), "class" => "delete", "data-id" => $invoice_id, "data-action-url" => get_uri("invoices/delete"), "data-action" => "delete-confirmation")) . '</li>';
        //}


        $buttons[] = '<li role="presentation">' . modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("title" => lang('add_payment'), "data-post-invoice_id" => $invoice_id)) . '</li>';

        return '
                <span class="dropdown inline-block">
                    <button class="btn btn-default dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class="fa fa-cogs"></i>&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">' . implode('', $buttons) . '</ul>
                </span>';
    }

    /* load invoice details view */

    function view($invoice_id = 0)
    {


        if ($invoice_id) {




            $invoice_info = $this->Invoices_model->get_details(array("id" => $invoice_id))->row();

            if ($invoice_info) {
                $sql = "SELECT credit FROM estimates WHERE estimates.id = " . $invoice_info->es_id . "";
                $credit = $this->db->query($sql)->row();
                $data['invoice_info'] = $invoice_info;
                $data['client_info'] = $this->Clients_model->get_one($data['invoice_info']->client_id);
                $data['invoice_items'] = $this->Invoice_items_model->get_details(array("invoice_id" => $invoice_id))->result();
                $data['invoice_status_label'] = get_invoice_status_label($invoice_info);
                $data["invoice_total_summary"] = $this->Invoices_model->get_invoice_total_summary($invoice_id);
                $data['es_credit'] = $credit;
                $data['invoice_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "invoices", "show_in_invoice" => true, "related_to_id" => $invoice_id))->result();
                $data['client_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_invoice" => true, "related_to_id" => $data['invoice_info']->client_id))->result();

                $view_data = $data;

                $view_data['invoice_status'] = $this->_get_invoice_status_label($view_data["invoice_info"], false);
                $view_data["can_edit_invoices"] = $this->can_edit_invoices();

                $view_data['getRolePermission'] = $this->getRolePermission;

                $param['id'] = $invoice_id;
                $param['tbName'] = $this->className;
                $view_data["proveButton"] = $this->dao->getProveButton($param);

                $this->template->rander("" . $this->className . "/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    function index()
    {

        $this->check_module_availability("module_invoice");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data["can_edit_invoices"] = $this->can_edit_invoices();

        //arr($this->getRolePermission);
        //exit;
        $buttonTop = array();
        //if( !empty( $this->getRolePermission['admin'] ) ) {
        $buttonTop[] = modal_anchor(get_uri("labels/modal_form"), "<i class='fa fa-tags'></i> " . lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => lang('manage_labels'), "data-post-type" => $_SESSION['table_name']));
        //}

        //if( !empty( $this->getRolePermission['edit_row'] ) ) {
        $buttonTop[] = modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("class" => "btn btn-default mb0", "title" => lang('add_payment')));
        ///}


        ///if( !empty( $this->getRolePermission['add_row'] ) ) {

        $buttonTop[] = modal_anchor(get_uri("invoices/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_invoice'), array("class" => "btn btn-default mb0", "title" => lang('add_invoice')));

        ///}

        $view_data["buttonTop"] = implode('', $buttonTop);



        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

        $this->template->rander("invoices/index", $view_data);

        return;

        if ($this->login_user->user_type === "staff") {

            if (!$this->can_view_invoices()) {
                // redirect("forbidden");
            }

            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

            $this->template->rander("invoices/index", $view_data);
        } else {
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";
            $this->template->rander("clients/invoices/index", $view_data);
        }
    }

    function pay_split($inv_id = null)
    {

        $invoice_total = $this->Invoices_model->get_invoice_total_summary($inv_id);
        // var_dump($invoice_total->invoice_subtotal);
        $pay_sp = $_REQUEST['pay_sp'];
        $pay_type = $_REQUEST['pay_type'];

        if (!empty($invoice_total->tax_percentage)) {

            $vat = $invoice_total->tax_percentage / 100;
        } else if (!empty($invoice_total->tax_percentage2)) {
            $vat = $invoice_total->tax_percentage2 / 100;
        } else {
            $vat = 0;
        }


        $sql = "
			UPDATE
				invoices
			SET
				pay_sp = $pay_sp,
				pay_type = '" . $pay_type . "'
			WHERE invoices.id = $inv_id; ";
        //arr( $sql );
        $this->Db_model->execDatas($sql);
        if ($pay_type == "fixed_amount") {
            $vat_bath = $pay_sp * $vat;
            $after_vat = $pay_sp + $vat_bath;
            $new_data = array($vat_bath, $after_vat);
            // $invoice_total = $this->Invoices_model->get_invoice_total_summary($inv_id,$new_data);

            $datas = array("vat_bath" => number_format($vat_bath, 2, '.', ''), "after_vat" => number_format($after_vat, 2, '.', ''));
        } else if ($pay_type == "percentage") {

            $vat_bath = ($pay_sp / 100) * $vat * $invoice_total->invoice_subtotal;
            $after_vat = ($pay_sp / 100) * $invoice_total->invoice_subtotal + $vat_bath;
            $datas = array("vat_bath" => number_format($vat_bath, 2, '.', ''), "after_vat" => number_format($after_vat, 2, '.', ''));
        }


        echo json_encode($datas);
    }

    //
    //
    function __construct()
    {

        parent::__construct();


        $type = 'invoices';
        $this->init_permission_checker($type);
        $this->className = 'invoices';
        $this->load->model('Db_model');

        $_REQUEST = $this->input->post();

        foreach ($this->input->get() as $ka => $va) {
            $_REQUEST[$ka] = $va;
        }
        //
        //$param['table_name'] = $type;
        //$this->getRolePermission = $this->Db_model->getRolePermission( $param );
        //arr( $this->getRolePermission );
        //exit;
    }



    function list_data()
    {

        if (!$this->can_view_invoices()) {
            // redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "currency" => $this->input->post("currency"),
            "custom_fields" => $custom_fields
        );

        $options = array();

        //arr($this->getRolePermission);

        //	exit;

        $list_data = $this->Invoices_model->get_details($options, $this->getRolePermission)->result();

        // arr(  $list_data );

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    private function _make_row($data, $custom_fields)
    {

        $invoice_url = "";
        if ($this->login_user->user_type == "staff") {
            $invoice_url = anchor(get_uri("invoices/view/" . $data->id), $data->doc_no);
        } else {
            $invoice_url = anchor(get_uri("invoices/preview/" . $data->id), $data->doc_no);
        }

        $val1 = ($data->pay_spilter * $data->tax_percentage) / 100;
        $val2 = ($data->pay_spilter * $data->tax_percentage2) / 100;
        // var_dump($data);

        $deposit = $data->include_deposit == 1 ? $data->deposit : 0;
        if (!empty($data->tax_percentage)) {

            $ture_val = $data->pay_spilter + $val1 - $val2 ;
        } else if (!empty($data->tax_percentage2)) {
            $ture_val = $data->pay_spilter - $val1 + $val2;
        } else {
            $ture_val = $data->invoice_value;
        }

        $invoice_labels = make_labels_view_data($data->labels_list, true, true);
        // arr($data);

        $row_data = array(
            $invoice_url,
            anchor(get_uri("clients/view/" . $data->client_id), $data->company_name),
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $data->bill_date,
            format_to_date($data->bill_date, false),
            $data->due_date,
            format_to_date($data->due_date, false),
            to_decimal_format3($ture_val),
            to_decimal_format3($data->payment_received),
            !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB'),
            get_invoice_status_label($data, true)
        );
        //. $invoice_labels $data->currency_symbol
        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $row_data[] = $this->_make_options_dropdown($data->id);
        //$row_data[] = 'dfsaasdasdf';

        return $row_data;
    }

    //prepare invoice status label
    private function _get_invoice_status_label($data, $return_html = true)
    {
        return get_invoice_status_label($data, $return_html);
    }




    function save()
    {

        $created_by = $_SESSION['user_id'];
        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "invoice");
        $new_files = unserialize($files_data);

       



        $created_by = $_SESSION['user_id'];
        $es_id = $_REQUEST['es_item_title'];
        $inv_id = $_REQUEST['id'];

    


        $config['invoices'] = array('prefix' => 'BL');
        $param = $config['invoices'];
        $param['LPAD'] = 4;
        $param['column'] = 'doc_no';
        $param['table'] = 'invoices';
        $param['id'] = $inv_id;
        $data['doc_no'] = $this->dao->genDocNo($param);

        $is_clone = $this->input->post('is_clone');
        $estimate_id = $this->input->post('estimate_id');
        $main_invoice_id = "";

        if($inv_id){
            $sql = "UPDATE `invoices` SET `bill_date` = '".$_REQUEST['invoice_bill_date']."', `due_date` = ADDDATE( '".$_REQUEST['invoice_bill_date']."', INTERVAL 1 month ) WHERE `invoices`.`id` = ".$inv_id.";";
            $this->dao->execDatas($sql);

            echo json_encode(array("success" => true, "data" => $this->_row_data($inv_id), 'id' => $inv_id, 'message' => lang('record_saved')));
            exit;
        }
    
        if ($is_clone) {


            //echo 'dsfdsdsdf';
            $main_invoice_id = $inv_id;
            //var_dump($request);exit;
            $data['doc_no'] = $this->dao->genDocNo($param);
            $sql = "

                INSERT INTO invoices (
                    doc_no,
                    es_id,
                    project_id,
                    client_id,
                    bill_date,
                    due_date,
                    last_email_sent_date,
                    note, status, tax_id, tax_id2, discount_amount, discount_amount_type, discount_type,
                    cancelled_by,created_by,files,user_id,repeat_type

                )

                SELECT
                    '" . $data['doc_no'] . "' as doc_no,
                    '" . $es_id . "' as es_id,
                    project_id,
                    client_id,
                    es.estimate_date as bill_date,
                    es.valid_until as due_date,
                    last_email_sent_date,
                    note, status, tax_id, tax_id2,
                    discount_amount, discount_amount_type, discount_type,
                    0 as cancelled_by,
                    " . $created_by . " as created_by,
                    '" . serialize($new_files) . "' as files,
                    0 as user_id,
                    '" . $_REQUEST['repeat_type'] . "' as repeat_type

                FROM estimates es

                WHERE id = " . $es_id . "
            ";


            $this->dao->execDatas($sql);


            $inv_id = $this->db->insert_id();

            $this->Crud_model->insertLabels($inv_id, array(), $this->getRolePermission['table_name']);

            if ($is_clone && $main_invoice_id) {
                save_custom_fields("invoices", $inv_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a invoice
                $invoice_items = $this->Invoice_items_model->get_all_where(array("invoice_id" => $main_invoice_id, "deleted" => 0))->result();

                foreach ($invoice_items as $invoice_item) {
                    //prepare new invoice item data
                    $invoice_item_data = (array) $invoice_item;

                    unset($invoice_item_data["id"]);
                    $invoice_item_data['invoice_id'] = $inv_id;
                    // var_dump($invoice_item_data['invoice_id']);
                    $invoice_item = $this->Invoice_items_model->save($invoice_item_data);
                }
            } else {
                save_custom_fields("invoices", $inv_id, $this->login_user->is_admin, $this->login_user->user_type);
            }


            echo json_encode(array("success" => true, "data" => $this->_row_data($inv_id), 'id' => $inv_id, 'message' => lang('record_saved')));
        }       
        else {



            //   TRUNCATE TABLE invoices;   TRUNCATE TABLE invoice_items ; 



            $sql = "
				SELECT 				
                    estimates.*,
					ADDDATE( '" . $_REQUEST['invoice_bill_date'] . "', INTERVAL credit day ) as first_pay,
                    invoices.include_deposit as include_deposit
				FROM `estimates` 
                LEFT JOIN invoices ON invoices.es_id = estimates.id
				WHERE estimates.id = " . $es_id . "  
			";
            

            $estimates = $this->dao->fetch($sql);
            
            $inv_sql = "SELECT * FROM invoices WHERE invoices.project_id = '".$estimates->project_id."' ";
            $res_data = $this->dao->fetchAll($inv_sql);
            if($res_data){
                foreach($res_data as $k => $v){
                    if($v->project_id == $estimates->project_id && $v->client_id == $estimates->client_id){
                        $sql_check_project_id = "DELETE FROM invoices WHERE invoices.project_id = '" . $v->project_id . "' ";
                        $sql_check_item_id = "DELETE FROM invoice_items WHERE invoice_items.invoice_id = '" . $v->id . "' ";

                        // arr($sql_check_item_id);exit;
                        $this->dao->execDatas($sql_check_project_id);
                        $this->dao->execDatas($sql_check_item_id);
                    }
                    
                }
            }
            	
            if (!empty(json_decode($estimates->pay_sps))) {

                $rq_time = 0;
                foreach (json_decode($estimates->pay_sps) as $k => $v) {

                    if ($v->pay_types == 'percentage') {
                        $iWillPay[$k] = $v->pay_sps / 100 * $estimates->sub_total_estimate;
                        ++$rq_time;
                    } else {
                        $iWillPay[$k] = $v->pay_sps;
                        ++$rq_time;
                    }
                }

                
            } else {                
                
                if ($estimates->pay_type == 'time') {

                    $rq_time = !empty($estimates->pay_sp) ? $estimates->pay_sp : 1;


                    $estimate_total = $this->Estimates_model->get_estimate_total_summary($es_id);

                    $iWillPay[0] = ($estimates->sub_total_estimate) / $rq_time;
                } else if ($estimates->pay_type == 'percentage') {

                    $rq_time = ROUND(100 / $estimates->pay_sp);

                    //	exit;


                    $estimate_total = $this->Estimates_model->get_estimate_total_summary($es_id);

                    $iWillPay[0] = ($estimates->sub_total_estimate) * $estimates->pay_sp / 100;

                } else if ($estimates->pay_type == 'fixed_amount') {

                    $rq_time = !empty($estimates->pay_sp) ? $estimates->pay_sp : 1;
                    	

                    $estimate_total = $this->Estimates_model->get_estimate_total_summary($es_id);

                    $iWillPay[0] = $estimates->pay_sp;                    
                } else {
                    $rq_time = 1;


                    $estimate_total = $this->Estimates_model->get_estimate_total_summary($es_id);

                    $iWillPay[0] = $estimates->sub_total_estimate;
                }
                
            }

           
            if($estimates->deposit > 0 ){
                $sql_main = "
                INSERT INTO invoices (
                    include_deposit,                    
                    lock_parent_id,
                    pay_spilter,
                    doc_no,                        
                    es_id,                    
                    project_id,
                    client_id,
                    pay_sp,
                    pay_type,
                    pay_time,
                    bill_date,
                    due_date,
                    last_email_sent_date,
                    note, 
                    status, 
                    tax_id, 
                    tax_id2, 
                    discount_amount, 
                    discount_amount_type, 
                    discount_type,
                    cancelled_by,
                    created_by,
                    files,
                    user_id,
                    repeat_type
                )
                VALUES(
                    2,                    
                    '".$estimates->id."',
                    '".$estimates->deposit."',
                    '".$data['doc_no']."',
                    '".$estimates->id."',                    
                    '".$estimates->project_id."',
                    '".$estimates->client_id."',
                    0,
                    0,
                    1,
                    '".$estimates->estimate_date."',
                    ADDDATE( '".$estimates->first_pay."', INTERVAL 2 month ),
                    NULL,
                    '".$estimates->note."',
                    '".$estimates->status."',
                    '".$estimates->tax_id."',
                    '".$estimates->tax_id2."',
                    '".$estimates->discount_amount."', 
                    '".$estimates->discount_amount_type."', 
                    '".$estimates->discount_type."',
                    0,
                    " . $created_by . ",
                    '" . serialize($new_files) . "',
                    0,
                    '" . $_REQUEST['repeat_type'] . "'
                )
            ";

            

            $a = $this->dao->execDatas($sql_main);
            // var_dump($a);exit;

            $inv_id = $this->db->insert_id();
            
            $dfsaafdsd = $inv_id;

            


                $this->Crud_model->insertLabels($inv_id, array(), $this->getRolePermission['table_name']);

                $sql_delete = "
                    DELETE FROM invoice_items WHERE invoice_id = " . $inv_id . "
                ";
                $this->dao->execDatas($sql_delete);

                $titles = "รับมัดจำ";
                $description = "ใบเสนอราคาเลขที่ ".$estimates->doc_no." มูลค่า ".number_format($estimates->total_estimate,2,'.',',')." บาท";

                $sql_deposit = "INSERT INTO invoice_items 
                    (`title`, `description`, quantity,rate,`total`, `invoice_id`, `estimate_id`) 
                    VALUES ('".$titles."', '".$description."', 1,".$estimates->deposit.", ".$estimates->deposit.", ".$inv_id.", ".$estimates->id.")";

            $this->dao->execDatas($sql_deposit);
            
            }

            
            
            
            
            for ($s = 1; $s <= 9999; ++$s) {
                $stime = 0;
                if (!empty(json_decode($estimates->pay_sps))) {
                    $stime = $s;
                    if (!isset($iWillPay[$s])) {
                        break;
                    }
                }

                $deposit = 0;
                if($s == 1){
                    $deposit = 1;
                }
                $inv_id = NULL;
                $param = $config['invoices'];
                $param['LPAD'] = 4;
                $param['column'] = 'doc_no';
                $param['table'] = 'invoices';
                $param['id'] = $inv_id;
                $data['doc_no'] = $this->dao->genDocNo($param);

                $sql = "
					REPLACE INTO invoices (
                        include_deposit,
						pay_time,
						lock_parent_id,
						pay_spilter,
						doc_no,                        
						es_id,
						id,
						project_id,
						client_id,
						bill_date,
						due_date,
						last_email_sent_date,
						note, 
						status, 
						tax_id, 
						tax_id2, 
						discount_amount, 
						discount_amount_type, 
						discount_type,
						cancelled_by,created_by,files,user_id,repeat_type
					)
					SELECT
                        ".$deposit." as include_deposit,
						IFNULL( inv.last_pay_time, 0 ) + 1 as pay_time,
						'" . $es_id . "' as lock_parent_id,
						
						IF(
							" . $s . " = " . $rq_time . ", 
							es.sub_total_estimate - IFNULL( inv.total_inv, 0 ) ,
							IF( 
								es.sub_total_estimate - IFNULL( inv.total_inv, 0 ) > " . $iWillPay[$stime] . ", 
								" . $iWillPay[$stime] . ", 
								es.sub_total_estimate - IFNULL( inv.total_inv, 0 )  
							) 
						
						)  as pay_spilter,
						'" . $data['doc_no'] . "' as doc_no,
						'" . $es_id . "' as es_id,

						" . json_encode($inv_id) . " as inv_id,
						project_id,
						client_id,
						es.estimate_date as bill_date,
						
						ADDDATE( '" . $estimates->first_pay . "', INTERVAL " . ($s - 1) . " month ) as due_date,
						last_email_sent_date,
						note, status, tax_id, tax_id2,
						discount_amount, 
						discount_amount_type, 
						discount_type,
						0 as cancelled_by,
						" . $created_by . " as created_by,
						'" . serialize($new_files) . "' as files,
						0 as user_id,
						'" . $_REQUEST['repeat_type'] . "' as repeat_type

					FROM estimates es
					LEFT JOIN (
						SELECT
							MAX( pay_time ) as last_pay_time,
							lock_parent_id, 
							SUM( pay_spilter ) as total_inv
							 
						FROM invoices 
						WHERE deleted = 0
						GROUP BY 
							lock_parent_id
					
					) as inv ON es.id = inv.lock_parent_id
					WHERE es.id = " . $es_id . "
					HAVING pay_spilter > 0
				";
               

                //echo '<hr>';

                $this->dao->execDatas($sql);

                

                $inv_id = $this->db->insert_id();


                if (!$inv_id) {

                    break;
                }

                $dfsaafdsd = $inv_id;


                $this->Crud_model->insertLabels($inv_id, array(), $this->getRolePermission['table_name']);

                $sql = "
					DELETE FROM invoice_items WHERE invoice_id = " . $inv_id . "
				";
                $this->dao->execDatas($sql);

                $sql = "
					INSERT INTO invoice_items (
						quantity,
						rate,
						total,
						invoice_id,
						lock_dt_id,
						lock_parent_id,
						title,
						description,
						unit_type,
						sort,
						estimate_id
					)
					SELECT
						esItem.quantity as quantity,
						esItem.rate,
						esItem.total as total,
						" . $inv_id . " as invoice_id,
						esItem.id as lock_dt_id,
						esItem.estimate_id lock_parent_id,
						esItem.title,
						esItem.description,
						esItem.unit_type,
						esItem.sort,
						'" . $_REQUEST['es_item_title'] . "' as estimate_id
					FROM estimate_items esItem
					LEFT JOIN (
						SELECT
							invItem.lock_dt_id,
							sum( invItem.quantity ) as receive_qty,
							sum( invItem.total ) as total_amt
						FROM invoice_items invItem
						INNER JOIN invoices inv on invItem.invoice_id = inv.id
						WHERE invItem.deleted = 0
						AND inv.deleted = 0
						GROUP BY
							invItem.lock_dt_id
					) as new_tb ON esItem.id = new_tb.lock_dt_id
					WHERE esItem.estimate_id = '" . $_REQUEST['es_item_title'] . "'
					
					AND esItem.deleted = 0
					HAVING quantity > 0
				";

               
                
                $this->dao->execDatas($sql);
                
                
            }
            
            $id_s = isset($dfsaafdsd) ? $dfsaafdsd : $this->input->get('id');
            // var_dump($id_s);exit;

            echo json_encode(array("success" => true, "data" => $this->_row_data($id_s), 'id' => $id_s, 'message' => lang('record_saved')));



            // exit;
        }
    }

    function save_paySplit(){
        $newpay = $this->input->post();
        $es_id = $newpay['es_id'];
        $date = isset($newpay['invoice_bill_date']) ? $newpay['invoice_bill_date'] : date("Y-m-d") ;
        $pay = $newpay['paySpliter'];
        $invs_id = $newpay['invoice_id'];
        
        $config['invoices'] = array('prefix' => 'BL');
        $param = $config['invoices'];
                $param['LPAD'] = 4;
                $param['column'] = 'doc_no';
                $param['table'] = 'invoices';
                $param['id'] = NULL;
                $data['doc_no'] = $this->dao->genDocNo($param);

        $sql_new = "
					REPLACE INTO invoices (
                        include_deposit,
						pay_time,
						lock_parent_id,
						pay_spilter,
						doc_no,                        
						es_id,						
						project_id,
						client_id,
						bill_date,
						due_date,
						last_email_sent_date,
						note, 
						status, 
						tax_id, 
						tax_id2, 
						discount_amount, 
						discount_amount_type, 
						discount_type,
						cancelled_by,created_by,files,user_id,repeat_type
					)
					SELECT
                    include_deposit,
                    pay_time,
                    lock_parent_id,
                    ".$pay." as pay_spilter,
                    '".$data['doc_no']."' as doc_no ,                        
                    es_id,                   
                    project_id,
                    client_id,
                    bill_date,
                    (SELECT ADDDATE( max(`due_date`), INTERVAL 30 day ) FROM `invoices` WHERE `es_id` = inv.es_id ) as due_date,
                    last_email_sent_date,
                    note, 
                    status, 
                    tax_id, 
                    tax_id2, 
                    discount_amount, 
                    discount_amount_type, 
                    discount_type,
                    cancelled_by,created_by,files,user_id,repeat_type

					FROM invoices inv 
				
					WHERE inv.id = ".$invs_id." 
                    HAVING pay_spilter > 0
					
				";

                
                
                $this->dao->execDatas($sql_new);

                $inv_id = $this->db->insert_id();

                $sql_get = "
                    SELECT 				
                        invoices.*                        
                    FROM `invoices`
                    
                    WHERE invoices.id = " . $invs_id . "  
			    ";
                
               

                $invoices = $this->dao->fetch($sql_get);
                

                if ($inv_id) {                    

                    $sql_item = "
                        INSERT INTO invoice_items (
                            quantity,
                            rate,
                            total,
                            invoice_id,
                            lock_dt_id,
                            lock_parent_id,
                            title,
                            description,
                            unit_type,
                            sort,
                            estimate_id
                        )
                        SELECT
                            esItem.quantity as quantity,
                            esItem.rate,
                            esItem.total as total,
                            " . $inv_id . " as invoice_id,
                            esItem.id as lock_dt_id,
                            esItem.estimate_id lock_parent_id,
                            esItem.title,
                            esItem.description,
                            esItem.unit_type,
                            esItem.sort,
                            '" . $es_id . "' as estimate_id
                        FROM estimate_items esItem
                        LEFT JOIN (
                            SELECT
                                invItem.lock_dt_id,
                                sum( invItem.quantity ) as receive_qty,
                                sum( invItem.total ) as total_amt
                            FROM invoice_items invItem
                            INNER JOIN invoices inv on invItem.invoice_id = inv.id
                            WHERE invItem.deleted = 0
                            AND inv.deleted = 0
                            GROUP BY
                                invItem.lock_dt_id
                        ) as new_tb ON esItem.id = new_tb.lock_dt_id
                        WHERE esItem.estimate_id = '" . $es_id . "'
                        
                        AND esItem.deleted = 0
                        HAVING quantity > 0
                    ";

                    $this->dao->execDatas($sql_item);
                    
                }
                $newPaid = $invoices->pay_spilter - $pay;
                $sql_edit = "UPDATE `invoices` SET `pay_spilter` = ".$newPaid." WHERE `invoices`.`id` = ".$invs_id." ";
                
                $this->dao->execDatas($sql_edit);
                redirect(base_url('invoices/view/'.$invs_id));

    }


    function modal_form()
    {

        $request = $this->input->post();


        $client_id = $this->input->post('client_id');

        $project_id = $this->input->post('project_id');

        $model_info = $this->Invoices_model->get_one($this->input->post('id'));


        //check if estimate_id/order_id posted. if found, generate related information
        $estimate_id = $this->input->post('estimate_id');

        $order_id = $this->input->post('order_id');


        if (!$this->can_edit_invoices()) {
            // redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));

        $client_id = $this->input->post('client_id');

        $project_id = $this->input->post('project_id');
        $model_info = $this->Invoices_model->get_one($this->input->post('id'));


        //check if estimate_id/order_id posted. if found, generate related information
        $estimate_id = $this->input->post('estimate_id');
        $order_id = $this->input->post('order_id');




        $view_data['estimate_id'] = $estimate_id;
        $view_data['order_id'] = $order_id;

        if ($estimate_id || $order_id) {

            $info = null;
            if ($estimate_id) {
                $info = $this->Estimates_model->get_one($estimate_id);
            } else if ($order_id) {
                $info = $this->Orders_model->get_one($order_id);
            }

            //arr($info);
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

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id"); //array("client_id" => $project_client_id)

        $suggestion = array(array("id" => "", "text" => "-"));

        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        $view_data['projects_suggestion'] = $suggestion;

        //arr(   );

        $view_data['client_id'] = $client_id;

        $view_data['project_id'] = $project_id;
        if (!empty($info->project_id)) {
            $view_data['project'] = $projects[$info->project_id];
        }

        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;


        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("invoices", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->result();

        //arr($model_info ) ;
        //  $view_data['e'] = $estimate_id;

        //get_estimate_making_data( $estimate_id );
        if (isset($estimate_id)) {
            $view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id);
        }


        $options = array("percentage" => '%', "fixed_amount" => 'บาท', 'time' => 'งวด');

        $view_data["gogo"] = '
			
			
		';

        // arr(  $view_data["estimate_total_summary"] estimate_total);

        $this->load->view('invoices/modal_form', $view_data);
    }
}

/* End of file invoices.php */
/* Location: ./application/controllers/invoices.php */