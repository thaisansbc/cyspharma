<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#DepartmentTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getDepartments/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox},
			null,
			null,
			null,
			{"bSortable": false,"sClass":"center"}]
			,'fnRowCallback': function (nRow, aData, iDisplayIndex) {
					var oSettings = oTable.fnSettings();
					nRow.id = aData[0];
					nRow.className = "";
					return nRow;
				},
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {									
				}
			}).fnSetFilteringDelay().dtFilter([            
				{column_number: 1, filter_default_label: "[<?= lang("code") ?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?= lang("name") ?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?= lang("description") ?>]", filter_type: "text", data: []},				
			], "footer");
    });
</script>


<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('hr/department_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('departments').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <!-- <a href="<?php echo admin_url('hr/add_department/'.$parent_id); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_department') ?>
                            </a> -->
                            <a href="<?php echo admin_url('hr/add_department'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_department') ?>
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
								title="<b><?=lang("delete_departments")?></b>"
								data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
								data-html="true" data-placement="left">
								<i class="fa fa-trash-o"></i> <?=lang('delete_departments')?>
							</a>
						</li>
                    </ul>
                </li>
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?= admin_url('hr/departments') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
								<li class="divider"></li>
								<?php
								foreach ($billers as $biller) {
									echo '<li><a href="' . admin_url('hr/departments/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
								}
								?>
							</ul>
						</liv>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="DepartmentTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-condensed table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
								<th><?= lang("code"); ?></th>
                                <th><?= lang("name"); ?></th>
								<th><?= lang("description"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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