<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#TOData').dataTable({
    "aaSorting": [[1, "desc"], [2, "desc"]],
    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
    "iDisplayLength": <?= $Settings->rows_per_page ?>,
    'bProcessing': true, 'bServerSide': true,
    'sAjaxSource': '<?= admin_url('transfers/getTransfers' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
    'fnServerData': function (sSource, aoData, fnCallback) {
        aoData.push({
            "name": "<?= $this->security->get_csrf_token_name() ?>",
            "value": "<?= $this->security->get_csrf_hash() ?>"
        });
        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
    },
    "aoColumns": [
        {"bSortable": false, "mRender": checkbox},
        {"mRender": fld},
        null,
        null,
        null,
        {"mRender": row_status},
        {"bSortable": false, "mRender": attachment},
        {"bSortable": false}
    ],
    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
        var oSettings = oTable.fnSettings();
        nRow.id = aData[0];
        nRow.className = "transfer_link";
        return nRow;
    }
}).fnSetFilteringDelay().dtFilter([
    {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
    {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
    {column_number: 3, filter_default_label: "[<?=lang('warehouse') . ' (' . lang('from') . ')';?>]", filter_type: "text", data: []},
    {column_number: 4, filter_default_label: "[<?=lang('warehouse') . ' (' . lang('to') . ')';?>]", filter_type: "text", data: []},
    {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
], "footer");
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('transfers/transfer_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <?php $wh_title = ($warehouse_id ? $warehouse->name : ((isset($user_warehouse) && !empty($user_warehouse)) ? $user_warehouse->name : lang('all_warehouses'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('transfers') . ' (' . $wh_title . ')'; ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang('actions') ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('transfers/add') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_transfer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= admin_url('transfers/transfer_by_csv') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_transfer_by_csv') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="combine" data-action="combine">
                            <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_transfers') ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_transfers') ?>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="box-icon">
        <ul class="btn-tasks">
            <?php if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('transfers') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('transfers/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($warehouses)){ ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('transfers') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $warehouse_id = explode(',', $this->session->userdata('warehouse_id'));
                        foreach ($warehouses as $warehouse) {
                            foreach ($warehouse_id as $key => $value) {
                                if ($warehouse->id==$value) {
                                    echo '<li><a href="' . admin_url('transfers/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                            }
                            
                        } ?>    
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-hover table-striped">
                    <thead>
    <tr class="active">
        <th style="min-width:30px; width: 30px; text-align: center;">
            <input class="checkbox checkft" type="checkbox" name="check"/>
        </th>
        <th><?= lang('stock_transfer_date'); ?></th>
        <th><?= lang('stock_transfer_reference_no'); ?></th>
        <th><?= lang('warehouse') . ' (' . lang('from') . ')'; ?></th>
        <th><?= lang('warehouse') . ' (' . lang('to') . ')'; ?></th>
        <th><?= lang('status'); ?></th>
        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
        <th style="width:100px;"><?= lang('actions'); ?></th>
    </tr>
</thead>
<tbody>
    <tr>
        <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
    </tr>
</tbody>
<tfoot class="dtFilter">
    <tr class="active">
        <th style="min-width:30px; width: 30px; text-align: center;">
            <input class="checkbox checkft" type="checkbox" name="check"/>
        </th>
        <th></th><th></th><th></th><th></th>
        <th></th>
        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
        <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
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