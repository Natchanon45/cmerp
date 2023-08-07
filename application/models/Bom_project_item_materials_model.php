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
        $this->db->where('id', $id)->where('entry_flag', 1)->delete('bom_project_item_materials');
        
        if ($this->db->affected_rows() == 0) {
            $this->db->where('id', $id);
            $this->db->update('bom_project_item_materials', array('mr_id' => null));
        }
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

    function postProjectItemMaterialFromMaterialRequest($data)
    {
        $verify = $this->db->get_where('bom_project_item_materials', array(
            'mr_id' => $data['mr_id'],
            'material_id' => $data['material_id'],
            'stock_id' => $data['stock_id']
        ));

        if ($verify->num_rows() > 0) {
            return $verify->row()->id;
        } else {
            $this->db->insert('bom_project_item_materials', $data);
            return $this->db->insert_id();
        }
    }

    function patchProjectItemMaterialFromMaterialRequest($data)
    {
        $this->db->set('stock_id', $data['stock_id']);
        $this->db->set('ratio', $data['ratio']);
        $this->db->where('id', $data['id']);
        $this->db->update('bom_project_item_materials');
        return $this->db->affected_rows();
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

    function dev2_getBomMaterialToCreatePrAll()
    {
        $sql = "SELECT * FROM bom_project_item_materials bpim WHERE bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 ORDER BY bpim.material_id";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrByProjectItemId($project_item_id)
    {
        $sql = "SELECT * FROM bom_project_item_materials bpim 
        WHERE bpim.project_item_id = '" . $project_item_id . "' 
        AND bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 ORDER BY bpim.id";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrByProjectId($project_id)
    {
        $sql = "SELECT bpim.material_id, SUM(bpim.ratio) AS total_ratio FROM bom_project_items bpi 
        LEFT JOIN bom_project_item_materials bpim ON bpi.id = bpim.project_item_id 
        WHERE bpi.project_id = '" . $project_id . "' AND bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 
        GROUP BY bpim.material_id ORDER BY bpim.material_id";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrList()
    {
        $sql = "
        SELECT bpim.id, bpi.project_id, bpi.project_name, bpim.material_id, 
        bs.name, bs.production_name, bpim.ratio, bs.unit, bpim.created_at 
        FROM bom_project_item_materials bpim 
        LEFT JOIN bom_materials bs ON bpim.material_id = bs.id 
        LEFT JOIN(
            SELECT a.id AS project_item_id, a.project_id, b.title AS project_name 
            FROM bom_project_items a INNER JOIN projects b ON a.project_id = b.id
        ) AS bpi ON bpim.project_item_id = bpi.project_item_id 
        WHERE 
        bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 
        ORDER BY bpi.project_id
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrSummary()
    {
        $sql = "
        SELECT bpim.material_id, bs.name, bs.production_name, bs.unit, SUM(bpim.ratio) AS ratio 
        FROM bom_project_item_materials bpim 
        LEFT JOIN bom_materials bs ON bpim.material_id = bs.id 
        WHERE bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 
        GROUP BY bpim.material_id
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrInMaterialsId($material_ids)
    {
        $sql = "
        SELECT bpim.material_id, bs.name, bs.production_name, bs.description, bs.unit, SUM(bpim.ratio) AS ratio 
        FROM bom_project_item_materials bpim 
        LEFT JOIN bom_materials bs ON bpim.material_id = bs.id 
        WHERE bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 AND bpim.material_id IN (" . $material_ids . ") 
        GROUP BY bpim.material_id
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getBomMaterialToCreatePrInBpimId($bpim_ids)
    {
        $sql = "
        SELECT bpim.id, bpim.material_id, bs.name, bs.production_name, bs.description, bs.unit, bpim.ratio 
        FROM bom_project_item_materials bpim 
        LEFT JOIN bom_materials bs ON bpim.material_id = bs.id 
        WHERE bpim.pr_id IS NULL AND bpim.stock_id IS NULL AND bpim.ratio < 0 
        AND bpim.id IN(" . $bpim_ids . ") 
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_updatePrIdByMaterialId($pr_id, $material_id)
    {
        $this->db->where('material_id', $material_id);
        $this->db->update('bom_project_item_materials', array('pr_id' => $pr_id));

        return $this->db->affected_rows();
    }

    function dev2_updatePrIdByBpimId($pr_id, $bpim_id)
    {
        $this->db->where('id', $bpim_id);
        $this->db->update('bom_project_item_materials', array('pr_id' => $pr_id));

        return $this->db->affected_rows();
    }

    function dev2_deleteBomMaterialToCreatePrInMaterialsId($material_id)
    {
        $sql = "DELETE FROM bom_project_item_materials WHERE ratio < 0 AND material_id = '" . $material_id . "'";
        $this->db->query($sql);
        
        return $this->db->affected_rows();
    }

    function dev2_deleteBomMaterialToCreatePrByBpimId($bpim_id)
    {
        $sql = "DELETE FROM bom_project_item_materials WHERE ratio < 0 AND id = '" . $bpim_id . "'";
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

}
