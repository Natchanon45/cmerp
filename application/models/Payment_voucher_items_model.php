<?php

class Payment_voucher_items_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'payment_voucher_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $payment_voucher_items_table = $this->db->dbprefix('payment_voucher_items');
        $payment_vouchers_table = $this->db->dbprefix('payment_vouchers');
        $clients_table = $this->db->dbprefix('clients');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $payment_voucher_items_table.id=$id";
        }
        $invoice_id = get_array_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $payment_voucher_items_table.invoice_id=$invoice_id";
        }

        $sql = "SELECT $payment_voucher_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$payment_vouchers_table.client_id limit 1) AS currency_symbol
        FROM $payment_voucher_items_table
        LEFT JOIN $payment_vouchers_table ON $payment_vouchers_table.id=$payment_voucher_items_table.invoice_id
        WHERE $payment_voucher_items_table.deleted=0 $where
        ORDER BY $payment_voucher_items_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_item_suggestion($keyword = "") {
        $items_table = $this->db->dbprefix('items');

        $keyword = $this->db->escape_str($keyword);

        $sql = "SELECT $items_table.title
        FROM $items_table
        WHERE $items_table.deleted=0  AND $items_table.title LIKE '%$keyword%'
        LIMIT 10 
        ";
        return $this->db->query($sql)->result();
    }

    function get_item_info_suggestion($item_name = "") {

        $items_table = $this->db->dbprefix('items');

        $item_name = $this->db->escape_str($item_name);

        $sql = "SELECT $items_table.*
        FROM $items_table
        WHERE $items_table.deleted=0  AND $items_table.title LIKE '%$item_name%'
        ORDER BY id DESC LIMIT 1
        ";

        $result = $this->db->query($sql);

        if ($result->num_rows()) {
            return $result->row();
        }
    }

}
