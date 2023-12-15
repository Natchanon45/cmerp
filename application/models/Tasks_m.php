<?php
class Tasks_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function getMasterIndexHTML($row = null, $rows = null){
        $html = [];

        if($row != null){
            return [
                        $row->title,
                        $row->assigned_to,
                        $row->collaborators,
                        $edit_button." ".$delete_button
                    ]; 
        }elseif($rows != null){
            if(!empty($rows)){
                foreach($rows as $row){
                    $edit_button = "<a href='settings/task_list_manage' class='edit' data-post-id=''><i class='fa fa-pencil'></i></a>";
                    $delete_button = "<a href='project_types/modal_form' class='delete' data-id='' data-action-url='".get_uri("project_types/delete")."' data-action='delete'><i class='fa fa-times fa-fw'></i></a>";

                    $html[] = [
                            $row->title,
                            $row->assigned_to,
                            $row->collaborators,
                            $edit_button." ".$delete_button
                        ];    
                }    
            }
        }

        return $html;
    }

    function getMasterRows(){
        $rows = $this->db->select()
                            ->from("project_tasks")
                            ->get()->result();

        if(empty($rows)) return null;

        return $rows;
    }

    function getMasterRow($row_id){
        $db = $this->db;

        $row = $db->select("*")
                    ->from("project_tasks")
                    ->where("id", $row_id)
                    ->get()->row();

        if(empty($row)) return null;
        return $row;        
    }

    function saveMasterRow(){
        $db = $this->db;
        $id = $this->input->post("id");
        $title = $this->input->post("title");
        $description = $this->input->post("description");
        $assigned_to = $this->input->post("assigned_to");
        $collaborators = $this->input->post("collaborators");

        log_message("error", json_encode($collaborators));
        return;
        //return json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));

        if($id != null){
            $db->where("id", $id);
            $db->update("project_tasks", [
                                            "title"=>$title,
                                            "description"=>$description,
                                            "assigned_to"=>$assigned_to,
                                            "collaborators"=>$collaborators
                                        ]);
        }else{
            $db->insert("project_tasks", [
                                            "title"=>$title,
                                            "description"=>$description,
                                            "assigned_to"=>$assigned_to,
                                            "collaborators"=>$collaborators
                                        ]);

            $id = $this->db->insert_id();
        }

        $trow = $this->getMasterRow($id);
        if($trow == null) return ["success"=>false, "message"=>lang('error_occurred')];

        return [
                    "success"=>true,

                ];

        
        echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));

        return $id;


    }

    function deleteMasterRow(){
        $id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        if ($this->Bom_item_model->delete_material_and_sub_items($id)) {
            return array("success" => true, 'message' => lang('record_deleted'));
        } else {
            return array("success" => false, 'message' => lang('record_cannot_be_deleted'));
        }   
    }

    
}