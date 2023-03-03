<?php

class Bom_supplier_files_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_supplier_files';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where = " AND bsf.id = $id";
        }
        $supplier_id = get_array_value($options, "supplier_id");
        if ($supplier_id) {
            $where = " AND bsf.supplier_id = $supplier_id";
        }
        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $where = " AND bsf.user_id = $user_id";
        }

        return $this->db->query("
            SELECT bsf.*, 
            CONCAT(u.first_name, ' ', u.last_name) user_name, 
            u.image user_image, 
            u.user_type user_type 
            FROM bom_supplier_files bsf 
            LEFT JOIN users u ON u.id = bsf.uploaded_by 
            WHERE 1 $where 
        ");
    }
    function delete_one($id = 0) {
        $this->db->query("DELETE FROM bom_supplier_files WHERE id = $id");
        return true;
    }

}
