<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchases_order extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }

        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('purchases_request_model');
        $this->load->admin_model('purchases_order_model');
        $this->load->admin_model('approved_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
        
    }

    /* ------------------------------------------------------------------------- */

    public function index($biller_id = null)
    {
        $this->bpas->checkPermissions('index', NULL,'purchases_order');
		$this->load->admin_model('reports_model');
		if(isset($_GET['d']) != ""){
			$date = $_GET['d'];
			$this->data['date'] = $date;
		}
		$this->data['users'] = $this->reports_model->getStaff();
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || empty($count)) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller_id'] = $biller_id;
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['billers']   = $this->site->getAllCompanies('biller');
            } else {
                $this->data['billers']   = null;
            }
            $this->data['count_billers'] = $count;
            $this->data['user_biller']   = (isset($count) && count($count) == 1) ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
            $this->data['biller_id']     = $biller_id;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }
        $this->data['warehouse_id'] = null;
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['projects']     = $this->site->getAllProject();
        $bc = array(array('link'   => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchase_order')));
        $meta = array('page_title' => lang('purchase_order'), 'bc' => $bc);
        $this->page_construct('purchases_order/index', $meta, $this->data);
    }

    public function getPurchasesOrder($biller_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'purchases_order');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }

        $user_query = $this->input->get('user') ? $this->input->get('user'):null;
    

		if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
		if ($this->input->get('project')) {
            $project = $this->input->get('project');
        } else {
            $project = NULL;
        }
		if ($this->input->get('status')) {
            $status = $this->input->get('status');
        } else {
            $status = NULL;
        }
		if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }
        $detail_link = anchor('admin/purchases_order/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_order_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $view_a5 = anchor('admin/purchases_order/view_a5/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_a5'));
        $edit_link = anchor('admin/purchases_order/edit/$1', '<i class="fa fa-edit"></i>' . lang('edit_purchase_order'), array('class' => 'auth'));
		$auth_link = anchor('admin/purchases_order/approved/$1', '<i class="fa fa-check"></i>' . lang('approved'));
		$unauth_link = anchor('admin/purchases_order/unapproved/$1', '<i class="fa fa-check"></i>' . lang('unapproved'));
		$reject = anchor('admin/purchases_order/rejected/$1', '<i class="fa fa-times" aria-hidden="true"></i> ' . lang('reject'));
		$unreject = anchor('admin/purchases_order/unreject/$1', '<i class="fa fa-check"></i> ' . lang('unreject'));
		$create_link = anchor('admin/purchases/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_stock_in'), array('class' => 'disabled-link'));
		$create_purchase = anchor('admin/purchases_order/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('creat_puchases'), array('class' => 'disabled-link'));
		
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase_order") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases_order/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase_order') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>'
            .'<li>' . $view_a5 . '</li>'.
            (($this->Owner || $this->Admin) ? '<li class="approved">'.$auth_link.'</li>' : ($this->GP['purchases_order-approved'] ? '<li class="approved">'.$auth_link.'</li>' : '')).
            (($this->Owner || $this->Admin) ? '<li class="unapproved">'.$unauth_link.'</li>' : ($this->GP['purchases_order-approved'] ? '<li class="unapproved">'.$unauth_link.'</li>' : '')).
            (($this->Owner || $this->Admin) ? '<li class="reject">'.$reject.'</li>' : ($this->GP['purchases_order-rejected'] ? '<li class="reject">'.$reject.'</li>' : '')).			
			(($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['purchases_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
            (($this->Owner || $this->Admin) ? '<li class="create">'.$create_link.'</li>' : ($this->GP['purchases_order-add'] ? '<li 
                class="create">'.$create_link.'</li>' : '')).
            (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['purchases_order-delete'] ? '<li 
                class="create">'.$delete_link.'</li>' : '')).
        '</ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("
                purchases_order.id, 
                DATE_FORMAT({$this->db->dbprefix('purchases_order')}.date, '%Y-%m-%d %T') as date, 
                companies.company, 
                reference_no, 
                purchase_ref, 
                supplier, 
                eta, 
                order_status, 
                grand_total, 
                purchases_order.status
            ")
            ->from('purchases_order')
            ->join('companies', 'companies.id = purchases_order.biller_id', 'left');
        if ($biller_id) {
            $this->datatables->where_in('purchases_order.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_purchases_order.created_by, '" . $this->session->userdata('user_id') . "')");
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
		if ($user_query) {
			$this->datatables->where('purchases_order.created_by', $user_query);
		}
		if ($supplier) {
			$this->datatables->where('purchases_order.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases_order.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases_order.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases_order').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($project) {
			$this->datatables->like('purchases_order.project_id', $project);
		}
		if ($status) {
			$this->datatables->like('purchases_order.status', $status);
		}
		if ($note) {
			$this->datatables->like('purchases_order.note', $note, 'both');
		}
        if ((!$this->Owner && !$this->Admin) && !$this->session->userdata('show_cost')) {
            $this->datatables->unset_column('grand_total');
        }
        $this->datatables->add_column("Actions", $action, "purchases_order.id");
        echo $this->datatables->generate();
    }
    public function add($purchase_order_id = null, $quote_id = null)
    {
        $this->bpas->checkPermissions();
        if($purchase_order_id){
            $pur_request = $this->purchases_request_model->getPurchaseRequestByID_($purchase_order_id); 
            if($pur_request->status == 'requested'){
                $this->session->set_flashdata('error', lang("purchase_request_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if($pur_request->status == 'reject'){
                $this->session->set_flashdata('error', lang("purchase_request_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if($pur_request->order_status == 'completed'){
                $this->session->set_flashdata('error', lang("purchase_request_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('project', $this->lang->line("project"), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $reference  = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            $reference_purchase_no = $this->input->post('reference_purchase_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            if ($this->Owner || $this->Admin) {
            $deadline = $this->bpas->fld(trim($this->input->post('deadline')));
            } else {
                $deadline = date('Y-m-d H:i:s');
            }
            $biller_id    = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id  = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $payment_term_details    = $this->site->getAllPaymentTermByID($payment_term);
            //$due_date                = $payment_term_details[0]->id ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL;
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $pri_id = $_POST['pri_id'][$r] ? ($_POST['pri_id'][$r] != 'null' ? $_POST['pri_id'][$r] : null) : null;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r];
                $item_qoh = isset($_POST['qoh'][$r]) ? $_POST['qoh'][$r] : 0;
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $product = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        // 'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'description'       => $item_description,
                        'qoh'       => $item_qoh,
                        'supplier_part_no' => $supplier_part_no,
                        'pri_id' => $pri_id,
                    );
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'reference_no' => $reference,
                'purchase_ref' => $reference_purchase_no,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note'          => $note,
                'total'         => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax'   => $product_tax,
                'order_tax_id'  => $this->input->post('order_tax'),
                'order_tax'     => $order_tax,
                'total_tax'     => $total_tax,
                'shipping'      => $this->bpas->formatDecimal($shipping),
                'grand_total'   => $grand_total,
                'status'        => $status,
                'created_by'    => $this->session->userdata('user_id'),
                'payment_term'  => $payment_term,
                'due_date'      => $due_date,
                'deadline'      => date('Y-m-d', strtotime($deadline)),
                'eta'           => $this->bpas->fld(trim($this->input->post('eta'))),
                'currency'      => $this->input->post('currency'),
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->purchases_order_model->addPurchaseorder($purchase_order_id,$data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect('purchases_order');
        } else {
            if ($purchase_order_id) {
                $this->data['quote'] = $this->purchases_request_model->getPurchaseRequestByID($purchase_order_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_request_model->getAllPurchaseRequestItems($purchase_order_id);
                $purchase_order_items = $this->site->getPOI_By_PRID($purchase_order_id);
                //  krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
                    if($purchase_order_items !== false){
                        $key = array_search($item->id, array_column($purchase_order_items, 'pri_id'));
                        if($key !== false){
                            if($item->unit_quantity > $purchase_order_items[$key]->quantity){
                                $item->unit_quantity = $item->unit_quantity - $purchase_order_items[$key]->quantity;
                            } else {
                                $b = true;
                            }
                        } 
                    }
                    if($b == true){
                        continue;
                    }
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $item->real_unit_cost ? $item->real_unit_cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;
                            $crow->description    = $item->description;
                            $crow->qoh    = $item->qoh;
                            $set_price = $this->site->getUnitByProId($crow->id);
                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }
                        $row->pri_id = $item->id;
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $item->real_unit_cost ? $item->real_unit_cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);
                        $units   = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;
                        $row->description    = $item->description;
                        $row->qoh    = $item->qoh;
                        $set_price = $this->site->getUnitByProId($row->id);
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['orderid'] = $purchase_order_id;
                $this->data['purchase'] = $this->purchases_request_model->getPurchaseRequestByID($purchase_order_id);
            }
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $purchase_order_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            //  $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->data['ponumber'] = $this->site->getReference('po');
            $this->data['projects'] = $this->site->getAllProject();
            $this->data['billers']  = $this->site->getAllCompanies('biller');
            if($this->Settings->purchase_request){
                $this->data['purchase_requests'] = $this->purchases_order_model->getRefPurchaseRequests('approved');
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases_order'), 'page' => lang('purchases_order')), array('link' => '#', 'page' => lang('add_purchase_order')));
            $meta = array('page_title' => lang('add_purchase_order'), 'bc' => $bc);
            $this->page_construct('purchases_order/add', $meta, $this->data);
        }
    }

    public function edit($purchase_order_id = null,$quote_id = null)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('project', $this->lang->line("project"), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            $reference_purchase_no = $this->input->post('reference_purchase_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            if($this->Owner || $this->Admin) {
                $deadline=$this->bpas->fld(trim($this->input->post('deadline')));
            }else {
                $deadline=date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id  = $this->input->post('supplier');
            $status       = $this->input->post('status');
            $biller_id    = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $project_id   = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $shipping     = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $payment_term_details    = $this->site->getAllPaymentTermByID($payment_term);
            //$due_date                = $payment_term_details[0]->id ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL;
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $pri_id = $_POST['pri_id'][$r] ? ($_POST['pri_id'][$r] != 'null' ? $_POST['pri_id'][$r] : null) : null;
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r];
                $item_qoh = isset($_POST['qoh'][$r]) ? $_POST['qoh'][$r] : 0;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        // 'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'description'       => $item_description,
                        'qoh'       => $item_qoh,
                        'supplier_part_no' => $supplier_part_no,
                        'pri_id' => $pri_id,
                    );

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'biller_id'     => $biller_id,
                'project_id' => $project_id,
                'reference_no' => $reference,
                'purchase_ref' => $reference_purchase_no,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax'   => $product_tax,
                'order_tax_id'  => $this->input->post('order_tax'),
                'order_tax'     => $order_tax,
                'total_tax'     => $total_tax,
                'shipping'      => $this->bpas->formatDecimal($shipping),
                'grand_total'   => $grand_total,
                'status'        => $status,
                'created_by'    => $this->session->userdata('user_id'),
                'payment_term'  => $payment_term,
                'due_date'      => $due_date,
                'deadline'      => date('Y-m-d',strtotime($deadline)),
                'eta'           => $this->bpas->fld(trim($this->input->post('eta'))),
                'currency'      => $this->input->post('currency'),
            );
     

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->purchases_order_model->UpdatePurchaseorder($purchase_order_id,$data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect('purchases_order');
        } else {
            if ($purchase_order_id) {
                $this->data['quote'] = $this->purchases_order_model->getPurchaseorderByID($purchase_order_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_order_model->getAllPurchase_orderItems($purchase_order_id);
                //  krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $item->real_unit_cost ? $item->real_unit_cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;
                            $crow->description    = $item->description;
                            $crow->qoh    = $item->qoh;
                            $set_price = $this->site->getUnitByProId($crow->id);
                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'set_price'=>$set_price, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->pri_id         = $item->pri_id;
                        $row->id             = $item->product_id;
                        $row->code           = $item->product_code;
                        $row->name           = $item->product_name;
                        $row->base_quantity  = $item->quantity;
                        $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit           = $item->product_unit_id;
                        $row->qty            = $item->unit_quantity;
                        $row->option         = $item->option_id;
                        $row->discount       = $item->discount ? $item->discount : '0';
                        $supplier_cost       = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost           = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                        $row->real_unit_cost = $item->real_unit_cost;
                        $row->tax_rate       = $item->tax_rate_id;
                        $row->description    = $item->description;
                        $row->qoh            = $item->qoh;
                        $row->expiry         = '';
                        $options             = $this->purchases_model->getProductOptions($row->id);
                        $units               = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                        $set_price           = $this->site->getUnitByProId($row->id);
                        $ri                  = $this->Settings->item_addition ? $row->id : $c;
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['orderid'] = $purchase_order_id;
                $this->data['purchase'] = $this->purchases_order_model->getPurchaseorderByID($purchase_order_id);
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']     = $purchase_order_id;
            $this->data['suppliers']    = $this->site->getAllCompanies('supplier');
            $this->data['categories']   = $this->site->getAllCategories();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['ponumber']     = $this->site->getReference('po');
            $this->data['projects']     = $this->site->getAllProject();
            $this->data['billers']      = $this->site->getAllCompanies('biller');

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases_order'), 'page' => lang('purchases_order')), array('link' => '#', 'page' => lang('edit_purchase_order')));
            $meta = array('page_title' => lang('edit_purchase_order'), 'bc' => $bc);
            $this->page_construct('purchases_order/edit', $meta, $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_order_model->deletePurchaseOrder($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("purchase_order_deleted")));
            }
            $this->session->set_flashdata('message', lang('purchase_order_deleted'));
            admin_redirect('welcome');
        }
    }
    public function purchase_order_deadline_alerts($warehouse_id = NULL)
    {
        $this->load->admin_model('reports_model');
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['projects'] = $this->site->getAllProject();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('list_purchase_order_alerts')));
        $meta = array('page_title' => lang('list_purchase_order_alerts'), 'bc' => $bc);
        $this->page_construct('purchases_order/purchase_order_deadline_alerts', $meta, $this->data);
    }
    public function purchase_order_alerts($warehouse_id = NULL)
    {
        $this->load->admin_model('reports_model');
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['projects'] = $this->site->getAllProject();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('list_purchase_order_alerts')));
        $meta = array('page_title' => lang('list_purchase_order_alerts'), 'bc' => $bc);
        $this->page_construct('purchases_order/purchases_order_alerts', $meta, $this->data);
    }

    /* ----------------------------------------------------------------------------- */

    public function getPurchasesOrderlert($warehouse_id = null)
    {

        $this->bpas->checkPermissions('index');

        //    if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        if ((!$this->Owner) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
        if ($this->input->get('project')) {
            $project = $this->input->get('project');
        } else {
            $project = NULL;
        }
        if ($this->input->get('status')) {
            $status = $this->input->get('status');
        } else {
            $status = NULL;
        }
        if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }

        $detail_link = anchor('admin/purchases_order/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_Order_Details'));
        $edit_link = anchor('admin/purchases_order/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase_order'), array('class' => 'auth'));
        $auth_link = anchor('admin/purchases_order/approved/$1', '<i class="fa fa-check"></i> ' . lang('approve'));
        $unauth_link = anchor('admin/purchases_order/unapproved/$1', '<i class="fa fa-check"></i> ' . lang('unapproved'));
        $reject = anchor('admin/purchases_order/rejected/$1', '<i class="fa fa-times" aria-hidden="true"></i> ' . lang('reject'));
        $unreject = anchor('admin/purchases_order/unreject/$1', '<i class="fa fa-check"></i> ' . lang('unreject'));
        $create_link = anchor('admin/purchases/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_order'), array('class' => 'disabled-link'));
        $create_purchase = anchor('admin/purchases_order/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('creat_puchases'), array('class' => 'disabled-link'));

        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases_order/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete_Purchase_Request') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">

            <li>' . $detail_link . '</li>'

            . (($this->Owner || $this->Admin) ? '<li class="approved">' . $auth_link . '</li>' : ($this->GP['purchases_order-approved'] ? '<li class="approved">' . $auth_link . '</li>' : '')) . (($this->Owner || $this->Admin) ? '<li class="unapproved">' . $unauth_link . '</li>' : ($this->GP['purchases_order-approved'] ? '<li class="unapproved">' . $unauth_link . '</li>' : '')) . (($this->Owner || $this->Admin) ? '<li class="reject">' . $reject . '</li>' : ($this->GP['purchases_order-rejected'] ? '<li class="reject">' . $reject . '</li>' : '')) .

            '<li class="create">' . $create_link . '</li>
			 '

            . (($this->Owner || $this->Admin) ? '<li class="edit">' . $edit_link . '</li>' : ($this->GP['purchases_order-edit'] ? '<li class="edit">' . $edit_link . '</li>' : '')) .

            '</ul>
        </div></div>';
        // .(($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['purchase_request-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).

        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("purchases_order.id,DATE_FORMAT(date, '%Y-%m-%d %T') as date,project_name, reference_no,purchase_ref,supplier,order_status , grand_total,purchases_order.status")
                ->from('purchases_order')
                ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("purchases_order.id,DATE_FORMAT(date, '%Y-%m-%d %T') as date,project_name, reference_no,purchase_ref, supplier, order_status, grand_total,purchases_order.status")
                ->from('purchases_order')
                ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
                ->where('purchases_order.status','Pending');
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('purchases_order.created_by', $user_query);
        }
        if ($supplier) {
            $this->datatables->where('purchases_order.supplier_id', $supplier);
        }
        if ($warehouse) {
            $this->datatables->where('purchases_order.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('purchases_order.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('purchases_order') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if ($project) {
            $this->datatables->like('purchases_order.project_id', $project);
        }
        if ($status) {
            $this->datatables->like('purchases_order.status', $status);
        }
        if ($note) {
            $this->datatables->like('purchases_order.note', $note, 'both');
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();

    }
    /* ----------------------------------------------------------------------------- */

    public function purchase_order_deadline_alert($warehouse_id = null)
    {

        $this->bpas->checkPermissions('index');

        //    if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        if ((!$this->Owner) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
        if ($this->input->get('project')) {
            $project = $this->input->get('project');
        } else {
            $project = NULL;
        }
        if ($this->input->get('status')) {
            $status = $this->input->get('status');
        } else {
            $status = NULL;
        }
        if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }

        $detail_link = anchor('admin/purchases_order/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_Order_Details'));
        $edit_link = anchor('admin/purchases_order/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase_order'), array('class' => 'auth'));
        $auth_link = anchor('admin/purchases_order/approved/$1', '<i class="fa fa-check"></i> ' . lang('approve'));
        $unauth_link = anchor('admin/purchases_order/unapproved/$1', '<i class="fa fa-check"></i> ' . lang('unapproved'));
        $reject = anchor('admin/purchases_order/rejected/$1', '<i class="fa fa-times" aria-hidden="true"></i> ' . lang('reject'));
        $unreject = anchor('admin/purchases_order/unreject/$1', '<i class="fa fa-check"></i> ' . lang('unreject'));
        $create_link = anchor('admin/purchases/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_order'), array('class' => 'disabled-link'));
        $create_purchase = anchor('admin/purchases_order/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('creat_puchases'), array('class' => 'disabled-link'));

        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase_request") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases_order/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete_Purchase_Request') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">

            <li>' . $detail_link . '</li>'

            . (($this->Owner || $this->Admin) ? '<li class="approved">' . $auth_link . '</li>' : ($this->GP['purchases_order-approved'] ? '<li class="approved">' . $auth_link . '</li>' : '')) . (($this->Owner || $this->Admin) ? '<li class="unapproved">' . $unauth_link . '</li>' : ($this->GP['purchases_order-approved'] ? '<li class="unapproved">' . $unauth_link . '</li>' : '')) . (($this->Owner || $this->Admin) ? '<li class="reject">' . $reject . '</li>' : ($this->GP['purchases_order-rejected'] ? '<li class="reject">' . $reject . '</li>' : '')) .

            '<li class="create">' . $create_link . '</li>
			 '

            . (($this->Owner || $this->Admin) ? '<li class="edit">' . $edit_link . '</li>' : ($this->GP['purchases_order-edit'] ? '<li class="edit">' . $edit_link . '</li>' : '')) .

            '</ul>
        </div></div>';
        // .(($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['purchase_request-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).

        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $futureDate=date('Y-m-d');
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, project_name, reference_no, purchase_ref, supplier, order_status, grand_total, purchases_order.status")
                ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
                ->from('purchases_order')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id,DATE_FORMAT(date, '%Y-%m-%d %T') as date,project_name, reference_no,purchase_ref, supplier, order_status, grand_total, purchases_order.status")
                ->from('purchases_order')
                ->join('projects', 'purchases_order.project_id = projects.project_id', 'left')
                ->where('purchases_order.deadline !=', NULL)
                ->where('purchases_order.deadline !=', '0000-00-00')
                ->where('purchases_order.deadline !=', '0000-00-00 00:00:00')
                ->where('purchases_order.deadline !=', '1970-01-01')
                ->where('purchases_order.deadline <=', $futureDate);
        }
        // $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('purchases_order.created_by', $user_query);
        }
        if ($supplier) {
            $this->datatables->where('purchases_order.supplier_id', $supplier);
        }
        if ($warehouse) {
            $this->datatables->where('purchases_order.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('purchases_order.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('purchases_order') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if ($project) {
            $this->datatables->like('purchases_order.project_id', $project);
        }
        if ($status) {
            $this->datatables->like('purchases_order.status', $status);
        }
        if ($note) {
            $this->datatables->like('purchases_order.note', $note, 'both');
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

     /*------------------------------------------------------------------------------*/
    public function modal_view($purchase_id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_order_model->getPurchaseorderByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_order_model->getAllPurchase_orderItems($purchase_id);
                
		$this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

   //     $this->load->view($this->theme . 'purchases_order/modal_view', $this->data);
        $this->load->view($this->theme . 'purchases_order/modal_view_manufature', $this->data);

    }

    public function view($purchase_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_order_model->getPurchaseorderByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_order_model->getAllPurchase_orderItems($purchase_id);
		$this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
       // $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases_order'), 'page' => lang('purchases_order')), array('link' => '#', 'page' => lang('view_purchase_order_details')));
        //$meta = array('page_title' => lang('view_purchase_order_details'), 'bc' => $bc);
       // $this->page_construct('purchases_order/view_form', $meta, $this->data);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'purchases_order/view_form', $this->data);
    }

    public function view_a5($purchase_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_order_model->getPurchaseorderByID($purchase_id);

        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_order_model->getAllPurchase_orderItems($purchase_id);
		$this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->load->view($this->theme . 'purchases_order/view_a5', $this->data);
    }

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_order_model->getPurchaseorderByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_order_model->getAllPurchase_orderItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases_order/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            echo $html;
            die();
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->bpas->generate_pdf($html, $name);
        }

    }

    public function combine_pdf($purchases_id)
    {
        $this->bpas->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_order_model->getPurchaseorderByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_order_model->getAllPurchase_orderItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_order_model->getPurchaseorderByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->purchases_order_model->getAllPurchase_orderItems($inv->return_id) : NULL;
            $inv_html = $this->load->view($this->theme . 'purchases_request/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = array(
                'content' => $inv_html,
                'footer' => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->bpas->generate_pdf($html, $name);

    }

    public function email($purchase_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');

            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
                    $this->session->set_flashdata('message', $this->lang->line("email_sent"));
                    admin_redirect("purchases");
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER["HTTP_REFERER"]);
            }

        } elseif ($this->input->post('send_email')) {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/admin/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases_request/email', $this->data);

        }
    }


    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("purchases_request/purchase_by_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {

                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {

                        if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {

                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_code = $csv_pr['code'];
                            $item_net_cost = $this->bpas->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_expiry = isset($csv_pr['expiry']) ? $this->bpas->fsd($csv_pr['expiry']) : null;

                            $pr_discount = $this->site->calculateDiscount($item_discount, $item_net_cost);
                            $pr_item_discount = $this->bpas->formatDecimal(($pr_discount * $item_quantity), 4);
                            $product_discount += $pr_item_discount;

                            $tax = "";
                            $pr_item_tax = 0;
                            $unit_cost = $item_net_cost - $pr_discount;
                            $gst_data = [];
                            $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->purchases_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                            if ($tax_details) {
                                $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                $item_tax = $ctax['amount'];
                                $tax = $ctax['tax'];
                                if ($product_details->tax_method != 1) {
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                                $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_quantity, 4);
                                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                                    $total_cgst += $gst_data['cgst'];
                                    $total_sgst += $gst_data['sgst'];
                                    $total_igst += $gst_data['igst'];
                                }
                            }

                            $product_tax += $pr_item_tax;
                            $subtotal = $this->bpas->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                            $unit = $this->site->getUnitByID($product_details->unit);
                            $product = array(
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option->id,
                                'net_unit_cost' => $item_net_cost,
                                'quantity' => $item_quantity,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $quantity_balance,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $tax_details ? $tax_details->id : null,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'expiry' => $item_expiry,
                                'subtotal' => $subtotal,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' => $this->bpas->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost' => $this->bpas->formatDecimal(($item_net_cost + $item_tax + $pr_discount), 4),
                            );

                            $products[] = ($product+$gst_data);
                            $total += $this->bpas->formatDecimal(($item_net_cost * $item_quantity), 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }

                }
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $total_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->bpas->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('username'),
            );
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {

            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect("purchases_request");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_by_csv')));
            $meta = array('page_title' => lang('add_purchase_by_csv'), 'bc' => $bc);
            $this->page_construct('purchases_request/purchase_by_csv', $meta, $this->data);

        }
    }
    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchases_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry = 1;
                $row->expiry = '';
                $row->qty = 1;
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$category = $this->site->getCategoryByID($row->category_id);
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ") | ".$category->name,
                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_order_model->deleteorder($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases_order'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('pr_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('distict'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('province'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('tel'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('specification'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('material_type'));
                    // $this->excel->getActiveSheet()->SetCellValue('K1', lang('dc_exspect'));
                    // $this->excel->getActiveSheet()->SetCellValue('L1', lang('delivery_status'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('purchase_status'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('approved_date'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('unit'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('subtotal'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('payment_status'));

                    $row = 2;
					$i = 1;
                    foreach ($_POST['val'] as $id) {
                        $purchases = $this->purchases_order_model->getPurchase_order_detail_ByID($id);
						foreach ($purchases as $purchase) {  
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($purchase->date));
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->purchase_ref);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->supplier);
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $purchase->state);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $purchase->city);
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, $purchase->phone);
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase->note);
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $purchase->warehouse_name);
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $purchase->project_name);
                            // $this->excel->getActiveSheet()->SetCellValue('K' . $row, $purchase->dc_expected);
                            // $this->excel->getActiveSheet()->SetCellValue('L' . $row, $purchase->delivery_status);
                            $this->excel->getActiveSheet()->SetCellValue('K' . $row, $purchase->status);
                            $this->excel->getActiveSheet()->SetCellValue('L' . $row, ($purchase->approved_date != 0 ? $this->bpas->hrld($purchase->approved_date) : ''));
                            $this->excel->getActiveSheet()->SetCellValue('M' . $row, $purchase->product_code);
                            $this->excel->getActiveSheet()->SetCellValue('N' . $row, $purchase->product_name);
                            $this->excel->getActiveSheet()->SetCellValue('O' . $row, $purchase->product_unit_code);
                            $this->excel->getActiveSheet()->SetCellValue('P' . $row, $purchase->unit_cost);
                            $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $purchase->quantity);
                            $this->excel->getActiveSheet()->SetCellValue('R' . $row, $this->bpas->formatMoney($purchase->subtotal));
                            $this->excel->getActiveSheet()->SetCellValue('S' . $row, $purchase->payment_status);
                            
                            $row++;
                            $i++;
                        }
                    }				
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_order_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function excel_export($id = null)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $purchases = $this->purchases_order_model->getPurchase_order_detail_ByID($id);
        if ($purchases) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchase_order'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                $row = 2;
                foreach ($purchases as $purchase) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($purchase->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->status);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatMoney($purchase->grand_total));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'purchase_order_' . date('Y_m_d_H_i_s');
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases_request/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'purchases_request/payment_note', $this->data);
    }

    public function email_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        if ( ! $supplier->email) {
            $this->bpas->send_json(array('msg' => lang("update_supplier_email")));
        }
        $this->data['supplier'] =$supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'purchases_request/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $supplier->company && $supplier->company != '-' ? $supplier->company :  $supplier->name,
            'email' => $supplier->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->bpas->send_email($supplier->email, $subject, $message)) {
            $this->bpas->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->bpas->send_json(array('msg' => lang("email_failed")));
        }
    }

    public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang("purchase_already_paid"));
            $this->bpas->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases_request/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->bpas->clear_tags($this->input->post('note')),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            admin_redirect("purchases_request");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases_request/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function view_return($id = null)
    {
        $this->bpas->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme.'purchases_request/view_return', $this->data);
    }

    public function return_purchase($id = null)
    {
        $this->bpas->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->return_id) {
            $this->session->set_flashdata('error', lang("purchase_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $supplier_details = $this->site->getCompanyByID($purchase->supplier_id);

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->bpas->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                    $product_discount += $pr_item_discount;
                    $item_net_cost = $unit_cost;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->bpas->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
                    );

                    $products[] = ($product+$gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount') ? $this->input->post('order_discount') : null, ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($return_surcharge) - $order_discount), 4);
            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $purchase->reference_no,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => ($this->input->post('discount') ? $this->input->post('order_discount') : null),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->bpas->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'payment_status' => $purchase->payment_status == 'paid' ? 'due' : 'pending',
            );
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {
            $this->session->set_flashdata('message', lang("return_purchase_added"));
            admin_redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $purchase;
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang("purchase_status_x_received"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->unit_quantity;
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'set_price' => $set_price, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);

                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->page_construct('purchases_request/return_purchase', $meta, $this->data);
        }
    }

    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost =  $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost =  $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost =  $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost =  $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost =  $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->bpas->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {
            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchases_request/update_status', $this->data);
        }
    }

	public function rejected($id = null)
    {
        $data['rejected_date']         =  date('Y-m-d H:i:s');
        $data['rejected_by']           = $this->session->userdata('user_id');
        $data['purchase_order_id']     = $id;

        $request                       = ['purchase_order_id' => $id];
        $aprroved['purchase_order_id'] = $id;
        $col                           = "purchase_order_id";
        $status                        = 'reject';

        $this->approved_model->changeStatus($id, $col, $request,$data);
		$this->db->set(array(
            'status'      => $status,
            'rejected_by' => $this->session->userdata('user_id'),
            'approved_by' => null,
        ));
		$this->db->where('bpas_purchases_order.id', $id);
		if($this->db->update('bpas_purchases_order')){
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_rejected"));
            redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	public function unreject($id = null)
    {
        $data['unrejected_date'] =  date('Y-m-d H:i:s');
        $data['unrejected_by'] = $this->session->userdata('user_id');
        $data['purchase_order_id'] = $id;
        $request = ['purchase_order_id' => $id];
        $aprroved['purchase_order_id'] = $id;
        $col = "purchase_order_id";
        $this->approved_model->changeStatus($id, $col, $request, $data);
		$status='pending';
		$this->db->set('status',$status);
		$this->db->where('bpas_purchases_order.id',$id);
		if($this->db->update('bpas_purchases_order')){
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_unrejected"));
			redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	public function approved($request_id = null)
    {
        $data['approved_date']         =  date('Y-m-d H:i:s');
        $data['approved_by']           = $this->session->userdata('user_id');
        $data['purchase_order_id']     = $request_id;

        $request                       = ['purchase_order_id' => $request_id];
        $aprroved['purchase_order_id'] = $request_id;
        $col                           = "purchase_order_id";
        $status                        = "approved";

        $this->approved_model->changeStatus($request_id, $col, $request, $data);
		$this->db->set(
            array(
				'status'        => $status,
                'approved_by'   => $this->session->userdata('user_id'),
                'rejected_by'   => null,
				'approved_date' => date('Y-m-d H:i:s')
				));   
		$this->db->where('id', $request_id);  
		if($this->db->update('purchases_order')){
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_approved"));
            redirect($_SERVER["HTTP_REFERER"]);	 
        }
	}
	public function unapproved($request_id = null)
    {
        $data['unapproved_date']       = date('Y-m-d H:i:s');
        $data['unapproved_by']         = $this->session->userdata('user_id');
        $data['purchase_order_id']     = $request_id;

        $request                       = ['purchase_order_id' => $request_id];
        $aprroved['purchase_order_id'] = $request_id;
        $col                           = "purchase_order_id";
        $status                        = "requested";

        $this->approved_model->changeStatus($request_id, $col, $request, $data);
		$this->db->set('status', $status);
		$this->db->where('id', $request_id);
		if($this->db->update('purchases_order')){
            $this->session->set_flashdata('message', $this->lang->line("purchase_order_unapproved"));
            redirect($_SERVER["HTTP_REFERER"]);
        }	  
	}
     public function approved_status($id){
        $this->form_validation->set_rules('update', lang("update"), 'required');
        if ($this->form_validation->run() == true) {
            // $note           = $this->bpas->clear_tags($this->input->post('note'));
            // $col            =  'purchase_order_id';
            // $request        = ['purchase_order_id' => $id];
            // $req            = $this->approved_model->getApprovedStatus($request);
            // foreach ($req as $key_ => $value_) {
            //     foreach($_POST as $key => $value){
            //         $d = explode("_by", $key);
            //         $m = $d[0] . "_status";
            //         if(($key != 'update') && ($key != 'note') && ($m == $key_) && ($value != $value_)){
            //             $data[]  = array(
            //                 $d[0]."_status" => $value,
            //                 $key            => $this->session->userdata('user_id'),
            //                 $d[0]."_date"   => date('Y-m-d h:i:s')
            //             );
            //         }
            //     }
            // }
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $col            =  'purchase_order_id';
            $table          = "purchases_order";
            $request        = ['purchase_order_id' => $id];
            $req            = $this->approved_model->getApprovedStatus($request);
            if($req){
            foreach ($req as $key_ => $value_) {
                foreach($_POST as $key => $value){
                    $d = explode("_by", $key);
                    $m = $d[0] . '_status';
                    if(($key != 'update') && ($key != 'note') && ($m == $key_) && ($value != $value_)){
                        $data[]  = array(
                            $d[0] . '_status'   => $value,
                            $key                => $this->session->userdata('user_id'),
                            $d[0] . '_date'     => date('Y-m-d h:i:s'));
                        }
                     }
                }
            }else{
                foreach($_POST as $key => $value){
                    $d = explode("_by", $key);
                    $m = $d[0] . '_status';
                    if(($key != 'update') && ($key != 'note')){
                        $data[]  = array(
                            $d[0] . '_status'   => $value,
                            $key                => $this->session->userdata('user_id'),
                            $d[0] . '_date'     => date('Y-m-d h:i:s'));
                    }
                }
            }
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'purchases_order');
        }
        if ($this->form_validation->run() == true && $this->approved_model->change_Status($id, $col, $data, $request, $note, $_POST, $table)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'purchases_order');
        } else {
            $this->data['inv']             = $this->purchases_model->getPurchaseByID($id);
            $this->data['PersonApproved']  = $this->site->getMultiApproved(0,'po');
            $this->data['approved']        = $this->approved_model->getApprovedByID(['purchase_order_id'=>$id]);
            $this->data['returned']        = FALSE;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned']    = TRUE;
            }
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme.'purchases_order/approved_status', $this->data);
        }
    }
}