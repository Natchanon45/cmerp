<?php
class Materialrequest_m extends MY_Model{

    function getDocNumber($doc_id){
        $mrhrow = $this->db->select("doc_no")
                            ->from("materialrequests")
                            ->where("id", $doc_id)
                            ->get()->row();

        if(empty($mrhrow)) return "";
        return $mrhrow->doc_no;
    }

    function row($mrid){
        $mrrow = $this->db->select("*")
                            ->from("materialrequests")
                            ->where("id", $mrid)
                            ->get()->row();

        if(empty($mrrow)) return null;

        return $mrrow;
    }


    function updateStatus($mrid, $update_to_status){
        $mrrow = $this->row($mrid);

        if($this->Permission_m->approve_material_request != true){
            return ["process"=>"fail", "Don't have permission"];
        }

        if(empty($mrrow)){
            return ["process"=>"fail", "Not found MR"];
        }

        if($update_to_status == 3){
            if($mrrow->status_id != 1){
                return ["process"=>"fail", "message"=>"Approval fail"];
            }

            $this->db->where('id', $mrid);
            $this->db->update('materialrequests', ["status_id"=>3]);

            return ["process"=>"success", "message"=>"Successfully Approved"];
        }

        if($update_to_status == 4){
            if($mrrow->status_id != 1){
                return ["process"=>"fail", "message"=>"Disapproval fail"];
            }

            $this->db->where('id', $mrid);
            $this->db->update('materialrequests', ["status_id"=>4]);

            return ["process"=>"success", "message"=>"Successfully Disapproved"];
        }


    }

    
}
