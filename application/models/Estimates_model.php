<?php

class Estimates_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'estimates';
        $this->load->model('Db_model');
        parent::__construct($this->table);
    }

    function get_details($options = array(), $getRolePermission = array() ) {
	//PRINT_R( $this->input->post());
        $estimates_table = $this->db->dbprefix('estimates');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');
        $estimate_items_table = $this->db->dbprefix('estimate_items');
        $projects_table = $this->db->dbprefix('projects');
      
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $estimates_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $estimates_table.client_id=$client_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($estimates_table.estimate_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.estimate_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.estimate_value,0))";

        $discountable_estimate_value = "IF($estimates_table.discount_type='after_tax', (IFNULL(items_table.estimate_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.estimate_value,0) )";

        $discount_amount = "IF($estimates_table.discount_amount_type='percentage', IFNULL($estimates_table.discount_amount,0)/100* $discountable_estimate_value, $estimates_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.estimate_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.estimate_value,0)- $discount_amount))";

        $estimate_value_calculation = "(
            IFNULL(items_table.estimate_value,0)+
            IF($estimates_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

     

        $status = get_array_value( $options, "status");
        if ($status) {
            $where .= " AND $estimates_table.status='$status'";
        }

        $exclude_draft = get_array_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $estimates_table.status!='draft' ";
        }


        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("estimates", $custom_fields, $estimates_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

//

        $sql = "
			SELECT
				$estimates_table.id,

				IF(
					pt.status_id IS NULL,
					$estimates_table.status,
					IF(
						pt.status_id = 1,
						IF(
							(
								SELECT
									count( * )
								FROM invoice_items
								WHERE lock_parent_id = $estimates_table.id
								LIMIT 0, 1
							) > 0,
							'sent',
							'accepted'
						),
						'declined'
					)
				) as status,


				$estimates_table.doc_no, 
				$estimates_table.created_by, 
				$estimates_table.client_id, $estimates_table.estimate_request_id, 
				$estimates_table.estimate_date, $estimates_table.valid_until, $estimates_table.note, $estimates_table.last_email_sent_date, $estimates_table.tax_id, $estimates_table.tax_id2, $estimates_table.discount_type, $estimates_table.discount_amount, $estimates_table.discount_amount_type, $estimates_table.project_id, $estimates_table.deleted,


				$clients_table.currency,
				$clients_table.currency_symbol, $clients_table.company_name, $projects_table.title as project_title, $clients_table.is_lead,
				$estimate_value_calculation AS estimate_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
			FROM $estimates_table

			LEFT JOIN prove_table pt ON $estimates_table.id = pt.doc_id AND pt.tbName = '". $estimates_table ."'
			LEFT JOIN $clients_table ON $clients_table.id= $estimates_table.client_id
			LEFT JOIN $projects_table ON $projects_table.id= $estimates_table.project_id
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $estimates_table.tax_id
			LEFT JOIN (
				SELECT $taxes_table.* FROM $taxes_table
			) AS tax_table2 ON tax_table2.id = $estimates_table.tax_id2
			LEFT JOIN (
				SELECT estimate_id, SUM(total) AS estimate_value FROM $estimate_items_table WHERE deleted=0 GROUP BY estimate_id
			) AS items_table ON items_table.estimate_id = $estimates_table.id
			$join_custom_fieds
			[WHERE]
			[HAVING]
		";


		$filters = array();

		if( isset( $this->getRolePermission['filters'] ) ) {
			$filters = $this->getRolePermission['filters'];
		}
//arr($this->input->post() );
		if( !empty( $this->input->post( 'status' ) ) ) {
			//
			$filters['HAVING'][] = "status = '". $this->input->post( 'status' ) ."'";
		}
		if( !empty( $this->input->post( 'end_date' ) ) ) {
			//$estimates_table.estimate_date
			$filters['WHERE'][] = "LAST_DAY( $estimates_table.estimate_date ) = LAST_DAY( '". $this->input->post( 'end_date' ) ."' )";
		}

 
		if( !empty( $options['id'] ) ) {
			$filters['WHERE'][] = "". $estimates_table .".id = ". $options['id'] ."";
		}
 
        if($where) {
            $filters['WHERE'][] = " 1 ". $where;
        }

        $sql = gencond_( $sql, $filters );
        // arr($sql);
 
        return $this->db->query($sql);

    }

    function get_estimate_total_summary($estimate_id = 0) {
		//estimate_total
        $estimate_items_table = $this->db->dbprefix('estimate_items');
        $estimates_table = $this->db->dbprefix('estimates');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $item_sql = "SELECT SUM($estimate_items_table.total) AS estimate_subtotal
        FROM $estimate_items_table
        LEFT JOIN $estimates_table ON $estimates_table.id= $estimate_items_table.estimate_id
        WHERE $estimate_items_table.deleted=0 AND $estimate_items_table.estimate_id=$estimate_id AND $estimates_table.deleted=0";
        $item = $this->db->query($item_sql)->row();


        $estimate_sql = "SELECT $estimates_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $estimates_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $estimates_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $estimates_table.tax_id2
        WHERE $estimates_table.deleted=0 AND $estimates_table.id=$estimate_id";
        $estimate = $this->db->query($estimate_sql)->row();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$estimate->client_id";
        $client = $this->db->query($client_sql)->row();


        $result = new stdClass();
        $result->estimate_subtotal = $item->estimate_subtotal;
        $result->tax_percentage = $estimate->tax_percentage;
        $result->tax_percentage2 = $estimate->tax_percentage2;
        $result->tax_name = $estimate->tax_name;
        $result->tax_name2 = $estimate->tax_name2;
        $result->tax_id = $estimate->tax_id;
        $result->tax_id2 = $estimate->tax_id2;
        $result->tax = 0;
        $result->tax2 = 0;

        
        $result->deposit = $estimate->deposit;

        


        $estimate_subtotal = $result->estimate_subtotal;// - $result->deposit
        $estimate_subtotal_for_taxes = $estimate_subtotal;
        
        if ($estimate->discount_type == "before_tax") {
            $estimate_subtotal_for_taxes = $estimate_subtotal - ($estimate->discount_amount_type == "percentage" ? ($estimate_subtotal * ($estimate->discount_amount / 100)) : $estimate->discount_amount);
        }

        if ($estimate->tax_percentage) {
            $result->tax = $estimate_subtotal_for_taxes * ($estimate->tax_percentage / 100);
           
        }
        if ($estimate->tax_percentage2) {
            $result->tax2 = $estimate_subtotal_for_taxes * ($estimate->tax_percentage2 / 100);
           
        }

        if($estimate->tax_id2 == 1){
            $estimate_total = $estimate_subtotal + $result->tax + $result->tax2;
        }else{
            $estimate_total = $estimate_subtotal + $result->tax - $result->tax2;
        }
       

        //get discount total
        $result->discount_total = 0;
        if ($estimate->discount_type == "after_tax") {
            $estimate_subtotal = $estimate_total;
        }

        $result->discount_total = $estimate->discount_amount_type == "percentage" ? ($estimate_subtotal * ($estimate->discount_amount / 100)) : $estimate->discount_amount;

        $result->discount_type = $estimate->discount_type;

        $result->estimate_total = $estimate_total - number_format($result->discount_total, 2, ".", "");
        // var_dump($result->estimate_total);exit;
		
		$sql = "
			UPDATE estimates 
			SET total_estimate = ". $result->estimate_total.", sub_total_estimate = ".($estimate_subtotal-$result->discount_total)."

			
			WHERE id = ". $estimate_id ."
		";
		
		$this->Db_model->execDatas( $sql );
		
        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");
        return $result;
    }

    //get estimate last id
    function get_estimate_last_id() {
        $estimates_table = $this->db->dbprefix('estimates');

        $sql = "SELECT MAX($estimates_table.id) AS last_id FROM $estimates_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of estimate
    function save_initial_number_of_estimate($value) {
        $estimates_table = $this->db->dbprefix('estimates');

        $sql = "ALTER TABLE $estimates_table AUTO_INCREMENT=$value;";

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
          ei.estimate_id as estimateId,
          estimates.doc_no,
          ei.estimate_id,
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
            FROM estimate_items ei
            LEFT JOIN estimates on estimates.id = ei.estimate_id
            INNER JOIN prove_table ptt ON ptt.tbName = 'estimates' AND ei.estimate_id = ptt.doc_id
            GROUP BY estimate_id
            $where
          ";
//

          $filters = array();
          $filters['WHERE'][] = "
                it.estimate_id = ei.estimate_id
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
