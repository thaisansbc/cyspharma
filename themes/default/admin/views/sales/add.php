<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }
    main {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #reader {
        width: 100%;
    }
    #result {
        text-align: center;
        font-size: 1.5rem;
    }
    #html5-qrcode-button-camera-permission {
        padding: 5px;
    }
</style>
<script type="text/javascript">
    var count = 1,
    an = 1,
    product_variant = 0,
    DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0,
    invoice_tax = 0,
    product_discount = 0,
    order_discount = 0,
    total_discount = 0,
    total = 0,
    allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
    // var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    // var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    var quote = null;
    var is_editing = false;
    localStorage.setItem('group_price', JSON.stringify(<?= $group_price; ?>));
    $(document).ready(function() {
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('group_price')) {
                localStorage.removeItem('group_price');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('sloverselling')) {
                localStorage.removeItem('sloverselling');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slsaleman_by')) {
                localStorage.removeItem('slsaleman_by');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            if (localStorage.getItem('sale_ref')) {
                localStorage.removeItem('sale_ref');
            }
            if (localStorage.getItem('saleTax_ref')) {
                localStorage.removeItem('saleTax_ref');
            }
            if (localStorage.getItem('slshipping_request')) {
                localStorage.removeItem('slshipping_request');
            }
            if (localStorage.getItem('slshipping_request_phone')) {
                localStorage.removeItem('slshipping_request_phone');
            }
            if (localStorage.getItem('slshipping_request_address')) {
                localStorage.removeItem('slshipping_request_address');
            }
            if (localStorage.getItem('slshipping_request_note')) {
                localStorage.removeItem('slshipping_request_note');
            }
            localStorage.removeItem('remove_slls');
        }
        <?php if (isset($quote_id) && !empty($quote_id)) { ?>
            // localStorage.setItem('sldate', '<?= $this->bpas->hrld($quote->date) ?>');
            localStorage.setItem('slcustomer',   '<?= $quote->customer_id ?>');
            localStorage.setItem('slbiller',     '<?= $quote->biller_id ?>');
            localStorage.setItem('slsaleman_by', '<?= isset($quote->saleman_by) ? $quote->saleman_by : '' ?>');
            localStorage.setItem('slwarehouse',  '<?= $quote->warehouse_id ?>');
            localStorage.setItem('slnote',       '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($quote->note)); ?>');
            localStorage.setItem('sldiscount',   '<?= (isset($quote->order_discount_id) ? $quote->order_discount_id : '') ?>');
            localStorage.setItem('sltax2',       '<?= (isset($quote->order_tax_id) ? $quote->order_tax_id : '') ?>');
            localStorage.setItem('slshipping',   '<?= (isset($quote->shipping) ? $quote->shipping : '') ?>');
            localStorage.setItem('slitems',      JSON.stringify(<?= $quote_items; ?>));
            quote = jQuery.parseJSON('<?php echo json_encode($quote); ?>');
        <?php } ?>
        <?php if ($this->input->get('customer')) { ?>
            if (!localStorage.getItem('slitems')) {
                localStorage.setItem('slcustomer', <?= $this->input->get('customer'); ?>);
            }
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('sldate')) {
                $("#sldate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'bpas',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
            $(document).on('change', '#sldate', function(e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
        <?php } ?>
        $(document).on('change', '#slbiller', function(e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        $(document).on('change', '#slsaleman_by', function(e) {
            localStorage.setItem('slsaleman_by', $(this).val());
        });
        if (slsaleman_by = localStorage.getItem('slsaleman_by')) {
            $('#slsaleman_by').val(slsaleman_by);
        }
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?= $slnumber ?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?= $Settings->default_tax_rate2; ?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function(e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function(request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                var Settings_ = jQuery.parseJSON('<?php echo json_encode($Settings); ?>');
                if (ui.item.id !== 0) {
                    if (ui.item.combo_items !== false && Settings_['product_combo'] == 0) {
                        var wh = $("#slwarehouse").val();
                        $.ajax({
                            type: "get",
                            url: "<?= admin_url('sales/getProductCombo'); ?>",
                            data: { product_id: ui.item.row.id, warehouse_id: wh, },
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    for (var i = 0; i < data.length; i++) {
                                        data.free = true;
                                        data.parent = ui.item.row.id;
                                        add_invoice_item(data[i]);
                                    }
                                }
                                $("#add_item").removeClass('ui-autocomplete-loading');
                            }
                        }).done(function () {
                            $('#add_item').val('');
                            $('#modal-loading').hide();
                        });
                        if (row) $(this).val('');
                    } else {
                        var row = add_invoice_item(ui.item);
                        var wh = $("#slwarehouse").val();
                        $.ajax({
                            type: "get",
                            url: "<?=admin_url('pos/getProductPromo');?>",
                            data: {product_id: ui.item.row.id, warehouse_id: wh, qty:ui.item.row.qty},
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    for (var i = 0; i < data.length; i++) {
                                        data.free = true;
                                        data.parent = ui.item.row.id;
                                        add_invoice_item(data[i]);
                                    }
                                }
                                $("#add_item").removeClass('ui-autocomplete-loading');
                            }
                        }).done(function () {
                            $('#modal-loading').hide();
                        });
                        if (row) $(this).val('');
                    }
                } else {
                    bootbox.alert('<?=lang('no_match_found')?>');
                }
            }
        });
        $(document).on('change', '#gift_card_no', function() {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get",
                    async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function(data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                        } else {
                            $('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
        $(document).ready(setZoneSelector);
        $('#slsaleman_by').change(setZoneSelector);
        function setZoneSelector(){
            var saleman_id =  $('#slsaleman_by').val() ?  $('#slsaleman_by').val() : '';
            if(saleman_id != ""){
                $.ajax({
                    type: "get",
                    url: site.base_url + "sales/getZonesBySaleman_ajax/" + saleman_id,
                    dataType: "json",
                    success: function (data) {
                        if(data != false){
                            $("#zone_id").find('option').remove().end();
                            if(data['z_b_user']['multi_zone'] != null){
                                $("#zone_id").append("<option selected='selected'>Select Zone</option>");
                                mz_id = data['z_b_user']['multi_zone'].split(',');
                                mz_id.forEach((element, index, array) => {
                                    let zone = data['z_all'].find(x => x.id === element);
                                    $("#zone_id").append("<option value='" + zone.id + "'>" + zone.zone_name + "</option>");
                                    if(zone.parent_id == 0){
                                        data['z_all'].forEach(element => {
                                            if(element.parent_id == zone.id){
                                                $("#zone_id").append("<option value='" + element.id + "'>" + "&emsp;" + element.zone_name + "</option>");
                                            }
                                        });
                                    } 
                                });
                            } else {
                                $("#zone_id").append("<option selected='selected'>No matches found</option>");
                            }
                            $("#zone_id option:first").attr('selected','selected').trigger('change');
                        }
                    },
                }).fail(function(xhr, error){
                    console.debug(xhr); 
                    console.debug(error);
                });
            }
        }
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></h2>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                if (isset($quote_id) || isset($sale_order_id) || isset($delivery_id)) {
                    if (isset($sale_order_id) && !empty($sale_order_id)) {
                        echo admin_form_open_multipart("sales/add/".$sale_order_id, $attrib);
                    } elseif (isset($delivery_id) && !empty($delivery_id)) {
                        echo admin_form_open_multipart("sales/add/0/0/" . $delivery_id, $attrib);
                    } elseif (isset($prescription_id) && !empty($prescription_id)) {
                        echo admin_form_open_multipart("sales/add/0/0/0/" . $prescription_id, $attrib);
                    } else {
                        echo admin_form_open_multipart("sales/add/0/" . $quote_id, $attrib);
                    }

                } else {
                    echo admin_form_open_multipart("sales/add", $attrib);
                }
                if ($sale_order_id) {
                    echo form_hidden('sale_order_id', $sale_order_id);
                } 
                if ($prescription_id) {
                    echo form_hidden('prescription_id', $prescription_id);
                } 
                if ($delivery_id) {
                    echo form_hidden('delivery_id', $delivery_id);
                } 
                echo form_hidden('consignment_id', isset($consignment_id)? $consignment_id: 0);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'sldate'); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'slref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                            </div>
                        </div>
                        <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } elseif (count($user_billers) > 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        foreach ($user_billers as $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type'  => 'hidden',
                                'name'  => 'biller',
                                'id'    => 'slbiller',
                                'value' => $user_billers[0],
                            );
                            echo form_input($biller_input);
                        } ?>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <div class="panel panel-warning">
                            <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                            <div class="panel-body" style="padding: 5px;">
                                <?php if ($this->Settings->project) { ?>
                                    <div class="col-md-4">
                                        <!-- <div class="form-group">
                                            <?= lang("project", "poproject"); ?>
                                            <?php
                                            $project_id = isset($inv) ? $inv->project_id : '';
                                            $pro[""] = "";
                                            foreach ($projects as $project) {
                                                $pro[$project->project_id] = $project->project_name;
                                            }
                                            echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                            ?>
                                        </div> -->
                                        <div class="form-group">
                                            <?= lang("project", "poproject"); ?>
                                            <div class="input-group" style="width:100%">
                                                <SELECT class="form-control input-tip select" name="project" style="width:100%;">
                                                    <!-- <option value="">--Select--</option> -->
                                                    <?php
                                                    $project_id = isset($inv) ? $inv->project_id : '';
                                                    $pro[""] = lang("please_selected");
                                                    foreach ($projects as $project) {
                                                        $pro[$project->project_id] = $project->project_name;

                                                        echo "<option value='" . $project->project_id . "' >" . $project->project_name;
                                                        ?>
                                                    <?php } ?>
                                                </SELECT>
                                                <?php if ($Owner) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?php echo admin_url('projects/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('warehouse', 'slwarehouse'); ?>
                                            <?php
                                            $wh[''] = '';
                                            if (!empty($warehouses)) {
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                        </div>
                                    </div>
                                <?php } elseif (count($count) > 1) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('warehouse', 'slwarehouse'); ?>
                                            <?php
                                            $wh[''] = '';
                                            if (!empty($warehouses)) {
                                                foreach ($warehouses as $warehouse) {
                                                    foreach ($count as $key => $value) {
                                                        if ($warehouse->id == $value) {
                                                            $wh[$warehouse->id] = $warehouse->name;
                                                        }
                                                    }
                                                }
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    $warehouse_input = [
                                        'type'  => 'hidden',
                                        'name'  => 'warehouse',
                                        'id'    => 'slwarehouse',
                                        'value' => $this->session->userdata('warehouse_id'),
                                    ];
                                    echo form_input($warehouse_input);
                                } ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('customer', 'slcustomer'); ?>
                                        <div class="input-group">
                                            <?php
                                            echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="slcustomer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                                            ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                <a href="#" id="toogle-customer-read-attr" class="external">
                                                    <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                </a>
                                            </div>
                                            <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                <a href="#" id="view-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                    <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                </a>
                                            </div>
                                            <?php if ($Owner || $Admin || $GP['customers-add']) {
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= admin_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($Settings->sale_man) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= $Settings->module_clinic == 1 ? lang("doctor","doctor") : lang("salesman", "salesman"); ?>
                                             <select id="slsaleman_by" name="saleman_by" class="form-control input-tip select">
                                                <?php
                                                echo '<option value="">----------</option>';
                                                if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                                    echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
                                                } else {
                                                    foreach($salemans as $agency){
                                                        echo '<option value="'.$agency->id.'">'.$agency->first_name.' '.$agency->last_name.'</option>';
                                                    }
                                                }
                                                ?>
                                            </select> 
                                        </div>
                                    </div>
                                <?php } if ($Settings->zone) { ?>
                                    <div class="col-md-4 hide">
                                        <div class="form-group">
                                            <?= lang('zone', 'zone_id') ?>
                                            <?php
                                            if($Settings->zone_by_saleman){
                                                echo form_dropdown('zone_id', lang("select") . ' ' . lang("zone"), (isset($_POST['zone_id']) ? $_POST['zone_id'] : ''), 'class="form-control select" id="zone_id" data-placeholder="' . lang("select") . ' ' . lang("zone") . '"style="width:100%"');
                                            }else{
                                                $zon[""] = "";
                                                foreach ($zones as $zone) {
                                                    $zon[$zone->id] = $zone->zone_code.' '.$zone->zone_name;
                                                }
                                                echo form_dropdown('zone_id',$zon, (isset($_POST['zone_id']) ? $_POST['zone_id'] :''), 'class="form-control select" data-placeholder="'.lang("select").' ' .lang("zone").'"style="width:100%"');
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php } if ($this->Settings->module_clinic) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('diagnosis', 'diagnosis'); ?>
                                        <?php 
                                        $diagnosis_id = isset($quote->diagnosis_id) ? explode(',', $quote->diagnosis_id):'';

                                        $get_fields = $this->site->getcustomfield('ill');
                                        $field ['']=lang('select');
                                        if (!empty($get_fields)) {
                                            foreach ($get_fields as $field_id) {
                                                $field[$field_id->id] = $field_id->name;
                                            }
                                        }
                                        echo form_dropdown('diagnosis[]',$field, (isset($diagnosis_id) ? $diagnosis_id : ''), 'class="form-control select" id="diagnosis" multiple="multiple"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('type', 'type'); ?>
                                        <?php 
                                        $patience_type =['IPD'=>lang('IPD'),'OPD'=>lang('OPD')];
                                        echo form_dropdown('patience_type',$patience_type, (isset($quote->patience_type) ? $quote->patience_type : ''), 'class="form-control select" id="patience_type"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("bed","bed"); ?>
                                        <?php
                                        $tab [''] = lang('please_selected');
                                        foreach ($tables as $table) {
                                            $tab[$table->id] = $table->name;
                                        }
                                        echo form_dropdown('bed', $tab, (isset($quote->bed_id) ? $quote->bed_id : ''), 'class="form-control tip" id="bed" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('surcharge', 'surcharge'); ?>
                                        <?php echo form_input('surcharge', (isset($_POST['surcharge']) ? $_POST['surcharge'] : 0), 'class="form-control input-tip" id="surcharge" required="required"'); ?>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="col-md-4 hide">
                                    <div class="form-group">
                                        <?= lang('po', 'po'); ?>
                                        <?php echo form_input('po', (isset($_POST['po']) ? $_POST['po'] :''), 'class="form-control input-tip" id="po"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" id="sticker">
                        <div class="well well-sm">
                            <div class="clearfix"></div>
                            <div class="form-group hide">
                                <main>
                                    <div id="reader"></div>
                                    <div id="result"></div>
                                </main>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="input-group wide-tip">
                                    <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                        <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                    <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php }
                                        if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
                                                    <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="slTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                <th id="addition_type" class="col-md-2" style="display: none;"><?= lang('type'); ?></th>
                                                <?php
                                                // if ($Settings->product_expiry) {
                                                //     echo '<th class="col-md-1">' . lang("expiry_date") . '</th>';
                                                // }
                                                if ($Settings->product_serial) {
                                                    echo '<th class="col-md-1">' . lang('serial_no') . '</th>';
                                                }
                                                if ($Settings->product_serial) {
                                                    echo '<th class="col-md-1">' . lang('max_serial') . '</th>';
                                                } ?>
                                                <?php
                                                if ($Settings->warranty) {
                                                    echo '<th class="col-md-1">' . lang('warranty') . '</th>';
                                                } ?>
                                                <th class="col-md-1"><?= lang('net_unit_price'); ?></th>
                                                <th><?= lang('unit'); ?></th>
                                                <?php if ($Settings->show_qoh == 1) { ?>  
                                                <th class="col-md-1"><?= lang("qoh"); ?></th>
                                                <?php }?>
                                                <th class="col-md-1"><?= lang('quantity'); ?></th>
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                    echo '<th class="col-md-1">' . lang('discount') . '</th>';
                                                } ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . lang('product_tax') . '</th>';
                                                } ?>
                                                <th>
                                                    <?= lang('subtotal'); ?>
                                                    (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <!-- <div class="form-group">
                                    <?= lang('order_tax', 'sltax2'); ?>
                                    <?php
                                    $tr[''] = '';
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div> -->
                                <div class="form-group">
                                    <?= lang('order_tax', 'sltax2'); ?>
                                    <div class="input-group" style="width:100%">
                                        <?php
                                        $tr[''] = '';
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                        <?php if ($Owner || $Admin) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                <a href="<?php echo admin_url('system_settings/add_tax_rate'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                    <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_discount', 'sldiscount'); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('shipping', 'slshipping'); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('document', 'document') ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('sale_status', 'slsale_status'); ?>
                                    <?php 
                                        if ($Settings->sale_consignment) {
                                            $sst = ['completed' => lang('completed'), 'consignment' => lang('consignment'), 'pending' => lang('pending')];
                                        } else {
                                            $sst = ['completed' => lang('completed'), 'pending' => lang('pending')];
                                        }
                                        echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"'); 
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('payment_term', 'slpayment_term'); ?>
                                    <?php
                                    if ($this->Settings->payment_term) {
                                        echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"');  
                                    } else {
                                        $ptr[""] = "";
                                        foreach ($payment_term as $term) {
                                            $ptr[$term->id] = $term->description;
                                        }
                                        echo form_dropdown('payment_term', $ptr, isset($sale_order->payment_term) ? $sale_order->payment_term : "", 'id="slpayment_term" data-placeholder="' . lang("payment_term_tip") .  '" class="form-control input-tip select" style="width: 100%;"');
                                    } ?>
                                </div>
                            </div>
                            <?php if ($Owner || $Admin || $GP['sales-payments']) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('payment_status', 'slpayment_status'); ?>
                                    <?php 
                                    if ($Settings->module_account) {
                                        // prevent error when edit sales
                                        $pst = ['pending' => lang('pending'), 'due' => lang('due'), 'paid' => lang('paid')];
                                    } else {
                                        $pst = ['pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid')];
                                    }
                                    echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>
                                </div>
                            </div>
                            <?php } else {
                                echo form_hidden('payment_status', 'pending');
                            } ?>
                            <div class="col-sm-4 hide">
                                <div class="form-group">
                                    <?= lang('shipping_request', 'slshipping_request'); ?>
                                    <?php 
                                    $sh_opt = [0 => lang('no'), 1 => lang('yes')];
                                    echo form_dropdown('shipping_request', $sh_opt, '', 'class="form-control input-tip" required="required" id="slshipping_request"'); ?>
                                </div>
                            </div>

                        <div class="clearfix"></div>
                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang('payment_reference_no', 'payment_reference_no'); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang('amount', 'amount_1'); ?>
                                                        <input name="amount-paid" type="text" id="amount_1" class="pa form-control kb-pad amount" />
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang('gift_card_no', 'gift_card_no'); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad" />

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->bpas->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php if ($this->Settings->accounting) { ?>
                                                <div class="col-sm-4" id="bank_acc">
                                                    <div class="form-group">
                                                        <?= lang("bank_account", "bank_account_1"); ?>
                                                        <?php
                                                        $bankAccounts =  $this->site->getAllBankAccounts();
                                                        //    $bank = array('0' => '-- Select Bank Account --');
                                                        $default_paid = $this->accounting_setting->default_cash;
                                                       // if ($Owner || $Admin) {
                                                        foreach ($bankAccounts as $bankAcc) {
                                                            $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
                                                        }
                                                        echo form_dropdown('bank_account', $bank, $default_paid, 'id="bank_account_1" class="ba form-control kb-pad bank_account" required="required" data-bv-notempty="true"');
                                                       /* } else {
                                                            $userBankAccounts =  $this->site->getAllBankAccountsByUserID();
                                                            $ubank = array('0' => '-- Select Bank Account --');
                                                            foreach ($userBankAccounts as $userBankAccount) {
                                                                $ubank[$userBankAccount->accountcode] = $userBankAccount->accountcode . ' | ' . $userBankAccount->accountname;
                                                            }
                                                            echo form_dropdown('bank_account', $ubank, $default_paid, 'id="bank_account_1" required="required" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
                                                        }*/
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type" placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang('Visa'); ?></option>
                                                            <option value="MasterCard"><?= lang('MasterCard'); ?></option>
                                                            <option value="Amex"><?= lang('Amex'); ?></option>
                                                            <option value="Discover"><?= lang('Discover'); ?></option>
                                                        </select>
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control" placeholder="<?= lang('month') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pcheque_1" style="display: none;">
                                            <div class="form-group"><?= lang('cheque_no', 'cheque_no_1'); ?>
                                            <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <?= lang('payment_note', 'payment_note_1'); ?>
                                        <textarea name="payment_note" id="payment_note_1" class="pa form-control kb-text payment_note"></textarea>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        </div>
                        
                        <div class="clearfix"></div>
                        <div id="shipping_request_form" style="display: none;">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= lang('shipping_request_info', 'shipping_request_info'); ?>
                                        <div class="col-md-12 well well-sm well_1">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <?= lang('phone', 'shipping_request_phone'); ?>
                                                    <?= form_input('shipping_request_phone', '', 'class="form-control tip" id="slshipping_request_phone"'); ?>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <?= lang('address', 'shipping_request_address'); ?>
                                                    <textarea name="shipping_request_address" id="slshipping_request_address" class="pa form-control kb-text shipping_request_address"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <?= lang('note', 'shipping_request_note'); ?>
                                                    <textarea name="shipping_request_note" id="slshipping_request_note" class="pa form-control kb-text shipping_request_note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                    <div class="row" id="bt">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('sale_note', 'slnote'); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('staff_note', 'slinnote'); ?>
                                    <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : (isset($inv->staff_note) ? $inv->staff_note : '')), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="fprom-group">
                                <?php echo form_submit('add_sale', lang('submit'), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <?php echo form_submit('add_sale_new', lang('submit_new'), 'id="add_sale_new" class="btn btn-info hide" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                            </div>
                        </div>
                    </div>
                    <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                        <table class="table table-condensed totals" style="margin-bottom:0;">
                            <tr class="warning">
                                <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                                <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                                <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                    ?>
                                    <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                                    <?php
                                } ?>
                                <?php if ($Settings->tax2) {
                                    ?>
                                    <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                                    <?php
                                } ?>
                                <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                                <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                            </tr>
                        </table>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
        <div class="modal-dialog" style="width:50%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?=lang('close');?></span>
                    </button>
                    <h4 class="modal-title" id="comboModalLabel"></h4>
                </div>
                <div class="modal-body" style="margin-top:-15px !important;">
                    <label class="table-label"><?= lang("combo_products"); ?></label>
                    <table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                        <thead>
                            <tr>
                                <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                <?php if ($Settings->qty_operation) { ?>
                                    <th><?= lang('width') ?></th>
                                    <th><?= lang('height') ?></th>
                                <?php } ?>
                                <th><?= lang('quantity') ?></th>
                                <th><?= lang('price') ?></th>
                                <th width="3%">
                                    <a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="prModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <form class="form-horizontal" role="form">
                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                                <div class="col-sm-8">
                                    <?php
                                    $tr[''] = '';
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('ptax', $tr, '', 'id="ptax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($Settings->product_serial) { ?>
                            <div class="form-group">
                                <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pserial">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pquantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                            <div class="col-sm-8">
                                <div id="punits-div"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_variant') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div"></div>
                            </div>
                        </div>
                        <?php if($this->Settings->product_option) { ?>
                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div_1"></div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                            <div class="form-group">
                                <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pdiscount">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pdescription" class="col-sm-4 control-label"><?= lang('description') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdescription">
                            </div>
                        </div>
                        <?php if ($Settings->sale_man && $Settings->commission) { ?>
                            <label for="pdescription" class="col-sm-4 control-label"><?= lang('saleman') ?></label>
                            <div class="col-sm-8">
                                <div class="form-group">
                                     <select id="saleman_item" name="saleman_item" class="form-control input-tip select">
                                        <?php
                                        echo '<option value="">----------</option>';
                                        if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                            echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
                                        } else {
                                            foreach($salemans as $agency){
                                                echo '<option value="'.$agency->id.'">'.$agency->first_name.' '.$agency->last_name.'</option>';
                                            }
                                        } ?>
                                    </select> 
                                </div>
                            </div>
                        <?php } ?>
                        <table class="table table-striped">
                            <tr>
                                <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                                <th style="width:25%;"><span id="net_price"></span></th>
                                <th style="width:25%;"><?= lang('product_tax'); ?></th>
                                <th style="width:25%;"><span id="pro_tax"></span></th>
                            </tr>
                        </table>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                            <div class="form-group">
                                <label for="psubt" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="psubt" disabled="disabled">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pdiscount" class="col-sm-4 control-label"><?= lang('adjust_subtotal') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="padiscount" placeholder="<?= lang('nearest_subtotal'); ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <input type="hidden" id="punit_price" value="" />
                        <input type="hidden" id="old_tax" value="" />
                        <input type="hidden" id="old_qty" value="" />
                        <input type="hidden" id="old_price" value="" />
                        <input type="hidden" id="row_id" value="" />
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mcode">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mname">
                            </div>
                        </div>
                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>
                                <div class="col-sm-8">
                                    <?php
                                    $tr[''] = '';
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mquantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="munit" class="col-sm-4 control-label"><?= lang('unit') ?> *</label>
                            <div class="col-sm-8">
                                <?php
                                $uts[''] = '';
                                foreach ($units as $unit) {
                                    $uts[$unit->id] = $unit->name;
                                }
                                echo form_dropdown('munit', $uts, '', 'id="munit" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                            <div class="form-group">
                                <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="mdiscount">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mprice">
                            </div>
                        </div>
                        <table class="table table-striped">
                            <tr>
                                <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                                <th style="width:25%;"><span id="mnet_price"></span></th>
                                <th style="width:25%;"><?= lang('product_tax'); ?></th>
                                <th style="width:25%;"><span id="mpro_tax"></span></th>
                            </tr>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= lang('enter_info'); ?></p>
                    <div class="alert alert-danger gcerror-con" style="display: none;">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <span id="gcerror"></span>
                    </div>
                    <div class="form-group">
                        <?= lang('card_no', 'gccard_no'); ?> *
                        <div class="input-group">
                            <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#" id="genNo"><i class="fa fa-cogs"></i></a></div>
                        </div>
                    </div>
                    <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname" />
                    <div class="form-group">
                        <?= lang('value', 'gcvalue'); ?> *
                        <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('price', 'gcprice'); ?> *
                        <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('customer', 'gccustomer'); ?>
                        <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('expiry_date', 'gcexpiry'); ?>
                        <?php echo form_input('gcexpiry', $this->bpas->hrsd(date('Y-m-d', strtotime('+2 year'))), 'class="form-control date" id="gcexpiry"'); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#gccustomer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            }
        });
        $('#genNo').click(function() {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
        $('#sltax2').change(function(){
            $.ajax({
                type: "get",
                url: site.base_url + "sales/getSaleRef/" + $(this).val(),
                success: function(dataResult){
                    if(dataResult){
                        localStorage.setItem('slref', dataResult);
                        $('#slref').val(dataResult);
                    } else {
                        console.log("Not Found!");
                    }
                },
                // error: function(jqXHR, textStatus, errorThrown){
                //     alert("Error!: " + textStatus);
                // },
                // complete: function(xhr, statusText){
                //     alert(xhr.status + " " + statusText);
                // }
            });
        });
        var destination;
        $("#destination").on("select2-focus",function(e) {
            destination = $(this).val();
        }).on("select2-close",function(e){
            if($(this).val() != '' && $(this).val() == $("#from").val()) {
                $(this).select2('val',destination);
                bootbox.alert("<?= lang("please_select_different_destination")?>");
            }
        });
        var from;
        $("#from").on("select2-focus",function(e){
            from=$(this).val();
        }).on("select2-close",function(e){
            if($(this).val() != '' && $(this).val() == $('#destination').val()){
                $(this).select2('val',from);
                bootbox.alert("<?= lang("please_select_different_destination")?>");
            }
        });
        $(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
            $(this).autocomplete({
                source: '<?= admin_url('products/suggestions'); ?>',
                minLength: 1,
                autoFocus: false,
                delay: 250,
                response: function (event, ui) {
                    if (ui.content.length == 1 && ui.content[0].id != 0) {
                        ui.item = ui.content[0];
                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                        $(this).removeClass('ui-autocomplete-loading');
                    }
                },
                select: function (event, ui) {
                    event.preventDefault();
                    if (ui.item.id !== 0) {
                        var parent = $(this).parent().parent();
                        parent.find(".combo_product_id").val(ui.item.id);
                        parent.find(".combo_name").val(ui.item.name);
                        parent.find(".combo_code").val(ui.item.code);
                        parent.find(".combo_price").val(formatDecimal(ui.item.price));
                        parent.find(".combo_qty").val(formatDecimal(1));
                        if (site.settings.qty_operation == 1) {
                            parent.find(".combo_width").val(formatDecimal(1));
                            parent.find(".combo_height").val(formatDecimal(1));
                        }
                        $(this).val(ui.item.label);
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });

    });
</script>
<script type="text/javascript" src="<?= $assets . 'js/html5-qrcode.min.js' ?>"></script> 
<script type="text/javascript">
    const scanner = new Html5QrcodeScanner('reader', { 
        qrbox: {
            width: 650,
            height: 650,
        },  
        fps: 20, 
    });
    scanner.render(success, error);
    function success(result) {
        $("#add_item").val(result);
        $('#add_item').trigger('keydown');
    }
    function error(err) {
        console.error(err);
    }
</script>
<!--
<script type="text/javascript">
    $(document).ready(function() {
        calculateSurcharge();
        $('#sldate').on('change', function() {
            calculateSurcharge();
        });
    });
    function calculateSurcharge() {
        if (quote != null && quote.bed_id != null) {
            var quoteDateStr = quote.date;
            var saleDateStr  = $('#sldate').val();
            var quoteDate    = moment(quoteDateStr, 'YYYY-MM-DD HH:mm:ss');
            var saleDate     = moment(saleDateStr, 'DD/MM/YYYY HH:mm');
            var daysDiff     = saleDate.diff(quoteDate, 'days') + 1;
            var surcharge_amount = daysDiff * quote.bed_price;
            $('#surcharge').val(is_numeric(surcharge_amount) ? surcharge_amount:0);
        }
    }
</script>
-->