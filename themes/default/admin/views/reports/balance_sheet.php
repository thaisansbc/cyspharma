<script>
	$(document).ready(function () {
        CURI = '<?= admin_url('reports/balance_sheet'); ?>';
	});
</script>
<style>
	@media print {
        .fa {
            color: #EEE;
            display: none;
        }
        .small-box {
            border: 1px solid #CCC;
        }
    }
</style>
<?php
	$start_date=date('Y-m-d H:i:s',strtotime($start));
//	$rep_space_end=str_replace(' ','_',$end);
	$end_date=date('Y-m-d H:i:s',strtotime($end)); //str_replace(':','-',$rep_space_end);
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('balance_sheet'); ?> >> <?= (isset($start)?$start:""); ?> >> <?= (isset($end)?$end:""); ?></h2>
        
        <div class="box-icon hide">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text"
                               value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a id="downloadLink" style="cursor: pointer;" onclick="exportF(this)" class="tip" title="<?= lang('export_excel') ?>">
                	<i class="icon fa fa-file-excel-o"></i></a></li>
				<li class="dropdown hide"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("projects") ?>"></i>
					</a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= admin_url('reports/balance_sheet') ?>"><i class="fa fa-building-o"></i> <?= lang('projects') ?></a></li>
						<li class="divider"></li>
						<?php
						$b_sep = 0;
						foreach ($billers as $biller) {
							
							$biller_sep = explode('-', $this->uri->segment(7));
							if($biller_sep[$b_sep] == $biller->id){
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" checked value="'. $biller->id .'" >&nbsp;&nbsp;' . $biller->company . '</li>';
								echo '<li class="divider"></li>';
								$b_sep++;
							}else{
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" value="'. $biller->id .'" >&nbsp;&nbsp;' . $biller->company . '</li>';
								echo '<li class="divider"></li>';
							}
						}
						?>
						<li class="text-center"><a href="#" id="biller-filter" class="btn btn-primary"><?=lang('submit')?></a></li>
					</ul>
				</li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<p class="introtext"><?= lang('list_results'); ?></p>
				<div id="form">
                    <?php echo admin_form_open("reports/balance_sheet/"); ?>
                    <div class="row center">
                    	<div class="col-sm-3 hide">
                    		<div class="form-group">
                    			<?= lang("biller", "biller"); ?> <br>
		                    	<?php
								$bl[''] = '';
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, '', 'data-placeholder="' . lang('select') . ' ' . lang('biller') . '" class="form-control" style="width:100%;"');
		                    	?>
                    		</div>
                     	</div>
              
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($start_date) ? $start_date : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($end_date) ? $end_date : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
				<?php $num_col=2; ?>
                <div class="table-scroll">
                    <table  cellpadding="0" id="export_contain" cellspacing="0" border="0" class="table table-hover table-striped table-condensed">
                    	
						<thead>
							<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 18px;"><?= $this->Settings->site_name;?></td>
                    		</tr>
							<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 16px;">Balance Sheet Report</td>
                    		</tr>
                    		<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 16px;">Report of <?= ($start ? $this->bpas->hrld($start) : '') . ' To ' . ($end ? $this->bpas->hrld($end) : ''); ?></td>
                    		</tr>
                    		
							<tr>                           
								<th style="width:300px;"><?= lang("account_name"); ?></th>
								<?php
								$new_billers = array();
								foreach ($billers as $b1) {
									if($this->uri->segment(7)){
										$biller_sep = explode('-', $this->uri->segment(7));
										for($i=0; $i < count($biller_sep); $i++){
											if($biller_sep[$i] == $b1->id){
												echo '<th style="width:200px;text-align:right;">' . $b1->company . '</th>';
												$new_billers[] = array('id' => $b1->id);
											}
										}
									}else{
										$new_billers = $billers;
										echo '<th style="width:200px;text-align:right;">' . $b1->company . '</th>';
									}
									$num_col++;
								}
								?>
								<th style="width:200px;text-align:right;"><?= lang("total_amount") ?></th>
							</tr>
							<tr class="primary">                            
								<th style="text-align:left;" colspan="<?=$num_col?>"><?= lang("asset"); ?></th>							
							</tr>
                        </thead> 
				
                        <tbody>
						<?php
							if(isset($this->uri->segments["3"])){
								$from = $start;//explode("%",$this->uri->segments["3"])[0];
							}
							if(isset($this->uri->segments["4"])){
								$to = $end;//explode("%",$this->uri->segments["4"])[0];
							}
							$from_st = !empty($from)? "&start_date=".$this->bpas->hrld($from) : "";
							$to_st = !empty($end_date)? "&end_date=".$this->bpas->hrld($end_date) : "";	

							$total_asset = 0;
							$totalBeforeAyear_asset = 0;
							$colbot = 0;
							if($this->uri->segment(7)){
								$col1 = 3;
								$colbot = 3;
							}else{
								$colcount = count($new_billers);
								$col1 = $colcount+2;
								$colbot = $colcount + 2;
							}
							$total_asset_arr = array();
							$total_lib_arr = array();
							$total_eq_arr = array();
							$sum_asset_arr = array();
							$sum_lib_arr = array();
							$sum_eq_arr = array();
							///

							$total_eq_arr_current = array();
							foreach($dataAsset->result() as $row){
								$index = 0;
								$total_per_asset = 0;
								echo '<tr>';
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index]['id'];
									}else{
										$bill_id = $new_billers[$index]->id;
									}
									
									$query = $this->db->query("SELECT
									SUM(CASE WHEN bpas_gl_trans.amount < 0 THEN bpas_gl_trans.amount ELSE 0 END) as NegativeTotal,
									SUM(CASE WHEN bpas_gl_trans.amount >= 0 THEN bpas_gl_trans.amount ELSE 0 END) as PostiveTotal,
									SUM(
										COALESCE (bpas_gl_trans.amount, 0)
									) AS amount
									FROM
										bpas_gl_trans
									WHERE
										biller_id = '$bill_id' AND account_code = '" . $row->account_code . "'
										
										AND date(bpas_gl_trans.tran_date) <= '$to_date' ;");
									
									// AND date(bpas_gl_trans.tran_date) >= '$from_date'

									$totalBeforeAyearRows = $query->row();
									$totalBeforeAyear_asset += ($totalBeforeAyearRows->NegativeTotal + $totalBeforeAyearRows->PostiveTotal);
									
									$amount_asset = '';
									$amount_asset = $totalBeforeAyearRows->amount;
									
									if($totalBeforeAyearRows->amount<0){
										$amount_asset = '( '.number_format(abs($totalBeforeAyearRows->amount),2).' )';
									}else{
										$amount_asset = number_format(abs($totalBeforeAyearRows->amount),2);
									}
									
									if(($index+1)==1){
										$total_asset_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);
										
										//$parent =($row->parent_acc <> 0)?"&nbsp;&nbsp;&nbsp;&nbsp;":"";<?= $parent 
								?>
										<td>
											<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&account='.$row->account_code) ?>">
												<?php echo $row->account_code;?> - <?php echo $row->accountname;?>
											</a>
										</td>
										
										<td class="right">
											<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&biller='.$bill_id.'&account='.$row->account_code) ?>">
												<?php echo $amount_asset;?>
											</a>
										</td>
								<?php
									}else{
										$total_asset_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);								
										?>
											<td class="right">
												<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&biller='.$bill_id.'&account='.$row->account_code) ?>">
													<?php echo $amount_asset;?>
												</a>
											</td>
										<?php 
									}
									$total_per_asset += $totalBeforeAyearRows->amount;
									$total_asset += $totalBeforeAyearRows->amount;
									$index++;
								}
								
								if($total_per_asset<0){
									$total_per_asset = '( '.number_format(abs($total_per_asset),2).' )';
								}else{
									$total_per_asset = number_format(abs($total_per_asset),2);
								}
								
								echo '<td class="right">'.$total_per_asset.'</td>';
								echo '</tr>';
							}

						?>
							<tr>
								<td><b><?= lang("total_asset"); ?></b></td>
								<?php
								for($c= 0; $c < count($new_billers); $c++){
									$in_bill_id1 = 0;
									if($this->uri->segment(7)){
										$in_bill_id1 = $new_billers[$c]['id'];
									}else{
										$in_bill_id1 = $new_billers[$c]->id;
									}
									$total_asset_amt = 0;
									foreach($total_asset_arr as $new_arr){
										if($new_arr['biller_id'] == $in_bill_id1){
											$total_asset_amt += $new_arr['amount'];
										}
									}
									$sum_asset_arr[] = array(
										'id' => $in_bill_id1,
										'amount' => $total_asset_amt
									);
									if($total_asset_amt<0){
										$total_asset_amt = '( '.number_format(abs($total_asset_amt),2).' )';
									}else{
										$total_asset_amt = number_format(abs($total_asset_amt),2);
									}
									echo '<td class="right"><b>'. $total_asset_amt .'</b></td>';
								}
								$total_asset_display = '';
								if($total_asset<0){
									$total_asset_display = '( '.number_format(abs($total_asset),2).' )';
								}else{
									$total_asset_display = number_format(abs($total_asset),2);
								}
								?>
								<td class="right"><b><?php echo $total_asset_display;?></b></td>
							</tr>
						    <tr class="primary">
								<th style="text-align:left;" colspan="<?=$num_col?>"><?= lang("liabilities"); ?></th>
							</tr>  
							<?php
							$total_liability = 0;
							$totalBeforeAyear_liability = 0;
							foreach($dataLiability->result() as $rowlia){
								
								$index = 0;
								$total_per_lib = 0;
								echo '<tr>';
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index]['id'];
									}else{
										$bill_id = $new_billers[$index]->id;
									}
								
									$query = $this->db->query("SELECT
									SUM(CASE WHEN bpas_gl_trans.amount < 0 THEN bpas_gl_trans.amount ELSE 0 END) as NegativeTotal,
									SUM(CASE WHEN bpas_gl_trans.amount >= 0 THEN bpas_gl_trans.amount ELSE 0 END) as PostiveTotal,
									SUM(
										COALESCE (bpas_gl_trans.amount, 0)
									) AS amount
									FROM
										bpas_gl_trans
									WHERE
										biller_id = '$bill_id' AND account_code = '" . $rowlia->account_code . "'
										AND date(bpas_gl_trans.tran_date) < '$to_date' ;");
										// AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date' AND '$to_date' ;");

									$totalBeforeAyearRows = $query->row();
									$totalBeforeAyear_liability += ($totalBeforeAyearRows->NegativeTotal + $totalBeforeAyearRows->PostiveTotal);
									$amount_lib = '';
									
									if($totalBeforeAyearRows->amount<0){
										$amount_lib = number_format(abs($totalBeforeAyearRows->amount),2);
									}else{
										$amount_lib = '( '.number_format(abs($totalBeforeAyearRows->amount),2).' )';
									}
									
									if(($index+1)==1){
										$total_lib_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);
									?>
										<td>
											<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&account='.$rowlia->account_code) ?>">
												<?php echo $rowlia->account_code;?> - <?php echo $rowlia->accountname;?>
											</a>
										</td>
										<td class="right">
											<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&biller='.$bill_id.'&account='.$rowlia->account_code) ?>">
												<?php echo $amount_lib;?>
											</a>
										</td>
									<?php
									} else {
										$total_lib_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);?>
											
										<td class="right">
											<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&biller='.$bill_id.'&account='.$rowlia->account_code) ?>">
												<?php echo $amount_lib;?>
											</a>
										</td>	
											
										<?php
										
									}
									$total_per_lib += $totalBeforeAyearRows->amount;
									$total_liability += $totalBeforeAyearRows->amount;
									$index++;
								}
								$total_per_lib_display = $total_per_lib;
								//$total_liability += $total_per_lib;
								
								if($total_per_lib < 0){
									$total_per_lib_display = number_format(abs($total_per_lib),2);
								}else{
									$total_per_lib_display = '( ' . number_format(abs($total_per_lib),2) . ' )';
								}
							
								echo '<td class="right">'. $total_per_lib_display.'</td>';
								echo '</tr>';
							}
						?>
							<tr>
							<td><b><?= lang("total_liabilities"); ?></b></td>
							
							<?php
							for($c= 0; $c < count($new_billers); $c++){
								$in_bill_id1 = 0;
								if($this->uri->segment(7)){
									$in_bill_id1 = $new_billers[$c]['id'];
								}else{
									$in_bill_id1 = $new_billers[$c]->id;
								}
								$total_lib_amt = 0;
								foreach($total_lib_arr as $new_arr){
									if($new_arr['biller_id'] == $in_bill_id1){
										$total_lib_amt += $new_arr['amount'];
									}
								}
								$sum_lib_arr[] = array(
									'biller_id' => $in_bill_id1,
									'amount' => $total_lib_amt
								);
								
								if($total_lib_amt <= 0){
									echo '<td class="right"><b>' . number_format(abs($total_lib_amt),2) .'</b></td>';
								}else{
									echo '<td class="right"><b>( '. number_format($total_lib_amt,2) .' )</b></td>';
								}
								
							}
							$end_total_lib = $total_liability;

					
							?>
								<td class="right">
									<?php
									if($total_liability <= 0){
										echo '<b>' . number_format(abs($total_liability),2) . '</b>';
									}else{
										echo '<b>( ' . number_format(abs($total_liability),2) . ' )</b>';
									}
									?>
								</td>
							</tr>
							<tr class="primary">
								<th colspan="<?=$num_col?>">
									<?= lang("equities"); ?>
								</th>
							</tr>
							<?php
									$total_income = 0;
									$total_expense = 0;
									$total_retained = 0;
									$total_income_beforeAyear = 0;
									$total_expense_beforeAyear = 0;
									$total_retained_beforeAyear = 0;

									$queryIncom = $this->db->query("
										SELECT sum(bpas_gl_trans.amount) AS amount 
										FROM bpas_gl_trans 
										INNER JOIN bpas_gl_charts 
										ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
										WHERE bpas_gl_trans.tran_date < '$from_date' 
										AND	bpas_gl_charts.sectionid IN ('40,70') 
							 
										GROUP BY bpas_gl_trans.account_code");
								
									$queryIncom = $queryIncom->row();
									
									$total_income_beforeAyear = $queryIncom['amount'];

									$queryExpense = $this->db->query("
										SELECT sum(bpas_gl_trans.amount) AS amount 
										FROM bpas_gl_trans
										INNER JOIN bpas_gl_charts 
										ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
										WHERE bpas_gl_trans.tran_date < '$from_date' 
										AND	bpas_gl_charts.sectionid IN ('50,60,80,90') 
										GROUP BY bpas_gl_trans.account_code");

									$queryExpense = $queryExpense->row();
									$total_expense_beforeAyear = $queryExpense['amount'];
									$total_retained_beforeAyear = abs($total_income_beforeAyear)-abs($total_expense_beforeAyear);
									$retained_inc_arr = array();
									$retained_exp_arr = array();

						
									foreach($dataIncome_retain->result() as $rowincome){
										$total_income += (-1) * $rowincome->amount;
									}

									foreach($dataAllIncome_retain->result() as $rowallinc){
										$retained_inc_arr[] = array(
											'biller_id' => $rowallinc->biller_id,
											'amount' => $rowallinc->amount
										);
									}	
									foreach($dataExpense_retain->result() as $rowexpense){
										$total_expense += $rowexpense->amount;
									}
									foreach($dataAllExpense_retain->result() as $rowallexp){
										$retained_exp_arr[] = array(
											'biller_id' => $rowallexp->biller_id,
											'amount' => $rowallexp->amount
										);
									}
						
									$total_retained = $total_income - $total_expense;
								?>
							<tr>
								<td>
									<?php
										$retained = $this->db->get("account_settings")->row();
									?>
									<a href="<?= admin_url('reports/income_statement/') ?>">
									Retained Earnings
									<a/>
								</td>
								
								<?php
								$total_retained_arr = array();

								for($c= 0; $c < count($new_billers); $c++){
									$in_bill_id1 = 0;
									if($this->uri->segment(7)){
										$in_bill_id1 = $new_billers[$c]['id'];
									}else{
										$in_bill_id1 = $new_billers[$c]->id;
									}

									$total_per_retained = 0;
									
									$k = 0;
									$r_inc_per = 0;
									$r_exp_per = 0;
									

									if(count($retained_exp_arr) == 0){
										foreach($retained_inc_arr as $exp_row){
											if($exp_row['biller_id'] == $in_bill_id1){
												$r_exp_per += $exp_row['amount'];
											}
											$k++;
										}
									}
									
									foreach($retained_exp_arr as $exp_row){
										if($exp_row['biller_id'] == $in_bill_id1){
											$r_exp_per += $exp_row['amount'];
										}
									}
									

									foreach($retained_inc_arr as $inc_row) {
										if($in_bill_id1 == $inc_row['biller_id']){
											$r_inc_per += $inc_row['amount'];
										}
									}
									
									$total_per_retained = $r_exp_per + $r_inc_per;
										
										$total_retained_arr[] = array(
											'biller_id' => $in_bill_id1,
											'amount' => $total_per_retained
										);


									if($total_per_retained>0){
										$total_per_retained = '( '.number_format(abs($total_per_retained),2).' )';
									}else{
										$total_per_retained = number_format(abs($total_per_retained),2);
									}
									echo '<td class="right"><a href="'.admin_url('reports/income_statement/').'">'. $total_per_retained .'</a></td>';
								}
								?>
								
								<?php if($total_retained < 0) { ?>						
									<td class="right"><?php echo '( ' . number_format(abs($total_retained),2) . ' )';?></td>
								<?php } else { ?>
									<td class="right"><?php echo number_format(abs($total_retained),2);?></td>
								<?php }	?>							

							</tr>
							<tr>
								<td>
									<?php
										$retained = $this->db->get("account_settings")->row();
									?>
									<a href="<?= admin_url('reports/income_statement/') ?>">
										<?= lang('p_l_current_year')?>
									<a/>
								</td>
								<?php
									$total_income_current = 0;
									$total_expense_current = 0;
									$total_currect_year = 0;
									$total_income_beforeAyear = 0;
									$total_expense_beforeAyear = 0;
									$total_retained_beforeAyear = 0;
									$total_amt_current =0;
									$queryIncom = $this->db->query("
										SELECT sum(bpas_gl_trans.amount) AS amount 
										FROM bpas_gl_trans 
										INNER JOIN bpas_gl_charts 
										ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
										WHERE DATE(tran_date) = '$totalBeforeAyear' 
										AND	bpas_gl_trans.sectionid IN ('40,70') 
										AND date(bpas_gl_trans.tran_date) 
										BETWEEN '$from_date' AND '$to_date' 
										GROUP BY bpas_gl_trans.account_code");
								
									$queryIncom = $queryIncom->row();
									
									$total_income_beforeAyear = $queryIncom['amount'];

									$queryExpense = $this->db->query("
										SELECT sum(bpas_gl_trans.amount) AS amount 
										FROM bpas_gl_trans
										INNER JOIN bpas_gl_charts 
										ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
										WHERE DATE(tran_date) = '$totalBeforeAyear' 
										AND	bpas_gl_trans.sectionid IN ('50,60,80,90') 
										GROUP BY bpas_gl_trans.account_code");

									$queryExpense = $queryExpense->row();
									$total_expense_beforeAyear = $queryExpense['amount'];
									$total_retained_beforeAyear = abs($total_income_beforeAyear)-abs($total_expense_beforeAyear);
									$retained_inc_arr = array();
									$retained_exp_arr = array();

						
									foreach($dataIncome_current->result() as $rowincome){
										$total_income_current += (-1) * $rowincome->amount;
									}

									foreach($dataAllIncome->result() as $rowallinc){
										$retained_inc_arr[] = array(
											'biller_id' => $rowallinc->biller_id,
											'amount' => $rowallinc->amount
										);
									}	
									foreach($dataExpense_current->result() as $rowexpense){
										$total_expense_current += $rowexpense->amount;
									}
									foreach($dataAllExpense->result() as $rowallexp){
										$retained_exp_arr[] = array(
											'biller_id' => $rowallexp->biller_id,
											'amount' => $rowallexp->amount
										);
									}
						
									$total_currect_year = $total_income_current - $total_expense_current;


								?>

								<?php
								$total_retained_arr_current = array();

								for($c= 0; $c < count($new_billers); $c++){
									$in_bill_id1 = 0;
									if($this->uri->segment(7)){
										$in_bill_id1 = $new_billers[$c]['id'];
									}else{
										$in_bill_id1 = $new_billers[$c]->id;
									}

									$total_per_currenct = 0;
									
									$k = 0;
									$r_inc_per = 0;
									$r_exp_per = 0;
									

									if(count($retained_exp_arr) == 0){
										foreach($retained_inc_arr as $exp_row){
											if($exp_row['biller_id'] == $in_bill_id1){
												$r_exp_per += $exp_row['amount'];
											}
											$k++;
										}
									}
									
									foreach($retained_exp_arr as $exp_row){
										if($exp_row['biller_id'] == $in_bill_id1){
											$r_exp_per += $exp_row['amount'];
										}
									}
									

									foreach($retained_inc_arr as $inc_row) {
										if($in_bill_id1 == $inc_row['biller_id']){
											$r_inc_per += $inc_row['amount'];
										}
									}
									
									$total_per_currenct = $r_exp_per + $r_inc_per;
										
										$total_retained_arr_current[] = array(
											'biller_id' => $in_bill_id1,
											'amount' => $total_per_currenct
										);

									if($total_per_currenct>0){
										$total_per_currenct = '( '.number_format(abs($total_per_currenct),2).' )';
									}else{
										$total_per_currenct = number_format(abs($total_per_currenct),2);
									}
									echo '<td class="right"><a href="'.admin_url('reports/income_statement/').'">'. $total_per_currenct .'</a></td>';
								}
								//echo $total_amt_current;
								?>
								
								<?php if($total_currect_year < 0) { ?>						
									<td class="right"><?php echo '( ' . number_format(abs($total_currect_year),2) . ' )';?></td>
								<?php } else { ?>
									<td class="right"><?php echo number_format(abs($total_currect_year),2);?></td>
								<?php }	?>							

							</tr>
						<?php
							$total_equity = 0;
							$totalBeforeAyear_equity = 0;
							foreach($dataEquity->result() as $rowequity){
								$total_equity += $rowequity->amount;
								
								$index = 0;
								$total_per_eq = 0;
								echo '<tr>';
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index]['id'];
									}else{
										$bill_id = $new_billers[$index]->id;
									}
									$query = $this->db->query("SELECT
										SUM(bpas_gl_trans.amount) AS amount
									FROM
										bpas_gl_trans
									WHERE
										biller_id = '$bill_id' AND account_code = '" . $rowequity->account_code . "'
									AND date(bpas_gl_trans.tran_date) < '$to_date' ;");

									//AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date' AND '$to_date' ;");

									$totalBeforeAyearRows = $query->row();
									$totalBeforeAyear_equity += $totalBeforeAyearRows->amount;
									
									if(($index+1)==1){
										$total_eq_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);
									?>
									<td>
										<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&account='.$rowequity->account_code) ?>">
											<?php echo $rowequity->account_code;?> - <?php echo $rowequity->accountname;?>
										</a>
									</td>
									<td class="right">
										<a href="<?= admin_url('reports/ledger/?w=1'.$from_st.$to_st.'&account='.$rowequity->account_code) ?>">
											<?php 
											if($totalBeforeAyearRows->amount < 0){
												echo number_format(abs($totalBeforeAyearRows->amount),2);
											}else{
												echo "( ".number_format(abs($totalBeforeAyearRows->amount),2)." )";
											}
											?>
										</a>
									</td>
									<?php
									}else{
										$total_eq_arr[] = array(
											'biller_id' => $bill_id,
											'amount' => $totalBeforeAyearRows->amount
										);
										echo '<td class="right">' .number_format(abs($totalBeforeAyearRows->amount),2). '</td>';
									}
									$total_per_eq += $totalBeforeAyearRows->amount;
									$index++;
								}
								if($total_per_eq <= 0){
									echo '<td class="right">' .number_format(abs($total_per_eq),2).'</td>';
								}else{
									echo '<td class="right">( '.number_format(abs($total_per_eq),2).' )</td>';
								}
								echo '</tr>';
							}
						?>							
							<tr>
								<td>
									<b><?= lang("total_equities"); ?></b>
								</td>
							
							<?php
							for($c= 0; $c < count($new_billers); $c++){
								$in_bill_id1 = 0;
								if($this->uri->segment(7)){
									$in_bill_id1 = $new_billers[$c]['id'];
								}else{
									$in_bill_id1 = $new_billers[$c]->id;
								}
								$total_eq_amt = 0;
								$total_eq_amt_current = 0;
								$k = 0;
								if(count($total_eq_arr) == 0){
									foreach($total_retained_arr as $new_arr){
										if($new_arr['biller_id'] == $in_bill_id1){
											$total_eq_amt += $total_retained_arr[$k]['amount'] ;
										}
										$k++;
									}
								}
								foreach($total_eq_arr as $new_arr){
									if($new_arr['biller_id'] == $in_bill_id1){
										if($total_retained_arr[$k]['biller_id'] == $in_bill_id1){
											$total_eq_amt += $new_arr['amount'] + $total_retained_arr[$k]['amount'] ;
										}else{
											$total_eq_amt += $new_arr['amount'];
										}
									}
									$k++;
								}
							
								$sum_eq_arr[] = array(
									'biller_id' => $in_bill_id1,
									'amount' => $total_eq_amt
								);
								//--------
								$r_inc_per = 0;
								$r_exp_per = 0;
									

								if(count($retained_exp_arr) == 0){
									foreach($retained_inc_arr as $exp_row){
										if($exp_row['biller_id'] == $in_bill_id1){
											$r_exp_per += $exp_row['amount'];
										}
										$k++;
									}
								}
								
								foreach($retained_exp_arr as $exp_row){
									if($exp_row['biller_id'] == $in_bill_id1){
										$r_exp_per += $exp_row['amount'];
									}
								}
								

								foreach($retained_inc_arr as $inc_row) {
									if($in_bill_id1 == $inc_row['biller_id']){
										$r_inc_per += $inc_row['amount'];
									}
								}
								
								$total_per_currenct = $r_exp_per + $r_inc_per;
							
								
								$total_eq_amt = $total_eq_amt + $total_per_currenct;
								

								if($total_eq_amt<=0){
									echo '<td class="right"><b>'. number_format(abs($total_eq_amt), 2) .'</b></td>';
								}else{
									echo '<td class="right"><b>( '. number_format(abs($total_eq_amt), 2) .' )</b></td>';
								}
								$total_eq_amt = $total_eq_amt;
								
							}

							$total_eq_sum = (-1)*$total_equity + $total_retained + $total_currect_year;
							$end_total_eq = $total_eq_sum;


							?>
								<td class="right">
									<?php
									if($total_eq_sum > 0){
										echo '<b>' . number_format(abs($total_eq_sum),2) . '</b>';
									} else {
										echo '<b>( ' . number_format(abs($total_eq_sum),2) .' )</b>';
									} ?>
								</td>
							</tr>
							
							<tr>
								<td><b><?= lang("total_liabilities_equities"); ?></b></td>
								
								<?php
								
								for($c= 0; $c < count($new_billers); $c++){
									$in_bill_id1 = 0;
									if($this->uri->segment(7)){
										$in_bill_id1 = $new_billers[$c]['id'];
									}else{
										$in_bill_id1 = $new_billers[$c]->id;
									}
									$total_lib_eq = 0;
									$total_lib_current = 0;

									$k = 0;
									foreach($sum_lib_arr as $lib_row){
										if($lib_row['biller_id'] == $in_bill_id1 && ($lib_row['biller_id'] == $sum_eq_arr[$k]['biller_id'])){
											$total_lib_eq += $lib_row['amount'] + $sum_eq_arr[$k]['amount'];
										}
										$k++;
									}
									//------------
									$r_inc_per = 0;
									$r_exp_per = 0;
										

									if(count($retained_exp_arr) == 0){
										foreach($retained_inc_arr as $exp_row){
											if($exp_row['biller_id'] == $in_bill_id1){
												$r_exp_per += $exp_row['amount'];
											}
											$k++;
										}
									}
									
									foreach($retained_exp_arr as $exp_row){
										if($exp_row['biller_id'] == $in_bill_id1){
											$r_exp_per += $exp_row['amount'];
										}
									}
									

									foreach($retained_inc_arr as $inc_row) {
										if($in_bill_id1 == $inc_row['biller_id']){
											$r_inc_per += $inc_row['amount'];
										}
									}
									
									$total_per_currenct = $r_exp_per + $r_inc_per;

									$total_lib_eq = $total_lib_eq + $total_per_currenct;

									if($total_lib_eq<=0){
										$total_lib_eq = number_format(abs($total_lib_eq),2);
									}else{
										$total_lib_eq = '( '.number_format(abs($total_lib_eq),2).' )';
									}
									echo '<td class="right"><b>'. $total_lib_eq .'</b></td>';
									
									
								}
								
									$end_lib_eq = (-1)*$end_total_lib + $end_total_eq;
									$d = 0;
									if($end_lib_eq > 0){
										$end_lib_eq = number_format(abs($end_lib_eq),2);
									}else{
										$end_lib_eq = '( '.number_format(abs($end_lib_eq),2).' )';
									}
								?>								
								<td class="right"><b><?php echo $end_lib_eq;?></b></td>								
							</tr>							
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
	table{ 
		white-space: nowrap; 
		overflow-x: scroll;
		width:100%;
	}
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    	$('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {

            $("#form").slideUp();
            return false;
        });

		$("#biller-filter").on('click', function(event){
			event.preventDefault();
			var hasCheck = false;
			biller_ids = '';
			$.each($("input[name='biller_checkbox[]']:checked"), function(){
				hasCheck = true;
				biller_ids += $(this).val() + '-';
			});
			var billers = removeSymbolLastString(biller_ids, '-');
			if(hasCheck == true){
				var encodedName = encodeURIComponent(billers);
				var url = "<?php echo admin_url('reports/balance_sheet/'.$start.'/'.$end.'/0/0') ?>" + '/' + encodeURIComponent(billers);
				window.location.href = "<?=admin_url('reports/balance_sheet/'. $start .'/'.$end.'/0/0/')?>" + '/' + encodedName;
			}
			
			if(hasCheck == false){
				bootbox.alert('Please select project first!');
				return false;
			}
			return false;
		});
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/balance_sheet/'. $start .'/'.$end.'/pdf/0/'.$excel_biller_id)?>";
            return false;
        });
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/balance_sheet/'. $start .'/'.$end.'/0/xls/'.$excel_biller_id)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
	function exportF(elem) {
	  var table = document.getElementById("export_contain");
	  var html = table.outerHTML;
	  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
	  elem.setAttribute("href", url);
	  elem.setAttribute("download", "BalanceSheet.xls"); // Choose the file name
	  return false;
	}
	function removeSymbolLastString(string, symbol = ','){
		var strVal = $.trim(string);
		var lastChar = strVal.slice(-1);
		if (lastChar == symbol) {
			strVal = strVal.slice(0, -1);
		}
		return strVal;
	}
	
</script>