<?php

class Items_model extends Crud_model
{
	private $table = null;

	function __construct()
	{
		$this->table = 'items';
		parent::__construct($this->table);
	}

	function get_details($options = array())
	{
		$items_table = $this->db->dbprefix('items');
		$order_items_table = $this->db->dbprefix('order_items');
		$item_categories_table = $this->db->dbprefix('item_categories');

		$where = "";

		$item_type = get_array_value($options, "item_type");
		
		if($item_type != null){
			if(strtoupper($item_type) == "SFG") $where .= " AND item_type='".strtoupper($item_type)."'";
			if(strtoupper($item_type) == "FG") $where .= " AND item_type='".strtoupper($item_type)."'";
		}else{
			$where .= " AND item_type='FG'";
		}

		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND $items_table.id=$id";
		}

		$search = get_array_value($options, "search");
		if ($search) {
			$search = $this->db->escape_str($search);
			$where .= " AND ($items_table.title LIKE '%$search%' OR $items_table.description LIKE '%$search%')";
		}

		$show_in_client_portal = get_array_value($options, "show_in_client_portal");
		if ($show_in_client_portal) {
			$where .= " AND $items_table.show_in_client_portal=1";
		}

		$category_id = get_array_value($options, "category_id");
		if ($category_id) {
			$where .= " AND $items_table.category_id=$category_id";
		}

		$extra_select = "";
		$login_user_id = get_array_value($options, "login_user_id");
		if ($login_user_id) {
			$extra_select = ", (SELECT COUNT($order_items_table.id) FROM $order_items_table WHERE $order_items_table.deleted=0 AND $order_items_table.order_id=0 AND $order_items_table.created_by=$login_user_id AND $order_items_table.item_id=$items_table.id) AS added_to_cart";
		}

		$limit_query = "";
		$limit = get_array_value($options, "limit");
		if ($limit) {
			$offset = get_array_value($options, "offset");
			$limit_query = "LIMIT $offset, $limit";
		}

		$sql = "SELECT $items_table.*, $item_categories_table.title as category_title $extra_select
			FROM $items_table
			LEFT JOIN $item_categories_table ON $item_categories_table.id= $items_table.category_id
			WHERE $items_table.deleted=0 $where
			ORDER BY $items_table.title ASC
			$limit_query";

		return $this->db->query($sql);
	}

	function get_items($options = array())
	{
		$items_table = $this->db->dbprefix('items');
		$order_items_table = $this->db->dbprefix('order_items');
		$item_categories_table = $this->db->dbprefix('item_categories');

		$where = "";
		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND $items_table.id=$id";
		}

		return $this->db->query(
			"SELECT $items_table.id, $items_table.title, $items_table.unit_type 
			FROM $items_table 
			WHERE $items_table.deleted=0 $where 
			ORDER BY $items_table.title ASC"
		);
	}

	function get_categories($options = array())
	{
		$where = "";
		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND bmc.id = $id";
		}

		return $this->db->query(
			"SELECT bmc.* 
			FROM item_categories bmc 
			WHERE 1 $where "
		);
	}

	function category_create($data)
	{
		$this->db->insert('item_categories', $data);
		return $this->db->insert_id();
	}

	function category_update($data)
	{
		$this->db->replace('item_categories', $data);
		return $data['id'];
	}

	function category_delete($id = 0)
	{
		$this->db->query("UPDATE items SET category_id = NULL WHERE category_id = $id");
		$this->db->delete('item_categories', ['id' => $id]);
		return true;
	}

	function get_category_dropdown($options = array())
	{
		$data = $this->get_categories($options)->result();
		$result = [
			['id' => '', 'text' => '- ' . lang('stock_material_category') . ' -']
		];
		foreach ($data as $d) {
			$result[] = ['id' => $d->id, 'text' => $d->title];
		}
		return $result;
	}

	function get_item_request_suggestion($keyword = "")
	{
		$item_table = $this->db->dbprefix('items');

		$keyword = $this->db->escape_str($keyword);

		$sql = "SELECT $item_table.`id`,concat($item_table.`title`,'  ',' (',FORMAT(SUM(bs.remaining), 'N2'),' ',$item_table.`unit_type`,')') as `text` , SUM(bs.item_id) AS 'remaining'
			FROM $item_table
			LEFT JOIN bom_item_stocks bs ON $item_table.id = bs.item_id  
			WHERE bs.remaining > 0 
			AND $item_table.`title` LIKE '%$keyword%'
			GROUP BY bs.item_id
			LIMIT 10 
		";

		// arr($sql);
		return $this->db->query($sql)->result();
	}

	function get_item_info_suggestion($material_id)
	{
		$materials_table = $this->db->dbprefix('items');

		$material_id = $this->db->escape_str($material_id);

		$sql = "SELECT $materials_table.*, SUM(bs.remaining) AS remaining
			FROM $materials_table
			LEFT JOIN bom_item_stocks bs ON $materials_table.id = bs.item_id  
			WHERE $materials_table.`id` = '$material_id'
			AND bs.remaining > 0 
			GROUP BY bs.item_id
        	";

		// arr($sql);
		$result = $this->db->query($sql);

		if ($result->num_rows()) {
			return $result->row();
		}
	}

	function dev2_getItemDropDown()
	{
		$result = $this->db->get_where('items', ['deleted' => 0])->result();
		$data[] = [
			'id' => '',
			'text' => '- ' . lang('finished_goods') . ' -',
			'unit' => ''
		];

		foreach ($result as $item) {
			if (isset($item->item_code) && !empty($item->item_code)) {
				$item->fg_name = $item->item_code . ' - ' . $item->title;
			} else {
				$item->fg_name = $item->title;
			}

			$data[] = [
				'id' => $item->id,
				'text' => $item->fg_name,
				'unit' => $item->unit_type ? $item->unit_type : lang('stock_material_unit')
			];
		}
		return $data;
	}

	function dev2_getItemPricings($options)
	{
		$data = [];

		if (isset($options['item_id']) && !empty($options['item_id'])) {
			$this->db->where('item_id', $options['item_id']);
		}

		if (isset($options['id']) && !empty($options['id'])) {
			$this->db->where('id', $options['id']);
		}

		$items = $this->db->get('bom_item_pricings')->result();

		if (sizeof($items)) {
			foreach ($items as $item) {
				$item->item_data = $this->db->get_where('items', ['id' => $item->item_id])->row();
				$item->supplier_data = $this->db->get_where('bom_suppliers', ['id' => $item->supplier_id])->row();
				$item->supplier_contact = $this->db->get_where('bom_supplier_contacts', ['supplier_id' => $item->supplier_id, 'is_primary' => 0])->row();
			}

			$data = $items;
		}

		return $data;
	}

}