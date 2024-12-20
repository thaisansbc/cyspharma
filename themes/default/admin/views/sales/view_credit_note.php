<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<head>
    <meta charset="utf-8">
    <style>
        .container {
            width: 29.7cm;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }
        .table_pro {
            width: 100%;
        }
        .table_pro tr > th {
            text-align: center !important;
            font-size: 11px;
            padding: 5px;
        }
        .table_pro tr > th, .table_pro tr > td {
            border: 1px solid #000 !important;
            font-size: 11px;
        }
        .table_top tr > th, .table_top tr > td {
            border: 1px solid #000 !important;
            font-size: 11px;
            text-align: center;
        }
        .well { padding-bottom: 0px; }
        .qrimg { width: 50px !important; }
        
        @media print {
            .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
                border-top: 1px solid #000000 !important;
            }
            @page {
                margin: 0.15in 0 1.68in 0;
            }
            .note_ { border: 1px solid black !important; }
            thead { display: table-header-group; }
        }

        @font-face {
            font-family: 'KhmerOS_muollight';
            src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
        }
    </style>
</head>
<body>
<div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; margin-top: -15px !important;">
    <div class="modal-content">    
        <div class="modal-body">
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
                echo '<div class="alert alert-info no-print"><p>' . lang("sale_is_returned") . ': ' . $inv->return_sale_ref;
                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                echo '</p></div>';
            } ?>
            <table class="" border="0" cellspacing="0" style="width: 100%;" id="tb_outter">
                <thead>
                    <tr>
                        <td>
                            <div class="col-xs-2">
                                <?php
                                if($islogo){
                                if ($logo) { ?>
                                    <div><img style="width: 180px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$biller->logo; ?>" ></div>
                                <?php } 
                                }else{
                                    echo '&nbsp;';
                                }
                                ?>                                
                            </div>
                            <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                                <h2 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h2>
                                <h2 style="font-weight: bold; font-family: 'FontAwesome';"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                                <div style="font-size:14px; font-weight: bold; line-height: 110%; text-align: center;">
                                    <?php
                                        echo '<p style="letter-spacing: 3px;">' . $biller->cf3 . '</p>';
                                        echo '<p>' . $biller->cf2 . '</p>';
                                        if($biller->address){
                                            echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                                        }
                                        if($biller->phone){
                                            echo '<p>Tel: ' . $biller->phone . '</p>';
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="col-xs-2 text-right order_barcodes" style="margin-top: 15px;">
                                <?= $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="col-xs-6" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div>
                            <div class="col-xs-3 text-center" style="font-size: 20px; line-height: 55%; font-weight: bold; padding: 0;">
                                <p><?= lang('credit_note') ?></p>
                            </div>
                            <div class="col-xs-3" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div> <!-- #5DADE2 -->
                        </td>
                    </tr>
                    <tr style="font-size: 11px;">
                        <td>
                            <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 49%; float: left; margin-right: 2%; font-weight: bold; margin-bottom: 5px !important;">
                                <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 85%; margin-bottom: -5px; font-style: italic !important;">ព័ត៍មានអតិថិជន</caption>
                                <tr>
                                    <td style="width: 35%; padding-left: 5px;">អតិថិជន / <?= lang('customer'); ?></td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><b><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></b></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                                    <td>:</td>
                                    <td><?= $customer->phone ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន /<?= lang('address'); ?></td>
                                    <td style="vertical-align: top;">:</td>
                                    <td style="padding-bottom: 3px;"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country; ?></td>
                                </tr>
                                <?php if($inv->payment_term) {?>
                                <tr>
                                    <td style="padding-left: 5px;">Payment Term</td>
                                    <td>:</td>
                                    <td><?= $inv->payment_term ?> Day</td>
                                </tr>
                                <?php }?>
                            </table>
                            <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 49%; font-weight: bold;">
                                <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;">ឯកសារយោង</caption>
                                <tr>
                                    <td style="width: 25%; padding-left: 5px;">វិក្កយបត្រ / Invoice NO</td>
                                    <td style="width: 1%;">:</td>
                                    <td style="width: 30%;"><?= $inv->reference_no; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">កាលបរិច្ឆាទ / Date</td>
                                    <td>:</td>
                                    <td><?= $this->bpas->hrsd($inv->date); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5px;">អ្នកគិតលុយ / Cashier</td>
                                    <td>:</td>
                                    <td><?php echo $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                                </tr>
                                <?php 
                                if($sold_by){
                                   // var_dump($sold_by);
                                ?>
                                <tr>
                                    <td style="padding-left: 5px;">អ្នកលក់ / Sale Man</td>
                                    <td>:</td>
                                    <td><?php echo $sold_by->first_name . ' ' . $sold_by->last_name; ?></td>
                                </tr>
                                 <?php }?>
                            </table>
                        </td>
                    </tr>
                    <!-- <tr><td>&nbsp;</td></tr> -->
                </thead>
                <tbody>
                     <?php  $detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";  ?>
                    <tr>
                        <td>
                            <div class="table-responsive">
                                <table class="table" style="width: 100%;">
                                    <thead style="border: 1px solid #000000 !important; font-size: 12px;">
                                        <tr style="border: 1px solid #000000 !important;">
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 10px;">ល.រ</br>Nº<br></th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 180px">បរិយាយ<br>Description<br></th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 50px;">ចំនួន<br>Qty</th>
                                            <th style="background-color: #5DADE2 !important;  padding: 5px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 10%;">តំលៃ<br>Price</th>
                                            <?php 
                                            if ($Settings->product_discount) {
                                                echo '<th style="background-color: #5DADE2 !important;  padding: 2px 0; text-align: center !important;border: 1px solid #000000 !important; line-height:12px !important; width: 12%">បញ្ចុះតំលៃ<br>Discount</th>';
                                            } ?>
                                            <th style="background-color: #5DADE2 !important;  padding: 2px 0; text-align: center !important; border: 1px solid #000000 !important; line-height:12px !important; width: 17%;">សរុប<br>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="content-print">
                                    <?php 
                                    $i = 1;
                                    $stotal = 0;
                                    $unit = "";
                                    $qty = 0; 
                                    // var_dump(sizeof($rows));
                                    // for($k= 0 ; $k < sizeof($rows) /10; $k ++){\
                                        foreach($rows as $rowx){
                                            if($rowx->option_id == 0 || $rowx->option_id == ""){
                                          
                                                $qty = $rowx->unit_quantity;
                                            } else {
                                                $unit = $rowx->variant;
                                                $qty = $rowx->unit_quantity;
                                            }
                                            $stotal += $qty * $rowx->unit_price; 
                                        }
                                    foreach($rows as $row){
                                        if($row->option_id == 0 || $row->option_id == ""){
                                      
                                            $qty = $row->unit_quantity;
                                        } else {
                                
                                            $qty = $row->unit_quantity;
                                        }
                                    ?>
                                        <tr style="line-height: 5px !important; border:1px solid #000000 !important; font-size: 12px;">
                                            <td style="padding: 2px; border-right: 1px solid #000000 !important; text-align:center;"><?= $i ?></td>
                                            <td style="padding: 2px; border-right: 1px solid #000000 !important; font-size: 12px;" class="cap-height">
                                                <?php echo $row->product_code.' '.$row->product_name; ?>
                                                <?= $row->description? '['.$row->description.']':''; ?>
                                            </td>
                                            <td style="padding: 2px; text-align:center; border-right: 1px solid #000000 !important;">
                                                <?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->unit_name; ?></td>
                                            <td style="padding: 2px; text-align:center; border-right: 1px solid #000000 !important;">
                                                <!-- <?= ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' ?> -->
                                                <?php if($row->net_unit_price == 0){ echo "Free"; } else { echo $detault_currency.$this->bpas->formatMoney($row->net_unit_price); } ?>
                                            </td>
                                            <?php
                                                if ($Settings->product_discount){
                                                    echo '<td style="padding: 2px; text-align: center; border-right: 1px solid #000000 !important;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $detault_currency.$this->bpas->formatMoney($row->item_discount) . '</td>';
                                                }
                                            ?>
                                            <td style="padding: 2px; text-align: right; border-right: 1px solid #000000 !important;">
                                                <?php if($row->unit_price == 0){echo "Free";} else { echo $row->subtotal!=0 ? $detault_currency.$this->bpas->formatMoney($row->subtotal) : $t ; ?>&nbsp<?php } ?> 
                                            </td>      
                                        </tr>
                                    <?php
                                    $i++;
                                    }
                                    $G = ((count($rows) / 10) - floor(count($rows) / 10)) * 10;
                                    if(count($rows) != 10){
                                        if($G < 10 && $G != 0){
                                            $G++;
                                            $num = count($rows) + 1;
                                            while($G <= 10) {
                                                echo ' 
                                                    <tr style="line-height: 8px !important; border:1px solid #000000 !important;">
                                                        <td style="padding: 2px; border-right: 1px solid #000000 !important; text-align:center;">'.$num.'</td>
                                                        <td style="padding: 2px; border-right: 1px solid #000000 !important;"></td>
                                                        <td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>
                                                        <td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                                if ($Settings->product_discount){
                                                    echo '<td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;"></td>';
                                                }
                                                echo   '<td style="padding: 2px; text-align:center;border-right: 1px solid #000000 !important;" ></td>     
                                                    </tr>'; 
                                                $G++;
                                                $num++;
                                            }
                                        }
                                    }
                                    // }    
                                    ?>
                                    </tbody>
                                    <tfoot style=" font-size: 12px;">
                                        <tr style="font-size: 11px;border-top: 2px solid #000000 !important;">
                                            <td style="vertical-align: top !important; padding: 3.5px 5px; border: 0 solid !important;" rowspan="8" colspan="2">បញ្ជាក់៖ ទំនិញទិញរួចមិនអាចដូរវិញបានទេ!</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">សរុបទឹកប្រាក់ / Total</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency.$this->bpas->formatMoney($stotal)?></td>
                                        </tr>
                                        <?php if ($inv->order_discount != 0) {
                                            echo '<tr style="font-size: 11px;">
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">បញ្ចុះតម្លៃ / ' . lang("order_discount") . '</td>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                                        }
                                        ?>
                                        <?php 
                                          if ($inv->order_tax !=0) {
                                            echo '<tr style="font-size: 11px;">';
                                            echo '<td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">អាករលើតម្លៃបន្ថែម / ' . lang("vat") . '</td>
                                                    <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td>
                                                </tr>';
                                        }
                                        ?>
                                        <?php if ($inv->shipping != 0) {
                                            echo '<tr>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ថ្លៃដឹកជញ្ជូន / ' . lang("shipping") . '</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">$' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                                        }
                                        ?>
                                        <tr style="font-size: 11px;">
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3">ចំនួនទឹកប្រាក់ / Grand Total</td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;"><?= $detault_currency.$this->bpas->formatMoney($inv->grand_total)?></td>
                                        </tr>
                                        <tr style="font-size: 11px;">
                                            <?php $usa ="ចំនួនទឹកប្រាក់ជាដុល្លា / USA"; $kh ="ចំនួនទឹកប្រាក់ជារៀល / Riel";  ?>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;" colspan="3"> <?=  $detault_currency == "៛" ? $usa : $kh ; ?> </td>
                                            <td style="text-align: right; border:1px solid !important; font-weight: bold; padding: 5px 5px;">
                                                <?=  $detault_currency == "៛" ? "$".$this->bpas->formatMoney( $inv->grand_total / $inv->currency_rate_kh) : "៛".$this->bpas->formatMoney( $inv->grand_total * $inv->currency_rate_kh)?></td>
                                        </tr>
                               
                                       
                                    </tfoot>
                                </table>
                            </div>
                
                            <div class="row">
                                <div class="col-xs-6">
                                    <?php
                                        if ($inv->note || $inv->note != "") { ?>
                                            <div class="well well-sm note_" style="font-size: 8px;">
                                                <p class="bold"><?= lang("note"); ?>:</p>
                                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                                            </div>
                                        <?php
                                        }
                                        if ($inv->staff_note || $inv->staff_note != "") { ?>
                                            <div class="well well-sm staff_note" style="font-size: 8px;">
                                                <p class="bold"><?= lang("staff_note"); ?>:</p>
                                                <div><?= $this->bpas->decode_html($inv->staff_note); ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <!-- <div class="col-xs-6" style="display: none;">
                                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
                                        <div class="well well-sm">
                                            <p><?= lang("created_by"); ?> : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>
                                            <p><?= lang("date"); ?> : <?= $this->bpas->hrld($inv->date); ?></p>
                                            <?php if ($inv->updated_by) { ?>
                                                <p><?= lang("updated_by"); ?> : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                                <p><?= lang("update_at"); ?> : <?= $this->bpas->hrld($inv->updated_at); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div> -->
                                </div>
                                
                                <?php if (!empty($payments)) { ?>
                                    <div class="row staff_note hide">
                                        <div class="col-xs-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-condensed print-table">
                                                    <thead>
                                                        <tr>
                                                            <th><?= lang('date') ?></th>
                                                            <th><?= lang('payment_reference') ?></th>
                                                            <th><?= lang('paid_by') ?></th>
                                                            <th><?= lang('amount') ?></th>
                                                            <th><?= lang('created_by') ?></th>
                                                            <th><?= lang('type') ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($payments as $payment) { ?>
                                                            <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                                                <td><?= $this->bpas->hrld($payment->date) ?></td>
                                                                <td><?= $payment->reference_no; ?></td>
                                                                <td><?= lang($payment->paid_by);
                                                                    if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                                        echo ' (' . $payment->cc_no . ')';
                                                                    } elseif ($payment->paid_by == 'Cheque') {
                                                                        echo ' (' . $payment->cheque_no . ')';
                                                                    }
                                                                    ?></td>
                                                                <td><?= $this->bpas->formatMoney($payment->amount); ?></td>
                                                                <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                                                <td><?= lang($payment->type); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <div class="row" style="font-size: 11px;">
                <div class="col-xs-4 pull-left text-center">
                    <p style="margin-top: 2px;">អ្នកលក់ / Seller Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center">
                    <p style="margin-top: 2px;">អ្នកទិញ / Buyer Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
                <div class="col-xs-4 pull-right text-center">
                    <p style="margin-top: 2px;">អ្នកដឹក / Delivery Signature</p><br><br>
                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 35px auto 0 auto;">
                </div>
            </div>
            <div class="container " style="width: 100%;">
            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
            <div class="row" >
                <?php if (!$Supplier || !$Customer) { ?>
                    <div class="buttons">
                        <div class="btn-group btn-group-justified">
                            
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/view/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view') ?>">
                                    <i class="fa fa-file-text-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('view') ?></span>
                                </a>
                            </div>
                            
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/tax_invoice/' . $inv->id) ?>" target="_blank" class="tip btn btn-primary" title="<?= lang('tax_invoice') ?>">
                                    <i class="fa fa-download"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('tax_invoice') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/view/'.$inv->id.'/issue_inv') ?>" class="tip btn btn-primary" title="<?= lang('view') ?>">
                                    <span class="hidden-sm hidden-xs"><?= lang('issue_invoice') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
            
                    <div class="buttons">
                        <div class="btn-group btn-group-justified">
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/view_a5/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view_a5') ?>">
                                    <i class="fa fa-file-text-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('view_a5') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('add_payment') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2">
                                    <i class="fa fa-dollar"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('payment') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('add_delivery') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2">
                                    <i class="fa fa-truck"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('delivery') ?></span>
                                </a>
                            </div>
                            <?php if ($inv->attachment) {
                            ?>
                                <div class="btn-group">
                                    <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                        <i class="fa fa-chain"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                    </a>
                                </div>
                            <?php
                            } ?>
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                    <i class="fa fa-envelope-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                    <i class="fa fa-download"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                                </a>
                            </div>
                            <?php if (!$inv->sale_id) {
                            ?>
                                <div class="btn-group">
                                    <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                        <i class="fa fa-edit"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_sale') ?></b>" data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" data-html="true" data-placement="top">
                                        <i class="fa fa-trash-o"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                    </a>
                                </div>
                            <?php
                            } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();            
    });
</script>   