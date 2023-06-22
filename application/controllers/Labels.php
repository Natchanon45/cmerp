<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Labels extends MY_Controller {

    function __construct() {
		 
        parent::__construct();
		
		$this->load->model( 'db_model' );
		
		$this->dao = $this->db_model;
		
		
		if( empty( $this->getRolePermission['p']['can_setting'] ) ) {
			
			echo permissionBlock();
			
			exit;
		}
    }


    private function can_access_labels_of_this_context($context = "", $label_id = 0) {
        if ($context == "project" && $this->can_edit_projects()) {
            return true;
        } else if ($context == "ticket") {
            $this->init_permission_checker("ticket");
            return $this->access_only_allowed_members();
        } else if ($context == "invoice") {
            $this->init_permission_checker("invoice");
            return $this->access_only_allowed_members();
        } else if ($context == "event" || $context == "note" || $context == "to_do") {
            if ($label_id) {
                //can access only own labels if there has any associated user id with this label
                $label_info = $this->Labels_model->get_one($label_id);
                if ($label_info->user_id && $label_info->user_id !== $this->login_user->id) {
                    return false;
                }
            }

            return true;
        } else if ($context == "task") {
            if ($this->can_manage_all_projects() || get_array_value($this->login_user->permissions, "can_edit_tasks") == "1") {
                return true;
            }
        }
    }


    private function _make_existing_labels_data($type) {
        $labels_dom = "";
        $labels_where = array(
            "context" => $type
        );

        if ($type == "event" || $type == "note" || $type == "to_do") {
            $labels_where["user_id"] = $this->login_user->id;
        }

        $labels = $this->Labels_model->get_details($labels_where)->result();

        foreach ($labels as $label) {
            $labels_dom .= $this->_get_labels_row_data($label);
        }

        return $labels_dom;
    }

    private function _get_labels_row_data($data) {
        return "<span data-act='label-edit-delete' data-id='" . $data->id . "' data-color='" . $data->color . "' class='label large mr5 clickable' style='background-color: " . $data->color . "'>" . $data->title . "</span>";
    }

    function save() {
        validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "type" => "required"
        ));

        $id = $this->input->post("id");
        ///echo $context = $this->input->post("type");
		
		
		if( isset( $_SESSION['table_name'] ) ) {
			
			//$context = $_SESSION['table_name'];
		}
		else {
			
			
		}
		$context = $this->input->post("type");

        if (!$this->can_access_labels_of_this_context($context, $id)) {
            //redirect("forbidden");
        }

        $label_data = array(
            "context" => $context,
            "title" => $this->input->post("title"),
            "color" => $this->input->post("color")
        );

        //save user_id for only events and personal notes
      //  if ($context == "event" || $context == "to_do" || $context == "note") {
            $label_data["user_id"] = $this->login_user->id;
       // }
	   
	 ///  arr(  $label_data );

        $save_id = $this->Labels_model->save( $label_data, $id );

        if ($save_id) {
            $label_info = $this->Labels_model->get_one($save_id);
            echo json_encode(array("success" => true, 'data' => $this->_get_labels_row_data($label_info), 'id' => $id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function delete() {
		
		
        $id = $this->input->post("id");
		
        $type = $this->input->post("type");
		
		
		$sql = "
		
			SELECT 
				GROUP_CONCAT( DISTINCT `label_id` ) as  labels
			FROM `label_table` WHERE `tbName` = '". $type ."'
		
			
		";
		
		foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {
			

			if( in_array( $id, explode( ',', $va->labels ) ) ) {
				echo json_encode(array( "success" => false, 'label_exists' => true, 'message' => 'ป้ายกำกับนี้ถูกใช้งานอยู่' ));

				exit;
			}

		}
		
		$sql = "
			DELETE FROM `labels` WHERE `id` = ". $id .";
		";

		$this->dao->execDatas( $sql );
		
		echo json_encode(array("success" => true, 'id' => $id, 'message' => lang('record_saved')));
		 
		
    }
	
	function index() {
		
		
        redirect("forbidden");
    }

	function saveRolePermission() {
		
		$table = 'role_permission';

		$context = $this->input->post();
		
		$config_id = 184;
		$config = getConfig_( $config_id );

		foreach( $config->columns as $kc => $vc ) {
			
			if( !isset( $context[$kc] ) ) {
				$context[$kc] = 0;
			}
			
			$datas[$kc] = $context[$kc]; 
		}
		
		if( !empty( $context['pri_key'] ) ) {
			
			
			if( !empty( $context['action'] ) ) {
				
				$condition['id'] = $context['pri_key'];
				$json['success'] = $this->dao->delete( $table, $condition );
				 
				 
			}
			else {
				
				$condition = "id = ". $context['pri_key'] ."";
				$json['success'] = $this->dao->update( $table, $datas, $condition );
			}
		}
		else { 
		
			$json['success'] = $this->dao->insert( $table, $datas );
		}
		
		
		if( $json['success'] == false ) {
			$json['message'] = 'เพิ่มข้อมูลผิดพลาด';
		}
	
		//$param['table_name'] = '';
		$json['table'] = $this->ajax_table( $datas );
		
		echo json_encode( $json ) ;
		
	}
	
	
	function ajax_table( $param = array() ) {
		
		
		
		$sql = "
			SELECT * FROM role_permission WHERE table_name LIKE '". $param['table_name'] ."'
		";
		
		return $this->getTable( $this->dao->fetchAll( $sql ) );
	}
	
	
	
	
	//
	//
	function getTable( $datas, $config = array() ) {
		
		$status = 'ready';
		//$status = 'edit';
		//$status = 'add';
		
		$config_id = 184;
		$config = getConfig_( $config_id );
		
		$r = 0;
		foreach( $datas as $kg => $vg ) {
			
			$tds = array();
			
			$tds['ready'][] = '<td class="">'. ( $r + 1  ) .'</td>';
			$tds['edit'][] = '<td class="">'. ( $r + 1  ) .'</td>';
			 
				
			foreach( $config->columns as $kc => $vc ) {
			
				if( empty( $vc->show ) ) {
					continue;
				}
				
				$tds['ready'][] = '<td class="'. $vc->a .'">'. getVal( $vg->$kc, $vc ) .'</td>';
				$tds['edit'][] = '<td class="'. $vc->a .'">'. getInput( $vc, $vg->$kc, '', $kc ) .'</td>';
			}
			
			
			$tds['ready'][] = '
				<td class="C">
					
					
					<a data-id="'. $vg->id .'" class="delete-row" title="ลบข้อมูล"><i class="fa fa-trash-o"></i></a> &nbsp;&nbsp;
					
					<a data-id="'. $vg->id .'" class="edit-row" title="แก้ไขข้อมูล"><i class="fa fa-edit"></i></a>
				
				</td>
			';
			
			$tds['edit'][] = '
				<td class="C">
			
					 
					<a class="confirm-row" title="บันทึกข้อมูล"><i class="fa fa-save"></i></a>

					<a data-id="'. $vg->id .'" class="cancel-row" title="ยกเลิก"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				
				</td>
			';
		
			
			$trs['ready'][$vg->id] = '<tr>'. implode( '', $tds['ready'] ) .'</tr>';
			
			$trs['edit'][$vg->id] = '<tr>'. implode( '', $tds['edit'] ) .'</tr>';
			
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
		$getTrOnStep = getTrOnStep( @$config->columns );
		$maxRow = count( $getTrOnStep );
		if(is_array($getTrOnStep)) foreach ( $getTrOnStep as $kl => $vl ) {

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

			if ( $kl == 0 )
				$ths[] = '<th rowspan="'. $maxRow .'"></th>';

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
	

	function modal_form() {
		

 
		
		$type = $this->input->post("type");
     

        if ( $type ) {
			
			$param['table_name'] = $type;
            $model_info = new stdClass();
            $model_info->color = "";

            $view_data["type"] = $type;
			
            $view_data["model_info"] = $model_info;

            $view_data["existing_labels"] = $this->_make_existing_labels_data($type);
		 	
	
			$view_data["gogo"] = '

			<script type="text/javascript" src="'. base_url( 'assets/js/jquery.form.js' ) .'"></script>
			'. form_open(get_uri("labels/saveRolePermission"), array("id" => "bomb-form", "class" => "form-role", "role" => "form")) .'
			<input type="submit" style=" " class="sub_hidden_submit">
			<input type="hidden" name="table_name" value="'. $type .'">
			<input type="hidden" name="pri_key" value="" />
			<input type="hidden" name="action" value="delete" />
			<div class="load-table">
			'. $this->ajax_table( $param ) .'
			</div>
			
			</form>
			
			<script>
		
			$( function() {
				
				$( \'#bomb-form\'  ).on( \'click\', \'.delete-row\', function() {
					
					me = $( this );
					
					$( \'[name="action"]\' ).val( \'delete\' );
					
					$( \'[name="pri_key"]\' ).val( me.attr( \'data-id\' ) );
					
					$( \'.sub_hidden_submit\' ).trigger( \'click\' );
					
					
					
				});
				
				$( \'#bomb-form\' ).on( \'click\', \'.confirm-row\', function() {
					
					$( \'.sub_hidden_submit\' ).trigger( \'click\' );
				});
				
				$( \'#bomb-form\' ).on( \'click\', \'.add-new-row\', function() {
					
					$( \'[name="action"]\' ).val( \'\' );
					
					$( \'[name="pri_key"]\' ).val( \'\' );
					
					me = $( this );
					
					parent_tr = me.parents( \'tr\' );
					
					parent_tr.replaceWith( trs.add );
					
					$( \'.delete-row, .edit-row, .add-new-row\' ).fadeOut();
				});
				
				
				
				//
				//
				$( \'.sub_hidden_submit\' ).on( \'click\', function () {
					
					var completed = \'0%\';
					
					$( \'.form-role\' ).ajaxForm({
						beforeSend: function() {
							var data = {};
							
						},
						complete: function( response ) {
							data = $.parseJSON( response.responseText );
							
							if( data.success == 1 ) {
								$( \'.load-table\' ).html( data.table );
							}
							else {
								
								alert( data.message );
							}
							
							return false;
					
						}
					});
				});
				
				$( \'#bomb-form\' ).on( \'click\', \'.cancel-row\', function() {
					
					$( \'[name="action"]\' ).val( \'delete\' );
					
					me = $( this );
					
					parent_tr = me.parents( \'tr\' );
					
					parent_tr.replaceWith( trs.ready[me.attr( \'data-id\' ) ] );
					
					$( \'.delete-row, .edit-row, .add-new-row\' ).fadeIn();
				});
				
				$( \'#bomb-form\' ).on( \'click\', \'.edit-row\', function() {
					
					
					me = $( this );
					$( \'[name="action"]\' ).val( \'\' );
					$( \'[name="pri_key"]\' ).val( me.attr( \'data-id\' ) );
					
					
					
					parent_tr = me.parents( \'tr\' );
					
					parent_tr.replaceWith( trs.edit[me.attr( \'data-id\' ) ] );
					$( \'.delete-row, .edit-row, .add-new-row\' ).fadeOut();
					
					
					
				});
				 
		
			});
			</script>
				
			';
			
			$view_data["gogo"] = '';
           $this->load->view("labels/modal_form", $view_data);
        }
    }
	


}

/* End of file Labels.php */
/* Location: ./application/controllers/Labels.php */