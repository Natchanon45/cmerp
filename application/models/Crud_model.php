<?php

//extend from this model to execute basic db operations
class Crud_model extends CI_Model {

    private $table;
    private $log_activity = false;
    private $log_type = "";
    private $log_for = "";
    private $log_for_key = "";
    private $log_for2 = "";
    private $log_for_key2 = "";

    function __construct($table = null) {
		
		
		
		//global $class;
		///echo $table;
		
		////echo '<br>';
		
        $this->use_table( $table );

        $this->db->query("SET sql_mode = ''");
		
		
    }

    protected function use_table($table) {
        $this->table = $table;
    }

    protected function disable_log_activity() {
        $this->log_activity = false;
    }

    protected function init_activity_log($log_type = "", $log_type_title_key = "", $log_for = "", $log_for_key = 0, $log_for2 = "", $log_for_key2 = 0) {
        if ($log_type) {
            $this->log_activity = true;
            $this->log_type = $log_type;
            $this->log_type_title_key = $log_type_title_key;
            $this->log_for = $log_for;
            $this->log_for_key = $log_for_key;
            $this->log_for2 = $log_for2;
            $this->log_for_key2 = $log_for_key2;
        }
    }

    function get_one($id = 0) {
        return $this->get_one_where(array('id' => $id));
    }

    function get_one_where($where = array()) {
        $result = $this->db->get_where($this->table, $where, 1);
        if ($result->num_rows()) {
            return $result->row();
        } else {
            $db_fields = $this->db->list_fields($this->table);
            $fields = new stdClass();
            foreach ($db_fields as $field) {
                $fields->$field = "";
            }
            return $fields;
        }
    }

    function get_all($include_deleted = false) {
        $where = array("deleted" => 0);
        if ($include_deleted) {
            $where = array();
        }
        return $this->get_all_where($where);
    }



	function update_where($data = array(), $where = array()) {
        if (count($where)) {
           // var_dump($this->db->update($this->table, $data, $where));exit;
            if ($this->db->update($this->table, $data, $where)) {
                $id = get_array_value($where, "id");
                if ($id) {
                    return $id;
                } else {
                    return true;
                }
            }
        }
    }



    //prepare a query string to get custom fields like as a normal field
    protected function prepare_custom_field_query_string($related_to, $custom_fields, $related_to_table) {

        $join_string = "";
        $select_string = "";
        $custom_field_values_table = $this->db->dbprefix('custom_field_values');


        if ($related_to && $custom_fields) {
            foreach ($custom_fields as $cf) {
                $cf_id = $cf->id;
                $virtual_table = "cfvt_$cf_id"; //custom field values virtual table

                $select_string .= " , $virtual_table.value AS cfv_$cf_id ";
                $join_string .= " LEFT JOIN $custom_field_values_table AS $virtual_table ON $virtual_table.related_to_type='$related_to' AND $virtual_table.related_to_id=$related_to_table.id AND $virtual_table.deleted=0 AND $virtual_table.custom_field_id=$cf_id ";
            }
        }

        return array("select_string" => $select_string, "join_string" => $join_string);
    }

    //get query of clients data according to to currency
    protected function _get_clients_of_currency_query($currency, $invoices_table, $clients_table) {
        $default_currency = get_setting("default_currency");
        $currency = $currency ? $currency : $default_currency;

        $client_where = ($currency == $default_currency) ? " AND $clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL" : " AND $clients_table.currency='$currency'";

        return " AND $invoices_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 $client_where)";
    }

    //get total invoice value calculation query
    protected function _get_invoice_value_calculation_query($invoices_table) {
        $select_invoice_value = "IFNULL(items_table.invoice_value,0)";

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*$select_invoice_value)";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*$select_invoice_value)";
        $after_tax_3 = "(IFNULL(tax_table3.percentage,0)/100*$select_invoice_value)";

        $discountable_invoice_value = "IF($invoices_table.discount_type='after_tax', (($select_invoice_value + $after_tax_1 + $after_tax_2) - $after_tax_3), $select_invoice_value )";

        $discount_amount = "IF($invoices_table.discount_amount_type='percentage', IFNULL($invoices_table.discount_amount,0)/100* $discountable_invoice_value, $invoices_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* ($select_invoice_value- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* ($select_invoice_value- $discount_amount))";
        $before_tax_3 = "(IFNULL(tax_table3.percentage,0)/100* ($select_invoice_value- $discount_amount))";


        $invoice_value_calculation_query = "(
                $select_invoice_value+
                IF($invoices_table.discount_type='before_tax',  (($before_tax_1+ $before_tax_2) - $before_tax_3), (($after_tax_1 + $after_tax_2) - $after_tax_3))
                - $discount_amount
               )";

        return $invoice_value_calculation_query;
    }

    //get total receipt_taxinvoice value calculation query
    protected function _get_receipt_taxinvoice_value_calculation_query($receipt_taxinvoices_table) {
        $select_receipt_taxinvoice_value = "IFNULL(items_table.receipt_taxinvoice_value,0)";

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*$select_receipt_taxinvoice_value)";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*$select_receipt_taxinvoice_value)";
        $after_tax_3 = "(IFNULL(tax_table3.percentage,0)/100*$select_receipt_taxinvoice_value)";

        $discountable_receipt_taxinvoice_value = "IF($receipt_taxinvoices_table.discount_type='after_tax', (($select_receipt_taxinvoice_value + $after_tax_1 + $after_tax_2) - $after_tax_3), $select_receipt_taxinvoice_value )";

        $discount_amount = "IF($receipt_taxinvoices_table.discount_amount_type='percentage', IFNULL($receipt_taxinvoices_table.discount_amount,0)/100* $discountable_receipt_taxinvoice_value, $receipt_taxinvoices_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* ($select_receipt_taxinvoice_value- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* ($select_receipt_taxinvoice_value- $discount_amount))";
        $before_tax_3 = "(IFNULL(tax_table3.percentage,0)/100* ($select_receipt_taxinvoice_value- $discount_amount))";


        $receipt_taxinvoice_value_calculation_query = "(
                $select_receipt_taxinvoice_value+
                IF($receipt_taxinvoices_table.discount_type='before_tax',  (($before_tax_1+ $before_tax_2) - $before_tax_3), (($after_tax_1 + $after_tax_2) - $after_tax_3))
                - $discount_amount
               )";

        return $receipt_taxinvoice_value_calculation_query;
    }

    protected function get_labels_data_query() {
        $labels_table = $this->db->dbprefix("labels");

        return "(SELECT GROUP_CONCAT($labels_table.id, '--::--', $labels_table.title, '--::--', $labels_table.color) FROM $labels_table WHERE FIND_IN_SET($labels_table.id, $this->table.labels)) AS labels_list";
    }

    function delete_permanently($id = 0) {
        if ($id) {
            $this->db->where('id', $id);
            $this->db->delete($this->table);
        }
    }
	
	function insertLabels( $doc_id = NULL, $datas = array(), $tbName = NULL ) {
        $no_labels = ["expenses"];
        if(in_array("expenses", $no_labels)) return;
		
		if( !empty( $tbName ) ) {
			
			$this->table = $tbName;
		}
	 	
		if( isset( $_POST['labels'] ) ) {
			
			$sql = "
				DELETE FROM label_table 
				WHERE doc_id = ". $doc_id ."
				AND tbName = '". $this->table ."'
			";
			
			$this->dao->execDatas( $sql );
	
//arr( $_POST['labels']);	
			foreach( explode( ',', $_POST['labels'] ) as $kl => $label_id ) {
				
				$sql = "
					INSERT INTO label_table ( label_id, doc_id, tbName )
					SELECT
						". $label_id ." as label_id, 
						". $doc_id ." as doc_id, 
						'". $this->table ."' as tbName
				";
			 
				$this->dao->execDatas( $sql );
			}
		}
	}
	
    function setTable($table) {
        $this->table = $table;
    }

    function save( &$data = [], $id = 0 ) {
		
	 	//var_dump($data,$id);exit;
		$config['purchaserequests'] = array( 'prefix' => 'PR' );
        $config['materialrequests'] = array( 'prefix' => 'MR' );
		$config['orders'] = array( 'prefix' => 'OD' );
		$config['payment_vouchers'] = array( 'prefix' => 'PV' );
		$config['estimates'] = array( 'prefix' => 'QT' );
		$config['invoices'] = array( 'prefix' => 'BL' );
        $config['deliverys'] = array( 'prefix' => 'DL' );
	
 	
		if( isset( $config[$this->table] ) ) {
			$param = $config[$this->table];
			$param['LPAD'] = 4;
			$param['column'] = 'doc_no';
			$param['table'] = $this->table;
		}
		
        //unset custom created by field if it's defined for activity log
        $activity_log_created_by_app = false;
        if ( get_array_value($data, "activity_log_created_by_app")) {
            $activity_log_created_by_app = true;
            unset($data["activity_log_created_by_app"]);
        }
        
        if ( $id ) {
			
			$this->insertLabels( $id, $data );
			
			
			if( isset( $config[$this->table] ) &&  in_array( 'doc_no', $this->dao->showColumns( $this->table ) ) ) {
				
				 
				$param['id'] = $id;
				$data['doc_no'] = $this->dao->genDocNo( $param  );
				 			
			}
			
			
            $where = array("id" => $id);

            //to log an activity we have to know the changes. now collect the data before update anything
            if ($this->log_activity) {
                $data_before_update = $this->get_one($id);
            }
            
            $success = $this->update_where( $data, $where );
            //var_dump($success);exit;
            if ( $success ) {
                if ($this->log_activity) {
                    //unset status_changed_at field for task update
                    if ($this->log_type === "task" && isset($data["status_changed_at"])) {
                        unset($data["status_changed_at"]);
                    }
                    
                    //to log this activity, check the changes
                    $fields_changed = array();
                    foreach ($data as $field => $value) {
                        if ($data_before_update->$field != $value) {
                            if($field != "team_id" && $field !="team_name"){
                                $fields_changed[$field] = array("from" => $data_before_update->$field, "to" => $value);
                            }
                           
                            
                        }
                    }

                    // var_dump($fields_changed);

                    
                    //has changes? log the changes.
                    if (count($fields_changed)) {
                        $log_for_id = 0;
                        if ($this->log_for_key) {
                            $log_for_key = $this->log_for_key;
                            $log_for_id = $data_before_update->$log_for_key;
                        }

                        $log_for_id2 = 0;
                        if ($this->log_for_key2) {
                            $log_for_key2 = $this->log_for_key2;
                            $log_for_id2 = $data_before_update->$log_for_key2;
                        }
                    // exit;
                        $log_type_title_key = $this->log_type_title_key;
                        $log_type_title = isset($data_before_update->$log_type_title_key) ? $data_before_update->$log_type_title_key : "";

                        $log_data = array(
                            "action" => "updated",
                            "log_type" => $this->log_type,
                            "log_type_title" => $log_type_title,
                            "log_type_id" => $id,
                            "changes" => serialize($fields_changed),
                            "log_for" => $this->log_for,
                            "log_for_id" => $log_for_id,
                            "log_for2" => $this->log_for2,
                            "log_for_id2" => $log_for_id2,
                        );
                        // var_dump($log_data);exit;
                        $this->Activity_logs_model->save($log_data, $activity_log_created_by_app);
                        $activity_log_id = $this->db->insert_id();
                        $data["activity_log_id"] = $activity_log_id;
                    }
                }
            }
            return $success;
			
        } else {
      // echo 'dsafsfdasdfsdsfddf';
			$this->load->model( 'Db_model' );
	
			if( in_array( 'created_by', $this->Db_model->showColumns( $this->table ) ) ) {
				if( !empty( $_SESSION['user_id'] ) ) {
					
					if( !isset( $data['created_by'] ) ) {
						$data['created_by'] = $_SESSION['user_id'];
					}
				}
			}
			
			if( isset( $config[$this->table] ) && in_array( 'doc_no', $this->Db_model->showColumns( $this->table ) ) ) { 
				$data['doc_no'] = $this->dao->genDocNo( $param  );
				 			
			}
			
	// arr( $this->table );
	// arr( $data );
			//$data['subject'] = 'subject'; 		
            if ($this->db->insert( $this->table, $data ) ) {
                $insert_id = $this->db->insert_id();
				
				
				$this->insertLabels( $insert_id, $data );
                if ($this->log_activity) {
                    //log this activity
                    $log_for_id = 0;
                    if ($this->log_for_key) {
                        $log_for_id = get_array_value($data, $this->log_for_key);
                    }

                    $log_for_id2 = 0;
                    if ($this->log_for_key2) {
                        $log_for_id2 = get_array_value($data, $this->log_for_key2);
                    }

                    $log_type_title = get_array_value($data, $this->log_type_title_key);
                    $log_data = array(
                        "action" => "created",
                        "log_type" => $this->log_type,
                        "log_type_title" => $log_type_title ? $log_type_title : "",
                        "log_type_id" => $insert_id,
                        "log_for" => $this->log_for,
                        "log_for_id" => $log_for_id,
                        "log_for2" => $this->log_for2,
                        "log_for_id2" => $log_for_id2,
                    );
                    $this->Activity_logs_model->save($log_data, $activity_log_created_by_app);
                    $activity_log_id = $this->db->insert_id();
                    $data["activity_log_id"] = $activity_log_id;
                }
                return $insert_id;
            }
        }
    }
	
	
    function delete( $id = 0, $undo = false ) {
		
		$this->load->model( 'db_model' ); 
		
        $param['url'] = '';
		$param['table_name'] = strtolower( $this->table );
		
		$this->getRolePermission = $this->db_model->getRolePermission( $param ); 
		
	
	
		if( !empty( $this->getRolePermission['view_row'] ) ) {
			if( empty( $this->getRolePermission['delete_row'] ) ) {
				echo json_encode(array("success" => false, 'message' => 'คุณไม่มีสิทธิ์ในการลบข้อมูลนี้' ));
				exit;
			}
			
		}
	
	
	
        $data = array('deleted' => 1);
        if ($undo === true) {
            $data = array('deleted' => 0);
        }
        $this->db->where("id", $id);
        $success = $this->db->update($this->table, $data);
        if ($success) {
            if ($this->log_activity) {
                if ($undo) {
                    // remove previous deleted log.
                    $this->Activity_logs_model->delete_where(array("action" => "deleted", "log_type" => $this->log_type, "log_type_id" => $id));
                } else {
                    //to log this activity check the title
                    $model_info = $this->get_one($id);
                    $log_for_id = 0;
                    if ($this->log_for_key) {
                        $log_for_key = $this->log_for_key;
                        $log_for_id = $model_info->$log_for_key;
                    }
                    $log_type_title_key = $this->log_type_title_key;
                    $log_type_title = $model_info->$log_type_title_key;
                    $log_data = array(
                        "action" => "deleted",
                        "log_type" => $this->log_type,
                        "log_type_title" => $log_type_title ? $log_type_title : "",
                        "log_type_id" => $id,
                        "log_for" => $this->log_for,
                        "log_for_id" => $log_for_id,
                    );
                    $this->Activity_logs_model->save($log_data);
                }
            }
        }
        return $success;
    }
	
    function get_all_where( $where = array(), $limit = 1000000, $offset = 0, $sort_by_field = null ) {
      
		$where_in = get_array_value($where, "where_in");
        if ($where_in) {
            foreach ($where_in as $key => $value) {
                $this->db->where_in($key, $value);
            }
            unset($where["where_in"]);
        }

        if ($sort_by_field) {
            $this->db->order_by( $sort_by_field, 'ASC' );
        }

        return $this->db->get_where( $this->table, $where, $limit, $offset);
    }
	
    function get_dropdown_list( $option_fields = array(), $key = "id", $where = array()) {
        $where["deleted"] = 0;
        $list_data = $this->get_all_where( $where, 0, 0, $option_fields[0])->result();
		
		
		//arr( $list_data );
		
		//exit;
        $result = array();
        foreach ($list_data as $data) {
            $text = "";
            foreach ($option_fields as $option) {
                $text .= $data->$option . " ";
            }
            $result[$data->$key] = $text;
        }
        return $result;
    }
}
