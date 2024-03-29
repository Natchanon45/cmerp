<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Estimates extends MY_Controller {

    function __construct() {
        parent::__construct();
       
		$this->init_permission_checker("estimate");
		
		$this->className = 'estimates';
        $this->load->model("Quotations_m");
        $this->load->model("Clients_m");
        $this->load->model("Users_m");
    }
	

    /* load estimate list view */

    function index() {
        redirect("quotations");

        // $this->check_module_availability("module_estimate");
        // $view_data['can_request_estimate'] = false;

        // $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type );

        // if ( $this->login_user->user_type === "staff" ) {
            
		// 	$this->access_only_allowed_members();

        //     $this->template->rander( "estimates/index", $view_data );
			
        // } else {
        //     //client view
        //     $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
        //     $view_data['client_id'] = $this->login_user->client_id;
        //     $view_data['page_type'] = "full";


        //     if ( get_setting( "module_estimate_request" ) == "1" ) {
        //         $view_data['can_request_estimate'] = true;
        //     }

        //     $this->template->rander("clients/estimates/client_portal", $view_data);
        // }
    }

    //load the yearly view of estimate list
    function yearly() {
        $this->load->view("estimates/yearly_estimates");
    }

    /* load new estimate modal */


    /* add, edit or clone an estimate */


    //update estimate status
    function update_estimate_status($estimate_id, $status) {
        if ($estimate_id && $status) {
            $estmate_info = $this->Estimates_model->get_one($estimate_id);
            $this->access_only_allowed_members_or_client_contact($estmate_info->client_id);


            if ($this->login_user->user_type == "client") {
                //updating by client
                //client can only update the status once and the value should be either accepted or declined
                if ($estmate_info->status == "sent" && ($status == "accepted" || $status == "declined")) {

                    $estimate_data = array("status" => $status);
                    $estimate_id = $this->Estimates_model->save($estimate_data, $estimate_id);

                    //create notification
                    if ($status == "accepted") {
                        log_notification("estimate_accepted", array("estimate_id" => $estimate_id));

                        //estimate accepted, create a new project
                        if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted")) {
                            $this->_create_project_from_estimate($estimate_id);
                        }
                    } else if ($status == "declined") {
                        log_notification("estimate_rejected", array("estimate_id" => $estimate_id));
                    }
                }
            } else {
                //updating by team members

                if ($status == "accepted" || $status == "declined") {
                    $estimate_data = array("status" => $status);
                    $estimate_id = $this->Estimates_model->save($estimate_data, $estimate_id);

                    //estimate accepted, create a new project
                    if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted")) {
                        if ($status == "accepted") {
                            $this->_create_project_from_estimate($estimate_id);
                        }
                    }
                }
            }
        }
    }

    /* create new project from accepted estimate */

    private function _create_project_from_estimate($estimate_id) {
        if ($estimate_id) {
            $estimate_info = $this->Estimates_model->get_one($estimate_id);

            //don't create new project if there has already been created a new project with this estimate
            if (!$this->Projects_model->get_one_where(array("estimate_id" => $estimate_id))->id) {
                $data = array(
                    "title" => get_estimate_id($estimate_info->id),
                    "client_id" => $estimate_info->client_id,
                    "start_date" => $estimate_info->estimate_date,
                    "deadline" => $estimate_info->valid_until,
                    "estimate_id" => $estimate_id
                );
                $save_id = $this->Projects_model->save($data);

                //save the project id
                $data = array("project_id" => $save_id);
                $this->Estimates_model->save($data, $estimate_id);
            }
        }
    }

    /* delete or undo an estimate */

    function delete() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Estimates_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Estimates_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

   


    /* list of estimate of a specific client, prepared for datatable  */

    function estimate_list_data_of_client($client_id) {

        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id, "status" => $this->input->post("status"), "custom_fields" => $custom_fields);

        if ($this->login_user->user_type == "client") {
            // don't show draft estimates to clients.
            $options["exclude_draft"] = true;
        }

        // $list_data = $this->Estimates_model->get_details($options)->result();
        // $result = array();
        // foreach ($list_data as $data) {
        //     $result[] = $this->_make_row($data, $custom_fields);
        // }

        echo json_encode(array("data" => array()));
    }

    /* return a row of estimate list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Estimates_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }

    /* prepare a row of estimate list table */



    //prepare estimate status label 
    private function _get_estimate_status_label($estimate_info, $return_html = true) {
        $estimate_status_class = "label-default";
        //don't show sent status to client, change the status to 'new' from 'sent'
        // var_dump($estimate_info);
        if ($this->login_user->user_type == "client") {
            if ($estimate_info->status == "sent") {
                $estimate_info->status = "new";
            } else if ($estimate_info->status == "declined") {
                $estimate_info->status = "rejected";
            }
        }
        if ( $estimate_info->status == "อนุมัติ" ) {
            $estimate_status_class = "label-success";
            $status = $estimate_info->status;
            
         }else
        if ($estimate_info->status == "draft") {
            $estimate_status_class = "label-default";
        } else if ($estimate_info->status == "declined" || $estimate_info->status == "rejected") {
            $estimate_status_class = "label-danger";
        } else if ($estimate_info->status == "accepted") {
            $estimate_status_class = "label-success";
        } else if ($estimate_info->status == "sent") {
            $estimate_status_class = "label-primary";
        } else if ($estimate_info->status == "new") {
            $estimate_status_class = "label-warning";
        }

        $estimate_status = "<span class='mt0 label $estimate_status_class large'>" . $estimate_info->status . "</span>";
        if ($return_html) {
            return $estimate_status;
        } else {
            return $status;
        }
    }

    /* load estimate details view */


    /* estimate total section */

    private function _get_estimate_total_view($estimate_id = 0) {
        $view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id);
        $view_data["estimate_id"] = $estimate_id;
        return $this->load->view('estimates/estimate_total_section', $view_data, true);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->input->post('estimate_id');

        $view_data['model_info'] = $this->Estimates_model->get_one($estimate_id);

        $this->load->view('estimates/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "estimate_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $estimate_id = $this->input->post('estimate_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Estimates_model->save($data, $estimate_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "estimate_total_view" => $this->_get_estimate_total_view($estimate_id), 'message' => lang('record_saved'), "estimate_id" => $estimate_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* load item modal */

    function item_modal_form() {
        $data = $this->Quotations_m->item();

        /*$estimate_id = $this->input->post('estimate_id');

        $view_data['model_info'] = $this->Estimate_items_model->get_one($this->input->post('id'));
        if (!$estimate_id) {
            $estimate_id = $view_data['model_info']->estimate_id;
        }
        $view_data['estimate_id'] = $estimate_id;*/


        $this->load->view('estimates/item_modal_form', $data);
    }

    /* Change Address Form */

    function from_address_modal() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $estimate_id = $this->input->post('estimate_id');

        $view_data['model_info'] = $this->Estimate_items_model->get_one($this->input->post('id'));
        if (!$estimate_id) {
            $estimate_id = $view_data['model_info']->estimate_id;
        }
        $view_data['estimate_id'] = $estimate_id;
        $this->load->view('estimates/from_address_modal', $view_data);
    }


    /* add or edit an estimate item */

    /*function save_item() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->input->post('estimate_id');

        $id = $this->input->post('id');
        $rate = unformat_currency($this->input->post('estimate_item_rate'));
        $quantity = unformat_currency($this->input->post('estimate_item_quantity'));

        $estimate_item_data = array(
            "estimate_id" => $estimate_id,
            "title" => $this->input->post('estimate_item_title'),
            "description" => $this->input->post('estimate_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->input->post('estimate_unit_type'),
            "rate" => unformat_currency($this->input->post('estimate_item_rate')),
            "total" => $rate * $quantity,
        );

        $estimate_item_id = $this->Estimate_items_model->save($estimate_item_data, $id);
        if ($estimate_item_id) {

            $options = array("id" => $estimate_item_id);
            $item_info = $this->Estimate_items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'id' => $estimate_item_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }*/

    /* delete or undo an estimate item */

    /*function delete_item() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Estimate_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Estimate_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Estimate_items_model->delete($id)) {
                $item_info = $this->Estimate_items_model->get_one($id);
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }*/

    function load_doc(){
        echo $this->Quotations_m->jDoc();
    }

    function load_items(){
        echo $this->Quotations_m->jItems();
    }

    function save_item(){
        echo json_encode($this->Quotations_m->saveItem());
    }

    function delete_item(){
        echo json_encode($this->Quotations_m->deleteItem());
    }

    /* list of estimate items, prepared for datatable  */
    function item_list_data($estimate_id = 0) {
        $this->access_only_allowed_members();

        $list_data = $this->Estimate_items_model->get_details(array("estimate_id" => $estimate_id))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of estimate item list table */

    private function _make_item_row($data) {
        $item = "<div class='item-row strong mb5' data-id='$data->id'><i class='fa fa-bars pull-left move-icon'></i> $data->title</div>";
        if ($data->description) {
            $item .= "<span style='margin-left:25px'>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->price_inc_vat, $data->currency_symbol),
            modal_anchor(get_uri("estimates/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_estimate'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of estimate item */

    function get_estimate_item_suggestion() {
        $key = @$_REQUEST["q"];
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_estimate_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($estimate_id = 0, $show_close_preview = false) {

        $view_data = array();

        if ($estimate_id) {

            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);

            //get the label of the estimate
            $estimate_info = get_array_value($estimate_data, "estimate_info");
            $estimate_data['estimate_status_label'] = $this->_get_estimate_status_label($estimate_info);

            $view_data['estimate_preview'] = prepare_estimate_pdf($estimate_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['estimate_id'] = $estimate_id;
            
            $this->template->rander("estimates/estimate_preview", $view_data);
        } else {
            show_404();
        }
    }

    function download_pdf($estimate_id = 0, $mode = "download") {
        if ($estimate_id) {
            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid estimate data. Prepare the view.

            prepare_estimate_pdf($estimate_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_estimate_access_permission($estimate_data) {
        //check for valid estimate
        if (!$estimate_data) {
            show_404();
        }

        //check for security
        $estimate_info = get_array_value($estimate_data, "estimate_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $estimate_info->client_id) {
                redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function get_estimate_status_bar($estimate_id = 0) {
        $this->access_only_allowed_members();

        $view_data["estimate_info"] = $this->Estimates_model->get_details(array("id" => $estimate_id))->row();
        $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
        $this->load->view('estimates/estimate_status_bar', $view_data);
    }

    function send_estimate_modal_form($estimate_id) {
        $this->access_only_allowed_members();

        if ($estimate_id) {
            $options = array("id" => $estimate_id);
            $estimate_info = $this->Estimates_model->get_details($options)->row();
            $view_data['estimate_info'] = $estimate_info;

            $is_lead = $this->input->post('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $estimate_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $estimate_info->client_id);
            }

            $contacts = $this->Users_model->get_details($contacts_options)->result();
            $contact_first_name = "";
            $contact_last_name = "";
            $contacts_dropdown = array();
            foreach ($contacts as $contact) {
                if ($contact->is_primary_contact) {
                    $contact_first_name = $contact->first_name;
                    $contact_last_name = $contact->last_name;
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name . " (" . lang("primary_contact") . ")";
                }
            }

            foreach ($contacts as $contact) {
                if (!$contact->is_primary_contact) {
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name;
                }
            }

            $view_data['contacts_dropdown'] = $contacts_dropdown;

            $email_template = $this->Email_templates_model->get_final_template("estimate_sent");

            $parser_data["ESTIMATE_ID"] = $estimate_info->id;
            $parser_data["CONTACT_FIRST_NAME"] = $contact_first_name;
            $parser_data["CONTACT_LAST_NAME"] = $contact_last_name;
            $parser_data["PROJECT_TITLE"] = $estimate_info->project_title;
            $parser_data["ESTIMATE_URL"] = get_uri("estimates/preview/" . $estimate_info->id);
            $parser_data['SIGNATURE'] = $email_template->signature;
            $parser_data["LOGO_URL"] = get_logo_url();

            $view_data['message'] = $this->parser->parse_string($email_template->message, $parser_data, TRUE);
            $view_data['subject'] = $email_template->subject;

            $this->load->view('estimates/send_estimate_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function send_estimate() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $estimate_id = $this->input->post('id');

        $contact_id = $this->input->post('contact_id');
        $cc = $this->input->post('estimate_cc');

        $custom_bcc = $this->input->post('estimate_bcc');
        $subject = $this->input->post('subject');
        $message = decode_ajax_post_data($this->input->post('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $estimate_data = get_estimate_making_data($estimate_id);
        $attachement_url = prepare_estimate_pdf($estimate_data, "send_email");

        $default_bcc = get_setting('send_estimate_bcc_to');
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => array(array("file_path" => $attachement_url)), "cc" => $cc, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "sent", "last_email_sent_date" => get_my_local_time());
            if ($this->Estimates_model->save($status_data, $estimate_id)) {
                echo json_encode(array('success' => true, 'message' => lang("estimate_sent_message"), "estimate_id" => $estimate_id));
            }
            // delete the temp estimate
            if (file_exists($attachement_url)) {
                unlink($attachement_url);
            }
        } else {
            echo json_encode(array('success' => false, 'message' => lang('error_occurred')));
        }
    }

    //update the sort value for estimate item
    function update_item_sort_values($id = 0) {

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
                $this->Estimate_items_model->save($data, $id);
            }
        }
    }
	
	
	
    function view($estimate_id = 0) {
        $this->access_only_allowed_members();
 
        if ( $estimate_id ) {

            $view_data = get_estimate_making_data($estimate_id);

            if ( $view_data ) {
				
                $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
                $view_data['estimate_status'] = $this->_get_estimate_status_label($view_data["estimate_info"], true);

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $view_data["can_create_projects"] = $this->can_create_projects();

                $view_data["estimate_id"] = $estimate_id;

				$param['id'] = $estimate_id;
				
				$param['tbName'] = $this->className;
				$view_data["proveButton"] = $this->dao->getProveButton( $param );
				
				///$param['status'] = $this->_get_estimate_status_label( $view_data )

                $this->template->rander($this->className ."/view", $view_data);
				
				
            } else {
                show_404();
            }
        }
    }

    function view2($estimate_id) {
        $data = $this->Quotations_m->doc($estimate_id);
        if ($data["success"] == true) {

            $data["created"] = $this->Users_m->getInfo($data["esrow"]->created_by);
            $data["client"] = $this->Clients_m->getInfo($data["esrow"]->client_id);
            $data["client_contact"] = $this->Clients_m->getContactInfo($data["esrow"]->client_id);
            if($data["client"] != null) $data["client_contact"] = $this->Clients_m->getContactInfo($data["esrow"]->client_id);
            
            $data['estimate_status_label'] = $this->_get_estimate_status_label($data["esrow"]);
            $data['estimate_status'] = $this->_get_estimate_status_label($data["esrow"], true);

            $param['id'] = $data["esrow"]->id;            
            $param['tbName'] = $this->className;
            $data["proveButton"] = $this->dao->getProveButton( $param );

            $this->template->rander( "". $this->className ."/view2", $data );
        }
    }
	
    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table( "estimates", $this->login_user->is_admin, $this->login_user->user_type );

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields
        );

        $list_data = $this->Estimates_model->get_details( $options, $this->getRolePermission )->result();
        // var_dump($list_data);
        
		$result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row( $data, $custom_fields );
        }

        echo json_encode( array( "data" => $result ) );
    }
	
	
    private function _make_row( $data, $custom_fields ) {
        $estimate_url = "";
        if ($this->login_user->user_type == "staff") {
            $estimate_url = anchor(get_uri("estimates/view/" . $data->id), $data->doc_no);
        } else {
            //for client client
            $estimate_url = anchor(get_uri("estimates/preview/" . $data->id), $data->doc_no);
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }


        $row_data = array(
            $estimate_url,
            $client,
            $data->estimate_date,
            format_to_date($data->estimate_date, false),
            to_decimal_format3($data->estimate_value),
            !empty($data->currency) ? lang($data->currency) : lang('THB'),
			$this->_get_estimate_status_label( $data ),
        );
		 //$data->status
		
//
        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $row_data[] = modal_anchor(get_uri("estimates/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_estimate'), "data-post-id" => $data->id))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_estimate'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete"), "data-action" => "delete"));

        return $row_data;
    }

    function set_time_pay(){
        $request = $this->input->get();
        
        echo json_encode(array("text" => $request['val']));
        
    }
	
	
    function modal_form() {
		
        validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));

        // $param = [];
		
        // $param['url'] = '';
		// $param['table_name'] = $_SESSION['table_name'];
		// $param['user_id'] = $_SESSION['user_id'];
		// $this->getRolePermission = $this->dao->getRolePermission( $param  );

        
        // if( empty( $this->getRolePermission['edit_row'] ) ) {
			
		// 	echo permissionBlock();
			
		// 	return;
			 
		// }


        $project_id = $this->input->post('project_id');
        $client_id = $this->input->post('client_id');
        $model_info = $this->Estimates_model->get_one($this->input->post('id'));

        //check if order_id posted. if found order_id, so, we'll show the order info to copy the order 
        $order_id = $this->input->post('order_id');
        $view_data['order_id'] = $order_id;
        if ($order_id) {
            $order_info = $this->Orders_model->get_one($order_id);
            $now = get_my_local_time("Y-m-d");
            $model_info->estimate_date = $now;
            $model_info->valid_until = $now;
            $model_info->client_id = $order_info->client_id;
            $model_info->tax_id = $order_info->tax_id;
            $model_info->tax_id2 = $order_info->tax_id2;
        }

        if ($project_id) {
            $client_id = $this->Projects_model->get_one($project_id)->client_id;
            $model_info->client_id = $client_id;
        }

        $project_client_id = $client_id;
        if ($model_info->client_id) {
            $project_client_id = $model_info->client_id;
        }

        $view_data['model_info'] = $model_info;

        $estimate_request_id = $this->input->post('estimate_request_id');
        $view_data['estimate_request_id'] = $estimate_request_id;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_clients_and_leads_dropdown();

        //don't show clients dropdown for lead's estimate editing
        $client_info = $this->Clients_model->get_one($view_data['model_info']->client_id);
        if ($client_info->is_lead) {
            $client_id = $client_info->id;
        }

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id");
        $suggestion = array(array("id" => "", "text" => "-"));
		
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }

        $view_data['client_id'] = $client_id;

        //clone estimate data
        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data['projects_suggestion'] = $suggestion;

        $view_data['project_id'] = $project_id;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("estimates", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type )->result();
		
		
		
		
		///$view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id );
		
		$pay_sp = !empty( $model_info->pay_sp )? $model_info->pay_sp: 100;
		$pay_type = !empty( $model_info->pay_type )? $model_info->pay_type: 'fixed_amount';
		
		
		$options = array( "percentage" => '%', 'time' => 'งวด', 'fixed_amount' => 'บาท' );
        
        $options2 = array( "percentage" => '%', 'fixed_amount' => 'บาท' );
        $keep = array();
        if (!empty($model_info->pay_sps)){
            
            $val_json = json_decode($model_info->pay_sps);
            foreach($val_json as $k => $v){ 
                $keep[$k] = $v; 
            }
            
            // arr($val_json);
        }

        for($i=1; $i<=6;$i++){
            $val = 0;
            $val2 = "percentage";
            if(isset($keep[$i])){
                $val = $keep[$i]->pay_sps;
                $val2 = $keep[$i]->pay_types;
            }
            $data[] = '
                            <label for="invoice_bill_date" class="col-md-3">
                            การชำระงวดที่ '.$i.'
                            </label>
                            <div class="col-md-9" style="margin-top: 5px;display: grid; grid-template-columns: auto auto;">

                                <input name="pay_sps['.$i.']" type="number" value="'. $val .'" class="form-control">

                                '. form_dropdown( "pay_types[".$i."]", $options2, array( $val2 ), "class='select2 tax-select2'" ) .'

                        </div>
            ';

        }

        
        if(!empty(json_decode($model_info->pay_sps))){
            $checked = 'checked';
        }else{
            $checked = '';
        }

        // var_dump($checked);
              
        
        
		$credit = !empty($model_info->credit) ? $model_info->credit : 0;
        $deposit = !empty($model_info->deposit) ? $model_info->deposit : 0;
		$view_data["gogo"] = '
			<div class="form-group">
				<label for="estimate_date" class=" col-md-3">'. lang('estimate_date') .'</label>
				<div class="col-md-9">
					'. form_input(array(
						"id" => "estimate_date",
						"name" => "estimate_date",
						"value" => $model_info->estimate_date,
						"class" => "form-control",
						"placeholder" => lang('estimate_date'),
						"autocomplete" => "off",
						"data-rule-required" => true,
						"data-msg-required" => lang("field_required"),
					)) .'
				</div>
			</div>

			<div class="form-group">
				<label for="invoice_bill_date" class=" col-md-3">
				
					การชำระ
				</label>
				<div class="col-md-9" style="display: grid;grid-template-columns: auto auto;">

					<input style="" id="pay_sp" name="pay_sp" type="number" value="'. $pay_sp .'" style="text-align: right;" class="form-control">

					'. form_dropdown( "pay_type", $options, array( $pay_type ), "id='pay_type' class='select2 tax-select2'" ) .'
                    <span id="timeChecker" style="color: red"></span>
				</div>
			</div>

                <div class="form-group" id="check_show" style="display: none;">
                    <label class=" col-md-3"></label>
                    <div class="col-md-9">					   
                        <input id="sp_deposit" type="checkbox" name="chckDes" style="padding-right: 5px;" '.$checked.'> <label for="sp_deposit"> แบ่งชำระแต่ละงวด</label>
                    </div>
                    
                </div>
                <div class="form-group" id="form_show" style="display: none;">                        
                    '.implode("",$data).'
                </div>
            

			<div class="form-group">
				<label for="invoice_due_date" class=" col-md-3">เครดิต</label>
				
				<div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">

					<input type="number" name="credit" value="'. $credit .'"   class="form-control" placeholder="กรอกเลข 0 หากชำระเงินสด" autocomplete="off" >
					 
					
					<div style="padding-left: 5px;"> วันหลังออกใบแจ้งหนี้</div>
				</div>
				
			</div>

            <div class="form-group">
				<label for="invoice_due_date" class=" col-md-3">วางเงินมัดจำ</label>
				
				<div class="col-md-9">

					<input type="number" name="deposit" value="'. $deposit .'"   class="form-control" placeholder="กรอกเลข 0 หากไม่วางเงินมัดจำ" autocomplete="off" >					 
				</div>
				
			</div>           
            
		';
        
		
		
		

        $this->load->view( 'estimates/modal_form', $view_data );
    }
	
    function save() {
        
        $this->access_only_allowed_members();
        $res = $_REQUEST;

        
        // arr($this->input->post());exit;
    //     // foreach( $_REQUEST['pay_sps'] as $k => $v){
    //     //     arr($v);
    //     // }
    //    exit;
        validate_submitted_data(array(
            "id" => "numeric",
            "estimate_client_id" => "required|numeric",
            "estimate_date" => "required",
            "valid_until" => "required",
            "estimate_request_id" => "numeric"
        ));

        $client_id = $this->input->post('estimate_client_id');
        $id = $this->input->post('id');
        
        $keep = array();
        
        foreach( $_REQUEST['pay_sps'] as $k => $v){

            if(!isset($_REQUEST['pay_types'][$k])){
                continue;
            }

            if(empty($_REQUEST['pay_sps'][$k])){
                continue;
            }
            
            $keep[$k] = array('pay_sps' => $v, 'pay_types' => $_REQUEST['pay_types'][$k]);
            
        }
        
        // arr($keep);
        // exit;

        $estimate_data = array(
            "client_id" => $client_id,
            "estimate_date" => $this->input->post('estimate_date'),
            "valid_until" => $this->input->post('valid_until'),
            "tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
            "tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
            "note" => $this->input->post('estimate_note'),
            "project_id" => $this->input->post('estimate_project_id'),
			
			
			
			"deposit" => $this->input->post('deposit') ? $this->input->post('deposit') : 0,
            "pay_sp" => $this->input->post('pay_sp') ? $this->input->post('pay_sp') : 100,
            "pay_type" => $this->input->post('pay_type') ? $this->input->post('pay_type') : 'percentage',
            "credit" => $this->input->post('credit') ? $this->input->post('credit') : 0
			
        );
        
        // "deposit"
        if(isset($_REQUEST['chckDes'])){
            $estimate_data['pay_sps'] = json_encode($keep);
        }else{
            $estimate_data['pay_sps'] = '';
        }

        $is_clone = $this->input->post('is_clone');
        $estimate_request_id = $this->input->post('estimate_request_id');

        //estimate creation from estimate request
        //store the estimate request id for the first time only
        //don't copy estimate request id on cloning too
        if ($estimate_request_id && !$id && !$is_clone) {
            $estimate_data["estimate_request_id"] = $estimate_request_id;
        }

        $main_estimate_id = "";
        if ($is_clone && $id) {
            $main_estimate_id = $id; //store main estimate id to get items later
            $id = ""; //on cloning estimate, save as new
            //save discount when cloning
            $estimate_data["discount_amount"] = $this->input->post('discount_amount') ? $this->input->post('discount_amount') : 0;
            $estimate_data["discount_amount_type"] = $this->input->post('discount_amount_type') ? $this->input->post('discount_amount_type') : "percentage";
            $estimate_data["discount_type"] = $this->input->post('discount_type') ? $this->input->post('discount_type') : "before_tax";
        }

        $estimate_id = $this->Estimates_model->save($estimate_data, $id);
        if ($estimate_id) {

            if ($is_clone && $main_estimate_id) {
                //add estimate items

                save_custom_fields("estimates", $estimate_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate

                $estimate_items = $this->Estimate_items_model->get_all_where(array("estimate_id" => $main_estimate_id, "deleted" => 0))->result();

                foreach ($estimate_items as $estimate_item) {
                    //prepare new estimate item data
                    $estimate_item_data = (array) $estimate_item;
                    unset($estimate_item_data["id"]);
                    $estimate_item_data['estimate_id'] = $estimate_id;

                    $estimate_item = $this->Estimate_items_model->save($estimate_item_data);
                }
            } else {
                save_custom_fields("estimates", $estimate_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_order? copy all items from order
            $copy_items_from_order = $this->input->post("copy_items_from_order");
            if ($copy_items_from_order) {
                $order_items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->result();

                foreach ($order_items as $data) {
                    $estimate_item_data = array(
                        "estimate_id" => $estimate_id,
                        "title" => $data->title ? $data->title : "",
                        "description" => $data->description ? $data->description : "",
                        "quantity" => $data->quantity ? $data->quantity : 0,
                        "unit_type" => $data->unit_type ? $data->unit_type : "",
                        "rate" => $data->rate ? $data->rate : 0,
                        "price_inc_vat" => $data->price_inc_vat ? $data->price_inc_vat : 0,
                    );

                    $this->Estimate_items_model->save($estimate_item_data);
                }
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($estimate_id), 'id' => $estimate_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }
	
	
}

/* End of file estimates.php */
/* Location: ./application/controllers/estimates.php */