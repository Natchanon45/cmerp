<?php
class Projects_m extends MY_Model {

    function __construct() {
		parent::__construct();
    }

    function getRows(){
        $db = $this->db;

        $prows = $db->select("*")
                    ->from("projects")
                    ->where("deleted", 0);

        //if ($this->input->post("keyword")) $db->like("title", $this->input->post("keyword"));
        //if ($this->input->get("keyword")) $db->like("title", $this->input->get("keyword"));

        $prows = $db->get()->result();

        return $prows;
    }

    function getName($project_id){
        $db = $this->db;
        
        $prow = $db->select("title")
                    ->from("projects")
                    ->where("id", $project_id)
                    ->get()->row();

        if(empty($prow)) return "";

        return $prow->title;
    }

    function delete($project_id){
        $db = $this->db;

        $db->where("id", $project_id);

        $prow = $db->select("*")
                    ->from("projects")
                    ->where("id", $project_id)
                    ->get()->row();

        if(empty($prow)) return false;

        $this->db->trans_begin();

        $mrows = $db->select("*")
                    ->from("materialrequests")
                    ->where("project_id", $project_id)
                    ->where("status_id", 1)
                    ->get()->result();

        //ตรวจสอบเพื่อลบใบเบิกที่ยังไม่อนุมัต
        if(!empty($mrows)){
            foreach($mrows as $mrow){
                $db->where("mr_id", $mrow->id);
                $db->update("mr_items", ["deleted"=>1]);
            }

            $db->where("id", $mrow->id);
            $db->update("materialrequests", ["deleted"=>1]);
        }//mrows

        $bpirows = $db->select("*")
                        ->from("bom_project_items")
                        ->where("project_id", $project_id)
                        ->get()->result();

        if(!empty($bpirows)){
            foreach($bpirows as $bpirow){

                $bpimrows = $db->select("*")
                                ->from("bom_project_item_materials")
                                ->where("project_item_id", $bpirow->id)
                                ->get()->result();

                                //log_message("error", $db->last_query());

                if(!empty($bpimrows)){
                    foreach($bpimrows as $bpimrow){
                        $stock_id = $bpimrow->stock_id;
                        /*$ratio = $bpimrow->ratio;

                        if($stock_id != NULL){
                            $bsrow = $db->select("remaining")
                                        ->from("bom_stocks")
                                        ->where("id", $stock_id)
                                        ->get()->row();

                            $remaining = 0;

                            if(!empty($bsrow)){
                                $remaining = $bsrow->remaining;
                            }

                            $remaining = $remaining + $ratio;

                            $db->where("id", $stock_id);
                            $db->update("bom_stocks", ["remaining"=>$remaining + $ratio]);
                        }*/


                        if($stock_id == null){
                            $db->where("id", $bpimrow->id);
                            $db->delete("bom_project_item_materials");
                        }
                    }
                }//bpimrows
            }
        }//bpirows

        $db->query("UPDATE projects SET deleted=1 WHERE id='".$project_id."'");

        $db->where("id", $project_id);
        $db->update("projects", ["deleted"=>1]);

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return false;
        }
            
        $db->trans_commit();

        $project_files = $db->query("SELECT * FROM project_files WHERE deleted=0 AND project_id='".$project_id."'")->result();
        $project_comments = $db->query("SELECT * FROM project_comments WHERE deleted=0 AND project_id= '".$project_id."'")->result();
        $db->query("UPDATE tasks SET deleted=1 WHERE project_id='".$project_id."'");
        $db->query("UPDATE milestones SET deleted=1 WHERE project_id='".$project_id."'");
        $db->query("UPDATE project_files SET deleted=1 WHERE project_id='".$project_id."'");
        $db->query("UPDATE project_comments SET deleted=1 WHERE project_id='".$project_id."'");
        $db->query("UPDATE activity_logs SET deleted=1 WHERE log_for='project' AND log_for_id='".$project_id."'");
        $db->query("UPDATE notifications SET deleted=1 WHERE project_id='".$project_id."'");

        //delete the comment files from directory
        $comment_file_path = get_setting("timeline_file_path");
        foreach ($project_comments as $comment_info) {
            if ($comment_info->files && $comment_info->files != "a:0:{}") {
                $files = unserialize($comment_info->files);
                foreach ($files as $file) {
                    delete_app_files($comment_file_path, array($file));
                }
            }
        }

        //delete the project files from directory
        $file_path = get_setting("project_file_path") . $project_id . "/";
        foreach ($project_files as $file) {
            delete_app_files($file_path, array(make_array_of_file($file)));
        }

        return true;
        
    }

    function getTypeRow($id){
        $ptrow = $this->db->select("*")
                            ->from("project_types")
                            ->where("id", $id)
                            ->get()->row();

        if(empty($ptrow)) return null;

        return $ptrow;
    }

    function getTypeRows(){
        $ptrows = $this->db->select("*")
                            ->from("project_types")
                            ->where("deleted", 0)
                            ->get()->result();

        return $ptrows;
    }

    function saveType(){
        $db = $this->db;
        $id = $this->input->post("id");
        $title = $this->input->post("title");

        if($id != null){
            $db->where("id", $id);
            $db->update("project_types", ["title"=>$title]);
        }else{
            $db->insert("project_types", ["title"=>$title]);
            $id = $this->db->insert_id();
        }

        return $id;
    }

    function getTaskRow($task_id){
        $db = $this->db;

        $trow = $db->select("*")
                    ->from("tasks")
                    ->where("id", $task_id)
                    ->get()->row();

        if(empty($trow)) return null;
        return $trow;        
    }


}
