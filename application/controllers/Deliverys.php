<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Deliverys extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Deliverys_model');
        $this->load->model('Delivery_items_model');
       
		$this->init_permission_checker("delivery");
		
		$this->className = 'deliverys';
    }
	

    /* load delivery list view */

    function index() {
		
		 
        $this->check_module_availability("module_delivery");
        $view_data['can_request_delivery'] = false;

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("deliverys", $this->login_user->is_admin, $this->login_user->user_type );

        if ( $this->login_user->user_type === "staff" ) {
            
			$this->access_only_allowed_members();

            $this->template->rander( "deliverys/index", $view_data );
			
        } else {
            //client view
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";


            if ( get_setting( "module_delivery_request" ) == "1" ) {
                $view_data['can_request_delivery'] = true;
            }

            $this->template->rander("clients/deliverys/client_portal", $view_data);
        }
    }

    //load the yearly view of delivery list
    function yearly() {
        $this->load->view("deliverys/yearly_deliverys");
    }

    /* load new delivery modal */


    /* add, edit or clone an delivery */


    //update delivery status
    function update_delivery_status($delivery_id, $status) {
        if ($delivery_id && $status) {
            $estmate_info = $this->Deliverys_model->get_one($delivery_id);
            $this->access_only_allowed_members_or_client_contact($estmate_info->client_id);


            if ($this->login_user->user_type == "client") {
                //updating by client
                //client can only update the status once and the value should be either accepted or declined
                if ($estmate_info->status == "sent" && ($status == "accepted" || $status == "declined")) {

                    $delivery_data = array("status" => $status);
                    $delivery_id = $this->Deliverys_model->save($delivery_data, $delivery_id);

                    //create notification
                    if ($status == "accepted") {
                        log_notification("delivery_accepted", array("delivery_id" => $delivery_id));

                        //delivery accepted, create a new project
                        if (get_setting("create_new_projects_automatically_when_deliverys_gets_accepted")) {
                            $this->_create_project_from_delivery($delivery_id);
                        }
                    } else if ($status == "declined") {
                        log_notification("delivery_rejected", array("delivery_id" => $delivery_id));
                    }
                }
            } else {
                //updating by team members

                if ($status == "accepted" || $status == "declined") {
                    $delivery_data = array("status" => $status);
                    $delivery_id = $this->Deliverys_model->save($delivery_data, $delivery_id);

                    //delivery accepted, create a new project
                    if (get_setting("create_new_projects_automatically_when_deliverys_gets_accepted")) {
                        if ($status == "accepted") {
                            $this->_create_project_from_delivery($delivery_id);
                        }
                    }
                }
            }
        }
    }

    /* create new project from accepted delivery */

    private function _create_project_from_delivery($delivery_id) {
        if ($delivery_id) {
            $delivery_info = $this->Deliverys_model->get_one($delivery_id);

            //don't create new project if there has already been created a new project with this delivery
            if (!$this->Projects_model->get_one_where(array("delivery_id" => $delivery_id))->id) {
                $data = array(
                    "title" => get_delivery_id($delivery_info->id),
                    "client_id" => $delivery_info->client_id,
                    "start_date" => $delivery_info->delivery_date,
                    "deadline" => $delivery_info->valid_until,
                    "delivery_id" => $delivery_id
                );
                $save_id = $this->Projects_model->save($data);

                //save the project id
                $data = array("project_id" => $save_id);
                $this->Deliverys_model->save($data, $delivery_id);
            }
        }
    }

    /* delete or undo an delivery */

    function delete() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Deliverys_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Deliverys_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

   


    /* list of delivery of a specific client, prepared for datatable  */

    function delivery_list_data_of_client($client_id) {
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("deliverys", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id, "status" => $this->input->post("status"), "custom_fields" => $custom_fields);

        if ($this->login_user->user_type == "client") {
            //don't show draft deliverys to clients.
            $options["exclude_draft"] = true;
        }

        $list_data = $this->Deliverys_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of delivery list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("deliverys", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Deliverys_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }

    /* prepare a row of delivery list table */



    //prepare delivery status label 
    private function _get_delivery_status_label($delivery_info, $return_html = true) {
        $delivery_status_class = "label-default";
        //don't show sent status to client, change the status to 'new' from 'sent'
        // var_dump($delivery_info);
        if ($this->login_user->user_type == "client") {
            if ($delivery_info->status == "sent") {
                $delivery_info->status = "new";
            } else if ($delivery_info->status == "declined") {
                $delivery_info->status = "rejected";
            }
        }
        if ( $delivery_info->status == "อนุมัติ" ) {
            $delivery_status_class = "label-success";
            $status = $delivery_info->status;
            
         }else
        if ($delivery_info->status == "draft") {
            $delivery_status_class = "label-default";
        } else if ($delivery_info->status == "declined" || $delivery_info->status == "rejected") {
            $delivery_status_class = "label-danger";
        } else if ($delivery_info->status == "accepted") {
            $delivery_status_class = "label-success";
        } else if ($delivery_info->status == "sent") {
            $delivery_status_class = "label-primary";
        } else if ($delivery_info->status == "new") {
            $delivery_status_class = "label-warning";
        }

        $delivery_status = "<span class='mt0 label $delivery_status_class large'>" . $delivery_info->status . "</span>";
        if ($return_html) {
            return $delivery_status;
        } else {
            return $status;
        }
    }

    /* load delivery details view */


    /* delivery total section */

    private function _get_delivery_total_view($delivery_id = 0) {
        $view_data["delivery_total_summary"] = $this->Deliverys_model->get_delivery_total_summary($delivery_id);
        $view_data["delivery_id"] = $delivery_id;
        return $this->load->view('deliverys/delivery_total_section', $view_data, true);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "delivery_id" => "required|numeric"
        ));

        $delivery_id = $this->input->post('delivery_id');

        $view_data['model_info'] = $this->Deliverys_model->get_one($delivery_id);

        $this->load->view('deliverys/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "delivery_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $delivery_id = $this->input->post('delivery_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Deliverys_model->save($data, $delivery_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "delivery_total_view" => $this->_get_delivery_total_view($delivery_id), 'message' => lang('record_saved'), "delivery_id" => $delivery_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* load item modal */

    function item_modal_form() {
		
		if( empty( $this->getRolePermission['edit_row'] ) ) {
			
			echo permissionBlock();
			
			return;
			 
		}
		
		
       // $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $delivery_id = $this->input->post('delivery_id');

        $view_data['model_info'] = $this->Delivery_items_model->get_one($this->input->post('id'));
        if (!$delivery_id) {
            $delivery_id = $view_data['model_info']->delivery_id;
        }
        $view_data['delivery_id'] = $delivery_id;
        $this->load->view('deliverys/item_modal_form', $view_data);
    }

    /* Change Address Form */

    function from_address_modal() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $delivery_id = $this->input->post('delivery_id');

        $view_data['model_info'] = $this->Delivery_items_model->get_one($this->input->post('id'));
        if (!$delivery_id) {
            $delivery_id = $view_data['model_info']->delivery_id;
        }
        $view_data['delivery_id'] = $delivery_id;
        $this->load->view('deliverys/from_address_modal', $view_data);
    }


    /* add or edit an delivery item */

    function save_item() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "delivery_id" => "required|numeric"
        ));

        $delivery_id = $this->input->post('delivery_id');

        $id = $this->input->post('id');
        $rate = unformat_currency($this->input->post('delivery_item_rate'));
        $quantity = unformat_currency($this->input->post('delivery_item_quantity'));

        $delivery_item_data = array(
            "delivery_id" => $delivery_id,
            "title" => $this->input->post('delivery_item_title'),
            "description" => $this->input->post('delivery_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->input->post('delivery_unit_type'),
            "rate" => unformat_currency($this->input->post('delivery_item_rate')),
            "total" => $rate * $quantity,
        );

        $delivery_item_id = $this->Delivery_items_model->save($delivery_item_data, $id);
        if ($delivery_item_id) {


            //check if the add_new_item flag is on, if so, add the item to libary. 
            $add_new_item_to_library = $this->input->post('add_new_item_to_library');
            if ($add_new_item_to_library) {
                $library_item_data = array(
                    "title" => $this->input->post('delivery_item_title'),
                    "description" => $this->input->post('delivery_item_description'),
                    "unit_type" => $this->input->post('delivery_unit_type'),
                    "rate" => unformat_currency($this->input->post('delivery_item_rate'))
                );
                $this->Items_model->save($library_item_data);
            }



            $options = array("id" => $delivery_item_id);
            $item_info = $this->Delivery_items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "delivery_id" => $item_info->delivery_id, "data" => $this->_make_item_row($item_info), "delivery_total_view" => $this->_get_delivery_total_view($item_info->delivery_id), 'id' => $delivery_item_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an delivery item */

    function delete_item() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Delivery_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Delivery_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "delivery_id" => $item_info->delivery_id, "data" => $this->_make_item_row($item_info), "delivery_total_view" => $this->_get_delivery_total_view($item_info->delivery_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Delivery_items_model->delete($id)) {
                $item_info = $this->Delivery_items_model->get_one($id);
                echo json_encode(array("success" => true, "delivery_id" => $item_info->delivery_id, "delivery_total_view" => $this->_get_delivery_total_view($item_info->delivery_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of delivery items, prepared for datatable  */

    function item_list_data($delivery_id = 0) {
        $this->access_only_allowed_members();

        $list_data = $this->Delivery_items_model->get_details(array("delivery_id" => $delivery_id))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of delivery item list table */

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
            modal_anchor(get_uri("deliverys/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_delivery'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("deliverys/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of delivery item */

    function get_delivery_item_suggestion() {
        $key = @$_REQUEST["q"];
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_delivery_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($delivery_id = 0, $show_close_preview = false) {

        $view_data = array();

        if ($delivery_id) {

            $delivery_data = get_delivery_making_data($delivery_id);
            $this->_check_delivery_access_permission($delivery_data);

            //get the label of the delivery
            $delivery_info = get_array_value($delivery_data, "delivery_info");
            $delivery_data['delivery_status_label'] = $this->_get_delivery_status_label($delivery_info);

            $view_data['delivery_preview'] = prepare_delivery_pdf($delivery_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['delivery_id'] = $delivery_id;
            
            $this->template->rander("deliverys/delivery_preview", $view_data);
        } else {
            show_404();
        }
    }

    function download_pdf($delivery_id = 0, $mode = "download") {
        if ($delivery_id) {
            $delivery_data = get_delivery_making_data($delivery_id);
            $this->_check_delivery_access_permission($delivery_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid delivery data. Prepare the view.

            prepare_delivery_pdf($delivery_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_delivery_access_permission($delivery_data) {
        //check for valid delivery
        if (!$delivery_data) {
            show_404();
        }

        //check for security
        $delivery_info = get_array_value($delivery_data, "delivery_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $delivery_info->client_id) {
                redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function get_delivery_status_bar($delivery_id = 0) {
        $this->access_only_allowed_members();

        $view_data["delivery_info"] = $this->Deliverys_model->get_details(array("id" => $delivery_id))->row();
        $view_data['delivery_status_label'] = $this->_get_delivery_status_label($view_data["delivery_info"]);
        $this->load->view('deliverys/delivery_status_bar', $view_data);
    }

    function send_delivery_modal_form($delivery_id) {
        $this->access_only_allowed_members();

        if ($delivery_id) {
            $options = array("id" => $delivery_id);
            $delivery_info = $this->Deliverys_model->get_details($options)->row();
            $view_data['delivery_info'] = $delivery_info;

            $is_lead = $this->input->post('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $delivery_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $delivery_info->client_id);
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

            $email_template = $this->Email_templates_model->get_final_template("delivery_sent");

            $parser_data["delivery_ID"] = $delivery_info->id;
            $parser_data["CONTACT_FIRST_NAME"] = $contact_first_name;
            $parser_data["CONTACT_LAST_NAME"] = $contact_last_name;
            $parser_data["PROJECT_TITLE"] = $delivery_info->project_title;
            $parser_data["delivery_URL"] = get_uri("deliverys/preview/" . $delivery_info->id);
            $parser_data['SIGNATURE'] = $email_template->signature;
            $parser_data["LOGO_URL"] = get_logo_url();

            $view_data['message'] = $this->parser->parse_string($email_template->message, $parser_data, TRUE);
            $view_data['subject'] = $email_template->subject;

            $this->load->view('deliverys/send_delivery_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function send_delivery() {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $delivery_id = $this->input->post('id');

        $contact_id = $this->input->post('contact_id');
        $cc = $this->input->post('delivery_cc');

        $custom_bcc = $this->input->post('delivery_bcc');
        $subject = $this->input->post('subject');
        $message = decode_ajax_post_data($this->input->post('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $delivery_data = get_delivery_making_data($delivery_id);
        $attachement_url = prepare_delivery_pdf($delivery_data, "send_email");

        $default_bcc = get_setting('send_delivery_bcc_to');
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
            if ($this->Deliverys_model->save($status_data, $delivery_id)) {
                echo json_encode(array('success' => true, 'message' => lang("delivery_sent_message"), "delivery_id" => $delivery_id));
            }
            // delete the temp delivery
            if (file_exists($attachement_url)) {
                unlink($attachement_url);
            }
        } else {
            echo json_encode(array('success' => false, 'message' => lang('error_occurred')));
        }
    }

    //update the sort value for delivery item
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
                $this->Delivery_items_model->save($data, $id);
            }
        }
    }
	
	
	
    function view($delivery_id = 0) {
		
	
        $this->access_only_allowed_members();
 
        if ( $delivery_id ) {
//echo 'dsfaafsd';exit;
            $view_data = get_delivery_making_data($delivery_id);
        // arr($view_data);exit;

            if ( $view_data ) {
				
                $view_data['delivery_status_label'] = $this->_get_delivery_status_label($view_data["delivery_info"]);
                $view_data['delivery_status'] = $this->_get_delivery_status_label($view_data["delivery_info"], true);

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $view_data["can_create_projects"] = $this->can_create_projects();

                $view_data["delivery_id"] = $delivery_id;

				$param['id'] = $delivery_id;
				
				$param['tbName'] = $this->className;
				$view_data["proveButton"] = $this->dao->getProveButton( $param );
				
				///$param['status'] = $this->_get_delivery_status_label( $view_data )


                $this->template->rander( "". $this->className ."/view", $view_data );
				
				
            } else {
                show_404();
            }
        }
    }
	
    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table( "deliverys", $this->login_user->is_admin, $this->login_user->user_type );

        $options = array(
            "status" => $this->input->post("status"),
            "start_date" => $this->input->post("start_date"),
            "end_date" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields
        );

        $list_data = $this->Deliverys_model->get_details( $options, $this->getRolePermission )->result();
        // var_dump($list_data);exit;
        
		$result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row( $data, $custom_fields );
            // var_dump($result);exit;
        }

        echo json_encode( array( "data" => $result ) );
    }
	
	
    private function _make_row( $data, $custom_fields ) {
        $delivery_url = "";
        if ($this->login_user->user_type == "staff") {
            $delivery_url = anchor(get_uri("deliverys/view/" . $data->id), $data->doc_no);
        } else {
            //for client client
            $delivery_url = anchor(get_uri("deliverys/preview/" . $data->id), $data->doc_no);
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }


        $row_data = array(
            $delivery_url,
            $client,
            $data->delivery_date,
            format_to_date($data->delivery_date, false),
            // to_currency($data->delivery_value, $data->currency_symbol),
			$this->_get_delivery_status_label( $data ),
        );
		 //$data->status
        //  var_dump($row_data);exit;
		
//
        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $row_data[] = modal_anchor(get_uri("deliverys/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_delivery'), "data-post-id" => $data->id))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_delivery'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("deliverys/delete"), "data-action" => "delete"));

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
        $model_info = $this->Deliverys_model->get_one($this->input->post('id'));

        //check if order_id posted. if found order_id, so, we'll show the order info to copy the order 
        $order_id = $this->input->post('order_id');
        $view_data['order_id'] = $order_id;
        if ($order_id) {
            $order_info = $this->Orders_model->get_one($order_id);
            $now = get_my_local_time("Y-m-d");
            $model_info->delivery_date = $now;
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

        $delivery_request_id = $this->input->post('delivery_request_id');
        $view_data['delivery_request_id'] = $delivery_request_id;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_clients_and_leads_dropdown();

        //don't show clients dropdown for lead's delivery editing
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

        //clone delivery data
        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data['projects_suggestion'] = $suggestion;

        $view_data['project_id'] = $project_id;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("deliverys", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type )->result();
		
		
		
		
		///$view_data["delivery_total_summary"] = $this->Deliverys_model->get_delivery_total_summary($delivery_id );
		
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
				<label for="delivery_date" class=" col-md-3">'. lang('delivery_date') .'</label>
				<div class="col-md-9">
					'. form_input(array(
						"id" => "delivery_date",
						"name" => "delivery_date",
						"value" => $model_info->delivery_date,
						"class" => "form-control",
						"placeholder" => lang('delivery_date'),
						"autocomplete" => "off",
						"data-rule-required" => true,
						"data-msg-required" => lang("field_required"),
					)) .'
				</div>
			</div>      
            
		';
        
		
		
		

        $this->load->view( 'deliverys/modal_form', $view_data );
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
            "delivery_client_id" => "required|numeric",
            "delivery_date" => "required",
            "delivery_request_id" => "numeric"
        ));

        $client_id = $this->input->post('delivery_client_id');
        $id = $this->input->post('id');
        
        $keep = array();
        
        // foreach( $_REQUEST['pay_sps'] as $k => $v){

        //     if(!isset($_REQUEST['pay_types'][$k])){
        //         continue;
        //     }

        //     if(empty($_REQUEST['pay_sps'][$k])){
        //         continue;
        //     }
            
        //     $keep[$k] = array('pay_sps' => $v, 'pay_types' => $_REQUEST['pay_types'][$k]);
            
        // }
        
        // arr($keep);
        // exit;

        $delivery_data = array(
            "client_id" => $client_id,
            "delivery_date" => $this->input->post('delivery_date'),
            "project_id" => $this->input->post('delivery_project_id')		
        );
        
        // "deposit"
        if(isset($_REQUEST['chckDes'])){
            $delivery_data['pay_sps'] = json_encode($keep);
        }else{
            $delivery_data['pay_sps'] = '';
        }

        $is_clone = $this->input->post('is_clone');
        $delivery_request_id = $this->input->post('delivery_request_id');

        //delivery creation from delivery request
        //store the delivery request id for the first time only
        //don't copy delivery request id on cloning too
        if ($delivery_request_id && !$id && !$is_clone) {
            $delivery_data["delivery_request_id"] = $delivery_request_id;
        }

        $main_delivery_id = "";
        if ($is_clone && $id) {
            $main_delivery_id = $id; //store main delivery id to get items later
            $id = ""; //on cloning delivery, save as new
            //save discount when cloning
            $delivery_data["discount_amount"] = $this->input->post('discount_amount') ? $this->input->post('discount_amount') : 0;
            $delivery_data["discount_amount_type"] = $this->input->post('discount_amount_type') ? $this->input->post('discount_amount_type') : "percentage";
            $delivery_data["discount_type"] = $this->input->post('discount_type') ? $this->input->post('discount_type') : "before_tax";
        }

        $delivery_id = $this->Deliverys_model->save($delivery_data, $id);
        if ($delivery_id) {

            if ($is_clone && $main_delivery_id) {
                //add delivery items

                save_custom_fields("deliverys", $delivery_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a delivery

                $delivery_items = $this->Delivery_items_model->get_all_where(array("delivery_id" => $main_delivery_id, "deleted" => 0))->result();

                foreach ($delivery_items as $delivery_item) {
                    //prepare new delivery item data
                    $delivery_item_data = (array) $delivery_item;
                    unset($delivery_item_data["id"]);
                    $delivery_item_data['delivery_id'] = $delivery_id;

                    $delivery_item = $this->Delivery_items_model->save($delivery_item_data);
                }
            } else {
                save_custom_fields("deliverys", $delivery_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_order? copy all items from order
            $copy_items_from_order = $this->input->post("copy_items_from_order");
            if ($copy_items_from_order) {
                $order_items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->result();

                foreach ($order_items as $data) {
                    $delivery_item_data = array(
                        "delivery_id" => $delivery_id,
                        "title" => $data->title ? $data->title : "",
                        "description" => $data->description ? $data->description : "",
                        "quantity" => $data->quantity ? $data->quantity : 0,
                        "unit_type" => $data->unit_type ? $data->unit_type : "",
                        "rate" => $data->rate ? $data->rate : 0,
                        "total" => $data->total ? $data->total : 0,
                    );

                    $this->Delivery_items_model->save($delivery_item_data);
                }
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($delivery_id), 'id' => $delivery_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }
	
	
}

/* End of file deliverys.php */
/* Location: ./application/controllers/deliverys.php */