<?php

class Bom_item_stocks_model extends Crud_model
{

    private $table = null;
    private $main_id = null;

    function __construct()
    {
        $this->table = 'bom_item_stocks';
        $this->main_id = 'item_id';

        parent::__construct($this->table);
    }

    function get_details($options = array())
    {
        $where = "";

        $id = get_array_value($options, "id");
        if (isset($options['id'])) {
            $where .= " AND bi.id = '" . intval($id) . "'";
        }

        $item_id = get_array_value($options, "item_id");
        if (isset($options['item_id'])) {
            $where .= " AND bi.item_id = '" . intval($item_id) . "'";
        }

        $group_id = get_array_value($options, "group_id");
        if (isset($options['group_id'])) {
            $where .= " AND bi.group_id = '" . intval($group_id) . "'";
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

    function delete_one($id)
    {
        $this->db->query("DELETE FROM bom_item_stocks WHERE id = $id");
        return true;
    }

    function update_stock($id, $qty, $operand = '+')
    {
        $temp = $this->db->query("SELECT * FROM bom_item_stocks WHERE id = $id")->row();
        if (!empty($temp->id) && $temp->id == $id) {
            if ($operand == '-')
                $remaining = max(0, $temp->remaining - $qty);
            else
                $remaining = $temp->remaining + $qty;
            $this->db->query("
                UPDATE bom_item_stocks SET remaining = '$remaining' WHERE id = $id
            ");
        }
        return true;
    }

    function reduce_material($id, $ratio)
    {
        $temp = $this->db->query("SELECT * FROM {$this->table} WHERE id = $id")->row();
        if (!empty($temp->id) && $temp->id == $id) {
            $remaining = max(0, $temp->remaining - $ratio);
            $this->db->query("
                UPDATE {$this->table} SET remaining = $remaining WHERE id = $id
            ");
        }
        return true;
    }

    function check_posibility($id, $used)
    {
        $temp = $this->db->query("SELECT {$this->main_id} , SUM(`remaining`) AS rem FROM {$this->table} WHERE {$this->main_id} = $id GROUP BY `{$this->main_id}`")->row();
        // var_dump($temp, !empty($temp->item_id), $temp->item_id == $id, $temp->rem >= $used);
        // exit;
        if (!empty($temp->item_id) && $temp->item_id == $id && $temp->rem >= $used) {
            return true;
        }
        return false;
    }

    function reduce_item_of_group($id, $used)
    {
        //Query sum of item in stock.
        $temp = $this->db->query("SELECT {$this->main_id} , SUM(`remaining`) AS rem FROM {$this->table} WHERE {$this->main_id} = $id GROUP BY `{$this->main_id}`")->row();
        //var_dump($temp);exit;
        if (!empty($temp->item_id) && $temp->item_id == $id && $temp->rem >= $used) {

            //If have item enough in stock. Minus the used item.
            $temp = $this->db->query("SELECT id ,{$this->main_id} , remaining AS rem FROM {$this->table} WHERE {$this->main_id} = $id")->result();
            $red_remain = $used;
            $remaining = 0;
            foreach ($temp as $t) {
                //Used item is more than remaining in that group. Remove all item in group and move to next group
                if ($red_remain > $t->rem) {
                    $red_remain = $red_remain - $t->rem;
                    $this->db->query("UPDATE {$this->table} SET remaining = 0 WHERE id = $t->id");
                }
                //Use item is less than remaining in that group.Remove used item from group
                else {
                    $remaining = $t->rem - $red_remain;
                    $red_remain = 0;
                    $this->db->query("UPDATE {$this->table} SET remaining = $remaining WHERE id = $t->id");
                    break;
                }

            }
            if ($red_remain > 0) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    function dev2_getSerialNumByGroupId($group_id)
    {
        $query = $this->db->select('serial_number')->get_where('bom_item_stocks', array('group_id' => $group_id));

        $sern = array();
        foreach ($query->result() as $item) {
            array_push($sern, $item->serial_number);
        }
        return $sern;
    }

    function dev2_getSerialNumByGroupIdWithoutSelf($group_id, $id)
    {
        $sql = "SELECT `serial_number` FROM `bom_item_stocks` WHERE `group_id` = '" . $group_id . "' AND `id` != '" . $id . "'";
        $query = $this->db->query($sql);

        $sern = array();
        foreach ($query->result() as $item) {
            array_push($sern, $item->serial_number);
        }
        return $sern;
    }

    function dev2_getCountRestockingByItemId($id)
    {
        $query = $this->db->get_where('bom_item_stocks', ['item_id' => $id]);
        return $query->num_rows();
    }

    function dev2_getRestockingByStockId($id)
    {
        $sql = "SELECT `bs`.`id` AS 'stock_id', `bsg`.`id` AS 'group_id', `bsg`.`name` AS 'stock_name', `bs`.`serial_number` AS 'serial_number', `bm`.`id` AS 'material_id', `bm`.`item_code` AS 'material_code', `bm`.`title` AS 'material_name', `bm`.`unit_type` AS 'material_unit', `bs`.`stock` AS 'stock_qty', `bs`.`remaining` AS 'stock_remain', `bsg`.`created_by` AS 'create_by', `bsg`.`created_date` AS 'create_date' 
        FROM `bom_item_stocks` AS `bs` 
        LEFT JOIN `bom_item_groups` AS `bsg` ON `bs`.`group_id` = `bsg`.`id` 
        LEFT JOIN `items` AS `bm` ON `bs`.`item_id` = `bm`.`id` 
        WHERE `bs`.`stock` > 0 AND `bs`.`id` = " . $id . " ORDER BY `bs`.`id` ";

        $query = $this->db->query($sql);
        $stock_info = $query->row();
        $stock_info->actual_remain = $this->dev2_getActualRemainingByStockId($stock_info->stock_id);

        return $stock_info;
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
        FROM bom_item_stocks bs
        LEFT JOIN(
            SELECT stock_id, SUM(ratio) AS used_qty 
            FROM bom_project_item_items 
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

    function dev2_verifyStockUsabled($stock_id, $qty)
    {
        $sql = "SELECT remaining FROM bom_item_stocks WHERE id = '{$stock_id}'";
        $query = $this->db->query($sql)->row();
        $remaining = $query->remaining;

        return $remaining >= $qty;
    }

    function dev2_updateStockUsed($stock_id, $qty)
    {
        $sql = "UPDATE bom_item_stocks SET remaining = remaining - {$qty} WHERE id = '{$stock_id}'";
        $this->db->query($sql);
    }

    public function cancelFinishedGoodsSaleTestCase(array $sale_info): array
    {
        if (sizeof($sale_info)) {
            $result = array(
                'sale_id' => $sale_info['sale_id'],
                'sale_type' => $sale_info['sale_type']
            );

            $sale_items = $this->fgSaleListBySaleTypeAndIdTestCase($result['sale_type'], $result['sale_id']);
            if (sizeof($sale_items)) {
                foreach ($sale_items as $item) {
                    // var_dump(arr($item));

                    $this->fgSaleReturnStockByStockIdTestCase($item->stock_id, $item->ratio);
                    $this->fgSaleDeleteSaleListByIdTestCase($item->id);
                }
            } else {
                $result['status'] = 'failure';
            }

            $result['status'] = 'success';
        }

        return $result;
    }

    public function processFinishedGoodsSaleTestCase(array $sale_info): array
    {
        if (sizeof($sale_info['items'])) {
            $result = array(
                'sale_id' => $sale_info['sale_id'],
                'sale_type' => $sale_info['sale_type'],
                'sale_document' => $sale_info['sale_document'],
                'project_id' => $sale_info['project_id'],
                'created_by' => $sale_info['created_by']
            );

            foreach ($sale_info['items'] as $item) {
                $item_result = array();
                $result['items'][] = array(
                    'id' => $item['id'], 'item_id' => $item['item_id'], 'ratio' => $item['ratio']
                );

                $item_stock = $this->fgSumRemainingByItemIdTestCase($item['item_id']);
                // var_dump(arr($item_stock));

                if (isset($item_stock->remaining) && !empty($item_stock->remaining)) {
                    $item_stock_list = $this->fgRemainingListByItemIdTestCase($item['item_id']);
                    // var_dump(arr($item_stock_list));

                    if (sizeof($item_stock_list)) {
                        $pop = count($item_stock_list) - 1;
                        $ratio = $item['ratio'];
                        
                        for ($i = 0; $i < count($item_stock_list); $i++) {
                            // var_dump(arr($item_stock_list[$i]));

                            $usage = min($ratio, $item_stock_list[$i]->remaining);
                            if (isset($usage) && $usage > 0) {
                                $this->db->where('id', $item_stock_list[$i]->id);
                                $this->db->update('bom_item_stocks', ['remaining' => $item_stock_list[$i]->remaining - $usage]);
                                $this->db->insert('bom_project_item_items', [
                                    'project_id' => $sale_info['project_id'],
                                    'item_id' => $item['item_id'],
                                    'stock_id' => $item_stock_list[$i]->id,
                                    'ratio' => $usage,
                                    'sale_id' => $sale_info['sale_id'],
                                    'sale_type' => $sale_info['sale_type'],
                                    'sale_document' => $sale_info['sale_document'],
                                    'sale_item_id' => $item['id'],
                                    'used_status' => 1,
                                    'created_by' => $sale_info['created_by']
                                ]);

                                array_push($item_result, $this->db->insert_id());
                                $ratio = $ratio - $usage;
                            }
                        }

                        if ($ratio > 0) {
                            $this->db->where('id', $item_stock_list[$pop]->id);
                            $this->db->update('bom_item_stocks', ['remaining' => $item_stock_list[$pop]->remaining - $ratio]);
                            $this->db->insert('bom_project_item_items', [
                                'project_id' => $sale_info['project_id'],
                                'item_id' => $item['item_id'],
                                'stock_id' => $item_stock_list[$pop]->id,
                                'ratio' => $ratio,
                                'sale_id' => $sale_info['sale_id'],
                                'sale_type' => $sale_info['sale_type'],
                                'sale_document' => $sale_info['sale_document'],
                                'sale_item_id' => $item['id'],
                                'used_status' => 1,
                                'created_by' => $sale_info['created_by']
                            ]);

                            array_push($item_result, $this->db->insert_id());
                        }
                    }
                } else {
                    $this->db->insert('bom_item_stocks', [
                        'item_id' => $item['item_id'],
                        'stock' => 0,
                        'remaining' => $item['ratio'] * -1,
                        'note' => 'backordered'
                    ]);
                    $backordered_id = $this->db->insert_id();

                    $this->db->insert('bom_project_item_items', [
                        'project_id' => $sale_info['project_id'],
                        'item_id' => $item['item_id'],
                        'stock_id' => $backordered_id,
                        'ratio' => $item['ratio'],
                        'sale_id' => $sale_info['sale_id'],
                        'sale_type' => $sale_info['sale_type'],
                        'sale_document' => $sale_info['sale_document'],
                        'sale_item_id' => $item['id'],
                        'used_status' => 1,
                        'created_by' => $sale_info['created_by']
                    ]);

                    array_push($item_result, $this->db->insert_id());
                }

                $result['result'][] = array(
                    'id' => $item['id'], 'bpii_id' => $item_result
                );
            }

            $result['status'] = 'success';
        } else {
            $result['status'] = 'failure';
        }
        
        return $result;
    }

    public function FinishedGoodsSalesVoidTestCase(array $sale_info): array
    {
        $this->db->trans_start();

        if (sizeof($sale_info)) {
            $result = array(
                'sale_id' => $sale_info['sale_id'],
                'sale_type' => $sale_info['sale_type']
            );

            $sale_items = $this->fgSaleListBySaleTypeAndIdTestCase($result['sale_type'], $result['sale_id']);
            if (sizeof($sale_items)) {
                foreach ($sale_items as $item) {
                    // var_dump(arr($item));

                    $this->fgSaleReturnStockByStockIdTestCase($item->stock_id, $item->ratio);
                    $this->fgSaleDeleteSaleListByIdTestCase($item->id);
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $result['status'] = 'failure';
            $this->db->trans_rollback();
        } else {
            $result['status'] = 'success';
            $this->db->trans_commit();
        }

        return $result;
    }

    public function FinishedGoodsSalesTestCase(SaleInformation $sale_info): array
    {
        $this->db->trans_start();

        if (sizeof($sale_info->items)) {
            $result = array(
                'sale_id' => $sale_info->sale_id,
                'sale_type' => $sale_info->sale_type,
                'sale_document' => $sale_info->sale_document,
                'project_id' => $sale_info->project_id,
                'created_by' => $sale_info->created_by
            );

            foreach ($sale_info->items as $item) {
                $item_result = array();
                $result['items'][] = array(
                    'id' => $item['id'], 'item_id' => $item['item_id'], 'ratio' => $item['ratio']
                );

                $item_stock = $this->fgSumRemainingByItemIdTestCase($item['item_id']);
                // var_dump(arr($item_stock));

                if (isset($item_stock->remaining) && !empty($item_stock->remaining)) {
                    $item_stock_list = $this->fgRemainingListByItemIdTestCase($item['item_id']);
                    // var_dump(arr($item_stock_list));

                    if (sizeof($item_stock_list)) {
                        $pop = count($item_stock_list) - 1;
                        $ratio = $item['ratio'];
                        
                        for ($i = 0; $i < count($item_stock_list); $i++) {
                            // var_dump(arr($item_stock_list[$i]));

                            $usage = min($ratio, $item_stock_list[$i]->remaining);
                            if (isset($usage) && $usage > 0) {
                                $this->db->where('id', $item_stock_list[$i]->id);
                                $this->db->update('bom_item_stocks', ['remaining' => $item_stock_list[$i]->remaining - $usage]);
                                $this->db->insert('bom_project_item_items', [
                                    'project_id' => $sale_info->project_id,
                                    'item_id' => $item['item_id'],
                                    'stock_id' => $item_stock_list[$i]->id,
                                    'ratio' => $usage,
                                    'sale_id' => $sale_info->sale_id,
                                    'sale_type' => $sale_info->sale_type,
                                    'sale_document' => $sale_info->sale_document,
                                    'sale_item_id' => $item['id'],
                                    'used_status' => 1,
                                    'created_by' => $sale_info->created_by
                                ]);

                                array_push($item_result, $this->db->insert_id());
                                $ratio = $ratio - $usage;
                            }
                        }

                        if ($ratio > 0) {
                            $this->db->where('id', $item_stock_list[$pop]->id);
                            $this->db->update('bom_item_stocks', ['remaining' => $item_stock_list[$pop]->remaining - $ratio]);
                            $this->db->insert('bom_project_item_items', [
                                'project_id' => $sale_info->project_id,
                                'item_id' => $item['item_id'],
                                'stock_id' => $item_stock_list[$pop]->id,
                                'ratio' => $ratio,
                                'sale_id' => $sale_info->sale_id,
                                'sale_type' => $sale_info->sale_type,
                                'sale_document' => $sale_info->sale_document,
                                'sale_item_id' => $item['id'],
                                'used_status' => 1,
                                'created_by' => $sale_info->created_by
                            ]);

                            array_push($item_result, $this->db->insert_id());
                        }
                    }
                } else {
                    $this->db->insert('bom_item_stocks', [
                        'item_id' => $item['item_id'],
                        'stock' => 0,
                        'remaining' => $item['ratio'] * -1,
                        'note' => 'backordered'
                    ]);
                    $backordered_id = $this->db->insert_id();

                    $this->db->insert('bom_project_item_items', [
                        'project_id' => $sale_info->project_id,
                        'item_id' => $item['item_id'],
                        'stock_id' => $backordered_id,
                        'ratio' => $item['ratio'],
                        'sale_id' => $sale_info->sale_id,
                        'sale_type' => $sale_info->sale_type,
                        'sale_document' => $sale_info->sale_document,
                        'sale_item_id' => $item['id'],
                        'used_status' => 1,
                        'created_by' => $sale_info->created_by
                    ]);

                    array_push($item_result, $this->db->insert_id());
                }

                $result['result'][] = array(
                    'id' => $item['id'], 'bpii_id' => $item_result
                );
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $result['status'] = 'failure';
            $this->db->trans_rollback();
        } else {
            $result['status'] = 'success';
            $this->db->trans_commit();
        }
        
        return $result;
    }

    private function fgSaleListBySaleTypeAndIdTestCase(string $sale_type, int $sale_id): ?array
    {
        return $this->db->select('*')
        ->from('bom_project_item_items')
        ->where('sale_type', $sale_type)
        ->where('sale_id', $sale_id)
        ->order_by('id', 'asc')
        ->get()
        ->result();
    }

    private function fgSaleReturnStockByStockIdTestCase(int $stock_id, float $ratio): void
    {
        $sql = "UPDATE bom_item_stocks SET remaining = remaining + ? WHERE id = ?";
        $this->db->query($sql, array($ratio, $stock_id));
    }

    private function fgSaleDeleteSaleListByIdTestCase(int $id): void
    {
        $this->db->where('id', $id);
        $this->db->delete('bom_project_item_items');
    }

    private function fgSumRemainingByItemIdTestCase(int $item_id): ?stdClass
    {
        return $this->db->select('item_id, SUM(remaining) AS remaining')
        ->from('bom_item_stocks')
        ->where('item_id', $item_id)
        ->group_by('item_id')
        ->get()
        ->row();
    }

    private function fgRemainingListByItemIdTestCase(int $item_id): ?array
    {
        return $this->db->select('*')->from('bom_item_stocks')
        ->where('item_id', $item_id)
        ->order_by('id', 'asc')
        ->get()
        ->result();
    }

}

class SaleInformation extends Bom_item_stocks_model
{
    public int $sale_id;
    public string $sale_type;
    public string $sale_document;
    public int $project_id;
    public int $created_by;
    public array $items = [
        array(
            'id' => 0,
            'item_id' => 0,
            'ratio' => 0.00
        )
    ];
}
