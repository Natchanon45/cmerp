<?php

class Invoice_payments_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'invoice_payments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $invoice_payments_table = $this->db->dbprefix('invoice_payments');
        $invoices_table = $this->db->dbprefix('invoices');
        $payment_methods_table = $this->db->dbprefix('payment_methods');
        $clients_table = $this->db->dbprefix('clients');

        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $invoice_payments_table.id=$id";
        }

        $invoice_id = get_array_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $invoice_payments_table.invoice_id=$invoice_id";
        }

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $invoices_table.client_id=$client_id";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $invoices_table.project_id=$project_id";
        }

        $payment_method_id = get_array_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $invoice_payments_table.payment_method_id=$payment_method_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($invoice_payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $currency = get_array_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        $sql = "SELECT $invoice_payments_table.*, $invoices_table.client_id, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id limit 1) AS currency_symbol, $payment_methods_table.title AS payment_method_title
        FROM $invoice_payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$invoice_payments_table.invoice_id
        LEFT JOIN $payment_methods_table ON $payment_methods_table.id = $invoice_payments_table.payment_method_id
        WHERE $invoice_payments_table.deleted=0 AND $invoices_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_yearly_payments_chart($year, $currency = "", $project_id = 0) {
        $payments_table = $this->db->dbprefix('invoice_payments');
        $invoices_table = $this->db->dbprefix('invoices');
        $clients_table = $this->db->dbprefix('clients');

        $where = "";
        if ($currency) {
            $where = $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        if ($project_id) {
            $where .= " AND $payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id=$project_id)";
        }

        $sql = "SELECT SUM($payments_table.amount) AS total, MONTH($payments_table.payment_date) AS month 
            FROM $payments_table 
            LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id 

            [WHERE] 
				 
			AND YEAR($payments_table.payment_date) = $year AND $invoices_table.deleted=0 $where 
            GROUP BY MONTH($payments_table.payment_date)";

		$filters = array();
		
		// if( isset( $this->getRolePermission['filters'] ) ) {
		// 	$filters = $this->getRolePermission['filters'];
		// }
		 
		$filters['WHERE'][] = "$payments_table.deleted = 0";
        // var_dump($filters);

		$sql = gencond_( $sql, $filters );
		$sql = str_replace( 'income_vs_expenses', $payments_table, $sql  );
        
        // arr( $sql );
        return $this->db->query($sql)->result();
    }

    function get_used_projects($type) {
        $payments_table = $this->db->dbprefix('invoice_payments');
        $invoices_table = $this->db->dbprefix('invoices');
        $projects_table = $this->db->dbprefix('projects');
        $expenses_table = $this->db->dbprefix('expenses');

        $payments_where = "SELECT $invoices_table.project_id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id!=0 AND $invoices_table.id IN(SELECT $payments_table.invoice_id FROM $payments_table WHERE $payments_table.deleted=0 GROUP BY $payments_table.invoice_id) GROUP BY $invoices_table.project_id";
        $expenses_where = "SELECT $expenses_table.project_id FROM $expenses_table WHERE $expenses_table.deleted=0 AND $expenses_table.project_id!=0 GROUP BY $expenses_table.project_id";

        $where = "";
        if ($type == "all") {
            $where = " AND $projects_table.id IN($payments_where) OR $projects_table.id IN($expenses_where)";
        } else if ($type == "payments") {
            $where = " AND $projects_table.id IN($payments_where)";
        } else if ($type == "expenses") {
            $where = " AND $projects_table.id IN($expenses_where)";
        }

        $sql = "SELECT $projects_table.id, $projects_table.title 
            FROM $projects_table 
            WHERE $projects_table.deleted=0 $where
            GROUP BY $projects_table.id";

        return $this->db->query($sql);
    }

}
