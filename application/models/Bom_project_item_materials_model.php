<?php

class Bom_project_item_materials_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_project_item_materials';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bpim.id = $id";
        }
        $material_id = get_array_value($options, "material_id");
        if ($material_id) {
            $where .= " AND bpim.material_id = $material_id";
        }
        $restock_id = get_array_value($options, "restock_id");
        if ($restock_id) {
            $where .= " AND bsg.id = $restock_id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND bsg.created_by = $created_by";
        }

        return $this->db->query("
            SELECT bpim.*, 
            bm.name material_name, bm.unit material_unit, bm.production_name,
            p.id project_id, p.title project_title, 
            bsg.id group_id, bsg.name stock_name, 
            bs.price, bs.stock 
            FROM bom_project_item_materials bpim 
            INNER JOIN bom_materials bm ON bm.id = bpim.material_id 
            INNER JOIN bom_stocks bs ON bs.id = bpim.stock_id 
            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id 
            LEFT JOIN projects p ON p.id = bpi.project_id 
            WHERE 1 $where 
            GROUP BY bpim.id 
        ");
    }

    public function getCountStockUsedById($id)
    {
        $query = $this->db->get_where('bom_project_item_materials', array('stock_id' => $id));
        return $query->num_rows();
    }

}
