<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.sledit', function(e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?= lang('you_will_loss_sale_data') ?>", function(result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });
</script>
<style type="text/css" media="all">
    table {
        font-size: 12px !important;
        
    }
    .border_table{
        border:1px solid #dddddd;
        width: 100%;
    }
    .border_table th{
        border: 1px solid #cccccc;
        color: #2a495a;
        background-color: #eeeeee;
        padding: 8px;
    }
    .border_table td{
        border: 1px solid #ddd;
        padding: 8px;
    }

    @media print {
        table {
            font-size: 12px !important;
            
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_no") . ' ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="sledit">
                                <i class="fa fa-edit"></i> <?= lang('edit_sale') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-dollar"></i> <?= lang('add_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <?php if (!$inv->sale_id) { ?>
                            <li>
                                <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                    <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= admin_url('sales/return_sale/' . $inv->id) ?>">
                                    <i class="fa fa-angle-double-left"></i> <?= lang('return_sale') ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
                    echo '<div class="alert alert-info no-print"><p>' . lang("sale_is_returned") . ': ' . $inv->return_sale_ref;
                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                    echo '</p></div>';
                } ?>

                <div class="table-responsive">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 20%;">
                                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" style="width: 200px;">
                            </td>
                            <td style="width: 60%;">
                                <div>
                                    <div style="text-align: center;">
                                        <h1 style="font-family:'Khmer OS Muol Light'; font-size:28px; font-weight: bold; margin-bottom: -15px;"><?= $biller->cf1; ?></h1>
                                        <hr style="width:40%; height:5px; border-bottom: 1px solid black; display: block; margin-left: 30%;">
                                        <h2 style="line-height: normal; margin-top: -18px;">
                                            <?= $biller->company; ?> <br>
                                            <span style="font-family:'Khmer OS Muol Light'; font-weight: normal;">
                                                <?= $biller->invoice_footer; ?></span><br>
                                            
                                            TREATMENT PLAN
                                        </h2>
                                    </div>
                                </div>            
                            </td>
                            <td style="width: 20%;"></td>
                        </tr>
                    </table>
                </div>

                
                <div class="clearfix"></div>
                <?php if ($Settings->invoice_view == 1) { ?>
                    <div class="col-xs-12 text-center">
                        <h1><?= lang('tax_invoice'); ?></h1>
                    </div>
                <?php } ?>
                <div class="clearfix"></div>
                <div class="col-xs-6">
                    <table width="100%">
                        <tr>
                            <td>អតិថិជន / <?= lang("customer"); ?></td>
                            <td>: <?= ($customer->company != "-" ? $customer->company : $customer->name); ?></td>
                        </tr>
                        <tr>
                            <td>ភេទ​ / <?= lang("gender"); ?></td>
                            <td>: <?= $customer->cf4 ?></td>
                        </tr>
                        <tr>
                            <td>អាយុ / <?= lang("age"); ?></td>
                            <td>: <?= $customer->cf6 ?></td>
                        </tr>
                        <tr>
                            <td>អាសយដ្ឋាន / <?= lang("address"); ?></td>
                            <td>: <?= $customer->address . " " . $customer->city . " " . $customer->postal_code . " " . $customer->state . " " . $customer->country; ?></td>
                        </tr>
                        <tr>
                            <td>ទូរស័ព្ទ / <?= lang("tel"); ?></td>
                            <td>: <?= $customer->phone ?></td>
                        </tr>
                        <?php if($customer->email) { ?>
                        <tr>
                            <td>អ៊ីមែល / <?= lang("email"); ?></td>
                            <td>: <?= $customer->email ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                    
                </div>
                <div class="col-xs-5">
                    <table width="100%">
                        <tr>
                            <td>លេខយោង / <?= lang("ref"); ?></td>
                            <td>: <?= $inv->reference_no; ?>
                                <?php if (!empty($inv->return_sale_ref)) {
                                    echo '<p>' . lang("return_ref") . ': ' . $inv->return_sale_ref;
                                    if ($inv->return_id) {
                                        echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                    } else {
                                        echo '</p>';
                                    }
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>ថ្ងៃ ខែ ឆ្នាំ /​ <?= lang("date"); ?></td>
                            <td>: <?= $this->bpas->hrld($inv->date); ?></td>
                        </tr>
                        <tr>
                            <td>ទន្តបណ្ឌិត​ / Dentist</td>
                            <td>: <?= $saleman->first_name.' '.$saleman->last_name; ?></td>
                        </tr>
                        <tr>
                            <td>អ្នកទូទាត់ / Cashier</td>
                            <td>: <?= $biller->name; ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("branch");?></td>
                            <td>: <?= $warehouse->name;?></td>
                        </tr>
                    </table>
                </div>
                <div class="clearfix"></div>
                
                <div class="table-responsive">
                    <table style="width: 100%;">
                        <tr>
                            <?php 
                            $ills = $this->bpas->ills();
                            foreach ($ills as $key => $value) {
                                if(!empty($key)){
                            ?>
                            <td style="width: 10%;">
                                <div>
                                    <?php 
                                    if($key == $customer->cf1){
                                        echo '<i class="fa fa-check-square-o" style="font-size:17px;" aria-hidden="true"></i>';
                                    }else{
                                        echo '<span style="width: 20px;height: 20px;border:1px solid #cccccc;">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                                    }
                                    ?>
                                    
                                    <label for="ch1"><?= $value;?></label>
                                </div>
                            </td>
                            <?php } }?>
                        </tr>
                    </table>
                </div>
                <?php 
                $imgs = 'assets/uploads/wpain/' . $inv->image;
                $check_model = explode("-", $inv->image);
                $model_teeth = "assets/uploads/mix_tooth.jpg";

                ?>
                <div style="width: 100%;  text-align: center;">
                    <div style="background-image: url('<?php echo base_url() . $model_teeth; ?>') !important; 
                      display:block;
                      line-height: 0 !important; 
                      vertical-align:middle !important; 
                      background-size:contain !important; 
                      background-repeat:no-repeat !important;">
                      <?php 
                      if($inv->image){
                        ?>
                        <img src="<?php echo base_url() . $imgs; ?>" class="img-fluid" width="100%">
                    <?php }?>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="border_table">
                        <thead>
                            <tr>
                                <th><?= lang("no"); ?></th>
                                <th><?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                                <?php if ($Settings->indian_gst) { ?>
                                    <th><?= lang("hsn_code"); ?></th>
                                <?php } ?>
                                <th><?= lang("quantity"); ?></th>
                                <th><?= lang("unit"); ?></th>
                                <?php
                                if ($Settings->product_serial) {
                                    echo '<th style="text-align:center; vertical-align:middle;">' . lang("serial_no") . '</th>';
                                }
                                ?>
                                <?php
                                if ($Settings->warranty) {
                                    echo '<th style="text-align:center; vertical-align:middle;">' . lang("warranty_expired") . '</th>';
                                }
                                ?>
                                <th style="padding-right:20px;"><?= lang("price"); ?></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                }
                                ?>
                                <th style="padding-right:20px;"><?= lang("subtotal"); ?></th>
                            </tr>

                        </thead>
                        <tbody>

                            <?php $r = 1;
                            $currency_rate_usd = 1;
                            $currency_rate_kh=$inv->currency_rate_kh;
                            $currency_rate_bat=$inv->currency_rate_bat;

                            $usd = " $";
                            $khm = " ៛";
                            $bat = " ฿";
                            foreach ($rows as $row) :
                            ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?php
                                        if ($row->comment) {
                                            echo ' [' . $row->comment . ']';
                                        }?>
                                    </td>
                                    <?php if ($Settings->indian_gst) { ?>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php } ?>
                                    <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                    <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                    <?php
                                    if ($Settings->product_serial) {
                                        echo '<td>' . $row->serial_no . '</td>';
                                    }
                                    if ($Settings->warranty) {
                                        $date = $inv->date;
                                        $warranty =date('Y-m-d', strtotime($date. ' + '.$row->warranty.' days'));
                                        $check_warranty = date('Y-m-d') > $warranty ? lang('expired') : $this->bpas->hrsd($warranty);                                        
                                        echo '<td>' .$check_warranty. '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:80px; vertical-align:middle;padding-right:10px;"><?= $this->bpas->formatMoney($row->real_unit_price); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:70px; vertical-align:middle;padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;

                            if ($return_rows) {
                                echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                                foreach ($return_rows as $row) :
                                ?>
                                    <tr class="warning">
                                        <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                        <td style="vertical-align:middle;">
                                            <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                            <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        </td>
                                        <?php if ($Settings->indian_gst) { ?>
                                            <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                        <?php } ?>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->quantity); ?></td>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                        <?php
                                        if ($Settings->product_serial) {
                                            echo '<td>' . $row->serial_no . '</td>';
                                        }
                                        if ($Settings->warranty) {
                                            echo '<td>' . $row->warranty . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right; width:80px; padding-right:10px;"><?= $this->bpas->formatMoney($row->real_unit_price); ?></td>
                                        <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right; width:70px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                    </tr>
                            <?php
                                    $r++;
                                endforeach;
                            }

                            ?>
                        </tbody>
                        <tfoot>
                            <?php
                            $col = $Settings->indian_gst ? 6 : 5;
                            if ($Settings->product_serial) {
                                $col++;
                            }
                            if ($Settings->warranty) {
                                $col++;
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                $col++;
                            }
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                $col++;
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                                $tcol = $col - 2;
                            } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                                $tcol = $col - 1;
                            } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                                $tcol = $col - 1;
                            } else {
                                $tcol = $col;
                            }
                            ?>
                            <?php if ($inv->grand_total != $inv->total) { ?>
                                <tr>
                                    <td colspan="<?= $tcol; ?>" style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                </tr>
                            <?php } ?>
                            <?php
                            if ($return_sale) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale->grand_total) . '</td></tr>';
                            }
                            if ($inv->surcharge != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                            }
                            ?>


                            <?php if ($inv->order_discount != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                            }
                            ?>
                            <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                            }
                            ?>
                            <?php if ($inv->shipping != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                            }
                            ?>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                            </tr>
                            <tr class="hide">
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                    (<?= $KHM->code; ?>)
                                </td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale * ($currency_rate_kh) ? ($inv->grand_total + $return_sale->grand_total) *  ($currency_rate_kh) : $inv->grand_total *  ($currency_rate_kh)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                            </tr>
                         
                        </tfoot>
                    </table>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <p style="margin-left: 10px;">
                            <span style="font-size: 14px;"><u>សម្គាល់:</u></span><br>
                            - ពេលយល់ព្រមទទូលសេវាកម្ម​ អតិថិជនត្រូវកក់ប្រាក់៥០% / Customers must deposit 50% after agreeing to receive services <br>
                            - ដំណាក់កាលបន្ទាប់ត្រូវបង់បន្ថែម៣០% / Next step, must deposit 30% <br>
                            - ពេលបញ្ចប់សេវាកម្មព្យាបាល អតិថិជនត្រូវបង់បង្រ្គប់​២០% / Customers must pay the rest after finishing the services <br>
                            - ប្រាក់ដែលកក់ហើយមិនអាចដកវិញបានទេ / Deposited amount is not refundable
                        </p>
                    </div>
                </div>

                <div class="row hide">
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("seller"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("buyer"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <?php
                        if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php
                        }
                        if ($inv->staff_note || $inv->staff_note != "") { ?>
                            <div class="well well-sm staff_note">
                                <p class="bold"><?= lang("staff_note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->staff_note); ?></div>
                            </div>
                        <?php } ?>

                        <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                                <div class="well well-sm">
                                    <?=
                                        '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                            . '<br>' .
                                            lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-xs-6" style="display: none;">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
                        <div class="well well-sm">
                            <p><?= lang("created_by"); ?>
                                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

                            <p><?= lang("date"); ?>: <?= $this->bpas->hrld($inv->date); ?></p>
                            <?php if ($inv->updated_by) { ?>
                                <p><?= lang("updated_by"); ?>
                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                <p><?= lang("update_at"); ?>: <?= $this->bpas->hrld($inv->updated_at); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" style="border: 1px solid black;"></div>
                <div class="well-sm">
                    <div class="col-xs-12">
                        <div class="col-xs-12">
                            <!-- <h2 class="text-center">Invoice</h2> -->
                            <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                            <?php
                            echo '<div class="text-center">';
                            echo $warehouse->address . '';
                            echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "" : '') .' '. ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                            echo "</div>";
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <?php if ($payments) { ?>
                    <div class="row no-print">
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
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/view_a5/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_a5') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_a5') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>

                    <div class="btn-group">
                        <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>

                    <div class="btn-group">
                        <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <?php if (!$inv->sale_id) { ?>

                        <div class="btn-group">
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning tip sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_sale") ?></b>" data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" data-html="true" data-placement="top"><i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <!--<div class="btn-group"><a href="<?= admin_url('sales/excel/' . $inv->id) ?>" class="tip btn btn-primary"  title="<?= lang('download_excel') ?>"><i class="fa fa-download"></i> <?= lang('excel') ?></a></div>-->
                </div>
            </div>
        <?php } ?>
    </div>
</div>