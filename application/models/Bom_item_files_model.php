<?php

class Bom_item_files_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_item_files';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where = " AND bmf.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where = " AND bmf.material_id = $item_id";
        }
        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $where = " AND bmf.user_id = $user_id";
        }

        return $this->db->query("
            SELECT bmf.*, 
            CONCAT(u.first_name, ' ', u.last_name) user_name, 
            u.image user_image, 
            u.user_type user_type 
            FROM bom_item_files bmf 
            LEFT JOIN users u ON u.id = bmf.uploaded_by 
            WHERE 1 $where 
        ");
    }
    function delete_one($id = 0) {
        $this->db->query("DELETE FROM bom_item_files WHERE id = $id");
        return true;
    }

}
