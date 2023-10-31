<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Goods_receipt extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!$this->Permission_m->access_purchase_request) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri('accounting/buy'));
        }

        $this->load->model('Purchase_order_m');
        $this->load->model('Goods_receipt_m');
        $this->load->model('Bom_materials_model');
    }

    function example()
    {
        $suppliers = $this->Bom_suppliers_model->getSupplierForGoodsReceipt();
        var_dump(arr($suppliers));
    }

    function index()
    {
        if ($this->input->post("datatable") == true) {
            jout(["data" => $this->Goods_receipt_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->taskName)) {
            if ($this->json->taskName == "update_doc_status") jout($this->Goods_receipt_m->updateStatus());
            if ($this->json->taskName == "item_deleted") jout($this->Goods_receipt_m->deleteRecordPaymentReceipt($this->json->id));
            if ($this->json->taskName == "got_a_receipt") jout($this->Goods_receipt_m->gotConfirmedPaymentReceipt($this->json->id));
            return;
        }

        redirect("accounting/buy/goods_receipt");
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save_doc") jout($this->Goods_receipt_m->saveDoc());
            if ($this->json->task == "get_po_list") jout($this->Goods_receipt_m->getPurchaseOrderBySupplierId());
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->input->post("id"));

        // var_dump(arr($data)); exit();
        $this->load->view('goods_receipt/addedit', $data);
    }

    function view()
    {
        if (isset($this->json->task) && !empty($this->json->task)) {
            if ($this->json->task == 'load_items') jout($this->Goods_receipt_m->items());
            if ($this->json->task == 'update_doc') jout($this->Goods_receipt_m->updateDoc());
            return;
        }

        if (empty($this->uri->segment(3))) {
            redirect(get_uri('accounting/buy/goods_receipt'));
            return;
        }

        $data["active_module"] = "goods_receipt";

        $data["gr_id"] = $this->uri->segment(3);
        $data["gr_info"] = $this->Goods_receipt_m->dev2_getGoodsReceiptInfoById($data["gr_id"]);
        $data["gr_info"]->total_in_text = "(" . numberToText($data["gr_info"]->total) . ")";
        if (!empty($data["gr_info"]->reference_list)) {
            $data["gr_info"]->references = (array) json_decode($data["gr_info"]->reference_list);
            foreach ($data["gr_info"]->references as $po_no) {
                $data["gr_info"]->references_link[] = anchor(
                    get_uri("purchase_order/view/" . $this->Goods_receipt_m->dev2_getPurchaseOrderIdByPurchaseOrderNo($po_no)),
                    $po_no,
                    array(
                        "target" => "_blank"
                    )
                );
            }

            $data["gr_info"]->references_links = implode(", ", $data["gr_info"]->references_link);
        } else {
            $data["gr_info"]->references_links = anchor(
                get_uri("purchase_order/view/" . $this->Goods_receipt_m->dev2_getPurchaseOrderIdByPurchaseOrderNo($data["gr_info"]->reference_number)),
                $data["gr_info"]->reference_number,
                array(
                    "target" => "_blank"
                )
            );
        }

        $data["gr_detail"] = $this->Goods_receipt_m->dev2_getGoodsReceiptDetailByHeaderId($data["gr_id"]);
        $data["created"] = $this->Users_m->getInfo($data["gr_info"]->created_by);
        $data["approved"] = $this->Users_m->getInfo($data["gr_info"]->approved_by);
        $data["supplier"] = $this->Bom_suppliers_model->getInfo($data["gr_info"]->supplier_id);
        $data["supplier_contact"] = $this->Bom_suppliers_model->getContactInfo($data["gr_info"]->supplier_id);
        $data["print_url"] = get_uri("goods_receipt/print_goods_receipt/" . str_replace("=", "", base64_encode($data["gr_info"]->id . ":" . $data["gr_info"]->doc_number)));

        // var_dump(arr($data)); exit();
        $this->template->rander("goods_receipt/view", $data);
    }

    function print_goods_receipt()
    {
        $this->data["doc"] = $this->Goods_receipt_m->getEdoc($this->uri->segment(3), null);
        $this->data["og_title"] = get_setting("company_name") . " - " . $this->data["doc"]["doc_number"];
        if ($this->data["doc"]["status"] != "success") {
            redirect("forbidden");
        }

        $this->data["additional_style"] = 'style="width: 30%;"';
        
        // var_dump(arr($this->data)); exit();
        $this->load->view("edocs/goods_receipt", $this->data);
    }

    function print_payment_voucher()
    {
        $this->data['doc'] = $doc = $this->Goods_receipt_m->getEdoc($this->uri->segment(3), null);
        if ($doc['status'] != 'success') redirect('forbidden');

        $this->data['additional_style'] = 'style="width: 30%;"';
        $this->data['docmode'] = 'private_print';

        // var_dump(arr($this->data)); exit();
        $this->load->view('edocs/payment_voucher', $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Purchase_order_m->undoDoc());
            return;
        }

        jout($this->Purchase_order_m->deleteDoc());
    }

    function items()
    {
        jout($this->Purchase_order_m->items());
    }

    function item()
    {
        $post = $this->input->post();
        $json = $this->json;

        if (isset($this->json->task)) {
            if ($this->json->task == 'save') jout($this->Purchase_order_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get('task') != null) {
            if ($this->input->get('task') == "suggest_products") {
                if ($this->input->get('type') == '3') {
                    $sprows = $this->Products_m->getRows();
                    if (!empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ['id' => $sprow->id, 'text' => $sprow->title, 'description' => $sprow->description, 'unit' => $sprow->unit_type, 'price' => $sprow->rate];
                        }
                    }
                } elseif ($this->input->get('type') == '1') {
                    $sprows = $this->Bom_materials_model->getRows();
                    if (!empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ['id' => $sprow->id, 'text' => $sprow->name . ' - ' . $sprow->production_name, 'description' => $sprow->description, 'unit' => $sprow->unit, 'price' => 0];
                        }
                    }
                } else {
                    $suggestion[] = array('id' => '+', 'text' => '+ ' . lang('create_new_item'));
                }
                jout($suggestion);
            }
            return;
        }

        $data = $this->Goods_receipt_m->item();

        // var_dump(arr($data)); exit();
        $this->load->view('goods_receipt/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "gen_sharekey") jout($this->Goods_receipt_m->genShareKey());
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->input->post("doc_id"));

        // var_dump(arr($data)); exit();
        $this->load->view("goods_receipt/share", $data);
    }

    function record_payment()
    {
        $post = $this->input->post();
        $data = $this->Goods_receipt_m->getDoc($post['doc_id']);
        $data['payments_dropdown'] = $this->Goods_receipt_m->payments_method();

        // var_dump(arr($post)); exit();
        $this->load->view('goods_receipt/addedit_payment', $data);
    }

    function payments_save()
    {
        $json = $this->json;

        $data = array(
            'pv_id' => $json->documentId,
            'date' => $this->DateCaseConvert($json->paymentDate),
            'amount' => $json->paymentAmount,
            'payment_id' => $json->paymentMethodId,
            'type_name' => $json->paymentMethodName,
            'type_description' => $json->paymentMethodDescription,
            'created_by' => $this->login_user->id
        );

        $insert_id = $this->Goods_receipt_m->postPaymentForGoodsReceipt($data);
        if (isset($insert_id) && !empty($insert_id)) {
            $this->Goods_receipt_m->postPayAmountForGoodsReceiptHeader($json->documentId, $json->paymentAmount);
        }

        echo json_encode(array('status' => 'success', 'id' => $insert_id, 'data' => $data, 'post' => $json));
    }

    function payments_items($id)
    {
        $data = array();

        if (isset($id) && !empty($id)) {
            $data = $this->Goods_receipt_m->getPaymentListForGoodsReceiptView($id);
        }
        echo json_encode(array('items' => $data));
    }

    function payments_dropdown()
    {
        $payments_method = $this->Goods_receipt_m->payments_method();
        var_dump(arr($payments_method));
    }

    function addnew()
    {
        $view_data = [];

        $view_data["supplier_dropdown"] = $this->Goods_receipt_m->dev2_getSupplieHavePurchaseOrderApproved();
        $view_data["project_dropdown"] = $this->Goods_receipt_m->dev2_getProjectReferByProjectOpen();

        // var_dump(arr($view_data)); exit();
        $this->load->view("goods_receipt/addnew", $view_data);
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

        $data = $this->Goods_receipt_m->dev2_getPurchaseOrderListBySupplierId($json->supplier_id);
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

        $data = $this->Goods_receipt_m->dev2_getPurchaseOrderListBySupplierIdEdit($json->document_id, $json->supplier_id);
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

    function editnew()
    {
        $post = $this->input->post();
        $view_data = [];

        if (!$post["id"]) {
            $view_data["status"] = "success";
            $view_data["message"] = "500 Internal server error.";
            
            $this->load->view("goods_receipt/editnew", $view_data);
            return;
        }

        $view_data["header_data"] = $this->Goods_receipt_m->dev2_getGoodsReceiptHeaderByPvId($post["id"]);
        if ($view_data["header_data"]->project_id != 0) {
            $view_data["header_data"]->project_name = $this->Goods_receipt_m->dev2_getProjectNameByProjectId($view_data["header_data"]->project_id);
        }
        if ($view_data["header_data"]->supplier_id != 0) {
            $view_data["header_data"]->supplier_name = $this->Goods_receipt_m->dev2_getSupplierNameBySupplierId($view_data["header_data"]->supplier_id);
        }

        $view_data["detail_data"] = $this->Goods_receipt_m->dev2_getGoodsReceiptDetailByPvId($post["id"]);
        $view_data["supplier_dropdown"] = $this->Goods_receipt_m->dev2_getSupplieHavePurchaseOrderApproved();
        $view_data["project_dropdown"] = $this->Goods_receipt_m->dev2_getProjectReferByProjectOpen();

        // var_dump(arr($view_data)); exit();
        $this->load->view("goods_receipt/editnew", $view_data);
    }

    function addnew_save()
    {
        $post = $this->input->post();
        $result = [
            "success" => true,
            "data" => $post,
            "message" => lang("gr_save_succeed")
        ];

        // verify po_item_id
        if (empty($post["po_item_id"])) {
            $result["success"] = false;
            $result["message"] = lang("gr_no_item_select");
            echo json_encode($result);

            return;
        }

        foreach ($post["po_item_id"] as $item) {
            if ($item == "") {
                $result["success"] = false;
                $result["message"] = lang("gr_incomplete_info");
                echo json_encode($result);

                return;
            }
        }

        $origin_po_item_id = $post["po_item_id"];
        $unique_po_item_id = array_unique($origin_po_item_id);

        if (sizeof($origin_po_item_id) !== sizeof($unique_po_item_id)) {
            $result["success"] = false;
            $result["message"] = lang("gr_item_duplicated");
            echo json_encode($result);

            return;
        }

        // verify status_qty
        if (sizeof($post["status_qty"])) {
            foreach ($post["status_qty"] as $item) {
                if ($item == "N") {
                    $result["success"] = false;
                    $result["message"] = lang("gr_incorrect_qty");
                    echo json_encode($result);

                    return;
                }
            }
        }

        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result["post_result"] = $this->Goods_receipt_m->dev2_postGoodsReceiptByCreateForm($post);

        echo json_encode($result);
    }

    function editnew_save()
    {
        $post = $this->input->post();
        $result = [
            "success" => true,
            "data" => $post,
            "message" => lang("gr_save_succeed")
        ];

        // verify po_item_id
        if (empty($post["po_item_id"])) {
            $result["success"] = false;
            $result["message"] = lang("gr_no_item_select");
            echo json_encode($result);

            return;
        }

        foreach ($post["po_item_id"] as $item) {
            if ($item == "") {
                $result["success"] = false;
                $result["message"] = lang("gr_incomplete_info");
                echo json_encode($result);

                return;
            }
        }

        $origin_po_item_id = $post["po_item_id"];
        $unique_po_item_id = array_unique($origin_po_item_id);

        if (sizeof($origin_po_item_id) !== sizeof($unique_po_item_id)) {
            $result["success"] = false;
            $result["message"] = lang("gr_item_duplicated");
            echo json_encode($result);

            return;
        }

        // verify status_qty
        if (sizeof($post["status_qty"])) {
            foreach ($post["status_qty"] as $item) {
                if ($item == "N") {
                    $result["success"] = false;
                    $result["message"] = lang("gr_incorrect_qty");
                    echo json_encode($result);

                    return;
                }
            }
        }

        $post["doc-date"] = $this->DateCaseConvert($post["doc-date"]);
        $result["post_result"] = $this->Goods_receipt_m->dev2_postGoodsReceiptByCreateFormEdit($post);

        echo json_encode($result);
    }

    public function dev2_serializeTypeForGoodsReceiptItems()
    {
        $this->Goods_receipt_m->dev2_serializeTypeForGoodsReceiptItems();
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

    private function TestCaseSaleFg()
    {   
        $sale_info['sale_id'] = 1;
        $sale_info['sale_type'] = 'IV';
        $sale_info['sale_document'] = 'IV2023080001';
        $sale_info['project_id'] = 1;
        $sale_info['created_by'] = 1;
        $sale_info['items'] = [
            ['id' => 1, 'item_id' => 26, 'ratio' => 5000]
        ];

        $testModel = $this->Bom_item_stocks_model->processFinishedGoodsSaleTestCase($sale_info);
        var_dump(arr($testModel));
    }

    private function TestCaseSaleFgVoid()
    {
        $sale_info['sale_id'] = 1;
        $sale_info['sale_type'] = 'IV';
        $sale_info['sale_document'] = 'IV2023080001';
        $sale_info['project_id'] = 1;
        $sale_info['created_by'] = 1;
        $sale_info['items'] = [
            ['id' => 1, 'item_id' => 26, 'ratio' => 5000]
        ];

        $testModel = $this->Bom_item_stocks_model->cancelFinishedGoodsSaleTestCase($sale_info);
        var_dump(arr($testModel));
    }

}
