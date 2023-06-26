<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class roles extends MY_Controller {

    function __construct() {

        parent::__construct();

        $this->access_only_admin();

		$this->load->model( 'db_model' );
        $this->load->model( 'Permission_m' );
        $this->load->model( 'Note_types_model' );

		$this->dao = $this->db_model;
		//arr();

		//exit;

		$config_id = 184;
		$this->config_ = getConfig_( $config_id );

    }

    //load the role view
    function index() {

//echo $view_data['test'];
       //$this->load->view('roles/index', $view_data);
        $this->template->rander("roles/index");
    }


    //load the role add/edit modal
    function modal_form() {

        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Roles_model->get_one($this->input->post('id'));
        $view_data['roles_dropdown'] = array("" => "-") + $this->Roles_model->get_dropdown_list(array("title"), "id");
        $this->load->view('roles/modal_form', $view_data);
    }

   //save a role
    function save() {
        validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));

        $id = $this->input->post('id');
        $copy_settings = $this->input->post('copy_settings');
        $data = array(
            "title" => $this->input->post('title'),
        );

        if ($copy_settings) {
            $role = $this->Roles_model->get_one($copy_settings);
            $data["permissions"] = $role->permissions;
        }

        $save_id = $this->Roles_model->save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

   //delete or undo a role
    function delete() {
        validate_submitted_data(array(
            "id" => "numeric|required"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Roles_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Roles_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    //get role list data
    function list_data() {
        $list_data = $this->Roles_model->get_details()->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get a row of role list
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Roles_model->get_details($options)->row();
        return $this->_make_row($data);
    }

    //make a row of role list table
    private function _make_row($data) {
        return array("<a href='#' data-id='$data->id' class='role-row link'>" . $data->title . "</a>",
            "<a class='edit'><i class='fa fa-check' ></i></a>" . modal_anchor(get_uri("roles/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "", "title" => lang('edit_role'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_role'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("roles/delete"), "data-action" => "delete"))
        );
    }

	//
	//
	function getTable( $datas, $config = array() ) {

		$status = 'ready';
		$status = 'edit';
		//$status = 'add';

		$config_id = 184;
		$config = getConfig_( $config_id );


		$config->columns['role_id']->show = 0;
		$config->columns['table_name']->show = 1;

		$r = 0;
		foreach( $datas as $kg => $vg ) {

			//arr( $vg );


			$tds = array();

			$tds['ready'][] = '<td class="">'. ( $r + 1  ) .'</td>';
			$tds['edit'][] = '<td class="">'. ( $r + 1  ) .'</td>';

			foreach( $config->columns as $kc => $vc ) {

				if( empty( $vc->show ) ) {
					continue;
				}

			//arr( $vc );
				if( $kc == 'table_name' ) {
					$tds['edit'][] = '<td class="'. $vc->a .'">'. lang( $vg->class_name ) .'</td>';
				}
				else {
					$tds['edit'][] = '<td class="'. $vc->a .'">'. getInput( $vc, $vg->$kc, '', 'permisissions[' . $vg->class_name .']['. $kc .']' ) .'</td>';
				}
			}

			$tds['ready'][] = '
				<td class="C">

					<a data-id="'. $vg->id .'" class="delete-row" title="ลบข้อมูล"><i class="fa fa-trash-o"></i></a> &nbsp;&nbsp;

					<a data-id="'. $vg->id .'" class="edit-row" title="แก้ไขข้อมูล"><i class="fa fa-edit"></i></a>

				</td>
			';

			$trs['ready'][$vg->id] = '<tr>'. implode( '', $tds['ready'] ) .'</tr>';

			$trs['edit'][] = '<tr>'. implode( '', $tds['edit'] ) .'</tr>';

			++$r;
		}


		$tds = array();

		$tds['add'][] = '<td class=""></td>';
		$tds['ready'][] = '<td class=""></td>';
		foreach( $config->columns as $kc => $vc ) {

			if( empty( $vc->show ) ) {
				continue;
			}

			$val = '';


			$tds['add'][] = '<td class="'. $vc->a .'">'. getInput( $vc, $val, '', $kc ) .'</td>';
			$tds['ready'][] = '<td class="'. $vc->a .'"></td>';
		}

		$tds['add'][] = '
			<td class="'. @$vc->a .'">
				<a data-id="rrrrrrrrrrr" class="cancel-row" title="ยกเลิก"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				<a class="confirm-row"><i class="fa fa-save"></i></a>

			</td>
		';

		$tds['ready'][] = '
			<td class="'. @$vc->a .'"><a class="add-new-row"><i class="fa fa-plus-circle"></i></a></td>
		';

		$trs['ready']['rrrrrrrrrrr'] = '<tr>'. implode( '', $tds['ready'] ) .'</tr>';
		$trs['add'] = '<tr>'. implode( '', $tds['add'] ) .'</tr>';

		$trHead = array();
		$getTrOnStep = getTrOnStep( $config->columns );
		$maxRow = count( $getTrOnStep );
		foreach ( $getTrOnStep as $kl => $vl ) {

			$ths = array();
			if ( $kl == 0 )
				$ths[] = '<th style="width: 5%;" rowspan="'. $maxRow .'">#</th>';

			foreach ( $vl as $kt => $vt ) {

				if ( empty( $vt['label'] ) )
					continue;

				$w = 'width: '. $vt['w'] .'%;';
				if( $vt['w'] == 0 ) {

					$w = 'width: auto;';
				}
				$ths[] = '<th style="'. $w .'" colspan="'. $vt['merg'] .'" rowspan="'. $vt['h'] .'">'. $vt['label'] .'</th>';

			}

			//if ( $kl == 0 )
			//	$ths[] = '<th rowspan="'. $maxRow .'"></th>';

			$trHead[] = '<tr>'. implode( '', $ths ) .'</tr>';

		}


		return '
			<link href="'. base_url( 'assets/css/flexigrid.pack.css?rand='. rand() .'' ) .'" rel="stylesheet"/>
			<table class="flexme3">'. implode( '', $trHead ) .''. implode( '', $trs[$status] ) .'</table>
			<script>

				trs = '. json_encode( $trs ) .'

			</script>

		';
	}

    //save permissions of a role
    function save_permissions() {
		$post = $this->input->post();
		
		$bom_restock_read_self = @$this->input->post("bom_restock_read_self");
		$bom_restock_read = @$this->input->post("bom_restock_read");

		$hide_team_members_list = @$this->input->post("hide_team_members_list");
		$team_member_update_permission = @$this->input->post("team_member_update_permission");
		$team_member_update = (($team_member_update_permission=='all') || ($team_member_update_permission=='specific'));
		$can_view_team_members_social_links = @$this->input->post("can_view_team_members_contact_info");
		$can_view_team_members_contact_info = @$this->input->post("can_view_team_members_social_links");

		$post['permisissions']['stock']['view_row'] = get_array_value($post, 'bom_material_read');
		$post['permisissions']['team_members']['view_row'] = ($hide_team_members_list || $team_member_update || $can_view_team_members_social_links || $can_view_team_members_contact_info);
		$post['permisissions']['team_members']['add_row'] = $post['permisissions']['team_members']['edit_row'] = $team_member_update;

		foreach( $post['permisissions'] as $kp => $vt ) {
			//log_message("error", json_encode($kp)."->".json_encode($vt));
			$keep = array();
			foreach( $this->config_->columns as $kc => $vc ) {
			//foreach( $vt as $kc => $vc ) {
				$val = 0;
				if( isset( $vt[$kc] ) ) {
					$val = $vt[$kc];
				}
				//$keep[$kc] = "'". $vc ."' as ". $kc ."";
				$keep[$kc] = "'". $val ."' as ". $kc ."";
			}

			$val = 0;
			$kc = 'prove_labels';
			
			$sql = "
				DELETE FROM `prove_labels` 
				WHERE `role_id` = ".  $post['id'] ." 
				AND `table_name` = '". $kp ."'
			";
			
			
			$this->dao->execDatas( $sql );
			
			$keep['role_id'] = "". $post['id'] ." as role_id";
			$keep['table_name'] = "'". $kp ."' as table_name";

			$sql = "
				replace INTO role_permission ( ". implode( ',', array_keys( $keep ) ) ." )

				SELECT
					". implode( ',', $keep ) ."

			";
	
			
			$this->dao->execDatas( $sql );
		}
		
        validate_submitted_data(array(
            "id" => "numeric|required"
        ));

        $id = $this->input->post('id');

        $access_note = false;
        $access_note_specific = null;
        $add_note = false;
        $update_note = false;
        $access_product_item_formula = false;
        $create_product_item = false;

        $access_material_request = false;
        $create_material_request = false;
        $update_material_request = false;
        $delete_material_request = false;
        $approve_material_request = false;
        $access_purchase_request = false;
        $create_purchase_request = false;
        $update_purchase_request = false;
        $delete_purchase_request = false;
        $approve_purchase_request = false;

        if($this->input->post('access_note') != false) $access_note = $this->input->post('access_note');
        if($access_note === "specific") $access_note_specific = $this->input->post('access_note_specific');
        if($this->input->post('add_note') == "Y") $add_note = true;
        if($this->input->post('update_note') == "Y") $update_note = true;

        if($this->input->post('access_product_item_formula') == "Y") $access_product_item_formula = true;
        if($this->input->post('create_product_item') == "Y") $create_product_item = true;

        $accounting_quotation_access = $this->input->post('accounting_quotation_access') == "Y" ? true : false;
        $accounting_billing_note_access = $this->input->post('accounting_billing_note_access') == "Y" ? true : false;
        $accounting_invoice_access = $this->input->post('accounting_invoice_access') == "Y" ? true : false;
        $accounting_receipt_access = $this->input->post('accounting_receipt_access') == "Y" ? true : false;

        if($this->input->post('access_material_request') == "Y") $access_material_request = true;
        if($this->input->post('create_material_request') == "Y") $create_material_request = true;
        if($this->input->post('update_material_request') == "Y") $update_material_request = true;
        if($this->input->post('delete_material_request') == "Y") $delete_material_request = true;
        if($this->input->post('approve_material_request') == "Y") $approve_material_request = true;

        if($this->input->post('access_purchase_request') == "Y") $access_purchase_request = true;
        if($this->input->post('create_purchase_request') == "Y") $create_purchase_request = true;
        if($this->input->post('update_purchase_request') == "Y") $update_purchase_request = true;
        if($this->input->post('delete_purchase_request') == "Y") $delete_purchase_request = true;
        if($this->input->post('approve_purchase_request') == "Y") $approve_purchase_request = true;

        $leave = $this->input->post('leave_permission');
        $leave_specific = "";
        if ($leave === "specific") {
            $leave_specific = $this->input->post('leave_permission_specific');
        }

        $attendance = $this->input->post('attendance_permission');
        $attendance_specific = "";
        if ($attendance === "specific") {
            $attendance_specific = $this->input->post('attendance_permission_specific');
        }

        $invoice = $this->input->post('invoice_permission');
        $estimate = $this->input->post('estimate_permission');
        $expense = $this->input->post('expense_permission');
        $order = $this->input->post('order_permission');
        $purchaserequest = $this->input->post('purchaserequest_permission');
        $payment_voucher = $this->input->post('payment_voucher_permission');
        $client = $this->input->post('client_permission');
        $lead = $this->input->post('lead_permission');

        $ticket = $this->input->post('ticket_permission');

        $ticket_specific = "";
        if ($ticket === "specific") {
            $ticket_specific = $this->input->post('ticket_permission_specific');
        }

        $can_manage_all_projects = $this->input->post('can_manage_all_projects');
        $can_create_projects = $this->input->post('can_create_projects');
        $can_edit_projects = $this->input->post('can_edit_projects');
        $can_delete_projects = $this->input->post('can_delete_projects');

        $can_add_remove_project_members = $this->input->post('can_add_remove_project_members');

        $can_create_tasks = $this->input->post('can_create_tasks');
        $can_edit_tasks = $this->input->post('can_edit_tasks');
        $can_delete_tasks = $this->input->post('can_delete_tasks');
        $can_comment_on_tasks = $this->input->post('can_comment_on_tasks');
        $show_assigned_tasks_only = $this->input->post('show_assigned_tasks_only');
        $can_update_only_assigned_tasks_status = $this->input->post('can_update_only_assigned_tasks_status');

        $can_create_milestones = $this->input->post('can_create_milestones');
        $can_edit_milestones = $this->input->post('can_edit_milestones');
        $can_delete_milestones = $this->input->post('can_delete_milestones');

        $can_delete_files = $this->input->post('can_delete_files');

        $announcement = $this->input->post('announcement_permission');
        $help_and_knowledge_base = $this->input->post('help_and_knowledge_base');

        $can_view_team_members_contact_info = $this->input->post('can_view_team_members_contact_info');
        $can_view_team_members_social_links = $this->input->post('can_view_team_members_social_links');
        $team_member_update_permission = $this->input->post('team_member_update_permission');


		$team_member_update_permission_specific = $this->input->post('team_member_update_permission_specific');

        $timesheet_manage_permission = $this->input->post('timesheet_manage_permission');


		$timesheet_manage_permission_specific = $this->input->post('timesheet_manage_permission_specific');

        $disable_event_sharing = $this->input->post('disable_event_sharing');

        $hide_team_members_list = $this->input->post('hide_team_members_list');

        $can_delete_leave_application = $this->input->post('can_delete_leave_application');

        $message_permission = "";
        $message_permission_specific = "";
        if ($this->input->post('message_permission_no')) {
            $message_permission = "no";
        } else if ($this->input->post('message_permission_specific_checkbox')) {
            $message_permission = "specific";
            $message_permission_specific = $this->input->post("message_permission_specific");
        }

        $permissions = array(
            "access_note"=>$access_note,
            "access_note_specific"=>$access_note_specific,
            "add_note"=>$add_note,
            "update_note"=>$update_note,
        	"access_product_item_formula"=>$access_product_item_formula,
        	"create_product_item"=>$create_product_item,
            "accounting"=>[
                "quotation"=>["access"=>$accounting_quotation_access],
                "billing_note"=>["access"=>$accounting_billing_note_access],
                "invoice"=>["access"=>$accounting_invoice_access],
                "receipt"=>["access"=>$accounting_receipt_access]
            ],
        	"access_material_request"=>$access_material_request,
        	"create_material_request"=>$create_material_request,
        	"update_material_request"=>$update_material_request,
            "delete_material_request"=>$delete_material_request,
        	"approve_material_request"=>$approve_material_request,
        	"access_purchase_request"=>$access_purchase_request,
            "create_purchase_request"=>$create_purchase_request,
            "update_purchase_request"=>$update_purchase_request,
            "delete_purchase_request"=>$delete_purchase_request,
            "approve_purchase_request"=>$approve_purchase_request,
            "leave" => $leave,
            "leave_specific" => $leave_specific,
            "attendance" => $attendance,
            "attendance_specific" => $attendance_specific,
            "invoice" => $invoice,
            "estimate" => $estimate,
            "expense" => $expense,
            "order" => $order,
            "purchaserequest" =>  $purchaserequest,
            "payment_voucher" => $payment_voucher,
            "client" => $client,
            "lead" => $lead,
			"stock" => ($bom_restock_read_self || $bom_restock_read),
            "ticket" => $ticket,
            "ticket_specific" => $ticket_specific,
            "announcement" => $announcement,
            "help_and_knowledge_base" => $help_and_knowledge_base,
            "can_manage_all_projects" => $can_manage_all_projects,
            "can_create_projects" => $can_create_projects,
            "can_edit_projects" => $can_edit_projects,
            "can_delete_projects" => $can_delete_projects,
            "can_add_remove_project_members" => $can_add_remove_project_members,
            "can_create_tasks" => $can_create_tasks,
            "can_edit_tasks" => $can_edit_tasks,
            "can_delete_tasks" => $can_delete_tasks,
            "can_comment_on_tasks" => $can_comment_on_tasks,
            "show_assigned_tasks_only" => $show_assigned_tasks_only,
            "can_update_only_assigned_tasks_status" => $can_update_only_assigned_tasks_status,
            "can_create_milestones" => $can_create_milestones,
            "can_edit_milestones" => $can_edit_milestones,
            "can_delete_milestones" => $can_delete_milestones,
            "can_delete_files" => $can_delete_files,
            "can_view_team_members_contact_info" => $can_view_team_members_contact_info,
            "can_view_team_members_social_links" => $can_view_team_members_social_links,
            "team_member_update_permission" => $team_member_update_permission,
            "team_member_update_permission_specific" => $team_member_update_permission_specific,
            "timesheet_manage_permission" => $timesheet_manage_permission,
            "timesheet_manage_permission_specific" => $timesheet_manage_permission_specific,
            "disable_event_sharing" => $disable_event_sharing,
            "hide_team_members_list" => $hide_team_members_list,
            "can_delete_leave_application" => $can_delete_leave_application,
            "message_permission" => $message_permission,
            "message_permission_specific" => $message_permission_specific,
        );

		// START: BOM
        if(get_setting('module_stock') == '1'){
            $permissions['bom_supplier_read_self'] = $this->input->post("bom_supplier_read_self");
            $permissions['bom_supplier_read'] = $this->input->post("bom_supplier_read");
            $permissions['bom_supplier_create'] = $this->input->post("bom_supplier_create");
            $permissions['bom_supplier_update'] = $this->input->post("bom_supplier_update");
            $permissions['bom_supplier_delete'] = $this->input->post("bom_supplier_delete");

            $permissions['bom_material_read'] = $this->input->post("bom_material_read");
            $permissions['bom_material_read_production_name'] = $this->input->post("bom_material_read_production_name");
            $permissions['bom_material_create'] = $this->input->post("bom_material_create");
            $permissions['bom_material_update'] = $this->input->post("bom_material_update");
            $permissions['bom_material_delete'] = $this->input->post("bom_material_delete");

            $permissions['bom_restock_read_self'] = $bom_restock_read_self;
            $permissions['bom_restock_read'] = $bom_restock_read;
            $permissions['bom_restock_read_price'] = $this->input->post("bom_restock_read_price");
            $permissions['bom_restock_create'] = $this->input->post("bom_restock_create");
            $permissions['bom_restock_update'] = $this->input->post("bom_restock_update");
            $permissions['bom_restock_delete'] = $this->input->post("bom_restock_delete");
        }

        
        // END: BOM

		//arr(  );


		if( !empty( $post['permisissions']['settings']['view_row'] ) ) {

            $permissions['can_setting'] = 1;
		}
		
		//if( !empty( $post['permisissions']['tickets']['read_only'] ) ) {

           // $permissions['can_setting'] = 1;
		//}

        $data = array(
            "permissions" => serialize($permissions),
        );

        $save_id = $this->Roles_model->save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }





    //get permisissions of a role
    function permissions( $role_id ) {
		$config_id = 184;
		$config = getConfig_( $config_id );

        if ( $role_id ) {
            $view_data['model_info'] = $this->Roles_model->get_one($role_id);

            $view_data['members_and_teams_dropdown'] = json_encode(get_team_members_and_teams_select2_data_list());
            $ticket_types_dropdown = array();
            $ticket_types = $this->Ticket_types_model->get_all_where(array("deleted" => 0))->result();
            foreach ($ticket_types as $type) {
                $ticket_types_dropdown[] = array("id" => $type->id, "text" => $type->title);
            }
            $view_data['ticket_types_dropdown'] = json_encode($ticket_types_dropdown);

            $permissions = unserialize($view_data['model_info']->permissions);

            if (!$permissions) $permissions = [];

            $view_data['leave'] = get_array_value($permissions, "leave");
            $view_data['leave_specific'] = get_array_value($permissions, "leave_specific");
            $view_data['attendance_specific'] = get_array_value($permissions, "attendance_specific");

            $view_data['attendance'] = get_array_value($permissions, "attendance");
            $view_data['invoice'] = get_array_value($permissions, "invoice");
            $view_data['estimate'] = get_array_value($permissions, "estimate");
            $view_data['expense'] = get_array_value($permissions, "expense");
            $view_data['order'] = get_array_value($permissions, "order");
            $view_data['purchaserequest'] = get_array_value($permissions, "purchaserequest");
            $view_data['payment_voucher'] = get_array_value($permissions, "payment_voucher");
            $view_data['client'] = get_array_value($permissions, "client");
            $view_data['lead'] = get_array_value($permissions, "lead");

            $view_data['ticket'] = get_array_value($permissions, "ticket");
            $view_data['ticket_specific'] = get_array_value($permissions, "ticket_specific");

            $view_data['announcement'] = get_array_value($permissions, "announcement");
            $view_data['help_and_knowledge_base'] = get_array_value($permissions, "help_and_knowledge_base");

            $view_data['can_manage_all_projects'] = get_array_value($permissions, "can_manage_all_projects");
            $view_data['can_create_projects'] = get_array_value($permissions, "can_create_projects");
            $view_data['can_edit_projects'] = get_array_value($permissions, "can_edit_projects");
            $view_data['can_delete_projects'] = get_array_value($permissions, "can_delete_projects");

            $view_data['can_add_remove_project_members'] = get_array_value($permissions, "can_add_remove_project_members");

            $view_data['can_create_tasks'] = get_array_value($permissions, "can_create_tasks");
            $view_data['can_edit_tasks'] = get_array_value($permissions, "can_edit_tasks");
            $view_data['can_delete_tasks'] = get_array_value($permissions, "can_delete_tasks");
            $view_data['can_comment_on_tasks'] = get_array_value($permissions, "can_comment_on_tasks");
            $view_data['show_assigned_tasks_only'] = get_array_value($permissions, "show_assigned_tasks_only");
            $view_data['can_update_only_assigned_tasks_status'] = get_array_value($permissions, "can_update_only_assigned_tasks_status");

            $view_data['can_create_milestones'] = get_array_value($permissions, "can_create_milestones");
            $view_data['can_edit_milestones'] = get_array_value($permissions, "can_edit_milestones");
            $view_data['can_delete_milestones'] = get_array_value($permissions, "can_delete_milestones");

            $view_data['can_delete_files'] = get_array_value($permissions, "can_delete_files");

            $view_data['can_view_team_members_contact_info'] = get_array_value($permissions, "can_view_team_members_contact_info");
            $view_data['can_view_team_members_social_links'] = get_array_value($permissions, "can_view_team_members_social_links");
            $view_data['team_member_update_permission'] = get_array_value($permissions, "team_member_update_permission");
            $view_data['team_member_update_permission_specific'] = get_array_value($permissions, "team_member_update_permission_specific");

            $view_data['timesheet_manage_permission'] = get_array_value($permissions, "timesheet_manage_permission");
            $view_data['timesheet_manage_permission_specific'] = get_array_value($permissions, "timesheet_manage_permission_specific");

            $view_data['disable_event_sharing'] = get_array_value($permissions, "disable_event_sharing");

            $view_data['hide_team_members_list'] = get_array_value($permissions, "hide_team_members_list");

            $view_data['can_delete_leave_application'] = get_array_value($permissions, "can_delete_leave_application");

            $view_data['message_permission'] = get_array_value($permissions, "message_permission");
            $view_data['message_permission_specific'] = get_array_value($permissions, "message_permission_specific");
            
            $view_data['module_stock'] = true;

            $view_data['bom_supplier_read_self'] = get_array_value($permissions, "bom_supplier_read_self");
            $view_data['bom_supplier_read'] = get_array_value($permissions, "bom_supplier_read");
            $view_data['bom_supplier_create'] = get_array_value($permissions, "bom_supplier_create");
            $view_data['bom_supplier_update'] = get_array_value($permissions, "bom_supplier_update");
            $view_data['bom_supplier_delete'] = get_array_value($permissions, "bom_supplier_delete");

            $view_data['bom_material_read'] = get_array_value($permissions, "bom_material_read");
            $view_data['bom_material_read_production_name'] = get_array_value($permissions, "bom_material_read_production_name");
            $view_data['bom_material_create'] = get_array_value($permissions, "bom_material_create");
            $view_data['bom_material_update'] = get_array_value($permissions, "bom_material_update");
            $view_data['bom_material_delete'] = get_array_value($permissions, "bom_material_delete");

            $view_data['bom_restock_read_self'] = get_array_value($permissions, "bom_restock_read_self");
            $view_data['bom_restock_read'] = get_array_value($permissions, "bom_restock_read");
            $view_data['bom_restock_read_price'] = get_array_value($permissions, "bom_restock_read_price");
            $view_data['bom_restock_create'] = get_array_value($permissions, "bom_restock_create");
            $view_data['bom_restock_update'] = get_array_value($permissions, "bom_restock_update");
            $view_data['bom_restock_delete'] = get_array_value($permissions, "bom_restock_delete");


            $note_types_dropdown = array();
            $note_types = $this->Note_types_model->get_all_where(array("deleted" => 0))->result();
            foreach ($note_types as $ntype) {
                $note_types_dropdown[] = array("id" => $ntype->id, "text" => $ntype->title);
            }
            $view_data['note_types_dropdown'] = json_encode($note_types_dropdown);

            if(get_array_value($permissions, "access_note") === null) $view_data['access_note'] = "assigned_only";
            else $view_data['access_note'] = get_array_value($permissions, "access_note");
            $view_data['access_note_specific'] = get_array_value($permissions, "access_note_specific");
            $view_data['add_note'] = get_array_value($permissions, "add_note");
            $view_data['update_note'] = get_array_value($permissions, "update_note");

            $view_data['access_product_item_formula'] = get_array_value($permissions, "access_product_item_formula");
            $view_data['create_product_item'] = get_array_value($permissions, "create_product_item");

            $view_data['accounting']['quotation']['access'] = !isset($permissions["accounting"]["quotation"]) ? false : get_array_value($permissions["accounting"]["quotation"], "access");
            $view_data['accounting']['billing_note']['access'] = !isset($permissions["accounting"]["billing_note"]) ? false : get_array_value($permissions["accounting"]["billing_note"], "access");
            $view_data['accounting']['invoice']['access'] = !isset($permissions["accounting"]["invoice"]) ? false : get_array_value($permissions["accounting"]["invoice"], "access");
            $view_data['accounting']['receipt']['access'] = !isset($permissions["accounting"]["receipt"]) ? false : get_array_value($permissions["accounting"]["receipt"], "access");

            $view_data['access_material_request'] = get_array_value($permissions, "access_material_request");
            $view_data['create_material_request'] = get_array_value($permissions, "create_material_request");
            $view_data['update_material_request'] = get_array_value($permissions, "update_material_request");
            $view_data['delete_material_request'] = get_array_value($permissions, "delete_material_request");
            $view_data['approve_material_request'] = get_array_value($permissions, "approve_material_request");

            $view_data['access_purchase_request'] = get_array_value($permissions, "access_purchase_request");
            $view_data['create_purchase_request'] = get_array_value($permissions, "create_purchase_request");
            $view_data['update_purchase_request'] = get_array_value($permissions, "update_purchase_request");
            $view_data['delete_purchase_request'] = get_array_value($permissions, "delete_purchase_request");
            $view_data['approve_purchase_request'] = get_array_value($permissions, "approve_purchase_request");
          
			$sql = "
				SELECT
					l.class_name,
					rp.*,
					GROUP_CONCAT( pl.`label_id` ) as prove_labels
				FROM left_menu l
				LEFT JOIN role_permission rp ON l.class_name = rp.table_name AND rp.role_id = ". $role_id ."
				LEFT JOIN prove_labels pl ON rp.table_name = pl.table_name AND pl.role_id = rp.role_id
				WHERE
				(
					l.left_menu = 1
					AND l.every_body = 0
				)
				OR
				l.id IN ( 4 )
				GROUP BY l.class_name

				ORDER BY
					l.order_number ASC
			";
			// var_dump($sql); 
			// $tryFetchAll = $this->dao->fetchAll( $sql );
			// var_dump($tryFetchAll);
			// exit;

			$view_data['lis'] = $this->getTable( $this->dao->fetchAll( $sql ) );

			$noNeedProveClass = array( 'items','leads', 'clients', 'income_vs_expenses', 'notes', 'invoice_payments', 'settings' );
			$view_data['ticketReadonly'] = '
				<div class="form-group">
				
					<input value="1" type="hidden" name="permisissions[tickets][view_row]">
					<input value="1" type="checkbox" name="permisissions[tickets][read_only]">
					<span class="require"></span> อ่านได้อย่างเดียว</div>';

			$uls = array();
			foreach( $this->dao->fetchAll( $sql ) as $ka => $vg ) {
				/*if( $vg->class_name == 'items' ) {
					continue;
				}*/
				if($vg->class_name == 'notes') continue;

				if( $vg->class_name == 'leaves' ) {

					continue;
				}
				if( $vg->class_name == 'help_articles' ) {

					continue;
				}
				if( $vg->class_name == 'tickets' ) {
 
					if( !empty( $vg->read_only ) ) {
						$view_data['ticketReadonly'] = '
						<div class="form-group"><input value="1" type="hidden" name="permisissions[tickets][view_row]">
						<input value="1" checked type="checkbox" name="permisissions[tickets][read_only]" id="permisissions[tickets][read_only]"><span class="require"></span> อ่านได้อย่างเดียว</div>';
					}
					continue;
				}
				if( $vg->class_name == 'announcements' ) {

					continue;
				}
				if( $vg->class_name == 'stock' ) {

					continue;
				}
				if( $vg->class_name == 'events' ) {

					continue;
				}
				if( $vg->class_name == 'clients' ) {

					continue;
				}



				$lis = array();
				foreach( $config->columns as $kc => $vc ) {

					if( empty( $vc->show ) ) {
						continue;
					}
					else if( $kc == 'table_name' ) {
						continue;
					}
					else if( $kc == 'role_id' ) {
						continue;
					}



					$arr = array();

					$setLabels = false;

					if( $vg->class_name == 'leaves' ) {

						if( $kc == 'view_row' ) {

							$arr[0] = array( 'label' => 'ไม่ใช่' );

							$arr[2] = array( 'label' => 'ใช่สำหรับทุกสมาชิก' );

							$arr[1] = array( 'label' => 'ใช่เฉพาะสมาชิกหรือทีม' );

							$keep = array();

							foreach( $arr as $kl => $vl ) {

								$checked = '';
								if( $kl == $vg->$kc ) {

									$checked = 'checked';
								}

								$keep[] = '<label style="margin-right: 10px;"><input  name="permisissions[' . $vg->class_name .']['. $kc .']" '. $checked .' type="radio" value="'. $kl.'" /> '.

								str_replace( '[val]', lang( $vg->class_name ), $vl['label'] ) .'</label>';
							}

							$lis[] = '<div style="padding-left: 0px;">'. implode( ' <br>', $keep ) .'</div>';

						}
						else {
							$lis[] = '
								<div>'. getInput( $vc, $vg->$kc, '', 'permisissions[' . $vg->class_name .']['. $kc .']' ) .' สามารถลบใบลาได้</div>

							';

						}
					}
					
				//	else if( $vg->class_name == 'settings' || $vg->class_name == 'income_vs_expenses' ) {
					else if( $vg->class_name == 'settings'   ) {

						if( $kc == 'view_row' ) {

							$arr[0] = array( 'label' => 'ไม่ใช่' );

							$arr[2] = array( 'label' => 'ใช่[val]ทั้งหมด' );

							$keep = array();

							

							foreach( $arr as $kl => $vl ) {
								$checked = '';

								if( $kl == $vg->$kc ) {

									$checked = 'checked';
								}

								$keep[] = '<label style="margin-right: 10px;"><input  name="permisissions[' . $vg->class_name .']['. $kc .']" '. $checked .' type="radio" value="'. $kl.'" /> '.



								str_replace( '[val]', lang( $vg->class_name ), $vl['label'] ) .'</label>';
							}

							$lis[] = '<div style="padding-left: 0px;">'. implode( ' <br>', $keep ) .'</div>';

						}

					}
					else {

						$setLabels = true;
						$noNeedProve = false;
						$noNeedProve = false;

						if( $kc == 'view_row' ) {

							$arr[0] = array( 'label' => 'ไม่ใช่' );
							$arr[1] = array( 'label' => 'ใช่เฉพาะ[val]ของตัวเองเท่านัน' );
							$arr[2] = array( 'label' => 'ใช่[val]ทั้งหมด' );

							$keep = array();

							foreach( $arr as $kl => $vl ) {
								$checked = '';

								if( $kl == $vg->$kc ) {

									$checked = 'checked';
								}

								$keep[] = '<label style="margin-right: 10px;"><input  name="permisissions[' . $vg->class_name .']['. $kc .']" '. $checked .' type="radio" value="'. $kl.'" /> '.



								str_replace( '[val]', lang( $vg->class_name ), $vl['label'] ) .'</label>';
							}

							$lis[] = '<div style="padding-left: 0px;">'. implode( ' <br>', $keep ) .'</div>';

						}
						else {
							
							if( $kc == 'prove_row' ) {
								
								if( !in_array( $vg->class_name, $noNeedProveClass ) ) {
									
									$lis[] = '
									
										<div>'. getInput( $vc, $vg->$kc, '', 'permisissions[' . $vg->class_name .']['. $kc .']' ) .' '. $vc->label .'</div>

									';
								}
								
							}
							else {
								
								if( $vg->class_name == 'notes' ) {
									
									//$vc->label = 'ไม่สามารถลบข้อมูลได้';
								}
								
								$lis[] = '
									<div>'. getInput( $vc, $vg->$kc, '', 'permisissions[' . $vg->class_name .']['. $kc .']' ) .' '. $vc->label .'</div>

								';
							}

						}
					}
				}

		
				//if( !in_array( $vg->class_name, $noNeedProveClass ) ) {
					
					$sql = "
					
						SELECT 
							lt.`label_id` as id,
							l.title as text
						FROM `label_table` lt
						INNER JOIN labels l ON lt.label_id = l.id

						WHERE `tbName` = '". $vg->class_name ."'

						GROUP BY 	
							lt.`label_id`
				
					";
					
					$label_suggestions = $this->dao->fetchAll( $sql );
				
					


					$name = 'permisissions['. $vg->class_name .'][prove_labels]';


					$val = $vg->prove_labels;
					$input = form_input( array(

						"name" => $name,
						"value" => $val,
						"class" => "form-control invoice_labels-". $vg->class_name ."",
						"placeholder" => 'เลือก'
					));
					
					$ssdssds = '';
					if( !in_array( $vg->class_name, $noNeedProveClass ) ) {
						
						
						$ssdssds = '<a href="#" class="btn btn-default mb0" title="จัดการคำกำกับ'. lang( $vg->class_name ) .'" data-post-type="'. $vg->class_name .'" data-act="ajax-modal" data-title="จัดการคำกำกับ'. lang( $vg->class_name ) .'" data-action-url="'. base_url( 'index.php/labels/modal_form' ) .'"><i class="fa fa-tags"></i> จัดการคำกำกับ'. lang( $vg->class_name ) .'</a>';
						
						
						if( in_array( $vg->class_name, array( 'items', 'invoices', 'expenses' ) ) ) {
							//$ssdssds = '';
						}


						$lis[] = '

							<div class="form-group">
								<label class="">

									จำกัดสิทธิ์การอนุมัติ <b>'. lang( $vg->class_name ) .'</b> ตามหมวด' . lang( $vg->class_name ) .'
									'. $input .'
								</label>

							</div>
							<script>
								$( function() {
									$( \'[name="'. $name .'"]\' ).select2({multiple: true, data:  '. json_encode( $label_suggestions ) .'});

								});
							</script>
							'. $ssdssds .'
							


						';
					
					}
				//}		
						
				if( $vg->class_name == 'orders' ) {
					$title = 'สามารถเข้าถึงคำสั่งซื้อ';
				}		
				else {
					
					$title = 'สามารถเข้าถึง'. lang( $vg->class_name );
				}
				$uls[] = '
					<li>
						<h5 style="color:red;">'. $title .'</h5>  

						<div class="permission-list">
							'. implode( '', $lis  ) .'

						</div>

					</li>
				';


			}


			$view_data['lis'] = implode( '', $uls );

            $this->load->view( "roles/permissions", $view_data );
        }
    }
}

/* End of file roles.php */
/* Location: ./application/controllers/roles.php */