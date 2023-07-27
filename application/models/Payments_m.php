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

    function getDoc($docId){
        $db = $this->db;

        
        $this->data["invoice_id"] = null;
        $this->data["invoice_number"] = null;

        $this->data["due_date"] = date("Y-m-d");
        $this->data["invoice_full_payment_amount"] = null;
        $this->data["total_net_amount_to_receive_payment"] = null;
        $this->data["net_await_payment_receive_amount"] = null;

        $this->data["payment_records"] = [];
        
    

        if(!empty($docId)){
            $ivrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($ivrow)) return $this->data;

            $quotation_id = $ivrow->quotation_id;
            $unpaid_amount = 0;

            if($quotation_id != null){
                $qrow = $db->select("total")
                            ->from("quotation")
                            ->where("id", $quotation_id)
                            ->where("deleted", 0)
                            ->get()->row();

                if(empty($qrow)){
                    log_message("error", "SYSERR=>Invoices_m->getDoc:".$db->last_query());
                    return $this->data;
                }

                $billed_amount = 0;
                $quotation_total = $qrow->total;
            }

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($ivrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $ivrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $ivrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["quotation_id"] = $quotation_id;
            $this->data["doc_number"] = $ivrow->doc_number;
            $this->data["share_link"] = $ivrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$ivrow->sharekey) : null;
            $this->data["doc_date"] = $ivrow->doc_date;
            $this->data["credit"] = $ivrow->credit;
            $this->data["due_date"] = $ivrow->due_date;
            $this->data["reference_number"] = $ivrow->reference_number;
            $this->data["discount_type"] = $ivrow->discount_type;
            $this->data["discount_percent"] = $ivrow->discount_percent;
            $this->data["discount_amount"] = $ivrow->discount_amount;
            $this->data["unpaid_amount"] = number_format($unpaid_amount, 2);
            $this->data["vat_inc"] = $ivrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($ivrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $ivrow->wht_inc;
            $this->data["project_id"] = $ivrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $ivrow->remark;
            $this->data["created_by"] = $ivrow->created_by;
            $this->data["created_datetime"] = $ivrow->created_datetime;
            $this->data["approved_by"] = $ivrow->approved_by;
            $this->data["approved_datetime"] = $ivrow->approved_datetime;
            $this->data["doc_status"] = $ivrow->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function getPaymentReceiveInfo(){
        $db = $this->db;
        $this->data["methods"] = $this->getRows();

        

        return $this->data;

    }
}