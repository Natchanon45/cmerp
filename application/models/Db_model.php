<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Db_model extends CI_Model {


	function getBombInputs( $doc_id = NULL ,$datas = NULL) {

		//var_dump($this->getRolePermission['table_name']);exit;

		$val = NULL;
		if( !empty( $this->getRolePermission['myDocId'] ) ) {

			$sql = "

				SELECT
					GROUP_CONCAT( label_id ) as label_ids
				FROM label_table
				WHERE doc_id = ". $this->getRolePermission['myDocId'] ."
				AND tbName = '". $this->getRolePermission['table_name'] ."'
			";

			foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

				$val = $va->label_ids;
			}
			
			//arr( $sql );
			$doc_id = $this->getRolePermission['myDocId'];
		}
		
		if( $this->getRolePermission['table_name'] == 'notes' ) {
//arr( $_POST['id'] );
			$sql = "

				SELECT
					GROUP_CONCAT( label_id ) as label_ids
				FROM label_table
				WHERE doc_id = ". $doc_id ."
				AND tbName = 'notes'
			";


			foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

				$val = $va->label_ids;
			}
			
		//arr( $sql );
			$doc_id = $doc_id;
		}
		
		if( $this->getRolePermission['table_name'] == 'notes' ) {
			$sql = "
				SELECT
					id,
					title as text
				FROM labels WHERE context = 'note'
				AND deleted = 0
			";
		}
		else {
			$sql = "
				SELECT
					id,
					title as text
				FROM labels WHERE context = '". $this->getRolePermission['table_name'] ."'
				AND deleted = 0
			";
		}

//arr( $sql );exit;
		$label_suggestions = $this->dao->fetchAll( $sql );
//arr( $label_suggestions);
		if($this->getRolePermission['table_name'] != 'receipts' && $this->getRolePermission['table_name'] != 'orders'){
			//var_dump($this->getRolePermission['table_name']);exit;
			$inputs[] = '
			<div class="form-group">
				<label for="invoice_labels" class=" col-md-3">'. lang( 'labels' ) .'</label>
				<div class=" col-md-9">
					'. form_input(array(
						"name" => "labels",
						"value" => $val,
						"class" => "form-control dsasdadsfdfsaadfsadsfdsafasd",
						"placeholder" => lang( 'labels' )
					)).'
				</div>
			</div>

			<script>

			$( function() {

				 $( \'[name="labels"]\' ).select2({multiple: true, data: '. json_encode( $label_suggestions ) .'});
			});
			</script>
		';
		
		}else if($this->getRolePermission['table_name'] == 'orders'){
			//var_dump($this->getRolePermission['table_name']);exit;
			$inputs[] = '
			<div class="form-group">
				<div class=" col-md-12">
					'. form_input(array(
						"name" => "labels",
						"value" => $val,
						"class" => "form-control dsasdadsfdfsaadfsadsfdsafasd",
						"placeholder" => lang( 'labels' )
					)).'
				</div>
			</div>

			<script>

			$( function() {

				 $( \'[name="labels"]\' ).select2({multiple: true, data: '. json_encode( $label_suggestions ) .'});
			});
			</script>
		';
		
		}

		
		
		
		if( $this->getRolePermission['table_name'] == 'receipts' ) {
			
			$sql = "
			SELECT * FROM `receipts` WHERE `id` = ". $doc_id ."
			";
			
			$po_id = NULL;
			foreach( $this->fetchAll( $sql ) as $ko => $vo ) {
				
				 
				$po_id = $vo->po_id.'|'.$vo->po_no;
			}
			
			//arr($model_info);
			//
			$items = $this->Pr_items_model->getPo_Item_suggestion( NULL,  $doc_id );
			// arr($items);
			$suggestion = array();
			foreach ( $items as $item ) {            
			   
				$suggestion[] = array( "id" => $item->pr_supplier_id, "text" => $item->tname );
				
			}
			$inputs[] = '
					<div class="form-group">
					<label for="po_item_title" class=" col-md-3">อ้างอิงจาก </label>
					<div class="col-md-9">
						'. form_input(array(
							"id" => "po_item_title",
							"name" => "po_item_title",
							"value" => $po_id,
							"class" => "form-control validate-hidden",
							"placeholder" => lang('select_or_create_new_item')
							// "data-rule-required" => true,
							// "data-msg-required" => lang("field_required"),
						)) .'
						<a id="po_item_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>

				</div>

				<script>

				$( function() {

					 $( \'[name="po_item_title"]\' ).select2({data: '. json_encode( $suggestion ) .'});

				});
				</script>
			';
		}

		if( $this->getRolePermission['table_name'] == 'invoices' ) {
			
			$sql = "
				SELECT * FROM `invoices` WHERE `id` = ". $doc_id ."
			";
			
			$es_id = NULL;
			foreach( $this->fetchAll( $sql ) as $ko => $vo ) {
				
				 
				$es_id = $vo->es_id;
			}

			$model_info = $this->Invoices_model->get_one( $doc_id );
			
			$items = $this->Estimates_model->getEstimate_Item_suggestion( NULL,  $doc_id, $datas );
			
			$suggestion = array();
			foreach ( $items as $item ) {            
			   
				$suggestion[] = array( "id" => $item->estimateId, "text" => 'ใบเสนอราคา # ' . $item->doc_no );
				
			}
			// arr($items);
			
			$inputs[] = '
					<div class="form-group">
					<label for="po_item_title" class=" col-md-3">อ้างอิงจาก </label>
					<div class="col-md-9">
						'. form_input(array(
							"id" => "es_item_title",
							"name" => "es_item_title",
							"value" => isset($es_id) ? $es_id : $datas,
							"class" => "form-control validate-hidden",
							"placeholder" => lang('select_or_create_new_item'),
							"data-rule-required" => true,
							"data-msg-required" => lang("field_required")
							
						)) .'
						<a id="po_item_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>

				</div>

				<script>

				$( function() {

					 $( \'[name="es_item_title"]\' ).select2({data: '. json_encode( $suggestion ) .'});

				});
				</script>
			';
		}

		if( $this->getRolePermission['table_name'] == 'receipt_taxinvoices' ) {
			
			$sql = "
				SELECT * FROM `receipt_taxinvoices` WHERE `id` = ". $doc_id ."
			";
			
			$es_id = NULL;
			foreach( $this->fetchAll( $sql ) as $ko => $vo ) {
				
				 
				$es_id = $vo->es_id;
			}

			$model_info = $this->Receipt_taxinvoices_model->get_one( $doc_id );
			
			$items = $this->Invoices_model->getInvoice_Item_suggestion( NULL,  $doc_id, $datas );
			
			$suggestion = array();
			foreach ( $items as $item ) {            
			   
				$suggestion[] = array( "id" => $item->invoiceId, "text" => 'ใบวางบิล/ใบแจ้งหนี้ (BL) # ' . $item->doc_no );
				
			}
			// arr($items);
			
			$inputs[] = '
					<div class="form-group">
					<label for="po_item_title" class=" col-md-3">อ้างอิงจาก </label>
					<div class="col-md-9">
						'. form_input(array(
							"id" => "es_item_title",
							"name" => "es_item_title",
							"value" => isset($es_id) ? $es_id : $datas,
							"class" => "form-control validate-hidden",
							"placeholder" => lang('select_or_create_new_item'),
							"data-rule-required" => true,
							"data-msg-required" => lang("field_required")
							
						)) .'
						<a id="po_item_title_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #B3B3B3;float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a>

				</div>

				<script>

				$( function() {

					 $( \'[name="es_item_title"]\' ).select2({data: '. json_encode( $suggestion ) .'});

				});
				</script>
			';
		}
		

		return implode( '', $inputs );
	}

	function createNewItemCat( $param = array() ) {

		$sql = "
			SELECT
				*
			FROM item_categories
			WHERE title LIKE '%". $param['valSend'] ."%'
			LIMIT 0, 1
		";

		$newVal = NULL;
		foreach( $this->fetchAll( $sql ) as $kt => $vt ) {
			$newVal = $vt->id;

		}

		if( empty( $newVal ) ) {
			$sql = "
				INSERT INTO item_categories ( title )
				SELECT
					'". $param['valSend'] ."' as title
			";

			if( $this->execDatas( $sql ) ) {
				$newVal = $this->db->insert_id();
			}
		}

		return $newVal;
	}


	function implodeExcel( $params = array() ) {


		if( !isset( $params['configs'] ) ) {
			$params['configs'] = array();
		}

		require_once( APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php" );

		$showColumns = $this->showColumns( $params['table'] );

        $excel_file = new SpreadsheetReader( $params['file_name'] );

        $now = get_current_utc_time();

		$rows = array();
        foreach ( $excel_file as $key => $vals ) { //rows

			if( $key == 0 ) {

				foreach( $vals as $kv => $val )  {

					if( in_array( $val, $showColumns ) ) {

						$columns[$kv] = $val;
					}
				}
			}
			else if( $key == 1 ) {


			}
			else {

				$data = array();
				foreach( $columns as $kc => $vc )  {


					if( in_array( $vc, array_keys( $params['configs'] ) ) ) {
						$param['valSend'] = $vals[$kc];
						$data[$vc] = call_user_func( array( $this, $params['configs'][$vc]['call'] ), $param );
					}
					else if( $vc == 'category_id' ) {

						$data[$vc] = $this->createNewItemCat( $vals[$kc] );

					}
					else {

						$data[$vc] = $vals[$kc];
					}

				}

				$autoColumns = array( 'created_by' => $_SESSION['user_id'], 'deleted' => 0 );

				foreach( $autoColumns as $kc => $val ) {

					if( in_array( $kc, $showColumns ) ) {

						if( !isset( $data[$kc] ) ) {
							$data[$kc] = $val;
						}

					}
				}

				//arr( $data );
				$this->db->insert( 'items', $data );
			}

        }

       // delete_file_from_directory($temp_file_path . $file_name); //delete temp file

        echo json_encode(array('success' => true, 'message' => lang("record_saved")));
	}



	//$param['url']
	//$param['table_name']
	//$param['user_id']
	function getRolePermission( $param = array() ) {
	 
		if( isset( $param['user_id'] ) ) {
			$user_id = $param['user_id'];
		}
		else {

			$user_id = $_SESSION['user_id'];
		}

		if( $param['table_name'] != 'labels' ) {
			$_SESSION['table_name'] = $param['table_name'];
		}

		$request = $this->input->post();

		$filters = array();

		if( in_array( 'deleted', $this->showColumns( $param['table_name'] ) ) ) {
			$filters['WHERE'][] = $param['table_name'] .".deleted = 0";
		}

		$sql = "
			SELECT
				u.*,
				r.permissions
			FROM users u
			LEFT JOIN roles r ON u.role_id = r.id
			WHERE u.id = ". $user_id ."
		";

		

		foreach( $this->fetchAll( $sql ) as $ka => $va ) {

			$unserializePermissions = unserialize( $va->permissions );

			
			$sql = "
				SELECT
					*
				FROM left_menu
				WHERE url = '". $param['url'] . "'
				AND class_name = '". $param['table_name'] ."'
			";

			foreach( $this->fetchAll( $sql ) as $km => $vm ) {
 	
				$vr = new stdClass;

				$vr->admin = 0;

				$vr->p = $unserializePermissions;

				if( $va->is_admin ) {

					$text = 'a:42:{s:5:"leave";s:3:"all";s:14:"leave_specific";s:0:"";s:10:"attendance";s:3:"all";s:19:"attendance_specific";s:0:"";s:7:"invoice";N;s:8:"estimate";N;s:7:"expense";N;s:5:"order";N;s:15:"purchaserequest";N;s:15:"payment_voucher";N;s:6:"client";N;s:4:"lead";N;s:6:"ticket";N;s:15:"ticket_specific";s:0:"";s:12:"announcement";N;s:23:"help_and_knowledge_base";N;s:23:"can_manage_all_projects";s:1:"1";s:19:"can_create_projects";s:1:"1";s:17:"can_edit_projects";s:1:"1";s:19:"can_delete_projects";s:1:"1";s:30:"can_add_remove_project_members";s:1:"1";s:16:"can_create_tasks";s:1:"1";s:14:"can_edit_tasks";s:1:"1";s:16:"can_delete_tasks";s:1:"1";s:20:"can_comment_on_tasks";s:1:"1";s:24:"show_assigned_tasks_only";N;s:37:"can_update_only_assigned_tasks_status";N;s:21:"can_create_milestones";s:1:"1";s:19:"can_edit_milestones";s:1:"1";s:21:"can_delete_milestones";s:1:"1";s:16:"can_delete_files";s:1:"1";s:34:"can_view_team_members_contact_info";s:1:"1";s:34:"can_view_team_members_social_links";s:1:"1";s:29:"team_member_update_permission";s:3:"all";s:38:"team_member_update_permission_specific";s:79:"member:45,member:39,member:38,team:1,member:4,member:5,member:6,member:2,team:4";s:27:"timesheet_manage_permission";s:3:"all";s:36:"timesheet_manage_permission_specific";s:0:"";s:21:"disable_event_sharing";N;s:22:"hide_team_members_list";N;s:28:"can_delete_leave_application";N;s:18:"message_permission";s:0:"";s:27:"message_permission_specific";s:0:"";}';

					$vr->p = convertObJectToArray( unserialize( $text ) ) ;
					$vr->p['can_setting'] = 1; //set label

					$vr->add_row = 1;
					$vr->edit_row = 1;
					$vr->delete_row = 1;
					$vr->view_row = 2;
					$vr->prove_row = 1;
					$vr->admin = 1;
					$vr->table_name = $param['table_name'];


					$vr->filters = $filters;

				}
				else if( $this->login_user->user_type == 'client' ) {


					$text = 'a:42:{s:5:"leave";s:0:"";s:14:"leave_specific";s:0:"";s:10:"attendance";s:0:"";s:19:"attendance_specific";s:0:"";s:7:"invoice";N;s:8:"estimate";N;s:7:"expense";N;s:5:"order";N;s:15:"purchaserequest";N;s:15:"payment_voucher";N;s:6:"client";s:3:"own";s:4:"lead";s:3:"own";s:6:"ticket";s:13:"assigned_only";s:15:"ticket_specific";s:0:"";s:12:"announcement";s:0:"";s:23:"help_and_knowledge_base";s:0:"";s:23:"can_manage_all_projects";N;s:19:"can_create_projects";s:1:"1";s:17:"can_edit_projects";s:1:"1";s:19:"can_delete_projects";s:1:"1";s:30:"can_add_remove_project_members";s:1:"1";s:16:"can_create_tasks";s:1:"1";s:14:"can_edit_tasks";s:1:"1";s:16:"can_delete_tasks";s:1:"1";s:20:"can_comment_on_tasks";s:1:"1";s:24:"show_assigned_tasks_only";N;s:37:"can_update_only_assigned_tasks_status";N;s:21:"can_create_milestones";s:1:"1";s:19:"can_edit_milestones";s:1:"1";s:21:"can_delete_milestones";s:1:"1";s:16:"can_delete_files";s:1:"1";s:34:"can_view_team_members_contact_info";s:1:"1";s:34:"can_view_team_members_social_links";s:1:"1";s:29:"team_member_update_permission";s:3:"all";s:38:"team_member_update_permission_specific";s:0:"";s:27:"timesheet_manage_permission";s:0:"";s:36:"timesheet_manage_permission_specific";s:0:"";s:21:"disable_event_sharing";N;s:22:"hide_team_members_list";N;s:28:"can_delete_leave_application";N;s:18:"message_permission";s:8:"specific";s:27:"message_permission_specific";s:56:"member:12,member:40,member:41,member:44,member:45,team:2";}';

					$vr->p = convertObJectToArray( unserialize( $text ) ) ;
					$vr->p['can_setting'] = 1; //set label

					$vr->add_row = 1;
					$vr->edit_row = 1;
					$vr->delete_row = 1;
					$vr->view_row = 2;
					$vr->prove_row = 1;
					$vr->admin = 0;
					$vr->table_name = $param['table_name'];




					$clientPermission = '[{"role_id":"5","read_only":"0","table_name":"leads","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"637","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"clients","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"1","id":"638","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"items","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"1","id":"639","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"notes","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"640","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"settings","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"641","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"orders","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"1","id":"642","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"purchaserequests","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"643","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"estimates","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"644","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"payment_vouchers","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"645","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"invoices","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"1","id":"646","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"invoice_payments","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"647","prove_labels":""},{"role_id":"5","read_only":"0","table_name":"expenses","time_update":"2022-03-13 12:36:04","add_row":"0","edit_row":"0","delete_row":"1","prove_row":"0","view_row":"0","id":"648","prove_labels":""}]';

					foreach( json_decode( $clientPermission ) as $kr => $vr ) {

						if( $param['table_name'] != $vr->table_name ) {

							continue;
						}

						if( $vr->read_only == 1 ) {

							$vr->add_row = 0;
							$vr->edit_row = 0;
							$vr->delete_row = 0;
						}
						else {
							$vr->add_row = 1;
							$vr->edit_row = 1;
							$vr->delete_row = 1;
						}

						if( $vr->view_row == 1 ) {

							$filters['WHERE'][] = $param['table_name'] .".created_by = ". $user_id;

						}
						else if( $vr->view_row == 0 ) {
							$filters['WHERE'][] = "1 = 0";
						}

						$vr->filters = $filters;

						$vr->p = $unserializePermissions;


						break;

					}

					$vr->filters = $filters;


				}
				else if( $param['table_name'] == 'help_articles' ) {

					$vr->view_row = 1;

					if( $vr->p['help_and_knowledge_base'] == 'all' ) {
						$vr->add_row = 1;
						$vr->edit_row = 1;
						$vr->delete_row = 1;
					}


					$vr->filters = $filters;
				}
				else if( $param['table_name'] == 'tickets' ) {
				 
					$sql = "
						SELECT
							r.*,
							GROUP_CONCAT( pl.label_id ) as prove_labels
						FROM role_permission r
						LEFT JOIN prove_labels pl ON r.table_name = pl.table_name AND pl.role_id = r.role_id
						WHERE r.role_id = ". $va->role_id ."
						AND r.table_name = '". $param['table_name'] ."'
						LIMIT 0, 1
					";

					foreach( $this->fetchAll( $sql ) as $kr => $vr ) {

						if( $vr->read_only == 1 ) {

							$vr->add_row = 0;
							$vr->edit_row = 0;
							$vr->delete_row = 0;
						}
						else {
							$vr->add_row = 1;
							$vr->edit_row = 1;
							$vr->delete_row = 1;
						}

						if( $vr->view_row == 1 ) {

							//$filters['WHERE'][] = $param['table_name'] .".created_by = ". $user_id;

						}
						else if( $vr->view_row == 0 ) {
							$filters['WHERE'][] = "1 = 0";
						}

						//$vr->filters = $filters;

						$vr->p = $unserializePermissions;

					}
					
					 

					$vr->view_row = 1;
					if( empty( $vr->p['ticket'] ) ) {
						$vr->view_row = 0;
					}
					else if( $vr->p['ticket'] == 'all' ) {

					}
					else if( $vr->p['ticket'] == 'assigned_only' ) {

						$filters['WHERE'][] = $param['table_name'] .".created_by = ". $user_id;
					}
					else if( !empty( $vr->p['ticket_specific']  ) ) {

						$filters['WHERE'][] = "". $param['table_name'] .".ticket_type_id IN ( ". $vr->p['ticket_specific']  ." )";
					}

					$vr->filters = $filters;
				}
				else if( $param['table_name'] == 'projects' ) {

					$vr->view_row = 1;

					if( !empty( $vr->p['can_create_projects'] ) ) {

						$vr->add_row = 1;
					}
					if( !empty( $vr->p['can_edit_projects'] ) ) {

						$vr->edit_row = 1;
					}
					if( !empty( $vr->p['can_delete_projects'] ) ) {

						$vr->delete_row = 1;
					}

					if( !empty( $vr->p['can_manage_all_projects'] ) ) {

						$vr->add_row = 1;
						$vr->edit_row = 1;
						$vr->delete_row = 1;
					}
					else if( !empty( $vr->p['show_assigned_tasks_only'] ) ) {

						$sql = "
							SELECT
								p.id,
								p.user_id,
								p.team_id,
								p.project_id,
								p.is_leader,
								p.deleted,
								t.members
							FROM project_members p
							INNER JOIN team t ON p.team_id = t.id
							WHERE p.team_id != 0
							AND p.deleted = 0
						";

						$project_ids = array( 0 );
						foreach( $this->fetchAll( $sql ) as $kp => $vp ) {

							if( in_array( $user_id, explode( ',', $vp->members ) ) ) {
								$project_ids[] = $vp->project_id;
							}
						}

						$filters['WHERE'][] = "(
							". $param['table_name'] .".id IN ( ". implode( ',', $project_ids  ) ." )

							OR

							". $param['table_name'] .".id IN ( SELECT project_id  FROM project_members WHERE user_id = ". $user_id ." AND deleted = 0 )

							OR

							". $param['table_name'] .".created_by = ". $user_id ."
						)";
					}
					else {

						$filters['WHERE'][] = "". $param['table_name'] .".created_by = ". $user_id ."";

					}

					$vr->filters = $filters;
				}
			
				else {
					

					$sql = "
						SELECT
							r.*,
							GROUP_CONCAT( pl.label_id ) as prove_labels
						FROM role_permission r
						LEFT JOIN prove_labels pl ON r.table_name = pl.table_name AND pl.role_id = r.role_id
						WHERE r.role_id = ". $va->role_id ."
						AND r.table_name = '". $param['table_name'] ."'
						LIMIT 0, 1
					";



					foreach( $this->fetchAll( $sql ) as $kr => $vr ) {
//arr( $vr );
						if( $vr->read_only == 1 ) {

							$vr->add_row = 0;
							$vr->edit_row = 0;
							$vr->delete_row = 0;
						}
						else {
							$vr->add_row = 1;
							$vr->edit_row = 1;
							$vr->delete_row = 1;
						}

						if( $vr->view_row == 1 ) {

							$filters['WHERE'][] = $param['table_name'] .".created_by = ". $user_id;

						}
						else if( $vr->view_row == 0 ) {
							$filters['WHERE'][] = "1 = 0";
						}
//arr( $filters );
						$vr->filters = $filters;

						$vr->p = $unserializePermissions;

					}

					$vr->table_name = $param['table_name'];
				}

				if( $vm->check_action != 0 ) {
				
					$check_id_name = 'id';

					if( !empty( $vm->check_id_name ) ) {
						$check_id_name = $vm->check_id_name;
					}
					
					if( empty( $request[$check_id_name] ) ) {
	
						if( empty( $vr->add_row ) ) {
							$param['message'] = 'คุณยังไม่รับสิทธิ์ในการเพิ่มข้อมูล กรุณาติดต่อผู้ดูแลระบบ';
							echo permissionBlock( $param );
							exit;
						}
					}
					else {

						$vr->myDocId = $request[$check_id_name];

						if( $vm->check_action == 1 ) {

							if( empty( $vr->edit_row ) ) {
								$param['message'] = 'คุณยังไม่รับสิทธิ์ในการแก้ไขข้อมูล กรุณาติดต่อผู้ดูแลระบบ';
								echo permissionBlock( $param );

								exit;

							}
						}
						else {

							if( empty( $vr->delete_row ) ) {
								$param['message'] = 'คุณไม่มีสิทธิ์ในการลบข้อมูลนี้ กรุณาติดต่อผู้ดูแลระบบ';
								echo json_encode(array("success" => false, 'message' => $param['message'] ));

								exit;

							}
						}
		 
					}
					
					
					///if( !$va->is_admin ) {
					if( true ) {
						if( !empty( $vm->child_action_check ) ) {
												
							$child_action_check = json_decode( $vm->child_action_check );
							
							if( empty( $request[$check_id_name] ) ) {
							
								$sql = "
									SELECT
										*
									FROM prove_table
									WHERE doc_id = ". $request[$child_action_check->child_parentName] ."
									AND tbName = '". $param['table_name'] ."'
									AND status_id = 1
								";
							}
							else {
								
								$sql = "
									SELECT
										*
									FROM ". $child_action_check->child_tbName ." dt 
									INNER JOIN ". $param['table_name'] ." parent ON dt.". $child_action_check->child_parentName ." = parent.id
									INNER JOIN prove_table pt ON parent.id = pt.doc_id AND tbName = '". $param['table_name'] ."'
									WHERE dt.id = ". $request[$check_id_name] ."
									AND pt.status_id = 1
								";
							}
							
							foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {
								
								$param['message'] = 'เอกสารนี้มีผู้อนุมัติแล้ว ไม่สามารถทำการแก้ไขได้';

								if( $vm->check_action == 1 ) {
									echo permissionBlock( $param );
								}
								else {
									echo json_encode(array("success" => false, 'message' => $param['message'] ));

								}

								exit;
							}
							
							
						}
						else {
							
							if( !empty( $request[$check_id_name] ) ) {
								$sql = "
									SELECT
										*
									FROM prove_table
									WHERE doc_id = ". $request[$check_id_name] ."
									AND tbName = '". $param['table_name'] ."'
									AND status_id = 1
								";

								foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {
									if($va->tbName == 'projects') continue;
									$param['message'] = 'เอกสารนี้มีผู้อนุมัติแล้ว ไม่สามารถทำการแก้ไขได้';

									if( $vm->check_action == 1 ) {
										echo permissionBlock( $param );
									}
									else {
										echo json_encode(array("success" => false, 'message' => $param['message'] ));

									}

									exit;

								}
							}
						}
					}
				}

				$vr->tb_name = $param['table_name'];

				return convertObJectToArray( $vr );

			}

		}

		return array();
	}

	
	//$param['id']
	//$param['tbName']
	//$param['needToknowCanprove']
	function getProveButton( $param = array() ) {
		
		$buttons = array();
		$buttons[] = '<a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn"  href="'. base_url( 'index.php/'. $param['tbName']  ) .'" ><i class="fa fa-hand-o-left" aria-hidden="true"></i> ย้อนกลับไปตารางรายการ</a>';

		if( in_array( $param['tbName'], array( 'orders' ) ) ) {
			//return implode( '', $buttons );
			
		}

		$canProve = false;
		if( !empty( $this->getRolePermission['prove_row'] ) ) {

			$canProve = true;

			//

			if( !empty( $this->getRolePermission['prove_labels'] ) ) {


				$canProve = false;
				$sql = "
					SELECT
						GROUP_CONCAT( DISTINCT label_id ) as labels
					FROM label_table
					WHERE doc_id = ". $param['id'] ."
					AND tbName = '". $param['tbName'] ."'
				";



				foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

					$docLabels = explode( ',', $va->labels );

					//arr( $docLabels );exit;

					foreach( explode( ',', $this->getRolePermission['prove_labels'] ) as $kl => $userLabel ) {

						if( in_array( $userLabel, $docLabels ) ) {

							$sql = "
								SELECT
									pt.*
								FROM prove_table pt
								WHERE pt.doc_id = ". $param['id'] ."

								AND pt.tbName = '". $param['tbName'] ."'
							";
							$prove = false;
							foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

								$prove = true;
								
					
								 
								
								$class = '';
								if( $va->status_id == 2 ) {
									$val = 'พิจารณาอนุมัติใหม่';
									$class = 'approve-btn';
								}
								else {
									$val = 'ยกเลิกอนุมัติ';
									$class = 'unapprove-btn';
								}
								$buttons[] = ' <a class="btn btn-danger mt0 mb0 approval-btn '.$class.'"  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] ) .'" >'. $val .'</a>';
								
								
							}

							if( $prove == false ) {
								$buttons[] = ' <a class="btn btn-info mt0 mb0 approval-btn approve-btn"  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] ) .'" >อนุมัติ </a>';
								
								$buttons[] = ' <a class="btn btn-danger mt0 mb0 approval-btn reject-btn  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] .'/2' ) .'" >ไม่อนุมัติ </a>';
							}

							$canProve = true;

							break;
						}

					}

				}

			}
			else {


				$sql = "
					SELECT
						*
					FROM prove_table
					WHERE doc_id = ". $param['id'] ."
					AND tbName = '". $param['tbName'] ."'
				";

				$prove = false;
				foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

					$prove = true;
					
					
					$class = '';
					if( $va->status_id == 2 ) {
						$val = 'พิจารณาอนุมัติใหม่';
						$class = 'approve-btn';
					}
					else {
						$val = 'ยกเลิกอนุมัติ';
						$class = 'unapprove-btn';
					}
					$buttons[] = ' <a class="btn btn-danger mt0 mb0 approval-btn '.$class.'"  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] ) .'" >'. $val .'</a>';
					
				}

				if( $prove == false ) {
					$buttons[] = ' <a class="btn btn-info mt0 mb0 approval-btn approve-btn"  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] ) .'" >อนุมัติ</a>';
					
					
					$buttons[] = ' <a class="btn btn-danger mt0 mb0 approval-btn reject-btn"  href="'. base_url( 'index.php/action/proveDoc/'. $param['tbName'] .'/'. $param['id'] .'/2' ) .'" >ไม่อนุมัติ </a>';
				}
			}



		}


		if( $canProve == false ) {

			$sql = "
				SELECT
					pt.*,
					DAtE_FORMAT( pt.doc_date,  '%d/%m/%Y' ) as prove_date,
					u.first_name as prove_by
				FROM prove_table pt
				INNER JOIN users u ON pt.user_id = u.id
				WHERE doc_id = ". $param['id'] ."
				AND tbName = '". $param['tbName'] ."'
			";

			$prove = false;
			foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {

				
				$prove = true;
				
				if( $va->status_id != 1 ) {
					 
					$buttons[] = '<a class="btn btn-info mt0 mb0 approval-btn requestapprove-btn"  href="'. get_uri("action/requestApprove/". $param['tbName'] ."/". $param['id'] ."") .'" >ส่งเรื่องให้ผู้อนุมัติใหม่อีกครั้ง</a>';
					
				}
				else {
					$buttons[] = '
						<div style="text-align: left;  display: inline-block;">

							<b>อนุมัติแล้วโดยคุณ  </b> '. $va->prove_by .' <b>ณ วันที่</b> '. $va->prove_date .'


						</div>
					';
				}
				
				
				
			}

			if( $prove == false ) {
				
				$buttons[] = '<a class="btn btn-info mt0 mb0 approval-btn requestapprove-btn"  href="'. get_uri("action/requestApprove/". $param['tbName'] ."/". $param['id'] ."") .'" >ส่งเรื่องให้ผู้อนุมัติ</a>';

			}
		}


		if( !empty( $param['needToknowCanprove'] ) ) {
			return $canProve;
		}
		

		return implode( '', $buttons );

	}


	function getRolePermissions() {
		$sql = "
			SELECT
				r.table_name,
				r.read_only,
				r.add_row,
				r.edit_row,
				r.delete_row,
				r.prove_row,
				r.view_row
			FROM users u
			INNER JOIN role_permission r ON u.role_id = r.role_id
			WHERE u.id = '". $_SESSION['user_id'] ."'";
		$rows = [];
		foreach( $this->fetchAll( $sql ) as $kr=> $vr ) {
			$tbname = $vr->table_name;
			unset($vr->table_name);
			$rows[$tbname] = convertObJectToArray( $vr );
		}
		return $rows;
	}

	//
	//
	public function getRowsCount( $sql  ) {

		$sql = "

			SELECT
				COUNT( * ) as t

			FROM (

				". $sql ."
			) as new_tb
		";


		return $this->fetch( $sql )->t;


	}

	//
	//
	public function delete( $tb_name, $cond = array() ) {
		$condition = '';
		$i=1;
		foreach($cond as $k=>$v)
		{
			$condition .= $k . " = '". $v ."'";
			if($i!=count($cond))
				$condition.=" AND ";

			++$i;
		}
		$sql = "
			DELETE FROM ". $tb_name ."
			WHERE " . $condition;




		if ( $this->execDatas( $sql ) )
			return true;

		return false;
	}

	//
	//
	public function update( $table, $arr, $condition = NULL ) {
		//
		$sql = "
			UPDATE ". $table ." SET ";
		$i=1;
		foreach( $arr as $k => $v ) {

			if( $v == '' && !is_numeric( $v ) ) {
				$sql .= "	". $k ." =  NULL";
			}
			else
				$sql .= "	". $k ." = '". addslashes( $v ) ."'";

			if( $i!=count( $arr ) )
				$sql .= ",";

			++$i;

		}
		if( !empty( $condition ) )
			$sql .= " WHERE " . $condition;
//arr( $sql );
		if ( $this->execDatas( $sql ) )
			return true;

		return false;
	}


	//
	//
	public function insert( $table_name, $data, $upDuplicate = false, $action = 'INSERT' ) {

		if( true ) {
			$val = '';
			$keep = array();
			$i = 0;
			foreach( $data as $ka => $va ) {

				++$i;
				$val .= "'". $va ."'";

				$keep[] = addslashes( $va );

				if( $i != count( $data ) )
					$val .= ",";

			}

			$sql = $action ." INTO ". $table_name ." ";

			$sql .= "(". implode(',', array_keys( $data ) ) .") VALUES (". $val .") ";
 ///arr( $sql );
			if( $upDuplicate )
				$sql .= "ON DUPLICATE KEY UPDATE " . $upDuplicate;

			if( $this->execDatas( $sql, $keep ) )
				return true;

			return false;
		}
		else {

			$val = '';
			$keep = array();
			$i = 0;
			foreach( $data as $ka => $va ) {

				++$i;
				$val .= "'". $va ."'";

				$keep[] = addslashes( $va );

				if( $i != count( $data ) )
					$val .= ",";

			}

			$sql = $action ." INTO ". $table_name ." ";

			$sql .= "(". implode(',', array_keys( $data ) ) .") VALUES (". $val .") ";
	//arr( $sql );
			if( $upDuplicate )
				$sql .= "ON DUPLICATE KEY UPDATE " . $upDuplicate;

			if( $this->execDatas( $sql, $keep ) )
				return true;

			return false;
		}


	}

	//
	//
	public function showColumns( $table ) {

		if($table == "leaves"){
			$table = "leave_applications";
		}

		$sql = "SHOW COLUMNS FROM " . $table;

		$keep = array();
		foreach( $this->fetchAll( $sql ) as $v ) {
			$keep[] = $v->Field;
		}
		return $keep;
	}


    public function execDatas( $sql, $param = array() ) {

	//arr( $sql );

		$query = $this->db->query( $sql );
		if( !$query ) {

			return false;
		}


		return true;
    }


    public function fetchAll( $sql, $param = array() ) {

		//
		if( isset( $param['filters'] ) ) {

			$sql = genCond_( $sql, $param['filters'] );
		}


		//arr( $sql );
		if( isset( $_REQUEST['bomb'] ) ) {
			arr( $sql );
		}

		if( isset( $param['bomb'] ) ) {
			//arr( $sql );
		}


		$query = $this->db->query( $sql );
		if( !$query ) {
			//arr( $sql );

			///file_put_contents( 'gogo.txt', $sql );
			return array();
		}

        return $query->result();
    }

    public function fetch( $sql, $param = array() ) {


		//arr( $sql );
		foreach( $this->fetchAll( $sql ) as $ka => $res ) {

			return $res;
		}


    }

    public function __construct()
    {
        parent::__construct();
    }

	function getUserPermission( $param = array() ) {


		$userData = $this->session->all_userdata();

		if( empty( $userData['user_id'] ) ) {

			redirect( 'admin/logout' );

			exit;
		}

		$ex = explode( '/', $this->uri->uri_string() );


		$param['pageName'] = isset( $ex[1] )? $ex[1]: 'welcome';

		$param['sql'] = "
			SELECT
				*
			FROM sma_page
			WHERE name = '". $param['pageName'] ."'

		";

		$data['permission'] = new stdClass;

		$pass = 'pass';

		foreach( $this->getRows( $param ) as $ka => $va ) {

			$param['sql'] = "
				SELECT
					gp.page_id,
					gp.page_name,
					g.*
				FROM sma_users u
				INNER JOIN sma_groups g ON u.group_id = g.id
				INNER JOIN sma_group_page gp ON u.group_id = gp.group_id
				WHERE u.id = ". $userData['user_id'] ."
				AND gp.page_id = ". $va->id ."
			";

			$pass = false;
			foreach( $this->getRows( $param ) as $ka => $va ) {

				$data['permission'] = $va;

				$pass = true;
			}
		}

		if( !$pass ) {

			//redirect( 'admin/logout' );

			echo '<h1>no permission</h1>';

			exit;
		}

		//arr(  );

		///exit;
		$data['permission']->pageName = $param['pageName'];
		return $data['permission'];

	}

	function getUser( $user_id ) {

		$param['sql'] = "
			SELECT
				u.*,
				g.prove_doc
			FROM sma_users u
			LEFT JOIN sma_groups g ON u.group_id = g.id
			WHERE u.id = ". $user_id ."
		";

		//arr($param['sql']);

		foreach( $this->getRows( $param ) as $ku => $vu ) {
			return $vu;
		}


	}

    public function getRows( $param = array() ) {

		//arr($param['sql']);

		$query = $this->db->query( $param['sql'] );

        return $query->result();
    }

    public function getBestSeller($start_date = null, $end_date = null)
    {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
        }

        $this->db
            ->select('product_name, product_code')
            ->select_sum('quantity')
            ->from('sale_items')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)
            ->where('date <', $end_date)
            ->group_by('product_name, product_code')
            ->order_by('sum(quantity)', 'desc')
            ->limit(10);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getLastestQuotes()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('quotes', 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestCustomers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('companies', ['group_name' => 'customer'], 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestPurchases()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('purchases', 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestSales()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('sales', 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestSuppliers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('companies', ['group_name' => 'supplier'], 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestTransfers()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('transfers', 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getStockValue()
    {
        $q = $this->db->query('SELECT SUM(qty*price) as stock_by_price, SUM(qty*cost) as stock_by_cost
        FROM (
            Select sum(COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0)) as qty, price, cost
            FROM ' . $this->db->dbprefix('products') . '
            JOIN ' . $this->db->dbprefix('warehouses_products') . ' ON ' . $this->db->dbprefix('warehouses_products') . '.product_id=' . $this->db->dbprefix('products') . '.id
            GROUP BY ' . $this->db->dbprefix('warehouses_products') . '.id ) a');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

	public function signature_approve( $tbId = NULL ,$tbName = NULL)
	{


		// var_dump($tb_name) ;
		$div = array();
		$sql = "
		SELECT
		users.signature,
		users.first_name,
		users.last_name,
		prove_table.*
		FROM prove_table
		LEFT JOIN users ON users.id = prove_table.user_id
		LEFT JOIN $tbName ON $tbName.id = prove_table.doc_id
		WHERE $tbName.id = $tbId AND prove_table.tbName = '$tbName'";

		foreach($this->fetchAll($sql) as $ksig){
			if($ksig->tbName != $tbName){

                return $div;

			}else{
				$div = $ksig;
				return $div;
			}
		}


	}


	function getInputLabel() {
		$view_data['bombLabel'] = '
			<div class="form-group">
				<label for="invoice_labels" class=" col-md-3">'. lang( 'labels' ) .'</label>
				<div class=" col-md-9">
					'. form_input(array(
						"id" => "invoice_labels",
						"name" => "labels",
						"value" => $model_info->labels,
						"class" => "form-control",
						"placeholder" => lang( 'labels' )
					)).'
				</div>
			</div>


			<script>

			$( function() {

				 $("#invoice_labels").select2({multiple: true, data: '. json_encode( $label_suggestions ) .'});
			});
			</script>
		';


	}

	public function userSignature( $user_id ) {
		$sql = "
			SELECT
				users.signature,
				users.first_name,
				users.last_name
			FROM users
			WHERE users.id = $user_id ";
		$query = $this->db->query($sql);
		if(!$query) return false;
		return $query->row();
	}

	public function creatBy( $tbId = NULL , $tbName = NULL ) {
		$data = array();

		$sql = "
			SELECT
				users.first_name,
				users.last_name,
				$tbName.*
			FROM users
			LEFT JOIN $tbName ON $tbName.id = $tbId AND $tbName.deleted = 0
			WHERE $tbName.created_by = users.id ";

		foreach($this->fetchAll($sql) as $kc){
			$data = $kc;
		}
		return $data;
		// var_dump($test);exit;
	}

    function toUserBlock() {

		$sql = "
			SELECT
				u.id as user_id,
				u.first_name,
				u.last_name,
				u.user_type,
				u.is_admin,
				u.role_id,
				u.email,
				r.title
			FROM users u
			LEFT JOIN roles r ON u.role_id = r.id
		
		";

		$keep = array();
		$keep['users'] = array();
		$keep['team'] = array();

		$allowed = array();

		if( !empty( $this->getRolePermission['p']['message_permission_specific'] ) ) {
			
			foreach( explode( ',', $this->getRolePermission['p']['message_permission_specific'] ) as $ka => $va ) {

				$ex = explode( ':', $va );

				$allowed[$ex[0]][] = $ex[1];

			}

			foreach( $this->fetchAll( $sql ) as $ka => $va ) {
				$last_name = str_replace("-", "", str_replace("N/A", "", $va->last_name));

				if( !empty( $allowed['member'] ) ) {
					if( in_array( $va->user_id, $allowed['member'] ) ) {
						$keep['users'][$va->user_id] = '<i class="fa fa-user"></i> <input type="checkbox" name="userCheckboxs[]" value="'. $va->user_id .'" /> '. $va->first_name.' '.$va->last_name;

					}
				}
				else {
					//$keep['users'][$va->user_id] = '<i class="fa fa-user"></i> <input type="checkbox" name="userCheckboxs[]" value="'. $va->user_id .'" /> '. $va->first_name.' -yy- '.$va->last_name;
				}

			}

			$sql = "
				SELECT * FROM team
				WHERE deleted = 0
			";

			foreach( $this->fetchAll( $sql ) as $ka => $va ) {

				if( !empty( $allowed['team'] ) ) {

					if( in_array( $va->id, $allowed['team'] ) ) {
						$keep['team'][$va->id] = '<i class="fa fa-users info"></i> <input type="checkbox" name="team[]" value="'. $va->id .'" /> '. $va->title .'';

					}

				}
				else {

					//$keep['team'][$va->id] = '<i class="fa fa-users info"></i> <input type="checkbox" name="team[]" value="'. $va->id .'" /> '. $va->title .'';
				}
			}

		}
		else {

			if($this->login_user->is_admin == 1){
				$sql = "
					SELECT
						u.id as user_id,
						u.first_name,
						u.last_name,
						u.user_type,
						u.is_admin,
						u.role_id,
						u.email,
						r.title
					FROM users u
					LEFT JOIN roles r ON u.role_id = r.id
				";

				foreach( $this->fetchAll( $sql ) as $ka => $va ) {
					$last_name = str_replace("-", "", str_replace("N/A", "", $va->last_name));

					$keep['users'][$va->user_id] = '<i class="fa fa-user"></i> <input type="checkbox" name="userCheckboxs[]" value="'. $va->user_id .'" /> '. $va->first_name." ".$last_name;
				}

				$sql = "
					SELECT * FROM team
					WHERE deleted = 0
				";

				foreach( $this->fetchAll( $sql ) as $ka => $va ) {

					$keep['team'][$va->id] = '<i class="fa fa-users info"></i> <input type="checkbox" name="team[]" value="'. $va->id .'" /> '. $va->title .'';
				}
			}
		}

	//arr( $_SESSION );
		$teamBlock = '';
		if( !empty( $keep['team'] ) ) {
			$teamBlock = '
			<span class="">ถึงทีม</span>
				<div>
					'. implode( '<br>', $keep['team'] )  .'
				</div>
			';
		}

		$userBlock = '';
		if( !empty( $keep['users'] ) ) {
			$userBlock = '
				<span  class="">ถึงคุณ</span>
				<div class="load-user-send" style="">
					'. implode( '<br>', $keep['users'] ) .'
				</div>
			';
		}

		if( empty($keep['users']) && empty( $keep['team'] ) ) {

			$param['message'] = 'คุณยังไม่รับสิทธิ์ในการส่งข้อความหาทีม กรุณาติดต่อผู้ดูแล';
			echo permissionBlock( $param );

			exit;
		}

		return '
			<div class="form-groupfdd" style="display: grid; align-items: center; grid-template-columns: 30% 70%; justify-content: space-evenly;">
				'. $teamBlock .'
				'. $userBlock .'
			</div>
		';
    }
	
	
	//$param['LPAD'] = 4;
	//$param['column'] = 'doc_no';
	//$param['table'] = 'erp_ap_pay';
	//$param['template'] = 'DATE_FORMAT( ADDDATE( NOW(), INTERVAL 543 year ), 'PR%y%m-[find]' ';
	function genDocNo( $param = array() ) {

		if( !isset( $param['LPAD'] ) ) {
			
			$param['LPAD'] = 4;
		}
		
		if( !isset( $param['column'] ) ) {
			
			$param['table'] = 'doc_no';
		}
		if( !isset( $param['table'] ) ) {
			
			$param['table'] = 'erp_sale_order';
		}
		
		if( !isset( $param['prefix'] ) ) {
			
			$param['prefix'] = $param['table'];
		}
		
		$param['template'] = 'DATE_FORMAT( ADDDATE( NOW(), INTERVAL 0 year ), \''. $param['prefix'] .'%Y%m[find]\' ) ';
		
		$sql = "
			SELECT
				replace(
					new_tb.myText,
					'[find]',
					LPAD(
						IFNULL(
							(
								SELECT
									MAX( REPLACE( REPLACE( ". $param['column'] .", new_tb.front, '' ), new_tb.back, '' ) ) + 1
								FROM ". $param['table'] ." 
								[WHERE] 
							),
							1
						),
						". $param['LPAD'] .",
						0
					)
				) as t	
			FROM (
				SELECT
					SUBSTRING_INDEX( new_tb.myText, '[find]', 1 ) as front,
					SUBSTRING_INDEX( new_tb.myText, '[find]', -1 ) as back,
					new_tb.myText
				FROM (
					SELECT
						". $param['template'] ." as myText
				) as new_tb
			) as new_tb
		";
		
		$filters = array();
		$filters['WHERE'][] = "REPLACE( REPLACE( ". $param['column'] .", new_tb.front, '' ), new_tb.back, '' ) REGEXP '^[0-9]+$'";
		
		if( !empty( $param['id'] ) ) {
			$filters['WHERE'][] = "id != ". $param['id'] ."";
		}
		
		$sql = genCond_( $sql, $filters );	
		
		//arr( $sql );
		foreach( $this->fetchAll( $sql ) as $kd => $vd ) {
			return $vd->t;
		}
	 
		
		//return $sql;
		
	}

	function runPoNo($pr_id=0) {
		$and = "";
		if($pr_id>0) {
			$and .= " AND pri.pr_id='{$pr_id}' ";
		}
		$sql = "
			SELECT 
				pr.doc_no,
				REPLACE( pr.doc_no, 'PR', 'PO' ) as po_temp,
				pri.po_no,
				CONCAT(
					pri.pr_id,
					'-',
					IFNULL( pri.supplier_name, '-' )
				) as gName,
				pri.pr_id,
				pri.supplier_id,
				DATE_FORMAT( pr.pr_date, '%Y%m' ) as d,
				GROUP_CONCAT( pri.id ) as ids
			FROM pr_items pri 
			INNER JOIN purchaserequests pr ON pri.pr_id = pr.id
			WHERE ISNULL(pri.po_no)
			{$and}
			GROUP BY 
				gName
			ORDER BY 
				gName ASC
				
		";
	
		$keep = array();
		foreach( $this->fetchAll( $sql ) as $kd => $vd ) {
		
			if( !isset( $keep[$vd->pr_id] ) ) {
				
				$number = 1;
				
				$keep[$vd->pr_id] = 1;
			}
			
			$doc_no = $vd->po_temp .'-'. makeFrontZero( $number, 2 );
			
			//
		/// arr( $doc_no );
			$sql = "
				UPDATE 
					pr_items SET 
				po_no = '". $doc_no ."' 
				WHERE pr_items.id IN ( ". $vd->ids ." )
				
			";
		//	AND ( po_no IS NULL OR po_no = '' )
			
			
			$this->execDatas( $sql );
			
			++$number;
		}
		
		// exit;
		
	}
	
	function getDocLabels( $doc_id = NULL, $status = NULL ) {

		$labels = array();
		
		$labels['สถานะ'][] = '<span class="mr10"><span class="mt0 label label-default large">new</span></span>';

		$sql = "
			SELECT
				*
			FROM prove_table
			WHERE doc_id = ". $doc_id ."
			AND tbName = '". $this->getRolePermission['table_name'] ."'
		";
		foreach( $this->dao->fetchAll( $sql ) as $kl => $vl ) {

			$labels['สถานะ'] = array();
		

			if( !empty( $status ) ) {
				$labels['สถานะ'][] = $status;
			}
			else if( $vl->status_id == 2 ) {
				$labels['สถานะ'][] = '<span class="mr10"><span class="mt0 label label-default large" style="background-color: red">ไม่อนุมัติ</span></span>';
			}
			else {
				
				$labels['สถานะ'][] = '<span class="mr10"><span class="mt0 label label-default large" style="background-color: #83c340">อนุมัติ</span></span>';
			}

		}

		$sql = "
			SELECT

				l.title,
				l.color
			FROM label_table lt
			INNER JOIN labels l ON lt.label_id = l.id
			WHERE lt.doc_id = ". $doc_id ."
			AND l.deleted = 0
			AND lt.tbName = '". $this->getRolePermission['table_name'] ."'

		";

		foreach( $this->dao->fetchAll( $sql ) as $kl => $vl ) {

			$labels['คำกำกับ'][] = '<span class="mt0 label large " style="background-color:'. $vl->color .';" title="คำกำกับ">'. $vl->title .'</span> ';

		}

		if( in_array( 'client_id', $this->showColumns( $this->getRolePermission['table_name'] ) ) ) {

			$sql = "
				SELECT

					inv.client_id,
					c.company_name
				FROM ".  $this->getRolePermission['table_name'] ." inv
				INNER JOIN clients c ON inv.client_id = c.id
				WHERE inv.id = ". $doc_id ."
			";

			foreach( $this->dao->fetchAll( $sql ) as $kl => $vl ) {

				$labels['บริษัท'][] = '<span class="ml15"><a href="'. base_url( 'index.php/clients/view/'. $vl->client_id ) .'">'. $vl->company_name .'</a></span> ';

			}
		}
		
	
		if( in_array( 'created_by', $this->showColumns( $this->getRolePermission['table_name'] ) ) ) {

			$sql = "
				SELECT

					inv.created_by,
					CONCAT( c.first_name, ' ', c.last_name ) as creator
				FROM ".  $this->getRolePermission['table_name'] ." inv
				INNER JOIN users c ON inv.created_by = c.id
				WHERE inv.id = ". $doc_id ."
			";

			foreach( $this->dao->fetchAll( $sql ) as $kl => $vl ) {

				$labels['สร้างโดย'][] = '<span class="ml15"><a href="'. base_url( 'index.php/clients/view/'. $vl->created_by ) .'">'. $vl->creator .'</a></span> ';

			}
		}
		

		foreach( $labels as $kl => $vl ) {

			$keep[] = '<b>'. $kl .'</b>:'. implode( ' ', $vl ) .'';
		}


		return '<div class="panel panel-default  p15 no-border m0">'. implode( ' ', $keep ) .'</div>';


	}



}
