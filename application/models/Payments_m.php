<?php

class Payments_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function getRows(){
        $db = $this->db;

        $pmrows = $db->select("*")
                        ->from("payment_methods")
                        ->where("deleted", 0)
                        ->order_by("id", "asc")
                        ->get()->result();

        return $pmrows;
    }

    function getPaymentMethodName($payment_method_id){
        $db = $this->db;

        if($payment_method_id == null) return "ไม่ระบุช่องทางการจ่ายเงิน";

        $pmrow = $db->select("title")
                        ->from("payment_methods")
                        ->where("id", $payment_method_id)
                        ->get()->row();

        if(empty($pmrow)) return "";

        return $pmrow->title;
    }
}