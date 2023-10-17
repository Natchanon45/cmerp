<?php
class Payment_voucher_m extends MY_Model
{
    private $code = "PV";

    private $table_header = "pv_header";

    private $table_detail = "pv_detail";

    private $shareHtmlAddress = "share/payment_voucher/html/";

    function __construct()
    {
        parent::__construct();
    }

    function getCode()
    {
        return $this->code;
    }

    function getNewDocNumber()
    {
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);

        $running_number = $this->db->get($this->table_header)->num_rows() + 1;
        $doc_number = $this->getCode() . date("Ym") . sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code)
    {
        if ($status_code == "W") {
            return "รออนุมัติ";
        }
    }

    function getIndexDataSetHTML($item)
    {
        $doc_status = '<select class="dropdown_status select-status" data-doc_id="' . $item->id . '">';

        if ($item->status == "W") {
            $doc_status .= '
                <option value="W" selected>' . lang('pr_pending') . '</option>
                <option value="A">' . lang('pr_approved') . '</option>
                <option value="X">' . lang('cancel') . '</option>
            ';
        }
        
        if ($item->status == "A") {
            $doc_status .= '<option value="A" selected>' . lang('pr_approved') . '</option>';

            if ($item->pay_status == "N") {
                $doc_status .= '<option value="X">' . lang('cancel') . '</option>';
            }
        }
        
        if ($item->status == "R") {
            $doc_status .= '
                <option value="R" selected>' . lang('pr_rejected') . '</option>
            ';
        }

        $doc_status .= '</select>';

        $request_by = '-';
        if ($item->created_by) {
            $user = $this->Users_model->getUserById($item->created_by);
            $url = get_avatar($user->image);
            $span = '<span class="avatar avatar-xs mr10"><img src="' . $url . '" alt=""></span>' . $user->first_name . ' ' . $user->last_name;
            $request_by = get_team_member_profile_link($user->id, $span);
        }

        $supplier_name = '-';
        if ($item->supplier_id) {
            $supplier = $this->Bom_suppliers_model->dev2_getSupplierNameById($item->supplier_id);
            $supplier_name = "<a href='" . get_uri('stock/supplier_view/' . $item->supplier_id) . "'>" . mb_strimwidth($supplier, 0, 55, '...') . "</a>";
        }

        $data = [
            "<a href='" . get_uri('payment_voucher/view/' . $item->id) . "'>" . convertDate($item->doc_date, true) . "</a>",
            "<a href='" . get_uri('payment_voucher/view/' . $item->id) . "'>" . $item->doc_number . "</a>",
            $item->reference_number,
            $supplier_name,
            $request_by,
            number_format($item->total, 2),
            $doc_status,
            "<a data-post-id='" . $item->id . "' data-action-url='" . get_uri("payment_voucher/addedit") . "' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
        ];

        return $data;
    }

    function indexDataSet()
    {
        $db = $this->db;

        $db->select("*")->from($this->table_header);

        if ($this->input->post("status") != null) {
            $db->where("status", $this->input->post("status"));
        }

        if ($this->input->post("start_date") != null && $this->input->post("end_date")) {
            $db->where("doc_date >=", $this->input->post("start_date"));
            $db->where("doc_date <=", $this->input->post("end_date"));
        }

        if ($this->input->post("supplier_id") != null) {
            $db->where("supplier_id", $this->input->post("supplier_id"));
        }

        $db->where("deleted", 0);

        $pvrows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach ($pvrows as $pvrow) {
            $dataset[] = $this->getIndexDataSetHTML($pvrow);
        }

        return $dataset;
    }

    function getDoc($docId)
    {
        $db = $this->db;

        $this->data["po_id"] = null;
        $this->data["doc_number"] = null;
        $this->data["po_type"] = null;
        $this->data["sharekey"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = null;
        $this->data["project_id"] = null;
        $this->data["supplier_id"] = null;
        $this->data["sub_total_before_discount"] = 0;
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["sub_total"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["vat_percent"] = 0;
        $this->data["vat_value"] = 0;
        $this->data["total"] = 0;
        $this->data["wht_inc"] = "N";
        $this->data["wht_percent"] = 0;
        $this->data["wht_value"] = 0;
        $this->data["payment_amount"] = 0;
        $this->data["pay_amount"] = 0;
        $this->data["pay_status"] = "N";
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["doc_status"] = NULL;

        if (!empty($docId)) {
            $pvrow = $db->select("*")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

            if (empty($pvrow)) return $this->data;

            $this->data["doc_id"] = $docId;
            $this->data["po_id"] = $pvrow->po_id;
            $this->data["po_type"] = $pvrow->po_type;
            $this->data["doc_number"] = $pvrow->doc_number;
            $this->data["share_link"] = $pvrow->sharekey != null ? get_uri($this->shareHtmlAddress . "th/" . $pvrow->sharekey) : null;
            $this->data["doc_date"] = $pvrow->doc_date;
            $this->data["credit"] = $pvrow->credit;
            $this->data["due_date"] = $pvrow->due_date;
            $this->data["reference_number"] = $pvrow->reference_number;
            $this->data["discount_type"] = $pvrow->discount_type;
            $this->data["discount_percent"] = $pvrow->discount_percent;
            $this->data["discount_amount"] = $pvrow->discount_amount;
            $this->data["pay_amount"] = $pvrow->pay_amount;
            $this->data["pay_status"] = $pvrow->pay_status;
            $this->data["vat_inc"] = $pvrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($pvrow->vat_percent, 2) . "%";
            $this->data["wht_inc"] = $pvrow->wht_inc;
            $this->data["wht_percent"] = $pvrow->wht_percent;
            $this->data["project_id"] = $pvrow->project_id;
            $this->data["supplier_id"] = $pvrow->supplier_id;
            $this->data["remark"] = $pvrow->remark;
            $this->data["created_by"] = $pvrow->created_by;
            $this->data["created_datetime"] = $pvrow->created_datetime;
            $this->data["approved_by"] = $pvrow->approved_by;
            $this->data["approved_datetime"] = $pvrow->approved_datetime;
            $this->data["doc_status"] = $pvrow->status;
        }

        $this->data["status"] = "success";
        return $this->data;
    }

    function getEdoc($docId = null, $sharekey = null)
    {
        $db = $this->db;
        $ci = get_instance();

        if ($docId != null && $sharekey == null) {
            $docId = base64_decode($docId);
            list($docId, $docNumber) = explode(":", $docId);
            $db->where("id", $docId);
            $db->where("doc_number", $docNumber);
        } elseif ($docId == null && $sharekey != null) {
            $db->where("sharekey", $sharekey);
        } else {
            return $this->data;
        }

        $db->where("deleted", 0);

        $pvrow = $db->select("*")->from($this->table_header)->get()->row();

        if (empty($pvrow)) return $this->data;

        $docId = $pvrow->id;
        $qirows = $db->select("*")->from($this->table_detail)->where("pv_id", $docId)->order_by("sort", "asc")->get()->result();
        $pay_qirows = $db->select("*")->from("goods_receipt_payment")->where("pv_id", $docId)->where('receipt_flag', 1)->order_by("id", "asc")->get()->result();

        if (sizeof($pay_qirows)) {
            foreach ($pay_qirows as $item) {
                $item->date_output = convertDate($item->date, true);
                $item->number_format = number_format($item->amount, 2);
                $item->currency_format = to_currency($item->amount);
            }
        }

        $supplier_id = $pvrow->supplier_id;

        $this->data["buyer"] = $ci->Users_m->getInfo($pvrow->created_by);
        $this->data["seller"] = $ci->Bom_suppliers_model->getInfo($supplier_id);
        $this->data["seller_contact"] = $ci->Bom_suppliers_model->getContactInfo($supplier_id);

        $this->data["doc_number"] = $pvrow->doc_number;
        $this->data["doc_date"] = $pvrow->doc_date;
        $this->data["credit"] = $pvrow->credit;
        $this->data["due_date"] = $pvrow->due_date;
        $this->data["reference_number"] = $pvrow->reference_number;
        $this->data["remark"] = $pvrow->remark;
        $this->data["sub_total_before_discount"] = $pvrow->sub_total_before_discount;
        $this->data["discount_type"] = $pvrow->discount_type;
        $this->data["discount_percent"] = $pvrow->discount_percent;
        $this->data["discount_amount"] = $pvrow->discount_amount;
        $this->data["sub_total"] = $pvrow->sub_total;
        $this->data["vat_inc"] = $pvrow->vat_inc;
        $this->data["vat_percent"] = $pvrow->vat_percent;
        $this->data["vat_value"] = $pvrow->vat_value;
        $this->data["total"] = $pvrow->total;
        $this->data["total_in_text"] = numberToText($pvrow->total);
        $this->data["wht_inc"] = $pvrow->wht_inc;
        $this->data["wht_percent"] = $pvrow->wht_percent;
        $this->data["wht_value"] = $pvrow->wht_value;
        $this->data["payment_amount"] = $pvrow->payment_amount;
        $this->data["sharekey_by"] = $pvrow->sharekey_by;
        $this->data["created_by"] = $ci->Users_m->getInfo($pvrow->created_by);
        $this->data["created_datetime"] = $pvrow->approved_datetime;
        $this->data["approved_by"] = $ci->Users_m->getInfo($pvrow->approved_by);
        $this->data["approved_datetime"] = $pvrow->approved_datetime;
        $this->data["doc_status"] = $pvrow->status;
        $this->data["doc"] = $pvrow;
        $this->data["items"] = $qirows;
        $this->data["payments"] = $pay_qirows;
        $this->data["status"] = "success";
        $this->data["message"] = "ok";

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

            $pvrow = $db->select("*")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

            if (empty($pvrow)) return $this->data;

            $discount_type = $this->json->discount_type;

            if ($discount_type == "P") {
                $discount_percent = getNumber($this->json->discount_percent);
                if ($discount_percent >= 100) $discount_percent = 99.99;
                if ($discount_percent < 0) $discount_percent = 0;
            } else {
                $discount_amount = getNumber($this->json->discount_value);
            }

            if ($vat_inc == "Y") $vat_percent = $this->Taxes_m->getVatPercent();
            if ($wht_inc == "Y") $wht_percent = getNumber($this->json->wht_percent);
        } else {
            $pvrow = $db->select("*")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

            if (empty($pvrow)) return $this->data;

            $discount_type = $pvrow->discount_type;
            $discount_percent = $pvrow->discount_percent;
            $discount_amount = $pvrow->discount_amount;

            $vat_inc = $pvrow->vat_inc;
            $wht_inc = $pvrow->wht_inc;

            if ($vat_inc == "Y") $vat_percent = $pvrow->vat_percent;
            if ($wht_inc == "Y") $wht_percent = $pvrow->wht_percent;
        }

        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")->from($this->table_detail)->where("pv_id", $docId)->get()->row()->SUB_TOTAL;

        if ($sub_total_before_discount == null) $sub_total_before_discount = 0;
        if ($discount_type == "P") {
            if ($discount_percent > 0) {
                $discount_amount = ($sub_total_before_discount * $discount_percent) / 100;
            }
        } else {
            if ($discount_amount > $sub_total_before_discount) $discount_amount = $sub_total_before_discount;
            if ($discount_amount < 0) $discount_amount = 0;
        }

        $sub_total = $sub_total_before_discount - $discount_amount;

        if ($vat_inc == "Y") $vat_value = ($sub_total * $vat_percent) / 100;
        $total = $sub_total + $vat_value;

        if ($wht_inc == "Y") $wht_value = ($sub_total * $wht_percent) / 100;
        $payment_amount = $total - $wht_value;

        $db->where("id", $docId);
        $db->update($this->table_header, [
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
        $this->data["wht_percent"] = number_format_drop_zero_decimals($wht_percent, 0);
        $this->data["wht_value"] = number_format($wht_value, 2);
        $this->data["payment_amount"] = number_format($payment_amount, 2);

        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        return $this->data;
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

            if (form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
            if (form_error('doc_valid_until_date') != null) $this->data["messages"]["doc_valid_until_date"] = form_error('doc_valid_until_date');
        }
    }

    function saveDoc()
    {
        $db = $this->db;

        $this->validateDoc();
        if ($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $reference_number = $this->json->reference_number;
        $supplier_id = $this->json->supplier_id;
        $project_id = $this->json->project_id;
        $remark = $this->json->remark;

        if ($docId != "") {
            $pvrow = $db->select("status")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

            if (empty($pvrow)) {
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if ($pvrow->status != "W") {
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update($this->table_header, [
                "doc_date" => $doc_date,
                "reference_number" => $reference_number,
                "supplier_id" => $supplier_id,
                "project_id" => $project_id,
                "remark" => $remark
            ]);
        } else {
            $doc_number = $this->getNewDocNumber();

            $db->insert($this->table_header, [
                "doc_number" => $doc_number,
                "doc_date" => $doc_date,
                "reference_number" => $reference_number,
                "vat_inc" => "N",
                "supplier_id" => $supplier_id,
                "project_id" => $project_id,
                "remark" => $remark,
                "created_by" => $this->login_user->id,
                "created_datetime" => date("Y-m-d H:i:s"),
                "status" => "W"
            ]);

            $docId = $db->insert_id();
        }

        $this->data["target"] = get_uri("payment_voucher/view/" . $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $pvrow = $db->select("status")->from($this->table_header)->where("id", $docId)->get()->row();

        if (empty($pvrow)) return $this->data;

        if ($pvrow->status != "W") {
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update($this->table_header, ["deleted" => 1]);

        $data["success"] = true;
        $data["message"] = lang("record_deleted");

        return $data;
    }

    function undoDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update($this->table_header, ["deleted" => 0]);

        $pvrow = $db->select("*")
            ->from($this->table_header)
            ->where("id", $docId)
            ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($pvrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items()
    {
        $db = $this->db;

        $pvrow = $db->select("id, status")->from($this->table_header)->where("id", $this->json->doc_id)->where("deleted", 0)->get()->row();

        if (empty($pvrow)) return $this->data;

        $qirows = $db->select("*")->from($this->table_detail)->where("pv_id", $this->json->doc_id)->order_by("id", "asc")->get()->result();

        if (empty($qirows)) {
            $this->data["status"] = "notfound";
            $this->data["message"] = lang("no_data_available");
            
            return $this->data;
        }

        $items = [];

        foreach ($qirows as $qirow) {
            $item["id"] = $qirow->id;
            $item["product_name"] = $qirow->product_name;
            $item["product_description"] = $qirow->product_description;
            $item["quantity"] = $qirow->quantity;
            $item["unit"] = $qirow->unit;
            $item["price"] = number_format($qirow->price, 2);
            $item["total_price"] = number_format($qirow->total_price, 2);
            $items[] = $item;
        }

        $this->data["doc_status"] = $pvrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item()
    {
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");
        $pvrow = $db->select("id")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

        if (empty($pvrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if (!empty($itemId)) {
            $qirow = $db->select("*")->from($this->table_detail)->where("id", $itemId)->where("payment_voucher_id", $docId)->get()->row();

            if (empty($qirow)) return $this->data;

            $this->data["item_id"] = $qirow->id;
            $this->data["product_id"] = $qirow->product_id;
            $this->data["product_name"] = $qirow->product_name;
            $this->data["product_description"] = $qirow->product_description;
            $this->data["quantity"] = number_format($qirow->quantity, $this->Settings_m->getDecimalPlacesNumber());
            $this->data["unit"] = $qirow->unit;
            $this->data["price"] = number_format($qirow->price, 2);
            $this->data["total_price"] = number_format($qirow->total_price, 2);
        }

        $this->data["status"] = "success";
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
        $pvrow = $db->select("id")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

        if (empty($pvrow)) return $this->data;

        $this->validateItem();
        if ($this->data["status"] == "validate") return $this->data;

        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id == "" ? null : $this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), $this->Settings_m->getDecimalPlacesNumber());
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 2);
        $total_price = round($price * $quantity, 2);

        $fdata = [
            "payment_voucher_id" => $docId,
            "product_id" => $product_id,
            "product_name" => $product_name,
            "product_description" => $product_description,
            "quantity" => $quantity,
            "unit" => $unit,
            "price" => $price,
            "total_price" => $total_price,
        ];

        $db->trans_begin();

        if (empty($itemId)) {
            $db->where("pv_id", $docId);
            $total_items = $db->count_all_results($this->table_detail);

            $fdata["pv_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert($this->table_detail, $fdata);
        } else {
            $db->where("id", $itemId);
            $db->where("pv_id", $docId);
            $db->update($this->table_detail, $fdata);
        }

        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();
        } else {
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("payment_voucher/view/" . $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteItem()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;

        $db->where("id", $this->json->item_id);
        $db->where("pv_id", $docId);
        $db->delete($this->table_detail);

        if ($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus()
    {
        $db = $this->db;
        
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $pvrow = $db->select("*")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();
        if (empty($pvrow)) return $this->data;

        $payment_voucher_id = $this->data["doc_id"] = $docId;
        $currentStatus = $pvrow->status;

        $this->db->trans_begin();

        if ($updateStatusTo == "A") { // Approved
            if ($currentStatus == "R") {
                $this->data["dataset"] = $this->getIndexDataSetHTML($pvrow);
                return $this->data;
            }

            $db->where("pv_id", $payment_voucher_id);
            $db->update("goods_receipt_payment", [
                "receipt_flag" => 1
            ]);

            $db->where("id", $payment_voucher_id);
            $db->update($this->table_header, [
                "approved_by" => $this->login_user->id,
                "approved_datetime" => date("Y-m-d H:i:s"),
                "status" => "A"
            ]);
        } elseif ($updateStatusTo == "R") { // Rejected
            $db->where("id", $payment_voucher_id);
            $db->update("payment_voucher", ["status" => "R"]);
        }

        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();

            $this->data["dataset"] = $this->getIndexDataSetHTML($pvrow);
            return $this->data;
        }

        $db->trans_commit();

        if (isset($this->data["task"])) return $this->data;

        $pvrow = $db->select("*")->from($this->table_header)->where("id", $docId)->where("deleted", 0)->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($pvrow);
        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");
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
                if ($db->count_all_results($this->table_header) < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress . "th/" . $sharekey);
        }

        $db->where("id", $docId);
        $db->update($this->table_header, ["sharekey" => $sharekey, "sharekey_by" => $sharekey_by]);

        return $this->data;
    }

    public function dev2_getPaymentVoucherHeaderById(int $id) : stdClass
    {
        $info = new stdClass();

        $query = $this->db->get_where("pv_header", ["id" => $id])->row();
        if (isset($query) && !empty($query)) {
            $info = $query;
        }
        return $info;
    }

    public function dev2_getPaymentVoucherDetailByHeaderId(int $id) : array
    {
        $info = [];

        $query = $this->db->get_where("pv_detail", ["pv_id" => $id])->result();
        if (isset($query) && !empty($query)) {
            if (sizeof($query)) {
                $info = $query;
            }
        }
        return $info;
    }

    public function dev2_getPaymentMethodItemsById(int $id) : array
    {
        $result = array();

        $data = $this->db->select('*')
        ->from('goods_receipt_payment')
        ->where('pv_id', $id)
        ->order_by('id', 'asc')
        ->get()
        ->result();
        
        if (sizeof($data)) {
            foreach ($data as $item) {
                $item->date_output = convertDate($item->date, true);
                $item->number_format = number_format($item->amount, 2);
                $item->currency_format = to_currency($item->amount);
            }
            $result = $data;
        }
        return $result;
    }

    public function postPaymentForPaymentVoucher(array $data) : int
    {
        $this->db->insert('goods_receipt_payment', $data);
        return $this->db->insert_id();
    }

    public function postPayAmountForPaymentVoucherHeader(int $id, float $amount) : void
    {
        $pay_status = 'N';

        $pay = $this->db->select('SUM(amount) AS amount')
        ->from('goods_receipt_payment')
        ->where('pv_id', $id)
        ->get()->row();

        $info = $this->db->select('*')
        ->from('pv_header')
        ->where('id', $id)
        ->get()->row();

        if ($pay->amount == 0) {
            $pay_status = 'N';
        } elseif ($pay->amount < $info->payment_amount) {
            $pay_status = 'P';
        } elseif ($pay->amount == $info->payment_amount) {
            $pay_status = 'C';
        } else {
            $pay_status = 'O';
        }

        $this->db->where('id', $id);
        $this->db->update('pv_header', array(
            'pay_amount' => $pay->amount, 
            'pay_status' => $pay_status
        ));
    }

    public function gotConfirmedPaymentReceipt(int $id) : array
    {
        $result = array();

        $data = $this->db->select('*')
        ->from('goods_receipt_payment')
        ->where('id', $id)
        ->get()->row();

        if (!empty($data)) {
            $this->db->where('id', $id);
            $this->db->update('goods_receipt_payment', array('receipt_flag' => 1));

            $result['status'] = 'success';
            $result['info'] = $data;
        }
        return $result;
    }

    public function deleteRecordPaymentReceipt(int $id) : array
    {
        $result = array();

        $data = $this->db->select('*')
        ->from('goods_receipt_payment')
        ->where('id', $id)
        ->get()->row();

        if (!empty($data)) {
            $this->db->where('id', $id);
            $this->db->delete('goods_receipt_payment');

            $result['status'] = 'success';
            $result['info'] = $data;

            $this->postPayAmountForPaymentVoucherHeader($data->pv_id, $data->amount);
        }

        return $result;
    }

}
