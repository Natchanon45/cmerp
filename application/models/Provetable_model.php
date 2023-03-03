<?php

class Provetable_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'prove_table';
        parent::__construct($this->table);
    }

    function getProve($doc_id, $tbname, $user_id=0) {
        $sql = "SELECT * FROM prove_table WHERE doc_id='{$doc_id}' AND tbName='purchaserequests'";
        if($user_id)
            $sql .= " AND user_id='{$user_id}'";
        $sql .= ";";
        return $this->db->query($sql);
    }

    function getAprover($doc_id, $tbName) {
        $sql = "SELECT u.*,pt.doc_date FROM prove_table as pt LEFT JOIN users as u ON pt.user_id=u.id WHERE pt.doc_id='{$doc_id}' AND pt.tbName='{$tbName}';";
        return $this->db->query($sql)->row();
    }

    function getApprovals($tbName) {
        $sql = "SELECT u.*,pt.doc_date,pt.status_id,pt.doc_id FROM prove_table as pt LEFT JOIN users as u ON pt.user_id=u.id WHERE pt.tbName='{$tbName}';";
        return $this->db->query($sql);
    }
}
