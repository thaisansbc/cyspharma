<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_payment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/add_payment_13", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<input type="hidden" name="salary_id" value="<?= $salary->id ?>"/>
								<input type="hidden" name="biller_id" value="<?= $salary->biller_id ?>"/>
								<input type="hidden" name="year" value="<?= $salary->year ?>"/>
								<?php if ($Owner || $Admin || $GP['payrolls-payments_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } if($Settings->accounting == 1){ ?>
								
									<div class="col-sm-4" id="bank_acc">
	                                    <div class="form-group">
	                                        <?= lang("paying_from", "paying_from"); ?>
	                                        <?php
	                                        	$bankAccounts =  $this->site->getAllBankAccounts();
	                                            $bank = array('' => '-- Select Bank Account --');
	                                        
	                                                foreach($bankAccounts as $bankAcc) {
	                                                    $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
	                                                }
	                                                echo form_dropdown('paying_from', $bank, '', 'id="paying_from" class="form-control bank_account" ');
	                                          
	                                        ?>
	                                    </div>
	                                </div>
								<?php } ?>

							</div>
						</div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="expTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                       <thead>
											<tr>
												<th><?= lang("code") ?></th>
												<th><?= lang("name") ?></th>
												<th><?= lang("net_amount") ?></th>
												<th><?= lang("paid_amount") ?></th>
												<th><?= lang("balance") ?></th>
												<th><?= lang("pay_amount") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataEmp">
											<?php if(isset($salary_items) && $salary_items){
												$dataEmp = "";
												foreach($salary_items as $salary_item){
													if($this->bpas->formatDecimal($salary_item->subtotal - $salary_item->paid) != 0){
														$dataEmp .= "<tr>
																	<td><input name='employee_id[]' value='".$salary_item->employee_id."' type='hidden'/>".$salary_item->empcode."</td>
																	<td>".$salary_item->lastname." ".$salary_item->firstname."</td>
																	<td class='text-right'>".$this->bpas->formatMoney($salary_item->subtotal)."</td>
																	<td class='text-right'>".$this->bpas->formatMoney($salary_item->paid)."</td>
																	<td class='text-right'>".$this->bpas->formatMoney($salary_item->subtotal - $salary_item->paid)."</td>
																	<td class='text-center'><input type='text' value='".($salary_item->subtotal - $salary_item->paid)."' class='form-control text-right' name='pay[]'/></td>
																	<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
																<tr>";	
													}
												}
												echo $dataEmp;
											} ?>
										
										</tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "note"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_payment', $this->lang->line("submit"), 'id="add_payment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		<?php if ($Owner || $Admin || $GP['payrolls-payments_date']) { ?>
			$("#date").datetimepicker({
				//format: site.dateFormats.js_sdate, minView: 2' : '
				format: site.dateFormats.js_ldate,
				fontAwesome: true,
				language: 'bms',
				weekStart: 1,
				todayBtn: 1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0
			}).datetimepicker('update', new Date());
		<?php } ?>
	});
</script>