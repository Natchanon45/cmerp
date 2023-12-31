<?php

class Purchase_order_m extends MY_Model
{
    private $code = 'PO';

    private $shareHtmlAddress = 'share/purchase_order/html/';

    function user_language()
    {
        return get_setting("user_" . $this->login_user->id . "_personal_language");
    }

    function modal_header()
    {
        $modal_header = str_replace('https:', '', str_replace('http:', '', str_replace('/', '', base_url())));
        return strtoupper($modal_header);
    }

    function __construct()
    {
        parent::__construct();
        $this->load->model('Purchase_request_m');
        $this->load->model('Goods_receipt_m');
    }

    function getCode()
    {
        return $this->code;
    }

    function getNewDocNumber()
    {
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);

        $running_number = $this->db->get("po_header")->num_rows() + 1;
        $doc_number = $this->getCode() . date("Ym") . sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code)
    {
        if ($status_code == 'W') {
            return lang('pr_pending');
        }
    }

    function dev2_getPoTypeById($id)
    {
        $type = array(
            '1' => 'direct_material',
            '2' => 'indirect_material',
            '3' => 'finised_goods',
            '4' => 'assets',
            '5' => 'services',
            '6' => 'expenses',
            '7' => 'mixed_purchase'
        );
        return lang($type[$id]);
    }

    function getIndexDataSetHTML($qrow)
    {
        $button = '';
        $doc_status = '<select class="dropdown_status select-status" data-doc_id="' . $qrow->id . '">';

        if ($qrow->status == "W") // Status is awaiting approval.
        {
            // The status can be appoved or canceled.
            $doc_status .= '
                <option value="W" selected>' . lang('pr_pending') . '</option>
                <option value="A">' . lang('pr_approved') . '</option>
                <option value="X">' . lang('cancel') . '</option>
            ';

            // The edit button can be used.
            $button = modal_anchor(
                get_uri('purchase_order/addedit'),
                '<i class="fa fa-pencil"></i>',
                array(
                    "data-post-id" => $qrow->id,
                    "data-title" => lang("purchase_order_edit"),
                    "data-act" => "ajax-modal",
                    "title" => lang("purchase_order_edit"),
                    "class" => "edit"
                )
            );
        } 
        elseif ($qrow->status == "A") // Status is approved.
        {
            // Start at approved.
            $doc_status .= '<option value="A" selected>' . lang('pr_approved') . '</option>';

            // The payment status is awaiting, it can create a payment voucher.
            if ($qrow->payment_status == "W") {
                $doc_status .= '
                    <option value="PV">' . lang('record_of_payment_voucher') . '</option>
                ';
            }
            // The receipt status is awaiting, it can create a goods receipt.
            if ($qrow->receipt_status == "W") {
                $doc_status .= '
                    <option value="GR">' . lang('record_of_goods_receipt') . '</option>
                ';
            }
            // The payment status and receipt status is awaiting, it can be canceled.
            if ($qrow->receipt_status == "W" && $qrow->payment_status == "W") {
                $doc_status .= '
                    <option value="X">' . lang('cancel') . '</option>
                ';
            }
            
            // The edit button was disabled.
            $button = modal_anchor(
                get_uri('purchase_order/addedit'),
                '<i class="fa fa-eye"></i>',
                array(
                    "data-post-id" => $qrow->id,
                    "data-title" => lang("purchase_order"),
                    "data-act" => "ajax-modal",
                    "title" => lang("purchase_order"),
                    "class" => "edit"
                )
            );
        } 
        elseif ($qrow->status == "R") // Status is rejected.
        {
            $doc_status .= '
                <option value="R" selected>' . lang('pr_rejected') . '</option>
            ';
            
            $button = '';
        } 
        elseif ($qrow->status == "X") // Status is canceled.
        {
            $doc_status .= '
                <option value="X" selected>' . lang('cancel') . '</option>
            ';
            
            $button = '';
        }

        $doc_status .= '</select>';

        $request_by = '-';
        if ($qrow->created_by) {
            $user = $this->Users_model->getUserById($qrow->created_by);
            $url = get_avatar($user->image);
            $span = '<span class="avatar avatar-xs mr10"><img src="' . $url . '" alt=""></span>' . $user->first_name . ' ' . $user->last_name;
            $request_by = get_team_member_profile_link($user->id, $span);
        }

        $supplier_name = '-';
        if ($qrow->supplier_id) {
            $supplier = $this->Bom_suppliers_model->dev2_getSupplierNameById($qrow->supplier_id);
            $supplier_name = "<a href='" . get_uri('stock/supplier_view/' . $qrow->supplier_id) . "'>" . mb_strimwidth($supplier, 0, 55, '...') . "</a>";
        }

        $data[] = "<a href='" . get_uri('purchase_order/view/' . $qrow->id) . "'>" . convertDate($qrow->doc_date, true) . "</a>";
        $data[] = "<a href='" . get_uri('purchase_order/view/' . $qrow->id) . "'>" . $qrow->doc_number . "</a>";
        $data[] = $qrow->reference_number ? $qrow->reference_number : '-';
        $data[] = $qrow->po_type ? $this->dev2_getPoTypeById($qrow->po_type) : '-';
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $data[] = $supplier_name;
        }
        
        $data[] = $request_by;
        $data[] = number_format($qrow->total, 2);
        $data[] = $doc_status;
        $data[] = $button;

        return $data;
    }

    function indexDataSet()
    {
        $db = $this->db;

        $db->select('*')->from('po_header');

        if ($this->input->post('status') != null) {
            $db->where('status', $this->input->post('status'));
        }

        if ($this->input->post('po_type') != null) {
            $db->where('po_type', $this->input->post('po_type'));
        }

        if ($this->input->post('start_date') != null && $this->input->post('end_date')) {
            $db->where('doc_date >=', $this->input->post('start_date'));
            $db->where('doc_date <=', $this->input->post('end_date'));
        }

        if ($this->input->post('supplier_id') != null) {
            $db->where('supplier_id', $this->input->post('supplier_id'));
        }

        $db->where('deleted', 0);

        $qrows = $db->order_by('doc_number', 'desc')->get()->result();

        $dataset = [];

        foreach ($qrows as $qrow) {
            $dataset[] = $this->getIndexDataSetHTML($qrow);
        }

        return $dataset;
    }

    function getDoc($docId)
    {
        $db = $this->db;

        $this->data["pr_id"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["doc_type"] = null;
        $this->data["credit"] = "0";
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["project_id"] = null;
        $this->data["supplier_id"] = null;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["doc_status"] = null;
        $this->data["doc_receipt_status"] = null;
        $this->data["doc_payment_status"] = null;

        if (!empty($docId)) {
            $qrow = $db->select("*")
                ->from("po_header")
                ->where("id", $docId)
                ->where("deleted", 0)
                ->get()->row();

            if (empty($qrow)) return $this->data;

            $this->data["doc_id"] = $docId;
            $this->data["pr_id"] = $qrow->pr_id;
            $this->data["doc_number"] = $qrow->doc_number;
            $this->data["share_link"] = $qrow->sharekey != null ? get_uri($this->shareHtmlAddress . "th/" . $qrow->sharekey) : null;
            $this->data["doc_date"] = $qrow->doc_date;
            $this->data["doc_type"] = $qrow->po_type;
            $this->data["credit"] = $qrow->credit;
            $this->data["due_date"] = $qrow->due_date;
            $this->data["reference_number"] = $qrow->reference_number;
            $this->data["discount_type"] = $qrow->discount_type;
            $this->data["discount_percent"] = $qrow->discount_percent;
            $this->data["discount_amount"] = $qrow->discount_amount;
            $this->data["vat_inc"] = $qrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($qrow->vat_percent, 2) . "%";
            $this->data["wht_inc"] = $qrow->wht_inc;
            $this->data["wht_percent"] = number_format_drop_zero_decimals($qrow->wht_percent, 0);
            $this->data["project_id"] = $qrow->project_id;
            $this->data["supplier_id"] = $qrow->supplier_id;
            $this->data["remark"] = $qrow->remark;
            $this->data["created_by"] = $qrow->created_by;
            $this->data["created_datetime"] = $qrow->created_datetime;
            $this->data["approved_by"] = $qrow->approved_by;
            $this->data["approved_datetime"] = $qrow->approved_datetime;
            $this->data["doc_status"] = $qrow->status;
            $this->data["doc_receipt_status"] = $qrow->receipt_status;
            $this->data["doc_payment_status"] = $qrow->payment_status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function getEdoc($docId = null, $sharekey = null, $lang = null)
    {
        if ($lang == null) $lang = $this->user_language();

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

        $qrow = $db->select("*")
            ->from("po_header")
            ->get()->row();

        if (empty($qrow)) return $this->data;

        $docId = $qrow->id;

        $qirows = $db->select("*")
            ->from("po_detail")
            ->where("po_id", $docId)
            ->order_by("sort", "asc")
            ->get()->result();

        $supplier_id = $qrow->supplier_id;

        $this->data["buyer"] = $ci->Users_m->getInfo($qrow->created_by);
        $this->data["seller"] = $ci->Bom_suppliers_model->getInfo($supplier_id);
        $this->data["seller_contact"] = $ci->Bom_suppliers_model->getContactInfo($supplier_id);
        $this->data["doc_number"] = $qrow->doc_number;
        $this->data["doc_date"] = $qrow->doc_date;
        $this->data["credit"] = $qrow->credit;
        $this->data["due_date"] = $qrow->due_date;
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
        $this->data["total_in_text"] = ($lang == 'en' || $lang == 'english') ? numberToBahtEng($qrow->total) : numberToText($qrow->total);
        $this->data["wht_inc"] = $qrow->wht_inc;
        $this->data["wht_percent"] = $qrow->wht_percent;
        $this->data["wht_value"] = $qrow->wht_value;
        $this->data["payment_amount"] = $qrow->payment_amount;

        $this->data["sharekey_by"] = $qrow->sharekey_by;
        $this->data["created_by"] = $ci->Users_m->getInfo($qrow->created_by);
        $this->data["created_datetime"] = $qrow->created_datetime;
        $this->data["approved_by"] = $ci->Users_m->getInfo($qrow->approved_by);
        $this->data["approved_datetime"] = $qrow->approved_datetime;
        $this->data["doc_status"] = $qrow->status;
        $this->data["doc"] = $qrow;
        $this->data["items"] = $qirows;
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

            $qrow = $db->select("*")
                ->from("po_header")
                ->where("id", $docId)
                ->where("deleted", 0)
                ->get()->row();

            if (empty($qrow)) return $this->data;

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
            $qrow = $db->select("*")
                ->from("po_header")
                ->where("id", $docId)
                ->where("deleted", 0)
                ->get()->row();

            if (empty($qrow)) return $this->data;

            $discount_type = $qrow->discount_type;
            $discount_percent = $qrow->discount_percent;
            $discount_amount = $qrow->discount_amount;

            $vat_inc = $qrow->vat_inc;
            $wht_inc = $qrow->wht_inc;

            if ($vat_inc == "Y") $vat_percent = $qrow->vat_percent;
            if ($wht_inc == "Y") $wht_percent = $qrow->wht_percent;
        }

        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
            ->from("po_detail")
            ->where("po_id", $docId)
            ->get()->row()->SUB_TOTAL;

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
        $db->update("po_header", [
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
        $this->data["total_in_text"] = ($this->user_language() == 'english') ? numberToBahtEng($qrow->total) : numberToText($qrow->total);
        $this->data["wht_inc"] = $wht_inc;
        $this->data["wht_percent"] = number_format_drop_zero_decimals($wht_percent, 2);
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
                "field" => "due_date",
                'label' => '',
                'rules' => 'required'
            ]
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->data["status"] = "validate";
            if (form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
            if (form_error('due_date') != null) $this->data["messages"]["due_date"] = form_error('due_date');
        }
    }

    function saveDoc()
    {
        $db = $this->db;

        $this->validateDoc();
        if ($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $po_type = $this->json->doc_type;
        $credit = intval($this->json->credit) < 0 ? 0 : intval($this->json->credit);
        $due_date = date('Y-m-d', strtotime($doc_date . " + " . $credit . " days"));
        $reference_number = $this->json->reference_number;
        $supplier_id = $this->json->supplier_id;
        $project_id = $this->json->project_id;
        $remark = $this->json->remark;

        if ($supplier_id == "") {
            $this->data["status"] = "validate";
            $this->data["messages"]["supplier_id"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }

        if ($docId != "") {
            $qrow = $db->select("status")
                ->from("po_header")
                ->where("id", $docId)
                ->where("deleted", 0)
                ->get()->row();

            if (empty($qrow)) {
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if ($qrow->status != "W") {
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("po_header", [
                "doc_date" => $doc_date,
                "credit" => $credit,
                "due_date" => $due_date,
                "reference_number" => $reference_number,
                "supplier_id" => $supplier_id,
                "project_id" => $project_id,
                "remark" => $remark
            ]);
        } else {
            $doc_number = $this->getNewDocNumber();

            $db->insert("po_header", [
                "doc_number" => $doc_number,
                "doc_date" => $doc_date,
                "po_type" => $po_type,
                "credit" => $credit,
                "due_date" => $due_date,
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

        $this->data["target"] = get_uri('purchase_order/view/' . $docId);
        $this->data["status"] = 'success';

        return $this->data;
    }

    function deleteDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $qrow = $db->select("status")
            ->from("po_header")
            ->where("id", $docId)
            ->get()->row();

        if (empty($qrow)) return $this->data;

        $bnrow = $db->select("*")
            ->from("goods_receipt_header")
            ->where("po_id", $docId)
            ->where("deleted", 0)
            ->get()->row();

        if (!empty($bnrow)) {
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารถูกอ้างอิงในใบวางบิลแล้ว";
            return $this->data;
        }

        if ($qrow->status != "W") {
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("po_header", ["deleted" => 1]);

        $data["success"] = true;
        $data["message"] = lang('record_deleted');

        return $data;
    }

    function undoDoc()
    {
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("po_header", ["deleted" => 0]);

        $qrow = $db->select("*")
            ->from("po_header")
            ->where("id", $docId)
            ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($qrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items()
    {
        $db = $this->db;

        $qrow = $db->select("id, status")
            ->from("po_header")
            ->where("id", $this->json->doc_id)
            ->where("deleted", 0)
            ->get()->row();

        if (empty($qrow)) return $this->data;

        $qirows = $db->select("*")
            ->from("po_detail")
            ->where("po_id", $this->json->doc_id)
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
            $item["product_name"] = $qirow->product_name;
            $item["product_description"] = $qirow->product_description;
            $item["quantity"] = number_format($qirow->quantity, 2);
            $item["unit"] = $qirow->unit;
            $item["price"] = number_format($qirow->price, 2);
            $item["total_price"] = number_format($qirow->total_price, 2);

            $items[] = $item;
        }

        $this->data["doc_status"] = $qrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item()
    {
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $qrow = $db->select("id, po_type")
            ->from("po_header")
            ->where("id", $docId)
            ->where("deleted", 0)
            ->get()->row();

        if (empty($qrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["doc_type"] = $qrow->po_type;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = 1.00;
        $this->data["unit"] = "";
        $this->data["price"] = 0.00;
        $this->data["total_price"] = 0.00;

        if (!empty($itemId)) {
            $qirow = $db->select("*")
                ->from("po_detail")
                ->where("id", $itemId)
                ->where("po_id", $docId)
                ->get()->row();

            if (empty($qirow)) return $this->data;

            $this->data["item_id"] = $qirow->id;
            $this->data["product_id"] = $qirow->product_id;
            $this->data["product_name"] = $qirow->product_name;
            $this->data["product_description"] = $qirow->product_description;
            $this->data["quantity"] = $qirow->quantity;
            $this->data["unit"] = $qirow->unit;
            $this->data["price"] = $qirow->price;
            $this->data["total_price"] = $qirow->total_price;
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

        $qrow = $db->select("id")
            ->from("po_header")
            ->where("id", $docId)
            ->where("deleted", 0)
            ->get()->row();

        if (empty($qrow)) return $this->data;

        $this->validateItem();
        if ($this->data["status"] == "validate") return $this->data;

        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id == "" ? null : $this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), 2);
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 4);
        $total_price = round(getNumber($this->json->total_price), 4);

        $fdata = [
            "po_id" => $docId,
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
            $db->where("po_id", $docId);
            $total_items = $db->count_all_results("po_detail");
            $fdata["po_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("po_detail", $fdata);
        } else {
            $db->where("id", $itemId);
            $db->where("po_id", $docId);
            $db->update("po_detail", $fdata);
        }

        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();
        } else {
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri('purchase_order/view/' . $docId);
        $this->data["status"] = 'success';

        return $this->data;
    }

    function deleteItem()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;

        $db->where("id", $this->json->item_id);
        $db->where("po_id", $docId);
        $db->delete("po_detail");

        if ($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus() // MARK
    {
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $qrow = $db->select('*')->from('po_header')->where('id', $docId)->where('deleted', 0)->get()->row();

        if (empty($qrow)) return $this->data;

        $currentStatus = $qrow->status;
        $po_id = $this->data["doc_id"] = $docId;

        if ($qrow->status == $updateStatusTo && $updateStatusTo != "P") {
            $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $this->db->trans_begin();

        if ($updateStatusTo == 'R') // Rejected
        {
            $db->where('id', $po_id);
            $db->update('po_header', ['status' => 'R']);
        } 
        elseif ($updateStatusTo == 'X') // Cancelled
        { 
            $cancel = $db->select('*')->from('po_header')->where('id', $po_id)->where('deleted', 0)->get()->row();

            $db->where('id', $cancel->pr_id);
            $db->update('pr_header', ['status' => 'W']);

            $db->where('id', $cancel->id);
            $db->update('po_header', ['status' => 'X']);

            $this->data['task'] = 'cancelled_purchase_order';
            $this->data['status'] = 'success';
            $this->data['message'] = lang('record_canceled');
        } 
        elseif ($updateStatusTo == 'A') // Approved
        { 
            // If current status is rejected
            if ($currentStatus == 'R') {
                $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            // Count po items detail
            $this->db->where('po_id', $po_id);
            $count_po_detail = $this->db->count_all_results('po_detail');

            if ($count_po_detail == 0) {
                $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
                $this->data['message'] = lang('no_item_found');
                return $this->data;
            }

            // Update po status
            $db->where('id', $po_id);
            $db->update('po_header', [
                'approved_by' => $this->login_user->id,
                'approved_datetime' => date("Y-m-d H:i:s"),
                'status' => "A"
            ]);

            $this->data['task'] = 'approved_purchase_order';
            $this->data['status'] = 'success';
            $this->data['message'] = lang('record_saved');
            $this->data['url'] = get_uri('purchase_order/view/' . $po_id);
        } 
        elseif ($updateStatusTo == 'PV') // Payment Voucher
        { 
            // If current status not equal to approved
            if ($currentStatus != 'A') {
                $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            // Prepare docuemnt number
            $param_docno = [
                "prefix" => "PV",
                "LPAD" => 4,
                "column" => "doc_number",
                "table" => "pv_header"
            ];

            $pv_doc_number = $this->Db_model->genDocNo($param_docno);
            $pv_doc_date = date("Y-m-d");
            $pv_credit_day = $qrow->credit;
            $pv_due_date = date("Y-m-d", strtotime($pv_doc_date . " + " . $pv_credit_day . " days"));

            $db->insert("pv_header", [
                "po_id" => $qrow->id,
                "doc_number" => $pv_doc_number,
                "po_type" => $qrow->po_type,
                "doc_date" => $pv_doc_date,
                "credit" => $pv_credit_day,
                "due_date" => $pv_due_date,
                "reference_number" => $qrow->doc_number,
                "project_id" => $qrow->project_id,
                "supplier_id" => $qrow->supplier_id,
                "sub_total_before_discount" => $qrow->sub_total_before_discount,
                "discount_type" => $qrow->discount_type,
                "discount_percent" => $qrow->discount_percent,
                "discount_amount" => $qrow->discount_amount,
                "sub_total" => $qrow->sub_total,
                "vat_inc" => $qrow->vat_inc,
                "vat_percent" => $qrow->vat_percent,
                "vat_value" => $qrow->vat_value,
                "total" => $qrow->total,
                "wht_inc" => $qrow->wht_inc,
                "wht_percent" => $qrow->wht_percent,
                "wht_value" => $qrow->wht_value,
                "payment_amount" => $qrow->payment_amount,
                "remark" => $qrow->remark,
                "created_by" => $this->login_user->id,
                "created_datetime" => date("Y-m-d H:i:s")
            ]);

            $pv_id = $db->insert_id();
            $qirows = $db->select('*')->from('po_detail')->where('po_id', $po_id)->order_by('sort', 'ASC')->get()->result();

            $sort = 1;
            if (!empty($qirows)) {
                foreach ($qirows as $qirow) {
                    $db->insert('pv_detail', array(
                        'pv_id' => $pv_id,
                        'po_id' => $po_id,
                        'po_item_id' => $qirow->id,
                        'product_id' => $qirow->product_id,
                        'product_name' => $qirow->product_name,
                        'product_description' => $qirow->product_description,
                        'quantity' => $qirow->quantity,
                        'unit' => $qirow->unit,
                        'price' => $qirow->price,
                        'total_price' => $qirow->total_price,
                        'sort' => $sort
                    ));
                    $sort++;

                    $db->where('id', $qirow->id);
                    $db->update('po_detail', array('payment' => $qirow->quantity));
                }
            }

            $db->where('id', $qrow->id);
            $db->update('po_header', array('payment_status' => 'C'));

            $this->data['pv_id'] = $pv_id;
            $this->data['task'] = 'create_payment_voucher';
            $this->data['status'] = 'success';
            $this->data['message'] = lang('record_saved');
            $this->data['url'] = get_uri('payment_voucher/view/' . $pv_id);
        } 
        elseif ($updateStatusTo == 'GR') // Goods Receipt
        { 
            // If current status not equal to approved
            if ($currentStatus != 'A') {
                $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            // Prepare document number
            $ex_doc_number = $this->Goods_receipt_m->getNewDocNumber('GR');

            // Prepare goods receipt info
            $pv_doc_number = $this->Goods_receipt_m->getNewDocNumber('PV');
            $ex_doc_date = date("Y-m-d");
            $ex_credit_day = $qrow->credit;
            $ex_due_date = date("Y-m-d", strtotime($ex_doc_date . " + " . $ex_credit_day . " days"));

            $db->insert('goods_receipt', array(
                'po_id' => $qrow->id,
                'doc_number' => $ex_doc_number,
                'pv_number' => $pv_doc_number,
                'po_type' => $qrow->po_type,
                'doc_date' => $ex_doc_date,
                'credit' => $ex_credit_day,
                'due_date' => $ex_due_date,
                'reference_number' => $qrow->doc_number,
                'project_id' => $qrow->project_id,
                'supplier_id' => $qrow->supplier_id,
                'sub_total_before_discount' => $qrow->sub_total_before_discount,
                'discount_type' => $qrow->discount_type,
                'discount_percent' => $qrow->discount_percent,
                'discount_amount' => $qrow->discount_amount,
                'sub_total' => $qrow->sub_total,
                'vat_inc' => $qrow->vat_inc,
                'vat_percent' => $qrow->vat_percent,
                'vat_value' => $qrow->vat_value,
                'total' => $qrow->total,
                'wht_inc' => $qrow->wht_inc,
                'wht_percent' => $qrow->wht_percent,
                'wht_value' => $qrow->wht_value,
                'payment_amount' => $qrow->payment_amount,
                'remark' => $qrow->remark,
                'created_by' => $qrow->created_by,
                'created_datetime' => date("Y-m-d H:i:s"),
                'status' => "W"
            ));

            $pv_id = $db->insert_id();
            $qirows = $db->select('*')->from('po_detail')->where('po_id', $po_id)->order_by('sort', 'ASC')->get()->result();

            $sort = 1;
            if (!empty($qirows)) {
                foreach ($qirows as $qirow) {
                    $db->insert('goods_receipt_items', array(
                        'pv_id' => $pv_id,
                        'po_id' => $po_id,
                        'po_item_id' => $qirow->id,
                        'product_id' => $qirow->product_id,
                        'product_name' => $qirow->product_name,
                        'product_description' => $qirow->product_description,
                        'quantity' => $qirow->quantity,
                        'unit' => $qirow->unit,
                        'price' => $qirow->price,
                        'total_price' => $qirow->total_price,
                        'sort' => $sort
                    ));
                    $sort++;

                    $db->where('id', $qirow->id);
                    $db->update('po_detail', array('receipt' => $qirow->quantity));
                }
            }

            $db->where('id', $qrow->id);
            $db->update('po_header', array('receipt_status' => 'C'));

            $this->data['pv_id'] = $pv_id;
            $this->data['task'] = 'create_goods_receipt';
            $this->data['status'] = 'success';
            $this->data['message'] = lang('record_saved');
            $this->data['url'] = get_uri('goods_receipt/view/' . $pv_id);
        }

        if ($db->trans_status() === FALSE) {
            $db->trans_rollback();

            $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $db->trans_commit();

        if (isset($this->data['task'])) return $this->data;

        $qrow = $db->select('*')->from('po_header')->where('id', $docId)->where('deleted', 0)->get()->row();

        $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
        $this->data['status'] = 'success';
        $this->data['message'] = lang('record_saved');
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
                if ($db->count_all_results("po_header") < 1)
                    break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress . "th/" . $sharekey);
        }

        $db->where("id", $docId);
        $db->update("po_header", ["sharekey" => $sharekey, "sharekey_by" => $sharekey_by]);

        return $this->data;
    }

}