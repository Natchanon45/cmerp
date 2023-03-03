<?php

class Deliverys_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'deliverys';
        $this->load->model('Db_model');
        parent::__construct($this->table);
    }

    function get_details($options = array() ) {
	//PRINT_R( $this->input->post());
        $deliverys_table = $this->db->dbprefix('deliverys');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');
        $delivery_items_table = $this->db->dbprefix('delivery_items');
        $projects_table = $this->db->dbprefix('projects');
      
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $deliverys_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $deliverys_table.client_id=$client_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($deliverys_table.delivery_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.delivery_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.delivery_value,0))";

        $discountable_delivery_value = "IF($deliverys_table.discount_type='after_tax', (IFNULL(items_table.delivery_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.delivery_value,0) )";

        $discount_amount = "IF($deliverys_table.discount_amount_type='percentage', IFNULL($deliverys_table.discount_amount,0)/100* $discountable_delivery_value, $deliverys_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.delivery_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.delivery_value,0)- $discount_amount))";

        $delivery_value_calculation = "(
            IFNULL(items_table.delivery_value,0)+
            IF($deliverys_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

     

        $status = get_array_value( $options, "status");
        if ($status) {
            $where .= " AND $deliverys_table.status='$status'";
        }

        $exclude_draft = get_array_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $deliverys_table.status!='draft' ";
        }


        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("deliverys", $custom_fields, $deliverys_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

//

        $sql = "
			SELECT
				$deliverys_table.id,

				IF(
					pt.status_id IS NULL,
					$deliverys_table.status,
					IF(
						pt.status_id = 1,
						IF(
							(
								SELECT
									count( * )
								FROM invoice_items
								WHERE lock_parent_id = $deliverys_table.id
								LIMIT 0, 1
							) > 0,
							'sent',
							'accepted'
						),
						'declined'
					)
				) as status,


				$deliverys_table.doc_no, 
				$deliverys_table.created_by, 
				$deliverys_table.client_id, $deliverys_table.delivery_request_id, 
				$deliverys_table.delivery_date, $deliverys_table.valid_until, $deliverys_table.note, $deliverys_table.last_email_sent_date, $deliverys_table.tax_id, $deliverys_table.tax_id2, $deliverys_table.discount_type, $deliverys_table.discount_amount, $deliverys_table.discount_amount_type, $deliverys_table.project_id, $deliverys_table.deleted,


				$clients_table.currency,
				$clients_table.currency_symbol, $clients_table.company_name, $projects_table.title as project_title, $clients_table.is_lead,
				$delivery_value_calculation AS delivery_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
			FROM $deliverys_table

			LEFT JOIN prove_table pt ON $deliverys_table.id = pt.doc_id AND pt.tbName = '". $deliverys_table ."'
			LEFT JOIN $clients_table ON $clients_table.id= $deliverys_table.client_id
			LEFT JOIN $projects_table ON $projects_table.id= $deliverys_table.project_id
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $deliverys_table.tax_id
			LEFT JOIN (
				SELECT $taxes_table.* FROM $taxes_table
			) AS tax_table2 ON tax_table2.id = $deliverys_table.tax_id2
			LEFT JOIN (
				SELECT delivery_id, SUM(total) AS delivery_value FROM $delivery_items_table WHERE deleted=0 GROUP BY delivery_id
			) AS items_table ON items_table.delivery_id = $deliverys_table.id
			$join_custom_fieds
			[WHERE]
			[HAVING]
		";


		$filters = array();

		// if( isset( $this->getRolePermission['filters'] ) ) {
		// 	$filters = $this->getRolePermission['filters'];
		// }
//arr($this->input->post() );
		if( !empty( $this->input->post( 'status' ) ) ) {
			//
			$filters['HAVING'][] = "status = '". $this->input->post( 'status' ) ."'";
		}
		if( !empty( $this->input->post( 'end_date' ) ) ) {
			//$deliverys_table.delivery_date
			$filters['WHERE'][] = "LAST_DAY( $deliverys_table.delivery_date ) = LAST_DAY( '". $this->input->post( 'end_date' ) ."' )";
		}

 
		if( !empty( $options['id'] ) ) {
			$filters['WHERE'][] = "". $deliverys_table .".id = ". $options['id'] ."";
		}
 
        if($where) {
            $filters['WHERE'][] = " 1 ". $where;
        }

        $sql = gencond_( $sql, $filters );
        // arr($sql);exit;
 
        return $this->db->query($sql);

    }

    function get_delivery_total_summary($delivery_id = 0) {
		//delivery_total
        $delivery_items_table = $this->db->dbprefix('delivery_items');
        $deliverys_table = $this->db->dbprefix('deliverys');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $item_sql = "SELECT SUM($delivery_items_table.total) AS delivery_subtotal
        FROM $delivery_items_table
        LEFT JOIN $deliverys_table ON $deliverys_table.id= $delivery_items_table.delivery_id
        WHERE $delivery_items_table.deleted=0 AND $delivery_items_table.delivery_id=$delivery_id AND $deliverys_table.deleted=0";
        $item = $this->db->query($item_sql)->row();


        $delivery_sql = "SELECT $deliverys_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $deliverys_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $deliverys_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $deliverys_table.tax_id2
        WHERE $deliverys_table.deleted=0 AND $deliverys_table.id=$delivery_id";
        $delivery = $this->db->query($delivery_sql)->row();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$delivery->client_id";
        $client = $this->db->query($client_sql)->row();


        $result = new stdClass();
        $result->delivery_subtotal = $item->delivery_subtotal;
        $result->tax_percentage = $delivery->tax_percentage;
        $result->tax_percentage2 = $delivery->tax_percentage2;
        $result->tax_name = $delivery->tax_name;
        $result->tax_name2 = $delivery->tax_name2;
        $result->tax_id = $delivery->tax_id;
        $result->tax_id2 = $delivery->tax_id2;
        $result->tax = 0;
        $result->tax2 = 0;

        
        $result->deposit = $delivery->deposit;

        


        $delivery_subtotal = $result->delivery_subtotal;// - $result->deposit
        $delivery_subtotal_for_taxes = $delivery_subtotal;
        
        if ($delivery->discount_type == "before_tax") {
            $delivery_subtotal_for_taxes = $delivery_subtotal - ($delivery->discount_amount_type == "percentage" ? ($delivery_subtotal * ($delivery->discount_amount / 100)) : $delivery->discount_amount);
        }

        if ($delivery->tax_percentage) {
            $result->tax = $delivery_subtotal_for_taxes * ($delivery->tax_percentage / 100);
           
        }
        if ($delivery->tax_percentage2) {
            $result->tax2 = $delivery_subtotal_for_taxes * ($delivery->tax_percentage2 / 100);
           
        }

        if($delivery->tax_id2 == 1){
            $delivery_total = $delivery_subtotal + $result->tax + $result->tax2;
        }else{
            $delivery_total = $delivery_subtotal + $result->tax - $result->tax2;
        }
       

        //get discount total
        $result->discount_total = 0;
        if ($delivery->discount_type == "after_tax") {
            $delivery_subtotal = $delivery_total;
        }

        $result->discount_total = $delivery->discount_amount_type == "percentage" ? ($delivery_subtotal * ($delivery->discount_amount / 100)) : $delivery->discount_amount;

        $result->discount_type = $delivery->discount_type;

        $result->delivery_total = $delivery_total - number_format($result->discount_total, 2, ".", "");
        // var_dump($result->delivery_total);exit;
		
		$sql = "
			UPDATE deliverys 
			SET total_delivery = ". $result->delivery_total.", sub_total_delivery = ".($delivery_subtotal-$result->discount_total)."

			
			WHERE id = ". $delivery_id ."
		";
		
		$this->Db_model->execDatas( $sql );
		
        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");
        return $result;
    }

    //get delivery last id
    function get_delivery_last_id() {
        $deliverys_table = $this->db->dbprefix('deliverys');

        $sql = "SELECT MAX($deliverys_table.id) AS last_id FROM $deliverys_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of delivery
    function save_initial_number_of_delivery($value) {
        $deliverys_table = $this->db->dbprefix('deliverys');

        $sql = "ALTER TABLE $deliverys_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    public function getEstimate_Item_suggestion( $keyword = "", $id = null,$datas = NULL ) {

        //  $keyword = $this->db->escape_str($keyword);
        
        if($id || $datas){
            $where = '';
        }else{
            $where = 'HAVING needQuality > recetpQty';
        }

          $sql = "
          SELECT
          ei.delivery_id as deliveryId,
          deliverys.doc_no,
          ei.delivery_id,
          sum(ei.quantity) as needQuality,
          IFNULL(
					(
						SELECT
							sum( it.quantity )
						FROM invoice_items it
						INNER JOIN invoices inv ON it.invoice_id = inv.id
						[WHERE]

					)
					,0
				) as recetpQty
            FROM delivery_items ei
            LEFT JOIN deliverys on deliverys.id = ei.delivery_id
            INNER JOIN prove_table ptt ON ptt.tbName = 'deliverys' AND ei.delivery_id = ptt.doc_id
            GROUP BY delivery_id
            $where
          ";
//

          $filters = array();
          $filters['WHERE'][] = "
                it.delivery_id = ei.delivery_id
                AND it.deleted = 0
                AND inv.deleted = 0
          ";

          if( $id != '' ){
              $filters['WHERE'][] = "it.invoice_id != ". $id ."";
          }
        $sql = gencond_( $sql, $filters );
// arr($sql  );
          return $this->db->query($sql)->result();


      }

}
