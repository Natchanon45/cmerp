<?php

class Receipts_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'receipts';
        parent::__construct($this->table);
    }

    function get_details($options = array(), $filters = array() ) {
        $Receipts_table = $this->db->dbprefix('receipts');
        $clients_table = $this->db->dbprefix('clients');
        $bom_suppliers = $this->db->dbprefix('bom_suppliers');
        $taxes_table = $this->db->dbprefix('taxes');
        $receipt_items_table = $this->db->dbprefix('receipt_items');
        $receipt_status_table = $this->db->dbprefix('order_status');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $Receipts_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $Receipts_table.client_id=$client_id";
        }

        $receipt_date = get_array_value($options, "receipt_date");
        $deadline = get_array_value($options, "deadline");
        if ($receipt_date && $deadline) {
            $where .= " AND ($Receipts_table.receipt_date BETWEEN '$receipt_date' AND '$deadline') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.receipt_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.receipt_value,0))";

        $discountable_receipt_value = "IF($Receipts_table.discount_type='after_tax', (IFNULL(items_table.receipt_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.receipt_value,0) )";

        $discount_amount = "IF($Receipts_table.discount_amount_type='percentage', IFNULL($Receipts_table.discount_amount,0)/100* $discountable_receipt_value, $Receipts_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.receipt_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.receipt_value,0)- $discount_amount))";

        $receipt_value_calculation = "(
            IFNULL(items_table.receipt_value,0)+
            IF($Receipts_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status_id = get_array_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $Receipts_table.status_id='$status_id'";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("Receipts", $custom_fields, $Receipts_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

        $sql = "SELECT 
        
            $bom_suppliers.company_name as su_comname, 
            $bom_suppliers.address as su_add,
            $bom_suppliers.city as su_city,
            $bom_suppliers.state as su_state,
            $bom_suppliers.zip as su_zip,
            $bom_suppliers.country as su_country,
            $bom_suppliers.vat_number as su_vat_number,
            $bom_suppliers.currency as su_currency,
            $bom_suppliers.currency_symbol as su_currency_symbol,
            
            $Receipts_table.*, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name,
           $receipt_value_calculation AS receipt_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2, $receipt_status_table.title AS receipt_status_title, $receipt_status_table.color AS receipt_status_color, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.user_type AS created_by_user_type $select_custom_fieds
        FROM $Receipts_table
        LEFT JOIN $clients_table ON $clients_table.id= $Receipts_table.client_id
        LEFT JOIN $bom_suppliers ON $bom_suppliers.id= $Receipts_table.supplier_id
        LEFT JOIN $receipt_status_table ON $Receipts_table.status_id = $receipt_status_table.id 
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $Receipts_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $Receipts_table.tax_id2 
        LEFT JOIN (SELECT receipt_id, SUM(total) AS receipt_value FROM $receipt_items_table WHERE deleted=0 GROUP BY receipt_id) AS items_table ON items_table.receipt_id = $Receipts_table.id 
        LEFT JOIN $users_table ON $users_table.id=$Receipts_table.created_by
        $join_custom_fieds
        [WHERE]
		";
		
		
		 if($where) {
             $filters['WHERE'][] = "1 ". $where;
         }
		
		//arr( $options );
		if( !empty( $options['id'] ) ) {
			$filters['WHERE'][] = "". $Receipts_table .".id = ". $options['id'] ."";
			$sql = gencond_( $sql, $filters );	
		}
		else {
			$sql = gencond_( $sql, $filters );	
		}
		
		// arr( $sql );exit;

        return $this->db->query($sql);
    }

    function get_processing_receipt_total_summary($user_id) {
        $receipt_items_table = $this->db->dbprefix('receipt_items');
        $Receipts_table = $this->db->dbprefix('Receipts');
        $clients_table = $this->db->dbprefix('clients');
        $users_table = $this->db->dbprefix('users');
        $taxes_table = $this->db->dbprefix('taxes');

        $where = " AND $receipt_items_table.created_by=$user_id";

        $receipt_tax_id = get_setting('receipt_tax_id') ? get_setting('receipt_tax_id') : 0;
        $receipt_tax_id2 = get_setting('receipt_tax_id2') ? get_setting('receipt_tax_id2') : 0;

        $item_sql = "SELECT SUM($receipt_items_table.total) AS receipt_subtotal, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $receipt_items_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $receipt_tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_tax_id2
        WHERE $receipt_items_table.deleted=0 AND $receipt_items_table.receipt_id=0 $where";
        $item = $this->db->query($item_sql)->row();

        $select_receipt_client_id = "";
        if ($user_id) {
            $select_receipt_client_id = "(SELECT $users_table.client_id FROM $users_table WHERE $users_table.id=$user_id)";
        } else {
            $select_receipt_client_id = "(SELECT $Receipts_table.client_id FROM $Receipts_table WHERE $Receipts_table.id=0)";
        }

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$select_receipt_client_id";
        $client = $this->db->query($client_sql)->row();

        $result = new stdClass();

        $result->receipt_subtotal = $item->receipt_subtotal;
        $result->tax_percentage = $item->tax_percentage;
        $result->tax_percentage2 = $item->tax_percentage2;
        $result->tax_name = $item->tax_name;
        $result->tax_name2 = $item->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $receipt_subtotal = $result->receipt_subtotal;
        if ($item->tax_percentage) {
            $result->tax = $receipt_subtotal * ($item->tax_percentage / 100);
        }
        if ($item->tax_percentage2) {
            $result->tax2 = $receipt_subtotal * ($item->tax_percentage2 / 100);
        }

        $result->receipt_total = $item->receipt_subtotal + $result->tax + $result->tax2;

        $result->currency_symbol = isset($client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = isset($client->currency) ? $client->currency : get_setting("default_currency");
        return $result;
    }

    function get_receipt_total_summary( $receipt_id = 0, $payment_id = 0 ) {
		
        $receipt_items_table = $this->db->dbprefix('receipt_items');
        $receipts_table = $this->db->dbprefix('receipts');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $item_sql = "SELECT SUM($receipt_items_table.total) AS receipt_subtotal
        FROM $receipt_items_table
        LEFT JOIN $receipts_table ON $receipts_table.id= $receipt_items_table.receipt_id    
        WHERE $receipt_items_table.deleted=0 AND $receipt_items_table.receipt_id=$receipt_id AND $receipts_table.deleted=0";
        $item = $this->db->query($item_sql)->row();

		$receipt_sql = "
			SELECT 
				$receipts_table.*, 
				tax_table.percentage AS tax_percentage, 
				tax_table.title AS tax_name,
				tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2,
				( 
					SELECT 
						SUM( pv.amount ) 
					FROM payment_voucher_payments pv
					INNER JOIN payment_vouchers pvp ON pv.payment_vouchers_id = pvp.id
					WHERE pv.invoice_id = $receipts_table.id 
					AND pv.id != $payment_id 
					AND pv.deleted = 0
					AND pvp.deleted = 0
				) as payment
			FROM $receipts_table
			LEFT JOIN (
				SELECT 
					$taxes_table.* 
				FROM $taxes_table 
			) AS tax_table ON tax_table.id = $receipts_table.tax_id
			LEFT JOIN (
				SELECT $taxes_table.* FROM $taxes_table
			
			) AS tax_table2 ON tax_table2.id = $receipts_table.tax_id2
			WHERE $receipts_table.deleted = 0 
			AND $receipts_table.id=$receipt_id
		";

		
		
	//arr( $receipt_sql );	
        $receipt = $this->db->query($receipt_sql)->row();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$receipt->client_id";
        $client = $this->db->query($client_sql)->row();


        $result = new stdClass();
        $result->receipt_subtotal = $item->receipt_subtotal;
        $result->tax_percentage = $receipt->tax_percentage;
        $result->tax_percentage2 = $receipt->tax_percentage2;
        $result->tax_name = $receipt->tax_name;
        $result->tax_name2 = $receipt->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $receipt_subtotal = $result->receipt_subtotal;
        $receipt_subtotal_for_taxes = $receipt_subtotal;
        if ($receipt->discount_type == "before_tax") {
            $receipt_subtotal_for_taxes = $receipt_subtotal - ($receipt->discount_amount_type == "percentage" ? ($receipt_subtotal * ($receipt->discount_amount / 100)) : $receipt->discount_amount);
        }

        if ($receipt->tax_percentage) {
            $result->tax = $receipt_subtotal_for_taxes * ($receipt->tax_percentage / 100);
        }
        if ($receipt->tax_percentage2) {
            $result->tax2 = $receipt_subtotal_for_taxes * ($receipt->tax_percentage2 / 100);
        }
        $receipt_total = $item->receipt_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($receipt->discount_type == "after_tax") {
            $receipt_subtotal = $receipt_total;
        }

        $result->discount_total = $receipt->discount_amount_type == "percentage" ? ($receipt_subtotal * ($receipt->discount_amount / 100)) : $receipt->discount_amount;

        $result->discount_type = $receipt->discount_type;

        $result->receipt_total = $receipt_total - number_format($result->discount_total, 2, ".", "");

        $result->currency_symbol = ($client && $client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = ($client && $client->currency) ? $client->currency : get_setting( "default_currency" );
        $result->payment = $receipt->payment;
        $result->notPaid = $result->receipt_total - $receipt->payment;
        $result->notPaid_n = $result->receipt_total - $receipt->payment;
		$result->showOnPaymen = 1;
        $result->paymentStatus = 'คงเหลือชำระ';
		if( $result->notPaid < 0 ) {
			$result->notPaid *= -1;
			$result->notPaid = '<span style="color: red;">'. to_currency( $result->notPaid, ($client && $client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol")) .'</span>';
			$result->paymentStatus = '<span style="color: red;">ชำระเกิน</span>';
			$result->showOnPaymen = 0;
		}
		else if( $result->notPaid == 0 ) {
			/*
			$result->notPaid *= -1;
			$result->notPaid = '<span style="color: red;">'. to_currency( $result->notPaid, $client->currency_symbol ) .'</span>';
			$result->paymentStatus = '<span style="color: red;">ชำระเกิน</span>';
			
			*/
			
			$result->showOnPaymen = 0;
			
		}
		else {
			
			
			
			$result->notPaid = to_currency( $result->notPaid, ($client && $client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol"));
			
			
		}
        
        return $result;
    }


	//get receipt last id
    function get_receipt_last_id() {
        $Receipts_table = $this->db->dbprefix('Receipts');

        $sql = "SELECT MAX($Receipts_table.id) AS last_id FROM $Receipts_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of receipt
    function save_initial_number_of_receipt($value) {
        $Receipts_table = $this->db->dbprefix('Receipts');

        $sql = "ALTER TABLE $Receipts_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    function get_Receipts_dropdown_list() {
        $Receipts_table = $this->db->dbprefix('Receipts');

        if(true){
            $sql = "SELECT $Receipts_table.id FROM $Receipts_table
            WHERE $Receipts_table.deleted=0 
            receipt BY $Receipts_table.id DESC";
        }else{
            $sql = "SELECT $Receipts_table.id FROM $Receipts_table
                        WHERE $Receipts_table.deleted=0 
                        receipt BY $Receipts_table.id DESC";
        }
        // $sql = "SELECT $invoices_table.id FROM $invoices_table
        //                 WHERE $invoices_table.deleted=0 
        //                 receipt BY $invoices_table.id DESC";

        return $this->db->query($sql);
    }

}
