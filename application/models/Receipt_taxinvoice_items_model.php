<?php

class Receipt_taxinvoice_items_model extends Crud_model
{

    private $table = null;

    function __construct()
    {
        $this->table = 'receipt_taxinvoice_items';
        parent::__construct($this->table);
    }

    function get_details($options = array())
    {
        $receipt_taxinvoice_items_table = $this->db->dbprefix('receipt_taxinvoice_items');
        $receipt_taxinvoices_table = $this->db->dbprefix('receipt_taxinvoices');
        $clients_table = $this->db->dbprefix('clients');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $receipt_taxinvoice_items_table.id=$id";
        }
        $receipt_taxinvoice_id = get_array_value($options, "receipt_taxinvoice_id");
        if ($receipt_taxinvoice_id) {
            $where .= " AND $receipt_taxinvoice_items_table.receipt_taxinvoice_id=$receipt_taxinvoice_id";
        }

        $sql = "SELECT $receipt_taxinvoice_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$receipt_taxinvoices_table.client_id limit 1) AS currency_symbol
        FROM $receipt_taxinvoice_items_table
        LEFT JOIN $receipt_taxinvoices_table ON $receipt_taxinvoices_table.id=$receipt_taxinvoice_items_table.receipt_taxinvoice_id
        WHERE $receipt_taxinvoice_items_table.deleted=0 $where
        ORDER BY $receipt_taxinvoice_items_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_item_suggestion($keyword = "")
    {
        $items_table = $this->db->dbprefix('items');

        $keyword = $this->db->escape_str($keyword);

        $sql = "SELECT $items_table.title
        FROM $items_table
        WHERE $items_table.deleted=0  AND $items_table.title LIKE '%$keyword%'
        LIMIT 10 
        ";
        return $this->db->query($sql)->result();
    }

    function get_item_info_suggestion($item_name = "")
    {

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
