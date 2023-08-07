<?php

class Bom_item_pricings_model extends Crud_model
{
    private $table = null;

    function __construct()
    {
        $this->table = 'bom_item_pricings';
        parent::__construct($this->table);
    }

    function getItemPricingById($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    function getItemPricingByItemId($item_id)
    {
        return $this->db->get_where($this->table, ['item_id' => $item_id])->result();
    }

    function getItemPricingBySupplierId($supplier_id)
    {
        return $this->db->get_where($this->table, ['supplier_id' => $supplier_id])->result();
    }

    function getItemPricingByItemSupplierId($item_id, $supplier_id)
    {
        return $this->db->get_where($this->table, ['item_id' => $item_id, 'supplier_id' => $supplier_id])->row();
    }

    function postItemPricingByInfo($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    function putItemPricingByPricingInfo($id, $data)
    {
        $this->db->where('id', $id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    function patchItemPricingByPricingInfo($item_id, $supplier_id, $data)
    {
        $this->db->where('item_id', $item_id)->where('supplier_id', $supplier_id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    function deleteItemPricingById($id)
    {
        $this->db->where('id', $id)->delete($this->table);
    }

}