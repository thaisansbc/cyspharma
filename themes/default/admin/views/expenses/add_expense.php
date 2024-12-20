<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_expense'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('expenses/add_expense', $attrib); ?>
        <input type="hidden" name="biller_id" value="<?= (isset($biller_id)?$biller_id:'');?>">
        <input type="hidden" name="project_id" value="<?= (isset($project_id)?$project_id:'');?>">
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control '.($Settings->date_with_time ? 'datetime':'date').'" id="date" required="required"'); ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('reference', 'reference'); ?>
                        <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control tip" id="reference"'); ?>
                    </div>
                </div>
                <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                    <div class="col-md-6">
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
                    <div class="col-md-6">
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
                <?php if ($this->Settings->project) { ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="project"><?= lang('project'); ?></label>
                        <?php
                        if (isset($quote)) {
                            $project_id =  $quote->project_id;
                        }elseif(isset($project_id)){
                            $project_id =  $project_id;
                        }else{
                            $project_id =  isset($_POST['project']) ? $_POST['project'] : '';
                        }

                        $pr[''] = lang('select') . ' ' . lang('project');
                        foreach ($projects as $project) {
                            $pr[$project->project_id] = $project->project_name;
                        }
                        echo form_dropdown('project', $pr, $project_id, 'class="form-control" id="project" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('project') . '"');
                        ?>
                    </div>

                </div>
                <?php }?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="user"><?= lang('expense_by'); ?></label>
                        <?php
                        $us[''] = lang('select') . ' ' . lang('user');
                        foreach ($users as $user) {
                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                        }
                        echo form_dropdown('expense_by', $us, (isset($_POST['expense_by']) ? $_POST['expense_by'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                        ?>
                    </div>
                </div>
                <!-- <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('category', 'category'); ?>
                        <?php
                        $ct[''] = lang('select') . ' ' . lang('category');
                        if ($categories) {
                            foreach ($categories as $category) {
                                $ct[$category->id] = $category->name;
                            }
                        }
                        ?>
                        <?= form_dropdown('category', $ct, set_value('category'), 'class="form-control tip" id="category"'); ?>
                    </div>
                </div> -->

                <div class="col-md-6">
                    <div class="form-group all">
                        <?= lang('category', 'category') ?>
                        <div class="input-group" style="width: 100%">
                            <?php 
                            $form_category = null;
                            function formMultiLevelCategory($data, $n, $str = '')
                            {
                                $form_category = ($n ? '<select id="category" name="category" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required"><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                foreach ($data as $key => $categories) {
                                    if (!empty($categories->children)) {
                                        $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
                                        $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'));
                                    } else {
                                        $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
                                    }
                                }

                                $form_category .= ($n ? '</select>' : '');
                                return $form_category;
                            }

                            // echo htmlentities(formMultiLevelCategory($nest_categories, 1));
                            echo formMultiLevelCategory($nest_categories, 1); ?>
                        </div>
                    </div>
                </div>

                
           </div>

            <?php if ($this->Settings->module_account) { ?>
                <table width="100%" id="dynamic_field" border="0">
                    <tr>
                        <td>
                            <div class="form-group bank_pay" style="margin:0px;">
                                <?= lang("category_expense", "category_expense"); ?>
                                <?php
                                $bank = array('0' => '-- Select Bank Account --');
                                foreach ($bankAccounts as $bankAcc) {
                                    $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
                                }
                                echo form_dropdown('bank_account[]', $bank, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="form-group bank_pay" style="margin:0 6px;">
                                <?= lang("paid_by", "paid_by"); ?>
                                <?php
                                $acc_section = array("" => "");
                                foreach ($paid_by as $section) {
                                    $acc_section[$section->accountcode] = $section->accountcode . ' | ' . $section->accountname;
                                }
                                echo form_dropdown('paid_by[]', $acc_section, '', 'id="paid_by"  class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="form-group bank_pay" style="margin:0px;">
                                <?= lang('amount', 'amount'); ?>
                                <input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" />
                            </div>
                        </td>
                        <td>
                            <div class="form-group bank_pay"><?= lang("  ", " "); ?><button type="button" name="add" id="add" class="btn btn-success" style="margin-top:45px;">
                                    <li class="fa fa-plus"></li>
                                </button></div>
                        </td>
                    </tr>
                </table><br>
            <?php }else{ ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('amount', 'amount'); ?>
                        <input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('paid_by', 'paid_by'); ?>
                        <select name="paid_by[]" id="paid_by_1" class="form-control paid_by" data="" required="required">
                            <?= $this->bpas->paid_opts(); ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php }?>
            
            
               
            <div class="form-group">
                <?= lang('attachment', 'attachment') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_expense', lang('add_expense'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
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
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var i = 1;
        <?php
        $bank = array('' => '-- Select Bank Account --');
        foreach ($bankAccounts as $bankAcc) {
            $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
        }
        $dropdown = form_dropdown('bank_account[]', $bank, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
        $acc_section = array("" => "-- Select Paid By --");
        foreach ($paid_by as $section) {
            $acc_section[$section->accountcode] = $section->accountcode . ' | ' . $section->accountname;
        }
        $dropdown2 = form_dropdown('paid_by[]', $acc_section, '', 'id="paid_by_" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
        ?>
        var complex = <?php echo json_encode($dropdown); ?>;
        var complex2 = <?php echo json_encode($dropdown2); ?>;
        $('#add').click(function() {
            // if (i <= 4) {
            $('#dynamic_field').append('<tr id="row' + i + '" class="dynamic-added"><td> <div class="form-group bank_pay" style="margin:0px;"><?= lang("category_expense    ", "category_expense"); ?>' + complex + '</div></td><td><div class="form-group bank_pay" style="margin:0 6px;"><?= lang("paid_by *", "paid_by"); ?>' + complex2 + '</div></td><td><div class="form-group bank_pay" style="margin:0px;"><?= lang('amount *', 'amount'); ?><input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" /></div></td><td><div class="form-group bank_pay" "><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove" style="margin-top:45px;"><li class="fa fa-remove"></li></button></div></td></tr>');
            i++;
            // } else {
            //     alert('<?= lang('max_reached') ?>');
            //     return false;
            // }
        });
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });

    });
</script>