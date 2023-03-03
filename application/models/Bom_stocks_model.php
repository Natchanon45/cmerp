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
    

}
