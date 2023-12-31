<?php

class Withholding_tax_model extends Crud_model
{
    public function indexDataSet()
    {
        $data = array();

        $this->db->select("*");
        $this->db->from("withholding_tax");
        $this->db->where("deleted", 0);

        $query = $this->db->get()->result();
        if (sizeof($query)) {
            foreach ($query as $row) {
                $data[] = array(
                    $row->id,
                    $row->doc_number,
                    $row->payee_name,
                    $row->pnd_type,
                    $row->payer_condition,
                    convertDate($row->doc_date, true),
                    $row->status,
                    $row->deleted
                );
            }
        }

        return $data;
    }
}