<?php

class Receipt_items_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'Receipt_items';
        parent::__construct($this->table);
    }
    function get_pr_item($pr_id, $supplier_id, $material_id, $item_id) {
         $sql = "";
        if($material_id>0)
            $sql = "SELECT po_no from pr_items WHERE pr_id='$pr_id' AND supplier_id='{$supplier_id}' AND material_id='{$material_id}';";
        else if($item_id>0)
            $sql = "SELECT po_no from pr_items WHERE pr_id='$pr_id' AND supplier_id='{$supplier_id}' AND item_id='{$item_id}';";
        
        if(!$sql) return null;
        $q = $this->db->query($sql);
        return $q?$q->row():null;
    }
    function get_details($options = array()) {
        $Receipt_items_table = $this->db->dbprefix('receipt_items');
        $items_table = $this->db->dbprefix('items');
        $clients_table = $this->db->dbprefix('clients');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $Receipt_items_table.id=$id";
        }

        $created_by = get_array_value($options, "created_by");
        if (isset($options['created_by'])) {
            $where .= " AND $Receipt_items_table.created_by='$created_by'";
        }

        $receipt_id = get_array_value($options, "receipt_id");
        if (isset($options['receipt_id'])) {
            $where .= " AND $Receipt_items_table.receipt_id='$receipt_id'";
        }

        $processing = get_array_value($options, "processing");
        if ($processing && $created_by) {
            $where .= " AND $Receipt_items_table.receipt_id='0'";
        }

        $sql = "SELECT $Receipt_items_table.*, $items_table.files,
            (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=(SELECT $users_table.client_id FROM $users_table WHERE $users_table.id=$Receipt_items_table.created_by) limit 1) AS currency_symbol
        FROM $Receipt_items_table
        LEFT JOIN $items_table ON $items_table.id=$Receipt_items_table.item_id
        WHERE $Receipt_items_table.deleted=0 $where
        ORDER BY $Receipt_items_table.id ASC";
        //var_dump($sql);exit;
        return $this->db->query($sql);
    }

}
