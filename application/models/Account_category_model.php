<?php

class Account_category_model extends Crud_model
{
    function get($id = 0)
    {
        $this->db->select("*");
        $this->db->from("account_category");

        if ($id !== 0) {
            $this->db->where("id", $id);
        }

        $query = $this->db->get();
        return $query->result();
    }

    function set($data = array())
    {
        $insert_id = 0;
        $this->db->trans_start();

        if (sizeof($data)) {
            $this->db->insert("account_category", $data);
        }
        $insert_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            // Something went wrong, so roll back the transaction
            $this->db->trans_rollback();
        } else {
            // Everything is successful, commit the transaction
            $this->db->trans_commit();
        }

        return $insert_id;
    }

    function created_by($id = 0)
    {
        $this->db->select("CONCAT(`first_name`, ' ', `last_name`) AS `full_name`");
        $this->db->from("users");
        $this->db->where("id", $id);

        $query = $this->db->get();
        return $query->row()->full_name;
    }

    function account_by($id = 0)
    {
        $this->db->select("CONCAT(`account_code`, ' - ', `thai_name`) AS `full_name`");
        $this->db->from("account_category");
        $this->db->where("id", $id);

        $query = $this->db->get();
        return $query->row()->full_name;
    }

    function get_list(array $options)
    {
        $result = array();

        $this->db->select("*");
        $this->db->from("account_category");

        // where account type
        if (isset($options["primary_id"]) && !empty($options["primary_id"])) {
            $this->db->where("primary_id", $options["primary_id"]);
        }

        // where sub account type
        if (isset($options["secondary_id"]) && !empty($options["secondary_id"])) {
            $this->db->where("secondary_id", $options["secondary_id"]);
        }

        $query = $this->db->get()->result();
        if (sizeof($query)) {
            $result = $query;
        }

        return $result;
    }

    function get_list_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_category");

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "",
            "text" => "- " . lang("account_category") . " -"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id,
                "text" => $item->account_code . " - " . $item->thai_name
            );
        }

        return $data;
    }

    function get_primary_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_primary");

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("account_type") . " --"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id,
                "text" => $item->thai_name . " (" . $item->account_code . ")"
            );
        }

        return $data;
    }

    function get_secondary_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_secondary");

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("account_sub_type") . " --"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id,
                "text" => $item->thai_name . " (" . $item->account_code . ")"
            );
        }

        return $data;
    }

    // Start new account category
    public function dev2_selectDataListByTableName(string $table) : array
    {
        $data = [];
        $query = $this->db->get($table)->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_selectDataListByColumnIndex(string $table, string $column, int $index) : array
    {
        $data = [];
        $query = $this->db->get_where($table, [$column => $index])->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_getExpenseSecondaryList() : array
    {
        $data = [];
        $query = $this->db->get_where("account_secondary", ["primary_id" => 5])->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_getExpenseCategoryList() : array
    {
        $data = [];
        $query = $this->db->get_where("account_category", ["primary_id" => 5])->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_getIncomeSecondaryList() : array
    {
        $data = [];
        $query = $this->db->get_where("account_secondary", ["primary_id" => 4])->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_getIncomeCategoryList() : array
    {
        $data = [];
        $query = $this->db->get_where("account_category", ["primary_id" => 4])->result();

        if (sizeof($query)) {
            $data = $query;
        }
        return $data;
    }

    public function dev2_getAccountServiceById(int $id) : stdClass
    {
        $data = new stdClass();
        $query = $this->db->get_where("services", ["id" => $id])->row();

        if (!empty($query)) {
            $query->expense_acct_cate_name = $this->dev2_getCategoryTextById($query->expense_acct_cate_id);
            $query->income_acct_cate_name = $this->dev2_getCategoryTextById($query->income_acct_cate_id);

            $data = $query;
        }
        return $data;
    }

    public function dev2_getAccountServicesList($options) : array
    {
        $data = array();

        $this->db->select("*");
        $this->db->from("services");

        // where income account category
        if (isset($options["income_acct_cate_id"]) && !empty($options["income_acct_cate_id"])) {
            $this->db->where("income_acct_cate_id", $options["income_acct_cate_id"]);
        }

        // where expense account category
        if (isset($options["expense_acct_cate_id"]) && !empty($options["expense_acct_cate_id"])) {
            $this->db->where("expense_acct_cate_id", $options["expense_acct_cate_id"]);
        }

        $query = $this->db->get()->result();
        if (sizeof($query)) {
            $data = $query;
        }
        
        return $data;
    }

    public function dev2_postAccountService(array $data, int $id = 0) : int
    {
        $insert_id = 0;

        if (!empty($id) && $id != 0) {
            // Update existing item
            $this->db->where("id", $id);
            $this->db->update("services", $data);
            $insert_id = $id;
        } else {
            // Insert new item
            $this->db->insert("services", $data);
            $insert_id = $this->db->insert_id();
        }
        return $insert_id;
    }

    private function dev2_getCategoryTextById(int $id, string $lang = "TH") : string
    {
        $text = "-";
        $query = $this->db->get_where("account_category", ["id" => $id])->row();

        if (!empty($query)) {
            $text = $query->account_code . " - ";
            if ($lang == "EN") {
                $text .= $query->english_name;
            } else {
                $text .= $query->thai_name;
            }
        }
        return $text;
    }

    public function get_expense_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_category");
        $this->db->where("primary_id", 5);

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("expense_account_category") . " --"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id,
                "text" => $item->thai_name . " (" . $item->account_code . ")"
            );
        }

        return $data;
    }

    public function get_income_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_category");
        $this->db->where("primary_id", 4);

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("income_account_category") . " --"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id,
                "text" => $item->thai_name . " (" . $item->account_code . ")"
            );
        }

        return $data;
    }

}
