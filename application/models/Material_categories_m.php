<?php

class Material_categories_m extends Crud_model
{
    private $table = null;

    public function __construct()
    {
        $this->table = "material_categories";
        parent::__construct($this->table);
    }

    public function dev2_getCategoryInfoById(int $id) : stdClass
    {
        $info = new stdClass();
        $query = $this->db->get_where("material_categories", ["id" => $id])->row();
        if (!empty($query)) {
            $info = $query;
        }

        return $info;
    }

    public function dev2_getCategoryListByType(string $item_type) : array
    {
        $data = array();
        $query = $this->db->get_where("material_categories", ["item_type" => $item_type])->result();
        if (sizeof($query)) {
            $data = $query;
        }

        return $data;
    }

    public function dev2_postCategoryData(array $data) : int
    {
        $post_id = 0;
        if (isset($data["id"]) && !empty($data["id"])) {
            $this->db->where("id", $data["id"]);
            $this->db->update("material_categories", ["title" => $data["title"]]);

            $post_id = $data["id"];
        } else {
            $this->db->insert("material_categories", $data);
            $post_id = $this->db->insert_id();
        }

		return $post_id;
    }

    public function dev2_deleteCategoryById(int $id) : bool
    {
        if ($this->db->delete("material_categories", ["id"=> $id])) {
            return true;
        } else {
            return false;
        }
    }

    public function dev2_getDuplicatedCategoryByNameWithId(int $id, string $name, string $type) : int
	{
		$rows = 0;
		$sql = "SELECT `id` FROM `material_categories` WHERE 1 AND LOWER(`title`) = '" . strtolower($name) . "' AND `id` != '" . $id . "' AND `item_type` = '" . $type . "'";

		if (isset($name) && strlen($name) > 0) {
			$query = $this->db->query($sql);
			$rows = $query->num_rows();
		}
		return $rows;
	}

    public function dev2_getDuplicatedCategoryByName(string $name, string $type) : int
	{
		$rows = 0;
		$sql = "SELECT `id` FROM `material_categories` WHERE 1 AND LOWER(`title`) = '" . strtolower($name) . "' AND `item_type` = '" . $type . "'";

		if (isset($name) && strlen($name) > 0) {
			$query = $this->db->query($sql);
			$rows = $query->num_rows();
		}
		return $rows;
	}

}
