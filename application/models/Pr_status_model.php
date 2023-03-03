<?php

class Pr_status_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'pr_status';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $pr_status_table = $this->db->dbprefix('pr_status');
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $pr_status_table.id=$id";
        }

        $option_where = get_array_value($options, "where");
        if ($option_where) {
            $where .= " AND $option_where";
        }

        $sql = "SELECT $pr_status_table.*, (SELECT COUNT($purchaserequests_table.id) FROM $purchaserequests_table WHERE $purchaserequests_table.deleted=0 AND $purchaserequests_table.status_id=$pr_status_table.id) AS total_purchaserequests
        FROM $pr_status_table
        WHERE $pr_status_table.deleted=0 $where
        ORDER BY $pr_status_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_max_sort_value() {
        $pr_status_table = $this->db->dbprefix('pr_status');

        $sql = "SELECT MAX($pr_status_table.sort) as sort
        FROM $pr_status_table
        WHERE $pr_status_table.deleted=0";
        $result = $this->db->query($sql);
        if ($result->num_rows()) {
            return $result->row()->sort;
        } else {
            return 0;
        }
    }

    function get_first_status() {
        $pr_status_table = $this->db->dbprefix('pr_status');

        $sql = "SELECT $pr_status_table.id AS first_pr_status
        FROM $pr_status_table
        WHERE $pr_status_table.deleted=0
        ORDER BY $pr_status_table.sort ASC
        LIMIT 1";

        return $this->db->query($sql)->row()->first_pr_status;
    }

}
