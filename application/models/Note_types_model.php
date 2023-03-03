<?php

class Note_types_model extends Crud_model {
    private $table = null;

    function __construct() {
        $this->table = 'note_types';
        parent::__construct($this->table);
    }

    function row($note_id){
        $ntrow = $this->db->select("*")
                            ->from("note_types")
                            ->where("id", $note_id)
                            ->get()->row();

        if(empty($ntrow)) return null;

        return $ntrow;
    }

    function get_details($options = array()) {
        $note_types_table = $this->db->dbprefix('note_types');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where = " AND $note_types_table.id=$id";
        }

        $sql = "SELECT $note_types_table.*
        FROM $note_types_table
        WHERE $note_types_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
