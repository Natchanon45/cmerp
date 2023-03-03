<?php

class Bom_material_pricings_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_material_pricings';
        parent::__construct($this->table);
    }
    

    function delete_one($id) {
        $this->db->query("DELETE FROM bom_material_pricings WHERE id = $id");
        return true;
    }

}
