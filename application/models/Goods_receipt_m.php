<?php

class Goods_receipt_m extends MY_Model
{
    private $code = 'PV';

    private $shareHtmlAddress = 'share/goods_receipt/html/';

    function __construct()
    {
        parent::__construct();

        $this->load->model('Purchase_order_m');
    }

    function getCode()
    {
        return $this->code;
    }

    function getPurchaseOrderBySupplierId()
    {
        $sql = "SELECT * FROM `po_header` WHERE 1 AND `status` = 'A' AND `supplier_id` = '" . $this->json->supplier_id . "'";
        $query = $this->db->query($sql)->result();

        $po_list = array();
        if (sizeof($query)) {
            $po_list = $query;
        }

        $this->data['supplier_id'] = $this->json->supplier_id;
        $this->data['po_list'] = $po_list;
        $this->data['status'] = "success";

        return $this->data;
    }

    function getNewDocNumber()
    {
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $running_number = $this->db->get("goods_receipt")->num_rows() + 1;

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
        $doc_status = '<select class="dropdown_status select-status" data-doc_id="' . $qrow->id . '">';
        if ($qrow->status == 'W') {
            $doc_status .= '
                <option value="W" selected>' . lang('pr_pending') . '</option>
                <option value="A">' . lang('pr_approved') . '</option>
                <option value="X">' . lang('cancel') . '</option>
            ';
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
            $supplier_name = '<a href="' .  get_uri('stock/supplier_view/' . $qrow->supplier_id) . '">' . mb_strimwidth($supplier, 0, 55, '...') . '</a>';
        }

        $data = array(
            '<a href="' . get_uri('goods_receipt/view/' . $qrow->id) . '">' . convertDate($qrow->doc_date, true) . '</a>',
            '<a href="' . get_uri('goods_receipt/view/' . $qrow->id) . '">' . $qrow->doc_number . '</a>',
            $qrow->reference_number,
            $qrow->po_type ? $this->dev2_getPoTypeById($qrow->po_type) : '-',
            $supplier_name,
            $request_by,
            number_format($qrow->total, 2),
            $doc_status,
            "<a data-post-id='" . $qrow->id . "' data-action-url='" . get_uri('goods_receipt/addedit') . "' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
        );

        return $data;
    }

    function indexDataSet()
    {
        $db = $this->db;

        $db->select('*')->from('goods_receipt');

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

        $this->data['po_id'] = 0;
        $this->data['doc_number'] = '';
        $this->data['share_link'] = null;
        $this->data['po_type'] = 0;
        $this->data['doc_date'] = date('Y-m-d');
        $this->data['credit'] = 0;
        $this->data['due_date'] = date('Y-m-d');
        $this->data['reference_number'] = '';
        $this->data['project_id'] = 0;
        $this->data['supplier_id'] = 0;
        $this->data['sub_total_before_discount'] = 0.00;
        $this->data['sub_total'] = 0.00;
        $this->data['vat_inc'] = 'N';
        $this->data['vat_percent'] = 7.00;
        $this->data['vat_value'] = 'N';
        $this->data['total'] = 0.00;
        $this->data['wht_inc'] = 'N';
        $this->data['wht_percent'] = 0.00;
        $this->data['wht_value'] = 0.00;
        $this->data['payment_amount'] = 0.00;
        $this->data['pay_amount'] = 0.00;
        $this->data['pay_status'] = 'N';
        $this->data['remark'] = null;
        $this->data['created_by'] = 0;
        $this->data['created_datetime'] = date('Y-m-d');
        $this->data['approved_by'] = 0;
        $this->data['approved_datetime'] = date('Y-m-d');
        $this->data['doc_status'] = 'W';
        $this->data['deleted'] = 0;

        if (!empty($docId)) {
            $qrow = $db->select('*')->from('goods_receipt')->where('deleted', 0)->where('id', $docId)->get()->row();
            if (empty($qrow)) return $this->data;

            $this->data['doc_id'] = $qrow->id;
            $this->data['po_id'] = $qrow->po_id;
            $this->data['doc_number'] = $qrow->doc_number;
            $this->data['share_link'] = $qrow->sharekey != null ? get_uri($this->shareHtmlAddress . "th/" . $qrow->sharekey) : null;
            $this->data['po_type'] = $qrow->po_type;
            $this->data['doc_date'] = $qrow->doc_date;
            $this->data['credit'] = $qrow->credit;
            $this->data['due_date'] = $qrow->due_date;
            $this->data['reference_number'] = $qrow->reference_number;
            $this->data['project_id'] = $qrow->project_id;
            $this->data['supplier_id'] = $qrow->supplier_id;
            $this->data['sub_total_before_discount'] = $qrow->sub_total_before_discount;
            $this->data['sub_total'] = $qrow->sub_total;
            $this->data['vat_inc'] = $qrow->vat_inc;
            $this->data['vat_percent'] = number_format_drop_zero_decimals($qrow->vat_percent, 2) . "%";
            $this->data['vat_value'] = $qrow->vat_value;
            $this->data['total'] = $qrow->total;
            $this->data['wht_inc'] = $qrow->wht_inc;
            $this->data['wht_percent'] = $qrow->wht_percent;
            $this->data['wht_value'] = $qrow->wht_value;
            $this->data['payment_amount'] = $qrow->payment_amount;
            $this->data['pay_amount'] = $qrow->pay_amount;
            $this->data['remain_amount'] = $qrow->payment_amount - $qrow->pay_amount;
            
            // Pay Status
            if ($qrow->pay_amount == 0) {
                $this->data['pay_status'] = 'N';
            } elseif ($qrow->pay_amount < $qrow->payment_amount) {
                $this->data['pay_status'] = 'P';
            } elseif ($qrow->pay_amount == $qrow->payment_amount) {
                $this->data['pay_status'] = 'C';
            } else {
                $this->data['pay_status'] = 'O';
            }

            $this->data['remark'] = $qrow->remark;
            $this->data['created_by'] = $qrow->created_by;
            $this->data['created_datetime'] = $qrow->created_datetime;
            $this->data['approved_by'] = $qrow->approved_by;
            $this->data['approved_datetime'] = $qrow->approved_datetime;
            $this->data['doc_status'] = $qrow->status;
            $this->data['deleted'] = $qrow->deleted;
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
        $this->data["total_in_text"] = numberToText($qrow->total);
        $this->data["wht_inc"] = $qrow->wht_inc;
        $this->data["wht_percent"] = $qrow->wht_percent;
        $this->data["wht_value"] = $qrow->wht_value;
        $this->data["payment_amount"] = $qrow->payment_amount;

        $this->data["sharekey_by"] = $qrow->sharekey_by;
        $this->data["approved_by"] = $qrow->approved_by;
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

        $discount_type = 'P';
        $discount_percent = 0;
        $discount_amount = 0;

        $vat_inc = 'N';
        $vat_percent = $this->Taxes_m->getVatPercent();
        $vat_value = 0;

        $wht_inc = 'N';
        $wht_percent = $this->Taxes_m->getWhtPercent();
        $wht_value = 0;

        if ($docId == null && isset($this->json->doc_id)) {
            $docId = $this->json->doc_id;

            $vat_inc = $this->json->vat_inc == true ? 'Y' : 'N';
            $wht_inc = $this->json->wht_inc == true ? 'Y' : 'N';

            $qrow = $db->select('*')
            ->from('goods_receipt')
            ->where('id', $docId)
            ->where('deleted', 0)
            ->get()->row();

            if (empty($qrow)) return $this->data;

            $discount_type = $this->json->discount_type;

            if ($discount_type == 'P') {
                $discount_percent = getNumber($this->json->discount_percent);
                if ($discount_percent >= 100) $discount_percent = 99.99;
                if ($discount_percent < 0) $discount_percent = 0;
            } else {
                $discount_amount = getNumber($this->json->discount_value);
            }

            if ($vat_inc == 'Y') $vat_percent = $this->Taxes_m->getVatPercent();
            if ($wht_inc == 'Y') $wht_percent = getNumber($this->json->wht_percent);
        } else {
            $qrow = $db->select('*')
            ->from('po_header')
            ->where('id', $docId)
            ->where('deleted', 0)
            ->get()->row();

            if (empty($qrow)) return $this->data;

            $discount_type = $qrow->discount_type;
            $discount_percent = $qrow->discount_percent;
            $discount_amount = $qrow->discount_amount;

            $vat_inc = $qrow->vat_inc;
            $wht_inc = $qrow->wht_inc;

            if ($vat_inc == 'Y') $vat_percent = $qrow->vat_percent;
            if ($wht_inc == 'Y') $wht_percent = $qrow->wht_percent;
        }

        $sub_total_before_discount = $db->select('SUM(total_price) AS SUB_TOTAL')
        ->from('goods_receipt_items')
        ->where('pv_id', $docId)
        ->get()->row()->SUB_TOTAL;

        if ($sub_total_before_discount == null) $sub_total_before_discount = 0;
        if ($discount_type == 'P') {
            if ($discount_percent > 0) {
                $discount_amount = ($sub_total_before_discount * $discount_percent) / 100;
            }
        } else {
            if ($discount_amount > $sub_total_before_discount) $discount_amount = $sub_total_before_discount;
            if ($discount_amount < 0) $discount_amount = 0;
        }

        $sub_total = $sub_total_before_discount - $discount_amount;

        if ($vat_inc == 'Y') $vat_value = ($sub_total * $vat_percent) / 100;
        $total = $sub_total + $vat_value;

        if ($wht_inc == 'Y') $wht_value = ($sub_total * $wht_percent) / 100;
        $payment_amount = $total - $wht_value;

        $db->where('id', $docId);
        $db->update('goods_receipt', [
            'sub_total_before_discount' => $sub_total_before_discount,
            'discount_type' => $discount_type,
            'discount_percent' => $discount_percent,
            'discount_amount' => $discount_amount,
            'sub_total' => $sub_total,
            'vat_inc' => $vat_inc,
            'vat_percent' => $vat_percent,
            'vat_value' => $vat_value,
            'total' => $total,
            'wht_inc' => $wht_inc,
            'wht_percent' => $wht_percent,
            'wht_value' => $wht_value,
            'payment_amount' => $payment_amount
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
                "field" => "receive_date",
                'label' => '',
                'rules' => 'required'
            ]
        ]);

        if ($this->form_validation->run() == FALSE) {
            $this->data["status"] = "validate";
            if (form_error('doc_date') != null)
                $this->data["messages"]["doc_date"] = form_error('doc_date');
            if (form_error('receive_date') != null)
                $this->data["messages"]["receive_date"] = form_error('receive_date');
        }
    }

    function saveDoc()
    {
        $db = $this->db;

        $this->validateDoc();
        if ($this->data["status"] == "validate")
            return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $po_list = $this->json->po_list;
        $receive_date = convertDate($this->json->receive_date);
        $reference_number = $this->json->reference_number;
        $supplier_id = $this->json->supplier_id;
        $remark = $this->json->remark;

        if ($supplier_id == "") {
            $this->data["status"] = "validate";
            $this->data["messages"]["supplier_id"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }

        if ($receive_date == "") {
            $this->data["status"] = "validate";
            $this->data["messages"]["receive_date"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }

        if ($docId != "") {
            $qrow = $db->select("status")
                ->from("gr_header")
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
            $db->update("gr_header", [
                "doc_date" => $doc_date,
                "po_list" => $po_list,
                "receive_date" => $receive_date,
                "reference_number" => $reference_number,
                "supplier_id" => $supplier_id,
                "remark" => $remark
            ]);
        } else {
            $doc_number = $this->getNewDocNumber();

            $db->insert("gr_header", [
                "doc_number" => $doc_number,
                "doc_date" => $doc_date,
                "po_list" => $po_list,
                "receive_date" => $receive_date,
                "reference_number" => $reference_number,
                "supplier_id" => $supplier_id,
                "vat_inc" => "N",
                "remark" => $remark,
                "created_by" => $this->login_user->id,
                "created_datetime" => date("Y-m-d H:i:s"),
                "status" => "W"
            ]);

            $docId = $db->insert_id();
        }

        $this->data["target"] = get_uri("goods_receipt/view/" . $docId);
        $this->data["status"] = "success";

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

        if (empty($qrow))
            return $this->data;

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

        $qrow = $db->select('*')
        ->from('goods_receipt')
        ->where('id', $this->json->doc_id)
        ->where('deleted', 0)
        ->get()->row();

        if (empty($qrow)) return $this->data;

        $qirows = $db->select('*')
        ->from('goods_receipt_items')
        ->where('pv_id', $this->json->doc_id)
        ->order_by('id', 'asc')
        ->get()->result();

        if (empty($qirows)) {
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach ($qirows as $qirow) {
            $items[] = [
                'id' => $qirow->id,
                'product_name' => $qirow->product_name,
                'product_description' => $qirow->product_description,
                'quantity' => $qirow->quantity,
                'unit' => $qirow->unit,
                'price' => number_format($qirow->price, 2),
                'total_price' => number_format($qirow->total_price, 2)
            ];
        }

        $this->data["doc_status"] = $qrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item()
    {
        $db = $this->db;
        $docId = $this->input->post('doc_id');
        $itemId = $this->input->post('item_id');

        $qrow = $db->select('*')->from('goods_receipt')->where('id', $docId)->where('deleted', 0)->get()->row();
        if (empty($qrow)) return $this->data;

        $this->data['doc_id'] = $docId;
        $this->data['po_id'] = $qrow->po_id;
        $this->data['doc_type'] = $qrow->po_type;
        $this->data['doc_status'] = $qrow->status;
        $this->data['product_id'] = '';
        $this->data['product_name'] = '';
        $this->data['product_description'] = '';
        $this->data['quantity'] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data['unit'] = '';
        $this->data['price'] = number_format(0, 2);
        $this->data['total_price'] = number_format(0, 2);

        if (isset($itemId) && !empty($itemId)) {
            $qirow = $db->select('*')->from('goods_receipt_items')->where('id', $itemId)->where('pv_id', $docId)->get()->row();
            if (empty($qirow)) return $this->data;

            $this->data['item_id'] = $qirow->id;
            $this->data['product_id'] = $qirow->product_id;
            $this->data['product_name'] = $qirow->product_name;
            $this->data['product_description'] = $qirow->product_description;
            $this->data['quantity'] = number_format($qirow->quantity, $this->Settings_m->getDecimalPlacesNumber());
            $this->data['unit'] = $qirow->unit;
            $this->data['price'] = number_format($qirow->price, 2);
            $this->data['total_price'] = number_format($qirow->total_price, 2);
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
            if (form_error('quantity') != null)
                $this->data["messages"]["quantity"] = form_error('quantity');
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

        $this->data["target"] = get_uri("purchase_order/view/" . $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteItem()
    {
        $db = $this->db;
        $docId = $this->json->doc_id;

        $db->where("id", $this->json->item_id);
        $db->where("po_id", $docId);
        $db->delete("po_detail");

        if ($db->affected_rows() != 1)
            return $this->data;

        $this->updateDoc($docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus()
    {
        $db = $this->db;
        $this->data['doc_id'] = $this->json->documentId;
        $this->data['updateStatusTo'] = $this->json->updateStatus;

        $qrow = $db->select('*')->from('goods_receipt')->where('id', $this->data['doc_id'])->where('deleted', 0)->get()->row();
        if (empty($qrow)) return $this->data;

        $pv_id = $this->data['doc_id'] = $qrow->id;
        $this->data['currentStatus'] = $qrow->status;

        if ($this->data['updateStatusTo'] == $this->data['currentStatus']) {
            $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        // $this->db->trans_begin();

        if ($this->data['updateStatusTo'] == 'W') // Save
        {
            if ($this->data['currentStatus'] != 'N') {
                $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            $db->where('id', $pv_id);
            $db->update('goods_receipt', [
                'status' => 'W'
            ]);

            $this->data['status'] = 'success';
        }

        // if ($updateStatusTo == 'A') // Approved
        // {
        //     if ($currentStatus == 'R') {
        //         $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
        //         return $this->data;
        //     }

        //     $db->where('id', $pv_id);
        //     $db->update('goods_receipt', [
        //         'approved_by' => $this->login_user->id,
        //         'approved_datetime' => date('Y-m-d H:i:s'),
        //         'status' => 'A'
        //     ]);
        // }
        
        // if ($updateStatusTo == 'R') // Rejected
        // {
        //     if ($currentStatus != 'W') {
        //         $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
        //         return $this->data;
        //     }

        //     $db->where('id', $pv_id);
        //     $db->update('goods_receipt', ['status' => 'R']);
        // }

        // if ($db->trans_status() === FALSE) {
        //     $db->trans_rollback();

        //     $this->data['post'] = $this->json;
        //     $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
        //     return $this->data;
        // }

        // $db->trans_commit();

        // if (isset($this->data['task'])) return $this->data;

        // $qrow = $db->select('*')->from('goods_receipt')->where('id', $docId)->where('deleted', 0)->get()->row();

        // $this->data['dataset'] = $this->getIndexDataSetHTML($qrow);
        // $this->data['status'] = 'success';
        // $this->data['message'] = lang('record_saved');

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

    function payments_method()
    {
        $result = $this->db->get('payment_methods')->result();

        $data = [];
        if (sizeof($result)) {
            foreach ($result as $item) {
                $data[] = [
                    'id' => $item->id,
                    'text' => $item->title,
                    'description' => $item->description
                ];
            }
        }
        return $data;
    }

    function postPaymentForGoodsReceipt(array $data): int
    {
        $this->db->insert('goods_receipt_payment', $data);
        return $this->db->insert_id();
    }

    function postPayAmountForGoodsReceiptHeader(int $id, float $amount): void
    {
        $sql = "UPDATE goods_receipt SET pay_amount = pay_amount + ? WHERE id = ?";
        $this->db->query($sql, array($amount, $id));
    }

    function getPaymentListForGoodsReceiptView(int $id): array
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

}
