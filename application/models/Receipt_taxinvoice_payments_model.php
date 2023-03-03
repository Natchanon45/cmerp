<?php

class Receipt_taxinvoice_payments_model extends Crud_model
{

    private $table = null;

    function __construct()
    {
        $this->table = 'receipt_taxinvoice_payments';
        parent::__construct($this->table);
    }

    function get_details($options = array())
    {
        $receipt_taxinvoice_payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $payment_methods_table = $this->db->dbprefix('payment_methods');
        $clients_table = $this->db->dbprefix('clients');

        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $receipt_taxinvoice_payments_table.id=$id";
        }

        $receipt_taxinvoice_id = get_array_value($options, "receipt_taxinvoice_id");
        if ($receipt_taxinvoice_id) {
            $where .= " AND $receipt_taxinvoice_payments_table.receipt_taxinvoice_id=$receipt_taxinvoice_id";
        }

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $receipt_taxinvoices_table.client_id=$client_id";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $receipt_taxinvoices_table.project_id=$project_id";
        }

        $payment_method_id = get_array_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $receipt_taxinvoice_payments_table.payment_method_id=$payment_method_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($receipt_taxinvoice_payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $currency = get_array_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $receipt_taxinvoices_table, $clients_table);
        }

        $sql = "SELECT $receipt_taxinvoice_payments_table.*, $receipt_taxinvoices_table.client_id, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$receipt_taxinvoices_table.client_id limit 1) AS currency_symbol, $payment_methods_table.title AS payment_method_title
        FROM $receipt_taxinvoice_payments_table
        LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id=$receipt_taxinvoice_payments_table.receipt_taxinvoice_id
        LEFT JOIN $payment_methods_table ON $payment_methods_table.id = $receipt_taxinvoice_payments_table.payment_method_id
        WHERE $receipt_taxinvoice_payments_table.deleted=0 AND $receipt_taxinvoices_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_yearly_payments_chart($year, $currency = "", $project_id = 0)
    {
        $payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $clients_table = $this->db->dbprefix('clients');

        $where = "";
        if ($currency) {
            $where = $this->_get_clients_of_currency_query($currency, $receipt_taxinvoices_table, $clients_table);
        }

        if ($project_id) {
            $where .= " AND $payments_table.receipt_taxinvoice_id IN(SELECT $receipt_taxinvoices_table.id FROM $receipt_taxinvoices_table WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.project_id=$project_id)";
        }

        $sql = "SELECT SUM($payments_table.amount) AS total, MONTH($payments_table.payment_date) AS month
            FROM $payments_table
            LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id=$payments_table.receipt_taxinvoice_id
            [WHERE] 
				 
			AND YEAR($payments_table.payment_date)= $year AND $receipt_taxinvoices_table.deleted=0 $where
            GROUP BY MONTH($payments_table.payment_date)";


        $filters = array();

        if (isset($this->getRolePermission['filters'])) {
            $filters = $this->getRolePermission['filters'];
        }

        $filters['WHERE'][] = "$payments_table.deleted = 0";
        $sql = gencond_($sql, $filters);
        $sql = str_replace('income_vs_expenses', $payments_table, $sql);

        //arr( $sql );			
        return $this->db->query($sql)->result();
    }

    function get_used_projects($type)
    {
        $payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $projects_table = $this->db->dbprefix('projects');
        $expenses_table = $this->db->dbprefix('expenses');

        $payments_where = "SELECT $receipt_taxinvoices_table.project_id FROM $receipt_taxinvoices_table WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.project_id!=0 AND $receipt_taxinvoices_table.id IN(SELECT $payments_table.receipt_taxinvoice_id FROM $payments_table WHERE $payments_table.deleted=0 GROUP BY $payments_table.receipt_taxinvoice_id) GROUP BY $receipt_taxinvoices_table.project_id";
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
