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
        if ($this->input->post('datatable') == true) {
            jout(["data" => $this->Goods_receipt_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->taskName)) {
            if ($this->json->taskName == "update_doc_status") jout($this->Goods_receipt_m->updateStatus());
            if ($this->json->taskName == "item_deleted") jout($this->Goods_receipt_m->deleteRecordPaymentReceipt($this->json->id));
            if ($this->json->taskName == "got_a_receipt") jout($this->Goods_receipt_m->gotConfirmedPaymentReceipt($this->json->id));
            return;
        }

        redirect('accounting/buy/goods_receipt');
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
        // if (isset($this->json->task)) {
        //     if ($this->json->task == "load_items") jout($this->Purchase_order_m->items());
        //     if ($this->json->task == "update_doc") jout($this->Purchase_order_m->updateDoc());
        //     if ($this->json->task == "delete_item") jout($this->Purchase_order_m->deleteItem());
        //     return;
        // }

        if (isset($this->json->task) && !empty($this->json->task)) {
            if ($this->json->task == 'load_items') jout($this->Goods_receipt_m->items());
            if ($this->json->task == 'update_doc') jout($this->Goods_receipt_m->updateDoc());
            return;
        }

        if (empty($this->uri->segment(3))) {
            redirect(get_uri('accounting/buy'));
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->uri->segment(3));
        if ($data['status'] != 'success') {
            redirect(get_uri('accounting/buy'));
            return;
        }

        $data['active_module'] = 'goods_receipt';
        $data['created'] = $this->Users_m->getInfo($data['created_by']);
        $data['supplier'] = $this->Bom_suppliers_model->getInfo($data['supplier_id']);
        $data['supplier_contact'] = $this->Bom_suppliers_model->getContactInfo($data['supplier_id']);
        $data['print_gr_url'] = get_uri('goods_receipt/print_goods_receipt/' . str_replace("=", "", base64_encode($data['doc_id'] . ':' . $data['doc_number'])));
        $data['print_pv_url'] = get_uri('goods_receipt/print_payment_voucher/' . str_replace("=", "", base64_encode($data['doc_id'] . ':' . $data['doc_number'])));

        // var_dump(arr($data)); exit();
        $this->template->rander('goods_receipt/view', $data);
    }

    function print_goods_receipt()
    {
        $this->data['doc'] = $doc = $this->Goods_receipt_m->getEdoc($this->uri->segment(3), null);
        if ($doc['status'] != 'success') redirect('forbidden');

        $this->data['additional_style'] = 'style="width: 30%;"';
        $this->data['docmode'] = 'private_print';
        
        // var_dump(arr($this->data)); exit();
        $this->load->view('edocs/goods_receipt', $this->data);
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
            if ($this->json->task == 'gen_sharekey') jout($this->Purchase_order_m->genShareKey());
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->input->post('doc_id'));
        $this->load->view('goods_receipt/share', $data);
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
