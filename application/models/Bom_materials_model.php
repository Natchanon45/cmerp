<?php

class Bom_materials_model extends Crud_model
{

	private $table = null;

	function __construct()
	{
		$this->table = 'bom_materials';
		parent::__construct($this->table);
	}

	function get_details($options = array())
	{
		$where = "";

		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND bm.id = $id";
		}

		$category_id = get_array_value($options, "category_id");
		if ($category_id) {
			$where .= " AND bm.category_id = $category_id";
		}

		$exceptId = get_array_value($options, "except_id");
		if ($exceptId) {
			$where .= " AND bm.id != $exceptId";
		}

		return $this->db->query(
			"SELECT bm.*, bmc.title category, SUM(bs.remaining) remaining 
			FROM bom_materials bm 
			LEFT JOIN bom_material_categories bmc ON bmc.id = bm.category_id 
			LEFT JOIN bom_stocks bs ON bs.material_id = bm.id AND bs.remaining > 0 
			WHERE 1 $where 
			GROUP BY bm.id "
		);
	}

	function delete_material_and_sub_items($material_id)
	{
		$this->db->query("DELETE FROM bom_materials WHERE id = $material_id");
		$this->db->query("DELETE FROM bom_material_mixings WHERE material_id = $material_id");
		$this->db->query("DELETE FROM bom_item_mixings WHERE material_id = $material_id");
		return true;
	}

	function duplicated_name($name)
	{
		$temp = $this->db->query("SELECT id FROM bom_materials WHERE name = '$name'");
		$temp = $temp->row();
		if ($temp) {
			return true;
		} else {
			return false;
		}
	}

	function get_mixings($options = array())
	{
		$where = "";
		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND bmm.material_id = $id";
		}

		return $this->db->query(
			"SELECT bmm.*, bm.name using_material_name, bm.unit using_material_unit 
			FROM bom_material_mixings bmm 
			INNER JOIN bom_materials bm ON bm.id = bmm.using_material_id 
			WHERE 1 $where 
			GROUP BY bmm.id "
		);
	}

	function mixing_save($material_id = 0, $ratio = 0, $using_material_ids = [], $using_ratios = [])
	{
		$this->db->query("DELETE FROM bom_material_mixings WHERE material_id = $material_id");
		if (!empty($using_material_ids) && sizeof($using_material_ids)) {
			foreach ($using_material_ids as $i => $d) {
				if (!empty($ratio) && $ratio > 0 && !empty($using_ratios[$i]) && $using_ratios[$i] > 0) {
					$this->db->insert('bom_material_mixings', [
						'material_id' => $material_id,
						'ratio' => $ratio,
						'using_material_id' => $d,
						'using_ratio' => $using_ratios[$i]
					]);
				}
			}
		}
	}

	function get_pricings($options = array())
	{
		$where = "";

		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND bmp.id = $id";
		}

		$material_id = get_array_value($options, "material_id");
		if ($material_id) {
			$where .= " AND bmp.material_id = $material_id";
		}

		$supplier_id = get_array_value($options, "supplier_id");
		if ($supplier_id) {
			$where .= " AND bmp.supplier_id = $supplier_id";
		}
		
		$category_id = get_array_value($options, "category_id");
		if ($category_id) {
			$where .= " AND bm.category_id = $category_id";
		}

		return $this->db->query(
			"SELECT bmp.*, bmc.title category, bm.name material_name, bm.unit, 
			bm.description,bm.production_name, bs.company_name, 
			bs.currency_symbol currency_symbol, bsc.first_name contact_first_name, 
			bsc.last_name contact_last_name, bsc.email contact_email, bsc.phone contact_phone 
			FROM bom_material_pricings bmp 
			INNER JOIN bom_materials bm ON bm.id = bmp.material_id 
			INNER JOIN bom_suppliers bs ON bs.id = bmp.supplier_id 
			LEFT JOIN bom_material_categories bmc ON bmc.id = bm.category_id 
			LEFT JOIN bom_supplier_contacts bsc ON bsc.supplier_id = bmp.supplier_id AND bsc.is_primary = 1 
			WHERE 1 $where 
			GROUP BY bmp.id "
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
			FROM bom_material_categories bmc 
			WHERE 1 $where "
		);
	}

	function category_create($data)
	{
		$this->db->insert('bom_material_categories', $data);
		return $this->db->insert_id();
	}

	function category_update($data)
	{
		$this->db->replace('bom_material_categories', $data);
		return $data['id'];
	}

	function category_delete($id = 0)
	{
		$this->db->query("UPDATE bom_materials SET category_id = NULL WHERE category_id = $id");
		$this->db->delete('bom_material_categories', ['id' => $id]);
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

	function get_materials_suggestion($keyword = "")
	{
		$materials_table = $this->db->dbprefix('bom_materials');
		$keyword = $this->db->escape_str($keyword);

		$sql = "SELECT $materials_table.`id`,concat($materials_table.`name`,' ',$materials_table.`production_name`) as `text`
			FROM $materials_table
			WHERE $materials_table.`name` LIKE '%$keyword%' OR $materials_table.`production_name` LIKE '%$keyword%'
			LIMIT 10 
        	";
		return $this->db->query($sql)->result();
	}

	function get_materials_request_suggestion($keyword = "")
	{
		$materials_table = $this->db->dbprefix('bom_materials');
		$keyword = $this->db->escape_str($keyword);

		$sql = "SELECT $materials_table.`id`,concat($materials_table.`name`,'  ',$materials_table.`production_name`,' (',FORMAT(SUM(bs.remaining), 'N2'),' ',$materials_table.`unit`,')') as `text` , SUM(bs.material_id) AS 'remaining'
			FROM $materials_table
			LEFT JOIN bom_stocks bs ON $materials_table.id = bs.material_id  
			WHERE bs.remaining > 0 
			GROUP BY bs.material_id
			LIMIT 10 
        	";
		return $this->db->query($sql)->result();
	}

	function get_material_request_info_suggestion($material_id)
	{
		$materials_table = $this->db->dbprefix('bom_materials');
		$material_id = $this->db->escape_str($material_id);

		$sql = "SELECT $materials_table.*, SUM(bs.remaining) AS remaining
			FROM $materials_table
			LEFT JOIN bom_stocks bs ON $materials_table.id = bs.material_id  
			WHERE $materials_table.`id` = '$material_id'
			AND bs.remaining > 0 
			GROUP BY bs.material_id
		";
		// arr($sql);
		$result = $this->db->query($sql);

		if ($result->num_rows()) {
			return $result->row();
		}
	}

	function get_material_info_suggestion($material_id)
	{
		$materials_table = $this->db->dbprefix('bom_materials');
		$material_id = $this->db->escape_str($material_id);

		$sql = "SELECT $materials_table.*
			FROM $materials_table
			WHERE $materials_table.`id` = '$material_id'
        	";
		$result = $this->db->query($sql);

		if ($result->num_rows()) {
			return $result->row();
		}
	}

	function get_low_quality_materials()
	{
		return $this->db->query(
			"SELECT * 
				FROM (
					SELECT SUM(bs.remaining) AS total, bm.id, bm.name, bm.noti_threshold, bm.unit 
					FROM bom_stocks AS bs 
					INNER JOIN bom_materials AS bm ON bm.id = bs.material_id 
					AND bm.noti_threshold IS NOT NULL AND bm.noti_threshold > 0 
					GROUP BY bs.material_id 
				) AS db 
			WHERE db.total < db.noti_threshold"
		);
	}

	function dev2_getCountNameByMaterialName($name)
	{
		$sql = "SELECT `name` FROM `bom_materials` WHERE LOWER(`name`) = '" . strtolower($name) . "'";

		if (isset($name) && strlen($name) > 0) {
			$query = $this->db->query($sql);
			return $query->num_rows();
		} else {
			return 0;
		}
	}

	function dev2_getCountNameByMaterialNameWithId($name, $id) 
	{
		$sql = "SELECT `name` FROM `bom_materials` WHERE 1 AND LOWER(`name`) = '" . strtolower($name) . "' AND `id` <> '" . $id . "'";
		
		if (isset($name) && strlen($name) > 0) {
			$query = $this->db->query($sql);
			return $query->num_rows();
		} else {
			return 0;
		}
	}

	function dev2_getMaterialCateByName($name)
	{
		$rows = 0;
        $sql = "SELECT `id` FROM `bom_material_categories` WHERE 1 AND LOWER(`title`) = '" . strtolower($name) . "'";

        if (isset($name) && strlen($name) > 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
	}

	function dev2_getMaterialCateByNameWithId($name, $id)
	{
		$rows = 0;
        $sql = "SELECT `id` FROM `bom_material_categories` WHERE 1 AND LOWER(`title`) = '" . strtolower($name) . "' AND `id` != '" . $id . "'";

        if (isset($name) && strlen($name) > 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
	}

	function dev2_getCountMaterialCateById($id)
	{
		$rows = 0;
        $sql = "SELECT `id` FROM `bom_materials` WHERE `category_id` = '" . $id . "'";

        if (isset($id) && $id != "0") {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
	}

	function dev2_getCountWarehouseById($id = 0)
	{
		$rows = 0;
		$sql = "SELECT id FROM bom_materials WHERE warehouse_id = {$id}";

		if (isset($id) && $id != 0) {
			$query = $this->db->query($sql);
			$rows = $query->num_rows();
		}
		return $rows;
	}

}