<?php

class Bom_supplier_contacts_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_supplier_contacts';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bsc.id = $id";
        }

        $supplier_id = get_array_value($options, "supplier_id");
        if ($supplier_id) {
            $where .= " AND bsc.supplier_id = $supplier_id";
        }

        return $this->db->query("
            SELECT bsc.*, bs.company_name 
            FROM bom_supplier_contacts bsc 
            INNER JOIN bom_suppliers bs ON bs.id = bsc.supplier_id 
            WHERE 1 $where 
            GROUP BY bsc.id 
            ORDER BY bsc.is_primary DESC 
        ");
    }

    function clear_primary($supplier_id = 0) {
        $this->db->query("
            UPDATE bom_supplier_contacts 
            SET is_primary = 0 
            WHERE supplier_id = $supplier_id 
        ");
    }
    
    function delete_one($id) {
        $this->db->query("DELETE FROM bom_supplier_contacts WHERE id = $id");
        return true;
    }

}
