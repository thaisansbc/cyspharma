<?php 
	defined('BASEPATH') OR exit('No direct script access allowed'); 
	$v = "";
	if($this->input->get("status")){
		$v .= "&status=". $this->input->get("status");
	}
?>
<script>
    $(document).ready(function () {
        oTable = $('#CSMData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getConsignments/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : '').'?v=1'.$v );?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "consignment_link";
                return nRow;
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, null,{"mRender": currencyFormat},{"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('total');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        <?php if($this->session->userdata('remove_csmls')) { ?>
        if (localStorage.getItem('csmitems')) {
            localStorage.removeItem('csmitems');
        }

        if (localStorage.getItem('csmref')) {
            localStorage.removeItem('csmref');
        }
        if (localStorage.getItem('csmwarehouse')) {
            localStorage.removeItem('csmwarehouse');
        }
        if (localStorage.getItem('csmnote')) {
            localStorage.removeItem('csmnote');
        }
        if (localStorage.getItem('csmcustomer')) {
            localStorage.removeItem('csmcustomer');
        }
        if (localStorage.getItem('csmbiller')) {
            localStorage.removeItem('csmbiller');
        }
        if (localStorage.getItem('csmdate')) {
            localStorage.removeItem('csmdate');
        }
		if (localStorage.getItem('csmvalid_day')) {
			localStorage.removeItem('csmvalid_day');
		}
        <?php $this->bpas->unset_data('remove_csmls'); } ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('products/consignment_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
  <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('consignments').' ('.($biller ? $biller->name : lang('all_billers')).') ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('products/add_consignment') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_consignment') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_consignments") ?></b>" 
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                            data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_consignments') ?>
                        </a>
                    </li>
                </ul>
            </li>
       
             <?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products/consignments') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('products/consignments/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            <?php } ?>
            
            <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products/consignments') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li><a href="' . admin_url('products/consignments/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </liv>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="CSMData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>                            
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
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
<?php } ?>