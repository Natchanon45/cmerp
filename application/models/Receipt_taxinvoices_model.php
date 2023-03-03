<?php

class Receipt_taxinvoices_model extends Crud_model {

    private $table = null;

    function __construct()
    {
        $this->table = 'receipt_taxinvoices';
        parent::__construct($this->table);
    }


    function get_receipt_taxinvoice_total_summary($receipt_taxinvoice_id = 0, $new_data = array())
    {
        $receipt_taxinvoice_items_table = $this->db->dbprefix('receipt_taxinvoice_items');
        $receipt_taxinvoice_payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $item_sql = "SELECT SUM($receipt_taxinvoice_items_table.total) AS receipt_taxinvoice_subtotal
        FROM $receipt_taxinvoice_items_table
        LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id= $receipt_taxinvoice_items_table.receipt_taxinvoice_id    
        WHERE $receipt_taxinvoice_items_table.deleted=0 AND $receipt_taxinvoice_items_table.receipt_taxinvoice_id=$receipt_taxinvoice_id AND $receipt_taxinvoices_table.deleted=0";
        $item = $this->db->query($item_sql)->row();

        $payment_sql = "SELECT SUM($receipt_taxinvoice_payments_table.amount) AS total_paid
        FROM $receipt_taxinvoice_payments_table
        WHERE $receipt_taxinvoice_payments_table.deleted=0 AND $receipt_taxinvoice_payments_table.receipt_taxinvoice_id=$receipt_taxinvoice_id";
        $payment = $this->db->query($payment_sql)->row();

        $receipt_taxinvoice_sql = "SELECT $receipt_taxinvoices_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2, 
            tax_table3.percentage AS tax_percentage3, tax_table3.title AS tax_name3,
            estimates.credit as credit, estimates.total_estimate as total_es, estimates.sub_total_estimate as sub_total_es,estimates.pay_sps as pay_es_sps,
            estimates.deposit as deposit
        FROM $receipt_taxinvoices_table
        LEFT JOIN estimates ON $receipt_taxinvoices_table.es_id = estimates.id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $receipt_taxinvoices_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_taxinvoices_table.tax_id2
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table3 ON tax_table3.id = $receipt_taxinvoices_table.tax_id3
        WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.id=$receipt_taxinvoice_id";
        $receipt_taxinvoice = $this->db->query($receipt_taxinvoice_sql)->row();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$receipt_taxinvoice->client_id";
        $client = $this->db->query($client_sql)->row();


        $result = new stdClass();

        $result->tax_percentage = $receipt_taxinvoice->tax_percentage;
        $result->tax_percentage2 = $receipt_taxinvoice->tax_percentage2;
        $result->tax_percentage3 = $receipt_taxinvoice->tax_percentage3;
        $result->tax_name = $receipt_taxinvoice->tax_name;
        $result->tax_name2 = $receipt_taxinvoice->tax_name2;
        $result->tax_name3 = $receipt_taxinvoice->tax_name3;
        $result->tax_id = $receipt_taxinvoice->tax_id;
        $result->tax_id2 = $receipt_taxinvoice->tax_id2;
        $result->tax_id3 = $receipt_taxinvoice->tax_id3;

        $result->credit = $receipt_taxinvoice->credit;
        $result->total_es = $receipt_taxinvoice->total_es;
        $result->sub_total_es = $receipt_taxinvoice->sub_total_es;
        $result->pay_es_sps = $receipt_taxinvoice->pay_es_sps;

        $result->pay_spilter = $receipt_taxinvoice->pay_spilter;
        $result->pay_time = $receipt_taxinvoice->pay_time;
        $result->deposit = $receipt_taxinvoice->deposit;
        $result->include_deposit = $receipt_taxinvoice->include_deposit;


        $result->receipt_taxinvoice_subtotal = $item->receipt_taxinvoice_subtotal;


        $result->tax = 0;
        $result->tax2 = 0;
        $result->tax3 = 0;

        $receipt_taxinvoice_subtotal = $result->receipt_taxinvoice_subtotal;

        $receipt_taxinvoice_subtotal_for_taxes = $receipt_taxinvoice_subtotal;
        if ($receipt_taxinvoice->discount_type == "before_tax") {
            $receipt_taxinvoice_subtotal_for_taxes = $receipt_taxinvoice_subtotal - ($receipt_taxinvoice->discount_amount_type == "percentage" ? (($result->receipt_taxinvoice_subtotal) * ($receipt_taxinvoice->discount_amount / 100)) : $receipt_taxinvoice->discount_amount);
        }

        if ($receipt_taxinvoice->tax_percentage) {
            $result->tax = $receipt_taxinvoice_subtotal_for_taxes * ($receipt_taxinvoice->tax_percentage / 100);
        }
        if ($receipt_taxinvoice->tax_percentage2) {
            $result->tax2 = $receipt_taxinvoice_subtotal_for_taxes * ($receipt_taxinvoice->tax_percentage2 / 100);
        }
        if ($receipt_taxinvoice->tax_percentage3) {
            $result->tax3 = $receipt_taxinvoice_subtotal_for_taxes * ($receipt_taxinvoice->tax_percentage3 / 100);
        }
        if ($receipt_taxinvoice->tax_percentage == 7) {
            $result->receipt_taxinvoice_total = $receipt_taxinvoice_subtotal + $result->tax - $result->tax2;
        } else {
            $result->receipt_taxinvoice_total = $receipt_taxinvoice_subtotal - $result->tax + $result->tax2;
        }



        // $result->receipt_taxinvoice_total = ($item->receipt_taxinvoice_subtotal + $result->tax + $result->tax2) - $result->tax3;

        $result->total_paid = $payment->total_paid;

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");

        //get discount total
        $result->discount_total = 0;
        if ($receipt_taxinvoice->discount_type == "after_tax") {
            $receipt_taxinvoice_subtotal = $result->receipt_taxinvoice_total;
        }

        $result->discount_total = $receipt_taxinvoice->discount_amount_type == "percentage" ? ($receipt_taxinvoice_subtotal * ($receipt_taxinvoice->discount_amount / 100)) : $receipt_taxinvoice->discount_amount;

        $result->discount_type = $receipt_taxinvoice->discount_type;

        $result->balance_due = number_format($result->receipt_taxinvoice_total, 2, ".", "") - number_format($payment->total_paid, 2, ".", "") - number_format($result->discount_total, 2, ".", "");

        return $result;
    }

    function receipt_taxinvoice_statistics($options = array())
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $receipt_taxinvoice_payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoice_items_table = $this->db->dbprefix('receipt_taxinvoice_items');
        $taxes_table = $this->db->dbprefix('taxes');
        $clients_table = $this->db->dbprefix('clients');

        $info = new stdClass();
        $year = get_my_local_time("Y");

        $where = "";
        $payments_where = "";
        $receipt_taxinvoices_where = "";

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $receipt_taxinvoices_table.client_id=$client_id";
        } else {
            $receipt_taxinvoices_where = $this->_get_clients_of_currency_query(get_array_value($options, "currency_symbol"), $receipt_taxinvoices_table, $clients_table);

            $payments_where = " AND $receipt_taxinvoice_payments_table.receipt_taxinvoice_id IN(SELECT $receipt_taxinvoices_table.id FROM $receipt_taxinvoices_table WHERE $receipt_taxinvoices_table.deleted=0 $receipt_taxinvoices_where)";
        }

        $payments = "SELECT SUM($receipt_taxinvoice_payments_table.amount) AS total, MONTH($receipt_taxinvoice_payments_table.payment_date) AS month
            FROM $receipt_taxinvoice_payments_table
            LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id=$receipt_taxinvoice_payments_table.receipt_taxinvoice_id    
            WHERE $receipt_taxinvoice_payments_table.deleted=0 AND YEAR($receipt_taxinvoice_payments_table.payment_date)=$year AND $receipt_taxinvoices_table.deleted=0 $where $payments_where
            GROUP BY MONTH($receipt_taxinvoice_payments_table.payment_date)";

        $receipt_taxinvoice_value_calculation_query = $this->_get_receipt_taxinvoice_value_calculation_query($receipt_taxinvoices_table);

        $receipt_taxinvoices = "SELECT SUM(total) AS total, MONTH(due_date) AS month FROM (SELECT $receipt_taxinvoice_value_calculation_query AS total ,$receipt_taxinvoices_table.due_date
            FROM $receipt_taxinvoices_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $receipt_taxinvoices_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_taxinvoices_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $receipt_taxinvoices_table.tax_id3
            LEFT JOIN (SELECT receipt_taxinvoice_id, SUM(total) AS receipt_taxinvoice_value FROM $receipt_taxinvoice_items_table WHERE deleted=0 GROUP BY receipt_taxinvoice_id) AS items_table ON items_table.receipt_taxinvoice_id = $receipt_taxinvoices_table.id 
            WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.status='not_paid' $where AND YEAR($receipt_taxinvoices_table.due_date)=$year $receipt_taxinvoices_where) as details_table
            GROUP BY  MONTH(due_date)";

        $info->payments = $this->db->query($payments)->result();
        $info->receipt_taxinvoices = $this->db->query($receipt_taxinvoices)->result();
        $info->currencies = $this->get_used_currencies_of_client()->result();

        return $info;
    }

    function get_used_currencies_of_client()
    {
        $clients_table = $this->db->dbprefix('clients');
        $default_currency = get_setting("default_currency");

        $sql = "SELECT $clients_table.currency
            FROM $clients_table
            WHERE $clients_table.deleted=0 AND $clients_table.currency!='' AND $clients_table.currency!='$default_currency'
            GROUP BY $clients_table.currency";

        return $this->db->query($sql);
    }

    function get_receipt_taxinvoices_total_and_paymnts()
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $receipt_taxinvoice_payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoice_items_table = $this->db->dbprefix('receipt_taxinvoice_items');
        $taxes_table = $this->db->dbprefix('taxes');
        $info = new stdClass();


        $payments = "SELECT SUM($receipt_taxinvoice_payments_table.amount) AS total
            FROM $receipt_taxinvoice_payments_table
            LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id=$receipt_taxinvoice_payments_table.receipt_taxinvoice_id    
            WHERE $receipt_taxinvoice_payments_table.deleted=0 AND $receipt_taxinvoices_table.deleted=0";
        $info->payments = $this->db->query($payments)->result();

        $receipt_taxinvoice_value_calculation_query = $this->_get_receipt_taxinvoice_value_calculation_query($receipt_taxinvoices_table);

        $receipt_taxinvoices = "SELECT SUM(total) AS total FROM (SELECT $receipt_taxinvoice_value_calculation_query AS total
            FROM $receipt_taxinvoices_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $receipt_taxinvoices_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_taxinvoices_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $receipt_taxinvoices_table.tax_id3
            LEFT JOIN (SELECT receipt_taxinvoice_id, SUM(total) AS receipt_taxinvoice_value FROM $receipt_taxinvoice_items_table WHERE deleted=0 GROUP BY receipt_taxinvoice_id) AS items_table ON items_table.receipt_taxinvoice_id = $receipt_taxinvoices_table.id 
            WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.status='not_paid') as details_table";

        $draft = "SELECT SUM(total) AS total FROM (SELECT $receipt_taxinvoice_value_calculation_query AS total
            FROM $receipt_taxinvoices_table
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $receipt_taxinvoices_table.tax_id
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_taxinvoices_table.tax_id2
            LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table3 ON tax_table3.id = $receipt_taxinvoices_table.tax_id3
            LEFT JOIN (SELECT receipt_taxinvoice_id, SUM(total) AS receipt_taxinvoice_value FROM $receipt_taxinvoice_items_table WHERE deleted=0 GROUP BY receipt_taxinvoice_id) AS items_table ON items_table.receipt_taxinvoice_id = $receipt_taxinvoices_table.id 
            WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.status='draft') as details_table";

        $payments_total = $this->db->query($payments)->row()->total;
        $receipt_taxinvoices_total = $this->db->query($receipt_taxinvoices)->row()->total;
        $draft_total = $this->db->query($draft)->row()->total;

        $info->payments_total = $payments_total;
        $info->receipt_taxinvoices_total = (($receipt_taxinvoices_total > $payments_total) && ($receipt_taxinvoices_total - $payments_total) < 0.05) ? $payments_total : $receipt_taxinvoices_total;
        $info->due = $info->receipt_taxinvoices_total - $info->payments_total;
        $info->draft_total = $draft_total;
        return $info;
    }

    //update receipt_taxinvoice status
    function update_receipt_taxinvoice_status($receipt_taxinvoice_id = 0, $status = "not_paid")
    {
        $status_data = array("status" => $status);
        return $this->save($status_data, $receipt_taxinvoice_id);
    }

    //get the recurring receipt_taxinvoices which are ready to renew as on a given date
    function get_renewable_receipt_taxinvoices($date)
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "SELECT * FROM $receipt_taxinvoices_table
                        WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.recurring=1
                        AND $receipt_taxinvoices_table.next_recurring_date IS NOT NULL AND $receipt_taxinvoices_table.next_recurring_date<='$date'
                        AND ($receipt_taxinvoices_table.no_of_cycles < 1 OR ($receipt_taxinvoices_table.no_of_cycles_completed < $receipt_taxinvoices_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    //get receipt_taxinvoices dropdown list
    function get_receipt_taxinvoices_dropdown_list()
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        if (true) {
            $sql = "SELECT $receipt_taxinvoices_table.id FROM $receipt_taxinvoices_table
            WHERE $receipt_taxinvoices_table.deleted=0 
            ORDER BY $receipt_taxinvoices_table.id DESC";
        } else {
            $sql = "SELECT $receipt_taxinvoices_table.id FROM $receipt_taxinvoices_table
                        WHERE $receipt_taxinvoices_table.deleted=0 
                        ORDER BY $receipt_taxinvoices_table.id DESC";
        }
        // $sql = "SELECT $receipt_taxinvoices_table.id FROM $receipt_taxinvoices_table
        //                 WHERE $receipt_taxinvoices_table.deleted=0 
        //                 ORDER BY $receipt_taxinvoices_table.id DESC";

        return $this->db->query($sql);
    }

    //get label suggestions
    function get_label_suggestions()
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $receipt_taxinvoices_table
        WHERE $receipt_taxinvoices_table.deleted=0";
        return $this->db->query($sql)->row()->label_groups;
    }

    //get receipt_taxinvoice last id
    function get_last_receipt_taxinvoice_id()
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "SELECT MAX($receipt_taxinvoices_table.id) AS last_id FROM $receipt_taxinvoices_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of receipt_taxinvoice
    function save_initial_number_of_receipt_taxinvoice($value)
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "ALTER TABLE $receipt_taxinvoices_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    //get draft receipt_taxinvoices
    function count_draft_receipt_taxinvoices()
    {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $sql = "SELECT COUNT($receipt_taxinvoices_table.id) AS total
        FROM $receipt_taxinvoices_table 
        WHERE $receipt_taxinvoices_table.deleted=0 AND $receipt_taxinvoices_table.status='draft'";
        return $this->db->query($sql)->row()->total;
    }

    function get_details($options = array(), $getRolePermission = array())
    {

        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $clients_table = $this->db->dbprefix('clients');
        $projects_table = $this->db->dbprefix('projects');
        $taxes_table = $this->db->dbprefix('taxes');
        $receipt_taxinvoice_payments_table = $this->db->dbprefix('receipt_taxinvoice_payments');
        $receipt_taxinvoice_items_table = $this->db->dbprefix('receipt_taxinvoice_items');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $receipt_taxinvoices_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $receipt_taxinvoices_table.client_id=$client_id";
        }

        $exclude_draft = get_array_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $receipt_taxinvoices_table.status!='draft' ";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $receipt_taxinvoices_table.project_id=$project_id";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($receipt_taxinvoices_table.due_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $next_recurring_start_date = get_array_value($options, "next_recurring_start_date");
        $next_recurring_end_date = get_array_value($options, "next_recurring_end_date");
        if ($next_recurring_start_date && $next_recurring_start_date) {
            $where .= " AND ($receipt_taxinvoices_table.next_recurring_date BETWEEN '$next_recurring_start_date' AND '$next_recurring_end_date') ";
        } else if ($next_recurring_start_date) {
            $where .= " AND $receipt_taxinvoices_table.next_recurring_date >= '$next_recurring_start_date' ";
        } else if ($next_recurring_end_date) {
            $where .= " AND $receipt_taxinvoices_table.next_recurring_date <= '$next_recurring_end_date' ";
        }

        $recurring_receipt_taxinvoice_id = get_array_value($options, "recurring_receipt_taxinvoice_id");
        if ($recurring_receipt_taxinvoice_id) {
            $where .= " AND $receipt_taxinvoices_table.recurring_receipt_taxinvoice_id=$recurring_receipt_taxinvoice_id";
        }

        $now = get_my_local_time("Y-m-d");
        //  $options['status'] = "draft";
        $status = get_array_value($options, "status");


        $receipt_taxinvoice_value_calculation_query = $this->_get_receipt_taxinvoice_value_calculation_query($receipt_taxinvoices_table);


        $receipt_taxinvoice_value_calculation = "TRUNCATE($receipt_taxinvoice_value_calculation_query,2)";

        if ($status === "draft") {
            $where .= " AND $receipt_taxinvoices_table.status='draft' AND IFNULL(payments_table.payment_received,0)<=0";
        } else if ($status === "not_paid") {
            $where .= " AND $receipt_taxinvoices_table.status !='draft' AND $receipt_taxinvoices_table.status!='cancelled' AND IFNULL(payments_table.payment_received,0)<=0";
        } else if ($status === "partially_paid") {
            $where .= " AND IFNULL(payments_table.payment_received,0)>0 AND IFNULL(payments_table.payment_received,0)<$receipt_taxinvoice_value_calculation";
        } else if ($status === "fully_paid") {
            $where .= " AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)>=$receipt_taxinvoice_value_calculation";
        } else if ($status === "overdue") {
            $where .= " AND $receipt_taxinvoices_table.status !='draft' AND $receipt_taxinvoices_table.status!='cancelled' AND $receipt_taxinvoices_table.due_date<'$now' AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)<$receipt_taxinvoice_value_calculation";
        } else if ($status === "cancelled") {
            $where .= " AND $receipt_taxinvoices_table.status='cancelled' ";
        }


        $recurring = get_array_value($options, "recurring");
        if ($recurring) {
            $where .= " AND $receipt_taxinvoices_table.recurring=1";
        }

        $currency = get_array_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $receipt_taxinvoices_table, $clients_table);
        }

        $exclude_due_reminder_date = get_array_value($options, "exclude_due_reminder_date");
        if ($exclude_due_reminder_date) {
            $where .= " AND ($receipt_taxinvoices_table.due_reminder_date !='$exclude_due_reminder_date') ";
        }

        $exclude_recurring_reminder_date = get_array_value($options, "exclude_recurring_reminder_date");
        if ($exclude_recurring_reminder_date) {
            $where .= " AND ($receipt_taxinvoices_table.recurring_reminder_date !='$exclude_recurring_reminder_date') ";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("receipt_taxinvoices", $custom_fields, $receipt_taxinvoices_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");




        $sql = "
			SELECT
                $receipt_taxinvoices_table.es_id, 
				$receipt_taxinvoices_table.pay_type, 
				$receipt_taxinvoices_table.pay_spilter, 
				$receipt_taxinvoices_table.id, 
                $receipt_taxinvoices_table.doc_no,
				$receipt_taxinvoices_table.created_by, 
				$receipt_taxinvoices_table.client_id, $receipt_taxinvoices_table.project_id, $receipt_taxinvoices_table.bill_date, $receipt_taxinvoices_table.due_date, $receipt_taxinvoices_table.note, $receipt_taxinvoices_table.labels, $receipt_taxinvoices_table.last_email_sent_date, 
				IF( pt.id IS NULL, $receipt_taxinvoices_table.status, 'อนุมัติ' ) status, 
				
				$receipt_taxinvoices_table.tax_id, $receipt_taxinvoices_table.tax_id2, $receipt_taxinvoices_table.tax_id3, $receipt_taxinvoices_table.recurring, $receipt_taxinvoices_table.recurring_receipt_taxinvoice_id, $receipt_taxinvoices_table.repeat_every, $receipt_taxinvoices_table.repeat_type, $receipt_taxinvoices_table.no_of_cycles, $receipt_taxinvoices_table.next_recurring_date, $receipt_taxinvoices_table.no_of_cycles_completed, $receipt_taxinvoices_table.due_reminder_date, $receipt_taxinvoices_table.recurring_reminder_date, $receipt_taxinvoices_table.discount_amount, $receipt_taxinvoices_table.discount_amount_type, $receipt_taxinvoices_table.discount_type, $receipt_taxinvoices_table.cancelled_at, $receipt_taxinvoices_table.cancelled_by, $receipt_taxinvoices_table.files, $receipt_taxinvoices_table.deleted,			
				$clients_table.currency, 
				$clients_table.currency_symbol, 
				$clients_table.company_name, 
				$projects_table.title AS project_title,
				$receipt_taxinvoices_table.pay_spilter AS receipt_taxinvoice_value, 
				
				IFNULL( payments_table.payment_received,0 ) AS payment_received, 
				tax_table.percentage AS tax_percentage, 
				tax_table2.percentage AS tax_percentage2, 
				tax_table3.percentage AS tax_percentage3, 
				CONCAT( $users_table.first_name, ' ',$users_table.last_name ) AS cancelled_by_user, $select_labels_data_query $select_custom_fieds
                ,invoices.doc_no as es_doc_no,
                $receipt_taxinvoices_table.include_deposit as include_deposit
            FROM $receipt_taxinvoices_table 
            LEFT JOIN invoices ON invoices.id = $receipt_taxinvoices_table.es_id
			LEFT JOIN prove_table pt ON $receipt_taxinvoices_table.id = pt.doc_id AND pt.tbName = '" . $receipt_taxinvoices_table . "'
			LEFT JOIN $clients_table ON $clients_table.id= $receipt_taxinvoices_table.client_id
			LEFT JOIN $projects_table ON $projects_table.id= $receipt_taxinvoices_table.project_id
			LEFT JOIN $users_table ON $users_table.id= $receipt_taxinvoices_table.cancelled_by
			LEFT JOIN ( SELECT $taxes_table.* FROM $taxes_table ) AS tax_table ON tax_table.id = $receipt_taxinvoices_table.tax_id
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $receipt_taxinvoices_table.tax_id2
			LEFT JOIN ( 
				SELECT 
					$taxes_table.* 
				FROM $taxes_table 
			) AS tax_table3 ON tax_table3.id = $receipt_taxinvoices_table.tax_id3
			LEFT JOIN (
				SELECT 
					receipt_taxinvoice_id, 
					SUM(amount) AS payment_received 
				FROM $receipt_taxinvoice_payments_table 
				WHERE deleted = 0 GROUP BY receipt_taxinvoice_id ) AS payments_table ON payments_table.receipt_taxinvoice_id = $receipt_taxinvoices_table.id 
			LEFT JOIN ( 
				SELECT 
					receipt_taxinvoice_id, 
					SUM( total ) AS receipt_taxinvoice_value 
				FROM $receipt_taxinvoice_items_table 
				WHERE deleted = 0 
				GROUP BY 
					receipt_taxinvoice_id 
			) AS items_table ON items_table.receipt_taxinvoice_id = $receipt_taxinvoices_table.id 
			$join_custom_fieds
			[WHERE]
		";

        //arr($getRolePermission);

        //exit;


        if (!isset($getRolePermission['filters'])) {

            $getRolePermission['filters'] = array();
        }
        if ($where) {
            $filters['WHERE'][] = " 1 " . $where;
        }
        //arr( $options );
        if (!empty($options['id'])) {
            $filters['WHERE'][] = "" . $receipt_taxinvoices_table . ".id = " . $options['id'] . "";
            $sql = gencond_($sql, $filters);
        } else {
            $filters = $this->getRolePermission['filters'];
            if ($where) {
                $filters['WHERE'][] = " 1 " . $where;
            }
            $sql = gencond_($sql, $filters);
        }

        // arr( $sql ); exit;
        return $this->db->query($sql);
    }

    //get order last id
    function get_rt_last_id() {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "SELECT MAX($receipt_taxinvoices_table.id) AS last_id FROM $receipt_taxinvoices_table";

        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of order
    function save_initial_number_of_order($value) {
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "ALTER TABLE $receipt_taxinvoices_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    function save_initial_number_of_rt($value) {
        $rt_table = $this->db->dbprefix('receipt_taxinvoices');

        $sql = "ALTER TABLE $rt_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }
}
