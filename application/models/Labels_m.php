<?php
class Labels_m extends MY_Model {
    private $module;

    function __construct() {
        parent::__construct();
        $this->module["context"] = "note";
        $this->module["tbname"] = "notes";
    }

    function getModuleInfo($module_name){
        if($module_name == "notes"){
            return ["context"=>"note", "tbname"=>"notes"];
        }

        return null;
    }

    function genLabel($module_name, $docId){
        $module = $this->getModuleInfo($module_name);
        if($module == null) return null;

        $label_ids = $this->db->select("GROUP_CONCAT( label_id ) as label_ids")
                                    ->from("label_table")
                                    ->where("doc_id", $docId)
                                    ->where("tbName", $module["tbname"])
                                    ->get()->row()->label_ids;

        $lrows = $this->db->select("id, title as text")
                                    ->from("labels")
                                    ->where("context", $module["context"])
                                    ->where("user_id", $this->login_user->id)
                                    ->where("deleted", 0)
                                    ->get()->result();

        $tag = "<input type='text' name='labels' value='".$label_ids."' class='form-control' placeholder='".lang( 'labels' )."'>";

        $tag .= "<script>";
            $tag .= "$(function() {";
                $tag .= "$('[name=\"labels\"]').select2({multiple: true, data:".json_encode( $lrows )."});";
            $tag .= "});";
        $tag .= "</script>";

        return $tag;
    }    
}
