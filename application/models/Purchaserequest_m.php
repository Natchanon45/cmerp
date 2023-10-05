<?php
class Purchaserequest_m extends CI_Model
{
    function __construct()
    {
        $this->load->model("Material_m");
        $this->load->model("Bom_suppliers_model");
    }

    function row($prid)
    {
        $prrow = $this->db->select("*")
            ->from("purchaserequests")
            ->where("id", $prid)
            ->get()->row();

        if (empty($prrow)) return null;

        return $prrow;
    }

    function updateStatus($prid, $update_to_status)
    {
        $prrow = $this->row($prid);

        if ($this->Permission_m->approve_purchase_request != true) {
            return ["process" => "fail", "Don't have permission"];
        }

        if (empty($prrow)) {
            return ["process" => "fail", "Not found PR"];
        }

        if ($update_to_status == 3) {
            if ($prrow->status_id != 1) {
                return ["process" => "fail", "message" => "Approval fail"];
            }

            $this->db->where('id', $prid);
            $this->db->update('purchaserequests', ["status_id" => 3]);

            return ["process" => "success", "message" => "Successfully Approved"];
        }

        if ($update_to_status == 4) {
            if ($prrow->status_id != 1) {
                return ["process" => "fail", "message" => "Disapproval fail"];
            }

            $this->db->where('id', $prid);
            $this->db->update('purchaserequests', ["status_id" => 4]);

            return ["process" => "success", "message" => "Successfully Disapproved"];
        }
    }

    function jLackedMaterial()
    {
        $db = $this->db;

        $bpimrows = $db->select("material_id, SUM(ratio) AS TOTAL_LACKED")
            ->from("bom_project_item_materials")
            ->where("stock_id IS NULL")
            ->group_by("material_id")
            ->get()->result();

        $materials = [];

        foreach ($bpimrows as $bpimrow) {
            $material_id = $bpimrow->material_id;
            $material_code = $this->Material_m->getCode($material_id);
            $total_lacked = abs($bpimrow->TOTAL_LACKED);

            $bpimrows = $db->select("project_item_id")
                ->from("bom_project_item_materials")
                ->where("material_id", $material_id)
                ->group_by("project_item_id")
                ->get()->result();

            $lacked_from_project_ids = [];
            $lacked_from_project_html = "";

            if (!empty($bpimrows)) {
                foreach ($bpimrows as $bpimrow) {
                    $project_item_id = $bpimrow->project_item_id;

                    $bpirow = $db->select("project_id")
                        ->from("bom_project_items")
                        ->where("id", $project_item_id)
                        ->get()->row();

                    if (empty($bpirow)) continue;
                    if (in_array($bpirow->project_id, $lacked_from_project_ids)) continue;

                    array_push($lacked_from_project_ids, $bpirow->project_id);
                    $lacked_from_project_html .= "<span class='lacked_from_project'>" . $this->Projects_m->getName($bpirow->project_id) . "</span>";
                }
            }

            $m = [];
            $m[0] = $material_id;
            $m[1] = '<a href="javascript:;" onclick="javascript:jQuery(\'.prj_' . $bpimrow->project_item_id . '\').toggle();">' . $this->Material_m->getCode($material_id) . '</a>';
            $m[2] = $lacked_from_project_html;
            $m[3] = $total_lacked;
            $m[4] = '<button type="button" class="btn btn-danger pull-right btn-pr" id="btn-pr1"><i class="fa fa-shopping-cart"></i> ' . lang('request_purchasing_materials') . '</button>';

            $materials[] = $m;
        }

        return json_encode(["data" => array_values($materials), "success" => 1, "message" => "Success"]);
    }

    function dev2_getPrStatusDropdown()
    {
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("status") . " --"
        );
        $data[] = array(
            "id" => "W",
            "text" => lang('pr_pending')
        );
        $data[] = array(
            "id" => "A",
            "text" => lang('pr_approved')
        );
        $data[] = array(
            "id" => "R",
            "text" => lang('pr_rejected')
        );

        return $data;
    }

    function dev2_getPoStatusDropdown()
    {
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("status") . " --"
        );
        $data[] = array(
            "id" => "W",
            "text" => lang('pr_pending')
        );
        $data[] = array(
            "id" => "A",
            "text" => lang('pr_approved')
        );
        $data[] = array(
            "id" => "X",
            "text" => lang('pr_cancelled')
        );

        return $data;
    }

    function dev2_getGrStatusDropdown()
    {
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang("status") . " --"
        );
        $data[] = array(
            "id" => "N",
            "text" => lang('payments_draft')
        );
        $data[] = array(
            "id" => "W",
            "text" => lang('payments_waiting')
        );
        $data[] = array(
            "id" => "P",
            "text" => lang('payments_partial')
        );
        $data[] = array(
            "id" => "C",
            "text" => lang('payments_completed')
        );

        return $data;
    }

    function dev2_getPrTypeDropdown()
    {
        $data[] = array(
            "id" => "",
            "text" => "-- " . lang('pr_type') . " --"
        );
        $data[] = array(
            "id" => "1",
            "text" => lang('direct_material')
        );
        // $data[] = array(
        //     "id" => "2", "text" => lang('indirect_material')
        // );
        $data[] = array(
            "id" => "3",
            "text" => lang('finised_goods')
        );
        // $data[] = array(
        //     "id" => "4", "text" => lang('assets')
        // );
        $data[] = array(
            "id" => "5",
            "text" => lang('services')
        );
        // $data[] = array(
        //     "id" => "6", "text" => lang('expenses')
        // );
        // $data[] = array(
        //     "id" => "7", "text" => lang('mixed_purchase')
        // );

        return $data;
    }

    function dev2_getPrTypeById($id)
    {
        $type = array(
            "1" => "direct_material",
            "2" => "indirect_material",
            "3" => "finised_goods",
            "4" => "assets",
            "5" => "services",
            "6" => "expenses",
            "7" => "mixed_purchase"
        );
        return lang($type[$id]);
    }

    function indexDataSet()
    {
        $this->db->select('*')->from('pr_header');

        if ($this->input->post('status') != null) {
            $this->db->where('status', $this->input->post('status'));
        }

        if ($this->input->post('pr_type') != null) {
            $this->db->where('pr_type', $this->input->post('pr_type'));
        }

        if ($this->input->post('start_date') != null && $this->input->post('end_date')) {
            $this->db->where('requisition_date >=', $this->input->post('start_date'));
            $this->db->where('requisition_date <=', $this->input->post('end_date'));
        }

        if ($this->input->post('supplier_id') != null) {
            $this->db->where('supplier_id', $this->input->post('supplier_id'));
        }

        $this->db->where('deleted_flag', 0);

        $dataSet = array();
        $result = $this->db->order_by('pr_no', 'desc')->get()->result();
        foreach ($result as $item) {
            $dataSet[] = $this->getIndexDataSetHTML($item);
        }

        return $dataSet;
    }

    function getIndexDataSetHTML($item)
    {
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

            $url = get_avatar($user->image);
            $span = '<span class="avatar avatar-xs mr10"><img src="' . $url . '" alt=""></span>' . $user->first_name . ' ' . $user->last_name;
            $request_by = get_team_member_profile_link($user->id, $span);
        }

        $supplier = $this->Bom_suppliers_model->getInfo($item->supplier_id);

        $data = array(
            "<a href='" . get_uri('purchase_request/view/' . $item->id) . "'>" . convertDate($item->requisition_date, true) . "</a>",
            "<a href='" . get_uri('purchase_request/view/' . $item->id) . "'>" . $item->pr_no . "</a>",
            $item->pr_type ? $this->dev2_getPrTypeById($item->pr_type) : '-',
            "<a href='" . get_uri('stock/supplier_view/' . $item->supplier_id) . "'>" . mb_strimwidth($supplier['company_name'], 0, 60, '...') . "</a>",
            $request_by,
            $status,
            "<a data-post-id='" . $item->id . "' data-action-url='" . get_uri('purchase_request/addedit') . "' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
        );

        return $data;
    }

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
            $this->data["doc_date"] = $query->requisition_date;
            $this->data["supplier_id"] = $query->supplier_id;
            $this->data["created_by"] = $query->created_by;
            $this->data["created_datetime"] = $query->created_date;
            $this->data["approved_by"] = $query->approved_by;
            $this->data["approved_datetime"] = $query->approved_date;
            $this->data["doc_status"] = $query->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

}
