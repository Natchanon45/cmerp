<?php

class Purchase_request_m extends MY_Model
{

    function getDoc($id)
    {
        $db = $this->db;

        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["doc_valid_until_date"] = date("Y-m-d");
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["supplier_id"] = null;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["doc_status"] = null;

        if (!empty($id)) {
            $query = $db->get_where('pr_header', array('deleted_flag' => 0, 'id' => $id))->row();
            if (empty($query)) {
                return $this->data;
            }

            $this->data["doc_id"] = $query->id;
            $this->data["doc_number"] = $query->pr_no;
            $this->data["share_link"] = $query->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$query->sharekey) : null;
            $this->data["doc_date"] = $query->requisition_date;
            $this->data["credit"] = $query->credit;
            $this->data["discount_type"] = $query->discount_type;
            $this->data["discount_percent"] = $query->discount_percent;
            $this->data["discount_amount"] = $query->discount_amount;
            $this->data["vat_inc"] = $query->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($query->vat_percent, 2)."%";
            $this->data["supplier_id"] = $query->supplier_id;
            $this->data["created_by"] = $query->created_by;
            $this->data["created_datetime"] = $query->created_date;
            $this->data["approved_by"] = $query->approved_by;
            $this->data["remark"] = $query->remark;
            $this->data["approved_datetime"] = $query->approved_date;
            $this->data["doc_status"] = $query->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function items()
    {
        $db = $this->db;

        $qrow = $db->select("id, status")
            ->from("pr_header")
            ->where("id", $this->json->doc_id)
            ->where("deleted_flag", 0)
            ->get()->row();

        if (empty($qrow))
            return $this->data;

        $qirows = $db->select("*")
            ->from("pr_detail")
            ->where("pr_id", $this->json->doc_id)
            ->order_by("id", "asc")
            ->get()->result();

        if (empty($qirows)) {
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach ($qirows as $qirow) {
            $item["id"] = $qirow->id;
            $item["product_name"] = $qirow->material_name;
            $item["product_description"] = "";
            $item["quantity"] = $qirow->pr_quantity;
            $item["unit"] = $qirow->pr_unit;
            $item["price"] = number_format($qirow->pr_price, 2);
            $item["total_price"] = number_format($qirow->pr_total_price, 2);

            $items[] = $item;
        }

        $this->data["doc_status"] = $qrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

}

?>