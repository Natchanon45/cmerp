<?php

class Bom_item_stocks_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_item_stocks';
        $this->main_id = 'item_id';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if (isset($options['id'])) {
            $where .= " AND bi.id = '".intval($id)."'";
        }

        $item_id = get_array_value($options, "item_id");
        if (isset($options['item_id'])) {
            $where .= " AND bi.item_id = '".intval($item_id)."'";
        }

        $group_id = get_array_value($options, "group_id");
        if (isset($options['group_id'])) {
            $where .= " AND bi.group_id = '".intval($group_id)."'";
        }
        $sql = "
            SELECT bi.*, 
            itm.title `item_name`, itm.unit_type `item_unit` 
            FROM bom_item_stocks bi 
            INNER JOIN items itm ON itm.id = bi.item_id 
            WHERE 1 $where 
            GROUP BY bi.id 
        ";
        return $this->db->query($sql);
    }
    function delete_one($id) {
        $this->db->query("DELETE FROM bom_item_stocks WHERE id = $id");
        return true;
    }

    function update_stock($id, $qty, $operand='+') {
        $temp = $this->db->query("SELECT * FROM bom_item_stocks WHERE id = $id")->row();
        if (!empty($temp->id) && $temp->id == $id) {
            if($operand=='-')
                $remaining = max(0, $temp->remaining - $qty);
            else
                $remaining = $temp->remaining + $qty;
            $this->db->query("
                UPDATE bom_item_stocks SET remaining = '$remaining' WHERE id = $id
            ");
        }
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
        var_dump($temp,!empty($temp->item_id),$temp->item_id == $id,$temp->rem >= $used);exit;
        if(!empty($temp->item_id) && $temp->item_id == $id && $temp->rem >= $used){
            return true;
        }
        return false;
    }

    function reduce_item_of_group($id, $used) {
        //Query sum of item in stock.
        $temp = $this->db->query("SELECT {$this->main_id} , SUM(`remaining`) AS rem FROM {$this->table} WHERE {$this->main_id} = $id GROUP BY `{$this->main_id}`")->row();
        //var_dump($temp);exit;
        if(!empty($temp->item_id) && $temp->item_id == $id && $temp->rem >= $used){

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
