<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
	#expTable th,#expTable td{
		text-align: center !important;
	}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_salary'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/edit_salary/".$salary->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['payrolls-salaries_date']) { ?>
									<div class="col-md-3">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($salary->date)), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								 <?php } ?>
								<div class="col-md-3">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : $salary->month."/".$salary->year), 'class="form-control month" required="required" id="month"'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("biller", "biller"); ?>
									<div class="form-group">
										<?php
										$bl[""] = "";
										if($billers){
											foreach ($billers as $biller) {
												$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
											}
										}
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $salary->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("position", "position"); ?>
									<div class="position_box form-group">
										<?php
											$ps[""] = lang("select")." ".lang("position");
											if($positions){
												foreach ($positions as $position) {
													$ps[$position->id] = $position->name;
												}
											}
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : $salary->position_id), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											if($departments){
												foreach ($departments as $department) {
													$dp[$department->id] = $department->name;
												}
											}
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : $salary->department_id), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("group", "group"); ?>
									<div class="group_box form-group">
										<?php
											$gp[""] = lang("select")." ".lang("group");
											if(isset($groups) && $groups){
												foreach ($groups as $group) {
													$gp[$group->id] = $group->name;
												}
											}
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $salary->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang("kh_rate", "kh_rate"); ?>
                                        <div class="input-group">
                                            <?php
                                            $data = $this->site->getCurrencyByCode('KHR');
                                            $kr_rate = $data->rate;
                                            echo form_input('kh_rate',($salary->kh_rate? $salary->kh_rate:$kr_rate), 'id="kh_rate" required="required" class="form-control" readonly');
                                            ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                            	<a href="<?= admin_url('system_settings/edit_currency/'.$data->id); ?>" id="add-customer"
                                                    class="external" data-toggle="modal" data-target="#myModal"><i
                                                        class="fa fa-pencil" id="addIcon"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang("nssf_rate", "nssf_rate"); ?>
                                        <div class="input-group">
                                            <?php
                                            $data = $this->site->getcustomfieldByName('nssf_rate');
                                            $rate = $data->description;
                                            echo form_input('nssf_rate',($salary->nssf_rate?$salary->nssf_rate:$rate), 'id="nssf_rate" required="required" class="form-control" readonly');
                                            ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                            	<a href="<?= admin_url('system_settings/edit_custom_field/'.$data->id); ?>" id="add-customer"
                                                    class="external" data-toggle="modal" data-target="#myModal"><i
                                                        class="fa fa-pencil" id="addIcon"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
							</div>
						</div>
                    </div>
                    <?php
						$th_addition = "";
						$th_addition_sub = "";
						if($additions){
							$colspan = 0;
							foreach($additions as $addition){
								$th_addition_sub .= '<th>'.$addition->name.'</th>';
								$colspan++;
							}
							$th_addition =  '<th colspan="'.$colspan.'">'.lang("ប្រាក់បន្ថែម").'</th>';
						}
						
						$th_deduction = "";
						$th_deduction_sub = "";
						if($deductions){
							$colspan = 0;
							foreach($deductions as $deduction){
								$th_deduction_sub .= '<th>'.$deduction->name.'</th>';
								$colspan++;
							}
							$th_deduction =  '<th colspan="'.$colspan.'">'.lang("ប្រាក់កាត់").'</th>';
						}
						
					?>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="expTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
											<tr>
												<th rowspan="2"><?= lang("code") ?></th>
												<th rowspan="2"><?= lang("name") ?></th>
												<th colspan="7"><?= lang("attendance") ?></th>
												<th rowspan="2"><?= lang("basic_salary") ?></th>
												<th rowspan="2"><?= lang("absent") ?></th>
												<th rowspan="2"><?= lang("permission") ?></th>
												<th rowspan="2" style="min-width: 80px;"><?= lang("late") ?> / <?= lang("early") ?></th>
												<th rowspan="2"><?= lang("overtime") ?></th>
												<?= $th_addition ?>
												<?= $th_deduction ?>
												<th rowspan="2"><?= lang("seniority") ?></th>
												<th colspan="2" class="text-center"><?= lang("salary") ?></th>
												<th colspan="5" class="text-center"><?= lang("NSSF") ?></th>
												<th colspan="2"><?= lang("gross_salary") ?></th>

												<th rowspan="2"><?= lang("spouse") ?></th>
												<th rowspan="2"><?= lang("children") ?></th>
												<th rowspan="2"><?= lang("amount_reduction") ?></th>
												<th rowspan="2"><?= lang("tax_base_for_salary") ?></th>

												<th rowspan="2"><?= lang("tax_payment") ?></th>
												<th rowspan="2"><?= lang("self_tax") ?></th>
												<th rowspan="2"><?= lang("net_salary") ?></th>
												<th rowspan="2"><?= lang("first_payment") ?></th>
												<th rowspan="2"><?= lang("cash_advanced") ?></th>
												<th rowspan="2"><?= lang("net_pay") ?></th>
												<th rowspan="2" style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
											<tr style="background:#428BCA; color:#FFFFFF; text-align:center; font-weight:bold;">
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("working_day") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("absent") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("permission") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;min-width: 80px;"><?= lang("late") ?> / <?= lang("early") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("normal_ot") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("weekend_ot") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("holiday_ot") ?></td>
												<?= $th_addition_sub ?>
												<?= $th_deduction_sub ?>
												<td>USD</td>
												<td>Riels</td>
												<td>Contributory NSSF</td>
												<td><?= lang("pension") ?> 2% by staff</td>
												<td><?= lang("pension") ?> 2% by Company</td>
												<td><?= lang("Health_NSSF") ?> 2.6%</td>
												<td><?= lang("Accident_NSSF") ?> 0.8%</td>
												<td>USD</td>
												<td>Riels</td>
											</tr>
                                        </thead>
                                        <tbody id="dataEmp">
											<?php
												$dataEmp = "";
												if($salary_items){
													foreach($salary_items as $salary_item){
														$self_tax = ($salary_item->self_tax == 1) ? lang('yes') : lang('no');

														$td_addition = "<input name='addition_amount[]' value='".$salary_item->addition_amount."' type='hidden'/>";
														if($additions){
															$emp_additions = false;
															if(json_decode($salary_item->additions)){
																foreach(json_decode($salary_item->additions) as $value){
																	$emp_additions[$value->id] = $value->value;
																}
															}
															foreach($additions as $addition){
																$amount = 0;
																if(isset($emp_additions[$addition->id]) && $emp_additions[$addition->id]){
																	$amount = $emp_additions[$addition->id];
																}
																$td_addition .="<td class='text-right'>
																	<input name='addition[".$salary_item->employee_id."][".$addition->id."]' value='".$amount."' type='hidden'/>
																	<input name='addition_name[".$salary_item->employee_id."][".$addition->id."]' value='".$addition->name."' type='hidden'/>
																	".$this->bpas->formatMoney($amount)."
																</td>";
															}
														}
														$td_deduction = "<input name='deduction_amount[]' value='".$salary_item->deduction_amount."' type='hidden'/>";
														if($deductions){
															$emp_duductions = false;
															if(json_decode($salary_item->deductions)){
																foreach(json_decode($salary_item->deductions) as $value){
																	$emp_duductions[$value->id] = $value->value;
																}
															}
															foreach($deductions as $deduction){
																$amount = 0;
																if(isset($emp_duductions[$deduction->id]) && $emp_duductions[$deduction->id]){
																	$amount = $emp_duductions[$deduction->id];
																}
																$td_deduction .="<td class='text-right'>
																	<input name='deduction[".$salary_item->employee_id."][".$deduction->id."]' value='".$amount."' type='hidden'/>
																	<input name='deduction_name[".$salary_item->employee_id."][".$deduction->id."]' value='".$deduction->name."' type='hidden'/>
																	".$this->bpas->formatMoney($amount)."
																</td>";

				
															}
														}
														
														$dataEmp .= "<tr>
															<td><input name='employee_id[]' value='".$salary_item->employee_id."' type='hidden'/>".$salary_item->empcode."</td>
															<td>".$salary_item->lastname." ".$salary_item->firstname."</td>
															<td class='text-center'><input name='working_day[]' value='".$salary_item->working_day."' type='hidden'/>".$salary_item->working_day."</td>
															<td class='text-center'><input name='absent[]' value='".$salary_item->absent."' type='hidden'/>".$salary_item->absent."</td>
															<td class='text-center'><input name='permission[]' value='".$salary_item->permission."' type='hidden'/>".$salary_item->permission."</td>
															<td class='text-center'><input name='late[]' value='".$salary_item->late."' type='hidden'/>".$salary_item->late."</td>
															<td class='text-center'><input name='normal_ot[]' value='".$salary_item->normal_ot."' type='hidden'/>".$salary_item->normal_ot."</td>
															<td class='text-center'><input name='weekend_ot[]' value='".$salary_item->weekend_ot."' type='hidden'/>".$salary_item->weekend_ot."</td>
															<td class='text-center'><input name='holiday_ot[]' value='".$salary_item->holiday_ot."' type='hidden'/>".$salary_item->holiday_ot."</td>
															<td class='text-right'><input name='basic_salary[]' value='".$salary_item->basic_salary."' type='hidden'/>".$this->bpas->formatMoney($salary_item->basic_salary)."</td>
															<td class='text-right'><input name='absent_amount[]' value='".$salary_item->absent_amount."' type='hidden'/>".$this->bpas->formatMoney($salary_item->absent_amount)."</td>
															<td class='text-right'><input name='permission_amount[]' value='".$salary_item->permission_amount."' type='hidden'/>".$this->bpas->formatMoney($salary_item->permission_amount)."</td>
															<td class='text-right'><input name='late_amount[]' value='".$salary_item->late_amount."' type='hidden'/>".$this->bpas->formatMoney($salary_item->late_amount)."</td>
															<td class='text-right'><input name='overtime[]' value='".$salary_item->overtime."' type='hidden'/>".$this->bpas->formatMoney($salary_item->overtime)."</td>
														";
														$dataEmp .= $td_addition;
														$dataEmp .= $td_deduction;
														$dataEmp .= "
															<td class='text-right'><input name='seniority[]' value='".$salary_item->seniority."' type='hidden'/>".$this->bpas->formatMoney($salary_item->seniority)."</td>

															<td class='text-right'><input name='nssf_salary_usd[]' value='".$salary_item->nssf_salary_usd."' type='hidden'/>".$this->bpas->formatMoney($salary_item->nssf_salary_usd)."</td>

															<td class='text-right'><input name='nssf_salary_riel[]' value='".$salary_item->nssf_salary_riel."' type='hidden'/>".$this->bpas->formatMoney($salary_item->nssf_salary_riel)."</td>


															<td class='text-right'><input name='contributory_nssf[]' value='".$salary_item->contributory_nssf."' type='hidden'/>".$this->bpas->formatMoney($salary_item->contributory_nssf)."</td>

															<td class='text-right'><input name='pension_by_staff[]' value='".$salary_item->pension_by_staff."' type='hidden'/>(".$this->bpas->formatMoney($salary_item->pension_by_staff).")</td>

															<td class='text-right'><input name='pension_by_company[]' value='".$salary_item->pension_by_company."' type='hidden'/>".$this->bpas->formatMoney($salary_item->pension_by_company)."</td>

															<td class='text-right'><input name='health_nssf[]' value='0' type='hidden'/>".$this->bpas->formatMoney($salary_item->health_nssf)."</td>
															<td class='text-right'><input name='accident_nssf[]' value='0' type='hidden'/>".$this->bpas->formatMoney($salary_item->accident_nssf)."</td>";

														$dataEmp .= "<td class='text-right'><input name='gross_salary[]' value='".$salary_item->gross_salary."' type='hidden'/>".$this->bpas->formatMoney($salary_item->gross_salary)."</td>

															<td class='text-right'><input name='gross_salary_riel[]' value='".$salary_item->gross_salary_riel."' type='hidden'/>".$this->bpas->formatMoney($salary_item->gross_salary_riel)."</td>

															<td class='text-right'><input name='spouse[]' value='".$salary_item->spouse."' type='hidden'/>".$this->bpas->formatQuantity($salary_item->spouse)."</td>
															<td class='text-right'><input name='children[]' value='".$salary_item->children."' type='hidden'/>".$this->bpas->formatQuantity($salary_item->children)."</td>
															<td class='text-right'><input name='spouse_children_reduction[]' value='".$salary_item->spouse_children_reduction."' type='hidden'/>".$this->bpas->formatMoney($salary_item->spouse_children_reduction)."</td>
															<td class='text-right'><input name='Taxbasesalary[]' value='".$salary_item->Taxbasesalary."' type='hidden'/>".$this->bpas->formatMoney($salary_item->Taxbasesalary)."</td>

															<td class='text-right'><input name='tax_payment[]' value='".$salary_item->tax_payment."' type='hidden'/>".$this->bpas->formatMoney($salary_item->tax_payment)."</td>

															<td class='text-right'><input name='self_tax[]' value='".$self_tax."' type='hidden'/>".$self_tax."</td>

															<td class='text-right'><input name='net_salary[]' value='".$salary_item->net_salary."' type='hidden'/>".$this->bpas->formatMoney($salary_item->net_salary)."</td>
															
															<td class='text-right'><input name='pre_salary[]' value='".$salary_item->pre_salary."' type='hidden'/>".$this->bpas->formatMoney($salary_item->pre_salary)."</td>

															<td class='text-right'><input name='cash_advanced[]' value='".$salary_item->cash_advanced."' type='hidden'/>".$this->bpas->formatMoney($salary_item->cash_advanced)."</td>

															<td class='text-right'><input name='net_pay[]' value='".$salary_item->net_pay."' type='hidden'/>".$this->bpas->formatMoney($salary_item->net_pay)."</td>
															<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
														<tr>";	
														
													}
												}
												echo $dataEmp;
											?>
										
										</tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "note"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $salary->note), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_salary', $this->lang->line("submit"), 'id="edit_salary" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		function getSalaryEmployee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var month = $("#month").val();
			var kh_rate = $("#kh_rate").val();
			var nssf_rate = $("#nssf_rate").val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_salary_employees/",
				data : { 
						biller_id : biller_id,
						position_id : position_id,
						department_id : department_id,
						group_id : group_id,
						month : month,
						nssf_rate:nssf_rate,
						kh_rate:kh_rate,
						edit_id : "<?= $salary->id ?>"
				},
				dataType: "json",
				success: function (data) {
					var dataEmp = "";
					if (data != false) {
						$.each(data, function () {
							var employee_id = this.employee_id;
							dataEmp += "<tr>";
								dataEmp += "<td><input name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
								dataEmp += "<td>"+this.lastname+" "+this.firstname+"</td>";
								dataEmp += "<td class='text-center'><input name='working_day[]' value='"+this.working_day+"' type='hidden'/>"+this.working_day+"</td>";
								dataEmp += "<td class='text-center'><input name='absent[]' value='"+this.absent+"' type='hidden'/>"+this.absent+"</td>";
								dataEmp += "<td class='text-center'><input name='permission[]' value='"+this.permission+"' type='hidden'/>"+this.permission+"</td>";
								dataEmp += "<td class='text-center'><input name='late[]' value='"+this.late+"' type='hidden'/>"+this.late+"</td>";
								dataEmp += "<td class='text-center'><input name='normal_ot[]' value='"+this.normal_ot+"' type='hidden'/>"+this.normal_ot+"</td>";
								dataEmp += "<td class='text-center'><input name='weekend_ot[]' value='"+this.weekend_ot+"' type='hidden'/>"+this.weekend_ot+"</td>";
								dataEmp += "<td class='text-center'><input name='holiday_ot[]' value='"+this.holiday_ot+"' type='hidden'/>"+this.holiday_ot+"</td>";
								dataEmp += "<td class='text-right'><input name='basic_salary[]' value='"+this.basic_salary+"' type='hidden'/>"+formatMoney(this.basic_salary)+"</td>";
								dataEmp += "<td class='text-right'><input name='absent_amount[]' value='"+this.absent_amount+"' type='hidden'/>"+formatMoney(this.absent_amount)+"</td>";
								dataEmp += "<td class='text-right'><input name='permission_amount[]' value='"+this.permission_amount+"' type='hidden'/>"+formatMoney(this.permission_amount)+"</td>";
								dataEmp += "<td class='text-right'><input name='late_amount[]' value='"+this.late_amount+"' type='hidden'/>"+formatMoney(this.late_amount)+"</td>";
								dataEmp += "<td class='text-right'><input name='overtime[]' value='"+this.overtime+"' type='hidden'/>"+formatMoney(this.overtime)+"</td>";
								if(this.approve_additions){
									$.each(this.approve_additions, function () {
										dataEmp += "<td class='text-right'><input name='addition["+employee_id+"]["+this.id+"]' value='"+this.value+"' type='hidden'/><input name='addition_name["+employee_id+"]["+this.id+"]' value='"+this.name+"' type='hidden'/>"+(this.value > 0 ? formatMoney(this.value) : "-")+"</td>";
									});
									dataEmp += "<input name='addition_amount[]' value='"+this.addition_amount+"' type='hidden'/>";
								}
								if(this.approve_deductions){
									$.each(this.approve_deductions, function () {
										dataEmp += "<td class='text-right'><input name='deduction["+employee_id+"]["+this.id+"]' value='"+this.value+"' type='hidden'/><input name='deduction_name["+employee_id+"]["+this.id+"]' value='"+this.name+"' type='hidden'/>"+(this.value > 0 ? formatMoney(this.value) : "-")+"</td>";
									});
									dataEmp += "<input name='deduction_amount[]' value='"+this.deduction_amount+"' type='hidden'/>";
								}
								dataEmp += "<td class='text-right'><input name='seniority[]' value='"+this.seniority+"' type='hidden'/>"+(this.seniority > 0 ? formatMoney(this.seniority) : "-")+"</td>";

								dataEmp += "<td class='text-right'><input name='nssf_salary_usd[]' value='"+this.nssf_salary_usd+"' type='hidden'/>"+(this.nssf_salary_usd > 0 ? formatMoney(this.nssf_salary_usd) : "-")+"</td>";
								dataEmp += "<td class='text-right'><input name='nssf_salary_riel[]' value='"+this.nssf_salary_riel+"' type='hidden'/>"+(this.nssf_salary_riel > 0 ? formatMoney(this.nssf_salary_riel) : "-")+"</td>";

								dataEmp += "<td class='text-right'><input name='contributory_nssf[]' value='"+this.contributory_nssf+"' type='hidden'/>"+(this.contributory_nssf > 0 ? formatMoney(this.contributory_nssf) : 0)+"</td>";
								dataEmp += "<td class='text-right'><input name='pension_by_staff[]' value='"+this.pension_by_staff+"' type='hidden'/>("+(this.pension_by_staff > 0 ? formatMoney(this.pension_by_staff) : 0)+")</td>";
								dataEmp += "<td class='text-right'><input name='pension_by_company[]' value='"+this.pension_by_staff+"' type='hidden'/>"+(this.pension_by_staff > 0 ? formatMoney(this.pension_by_staff) : 0)+"</td>";

								dataEmp += "<td class='text-right'><input name='health_nssf[]' value='"+this.health_nssf+"' type='hidden'/>"+(this.health_nssf > 0 ? formatMoney(this.health_nssf) : 0)+"</td>";
								dataEmp += "<td class='text-right'><input name='accident_nssf[]' value='"+this.accident_nssf+"' type='hidden'/>"+(this.accident_nssf > 0 ? formatMoney(this.accident_nssf) : 0)+"</td>";

								dataEmp += "<td class='text-right'><input name='gross_salary[]' value='"+this.gross_salary+"' type='hidden'/>"+formatMoney(this.gross_salary)+"</td>";
								dataEmp += "<td class='text-right'><input name='gross_salary_riel[]' value='"+this.gross_salary_riel+"' type='hidden'/>"+formatMoney(this.gross_salary_riel)+"</td>";

								dataEmp += "<td class='text-right'><input name='spouse[]' value='"+this.spouse+"' type='hidden'/>"+formatMoney(this.spouse)+"</td>";
								dataEmp += "<td class='text-right'><input name='children[]' value='"+this.children+"' type='hidden'/>"+formatMoney(this.children)+"</td>";
								dataEmp += "<td class='text-right'><input name='spouse_children_reduction[]' value='"+this.spouse_children_reduction+"' type='hidden'/>"+formatMoney(this.spouse_children_reduction)+"</td>";
								dataEmp += "<td class='text-right'><input name='Taxbasesalary[]' value='"+this.Taxbasesalary+"' type='hidden'/>"+formatMoney(this.Taxbasesalary)+"</td>";


								dataEmp += "<td class='text-right'><input name='tax_payment[]' value='"+this.tax_payment+"' type='hidden'/>"+formatMoney(this.tax_payment)+"</td>";
								dataEmp += "<td class='text-right'><input name='self_tax[]' value='"+this.self_tax+"' type='hidden'/>"+this.self_tax+"</td>";
								dataEmp += "<td class='text-right'><input name='net_salary[]' value='"+this.net_salary+"' type='hidden'/>"+formatMoney(this.net_salary)+"</td>";
								dataEmp += "<td class='text-right'><input name='pre_salary[]' value='"+this.pre_salary+"' type='hidden'/>"+formatMoney(this.pre_salary)+"</td>";
								dataEmp += "<td class='text-right'><input name='cash_advanced[]' value='"+this.cash_advanced+"' type='hidden'/>"+formatMoney(this.cash_advanced)+"</td>";
								dataEmp += "<td class='text-right'><input name='net_pay[]' value='"+this.net_pay+"' type='hidden'/>"+formatMoney(this.net_pay)+"</td>";
								dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
							dataEmp += "</tr>";
						});
					}
					$("#dataEmp").html(dataEmp);
				}
			});
		}
		
		
		$(document).on("change", "#biller", function () {	
			var biller_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_departments/",
				data : { biller_id : biller_id },
				dataType: "json",
				success: function (data) {
					var department_sel = "<select class='form-control' id='department' name='department'><option value=''><?= lang('select').' '.lang('department') ?></option>";
					if (data != false) {
						$.each(data, function () {
							department_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					department_sel += "</select>"
					$(".department_box").html(department_sel);
					$('select').select2();
				}
			});
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_positions/",
				data : { biller_id : biller_id },
				dataType: "json",
				success: function (data) {
					var postion_sel = "<select class='form-control' id='position' name='position'><option value=''><?= lang('select').' '.lang('position') ?></option>";
					if (data != false) {
						$.each(data, function () {
							postion_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					postion_sel += "</select>"
					$(".position_box").html(postion_sel);
					$('select').select2();
				}
			});
			getSalaryEmployee();
		});
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_groups/",
				data : { department_id : department_id },
				dataType: "json",
				success: function (data) {
					var group_sel = "<select class='form-control' id='group' name='group'><option value=''><?= lang('select').' '.lang('group') ?></option>";
					if (data != false) {
						$.each(data, function () {
							group_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					group_sel += "</select>"
					$(".group_box").html(group_sel);
					$('select').select2();
				}
			});
			getSalaryEmployee();
		});
		$(document).on("change", "#group, #position, #month", function () {	
			getSalaryEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});

	});
</script>