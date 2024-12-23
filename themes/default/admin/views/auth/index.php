<script>
    function user_remote_allow(x) {
        var y = x.split('__');
        return y[0] == 1 ?
            '<a href="' +
            site.base_url +
            'auth/is_remote_allow/' +
            y[1] +
            '"><span class="label label-success"><i class="fa fa-check"></i> ' +
            lang['active'] +
            '</span></a>' :
            '<a href="' +
            site.base_url +
            'auth/is_remote_allow/' +
            y[1] +
            '"><span class="label label-danger"><i class="fa fa-times"></i> ' +
            lang['inactive'] +
            '</span><a/>';
    }
    function password(x) {
        return '***********';
    }
    $(document).ready(function () {

        'use strict';
        oTable = $('#UsrTable').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('auth/getUsers' . ($biller_id ? '/' . $biller_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false,"mRender": checkbox}, 
                null, 
                null, 
                {"mRender": gender_status}, 
                null, 
                {"mRender": password}, 
                null, 
                {"mRender": user_status},{"mRender": user_remote_allow}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('first_name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('last_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('user_name');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('password');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
            {
                column_number: 7, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('active');?>'}, {value: '0', label: '<?=lang('inactive');?>'}]
            }
        ], "footer");
    });
</script>
<style>
    .table td:nth-child(6) {
        text-align: right;
        width: 10%;
    }
    .table td:nth-child(8) {
        text-align: center;
    }
    #dtFilter-filter--UsrTable-5 {
        text-align: right;
    }
</style>
<?php if ($Owner || $Store) {
    echo admin_form_open('auth/user_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('users') . ' (' . $biller_title . ')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('auth/create_user'); ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_user'); ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line('delete_users') ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_users') ?></a></li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('users') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('users/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('users') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = $count_billers;
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id == $value) {
                                        echo '<li><a href="' . admin_url('users/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                                    }
                                }
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="UsrTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?php echo lang('first_name'); ?></th>
                            <th class="col-xs-2"><?php echo lang('last_name'); ?></th>
                            <th class="col-xs-2"><?php echo lang('gender'); ?></th>
                            <th class="col-xs-2"><?php echo lang('user_name'); ?></th>
                            <th class="col-xs-1"><?php echo lang('password'); ?></th>
                            <th class="col-xs-2"><?php echo lang('user_group'); ?></th>
                            <th style="width:120px; text-align: center !important;"><?php echo lang('status'); ?></th>
                            <th style="width:120px; text-align: center !important;"><?php echo lang('is_remote_allow'); ?></th>
                            <th style="width:80px; text-align: center !important;"><?php echo lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th style="width:120px;"></th>
                            <th style="width:85px; text-align: center !important;"><?= lang('actions'); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Store) { ?>
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