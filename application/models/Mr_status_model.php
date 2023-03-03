<?php

class Mr_status_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'mr_status';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $mr_status_table = $this->db->dbprefix('mr_status');
        $materialrequests_table = $this->db->dbprefix('materialrequests');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $mr_status_table.id=$id";
        }

        $option_where = get_array_value($options, "where");
        if ($option_where) {
            $where .= " AND $option_where";
        }

        $sql = "SELECT $mr_status_table.*, (SELECT COUNT($materialrequests_table.id) FROM $materialrequests_table WHERE $materialrequests_table.deleted=0 AND $materialrequests_table.status_id=$mr_status_table.id) AS total_purchaserequests
        FROM $mr_status_table
        WHERE $mr_status_table.deleted=0 $where
        ORDER BY $mr_status_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_max_sort_value() {
        $mr_status_table = $this->db->dbprefix('mr_status');

        $sql = "SELECT MAX($mr_status_table.sort) as sort
        FROM $mr_status_table
        WHERE $mr_status_table.deleted=0";
        $result = $this->db->query($sql);
        if ($result->num_rows()) {
            return $result->row()->sort;
        } else {
            return 0;
        }
    }

    function get_first_status() {
        $mr_status_table = $this->db->dbprefix('mr_status');

        $sql = "SELECT $mr_status_table.id AS first_mr_status
        FROM $mr_status_table
        WHERE $mr_status_table.deleted=0
        ORDER BY $mr_status_table.sort ASC
        LIMIT 1";

        return $this->db->query($sql)->row()->first_mr_status;
    }

}
