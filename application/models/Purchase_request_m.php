<?php

class Purchase_request_m extends MY_Model
{
    private $shareHtmlAddress = "share/purchase_request/html/";

    function getDoc($id)
    {
        $db = $this->db;

        $this->data["doc_date"] = date("Y-m-d");
        $this->data["pr_type"] = null;
        $this->data["credit"] = "0";
        $this->data["doc_valid_until_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
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
            $this->data["reference_number"] = $query->reference_number;
            $this->data["doc_date"] = $query->requisition_date;
            $this->data["pr_type"] = $query->pr_type;
            $this->data["credit"] = $query->credit;
            $this->data["doc_valid_until_date"] = $query->doc_valid_until_date;
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
            $item["product_description"] = $qirow->material_description ? $qirow->material_description : '';
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

    function updateDoc($docId = null)
    {
        $db = $this->db;

        $discount_type = "P";
        $discount_percent = 0;
        $discount_amount = 0;

        $vat_inc = "N";
        $vat_percent = $this->Taxes_m->getVatPercent();
        $vat_value = 0;

        $wht_inc = "N";
        $wht_percent = $this->Taxes_m->getWhtPercent();
        $wht_value = 0;

        if ($docId == null && isset($this->json->doc_id)) {
            $docId = $this->json->doc_id;

            $vat_inc = $this->json->vat_inc == true ? "Y" : "N";
            $wht_inc = $this->json->wht_inc == true ? "Y" : "N";

            $qrow = $db->select("*")
                ->from("pr_header")
                ->where("id", $docId)
                ->where("deleted_flag", 0)
                ->get()->row();

            if (empty($qrow))
                return $this->data;

            $discount_type = $this->json->discount_type;

            if ($discount_type == "P") {
                $discount_percent = getNumber($this->json->discount_percent);
                if ($discount_percent >= 100)
                    $discount_percent = 99.99;
                if ($discount_percent < 0)
                    $discount_percent = 0;
            } else {
                $discount_amount = getNumber($this->json->discount_value);
            }


            if ($vat_inc == "Y")
                $vat_percent = $this->Taxes_m->getVatPercent();
            if ($wht_inc == "Y")
                $wht_percent = getNumber($this->json->wht_percent);

        } else {
            $qrow = $db->select("*")
                ->from("pr_header")
                ->where("id", $docId)
                ->where("deleted_flag", 0)
                ->get()->row();

            if (empty($qrow))
                return $this->data;

            $discount_type = $qrow->discount_type;
            $discount_percent = $qrow->discount_percent;
            $discount_amount = $qrow->discount_amount;


            $vat_inc = $qrow->vat_inc;
            $wht_inc = $qrow->wht_inc;

            if ($vat_inc == "Y")
                $vat_percent = $qrow->vat_percent;
            if ($wht_inc == "Y")
                $wht_percent = $qrow->wht_percent;
        }

        $sub_total_before_discount = $db->select("SUM(pr_total_price) AS SUB_TOTAL")
            ->from("pr_detail")
            ->where("pr_id", $docId)
            ->get()->row()->SUB_TOTAL;

        if ($sub_total_before_discount == null)
            $sub_total_before_discount = 0;
        if ($discount_type == "P") {
            if ($discount_percent > 0) {
                $discount_amount = ($sub_total_before_discount * $discount_percent) / 100;
            }
        } else {
            if ($discount_amount > $sub_total_before_discount)
                $discount_amount = $sub_total_before_discount;
            if ($discount_amount < 0)
                $discount_amount = 0;
        }

        $sub_total = $sub_total_before_discount - $discount_amount;

        if ($vat_inc == "Y")
            $vat_value = ($sub_total * $vat_percent) / 100;
        $total = $sub_total + $vat_value;

        if ($wht_inc == "Y")
            $wht_value = ($sub_total * $wht_percent) / 100;
        $payment_amount = $total - $wht_value;

        $db->where("id", $docId);
        $db->update("pr_header", [
            "sub_total_before_discount" => $sub_total_before_discount,
            "discount_type" => $discount_type,
            "discount_percent" => $discount_percent,
            "discount_amount" => $discount_amount,
            "sub_total" => $sub_total,
            "vat_inc" => $vat_inc,
            "vat_percent" => $vat_percent,
            "vat_value" => $vat_value,
            "total" => $total,
            "wht_inc" => $wht_inc,
            "wht_percent" => $wht_percent,
            "wht_value" => $wht_value,
            "payment_amount" => $payment_amount
        ]);

        $this->data["sub_total_before_discount"] = number_format($sub_total_before_discount, 2);
        $this->data["discount_type"] = $discount_type;
        $this->data["discount_percent"] = number_format($discount_percent, 2);
        $this->data["discount_amount"] = number_format($discount_amount, 2);
        $this->data["sub_total"] = number_format($sub_total, 2);
        $this->data["vat_inc"] = $vat_inc;
        $this->data["vat_percent"] = number_format_drop_zero_decimals($vat_percent, 2);
        $this->data["vat_value"] = number_format($vat_value, 2);
        $this->data["total"] = number_format($total, 2);
        $this->data["total_in_text"] = numberToText($total);
        $this->data["wht_inc"] = $wht_inc;
        $this->data["wht_percent"] = number_format_drop_zero_decimals($wht_percent, 2);
        $this->data["wht_value"] = number_format($wht_value, 2);
        $this->data["payment_amount"] = number_format($payment_amount, 2);

        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        return $this->data;
    }

    function deleteItem()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("pr_no", $docId);
        $db->delete("pr_detail");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function genShareKey()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;
        $genKey = $this->json->gen_key;

        $sharekey = null;
        $sharekey_by = null;

        if ($genKey == true) {
            $sharekey = "";
            $sharekey_by = $this->login_user->id;

            while (true) {
                $sharekey = uniqid();
                $db->where("sharekey", $sharekey);
                if ($db->count_all_results("pr_header") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress . "th/" . $sharekey);
        }

        $db->where("id", $docId);
        $db->update("pr_header", ["sharekey" => $sharekey, "sharekey_by" => $sharekey_by]);

        return $this->data;
    }

    function undoDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("pr_header", ["deleted_flag" => 0]);

        $qrow = $db->select("*")
            ->from("pr_header")
            ->where("id", $docId)
            ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($qrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function getIndexDataSetHTML($item) {
        $status = '<select class="dropdown_status select-status" data-doc_id="' . $item->id . '">';

        if ($item->status == 1) {
            $status .= '
                <option selected>' . lang('pr_pending') . '</option>
                <option value="2">' . lang('pr_approved') . '</option>
                <option value="3">' . lang('pr_rejected') . '</option>
            ';
        }

        if ($item->status == 2) {
            $status .= '
                <option selected>' . lang('pr_approved') . '</option>
            ';
        }

        if ($item->status == 3) {
            $status .= '
                <option selected>' . lang('pr_rejected') . '</option>
            ';
        }

        $status .= '</select>';

        $request_by = '-';
        if ($item->created_by) {
            $user = $this->Users_model->getUserById($item->created_by);

            $url =  get_avatar($user->image);
            $span = '<span class="avatar avatar-xs mr10"><img src="' . $url . '" alt=""></span>' . $user->first_name . ' ' . $user->last_name;
            $request_by = get_team_member_profile_link($user->id, $span);
        }
        
        $data = array(
            "<a href='" . get_uri('purchase_request/view/' . $item->id) . "'>" . convertDate($item->requisition_date, true) . "</a>",
            "<a href='" . get_uri('purchase_request/view/' . $item->id) . "'>" . $item->pr_no . "</a>",
            $item->pr_type ? $this->dev2_getPrTypeById($item->pr_type) : '-',
            "<a href='" . get_uri('stock/supplier_view/' . $item->supplier_id) . "'>" . mb_strimwidth($item->supplier_name, 0, 60, '...') . "</a>",
            $request_by,
            $status,
            "<a data-post-id='" . $item->id . "' data-action-url='" . get_uri('purchase_request/addedit') . "' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
        );

        return $data;
    }

    function dev2_getPrTypeById($id)
    {
        $query = $this->db->get_where('pr_type', array('id' => $id))->row();
        return lang($query->keyword);
    }

    function deleteDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $qrow = $db->select("status")
            ->from("pr_header")
            ->where("id", $docId)
            ->get()->row();

        if (empty($qrow))
            return $this->data;

        $bnrow = $db->select("*")
            ->from("po_header")
            ->where("pr_id", $docId)
            ->where("deleted", 0)
            ->get()->row();

        if (!empty($bnrow)) {
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบใบขอซื้อได้ เนื่องจากถูกอ้างอิงในใบสั่งซื้อแล้ว";
            return $this->data;
        }

        if ($qrow->status != "1") {
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบใบขอซื้อได้ เนื่องจากมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("pr_header", ["deleted_flag" => 1]);

        $data["success"] = true;
        $data["message"] = lang('record_deleted');

        return $data;
    }

    function getEdoc($docId = null, $sharekey = null)
    {
        $db = $this->db;
        $ci = get_instance();

        if ($docId != null && $sharekey == null) {
            $docId = base64_decode($docId);
            list($docId, $docNumber) = explode(":", $docId);
            $db->where("id", $docId);
            $db->where("pr_no", $docNumber);
        } elseif ($docId == null && $sharekey != null) {
            $db->where("sharekey", $sharekey);
        } else {
            return $this->data;
        }

        $db->where("deleted_flag", 0);

        $qrow = $db->select("*")
            ->from("pr_header")
            ->get()->row();

        if (empty($qrow))
            return $this->data;

        $docId = $qrow->id;

        $qirows = $db->select("*")
            ->from("pr_detail")
            ->where("pr_id", $docId)
            ->order_by("item_no", "asc")
            ->get()->result();

        $supplier_id = $qrow->supplier_id;
        $created_by = $qrow->created_by;

        $this->data["buyer"] = $ci->Users_m->getInfo($created_by);

        $this->data["seller"] = $ci->Bom_suppliers_model->getInfo($supplier_id);
        $this->data["seller_contact"] = $ci->Bom_suppliers_model->getContactInfo($supplier_id);

        $this->data["doc_number"] = $qrow->pr_no;
        $this->data["doc_date"] = $qrow->requisition_date;
        $this->data["credit"] = $qrow->credit;
        $this->data["doc_valid_until_date"] = $qrow->doc_valid_until_date;
        $this->data["reference_number"] = $qrow->reference_number;
        $this->data["remark"] = $qrow->remark;

        $this->data["sub_total_before_discount"] = $qrow->sub_total_before_discount;

        $this->data["discount_type"] = $qrow->discount_type;
        $this->data["discount_percent"] = $qrow->discount_percent;
        $this->data["discount_amount"] = $qrow->discount_amount;

        $this->data["sub_total"] = $qrow->sub_total;

        $this->data["vat_inc"] = $qrow->vat_inc;
        $this->data["vat_percent"] = $qrow->vat_percent;
        $this->data["vat_value"] = $qrow->vat_value;
        $this->data["total"] = $qrow->total;
        $this->data["total_in_text"] = numberToText($qrow->total);
        $this->data["wht_inc"] = $qrow->wht_inc;
        $this->data["wht_percent"] = $qrow->wht_percent;
        $this->data["wht_value"] = $qrow->wht_value;
        $this->data["payment_amount"] = $qrow->payment_amount;

        $this->data["sharekey_by"] = $qrow->sharekey_by;
        $this->data["approved_by"] = $qrow->approved_by;
        $this->data["approved_datetime"] = $qrow->approved_date;
        $this->data["doc_status"] = $qrow->status;

        $this->data["doc"] = $qrow;
        $this->data["items"] = $qirows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function validateItem()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->form_validation->set_rules([
            [
                "field" => "quantity",
                'label' => '',
                'rules' => 'required'
            ]
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->data["status"] = "validate";
            if (form_error('quantity') != null) $this->data["messages"]["quantity"] = form_error('quantity');
        }
    }

    function saveItem()
    {
        $db = $this->db;
        $docId = isset($this->json->doc_id) ? $this->json->doc_id : null;

        $qrow = $db->select("id")
            ->from("pr_header")
            ->where("id", $docId)
            ->where("deleted_flag", 0)
            ->get()->row();

        if (empty($qrow))
            return $this->data;

        $this->validateItem();
        if ($this->data["status"] == "validate")
            return $this->data;

        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id == "" ? null : $this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), $this->Settings_m->getDecimalPlacesNumber());
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 2);
        $total_price = round($price * $quantity, 2);

        $fdata = [
            "pr_id" => $docId,
            "material_id" => $product_id,
            "material_name" => $product_name,
            "material_description" => $product_description,
            "pr_quantity" => $quantity,
            "pr_unit" => $unit,
            "pr_price" => $price,
            "pr_total_price" => $total_price,
        ];

        $db->trans_begin();

        if (empty($itemId)) {
            $db->where("pr_id", $docId);
            $total_items = $db->count_all_results("pr_detail");
            $fdata["pr_id"] = $docId;
            $fdata["item_no"] = $total_items + 1;
            $db->insert("pr_detail", $fdata);
        } else {
            $db->where("id", $itemId);
            $db->where("pr_id", $docId);
            $db->update("pr_detail", $fdata);
        }


        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();
        } else {
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("purchase_request/view/" . $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function saveDoc()
    {
        $db = $this->db;

        $this->validateDoc();
        if ($this->data["status"] == "validate")
            return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $doc_type = $this->json->doc_type;
        $credit = intval($this->json->credit) < 0 ? 0 : intval($this->json->credit);
        $doc_valid_until_date = date('Y-m-d', strtotime($doc_date . " + " . $credit . " days"));
        $reference_number = $this->json->reference_number;
        $supplier_id = $this->json->supplier_id;
        $remark = $this->json->remark;

        if ($docId != "") {
            $qrow = $db->select("status")
                ->from("pr_header")
                ->where("id", $docId)
                ->where("deleted_flag", 0)
                ->get()->row();

            if (empty($qrow)) {
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if ($qrow->status != "1") {
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted_flag", 0);
            $db->update("pr_header", [
                "requisition_date" => $doc_date,
                "pr_type" => $doc_type,
                "credit" => $credit,
                "doc_valid_until_date" => $doc_valid_until_date,
                "reference_number" => $reference_number,
                "supplier_id" => $supplier_id,
                "remark" => $remark
            ]);
        } else {
            $doc_number = $this->getNewDocNumber();

            $db->insert("pr_header", [
                "pr_no" => $doc_number,
                "requisition_date" => $doc_date,
                "pr_type" => $doc_type,
                "credit" => $credit,
                "doc_valid_until_date" => $doc_valid_until_date,
                "reference_number" => $reference_number,
                "vat_inc" => "N",
                "supplier_id" => $supplier_id,
                "remark" => $remark,
                "created_by" => $this->login_user->id,
                "status" => "1"
            ]);

            $docId = $db->insert_id();
        }

        $this->data["target"] = get_uri("purchase_request/view/" . $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function getNewDocNumber()
    {
        $param = array(
            'prefix' => 'PR',
            'LPAD' => 5,
            'column' => 'pr_no',
            'table' => 'pr_header'
        );

        return $this->Db_model->genDocNo($param);
    }

    function validateDoc()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->form_validation->set_rules([
            [
                "field" => "doc_date",
                'label' => '',
                'rules' => 'required'
            ],
            [
                "field" => "doc_valid_until_date",
                'label' => '',
                'rules' => 'required'
            ]
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->data["status"] = "validate";
            if (form_error('doc_date') != null)
                $this->data["messages"]["doc_date"] = form_error('doc_date');
            if (form_error('doc_valid_until_date') != null)
                $this->data["messages"]["doc_valid_until_date"] = form_error('doc_valid_until_date');
        }
    }

    function updateStatus()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $qrow = $db->select("*")
            ->from("pr_header")
            ->where("id", $docId)
            ->where("deleted_flag", 0)
            ->get()->row();

        if (empty($qrow))
            return $this->data;

        $quotation_id = $this->data["doc_id"] = $docId;
        $quotation_number = $qrow->pr_no;
        $currentStatus = $qrow->status;

        $quotation_sub_total_before_discount = $qrow->sub_total_before_discount;

        $quotation_discount_type = $qrow->discount_type;
        $quotation_discount_percent = $qrow->discount_percent;
        $quotation_discount_amount = $qrow->discount_amount;

        $quotation_sub_total = $qrow->sub_total;

        $quotation_vat_inc = $qrow->vat_inc;
        $quotation_vat_percent = $qrow->vat_percent;
        $quotation_vat_value = $qrow->vat_value;

        $quotation_wht_inc = $qrow->wht_inc;
        $quotation_wht_percent = $qrow->wht_percent;
        $quotation_wht_value = $qrow->wht_value;

        $quotation_total = $qrow->total;
        $quotation_payment_amount = $qrow->payment_amount;

        if ($qrow->status == $updateStatusTo && $updateStatusTo != "P") {
            $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $this->db->trans_begin();

        if ($updateStatusTo == "2") { // Approved
            if ($currentStatus == "3") {
                $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            $db->where("id", $quotation_id);
            $db->update("pr_header", [
                "approved_by" => $this->login_user->id,
                "approved_date" => date("Y-m-d H:i:s"),
                "status" => "2"
            ]);
        } elseif ($updateStatusTo == "3") { // Rejected
            $db->where("id", $quotation_id);
            $db->update("pr_header", [
                "status" => "3",
                "rejected_by" => $this->login_user->id,
                "rejected_date" => date("Y-m-d H:i:s")
            ]);
        }

        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $db->trans_commit();

        if (isset($this->data["task"]))
            return $this->data;

        $qrow = $db->select("*")
            ->from("pr_header")
            ->where("id", $docId)
            ->where("deleted_flag", 0)
            ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $qrow = $db->select("id")
                        ->from("pr_header")
                        ->where("id", $docId)
                        ->where("deleted_flag", 0)
                        ->get()->row();

        if(empty($qrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if(!empty($itemId)){
            $qirow = $db->select("*")
                        ->from("pr_detail")
                        ->where("id", $itemId)
                        ->where("pr_id", $docId)
                        ->get()->row();

            if(empty($qirow)) return $this->data;

            $this->data["item_id"] = $qirow->id;
            $this->data["product_id"] = $qirow->material_id;
            $this->data["product_name"] = $qirow->material_name;
            $this->data["product_description"] = $qirow->material_description;
            $this->data["quantity"] = number_format($qirow->pr_quantity, $this->Settings_m->getDecimalPlacesNumber());
            $this->data["unit"] = $qirow->pr_unit;
            $this->data["price"] = number_format($qirow->pr_price, 2);
            $this->data["total_price"] = number_format($qirow->pr_total_price, 2);
        }

        $this->data["status"] = "success";

        return $this->data;
    }

}

?>