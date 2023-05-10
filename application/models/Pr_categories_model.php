<?php

class Pr_categories_model extends Crud_model
{

	private $table = null;

	function __construct()
	{
		$this->table = "pr_categories";
		parent::__construct($this->table);
	}

	function get_details($options = array())
	{
		$categories_table = $this->db->dbprefix("pr_categories");
		$users_table = $this->db->dbprefix("users");

		$select = "";
		$where = "";
		$left_join = "";
		$group_by = "";

		$id = get_array_value($options, "id");
		if ($id) {
			$where .= " AND cat.id=$id";
		}

		$created_by = get_array_value($options, "created_by");
		if ($created_by) {
			$where .= " AND cat.created_by=$created_by";
		}

		$count_pr = get_array_value($options, "count_pr");
		if ($count_pr) {
			$select .= ", count(pr.id) as count_pr";
			$left_join .= " LEFT JOIN purchaserequests as pr ON pr.catid=cat.id";
			$where .= "";
			$group_by .= (!$group_by ? " GROUP BY" : "") . " cat.id";
		}

		$sql = "SELECT cat.*, concat(creator.first_name, ' ', creator.last_name) as creator_name $select FROM $categories_table as cat 
		LEFT JOIN $users_table as creator ON creator.id = cat.created_by 
		$left_join 
		WHERE 1 $where 
		$group_by";
		
		return $this->db->query($sql);
	}

	function delete_by_id($id)
	{
		$this->db->delete("pr_categories", array("id" => $id));
		return $this->db->affected_rows();
	}
}