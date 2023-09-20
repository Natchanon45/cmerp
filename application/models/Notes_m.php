<?php
class Notes_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function getAvailableNoteTypes(){
        $db = $this->db;
        $ci = get_instance();

        $note_types = [];

        if($this->Permission_m->access_note == false || $this->Permission_m->access_note == "assigned_only") return $note_types;

        $db->select("id, title")
                ->from("note_types")
                ->where("deleted", 0);

        if($this->Permission_m->access_note == "specific") $db->where_in("id", explode(",", $this->Permission_m->access_note_specific));
        
        $ntrows = $db->get()->result();

        if(!empty($ntrows)){
            foreach($ntrows as $ntrow){
                $note_types[] = ["id"=>$ntrow->id, "title"=>$ntrow->title];
            }
        }

        return $note_types;   
    }
}
