<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Receipt_taxinvoices extends MY_Controller
{


    /* load receipt_taxinvoice list view */


    //load the yearly view of receipt_taxinvoice list
    function yearly()
    {
        $this->load->view("receipt_taxinvoices/yearly_receipt_taxinvoices");
    }

    //load the recurring view of receipt_taxinvoice list
    function recurring()
    {
        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
        $view_data["can_edit_receipt_taxinvoices"] = $this->can_edit_receipt_taxinvoices();
        $this->load->view("receipt_taxinvoices/recurring_receipt_taxinvoices_list", $view_data);
    }

    //load the custom view of receipt_taxinvoice list
    function custom()
    {
        $this->load->view("receipt_taxinvoices/custom_receipt_taxinvoices_list");
    }

    /* load new receipt_taxinvoice modal */


    /* prepare project dropdown based on this suggestion */

    function get_project_suggestion($client_id = 0)
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $client_id));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        echo json_encode($suggestion);
    }

    /* add or edit an receipt_taxinvoice */

    private function _copy_estimate_or_order_items_to_receipt_taxinvoice($copy_items_from_estimate, $copy_items_from_order, $receipt_taxinvoice_id)
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
            $receipt_taxinvoice_item_data = array(
                "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
            );
            $this->Receipt_taxinvoice_items_model->save($receipt_taxinvoice_item_data);
        }
    }

    /* delete or undo an receipt_taxinvoice */

    function delete()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            ///  redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');

        $receipt_taxinvoice_info = $this->Receipt_taxinvoices_model->get_one($id);

        if ($this->Receipt_taxinvoices_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($receipt_taxinvoice_info->files) {
                $files = unserialize($receipt_taxinvoice_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }



    /* list of receipt_taxinvoice of a specific client, prepared for datatable  */

    function receipt_taxinvoice_list_data_of_client($client_id)
    {
        if (!$this->can_view_receipt_taxinvoices($client_id)) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );

        //don't show draft receipt_taxinvoices to client
        if ($this->login_user->user_type == "client") {
            $options["exclude_draft"] = true;
        }


        $list_data = $this->Receipt_taxinvoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of receipt_taxinvoice of a specific project, prepared for datatable  */

    function receipt_taxinvoice_list_data_of_project($project_id)
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "project_id" => $project_id,
            "status" => $this->input->post("status"),
            "custom_fields" => $custom_fields
        );
        $list_data = $this->Receipt_taxinvoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* show sub receipt_taxinvoices tab  */

    function sub_receipt_taxinvoices($recurring_receipt_taxinvoice_id)
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            //redirect("forbidden");
        }
        $view_data["recurring_receipt_taxinvoice_id"] = $recurring_receipt_taxinvoice_id;
        $this->load->view("receipt_taxinvoices/sub_receipt_taxinvoices", $view_data);
    }

    /* list of sub receipt_taxinvoices of a recurring receipt_taxinvoice, prepared for datatable  */

    function sub_receipt_taxinvoices_list_data($recurring_receipt_taxinvoice_id)
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields,
            "recurring_receipt_taxinvoice_id" => $recurring_receipt_taxinvoice_id
        );

        $list_data = $this->Receipt_taxinvoices_model->get_details($options)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* return a row of receipt_taxinvoice list table */

    private function _row_data($id)
    {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Receipt_taxinvoices_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }



    // list of recurring receipt_taxinvoices, prepared for datatable
    function recurring_list_data()
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            //redirect("forbidden");
        }


        $options = array(
            "recurring" => 1,
            "next_recurring_start_date" => $this->input->post("next_recurring_start_date"),
            "next_recurring_end_date" => $this->input->post("next_recurring_end_date"),
            "currency" => $this->input->post("currency")
        );

        $list_data = $this->Receipt_taxinvoices_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_recurring_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of recurring receipt_taxinvoice list table */

    private function _make_recurring_row($data)
    {

        $receipt_taxinvoice_url = anchor(get_uri("receipt_taxinvoices/view/" . $data->id), get_receipt_taxinvoice_id($data->id));

        $cycles = $data->no_of_cycles_completed . "/" . $data->no_of_cycles;
        if (!$data->no_of_cycles) { //if not no of cycles, so it's infinity
            $cycles = $data->no_of_cycles_completed . "/&#8734;";
        }

        $status = "active";
        $receipt_taxinvoice_status_class = "label-success";
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
            $receipt_taxinvoice_status_class = "label-danger";
            $cycle_class = "text-danger";
        }

        return array(
            $receipt_taxinvoice_url,
            anchor(get_uri("clients/view/" . $data->client_id), $data->company_name),
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $next_recurring_date,
            $next_recurring,
            $data->repeat_every . " " . lang("interval_" . $data->repeat_type),
            "<span class='$cycle_class'>" . $cycles . "</span>",
            "<span class='label $receipt_taxinvoice_status_class large'>" . lang($status) . "</span>",
            to_currency($data->receipt_taxinvoice_value, $data->currency_symbol),
            $this->_make_options_dropdown($data->id)
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

    /* load item modal */

    function item_modal_form()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');

        $view_data['model_info'] = $this->Receipt_taxinvoice_items_model->get_one($this->input->post('id'));
        if (!$receipt_taxinvoice_id) {
            $receipt_taxinvoice_id = $view_data['model_info']->receipt_taxinvoice_id;
        }
        $view_data['receipt_taxinvoice_id'] = $receipt_taxinvoice_id;
        $this->load->view('receipt_taxinvoices/item_modal_form', $view_data);
    }

    /* add or edit an receipt_taxinvoice item */

    function save_item()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric",
            "receipt_taxinvoice_id" => "required|numeric"
        ));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');

        $id = $this->input->post('id');
        $rate = unformat_currency($this->input->post('receipt_taxinvoice_item_rate'));
        $quantity = unformat_currency($this->input->post('receipt_taxinvoice_item_quantity'));

        $receipt_taxinvoice_item_data = array(
            "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
            "title" => $this->input->post('receipt_taxinvoice_item_title'),
            "description" => $this->input->post('receipt_taxinvoice_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->input->post('receipt_taxinvoice_unit_type'),
            "rate" => unformat_currency($this->input->post('receipt_taxinvoice_item_rate')),
            "total" => $rate * $quantity,
        );

        $receipt_taxinvoice_item_id = $this->Receipt_taxinvoice_items_model->save($receipt_taxinvoice_item_data, $id);
        if ($receipt_taxinvoice_item_id) {

            //check if the add_new_item flag is on, if so, add the item to libary.
            $add_new_item_to_library = $this->input->post('add_new_item_to_library');
            if ($add_new_item_to_library) {
                $library_item_data = array(
                    "title" => $this->input->post('receipt_taxinvoice_item_title'),
                    "description" => $this->input->post('receipt_taxinvoice_item_description'),
                    "unit_type" => $this->input->post('receipt_taxinvoice_unit_type'),
                    "rate" => unformat_currency($this->input->post('receipt_taxinvoice_item_rate'))
                );
                $this->Items_model->save($library_item_data);
            }

            $options = array("id" => $receipt_taxinvoice_item_id);
            $item_info = $this->Receipt_taxinvoice_items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "data" => $this->_make_item_row($item_info), "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), 'id' => $receipt_taxinvoice_item_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an receipt_taxinvoice item */

    function delete_item()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Receipt_taxinvoice_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Receipt_taxinvoice_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "data" => $this->_make_item_row($item_info), "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Receipt_taxinvoice_items_model->delete($id)) {
                $item_info = $this->Receipt_taxinvoice_items_model->get_one($id);
                echo json_encode(array("success" => true, "receipt_taxinvoice_id" => $item_info->receipt_taxinvoice_id, "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($item_info->receipt_taxinvoice_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of receipt_taxinvoice items, prepared for datatable  */

    function item_list_data($receipt_taxinvoice_id = 0)
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            // redirect("forbidden");
        }

        $list_data = $this->Receipt_taxinvoice_items_model->get_details(array("receipt_taxinvoice_id" => $receipt_taxinvoice_id))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of receipt_taxinvoice item list table */

    private function _make_item_row($data)
    {
        $move_icon = "";
        $desc_style = "";
        if ($this->can_edit_receipt_taxinvoices()) {
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
            modal_anchor(get_uri("receipt_taxinvoices/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_receipt_taxinvoice'), "data-post-id" => $data->id))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("receipt_taxinvoices/delete_item"), "data-action" => "delete"))
        );
    }

    //update the sort value for the item
    function update_item_sort_values($id = 0)
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
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
                $this->Receipt_taxinvoice_items_model->save($data, $id);
            }
        }
    }

    /* prepare suggestion of receipt_taxinvoice item */

    function get_receipt_taxinvoice_item_suggestion()
    {
        $key = @$_REQUEST["q"];
        $suggestion = array();

        $items = $this->Receipt_taxinvoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_receipt_taxinvoice_item_info_suggestion()
    {
        $item = $this->Receipt_taxinvoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($receipt_taxinvoice_id = 0, $show_close_preview = false)
    {

        show_404();

        if ($receipt_taxinvoice_id) {
            $view_data = get_receipt_taxinvoice_making_data($receipt_taxinvoice_id);

            // $this->_check_receipt_taxinvoice_access_permission($view_data);

            $view_data['receipt_taxinvoice_preview'] = prepare_receipt_taxinvoice_pdf($view_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['receipt_taxinvoice_id'] = $receipt_taxinvoice_id;
            $view_data['payment_methods'] = $this->Payment_methods_model->get_available_online_payment_methods();

            $this->load->library("paypal");
            $view_data['paypal_url'] = $this->paypal->get_paypal_url();

            $this->load->library("paytm");
            $view_data['paytm_url'] = $this->paytm->get_paytm_url();

            $this->template->rander("receipt_taxinvoices/receipt_taxinvoice_preview", $view_data);
        } else {
            // show_404();
        }
    }

    //print receipt_taxinvoice
    function print_receipt_taxinvoice($receipt_taxinvoice_id = 0)
    {
        if ($receipt_taxinvoice_id) {
            $view_data = get_receipt_taxinvoice_making_data($receipt_taxinvoice_id);

            $this->_check_receipt_taxinvoice_access_permission($view_data);

            $view_data['receipt_taxinvoice_preview'] = prepare_receipt_taxinvoice_pdf($view_data, "html");

            echo json_encode(array("success" => true, "print_view" => $this->load->view("receipt_taxinvoices/print_receipt_taxinvoice", $view_data, true)));
        } else {
            echo json_encode(array("success" => false, lang('error_occurred')));
        }
    }

    function download_pdf($receipt_taxinvoice_id = 0, $mode = "download")
    {
        if ($receipt_taxinvoice_id) {
            $receipt_taxinvoice_data = get_receipt_taxinvoice_making_data($receipt_taxinvoice_id);
            $this->_check_receipt_taxinvoice_access_permission($receipt_taxinvoice_data);

            prepare_receipt_taxinvoice_pdf($receipt_taxinvoice_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_receipt_taxinvoice_access_permission($receipt_taxinvoice_data)
    {
        //check for valid receipt_taxinvoice
        if (!$receipt_taxinvoice_data) {
            show_404();
        }

        //check for security
        $receipt_taxinvoice_info = get_array_value($receipt_taxinvoice_data, "receipt_taxinvoice_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $receipt_taxinvoice_info->client_id) {
                //redirect("forbidden");
            }
        } else {
            if (!$this->can_view_receipt_taxinvoices()) {
                //redirect("forbidden");
            }
        }
    }

    function send_receipt_taxinvoice_modal_form($receipt_taxinvoice_id)
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        if ($receipt_taxinvoice_id) {
            $options = array("id" => $receipt_taxinvoice_id);
            $receipt_taxinvoice_info = $this->Receipt_taxinvoices_model->get_details($options)->row();
            $view_data['receipt_taxinvoice_info'] = $receipt_taxinvoice_info;

            $contacts_options = array("user_type" => "client", "client_id" => $receipt_taxinvoice_info->client_id);
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

            $template_data = $this->get_send_receipt_taxinvoice_template($receipt_taxinvoice_id, 0, "", $receipt_taxinvoice_info, $primary_contact_info);
            $view_data['message'] = get_array_value($template_data, "message");
            $view_data['subject'] = get_array_value($template_data, "subject");

            $this->load->view('receipt_taxinvoices/send_receipt_taxinvoice_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function get_send_receipt_taxinvoice_template($receipt_taxinvoice_id = 0, $contact_id = 0, $return_type = "", $receipt_taxinvoice_info = "", $contact_info = "")
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        if (!$receipt_taxinvoice_info) {
            $options = array("id" => $receipt_taxinvoice_id);
            $receipt_taxinvoice_info = $this->Receipt_taxinvoices_model->get_details($options)->row();
        }

        if (!$contact_info) {
            $contact_info = $this->Users_model->get_one($contact_id);
        }

        $email_template = $this->Email_templates_model->get_final_template("send_receipt_taxinvoice");

        $receipt_taxinvoice_total_summary = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($receipt_taxinvoice_id);

        $parser_data["INVOICE_ID"] = $receipt_taxinvoice_info->id;
        $parser_data["CONTACT_FIRST_NAME"] = $contact_info->first_name;
        $parser_data["CONTACT_LAST_NAME"] = $contact_info->last_name;
        $parser_data["BALANCE_DUE"] = to_currency($receipt_taxinvoice_total_summary->balance_due, $receipt_taxinvoice_total_summary->currency_symbol);
        $parser_data["DUE_DATE"] = format_to_date($receipt_taxinvoice_info->due_date, false);
        $parser_data["PROJECT_TITLE"] = $receipt_taxinvoice_info->project_title;
        $parser_data["INVOICE_URL"] = get_uri("receipt_taxinvoices/preview/" . $receipt_taxinvoice_info->id);
        $parser_data['SIGNATURE'] = $email_template->signature;
        $parser_data["LOGO_URL"] = get_logo_url();

        //add public pay receipt_taxinvoice url
        if (get_setting("client_can_pay_receipt_taxinvoice_without_login") && strpos($email_template->message, "PUBLIC_PAY_INVOICE_URL")) {
            $verification_data = array(
                "type" => "receipt_taxinvoice_payment",
                "code" => make_random_string(),
                "params" => serialize(array(
                    "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
                    "client_id" => $contact_info->client_id,
                    "contact_id" => $contact_info->id
                ))
            );

            $save_id = $this->Verification_model->save($verification_data);

            $verification_info = $this->Verification_model->get_one($save_id);

            $parser_data["PUBLIC_PAY_INVOICE_URL"] = get_uri("pay_receipt_taxinvoice/index/" . $verification_info->code);
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

    function send_receipt_taxinvoice()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $receipt_taxinvoice_id = $this->input->post('id');

        $contact_id = $this->input->post('contact_id');

        $cc_array = array();
        $cc = $this->input->post('receipt_taxinvoice_cc');

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

        $custom_bcc = $this->input->post('receipt_taxinvoice_bcc');
        $subject = $this->input->post('subject');
        $message = decode_ajax_post_data($this->input->post('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $receipt_taxinvoice_data = get_receipt_taxinvoice_making_data($receipt_taxinvoice_id);
        $attachement_url = prepare_receipt_taxinvoice_pdf($receipt_taxinvoice_data, "send_email");

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
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "receipt_taxinvoice");
        $attachments = prepare_attachment_of_files(get_setting("timeline_file_path"), $files_data);

        //add receipt_taxinvoice pdf
        array_unshift($attachments, array("file_path" => $attachement_url));

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => $attachments, "cc" => $cc_array, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "not_paid", "last_email_sent_date" => get_my_local_time());
            if ($this->Receipt_taxinvoices_model->save($status_data, $receipt_taxinvoice_id)) {
                echo json_encode(array('success' => true, 'message' => lang("receipt_taxinvoice_sent_message"), "receipt_taxinvoice_id" => $receipt_taxinvoice_id));
            }

            // delete the temp receipt_taxinvoice
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

    function get_receipt_taxinvoice_status_bar($receipt_taxinvoice_id = 0)
    {
        if (!$this->can_view_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        $view_data["receipt_taxinvoice_info"] = $this->Receipt_taxinvoices_model->get_details(array("id" => $receipt_taxinvoice_id))->row();
        $view_data['receipt_taxinvoice_status_label'] = $this->_get_receipt_taxinvoice_status_label($view_data["receipt_taxinvoice_info"]);
        $this->load->view('receipt_taxinvoices/receipt_taxinvoice_status_bar', $view_data);
    }

    function update_receipt_taxinvoice_status($receipt_taxinvoice_id = 0, $status = "")
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        if ($receipt_taxinvoice_id && $status) {
            //change the draft status of the receipt_taxinvoice
            $this->Receipt_taxinvoices_model->update_receipt_taxinvoice_status($receipt_taxinvoice_id, $status);

            //save extra information for cancellation
            if ($status == "cancelled") {
                $data = array(
                    "cancelled_at" => get_current_utc_time(),
                    "cancelled_by" => $this->login_user->id
                );

                $this->Receipt_taxinvoices_model->save($data, $receipt_taxinvoice_id);
            }

            echo json_encode(array("success" => true, 'message' => lang('record_saved')));
        }

        return "";
    }

    function pay_modal_form()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "receipt_taxinvoice_id" => "required|numeric"
        ));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');
        $view_data["receipt_taxinvoice_total_summary"] = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($receipt_taxinvoice_id);
        $view_data['model_info'] = $this->Receipt_taxinvoices_model->get_one($receipt_taxinvoice_id);

        $this->load->view('receipt_taxinvoices/pay_modal_form', $view_data);
    }


    /* load discount modal */

    function discount_modal_form()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "receipt_taxinvoice_id" => "required|numeric"
        ));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');

        $view_data['model_info'] = $this->Receipt_taxinvoices_model->get_one($receipt_taxinvoice_id);

        $this->load->view('receipt_taxinvoices/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount()
    {
        if (!$this->can_edit_receipt_taxinvoices()) {
            //redirect("forbidden");
        }

        validate_submitted_data(array(
            "receipt_taxinvoice_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $receipt_taxinvoice_id = $this->input->post('receipt_taxinvoice_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Receipt_taxinvoices_model->save($data, $receipt_taxinvoice_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "receipt_taxinvoice_total_view" => $this->_get_receipt_taxinvoice_total_view($receipt_taxinvoice_id), 'message' => lang('record_saved'), "receipt_taxinvoice_id" => $receipt_taxinvoice_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function load_statistics_of_selected_currency($currency = "")
    {
        if ($currency) {
            $statistics = receipt_taxinvoice_statistics_widget(true, array("currency" => $currency));

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

    /* check valid file for receipt_taxinvoices */

    function validate_receipt_taxinvoices_file()
    {
        return validate_post_file($this->input->post("file_name"));
    }

    function file_preview($id = "", $key = "")
    {
        if ($id) {
            $receipt_taxinvoice_info = $this->Receipt_taxinvoices_model->get_one($id);
            $files = unserialize($receipt_taxinvoice_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

            $this->load->view("receipt_taxinvoices/file_preview", $view_data);
        } else {
            show_404();
        }
    }










    //prepare options dropdown for receipt_taxinvoices list
    private function _make_options_dropdown($receipt_taxinvoice_id = 0)
    {


        ///if( !empty( $this->getRolePermission['edit_row'] ) ) {
        $buttons[] = '<li role="presentation">' . modal_anchor(get_uri("receipt_taxinvoices/modal_form"), "<i class='fa fa-pencil'></i> " . lang('edit'), array("title" => lang('edit_receipt_taxinvoice'), "data-post-id" => $receipt_taxinvoice_id)) . '</li>';
        //}

        //if( !empty( $this->getRolePermission['delete_row'] ) ) {
        $buttons[] = '<li role="presentation">' . js_anchor("<i class='fa fa-times fa-fw'></i>" . lang('delete'), array('title' => lang('delete_receipt_taxinvoice'), "class" => "delete", "data-id" => $receipt_taxinvoice_id, "data-action-url" => get_uri("receipt_taxinvoices/delete"), "data-action" => "delete-confirmation")) . '</li>';
        //}


        $buttons[] = '<li role="presentation">' . modal_anchor(get_uri("receipt_taxinvoice_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("title" => lang('add_payment'), "data-post-receipt_taxinvoice_id" => $receipt_taxinvoice_id)) . '</li>';

        return '
                <span class="dropdown inline-block">
                    <button class="btn btn-default dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                        <i class="fa fa-cogs"></i>&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">' . implode('', $buttons) . '</ul>
                </span>';
    }

    /* load receipt_taxinvoice details view */

    function view($receipt_taxinvoice_id = 0)
    {


        if ($receipt_taxinvoice_id) {




            $receipt_taxinvoice_info = $this->Receipt_taxinvoices_model->get_details(array("id" => $receipt_taxinvoice_id))->row();

            if ($receipt_taxinvoice_info) {
                $sql = "SELECT credit FROM estimates WHERE estimates.id = " . $receipt_taxinvoice_info->es_id . "";
                $credit = $this->db->query($sql)->row();
                $data['receipt_taxinvoice_info'] = $receipt_taxinvoice_info;
                $data['client_info'] = $this->Clients_model->get_one($data['receipt_taxinvoice_info']->client_id);
                $data['receipt_taxinvoice_items'] = $this->Receipt_taxinvoice_items_model->get_details(array("receipt_taxinvoice_id" => $receipt_taxinvoice_id))->result();
                $data['receipt_taxinvoice_status_label'] = get_receipt_taxinvoice_status_label($receipt_taxinvoice_info);
                $data["receipt_taxinvoice_total_summary"] = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($receipt_taxinvoice_id);
                $data['es_credit'] = $credit;
                $data['receipt_taxinvoice_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "receipt_taxinvoices", "show_in_receipt_taxinvoice" => true, "related_to_id" => $receipt_taxinvoice_id))->result();
                $data['client_info']->custom_fields = $this->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_receipt_taxinvoice" => true, "related_to_id" => $data['receipt_taxinvoice_info']->client_id))->result();

                $view_data = $data;

                $view_data['receipt_taxinvoice_status'] = $this->_get_receipt_taxinvoice_status_label($view_data["receipt_taxinvoice_info"], false);
                $view_data["can_edit_receipt_taxinvoices"] = $this->can_edit_receipt_taxinvoices();

                $view_data['getRolePermission'] = $this->getRolePermission;

                $param['id'] = $receipt_taxinvoice_id;
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

        $this->check_module_availability("module_receipt_taxinvoice");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data["can_edit_receipt_taxinvoices"] = $this->can_edit_receipt_taxinvoices();

        //arr($this->getRolePermission);
        //exit;
        $buttonTop = array();
        //if( !empty( $this->getRolePermission['admin'] ) ) {
        $buttonTop[] = modal_anchor(get_uri("labels/modal_form"), "<i class='fa fa-tags'></i> " . lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => lang('manage_labels'), "data-post-type" => $_SESSION['table_name']));
        //}

        //if( !empty( $this->getRolePermission['edit_row'] ) ) {
        //$buttonTop[] = modal_anchor(get_uri("receipt_taxinvoice_payments/payment_modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_payment'), array("class" => "btn btn-default mb0", "title" => lang('add_payment')));
        ///}


        ///if( !empty( $this->getRolePermission['add_row'] ) ) {

        $buttonTop[] = modal_anchor(get_uri("receipt_taxinvoices/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_receipt_taxinvoice'), array("class" => "btn btn-default mb0", "title" => lang('add_receipt_taxinvoice')));

        ///}

        $view_data["buttonTop"] = implode('', $buttonTop);



        $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

        $this->template->rander("receipt_taxinvoices/index", $view_data);

        return;

        if ($this->login_user->user_type === "staff") {

            if (!$this->can_view_receipt_taxinvoices()) {
                // redirect("forbidden");
            }

            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();

            $this->template->rander("receipt_taxinvoices/index", $view_data);
        } else {
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";
            $this->template->rander("clients/receipt_taxinvoices/index", $view_data);
        }
    }

    function pay_split($inv_id = null)
    {

        $receipt_taxinvoice_total = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($inv_id);
        // var_dump($receipt_taxinvoice_total->receipt_taxinvoice_subtotal);
        $pay_sp = $_REQUEST['pay_sp'];
        $pay_type = $_REQUEST['pay_type'];

        if (!empty($receipt_taxinvoice_total->tax_percentage)) {

            $vat = $receipt_taxinvoice_total->tax_percentage / 100;
        } else if (!empty($receipt_taxinvoice_total->tax_percentage2)) {
            $vat = $receipt_taxinvoice_total->tax_percentage2 / 100;
        } else {
            $vat = 0;
        }


        $sql = "
			UPDATE
				receipt_taxinvoices
			SET
				pay_sp = $pay_sp,
				pay_type = '" . $pay_type . "'
			WHERE receipt_taxinvoices.id = $inv_id; ";
        //arr( $sql );
        $this->Db_model->execDatas($sql);
        if ($pay_type == "fixed_amount") {
            $vat_bath = $pay_sp * $vat;
            $after_vat = $pay_sp + $vat_bath;
            $new_data = array($vat_bath, $after_vat);
            // $receipt_taxinvoice_total = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($inv_id,$new_data);

            $datas = array("vat_bath" => number_format($vat_bath, 2, '.', ''), "after_vat" => number_format($after_vat, 2, '.', ''));
        } else if ($pay_type == "percentage") {

            $vat_bath = ($pay_sp / 100) * $vat * $receipt_taxinvoice_total->receipt_taxinvoice_subtotal;
            $after_vat = ($pay_sp / 100) * $receipt_taxinvoice_total->receipt_taxinvoice_subtotal + $vat_bath;
            $datas = array("vat_bath" => number_format($vat_bath, 2, '.', ''), "after_vat" => number_format($after_vat, 2, '.', ''));
        }


        echo json_encode($datas);
    }

    //
    //
    function __construct()
    {

        parent::__construct();


        $type = 'receipt_taxinvoices';
        $this->init_permission_checker($type);
        $this->className = 'receipt_taxinvoices';
        $this->load->model('Db_model');
        $this->load->model('Receipt_taxinvoices_model');
        $this->load->model('Receipt_taxinvoice_items_model');
        $this->load->model('Receipt_taxinvoice_payments_model');

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

        if (!$this->can_view_receipt_taxinvoices()) {
            // redirect("forbidden");
        }

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipt_taxinvoices", $this->login_user->is_admin, $this->login_user->user_type);

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

        $list_data = $this->Receipt_taxinvoices_model->get_details($options, $this->getRolePermission)->result();

        // arr(  $list_data );

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    private function _make_row($data, $custom_fields)
    {

        $receipt_taxinvoice_url = "";
        if ($this->login_user->user_type == "staff") {
            $receipt_taxinvoice_url = anchor(get_uri("receipt_taxinvoices/view/" . $data->id), $data->doc_no);
        } else {
            $receipt_taxinvoice_url = anchor(get_uri("receipt_taxinvoices/preview/" . $data->id), $data->doc_no);
        }

        $val1 = ($data->pay_spilter * $data->tax_percentage) / 100;
        $val2 = ($data->pay_spilter * $data->tax_percentage2) / 100;
        // var_dump($data);

        //$deposit = $data->include_deposit == 1 ? $data->deposit : 0;
        if (!empty($data->tax_percentage)) {

            $ture_val = $data->pay_spilter + $val1 - $val2;
        } else if (!empty($data->tax_percentage2)) {
            $ture_val = $data->pay_spilter - $val1 + $val2;
        } else {
            $ture_val = $data->receipt_taxinvoice_value;
        }

        $receipt_taxinvoice_labels = make_labels_view_data($data->labels_list, true, true);
        // arr($data);

        $row_data = array(
            $receipt_taxinvoice_url,
            anchor(get_uri("clients/view/" . $data->client_id), $data->company_name),
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $data->bill_date,
            format_to_date($data->bill_date, false),
            $data->due_date,
            format_to_date($data->due_date, false),
            to_currency($ture_val, $data->currency_symbol),
            to_currency($data->payment_received, $data->currency_symbol),
            get_receipt_taxinvoice_status_label($data, true)
        );
        //. $receipt_taxinvoice_labels
        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $row_data[] = $this->_make_options_dropdown($data->id);
        //$row_data[] = 'dfsaasdasdf';

        return $row_data;
    }

    //prepare receipt_taxinvoice status label
    private function _get_receipt_taxinvoice_status_label($data, $return_html = true)
    {
        return get_receipt_taxinvoice_status_label($data, $return_html);
    }




    function save()
    {

        $created_by = $_SESSION['user_id'];
        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "receipt_taxinvoice");
        $new_files = unserialize($files_data);





        $created_by = $_SESSION['user_id'];
        $es_id = $_REQUEST['es_item_title'];
        $inv_id = $_REQUEST['id'];




        $config['receipt_taxinvoices'] = array('prefix' => 'INV');
        $param = $config['receipt_taxinvoices'];
        $param['LPAD'] = 4;
        $param['column'] = 'doc_no';
        $param['table'] = 'receipt_taxinvoices';
        $param['id'] = $inv_id;
        $data['doc_no'] = $this->dao->genDocNo($param);

        $is_clone = $this->input->post('is_clone');
        $estimate_id = $this->input->post('estimate_id');
        $main_receipt_taxinvoice_id = "";

        if ($inv_id) {
            $sql = "UPDATE `receipt_taxinvoices` SET `bill_date` = '" . $_REQUEST['receipt_taxinvoice_bill_date'] . "', `due_date` = ADDDATE( '" . $_REQUEST['receipt_taxinvoice_bill_date'] . "', INTERVAL 1 month ) WHERE `receipt_taxinvoices`.`id` = " . $inv_id . ";";
            $this->dao->execDatas($sql);

            echo json_encode(array("success" => true, "data" => $this->_row_data($inv_id), 'id' => $inv_id, 'message' => lang('record_saved')));
            exit;
        }

        if ($is_clone) {


            //echo 'dsfdsdsdf';
            $main_receipt_taxinvoice_id = $inv_id;
            //var_dump($request);exit;
            $data['doc_no'] = $this->dao->genDocNo($param);
            $sql = "

                INSERT INTO receipt_taxinvoices (
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

            if ($is_clone && $main_receipt_taxinvoice_id) {
                save_custom_fields("receipt_taxinvoices", $inv_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a receipt_taxinvoice
                $receipt_taxinvoice_items = $this->Receipt_taxinvoice_items_model->get_all_where(array("receipt_taxinvoice_id" => $main_receipt_taxinvoice_id, "deleted" => 0))->result();

                foreach ($receipt_taxinvoice_items as $receipt_taxinvoice_item) {
                    //prepare new receipt_taxinvoice item data
                    $receipt_taxinvoice_item_data = (array) $receipt_taxinvoice_item;

                    unset($receipt_taxinvoice_item_data["id"]);
                    $receipt_taxinvoice_item_data['receipt_taxinvoice_id'] = $inv_id;
                    // var_dump($receipt_taxinvoice_item_data['receipt_taxinvoice_id']);
                    $receipt_taxinvoice_item = $this->Receipt_taxinvoice_items_model->save($receipt_taxinvoice_item_data);
                }
            } else {
                save_custom_fields("receipt_taxinvoices", $inv_id, $this->login_user->is_admin, $this->login_user->user_type);
            }


            echo json_encode(array("success" => true, "data" => $this->_row_data($inv_id), 'id' => $inv_id, 'message' => lang('record_saved')));
        } else {


            //var_dump($_REQUEST);exit;

            $sqlinv = "SElECT doc_no FROM invoices WHERE id = " . $_REQUEST['es_item_title'] . " AND deleted = 0";
            $inv = $this->db->query($sqlinv)->row();

            $docname = str_replace('BL', 'INV', $inv->doc_no);
            // var_dump($docname);exit;


            //$sql = $this->dao->showColumns('invoice_items');

            
            //var_dump(implode(',', $sql));exit;

            // var_dump($_REQUEST);exit;

            //   TRUNCATE TABLE receipt_taxinvoices;   TRUNCATE TABLE receipt_taxinvoice_items ; 
            
                $sql = "REPLACE INTO receipt_taxinvoices 
                (`pay_time`,
                `pay_spilter`,
                `pay_sp`,
                `pay_type`,
                `include_deposit`,
                `doc_no`,`es_id`,
                `lock_parent_id`,
                `prove`,`created_by`,
                `client_id`,`project_id`,
                `bill_date`,`due_date`,
                `note`,`labels`,
                `last_email_sent_date`,
                `status`,
                `tax_id`,
                `tax_id2`,
                `tax_id3`,
                `recurring`,
                `recurring_receipt_taxinvoice_id`,
                `repeat_type`,
                `no_of_cycles`,
                `next_recurring_date`,
                `no_of_cycles_completed`,
                `due_reminder_date`,
                `recurring_reminder_date`,
                `discount_amount`,
                `discount_amount_type`,
                `discount_type`,
                `cancelled_at`,
                `cancelled_by`,
                `files`,`deleted`,
                `user_id`)
                
                SELECT `pay_time`,
                `pay_spilter`,
                `pay_sp`,
                `pay_type`,
                `include_deposit`,
                '" . $data['doc_no'] . "' as doc_no,
                `es_id`,
                `lock_parent_id`,
                `prove`,
                `created_by`,
                `client_id`,
                `project_id`,
                `bill_date`,
                `due_date`,
                `note`,
                `labels`,
                `last_email_sent_date`,
                `status`,
                `tax_id`,
                `tax_id2`,
                `tax_id3`,
                `recurring`,
                `id`,
                `repeat_type`,
                `no_of_cycles`,
                `next_recurring_date`,
                `no_of_cycles_completed`,
                `due_reminder_date`,
                `recurring_reminder_date`,
                `discount_amount`,
                `discount_amount_type`,
                `discount_type`,
                `cancelled_at`,
                `cancelled_by`,
                `files`,
                `deleted`,
                `user_id`
                FROM invoices WHERE id = '" . $_REQUEST['es_item_title'] . "'";

                // arr($sql);exit;
                $this->dao->execDatas($sql);

                $inv_id = $this->db->insert_id();


                $dfsaafdsd = $inv_id;

                $sqlck = "SELECT * FROM invoice_items WHERE invoice_id = " . $_REQUEST['es_item_title'] . " AND deleted = 0";

                foreach($this->db->query($sqlck)->result() as $row){
                    //var_dump($row);exit;
                    $sql = "REPLACE INTO receipt_taxinvoice_items
                (lock_parent_id,
                lock_dt_id,
                title,
                description,
                quantity,
                unit_type,
                rate,total,
                sort,
                receipt_taxinvoice_id,
                estimate_id,
                deleted)
                SELECT lock_parent_id,
                lock_dt_id,
                title,
                description,
                quantity,
                unit_type,
                rate,
                total,
                sort,
                invoice_id,
                estimate_id,
                deleted
                FROM invoice_items WHERE id = '" . $row->id . "'";

                    $this->dao->execDatas($sql);
                }
                


            $id_s = isset($dfsaafdsd) ? $dfsaafdsd : $this->input->get('id');
            // var_dump($id_s);exit;

            echo json_encode(array("success" => true, "data" => $this->_row_data($id_s), 'id' => $id_s, 'message' => lang('record_saved')));



            // exit;
        }
    }

    function save_paySplit()
    {
        $newpay = $this->input->post();
        $es_id = $newpay['es_id'];
        $date = isset($newpay['receipt_taxinvoice_bill_date']) ? $newpay['receipt_taxinvoice_bill_date'] : date("Y-m-d");
        $pay = $newpay['paySpliter'];
        $invs_id = $newpay['receipt_taxinvoice_id'];

        $config['receipt_taxinvoices'] = array('prefix' => 'INV');
        $param = $config['receipt_taxinvoices'];
        $param['LPAD'] = 4;
        $param['column'] = 'doc_no';
        $param['table'] = 'receipt_taxinvoices';
        $param['id'] = NULL;
        $data['doc_no'] = $this->dao->genDocNo($param);

        $sql_new = "
					REPLACE INTO receipt_taxinvoices (
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
                    " . $pay . " as pay_spilter,
                    '" . $data['doc_no'] . "' as doc_no ,                        
                    es_id,                   
                    project_id,
                    client_id,
                    bill_date,
                    (SELECT ADDDATE( max(`due_date`), INTERVAL 30 day ) FROM `receipt_taxinvoices` WHERE `es_id` = inv.es_id ) as due_date,
                    last_email_sent_date,
                    note, 
                    status, 
                    tax_id, 
                    tax_id2, 
                    discount_amount, 
                    discount_amount_type, 
                    discount_type,
                    cancelled_by,created_by,files,user_id,repeat_type

					FROM receipt_taxinvoices inv 
				
					WHERE inv.id = " . $invs_id . " 
                    HAVING pay_spilter > 0
					
				";



        $this->dao->execDatas($sql_new);

        $inv_id = $this->db->insert_id();

        $sql_get = "
                    SELECT 				
                        receipt_taxinvoices.*                        
                    FROM `receipt_taxinvoices`
                    
                    WHERE receipt_taxinvoices.id = " . $invs_id . "  
			    ";



        $receipt_taxinvoices = $this->dao->fetch($sql_get);


        if ($inv_id) {

            $sql_item = "
                        INSERT INTO receipt_taxinvoice_items (
                            quantity,
                            rate,
                            total,
                            receipt_taxinvoice_id,
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
                            " . $inv_id . " as receipt_taxinvoice_id,
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
                            FROM receipt_taxinvoice_items invItem
                            INNER JOIN receipt_taxinvoices inv on invItem.receipt_taxinvoice_id = inv.id
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
        $newPaid = $receipt_taxinvoices->pay_spilter - $pay;
        $sql_edit = "UPDATE `receipt_taxinvoices` SET `pay_spilter` = " . $newPaid . " WHERE `receipt_taxinvoices`.`id` = " . $invs_id . " ";

        $this->dao->execDatas($sql_edit);
        redirect(base_url('receipt_taxinvoices/view/' . $invs_id));
    }


    function modal_form()
    {

        $request = $this->input->post();


        $client_id = $this->input->post('client_id');

        $project_id = $this->input->post('project_id');

        $model_info = $this->Receipt_taxinvoices_model->get_one($this->input->post('id'));


        //check if estimate_id/order_id posted. if found, generate related information
        $estimate_id = $this->input->post('estimate_id');

        $order_id = $this->input->post('order_id');


        if (!$this->can_edit_receipt_taxinvoices()) {
            // redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));

        $client_id = $this->input->post('client_id');

        $project_id = $this->input->post('project_id');
        $model_info = $this->Receipt_taxinvoices_model->get_one($this->input->post('id'));


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


        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("receipt_taxinvoices", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->result();

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

        $this->load->view('receipt_taxinvoices/modal_form', $view_data);
    }
}

/* End of file receipt_taxinvoices.php */
/* Location: ./application/controllers/receipt_taxinvoices.php */