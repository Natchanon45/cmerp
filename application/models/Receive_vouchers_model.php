<?php

class Receive_vouchers_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'receive_vouchers';
        parent::__construct($this->table);
		
    }


	function get_invoice_total_summary($invoice_id = 0) {
        $payment_voucher_items_table = $this->db->dbprefix('receive_items');
        $payment_voucher_payments_table = $this->db->dbprefix('receive_voucher_payments');
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $item_sql = "        
        SELECT SUM(amount) AS invoice_subtotal FROM receive_voucher_payments WHERE receive_vouchers_id = $invoice_id  GROUP BY receive_vouchers_id
";

        // $item_sql = "SELECT SUM(amount) AS invoice_subtotal
        // FROM $payment_voucher_items_table  
        // WHERE payment_voucher_id = $invoice_id AND deleted=0";


        $item = $this->db->query($item_sql)->row();

        $payment_sql = "SELECT SUM($payment_voucher_payments_table.amount) AS total_paid
        FROM $payment_voucher_payments_table
        WHERE $payment_voucher_payments_table.deleted=0 AND $payment_voucher_payments_table.receive_vouchers_id=$invoice_id";
        $payment = $this->db->query($payment_sql)->row();

        $invoice_sql = "SELECT $payment_vouchers_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2, 
            tax_table3.percentage AS tax_percentage3, tax_table3.title AS tax_name3
        FROM $payment_vouchers_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $payment_vouchers_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $payment_vouchers_table.tax_id2
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table3 ON tax_table3.id = $payment_vouchers_table.tax_id3
        WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.id=$invoice_id";
        $invoice = $this->db->query($invoice_sql)->row();
        // print_r($invoice_sql);

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$invoice->client_id";
        $client = $this->db->query($client_sql)->row();


        $result = new stdClass();
        $result->invoice_subtotal = $item->invoice_subtotal;
        $result->tax_percentage = $invoice->tax_percentage;
        $result->tax_percentage2 = $invoice->tax_percentage2;
        $result->tax_percentage3 = $invoice->tax_percentage3;
        $result->tax_name = $invoice->tax_name;
        $result->tax_name2 = $invoice->tax_name2;
        $result->tax_name3 = $invoice->tax_name3;
        $result->tax = 0;
        $result->tax2 = 0;
        $result->tax3 = 0;

        $invoice_subtotal = $result->invoice_subtotal;
        $invoice_subtotal_for_taxes = $invoice_subtotal;
        if ($invoice->discount_type == "before_tax") {
            $invoice_subtotal_for_taxes = $invoice_subtotal - ($invoice->discount_amount_type == "percentage" ? ($result->invoice_subtotal * ($invoice->discount_amount / 100)) : $invoice->discount_amount);
        }

        if ($invoice->tax_percentage) {
            $result->tax = $invoice_subtotal_for_taxes * ($invoice->tax_percentage / 100);
        }
        if ($invoice->tax_percentage2) {
            $result->tax2 = $invoice_subtotal_for_taxes * ($invoice->tax_percentage2 / 100);
        }
        if ($invoice->tax_percentage3) {
            $result->tax3 = $invoice_subtotal_for_taxes * ($invoice->tax_percentage3 / 100);
        }
        $result->invoice_total = ($item->invoice_subtotal + $result->tax + $result->tax2) - $result->tax3;

        $result->total_paid = $payment->total_paid;

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");

        //get discount total
        $result->discount_total = 0;
        if ($invoice->discount_type == "after_tax") {
            $invoice_subtotal = $result->invoice_total;
        }

        $result->discount_total = $invoice->discount_amount_type == "percentage" ? ($invoice_subtotal * ($invoice->discount_amount / 100)) : $invoice->discount_amount;

        $result->discount_type = $invoice->discount_type;

        $result->balance_due = number_format($result->invoice_total, 2, ".", "") - number_format($payment->total_paid, 2, ".", "") - number_format($result->discount_total, 2, ".", "");

        return $result;
    }

    function invoice_statistics($options = array()) {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $payment_voucher_payments_table = $this->db->dbprefix('receive_voucher_payments');
        $payment_voucher_items_table = $this->db->dbprefix('receive_voucher_items');
        $taxes_table = $this->db->dbprefix('taxes');
        $clients_table = $this->db->dbprefix('clients');

        $info = new stdClass();
        $year = get_my_local_time("Y");

        $where = "";
        $payments_where = "";
        $invoices_where = "";

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $payment_vouchers_table.client_id=$client_id";
        } else {
            $invoices_where = $this->_get_clients_of_currency_query(get_array_value($options, "currency_symbol"), $payment_vouchers_table, $clients_table);

            $payments_where = " AND $payment_voucher_payments_table.order_id IN(SELECT $payment_vouchers_table.id FROM $payment_vouchers_table WHERE $payment_vouchers_table.deleted=0 $invoices_where)";
        }

        $payments = "SELECT SUM($payment_voucher_payments_table.amount) AS total, MONTH($payment_voucher_payments_table.payment_date) AS month
            FROM $payment_voucher_payments_table
            LEFT JOIN $payment_vouchers_table ON $payment_vouchers_table.id=$payment_voucher_payments_table.order_id    
            WHERE $payment_voucher_payments_table.deleted=0 AND YEAR($payment_voucher_payments_table.payment_date)=$year AND $payment_vouchers_table.deleted=0 $where $payments_where
            GROUP BY MONTH($payment_voucher_payments_table.payment_date)";

        $invoice_value_calculation_query = $this->_get_invoice_value_calculation_query($payment_vouchers_table);

        $invoices = "SELECT SUM(total) AS total, MONTH(due_date) AS month FROM (SELECT $invoice_value_calculation_query AS total ,$payment_vouchers_table.due_date
            FROM $payment_vouchers_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $payment_vouchers_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $payment_vouchers_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $payment_vouchers_table.tax_id3
            LEFT JOIN (SELECT order_id, SUM(total) AS invoice_value FROM $payment_voucher_items_table WHERE deleted=0 GROUP BY order_id) AS items_table ON items_table.order_id = $payment_vouchers_table.id 
            WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.status='not_paid' $where AND YEAR($payment_vouchers_table.due_date)=$year $invoices_where) as details_table
            GROUP BY  MONTH(due_date)";

        $info->payments = $this->db->query($payments)->result();
        $info->invoices = $this->db->query($invoices)->result();
        $info->currencies = $this->get_used_currencies_of_client()->result();

        return $info;
    }

    function get_used_currencies_of_client() {
        $clients_table = $this->db->dbprefix('clients');
        $default_currency = get_setting("default_currency");

        $sql = "SELECT $clients_table.currency
            FROM $clients_table
            WHERE $clients_table.deleted=0 AND $clients_table.currency!='' AND $clients_table.currency!='$default_currency'
            GROUP BY $clients_table.currency";

        return $this->db->query($sql);
    }

    function get_invoices_total_and_paymnts() {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $payment_voucher_payments_table = $this->db->dbprefix('receive_voucher_payments');
        $payment_voucher_items_table = $this->db->dbprefix('invoice_items');
        $taxes_table = $this->db->dbprefix('taxes');
        $info = new stdClass();


        $payments = "SELECT SUM($payment_voucher_payments_table.amount) AS total
            FROM $payment_voucher_payments_table
            LEFT JOIN $payment_vouchers_table ON $payment_vouchers_table.id=$payment_voucher_payments_table.order_id    
            WHERE $payment_voucher_payments_table.deleted=0 AND $payment_vouchers_table.deleted=0";
        $info->payments = $this->db->query($payments)->result();

        $invoice_value_calculation_query = $this->_get_invoice_value_calculation_query($payment_vouchers_table);

        $invoices = "SELECT SUM(total) AS total FROM (SELECT $invoice_value_calculation_query AS total
            FROM $payment_vouchers_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $payment_vouchers_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $payment_vouchers_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $payment_vouchers_table.tax_id3
            LEFT JOIN (SELECT order_id, SUM(total) AS invoice_value FROM $payment_voucher_items_table WHERE deleted=0 GROUP BY order_id) AS items_table ON items_table.order_id = $payment_vouchers_table.id 
            WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.status='not_paid') as details_table";

        $draft = "SELECT SUM(total) AS total FROM (SELECT $invoice_value_calculation_query AS total
            FROM $payment_vouchers_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $payment_vouchers_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $payment_vouchers_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $payment_vouchers_table.tax_id3
            LEFT JOIN (SELECT order_id, SUM(total) AS invoice_value FROM $payment_voucher_items_table WHERE deleted=0 GROUP BY order_id) AS items_table ON items_table.order_id = $payment_vouchers_table.id 
            WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.status='draft') as details_table";

        $payments_total = $this->db->query($payments)->row()->total;
        $invoices_total = $this->db->query($invoices)->row()->total;
        $draft_total = $this->db->query($draft)->row()->total;

        $info->payments_total = $payments_total;
        $info->invoices_total = (($invoices_total > $payments_total) && ($invoices_total - $payments_total) < 0.05 ) ? $payments_total : $invoices_total;
        $info->due = $info->invoices_total - $info->payments_total;
        $info->draft_total = $draft_total;
        return $info;
    }

    //update invoice status
    function update_invoice_status($invoice_id = 0, $status = "not_paid") {
        $status_data = array("status" => $status);
        return $this->save($status_data, $invoice_id);
    }

    //get the recurring invoices which are ready to renew as on a given date
    function get_renewable_invoices($date) {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');

        $sql = "SELECT * FROM $payment_vouchers_table
                        WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.recurring=1
                        AND $payment_vouchers_table.next_recurring_date IS NOT NULL AND $payment_vouchers_table.next_recurring_date<='$date'
                        AND ($payment_vouchers_table.no_of_cycles < 1 OR ($payment_vouchers_table.no_of_cycles_completed < $payment_vouchers_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    //get invoices dropdown list
    function get_invoices_dropdown_list() {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');

        $sql = "SELECT $payment_vouchers_table.id FROM $payment_vouchers_table
                        WHERE $payment_vouchers_table.deleted=0 
                        ORDER BY $payment_vouchers_table.id DESC";

        return $this->db->query($sql);
    }

    //get label suggestions
    function get_label_suggestions() {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $payment_vouchers_table
        WHERE $payment_vouchers_table.deleted=0";
        return $this->db->query($sql)->row()->label_groups;
    }

    //get invoice last id
    function get_last_invoice_id() {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');

        $sql = "SELECT MAX($payment_vouchers_table.id) AS last_id FROM $payment_vouchers_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of invoice
    function save_initial_number_of_invoice($value) {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');

        $sql = "ALTER TABLE $payment_vouchers_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    //get draft invoices
    function count_draft_invoices() {
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $sql = "SELECT COUNT($payment_vouchers_table.id) AS total
        FROM $payment_vouchers_table 
        WHERE $payment_vouchers_table.deleted=0 AND $payment_vouchers_table.status='draft'";
        return $this->db->query($sql)->row()->total;
    }
	
    function get_details($options = array(), $getRolePermission = array() ) {
		
        $payment_vouchers_table = $this->db->dbprefix('receive_vouchers');
        $clients_table = $this->db->dbprefix('clients');
        $projects_table = $this->db->dbprefix('projects');
        $taxes_table = $this->db->dbprefix('taxes');
        $payment_voucher_payments_table = $this->db->dbprefix('receive_voucher_payments');
        $payment_voucher_items_table = $this->db->dbprefix('receive_voucher_items');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $payment_vouchers_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $payment_vouchers_table.client_id=$client_id";
        }

        $exclude_draft = get_array_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $payment_vouchers_table.status!='draft' ";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $payment_vouchers_table.project_id=$project_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($payment_vouchers_table.due_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $next_recurring_start_date = get_array_value($options, "next_recurring_start_date");
        $next_recurring_end_date = get_array_value($options, "next_recurring_end_date");
        if ($next_recurring_start_date && $next_recurring_start_date) {
            $where .= " AND ($payment_vouchers_table.next_recurring_date BETWEEN '$next_recurring_start_date' AND '$next_recurring_end_date') ";
        } else if ($next_recurring_start_date) {
            $where .= " AND $payment_vouchers_table.next_recurring_date >= '$next_recurring_start_date' ";
        } else if ($next_recurring_end_date) {
            $where .= " AND $payment_vouchers_table.next_recurring_date <= '$next_recurring_end_date' ";
        }

        $recurring_invoice_id = get_array_value($options, "recurring_invoice_id");
        if ($recurring_invoice_id) {
            $where .= " AND $payment_vouchers_table.recurring_invoice_id=$recurring_invoice_id";
        }

        $now = get_my_local_time("Y-m-d");
        //  $options['status'] = "draft";
        $status = get_array_value($options, "status");


        $invoice_value_calculation_query = $this->_get_invoice_value_calculation_query($payment_vouchers_table);


        $invoice_value_calculation = "TRUNCATE($invoice_value_calculation_query,2)";

        if ($status === "draft") {
            $where .= " AND $payment_vouchers_table.status='draft' AND IFNULL(payments_table.payment_received,0)<=0";
        } else if ($status === "not_paid") {
            $where .= " AND $payment_vouchers_table.status !='draft' AND $payment_vouchers_table.status!='cancelled' AND IFNULL(payments_table.payment_received,0)<=0";
        } else if ($status === "partially_paid") {
            $where .= " AND IFNULL(payments_table.payment_received,0)>0 AND IFNULL(payments_table.payment_received,0)<$invoice_value_calculation";
        } else if ($status === "fully_paid") {
            $where .= " AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)>=$invoice_value_calculation";
        } else if ($status === "overdue") {
            $where .= " AND $payment_vouchers_table.status !='draft' AND $payment_vouchers_table.status!='cancelled' AND $payment_vouchers_table.due_date<'$now' AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)<$invoice_value_calculation";
        } else if ($status === "cancelled") {
            $where .= " AND $payment_vouchers_table.status='cancelled' ";
        }


        $recurring = get_array_value($options, "recurring");
        if ($recurring) {
            $where .= " AND $payment_vouchers_table.recurring=1";
        }

        $currency = get_array_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $payment_vouchers_table, $clients_table);
        }

        $exclude_due_reminder_date = get_array_value($options, "exclude_due_reminder_date");
        if ($exclude_due_reminder_date) {
            $where .= " AND ($payment_vouchers_table.due_reminder_date !='$exclude_due_reminder_date') ";
        }

        $exclude_recurring_reminder_date = get_array_value($options, "exclude_recurring_reminder_date");
        if ($exclude_recurring_reminder_date) {
            $where .= " AND ($payment_vouchers_table.recurring_reminder_date !='$exclude_recurring_reminder_date') ";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("invoices", $custom_fields, $payment_vouchers_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");




        $sql = "
			SELECT 
				$payment_vouchers_table.*, 
				$clients_table.currency, 
				$clients_table.currency_symbol, 
				$clients_table.company_name, 
				$projects_table.title AS project_title,
				$invoice_value_calculation_query AS invoice_value, IFNULL(payments_table.payment_received,0) AS payment_received, 
				tax_table.percentage AS tax_percentage, 
				tax_table2.percentage AS tax_percentage2, 
				tax_table3.percentage AS tax_percentage3, 
				CONCAT($users_table.first_name, ' ',$users_table.last_name) AS cancelled_by_user, $select_labels_data_query $select_custom_fieds
			FROM $payment_vouchers_table 
			LEFT JOIN $clients_table ON $clients_table.id= $payment_vouchers_table.client_id
			LEFT JOIN $projects_table ON $projects_table.id= $payment_vouchers_table.project_id
			LEFT JOIN $users_table ON $users_table.id= $payment_vouchers_table.cancelled_by
			LEFT JOIN ( SELECT $taxes_table.* FROM $taxes_table ) AS tax_table ON tax_table.id = $payment_vouchers_table.tax_id
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $payment_vouchers_table.tax_id2
			LEFT JOIN ( 
				SELECT 
					$taxes_table.* 
				FROM $taxes_table 
			) AS tax_table3 ON tax_table3.id = $payment_vouchers_table.tax_id3
			LEFT JOIN (
				SELECT 
					order_id, 
					SUM(amount) AS payment_received 
				FROM $payment_voucher_payments_table 
				WHERE deleted = 0 GROUP BY order_id ) AS payments_table ON payments_table.order_id = $payment_vouchers_table.id 
			LEFT JOIN ( 
				SELECT 
					order_id, 
					SUM( total ) AS invoice_value 
				FROM $payment_voucher_items_table 
				WHERE deleted = 0 
				GROUP BY 
					order_id 
			) AS items_table ON items_table.order_id = $payment_vouchers_table.id 
			$join_custom_fieds
			[WHERE]
		";
		
		
		
		//arr($getRolePermission);
		
		//exit;
		
		//arr( $options );
		if( !empty( $options['id'] ) ) {
			$filters['WHERE'][] = "". $payment_vouchers_table .".id = ". $options['id'] ."";
			$sql = gencond_( $sql, $filters );	
		}
		else {
			$sql = gencond_( $sql, $getRolePermission['filters'] );	
		}
		
 		
		 
        return $this->db->query($sql);
    }

}
