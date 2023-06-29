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
            WHERE 1 AND bpim.used_status = 1 $where 
            GROUP BY bpim.id 
        ");
    }

    public function getCountStockUsedById($id)
    {
        $query = $this->db->get_where('bom_project_item_materials', array('stock_id' => $id));
        return $query->num_rows();
    }

    public function updateMaterialRequestIdById($id, $mr_id)
    {
        $this->db->where('id', $id);
        $this->db->update('bom_project_item_materials', array('mr_id' => $mr_id));
    }

    function dev2_getProjectItemIdByProjectId($project_id)
    {
        $this->db->select('id')->from('bom_project_items')->where('project_id', $project_id);
        $query = $this->db->get();

        return $query->result();
    }

    function dev2_updateUsedStatusById($id, $status_id)
    {
        $this->db->where('id', $id);
        $this->db->update('bom_project_item_materials', array('used_status' => $status_id));
    }

    function dev2_rejectMaterialRequestById($id)
    {
        $this->db->where('id', $id);
        $this->db->update('bom_project_item_materials', array('used_status' => 0, 'mr_id' => null));
    }

    function dev2_updateUsedStatusByProjectItemId($item_id, $status_id)
    {
        $this->db->where('project_item_id', $item_id);
        $this->db->update('bom_project_item_materials', array('used_status' => $status_id));
    }

    function dev2_updateMaterialRequestIdByProjectItemId($item_id, $mr_id)
    {
        $this->db->where('project_item_id', $item_id)->where('ratio >', 0);
        $this->db->update('bom_project_item_materials', array('mr_id' => $mr_id));
    }

    function dev2_deleteProjectItemMaterialById($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('bom_project_item_materials');
    }

    function dev2_insertProjectItemMaterialWithStockId($data)
    {
        $this->db->insert('bom_project_item_materials', array(
            'project_item_id' => $data['project_item_id'],
            'material_id' => $data['material_id'],
            'stock_id' => $data['stock_id'],
            'ratio' => $data['ratio']
        ));
    }

    function dev2_insertProjectItemMaterialWithOutStockId($data)
    {
        $this->db->insert('bom_project_item_materials', array(
            'project_item_id' => $data['project_item_id'],
            'material_id' => $data['material_id'],
            'ratio' => $data['ratio']
        ));
    }

    function dev2_getBomListByMaterialRequestIsNotNull()
    {
        $this->db->select('*')->from('bom_project_item_materials')->where('mr_id is not null')->order_by('mr_id', 'desc');

        $query = $this->db->get();
        return $query->result();
    }

}
