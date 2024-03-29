<?php

class Bom_stocks_model extends Crud_model {

    private $table = null;
    private $table2 = null;

    function __construct() {
        $this->table = 'bom_stocks';
        $this->table2 = 'bom_materials';
        $this->main_id = 'material_id';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if (isset($options['id'])) {
            $where .= " AND bs.id = $id";
        }

        $material_id  = get_array_value($options, "material_id ");
        if (isset($options['material_id '])) {
            $where .= " AND bs.material_id  = '".intval($material_id )."'";
        }

        $group_id = get_array_value($options, "group_id");
        if (isset($options['group_id'])) {
            $where .= " AND bs.group_id = '".intval($group_id)."'";
        }
        $sql = "
            SELECT bs.*, 
            bm.name `material_name`, bm.unit `material_unit` 
            FROM {$this->table} bs 
            INNER JOIN {$this->table2} bm ON bm.id = bs.material_id 
            WHERE 1 $where 
            GROUP BY bs.id 
        ";
        return $this->db->query($sql);
    }

    function delete_one($id) {
        $this->db->query("DELETE FROM {$this->table} WHERE id = $id");
        return true;
    }

    function reduce_material($id, $ratio) {
        $temp = $this->db->query("SELECT * FROM {$this->table} WHERE id = $id")->row();
        if (!empty($temp->id) && $temp->id == $id) {
            $remaining = max(0, $temp->remaining - $ratio);
            $this->db->query("
                UPDATE {$this->table} SET remaining = $remaining WHERE id = $id
            ");
        }
        return true;
    }

    function check_posibility($id, $used){
        $temp = $this->db->query("SELECT {$this->main_id} , SUM(`remaining`) AS rem FROM {$this->table} WHERE {$this->main_id} = $id GROUP BY `{$this->main_id}`")->row();
        //var_dump($temp,!empty($temp->material_id),$temp->material_id == $id,$temp->rem >= $used);exit;
        if(!empty($temp->material_id) && $temp->material_id == $id && $temp->rem >= $used){
            
            return true;
        }
        return false;
    }

    function reduce_material_of_group($id, $used) {
        //Query sum of item in stock.
        $temp = $this->db->query("SELECT {$this->main_id} , SUM(`remaining`) AS rem FROM {$this->table} WHERE {$this->main_id} = $id GROUP BY `{$this->main_id}`")->row();
        //var_dump($temp);exit;
        if(!empty($temp->material_id) && $temp->material_id == $id && $temp->rem >= $used){

            //If have item enough in stock. Minus the used item.
            $temp = $this->db->query("SELECT id ,{$this->main_id} , remaining AS rem FROM {$this->table} WHERE {$this->main_id} = $id")->result();
            $red_remain =$used;
            $remaining = 0; 
            foreach($temp as $t){
                //Used item is more than remaining in that group. Remove all item in group and move to next group
                if($red_remain > $t->rem){
                    $red_remain = $red_remain - $t->rem;
                    $this->db->query("UPDATE {$this->table} SET remaining = 0 WHERE id = $t->id");
                }
                //Use item is less than remaining in that group.Remove used item from group
                else{
                    $remaining = $t->rem - $red_remain;
                    $red_remain = 0;
                    $this->db->query("UPDATE {$this->table} SET remaining = $remaining WHERE id = $t->id");
                    break;
                }

            }
            if($red_remain > 0){
                return false;
            }
        }else{
            return false;
        }
        return true;
    }

    public function dev2_getRestockingList($post)
    {
        $where_create_by = "";
        if ($post) {
            $where_create_by = "AND `bsg`.`created_by` = " . $post;
        }

        $sql = "SELECT `bs`.`id` AS 'stock_id', `bsg`.`id` AS 'group_id', `bsg`.`name` AS 'stock_name', `bs`.`serial_number` AS 'serial_number', `bm`.`id` AS 'material_id', `bm`.`name` AS 'material_code', `bm`.`production_name` AS 'material_name', `bm`.`unit` AS 'material_unit', `bs`.`stock` AS 'stock_qty', `bs`.`remaining` AS 'stock_remain', `bsg`.`created_by` AS 'create_by', `bs`.`created_date`, `bsg`.`created_date` AS 'bsg_create_date' 
        FROM `bom_stocks` AS `bs` 
        LEFT JOIN `bom_stock_groups` AS `bsg` ON `bs`.`group_id` = `bsg`.`id` 
        INNER JOIN `bom_materials` AS `bm` ON `bs`.`material_id` = `bm`.`id` 
        WHERE `bs`.`stock` > 0 " . $where_create_by . " ORDER BY `bs`.`id` ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    public function dev2_getRestockingById(&$id)
    {
        $sql = "SELECT `bs`.`id` AS 'stock_id', `bsg`.`id` AS 'group_id', `bsg`.`name` AS 'stock_name', `bs`.`serial_number` AS 'serial_number', `bm`.`id` AS 'material_id', `bm`.`name` AS 'material_code', `bm`.`production_name` AS 'material_name', `bm`.`unit` AS 'material_unit', `bs`.`stock` AS 'stock_qty', `bs`.`remaining` AS 'stock_remain', `bsg`.`created_by` AS 'create_by', `bs`.`created_date`, `bsg`.`created_date` AS 'bsg_create_date' 
        FROM `bom_stocks` AS `bs` 
        LEFT JOIN `bom_stock_groups` AS `bsg` ON `bs`.`group_id` = `bsg`.`id` 
        LEFT JOIN `bom_materials` AS `bm` ON `bs`.`material_id` = `bm`.`id` 
        WHERE `bs`.`stock` > 0 AND `bs`.`group_id` = " . $id . " ORDER BY `bs`.`id` ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    public function dev2_getRestockingByStockId(&$id)
    {
        $sql = "SELECT `bs`.`id` AS 'stock_id', `bsg`.`id` AS 'group_id', `bsg`.`name` AS 'stock_name', `bs`.`serial_number` AS 'serial_number', `bm`.`id` AS 'material_id', `bm`.`name` AS 'material_code', `bm`.`production_name` AS 'material_name', `bm`.`unit` AS 'material_unit', `bs`.`stock` AS 'stock_qty', `bs`.`remaining` AS 'stock_remain', `bsg`.`created_by` AS 'create_by', `bsg`.`created_date` AS 'create_date' 
        FROM `bom_stocks` AS `bs` 
        LEFT JOIN `bom_stock_groups` AS `bsg` ON `bs`.`group_id` = `bsg`.`id` 
        LEFT JOIN `bom_materials` AS `bm` ON `bs`.`material_id` = `bm`.`id` 
        WHERE `bs`.`stock` > 0 AND `bs`.`id` = " . $id . " ORDER BY `bs`.`id` ";

        $query = $this->db->query($sql);
        $stock_info = $query->row();
        $stock_info->actual_remain = $this->dev2_getActualRemainingByStockId($stock_info->stock_id);

        return $stock_info;
    }

    function dev2_getSerialNumByGroupId($group_id)
    {
        $query = $this->db->select('serial_number')->get_where('bom_stocks', array('group_id' => $group_id));
        
        $sern = array();
        foreach ($query->result() as $item) {
            array_push($sern, $item->serial_number);
        }
        return $sern;
    }

    function dev2_getSerialNumByGroupIdWithoutSelf($group_id, $id)
    {
        $sql = "SELECT `serial_number` FROM `bom_stocks` WHERE `group_id` = '" . $group_id . "' AND `id` != '" . $id . "'";
        $query = $this->db->query($sql);

        $sern = array();
        foreach ($query->result() as $item) {
            array_push($sern, $item->serial_number);
        }
        return $sern;
    }

    function dev2_getCountRestockingByMaterialId($id)
    {
        $query = $this->db->get_where('bom_stocks', ['material_id' => $id]);
        return $query->num_rows();
    }

    function dev2_getRestockNameByStockId($id)
    {
        $sql = "SELECT bsg.name FROM bom_stock_groups bsg LEFT JOIN bom_stocks bs ON bsg.id = bs.group_id WHERE 1 AND bs.id = '{$id}'";
        $name = "-";
        
        if (isset($id) && !empty($id)) {
            $query = $this->db->query($sql);
            $name = $query->row()->name;
        }
        return $name;
    }

    function dev2_getRestockGroupNameByStockId($id)
    {
        $sql = "SELECT bsg.id, bsg.name FROM bom_stock_groups bsg LEFT JOIN bom_stocks bs ON bsg.id = bs.group_id WHERE 1 AND bs.id = '{$id}'";
        
        $query = null;
        if (isset($id) && !empty($id)) {
            $query = $this->db->query($sql)->row();
        }
        return $query;
    }

    function dev2_verifyStockUsabled($stock_id, $qty)
    {
        $sql = "SELECT remaining FROM bom_stocks WHERE id = '{$stock_id}'";
        $query = $this->db->query($sql)->row();
        $remaining = $query->remaining;
        
        return $remaining >= $qty;
    }

    function dev2_updateStockUsed($stock_id, $qty)
    {
        $sql = "UPDATE bom_stocks SET remaining = remaining - {$qty} WHERE id = '{$stock_id}'";
        $this->db->query($sql);
    }

    function dev2_getActualRemainingByStockId($stock_id)
    {
        $actual_remain = 0;
        $sql = "
        SELECT 
            CASE 
                WHEN bs.stock - IFNULL(bpim.used_qty, 0) < 0 THEN 0 
                ELSE bs.stock - IFNULL(bpim.used_qty, 0) 
            END AS actual_remain 
        FROM bom_stocks bs
        LEFT JOIN(
            SELECT stock_id, SUM(ratio) AS used_qty 
            FROM bom_project_item_materials 
            WHERE stock_id = " . $stock_id . " GROUP BY stock_id
        ) AS bpim ON bs.id = bpim.stock_id 
        WHERE bs.id = " . $stock_id . "
        ";

        $query = $this->db->query($sql)->row();
        if ($query) {
            $actual_remain = $query->actual_remain;
        }

        return $actual_remain;
    }

    function dev2_getMaterialActualUsed()
    {
        $sql = "
        SELECT bs.id, bs.stock, bs.remaining, IFNULL(bpim.stock_qty, 0) AS actual_used, 
        bs.stock - IFNULL(bpim.stock_qty, 0) AS actual_remain, bs.remaining - (bs.stock - IFNULL(bpim.stock_qty, 0)) AS stock_diff 
        FROM bom_stocks bs 
        INNER JOIN (
            SELECT stock_id, SUM(ratio) AS stock_qty 
            FROM bom_project_item_materials 
            WHERE used_status = 1 
            GROUP BY stock_id
        ) AS bpim ON bs.id = bpim.stock_id 
        WHERE bs.remaining > 0 ORDER BY bs.id ASC
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_optimizeRemainingStock($stock_id, $actual_remain)
    {
        $this->db->where('id', $stock_id);
        $this->db->update('bom_stocks', array('remaining' => $actual_remain));
    }

    public function dev2_getRowTableFromId(string $table, int $id) : stdClass
    {
        $data = new stdClass();

        $query = $this->db->get_where($table, array("id" => $id))->row();

        if (!empty($query)) {
            $data = $query;
        }

        return $data;
    }

    public function dev2_getStockUsedReportRM(array $options) : array
    {
        $data = array();
        $where = "";

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if (is_date_exists($start_date) && is_date_exists($end_date)) {
            $where .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
        }

        $sql = "SELECT * FROM bom_project_item_materials WHERE used_status = 1 $where";

        $query = $this->db->query($sql)->result();
        
        if (sizeof($query)) {
            foreach ($query as $row) {
                $row->material_info = $this->dev2_getRowTableFromId("bom_materials", $row->material_id);
                $row->stock_info = $this->dev2_getRowTableFromId("bom_stocks", $row->stock_id);

                if (isset($row->stock_info->group_id) && !empty($row->stock_info->group_id)) {
                    $row->stock_info->group_info = $this->dev2_getRowTableFromId("bom_stock_groups", $row->stock_info->group_id);
                }

                if (isset($row->mr_id) && !empty($row->mr_id)) {
                    $row->mr_info = $this->dev2_getRowTableFromId("materialrequests", $row->mr_id);
                }
            }

            $data = $query;
        }

        return $data;
    }

    public function dev2_getStockUsedReportFG(array $options) : array
    {
        $data = array();
        $where = "";

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if (is_date_exists($start_date) && is_date_exists($end_date)) {
            $where .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
        }

        $sql = "SELECT bpii.* FROM bom_project_item_items AS bpii LEFT JOIN items AS i ON bpii.item_id = i.id WHERE i.item_type = 'FG' AND bpii.used_status = 1 $where";

        $query = $this->db->query($sql)->result();

        if (sizeof($query)) {
            foreach ($query as $row) {
                $row->item_info = $this->dev2_getRowTableFromId("items", $row->item_id);
                $row->stock_info = $this->dev2_getRowTableFromId("bom_item_stocks", $row->stock_id);


                if (isset($row->stock_info->group_id) && !empty($row->stock_info->group_id)) {
                    $row->stock_info->group_info = $this->dev2_getRowTableFromId("bom_item_groups", $row->stock_info->group_id);
                }

                if (isset($row->mr_id) && !empty($row->mr_id)) {
                    $row->mr_info = $this->dev2_getRowTableFromId("materialrequests", $row->mr_id);
                }
            }

            $data = $query;
        }

        return $data;
    }

    public function dev2_getStockUsedReportSFG(array $options) : array
    {
        $data = array();
        $where = "";

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if (is_date_exists($start_date) && is_date_exists($end_date)) {
            $where .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
        }

        $sql = "SELECT bpii.* FROM bom_project_item_items AS bpii LEFT JOIN items AS i ON bpii.item_id = i.id WHERE i.item_type = 'SFG' AND bpii.used_status = 1 $where";

        $query = $this->db->query($sql)->result();

        if (sizeof($query)) {
            foreach ($query as $row) {
                $row->item_info = $this->dev2_getRowTableFromId("items", $row->item_id);
                $row->stock_info = $this->dev2_getRowTableFromId("bom_item_stocks", $row->stock_id);

                if (isset($row->stock_info->group_id) && !empty($row->stock_info->group_id)) {
                    $row->stock_info->group_info = $this->dev2_getRowTableFromId("bom_item_groups", $row->stock_info->group_id);
                }

                if (isset($row->mr_id) && !empty($row->mr_id)) {
                    $row->mr_info = $this->dev2_getRowTableFromId("materialrequests", $row->mr_id);
                }
            }

            $data = $query;
        }

        return $data;
    }

}
