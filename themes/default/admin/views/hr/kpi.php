<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
		
		<?php if($this->session->userdata('remove_kpls')) { ?>
			if (localStorage.getItem('kpitems')) {
                localStorage.removeItem('kpitems');
            }
            if (localStorage.getItem('kpmnote')) {
                localStorage.removeItem('kpmnote');
            }
			if (localStorage.getItem('kpenote')) {
                localStorage.removeItem('kpenote');
            }
            if (localStorage.getItem('kpdate')) {
                localStorage.removeItem('kpdate');
            }
			if (localStorage.getItem('kpmonth')) {
                localStorage.removeItem('kpmonth');
            }
			if (localStorage.getItem('kpemployee')) {
                localStorage.removeItem('kpemployee');
            }
			if (localStorage.getItem('kpkpi_type')) {
                localStorage.removeItem('kpkpi_type');
            }
        <?php $this->bpas->unset_data('remove_kpls'); } ?>
		
		function resultBox(x){
			return '<div class="text-center">'+formatDecimal(x)+'%</div>';
		}
		function creditBox(x){
			if(x){
				var res = x.split("#");
				return '<div style="background-color:#'+res[1]+'" class="text-center">'+(res[0])+'</div>';
			}else{
				return x;
			}
			
		}
        var oTable = $('#KPITable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getKPI/') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox},
			{"mRender": fsd},
			null,
			null,
			null,
			{"mRender": resultBox},
			{"mRender": creditBox},
			{"mRender": decode_html},
			{"mRender": decode_html},
			{"bSortable": false,"sClass":"center"}]
			,'fnRowCallback': function (nRow, aData, iDisplayIndex) {
					var oSettings = oTable.fnSettings();
					nRow.id = aData[0];
					nRow.className = "kpi_link";
					return nRow;
				},
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {									
				}
			}).fnSetFilteringDelay().dtFilter([        
				{column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
				{column_number: 2, filter_default_label: "[<?= lang("month") ?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?= lang("employee") ?>]", filter_type: "text", data: []},
				{column_number: 4, filter_default_label: "[<?= lang("kpi_type") ?>]", filter_type: "text", data: []},
				{column_number: 5, filter_default_label: "[<?= lang("result") ?>]", filter_type: "text", data: []},
				{column_number: 6, filter_default_label: "[<?= lang("credit") ?>]", filter_type: "text", data: []},
				{column_number: 7, filter_default_label: "[<?= lang("manager_note") ?>]", filter_type: "text", data: []},
				{column_number: 8, filter_default_label: "[<?= lang("employee_note") ?>]", filter_type: "text", data: []},
			], "footer");
    });
</script>


<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('hr/kpi_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('kpi'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">

						<li>
                            <a href="<?=admin_url('hr/add_kpi')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_kpi')?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
								title="<b><?=lang("delete_kpi")?></b>"
								data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
								data-html="true" data-placement="left">
								<i class="fa fa-trash-o"></i> <?=lang('delete_kpi')?>
							</a>
						</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="KPITable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-condensed table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
								<th><?= lang("date"); ?></th>
                                <th><?= lang("month"); ?></th>
								<th><?= lang("employee"); ?></th>
								<th><?= lang("kpi_type"); ?></th>
								<th><?= lang("result"); ?></th>
								<th><?= lang("credit"); ?></th>
								<th><?= lang("manager_note"); ?></th>
								<th><?= lang("employee_note"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th style="width:100px; text-align: center;"></th>
							</tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <script language="javascript">
        $(document).ready(function () {
            $('#set_admin').click(function () {
                $('#usr-form-btn').trigger('click');
            });

        });
    </script>
<?php } ?>