<?php

class Notes_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'notes';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $notes_table = $this->db->dbprefix('notes');
        $users_table = $this->db->dbprefix('users');

        $where = "";

        $id = get_array_value( $options, "id" );
        if ( $id ) {
            $where .= " AND $notes_table.id=$id";
        }

        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $notes_table.created_by=$created_by";
        }

        $note_type_ids = get_array_value($options, "note_type_ids");
        if ($note_type_ids) {
            $where .= " AND $notes_table.note_type_id IN (".$note_type_ids.")";
        }

        $note_type_id = get_array_value($options, "note_type_id");
        if ($note_type_id) {
            $where .= " AND ($notes_table.note_type_id='".$note_type_id."' OR $notes_table.note_type_id=0)";
        }

        $my_notes = get_array_value($options, "my_notes");
        if ($my_notes) {
            $where .= " AND $notes_table.user_id=0 AND $notes_table.client_id=0 "; //don't include client's and team member's notes
        }

        $select_labels_data_query = $this->get_labels_data_query();
      // 
        $sql = "
			SELECT 
				$notes_table.*, 
				CONCAT( $users_table.first_name, ' ', $users_table.last_name ) AS created_by_user_name, $select_labels_data_query
			FROM $notes_table
			LEFT JOIN $users_table ON $users_table.id = $notes_table.created_by
			WHERE (($notes_table.project_id=0 OR $notes_table.is_public) AND $notes_table.deleted=0) $where
			[HAVING]
		";
		
		$replace = array();
		
		if( !empty( $options['label'] ) ) {
			
			$replace['HAVING'][] = "labels_list LIKE '%". $options['label'] ."%'";
		}
		
		$sql = genCond_( $sql, $replace );
        
        return $this->db->query( $sql );
    }

    function get_note_in_project($project_id, $note_id = null){
        $db = $this->db;

        $select_labels_data_query = $this->get_labels_data_query();
        
        $sql = $db->select("notes.*, concat(first_name,' ',last_name) AS created_by_user_name, notes.deleted, notes.created_at, ".$select_labels_data_query)
                    ->from("notes")
                    ->join("users", "users.id = notes.created_by")
                    ->where("project_id", $project_id)
                    ->where("notes.deleted", 0);

        if($note_id != null){
            $db->where("notes.id", $note_id);
        }

        $query = $sql->get();

        return $query;
    }

    function has_permission($project_id){
        $db = $this->db;

        if($this->login_user->is_admin == 1) return true;

        $db->where("user_id", $this->login_user->id);
        $db->where("deleted", 0);
        $db->where("project_id", $project_id);
        $user_in_project = $db->count_all_results("project_members");

        if($user_in_project > 0) return true;
        return false;
    }

    function get_label_suggestions( $user_id ) {
		
        $notes_table = $this->db->dbprefix('notes');
        $sql = "
			SELECT 
				GROUP_CONCAT( labels ) as label_groups
			FROM $notes_table
			WHERE $notes_table.deleted=0 
			AND $notes_table.created_by = $user_id";
        return $this->db->query($sql)->row()->label_groups;
    }

}
