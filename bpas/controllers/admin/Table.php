<?php defined('BASEPATH') or exit('No direct script access allowed');

class Table extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Customer || $this->Supplier) {
           $this->session->set_flashdata('warning', lang('access_denied'));
           redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->load->admin_model('products_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('table_model');
        $this->load->admin_model('sales_model');
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    function suspend_note($warehouse_id = NULL,$start_date = null, $end_date = null)
    {
        $this->bpas->checkPermissions('suspended_note');
         if (!$start_date) {
            $start      = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end      = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $this->data['start']                  = urldecode($start_date);
        $this->data['end']                    = urldecode($end_date);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('room'), 'page' => lang('room')), array('link' => '#', 'page' => lang('suspended_note')));
        $meta = array('page_title' => lang('suspended_note'), 'bc' => $bc);
        $this->page_construct('suspended/suspended_list', $meta, $this->data);
    }

    function getSuspended($index = null, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('suspended_note', TRUE);
        $checkIn_link         = anchor('admin/room/checkin/0/0/$1', '<i class="fa fa-money"></i> ' . lang('checkin'));
        $edit_room            = anchor('admin/table/edit_room/$1', '<i class="fa fa-edit"></i> ' . lang('edit_room'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $qrcode            = anchor('admin/table/qrcode/$1', '<i class="fa fa-qrcode"></i> ' . lang('qrcode'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');


        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_room') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('table/delete_room/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_room') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">' . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu"> 
                <li>' . $edit_room . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("note_id as id,
                    {$this->db->dbprefix('suspended_note')}.note_id as qr_code,
                    {$this->db->dbprefix('suspended_note')}.name, 
                    {$this->db->dbprefix('suspended_note')}.price,
                    bed,
                    custom_field.description,
                    status")
            ->join('custom_field', 'custom_field.id = suspended_note.suspend_type', 'left') 
            ->from('suspended_note');
            // ->join('warehouses', 'warehouses.id=suspended_note.warehouse_id', 'left');
            if ($warehouse_id) {
                $this->datatables->where('warehouse_id', $warehouse_id);
            }
        // $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('suspended_note/edit_room/$1') . "' class='tip' title='" . lang("edit_room") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  " . $delete_link . "</div>", "id");
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function qrcode($id = null){

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $room = $this->table_model->getroomByID($id);
        $this->data['room']   = $room;
        $this->data['file_name'] = ''; 
        if($room->qr_code){
            $file_name = "assets/qr_code/qr_".$room->qr_code.".png";
            $this->data['file_name'] = $file_name;
        }else{
            $bar_code = file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".admin_url()."&choe=UTF-8");
            $file_name = "assets/qr_code/qr_".$room->qr_code.".png";
            $file_handle = fopen($file_name, 'w');
            fwrite($file_handle, $bar_code);
            $this->data['file_name'] = $file_name;
        }
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'suspended/qrcode', $this->data);
        
    }
    function add_room($page = NULL)
    {
        $this->form_validation->set_rules('room', lang("room"), 'trim|required');
        $this->form_validation->set_rules('type', lang("type"), 'trim');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|numeric');
        // $this->form_validation->set_rules('price', lang("price"), 'trim|numeric');
        if ($this->form_validation->run() == true) {
            $data = array(
                'name'         => $this->input->post('room'),
                'type'         => $this->input->post('type'),
                'suspend_type' => $this->input->post('suspend_type'),
                'warehouse_id' => $this->input->post('warehouse'),
                'floor'        => $this->input->post('floor'),
                'bed'          => $this->input->post('bed'),
                'amount'       => $this->input->post('amount'),
                'description'  => $this->input->post('description'),
                'qr_code'      => md5(microtime(true).mt_Rand()),
                'create_date'  => date('Y-m-d H:i:s')
            );
            $data_options = null;
            $options = $this->site->getcustomfield('Room Options');
            if ($this->Settings->module_hotel_apartment && !empty($options)) {
                for ($i=0; $i < sizeof($_POST['custom_field']); $i++) { 
                    $data_options[] = array(
                        'custom_field_id' => $_POST['custom_field'][$i],
                        'price'           => (!empty($_POST['price'][$i]) && $_POST['price'][$i] != '' ? $_POST['price'][$i] : 0),
                    );
                }
                $data['price'] = $data_options[0]['price'];
            } else {
                $data['price'] = $this->input->post('price');
            }
            // $this->bpas->print_arrays($data, $data_options);
        } elseif ($this->input->post('add_room')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("table/suspend_note");
        }
        if ($this->form_validation->run() == true && $this->table_model->addRoom($data, $data_options)) { 
            $this->session->set_flashdata('message', lang("data_add"));
            admin_redirect("table/suspend_note");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang("add_room");
            $this->data['floors']     = $this->site->getAllFloors();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['options']    = $this->site->getcustomfield('Room Options');
            $this->load->view($this->theme . 'suspended/add_room', $this->data);
        }
    }

    function edit_room($id = NULL)
    {
        $this->form_validation->set_rules('room', lang("room"), 'trim|required');
        $this->form_validation->set_rules('type', lang("type"), 'trim');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|numeric');
        // $this->form_validation->set_rules('price', lang("price"), 'trim|numeric');
        if ($this->form_validation->run() == true) {

            $qr_code = md5(microtime(true).mt_Rand());
            $data = array(
                'name'         => $this->input->post('room'),
                'type'         => $this->input->post('type'),
                'suspend_type' => $this->input->post('suspend_type'),
                'warehouse_id' => $this->input->post('warehouse'),
                'floor'        => $this->input->post('floor'),
                'bed'          => $this->input->post('bed'),
                'amount'       => $this->input->post('amount'),
                'description'  => $this->input->post('description'),
                'qr_code'      => $qr_code,
                'create_date'  => date('Y-m-d H:i:s')
            );
            $bar_code = file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".admin_url()."&choe=UTF-8");
            $file_name = "assets/qr_code/qr_".$qr_code.".png";
            $file_handle = fopen($file_name, 'w');
            fwrite($file_handle, $bar_code);

            $data_options = null;
            $options = $this->site->getcustomfield('Room Options');
            if ($this->Settings->module_hotel_apartment && !empty($options)) {
                for ($i=0; $i < sizeof($_POST['custom_field']); $i++) { 
                    $data_options[] = array(
                        'suspended_note_id' => $id,
                        'custom_field_id'   => $_POST['custom_field'][$i],
                        'price'             => (!empty($_POST['price'][$i]) && $_POST['price'][$i] != '' ? $_POST['price'][$i] : 0),
                    );
                }
                $data['price'] = $data_options[0]['price'];
            } else {
                $data['price'] = $this->input->post('price');
            }
        } elseif ($this->input->post('edit_room')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("suspended_note");
        }
        if ($this->form_validation->run() == true && $this->table_model->updateRoom($id, $data, $data_options)) {
            $this->session->set_flashdata('message', lang("data_update"));
            admin_redirect("table/suspend_note");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rooms']        = $this->table_model->getroomByID($id);
            $this->data['room_options'] = $this->table_model->getRoomOptionsByRoomID($id);
            $this->data['id']           = $id;           
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['floors']       = $this->site->getAllFloors();
            $this->data['page_title']   = lang("edit_room");
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['options']      = $this->site->getcustomfield('Room Options');
            $this->load->view($this->theme . 'suspended/edit_room', $this->data);
        }
    }

    function delete_room($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);
        if ($this->table_model->delete_room($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("room_deleted")));
        }
    }

    /* ---------------------------------------------------------------------------------------------------- */
	public function bill_default()
    {
		$user=$this->session->userdata();
        $default_customer = null;
        if ($this->pos_settings->default_customer) {
            $default_customer = $this->site->getCompanyByID($this->pos_settings->default_customer);
        }
		$product = array(
		    'refer'      	    => $this->site->getReference('bill'),
			'date'      	    => date('Y-m-d H:i:s'),
			'start_date'        => date('Y-m-d H:i:s'),
			'customer_id'       => (!empty($default_customer) ? $default_customer->id : 1),
            'customer'          => (!empty($default_customer) ? $default_customer->name : 'Walk-in Customer'),
			'count'    		    => 1,
			'order_discount_id' => $this->input->get('discount'),
			'order_tax_id'      => 1,
			'total'      	    => $this->input->get('price'),
			'biller_id'         => $user['biller_id'],
			'warehouse_id' 	    => $user['warehouse_id'],
			'created_by' 	    => $user['user_id'],
			'suspend_note'      => $this->input->get('room')
		);
		$result= $this->db->insert('suspended_bills',$product);
		if ($result) {
            $bill_id = $this->db->insert_id();
            $room_id = $this->input->get('room');
            $this->db->select('set_item');
            $this->db->from('suspended_note');
            $this->db->where('note_id', $room_id);
            $query = $this->db->get();
            if($query->num_rows()) {
                $data        = $query->row();
                $get_product = $this->site->getProductByCode($data->set_item);
                $this->db->select('products.*,
                        combo_items.product_id as pro_id,
                        combo_items.quantity as set_qty,
                        combo_items.unit_price as set_price
                    ');
                $this->db->from('products');
                $this->db->join('combo_items','products.code = combo_items.item_code');
                $this->db->where('combo_items.product_id', $get_product->id);
                $query = $this->db->get();
                if($query->num_rows()) {   
                    $new_author = $query->result_array();
                    foreach ($new_author as $row) {
                        $data = array(
                            'suspend_id'     => $bill_id,
                            'product_id'     => $row['id'],
                            'product_code'   => $row['code'],
                            'product_name'   => $row['name'],
                            'quantity'          => $row['set_qty'],
                            'net_unit_price'    => $row['set_price'],
                            'unit_price'        => $row['set_price'],
                            'subtotal'          => $row['set_qty'] * $row['set_price'],
                            'real_unit_price'   => $row['set_price'],
                            'unit_quantity'     => $row['set_qty']
                        );
                        $this->db->insert('suspended_items',$data);
                    }        
                }
            }
			$data = array(
				'suspend_id'     => $bill_id,
				'product_id'     => 0,
				'product_code'   => "Time",
				'product_name'   => "Time Duration",
		        // 'product_name'   => $this->input->get('room_name'),
				'quantity'   		=> 0.001,
				'net_unit_price' 	=> $this->input->get('price'),
				'unit_price' 		=> 1,
				'subtotal' 			=> $this->input->get('price'),
				'real_unit_price' 	=> $this->input->get('price'),
				'unit_quantity'  	=> 0.001
			);
			$this->db->insert('suspended_items', $data);
			//-------update bookig---
			$data2 = array(
				'booking'   => ""
			);
			$result = $this->db->update('suspended_note',$data2,
				array('note_id' => $this->input->get('room'))
			);
		}
	}

	public function booking_room()
    {
        $data2 = array(
            'booking'   => "booking",
            'description' => $this->input->get('pos_pin')
        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }

    public function customer_qty()
    {
        $data2 = array(
            'booking'   => "booking",
            'customer_qty' => $this->input->get('pos_pin')
        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }
    public function cancel_booking_room(){
        $data2 = array(
            'booking'   => "",
            'description' => '',
            'customer_qty' => 1
        );
        $result= $this->db->update('suspended_note',$data2,
                array('note_id' => $this->input->get('room'))
            );
        if($result){
            echo 'success';
        }
    }
	public function redirect_room($roomid){
		$room= $this->table_model->get_sus_id_byroom($roomid);
	//	$this->session->set_userdata('remove_posls', 1);
		admin_redirect('pos/index/'.$room->id);
		
	}
// 	public function change_room(){
//         $sus_id =$this->input->get('note_id');
//         $old_table =$this->input->get('old_table');
//         $new_table =$this->input->get('new_table');
//         $user=$this->session->userdata();
//         $table_name = $this->site->get_room_name($old_table);
//         $this->db->select('*');
//         $this->db->from('suspended_bills');
//    //     $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
//         $this->db->where('suspend_note', $new_table);
//         $query = $this->db->get();

//         if($query->num_rows()) {
//             $data = $query->row();

//             $this->db->select('suspended_bills.id as sus_id,suspended_bills.suspend_note,
//                 suspended_items.*');
//             $this->db->from('suspended_bills');
//             $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
//             $this->db->where('suspended_bills.suspend_note', $old_table);
//             $old_q = $this->db->get();

//             $old_result = $old_q->result_array();
//             foreach ($old_result as $row) {
//                 $product = array(
//                     'suspend_id'      => $data->id,
//                     'product_id'      => $row['product_id'],
//                     'product_code'    => $row['product_code'],
//                     'product_name'    => $row['product_name'],
//                     'product_type'    => $row['product_type'],
//                     'option_id'       => $row['option_id'],
//                     'net_unit_price'  => $row['net_unit_price'],
//                     'unit_price'      => $row['unit_price'],
//                     'quantity'        => $row['quantity'],
//                     'product_unit_id' => $row['product_unit_id'],
//                     'product_unit_code' => $row['product_unit_code'],
//                     'unit_quantity'   => $row['warehouse_id'],
//                     'warehouse_id'    => $row['item_tax'],
//                     'item_tax'        => $row['item_tax'],
//                     'tax_rate_id'     => $row['tax_rate_id'],
//                     'tax'             => $row['tax'],
//                     'discount'        => $row['discount'],
//                     'item_discount'   => $row['item_discount'],
//                     'subtotal'        => $row['subtotal'],
//                     'serial_no'       => $row['serial_no'],
//                     'real_unit_price' => $row['real_unit_price'],
//                     'comment'         => $table_name->name,
//                 );
//                 $this->db->insert('suspended_items', $product);    
//             }
//             if ($this->pos_model->deleteBill($row['sus_id'],($old_table))) {
//                 echo 'success';
//             }
                      
//         }else{
//             $product = array('suspend_note'   => $new_table);
//             $result= $this->db->update('suspended_bills',$product,
//                     array('id' => $this->input->get('note_id')));
//             if($result){
//                 echo 'success';
//             }
//         }
// 	}
	public function change_room(){
        $sus_id =$this->input->get('note_id');
        $old_table =$this->input->get('old_table');
        $new_table =$this->input->get('new_table');
        $user=$this->session->userdata();
        $table_name = $this->site->get_room_name($old_table);
        $this->db->select('*');
        $this->db->from('suspended_bills');
        //$this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
        $this->db->where('suspend_note', $new_table);
        $query = $this->db->get();

        if($query->num_rows()) {
            $data = $query->row();

            $this->db->select('suspended_bills.id as sus_id,suspended_bills.suspend_note,
                suspended_items.*');
            $this->db->from('suspended_bills');
            $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
            $this->db->where('suspended_bills.suspend_note', $old_table);
            $old_q = $this->db->get();

            $old_result = $old_q->result_array();
            foreach ($old_result as $row) {
                $product = array(
                    'suspend_id'         => $data->id,
                    'product_id'         => $row['product_id'],
                    'product_code'       => $row['product_code'],
                    'product_name'       => $row['product_name'],
                    'product_second_name'=> $row['product_second_name'],
                    'product_type'       => $row['product_type'],
                    'option_id'          => $row['option_id'],
                    'net_unit_price'     => $row['net_unit_price'],
                    'unit_price'         => $row['unit_price'],
                    'quantity'           => $row['quantity'],
                    'product_unit_id'    => $row['product_unit_id'],
                    'product_unit_code'  => $row['product_unit_code'],
                    'unit_quantity'      => $row['unit_quantity'],
                    'original_price'     => $row['original_price'],
                    'warehouse_id'       => $row['warehouse_id'],
                    'item_tax'           => $row['item_tax'],
                    'tax_rate_id'        => $row['tax_rate_id'],
                    'free'               => $row['free'],
                    'tax'                => $row['tax'],
                    'discount'           => $row['discount'],
                    'item_discount'      => $row['item_discount'],
                    'subtotal'           => $row['subtotal'],
                    'serial_no'          => $row['serial_no'],
                    'real_unit_price'    => $row['real_unit_price'],
                    'comment'            => $table_name->name,
                );
                $this->db->insert('suspended_items', $product);    
            }
            if ($this->pos_model->deleteBill($row['sus_id'],($old_table))) {
                echo 'success';
            }
                      
        }else{
            $product = array('suspend_note'   => $new_table);
            $result= $this->db->update('suspended_bills',$product,
                    array('id' => $this->input->get('note_id')));
            if($result){
                echo 'success';
            }
        }
	}
    public function index($sid = NULL)
    {	
        $this->bpas->checkPermissions('index', true, 'room');
	//	$user=$this->session->userdata();

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            admin_redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;
        $floor_id = $this->input->get('floor') ? $this->input->get('floor') : NULL;
        if($floor_id === NULL){
            $floor_id = $this->pos_settings->show_floor;
        }else if($floor_id ==0){
            $floor_id = 0; 
        }
        $data2 = array('show_floor' => $floor_id);
        $this->db->update('pos_settings', $data2);
        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

        if ($this->form_validation->run() == TRUE) {
			
            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->bpas->clear_tags($this->input->post('pos_note'));
            $staff_note = $this->bpas->clear_tags($this->input->post('staff_note'));
            $reference = $this->site->getReference('pos');

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $digital = FALSE;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
               
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = TRUE;
                    }
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id'      => $item_id,
                        'product_code'    => $item_code,
                        'product_name'    => $item_name,
                        'product_type'    => $item_type,
                        'option_id'       => $item_option,
                        'net_unit_price'  => $item_net_price,
                        'unit_price'      => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'        => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : NULL,
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id'    => $warehouse_id,
                        'item_tax'        => $pr_item_tax,
                        'tax_rate_id'     => $item_tax_rate,
                        'tax'             => $tax,
                        'discount'        => $item_discount,
                        'item_discount'   => $pr_item_discount,
                        'subtotal'        => $this->bpas->formatDecimal($subtotal),
                        'serial_no'       => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'comment'         => $item_comment,
                    );

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }
			$cur_rate = $this->pos_model->getExchange_rate('KHR');

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $rounding = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->bpas->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = $this->bpas->formatMoney($round_total - $grand_total);
            }
			$currency =$this->input->post('kh_currenncy') =="" ? $this->input->post('en_currenncy') : $this->input->post('kh_currenncy');
			$currency_rate= ($currency =="usd") ? $cur_rate->rate : 1;
			

			$data = array('date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'rounding'          => $rounding,
                'suspend_note'      => $this->input->post('suspend_note'),
                'currency' 			=> $currency,
                'other_cur_paid_rate' => $currency_rate,
                'pos'               => 1,
                'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                );

            if (!$suspend) {
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->bpas->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
								'paid_amount'  => $_POST['paid_amount'][$r],
                                'currency_rate'=> $_POST['currency_rate'][$r],
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                                'gc_balance'  => $gc_balance,
							//	'currency' 	   => $this->input->post('kh_currenncy')
                                );

                        } else {
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_amount'  => $_POST['paid_amount'][$r],
                                'currency_rate'=> $_POST['currency_rate'][$r],
								'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['cc_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                            //    'currency' 	   => $this->input->post('kh_currenncy')
                                );

                        }

                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }

            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    admin_redirect("table");
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print='.$sale['sale_id'];
                        }
                    }
                    admin_redirect($redirect_to);
                }
            }
        } else {
            $this->data['old_sale'] = NULL;
            $this->data['oid'] = NULL;
            if ($duplicate_sale) {
                if ($old_sale = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid'] = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message'] = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("table");
                }
            }
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("pos");
                }
            }

            if (($sid || $duplicate_sale) && $inv_items) {
                    // krsort($inv_items);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                            $row->quantity = 0;
                        } else {
                            $category = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->type = $item->product_type;
                        $row->quantity += $item->quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity) + $this->bpas->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = $item->serial_no;
                        $row->option = $item->option_id;
                        $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

                        if ($options) {
                            $option_quantity = 0;
                            foreach ($options as $option) {
                                $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                                if ($pis) {
                                    foreach ($pis as $pi) {
                                        $option_quantity += $pi->quantity_balance;
                                    }
                                }
                                if ($option->quantity > $option_quantity) {
                                    $option->quantity = $option_quantity;
                                }
                            }
                        }

                        $row->comment = isset($item->comment) ? $item->comment : '';
                        $row->ordered = 1;
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }

                    $this->data['items'] = json_encode($pr);

            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            // $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['suspend_note']= $this->table_model->getAll_suspend_note();
            
			
			 $this->data['floors'] = $this->site->getAllFloors();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser();
			
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
		
       //     $this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers = json_decode($this->pos_settings->order_printers);
            
			$printers = array();
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings'] = $this->pos_settings;

            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->bpas->view_rights($inv->created_by, true);
                    }
                    $this->data['rows'] = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller'] = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer'] = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments'] = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
                    $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
                    $this->data['inv'] = $inv;
                    $this->data['print'] = $inv->id;
                    $this->data['created_by'] = $this->site->getUser($inv->created_by);
                }
            }
			$this->data['exchange_rate'] = $this->pos_model->getExchange_rate('KHR');
			$this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('THB');
			$this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('THB');
			
			$user = $this->site->getUser();
		    if ($this->Owner || $this->Admin) {
				$this->data['kitchen_note'] = $this->table_model->getAll_suspend_note($floor_id);
				$this->data['available_room']= $this->table_model->available_room();
			}else{
				$warehouse_id = $user->warehouse_id;
				$this->data['kitchen_note'] = $this->table_model->getAll_suspend_note($floor_id, $warehouse_id);
				$this->data['available_room']= $this->table_model->available_room($warehouse_id);
            }
			$currency_id=$this->site->getCurrencyWarehouseByUserID($user->id);
			$curr=$this->site->getCurrencyByID($currency_id);
            $this->data['default_img'] = $curr->code;
            $this->data['pos_type'] = $this->pos_settings->pos_type;
            $this->data['GP'] = $this->site->getPermission();
			$this->load->view($this->theme . 'suspended/index', $this->data);
        }
    }

    public function view_bill()
    {
        $this->bpas->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->admin_model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->admin_model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function registers()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function open_register()
    {
        $this->bpas->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'date' => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );
        }
        if ($this->form_validation->run() == TRUE && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang("welcome_to_pos"));
            admin_redirect("pos");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function close_register($user_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang("total_cheques"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang("total_cc_slips"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
                );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang("register_closed"));
            admin_redirect("welcome");
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : NULL;
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['cashrefunds'] = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users'] = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

    public function getProductDataByCode($code = NULL, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->serial = '';
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            $row->quantity = 0;
            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo NULL; die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $option_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
            if ($row->promotion) {
                $row->price = $row->promo_price;
            } elseif ($customer->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            } elseif ($warehouse->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }
            $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
            $row->real_unit_price = $row->price;
            $row->base_quantity = 1;
            $row->base_unit = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment = '';
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);

            $this->bpas->send_json($pr);
        } else {
            echo NULL;
        }
    }

    public function ajaxproducts($category_id = NULL, $brand_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");

        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id, $brand_id);
        $pro = 1;
        $prods = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" class='img-rounded' /><span>" . character_limiter($product->name, 40) . "</span></button>";

                $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function ajaxcategorydata($category_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"" . base_url() ."assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->bpas->send_json(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    public function ajaxbranddata($brand_id = NULL)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(FALSE, $brand_id);

        if (!($tcp = $this->pos_model->products_count(FALSE, FALSE, $brand_id))) {
            $tcp = 0;
        }

        $this->bpas->send_json(array('products' => $products, 'tcp' => $tcp));
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = NULL, $modal = NULL)
    {
        $this->bpas->checkPermissions('index');
		$user_id = $this->session->userdata('user_id');
        $currency_id=$this->site->getCurrencyWarehouseByUserID($user_id);
		$curr=$this->site->getCurrencyByID($currency_id);
		//echo $curr->code;
		
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");
		
		$this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('THB');
		$this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('THB');
		
		if($curr->code =="THB" || $curr->code =="THB"){
			$this->load->view($this->theme . 'pos/view_bath_default', $this->data);
		}else{
			//$this->load->view($this->theme . 'pos/view_2_currency', $this->data);
			$this->load->view($this->theme . 'pos/view_3_currency', $this->data);
		}
    }

    public function register_details()
    {
        $this->bpas->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getTodayCCSales();
        $this->data['cashsales'] = $this->pos_model->getTodayCashSales();
        $this->data['chsales'] = $this->pos_model->getTodayChSales();
        $this->data['pppsales'] = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales'] = $this->pos_model->getTodaySales();
        $this->data['refunds'] = $this->pos_model->getTodayRefunds();
        $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', TRUE);
        if ($pin == $this->pos_pin) {
            $this->bpas->send_json(array('res' => 1));
        }
        $this->bpas->send_json(array('res' => 0));
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
                'local_printers'            => $this->input->post('local_printers'),
            );
            $payment_config = array(
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("pos/settings");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect("pos/settings");
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect("pos/settings");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                admin_redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize = $this->config->item('authorize');
            $this->data['api_login_id'] = $authorize['api_login_id'];
            $this->data['api_transaction_key'] = $authorize['api_transaction_key'];
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['printers'] = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance'] = NULL; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = NULL; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("welcome");
        }
        if (DEMO) {
            return TRUE;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return TRUE;
            } else {
                @chmod($output_path, 0644);
                return FALSE;
            }
        } else {
            @chmod($output_path, 0644);
            return FALSE;
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = admin_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>'.lang('date').': ' . $bill->date . '<br>'.lang('items').': ' . $bill->count . '<br>'.lang('total').': ' . $this->bpas->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);

    }

    public function delete($id = NULL)
    {

        $this->bpas->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("suspended_sale_deleted")));
        }
    }

    public function email_receipt($sale_id = NULL, $view = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['page_title'] = $this->lang->line("invoice");

        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);
        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->bpas->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }

        try {
            if ($this->bpas->send_email($to, lang('receipt_from') .' ' . $this->data['biller']->company, $receipt)) {
                $this->bpas->send_json(array('msg' => $this->lang->line("email_sent")));
            } else {
                $this->bpas->send_json(array('msg' => $this->lang->line("email_failed")));
            }
        } catch (Exception $e) {
            $this->bpas->send_json(array('msg' => $e->getMessage()));
        }

    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }

    public function add_payment($id = NULL)
    {
        $this->bpas->checkPermissions('payments', TRUE, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == TRUE) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'received',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>'.$e;
                            }
                        } else {
                            $error .= '<br>'.$m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang("payment_added"));
                }
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            admin_redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }


    function open_drawer() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();

    }

    function p() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);

    }

    function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('printers')));
        $meta = array('page_title' => lang('list_printers'), 'bc' => $bc);
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select("id, title, type, profile, path, ip_address, port")
        ->from("printers")
        ->add_column("Actions", "<div class='text-center'> <a href='" . admin_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='".lang("edit_printer")."'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang("delete_printer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();

    }

    function add_printer()
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }

        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_added"));
            admin_redirect("pos/printers");

        } else {
            if($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'failed', 'msg' => validation_errors())); die();
            }

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => admin_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_printer')));
            $meta = array('page_title' => lang('add_printer'), 'bc' => $bc);
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    function edit_printer($id = NULL)
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect("pos");
        }
        if($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line("path"), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_updated"));
            admin_redirect("pos/printers");

        } else {

            $this->data['printer'] = $printer;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => admin_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('edit_printer')));
            $meta = array('page_title' => lang('edit_printer'), 'bc' => $bc);
            $this->page_construct('pos/edit_printer', $meta, $this->data);

        }
    }

    function delete_printer($id = NULL)
    {
        if(DEMO) {
            $this->session->set_flashdata('error', $this->lang->line("disabled_in_demo"));
            $this->bpas->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        if ($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        if ($this->pos_model->deletePrinter($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("printer_deleted")));
        }

    }
    function assign($warehouse_id = NULL,$start_date = null, $end_date = null)
    {
        $this->bpas->checkPermissions('suspended_note');
         if (!$start_date) {
            $start      = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end      = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $this->data['start']                  = urldecode($start_date);
        $this->data['end']                    = urldecode($end_date);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('room'), 'page' => lang('room')), array('link' => '#', 'page' => lang('assign')));
        $meta = array('page_title' => lang('assign'), 'bc' => $bc);
        $this->page_construct('suspended/suspended_assign', $meta, $this->data);
    }
    function getSuspendedAssign($index=null,$warehouse_id = NULL){
        $this->bpas->checkPermissions('suspended_note', TRUE);
        $checkIn_link          = anchor('admin/room/checkin/0/0/$1', '<i class="fa fa-money"></i> ' . lang('checkin'));

        $edit_room     = anchor('admin/table/edit_assign/$1', '<i class="fa fa-edit"></i> ' . lang('edit_assign'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_Assign') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('table/delete_Assign/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_Assign') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">

                <li>' . $edit_room . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("id as id,date,patient_id as name, bed,assign_date,status")
            //->join('custom_field', 'custom_field.id = suspended_note.suspend_type', 'left') 
            ->from('suspended_assign');
        //    ->join('warehouses', 'warehouses.id=suspended_note.warehouse_id', 'left');
            if ($warehouse_id) {
                $this->datatables->where('warehouse_id', $warehouse_id);
            }

     //   $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('suspended_note/edit_room/$1') . "' class='tip' title='" . lang("edit_room") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  " . $delete_link . "</div>", "id");
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    function add_assign($page = NULL)
    {
        $this->form_validation->set_rules('customer', lang("customer"), 'trim|required');
        $this->form_validation->set_rules('bed', lang("bed"), 'required|trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          => $this->bpas->fld(trim($this->input->post('date'))),
                'patient_id'    => $this->input->post('customer'),
                'bed'           => $this->input->post('bed'),
                'assign_date'   => $this->bpas->fld(trim($this->input->post('assign_date'))),
                'description'   => $this->input->post('description'),
               
            );
        } elseif ($this->input->post('add_assign')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("table/assign");
        }

        if ($this->form_validation->run() == true && $this->table_model->addAssignRoom($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("data_add"));
            admin_redirect("table/assign");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("add_assign");
            $this->data['floors'] = $this->site->getAllFloors();
            $this->data['customers']   = $this->site->getAllCompanies('customer');
            $this->data['tables']   = $this->table_model->getsuspend_note();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->view($this->theme . 'suspended/add_assign', $this->data);
        }
    }
    function edit_assign($id = NULL)
    {
        $this->form_validation->set_rules('customer', lang("customer"), 'trim|required');
        $this->form_validation->set_rules('bed', lang("bed"), 'required|trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          => $this->bpas->fld(trim($this->input->post('date'))),
                'patient_id'    => $this->input->post('customer'),
                'bed'           => $this->input->post('bed'),
                'assign_date'   => $this->bpas->fld(trim($this->input->post('assign_date'))),
                'description'   => $this->input->post('description'),
               
            );
        } elseif ($this->input->post('edit_assign')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("table/assign");
        }

        if ($this->form_validation->run() == true && $this->table_model->updateAssign($id,$data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("data_update"));
            admin_redirect("table/assign");
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rooms']    = $this->table_model->getAssignByID($id);
            $this->data['id']       = $id;           
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['floors']   = $this->site->getAllFloors();
            $this->data['page_title']   = lang("edit_assign");
            $this->data['customers']    = $this->site->getAllCompanies('customer');
            $this->data['tables']       = $this->table_model->getsuspend_note();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->load->view($this->theme . 'suspended/edit_assign', $this->data);
        }
    }
     function delete_Assign($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);

        if ($this->table_model->delete_Assign($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("assign_deleted")));
        }
    }
    public function input_customer_amount($sid = null, $room = null, $room_name = null, $price = null, $discount = null)
    {
        // var_dump($room);exit();
        $this->bpas->checkPermissions('payments', TRUE, 'sales');
        $this->load->helper('security');
        if ($this->input->get('sid')) {
            $sid = $this->input->get('sid');
        }
        if ($this->input->get('room')) {
            $room = $this->input->get('room');
        }
        if ($this->input->get('room_name')) {
            $room_name = $this->input->get('room_name');
        }
        if ($this->input->get('price')) {
            $price = $this->input->get('price');
        }
        if ($this->input->get('discount')) {
            $discount = $this->input->get('discount');
        }
        $this->form_validation->set_rules('people_qty_input', lang("people_qty_input"), 'numeric|required');
        $this->form_validation->set_rules('room', lang("room"), 'is_unique[suspended_bills.suspend_note]|numeric|required');
        if ($this->form_validation->run() == TRUE) {
            $data_qty = array(
                'booking'      => "booking",
                'customer_qty' => $this->input->post('people_qty_input')
            );
            if($this->db->update('suspended_note', $data_qty, array('note_id' => $this->input->post('room')))){
                $user = $this->session->userdata();
                $default_customer = null;
                if ($this->pos_settings->default_customer) {
                    $default_customer = $this->site->getCompanyByID($this->pos_settings->default_customer);
                }
                $product = array(
                    'refer'      	    => $this->site->getReference('bill'),//$this->site->GUID(),
                    'date'      	    => date('Y-m-d H:i:s'),
                    'start_date'        => date('Y-m-d H:i:s'),
                    'customer_id'       => (!empty($default_customer) ? $default_customer->id : 1),
                    'customer'          => (!empty($default_customer) ? $default_customer->name : 'Walk-in Customer'),
                    'count'    		    => 1,
                    'order_discount_id' => $this->input->post('discount'),
                    'order_tax_id'      => 1,
                    'total'      	    => $this->input->post('price'),
                    'biller_id'         => $user['biller_id'],
                    'warehouse_id' 	    => $user['warehouse_id'],
                    'created_by' 	    => $user['user_id'],
                    'suspend_note'      => $this->input->post('room')
                );
                // var_dump($product);exit();
                $result= $this->db->insert('suspended_bills',$product);
                if($result){
                    $bill_id = $this->db->insert_id();
                    $room_id = $this->input->post('room'); 
                    $this->db->select('set_item');
                    $this->db->from('suspended_note');
                    $this->db->where('note_id', $room_id);
                    $query = $this->db->get(); 
                    if($query->num_rows()) { 
                        $data = $query->row();
                        $get_product = $this->site->getProductByCode($data->set_item); 
                        $this->db->select('products.*, combo_items.product_id as pro_id, combo_items.quantity as set_qty, combo_items.unit_price as set_price');
                        $this->db->from('products');
                        $this->db->join('combo_items','products.code = combo_items.item_code');
                        $this->db->where('combo_items.product_id', $get_product->id);
                        $query = $this->db->get();
                        if($query->num_rows()) {   
                            $new_author = $query->result_array();
                            foreach ($new_author as $row) {
                                $data = array(
                                    'suspend_id'        => $bill_id,
                                    'product_id'        => $row['id'],
                                    'product_code'      => $row['code'],
                                    'product_name'      => $row['name'],
                                    'quantity'          => $row['set_qty'],
                                    'net_unit_price'    => $row['set_price'],
                                    'unit_price'        => $row['set_price'],
                                    'subtotal'          => $row['set_qty'] * $row['set_price'],
                                    'real_unit_price'   => $row['set_price'],
                                    'unit_quantity'     => $row['set_qty']
                                );
                                $this->db->insert('suspended_items',$data);
                            }        
                        }
                }
                $data = array(
                    'suspend_id'     => $bill_id,
                    'product_id'     => 0,
                    'product_code'   => "Time",
                    'product_name'   => "Time Duration", 
                    'quantity'   		=> 0.001,
                    'net_unit_price' 	=> $this->input->post('price'),
                    'unit_price' 		=> 1,
                    'subtotal' 			=> $this->input->post('price'),
                    'real_unit_price' 	=> $this->input->post('price'),
                    'unit_quantity'  	=> 0.001
                );
                    $this->db->insert('suspended_items', $data); 
                    //-------update bookig---
                    $data2 = array('booking'   => "");
                    $result= $this->db->update('suspended_note', $data2, array('note_id' => $this->input->post('room')));
                    admin_redirect("pos/index/".$bill_id);
                }
            } 
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == TRUE) {
            $this->session->set_flashdata('message', lang("has_new_booking"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sid']   = $sid;
            $this->data['room']   = $room;
            $suspendnote = $this->site->getSuspended_noteByID($room);
            $this->data['room_name']   = $suspendnote->name;
            $this->data['price']   = $price;
            $this->data['discount']   = $discount;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suspended/input_customer_amount', $this->data);
        }
    }
}