<?php
class Tasks_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function getIndexHTML($row){
        $assigned_to = "";
        $collaborators = "";

        $edit_button = "<a class='edit' data-post-id='".$row->id."' data-act='ajax-modal' data-title='แก้ไขรายการงาน' data-action-url='".get_uri("settings/task_list_manage")."' ><i class='fa fa-pencil'></i></a>";
        $delete_button = "<a class='delete' data-id='".$row->id."' data-action-url='".get_uri("settings/task_list_manage/delete")."' data-action='delete'><i class='fa fa-times fa-fw'></i></a>";

        $urow = $this->Users_m->getRow($row->id, ["first_name", "last_name"]);
        if($urow != null) $assigned_to = $urow->first_name." ".$urow->last_name;

        $cuids = explode(",", $row->collaborators);
        if(count($cuids) >= 0){
            foreach($cuids as $cuid){
                $urow = $this->Users_m->getRow($cuid, ["first_name", "last_name"]);
                if($urow == null) continue;
                $collaborators .= $urow->first_name." ".$urow->last_name.", ";

            }
            
            $collaborators = substr($collaborators, 0, -2);
        }

        return [
                    $row->title,
                    $assigned_to,
                    $collaborators,
                    $edit_button." ".$delete_button
                ]; 
    }

    function getIndexDataset(){
        $rows = $this->Tasks_m->getRows();
        $data_set = [];

        if($rows != null){
            foreach($rows as $row){
                $data_set[] = $this->getIndexHTML($row);
            }
        }

        return $data_set;
    }

    function getRows(){
        $rows = $this->db->select()
                            ->from("project_tasks")
                            ->where("deleted", 0)
                            ->get()->result();

        if(empty($rows)) return null;

        return $rows;
    }

    function getRow($row_id){
        $db = $this->db;

        $row = $db->select("*")
                    ->from("project_tasks")
                    ->where("id", $row_id)
                    ->get()->row();

        if(empty($row)) return null;
        return $row;        
    }

    function saveRow(){
        $db = $this->db;
        $id = $this->input->post("id");
        $title = $this->input->post("title");
        $description = $this->input->post("description");
        $assigned_to = $this->input->post("assigned_to");
        $collaborators = $this->input->post("collaborators");

        validate_submitted_data(["assigned_to" => "required"]);

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

        $row = $this->getRow($id);
        if($row == null) return ["success"=>false, "message"=>lang('error_occurred')];
        return ["success"=>true, "data"=>$this->getIndexHTML($row), "id"=>$id, "message"=>lang('record_saved')];

    }

    function deleteRow(){
        $this->db->where("id", $this->input->post("id"))->update("project_tasks", ["deleted"=>1]);
        
        if($this->db->affected_rows() < 1) return ["success" => false, 'message' => lang('record_cannot_be_deleted')];
        return ["success" => true, 'message' => lang('record_deleted')];   
    }
}