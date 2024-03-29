<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_voucher extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if ($this->Permission_m->access_purchase_request != true) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/buy"));
        }

        $this->load->model('Purchase_request_m');
        $this->load->model('Purchase_order_m');
        $this->load->model('Payment_voucher_m');
        $this->load->model('Goods_receipt_m');
        $this->load->model('Bom_materials_model');
        $this->load->model('Account_category_model');
    }

    function index()
    {
        if ($this->input->post("datatable") == true) {
            jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->taskName)) {
            if ($this->json->taskName == "update_doc_status") jout($this->Payment_voucher_m->updateStatus());
            if ($this->json->taskName == "item_deleted") jout($this->Payment_voucher_m->deleteRecordPaymentReceipt($this->json->id));
            if ($this->json->taskName == "got_a_receipt") jout($this->Payment_voucher_m->gotConfirmedPaymentReceipt($this->json->id));
            return;
        }

        redirect("accounting/buy/payment_voucher");
    }

    function test()
    {
        jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
        return;
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save_doc")
                jout($this->Payment_voucher_m->saveDoc());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("id"));

        $this->load->view('payment_voucher/addedit', $data);
    }

    function partial_payment_type()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status")
                jout($this->Payment_voucher_m->updateStatus());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success")
            return;

        $this->load->view('payment_voucher/partial_payment_type', $data);
    }

    function view($id = 0)
    {
        if ($id == 0 || empty($id)) {
            redirect(get_uri("accounting/buy/payment_voucher"));
            return;
        }

        $data["active_module"] = "payment_voucher";
        
        $data["pv_id"] = $id;
        $data["pv_info"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderById($data["pv_id"]);
        $data["pv_info"]->total_in_text = "(" . numberToText($data["pv_info"]->total) . ")";
        if (!empty($data["pv_info"]->reference_list)) {
            $data["pv_info"]->references = (array) json_decode($data["pv_info"]->reference_list);
            foreach ($data["pv_info"]->references as $po_no) {
                $data["pv_info"]->references_link[] = anchor(
                    get_uri("purchase_order/view/" . $this->Goods_receipt_m->dev2_getPurchaseOrderIdByPurchaseOrderNo($po_no)),
                    $po_no,
                    array("target" => "_blank")
                );
            }

            $data["pv_info"]->references_links = implode(", ", $data["pv_info"]->references_link);
        } else {
            $data["pv_info"]->references_links = "";

            if (isset($data["pv_info"]->reference_number) && !empty($data["pv_info"]->reference_number)) {
                $data["pv_info"]->references_links = anchor(
                    get_uri("purchase_order/view/" . $this->Goods_receipt_m->dev2_getPurchaseOrderIdByPurchaseOrderNo($data["pv_info"]->reference_number)),
                    $data["pv_info"]->reference_number,
                    array("target" => "_blank")
                );
            }
        }
        
        $data["pv_detail"] = $this->Payment_voucher_m->dev2_getPaymentVoucherDetailByHeaderId($data["pv_id"]);
        $data["pv_method"] = $this->Payment_voucher_m->dev2_getPaymentMethodItemsById($data["pv_id"]);
        $data["created_by"] = $this->Users_m->getInfo($data["pv_info"]->created_by);
        $data["approved_by"] = $this->Users_m->getInfo($data["pv_info"]->approved_by);
        $data["supplier"] = $this->Suppliers_m->getInfo($data["pv_info"]->supplier_id);
        $data["supplier_contact"] = $this->Suppliers_m->getContactInfo($data["pv_info"]->supplier_id);
        $data["print_url"] = get_uri("payment_voucher/print/" . str_replace("=", "", base64_encode($data["pv_info"]->id . ':' . $data["pv_info"]->doc_number)));

        $data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $data["bom_supplier_read"] = true;
        }

        // var_dump(arr($data)); exit();
        $this->template->rander("payment_voucher/view", $data);
    }

    function print()
    {
        $this->data["doc"] = $this->Payment_voucher_m->getEdoc($this->uri->segment(3), null);
        $this->data["og_title"] = get_setting("company_name") . " - " . $this->data["doc"]["doc_number"];
        if ($this->data["doc"]["status"] != "success") {
            redirect("forbidden");
        }

        $this->data["additional_style"] = 'style="width: 30%;"';
        $this->data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $this->data["bom_supplier_read"] = true;
        }

        // var_dump(arr($this->data)); exit();
        $this->load->view("edocs/payment_voucher", $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Payment_voucher_m->undoDoc());
            return;
        }

        jout($this->Payment_voucher_m->deleteDoc());
    }

    function items()
    {
        jout($this->Payment_voucher_m->items());
    }

    function item()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save")
                jout($this->Payment_voucher_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get("task") != null) {
            if ($this->input->get("task") == "suggest_products") {
                $sprows = $this->Products_m->getRows();
                if (!empty($sprows)) {
                    foreach ($sprows as $sprow) {
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description" => $sprow->description, "unit" => $sprow->unit_type, "price" => $sprow->rate];
                    }
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Payment_voucher_m->item();

        $this->load->view('payment_voucher/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "gen_sharekey") jout($this->Payment_voucher_m->genShareKey());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("doc_id"));

        // var_dump(arr($data)); exit();
        $this->load->view("payment_voucher/share", $data);
    }

    function record_payment()
    {
        $post = $this->input->post();
        
        $data["header_info"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderById($post["doc_id"]);
        $data["header_info"]->remain_amount = $data["header_info"]->payment_amount - $data["header_info"]->pay_amount;
        $data["payments_dropdown"] = $this->Goods_receipt_m->payments_method();

        // var_dump(arr($data)); exit();
        $this->load->view("payment_voucher/addedit_payment", $data);
    }

    function payments_save()
    {
        $json = $this->json;

        $data = [
            "pv_id" => $json->documentId,
            "date" => $this->DateCaseConvert($json->paymentDate),
            "amount" => $json->paymentAmount,
            "payment_id" => $json->paymentMethodId,
            "type_name" => $json->paymentMethodName,
            "type_description" => $json->paymentMethodDescription,
            "created_by" => $this->login_user->id
        ];

        $insert_id = $this->Payment_voucher_m->postPaymentForPaymentVoucher($data);
        if (isset($insert_id) && !empty($insert_id)) {
            $this->Payment_voucher_m->postPayAmountForPaymentVoucherHeader($json->documentId, $json->paymentAmount);
        }

        $result = [
            "success" => true,
            "status" => "success",
            "post" => $json,
            "data" => $data
        ];
        
        echo json_encode($result);
    }

    function addnew_no_po()
    {
        $view_data = [];

        $view_data["supplier_dropdown"] = $this->Payment_voucher_m->dev2_getSupplierList();
        $view_data["project_dropdown"] = $this->Payment_voucher_m->dev2_getProjectReferByProjectOpen();
        $view_data["account_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["account_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());

        // Get rm, fg, sfg list
        $view_data["rm_dropdown"] = $this->get_rm_dropdown();
        $view_data["fg_dropdown"] = $this->get_fg_dropdown();
        $view_data["sfg_dropdown"] = $this->get_sfg_dropdown();
        $view_data["sv_dropdown"] = $this->get_sv_dropdown();

        $view_data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $view_data["bom_supplier_read"] = true;
        }
        
        // var_dump(arr($view_data)); exit();
        $this->load->view("payment_voucher/addnew_no_po", $view_data);
    }

    function editnew_no_po()
    {
        $view_data = [];

        $view_data["supplier_dropdown"] = $this->Payment_voucher_m->dev2_getSupplierList();
        $view_data["project_dropdown"] = $this->Payment_voucher_m->dev2_getProjectReferByProjectOpen();
        $view_data["account_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["account_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());

        // Get rm, fg, sfg list
        $view_data["rm_dropdown"] = $this->get_rm_dropdown();
        $view_data["fg_dropdown"] = $this->get_fg_dropdown();
        $view_data["sfg_dropdown"] = $this->get_sfg_dropdown();
        $view_data["sv_dropdown"] = $this->get_sv_dropdown();

        $view_data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $view_data["bom_supplier_read"] = true;
        }

        $id = $this->input->post("id");
        if ($id) {
            $view_data["header_data"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderById($id);
            $view_data["detail_data"] = $this->Payment_voucher_m->dev2_getPaymentVoucherDetailByHeaderId($id);
        }

        // var_dump(arr($view_data)); exit();
        $this->load->view("payment_voucher/editnew_no_po", $view_data);
    }

    function addnew()
    {
        $view_data = [];

        $view_data["supplier_dropdown"] = $this->Payment_voucher_m->dev2_getSupplieHavePurchaseOrderApproved();
        $view_data["project_dropdown"] = $this->Payment_voucher_m->dev2_getProjectReferByProjectOpen();
        $view_data["account_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["account_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());

        $view_data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $view_data["bom_supplier_read"] = true;
        }

        // var_dump(arr($view_data)); exit();
        $this->load->view("payment_voucher/addnew", $view_data);
    }

    function editnew()
    {
        $post = $this->input->post();
        $view_data = [];

        if (!$post["id"]) {
            $view_data["status"] = "success";
            $view_data["message"] = "500 Internal server error.";
            
            $this->load->view("payment_voucher/editnew", $view_data);
            return;
        }

        $view_data["header_data"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderByPvId($post["id"]);
        if ($view_data["header_data"]->project_id != 0) {
            $view_data["header_data"]->project_name = $this->Payment_voucher_m->dev2_getProjectNameByProjectId($view_data["header_data"]->project_id);
        }
        if ($view_data["header_data"]->supplier_id != 0) {
            $view_data["header_data"]->supplier_name = $this->Payment_voucher_m->dev2_getSupplierNameBySupplierId($view_data["header_data"]->supplier_id);
        }

        $view_data["detail_data"] = $this->Payment_voucher_m->dev2_getPaymentVoucherDetailByPvId($post["id"]);
        $view_data["supplier_dropdown"] = $this->Payment_voucher_m->dev2_getSupplieHavePurchaseOrderApproved();
        $view_data["project_dropdown"] = $this->Payment_voucher_m->dev2_getProjectReferByProjectOpen();

        $view_data["bom_supplier_read"] = false;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $view_data["bom_supplier_read"] = true;
        }

        $view_data["account_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["account_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());

        if ($view_data["header_data"]->account_category_id) {
            $view_data["account_category_info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_category", "id", $view_data["header_data"]->account_category_id)[0];
            $view_data["account_secondary_info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_secondary", "id", $view_data["account_category_info"]->secondary_id)[0];
        }

        // var_dump(arr($view_data)); exit();
        $this->load->view("payment_voucher/editnew", $view_data);
    }

    function addnew_no_po_save()
    {
        validate_submitted_data(
            array(
                "doc-date" => "required",
                "supplier-id" => "required"
            )
        );

        $post = $this->input->post();
        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result = $this->Payment_voucher_m->dev2_postPaymentVoucherNonPurchaseOrder($post);

        echo json_encode($result);
    }

    function editnew_no_po_save()
    {
        validate_submitted_data(
            array(
                "document-id" => "required",
                "doc-date" => "required",
                "supplier-id" => "required"
            )
        );

        $post = $this->input->post();
        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result = $this->Payment_voucher_m->dev2_postPaymentVoucherNonPurchaseOrderEdit($post);

        echo json_encode($result);
    }

    function addnew_save()
    {
        validate_submitted_data(
            array(
                "doc-date" => "required",
                "supplier-id" => "required"
            )
        );

        $post = $this->input->post();
        $result = [
            "success" => true,
            "data" => $post,
            "message" => lang("pv_save_succeed")
        ];

        // verify po_item_id
        if (empty($post["po_item_id"])) {
            $result["success"] = false;
            $result["message"] = lang("pv_no_item_select");
            echo json_encode($result);

            return;
        }

        foreach ($post["po_item_id"] as $item) {
            if ($item == "") {
                $result["success"] = false;
                $result["message"] = lang("pv_incomplete_info");
                echo json_encode($result);

                return;
            }
        }

        $origin_po_item_id = $post["po_item_id"];
        $unique_po_item_id = array_unique($origin_po_item_id);

        if (sizeof($origin_po_item_id) !== sizeof($unique_po_item_id)) {
            $result["success"] = false;
            $result["message"] = lang("pv_item_duplicated");
            echo json_encode($result);

            return;
        }

        // verify status_qty
        if (sizeof($post["status_qty"])) {
            foreach ($post["status_qty"] as $item) {
                if ($item == "N") {
                    $result["success"] = false;
                    $result["message"] = lang("pv_incorrect_qty");
                    echo json_encode($result);

                    return;
                }
            }
        }

        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result["post_result"] = $this->Payment_voucher_m->dev2_postPaymentVoucherByCreateForm($post);

        echo json_encode($result);
    }

    function editnew_save()
    {
        validate_submitted_data(
            array(
                "doc-date" => "required",
                "supplier-id" => "required"
            )
        );

        $post = $this->input->post();
        $result = [
            "success" => true,
            "data" => $post,
            "message" => lang("pv_save_succeed")
        ];

        // verify po_item_id
        if (empty($post["po_item_id"])) {
            $result["success"] = false;
            $result["message"] = lang("pv_no_item_select");
            echo json_encode($result);
            return;
        }

        foreach ($post["po_item_id"] as $item) {
            if ($item == "") {
                $result["success"] = false;
                $result["message"] = lang("pv_incomplete_info");
                echo json_encode($result);
                return;
            }
        }

        $origin_po_item_id = $post["po_item_id"];
        $unique_po_item_id = array_unique($origin_po_item_id);

        if (sizeof($origin_po_item_id) !== sizeof($unique_po_item_id)) {
            $result["success"] = false;
            $result["message"] = lang("pv_item_duplicated");
            echo json_encode($result);
            return;
        }

        // verify status_qty
        if (sizeof($post["status_qty"])) {
            foreach ($post["status_qty"] as $item) {
                if ($item == "N") {
                    $result["success"] = false;
                    $result["message"] = lang("pv_incorrect_qty");
                    echo json_encode($result);
                    return;
                }
            }
        }

        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result["post_result"] = $this->Payment_voucher_m->dev2_postPaymentVoucherByCreateFormEdit($post);

        echo json_encode($result);
    }

    function purchase_order_list()
    {
        $json = $this->json;
        $result = [
            "success" => false,
            "data" => null,
            "length" => 0,
            "supplier_id" => $json->supplier_id
        ];

        $data = $this->Payment_voucher_m->dev2_getPurchaseOrderListBySupplierId($json->supplier_id);
        if (isset($data) && !empty($data)) {
            if (sizeof($data)) {
                $result = [
                    "success" => true,
                    "data" => $data,
                    "length" => sizeof($data),
                    "supplier_id" => $json->supplier_id
                ];
            }
        }

        echo json_encode($result);
    }

    function purchase_order_list_edit()
    {
        $json = $this->json;
        $result = [
            "success" => false,
            "data" => null,
            "length" => 0,
            "supplier_id" => $json->supplier_id
        ];

        $data = $this->Payment_voucher_m->dev2_getPurchaseOrderListBySupplierIdEdit($json->document_id, $json->supplier_id);
        if (isset($data) && !empty($data)) {
            if (sizeof($data)) {
                $result = [
                    "success" => true,
                    "data" => $data,
                    "length" => sizeof($data),
                    "supplier_id" => $json->supplier_id
                ];
            }
        }

        echo json_encode($result);
    }

    private function get_rm_dropdown()
    {
        $dropdown = array();
        $lists = $this->Payment_voucher_m->dev2_getRawMaterialMasterDataList();

        if (sizeof($lists)) {
            foreach ($lists as $list) {
                $dropdown[] = array(
                    "id" => $list->id,
                    "text" => $list->name . " - " . $list->production_name
                );
            }
        }

        return json_encode($dropdown);
    }

    private function get_fg_dropdown()
    {
        $dropdown = array();
        $lists = $this->Payment_voucher_m->dev2_getFinishedGoodsMasterDataList();

        if (sizeof($lists)) {
            foreach ($lists as $list) {
                $dropdown[] = array(
                    "id" => $list->id,
                    "text" => $list->title
                );
            }
        }

        return json_encode($dropdown);
    }

    private function get_sfg_dropdown()
    {
        $dropdown = array();
        $lists = $this->Payment_voucher_m->dev2_getSemiFinishedGoodsMasterDataList();

        if (sizeof($lists)) {
            foreach ($lists as $list) {
                $dropdown[] = array(
                    "id" => $list->id,
                    "text" => $list->title
                );
            }
        }

        return json_encode($dropdown);
    }

    private function get_sv_dropdown()
    {
        $dropdown = array();
        $lists = $this->Payment_voucher_m->dev2_getServicesMasterDataList();

        if (sizeof($lists)) {
            foreach ($lists as $list) {
                $dropdown[] = array(
                    "id" => $list->id,
                    "text" => $list->service_name,
                    "description" => ""
                );
            }
        }

        return json_encode($dropdown);
    }

    private function DateCaseConvert(string $dateInput): string
    {
        // Convert "DD/MM/YYYY" to "YYYY-MM-DD" 
        $dateOutput = '';

        $dateOutput = explode('/', $dateInput);
        $dateOutput = array_reverse($dateOutput);
        $dateOutput = implode('-', $dateOutput);

        return $dateOutput;
    }

}