<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 40;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table>
				<thead>
					<tr>
						<th>
							<table>
								<tr>
									<td class="text_center" style="width:20%">
										<?= '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">' ?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bpas->qrcode('link', urlencode(admin_url('payrolls/modal_view_benefit/' . $benefit->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:65%"><hr class="hr_title"></td>
									<td class="text_center" style="width:15%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('benefit') ?></i></b></span></td>
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:60%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td style="width:15%"><?= lang('date') ?></td>
													<td> : <strong><?= $this->bpas->hrld($benefit->date) ?></strong></td>
												</tr>
												<tr>
													<td style="width:15%"><?= lang('month') ?></td>
													<td> : <strong><?= $benefit->month.'/'.$benefit->year ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('created_by') ?></td>
													<td> : <?= $created_by->last_name.' '.$created_by->first_name ?></td>
												</tr>
												<?php 
													if($benefit->position_id > 0){ 
														$position = $this->payrolls_model->getPositionByID($benefit->position_id);
														echo "<tr><td>".lang("position")."</td><td> : ".$position->name."</td></tr>";
													} 
													if($benefit->department_id > 0){ 
														$department = $this->payrolls_model->getDepartmentByID($benefit->department_id);
														echo "<tr><td>".lang("department")."</td><td> : ".$department->name."</td></tr>";
													} 
													if($benefit->group_id > 0){ 
														$group = $this->payrolls_model->getGroupByID($benefit->group_id);
														echo "<tr><td>".lang("group")."</td><td> : ".$group->name."</td></tr>";
													} 
												?>
											</table>
										</fieldset>
									</td>
								</tr>
							</table>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$additions = json_decode($benefit_items[0]->additions);
						$deductions = json_decode($benefit_items[0]->deductions);
						$th_additions = "";
						$th_deductions = "";
						if($additions){
							foreach($additions as $addition){
								$th_additions .= "<th style='border: 2px solid #357EBD; color: white; background-color:#428bca; text-align:center'>".$addition->name."</th>";
							}
						}
						if($deductions){
							foreach($deductions as $deduction){
								$th_deductions .= "<th style='border: 2px solid #357EBD; color: white; background-color:#428bca; text-align:center'>".$deduction->name."</th>";
							}
						}
					
						$tbody = '';
						$i=1;
						$grand_total = 0;
						foreach ($benefit_items as $benefit_item){
							$subtotal = 0;
							$td_addition = "";
							if($additions){
								$emp_additions = false;
								if(json_decode($benefit_item->additions)){
									foreach(json_decode($benefit_item->additions) as $value){
										$emp_additions[$value->id] = $value->value;
									}
								}
								foreach($additions as $addition){
									$amount = 0;
									if(isset($emp_additions[$addition->id]) && $emp_additions[$addition->id]){
										$amount = $emp_additions[$addition->id];
									}
									$td_addition .='<td class="text_right">'.$this->bpas->formatMoney($amount).'</td>';
									$subtotal += $amount; 
								}
							}
							$td_deduction = "";
							if($deductions){
								$emp_deductions = false;
								if(json_decode($benefit_item->deductions)){
									foreach(json_decode($benefit_item->deductions) as $value){
										$emp_deductions[$value->id] = $value->value;
									}
								}
								foreach($deductions as $deduction){
									$amount = 0;
									if(isset($emp_deductions[$deduction->id]) && $emp_deductions[$deduction->id]){
										$amount = $emp_deductions[$deduction->id];
									}
									$td_deduction .='<td class="text_right">'.$this->bpas->formatMoney($amount).'</td>';
									$subtotal -= $amount; 
								}
							}
							if($benefit_item->cash_advanced > 0){
								$subtotal -= $benefit_item->cash_advanced; 
							}
							
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_center">'.$benefit_item->empcode.'</td>
											<td class="text_left">'.$benefit_item->lastname.' '.$benefit_item->firstname.'</td>
											<td class="text_right">'.$this->bpas->formatMoney($benefit_item->cash_advanced).'</td>
											'.$td_addition.'
											'.$td_deduction.'
											<td class="text_right">'.$this->bpas->formatMoney($subtotal).'</td>
										</tr>';		
							$i++;
							$grand_total += $subtotal;
						}
						$footer_colspan = 3;
						if($additions){
							$footer_colspan += count($additions);
						}
						if($deductions){
							$footer_colspan += count($deductions);
						}
						$footer_note = '<td class="footer_des" rowspan="1" colspan="'.$footer_colspan.'">'.$this->bpas->decode_html($benefit->note).'</td>';
						$tfooter = '<tr>
										'.$footer_note.'
										<td class="text_right"><b>'.lang("grand_total").'</b></td>
										<td class="text_right"><b>'.$this->bpas->formatMoney($grand_total).'</b></td>
									</tr>';
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th rowspan="2"><?= lang("#"); ?></th>
										<th rowspan="2"><?= lang("code"); ?></th>
										<th rowspan="2"><?= lang("name"); ?></th>
										<th rowspan="2"><?= lang("cash_advanced"); ?></th>
										<th colspan="<?= ($additions ? count($additions) : 1)  ?>"><?= lang("addition") ?></th>
										<th colspan="<?= ($deductions ? count($deductions) : 1) ?>"><?= lang("deduction") ?></th>
										<th rowspan="2"><?= lang("subtotal"); ?></th>
									</tr>
									<tr>
										<?= $th_additions ?>
										<?= $th_deductions ?>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tbody id="tfooter">
									<?= $tfooter ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("approver") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("preparer").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
	
			<div id="buttons" style="padding-top:10px;" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
							<i class="fa fa-close"></i>
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<i class="fa fa-print"></i>
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<?php if ($benefit->attachment) { ?>
						<div class="btn-group">
							<a href="<?= admin_url('assets/uploads/' . $benefit->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
								<i class="fa fa-download"></i>
								<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	@media print{
		.no-print{
			display:none !important;
		}
		.tr_print{
			display:table-row !important;
		}
		.modal-dialog{
			<?= $hide_print ?>
		}
		.bg-text{
			display:block !important;
		}
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
	}
	.tr_print{
		display:none;
	}
	#tbody .td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
		border-bottom:1px solid black !important;
	}
	.hr_title{
		border:3px double #428BCD !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_item td{
		border:1px solid black;
		line-height:<?=$td_line_height?>px !important;
	}
	.footer_des[rowspan] {
	  vertical-align: top !important;
	  text-align: left !important;
	  border:0px !important;
	}
	
	.text_center{
		text-align:center !important;
	}
	.text_left{
		text-align:left !important;
		padding-left:3px !important;
	}
	.text_right{
		text-align:right !important;
		padding-right:3px !important;
	}
	
	fieldset{
		-moz-border-radius: 9px !important;
		-webkit-border-radius: 15px !important;
		border-radius:9px !important;
		border:2px solid #428BCD !important;
		min-height:<?= $min_height ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}

	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	
	.modal table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
	}
</style>
<script type="text/javascript">
    $(document).ready( function() {
		window.addEventListener("beforeprint", function(event) { addTr();});
		function addTr(){
			$('.blank_tr').remove();
			var page_height = <?= $max_row_limit ?>;
			var form_height = $('.table_item').height()-0;
			if(form_height > page_height && (form_height - page_height) > 15){
				var pages = Math.ceil(form_height / page_height);
				page_height = (page_height - (15 * (pages + 1))) * pages;
			}
			var blank_height = page_height - form_height;
			if(blank_height > 0){
				var td_html = '<tr class="tr_print blank_tr">';
					td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					<?php if($additions){ foreach($additions as $addition){ ?> td_html +='<td class="td_print">&nbsp;</td>'; <?php } }else{ ?> td_html +='<td class="td_print">&nbsp;</td>'; <?php } ?>
					<?php if($deductions){ foreach($deductions as $deduction){ ?> td_html +='<td class="td_print">&nbsp;</td>'; <?php } }else{ ?> td_html +='<td class="td_print">&nbsp;</td>'; <?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
</script>
