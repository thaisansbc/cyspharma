<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('zone_group')) {
    $v .= '&zone_group=' . $this->input->post('zone_group');
}

?>
<script>

    $(document).ready(function () {
        function multi_commune(x) {
            if (x ==null){
                return '';
            }else{
                var com = x.split(",");
                var commune ='';
                for (var x = 0; x < com.length; x++) {
                  commune += ' <span class="label label-warning">'+com[x]+'</span>';
                }
                return commune;
            }
        }

        $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });

        oTable = $('#ZoneTable').dataTable({
            "aaSorting": [[3, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': false, 'bServerSide': false,
        //    'sAjaxSource': '<?= admin_url('system_settings/getZones/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            }
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('zone_group');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('city_province');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('district');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('commune');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('zones'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('system_settings/add_zone'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_zone') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_zones') ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_zones') ?>
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
                <div id="form">
                    <?php echo admin_form_open("system_settings/zones"); ?>
                        <div class="row">
                            <div class="col-sm-4">
                                   <div class="form-group">
                                        <?= lang('zone_group', 'zone_group'); ?>
                                        <?php 
                                        $get_fields = $this->site->getcustomfield('ZoneGroup');
                                        $field ['']=lang('select');
                                        if (!empty($get_fields)) {
                                            foreach ($get_fields as $field_id) {
                                                $field[$field_id->id] = $field_id->name;
                                            }
                                        }
                                        echo form_dropdown('zone_group',$field,(isset($_POST['zone_group']) ? $_POST['zone_group'] : ''), 'class="form-control select" required'); ?>
                                    </div>

                            </div>

                        </div>
                        <div class="form-group">
                            <div class="controls"> <?php echo form_submit('submit', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                    <?php echo form_close(); ?>
                </div>

                <p class="introtext"><?= lang('list_results'); ?></p>
                <?= admin_form_open('system_settings/zone_actions', 'id="action-form"') ?>
                <div class="table-responsive">
                    <table id="ZoneTable" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th width="150"><?= lang('zone_group'); ?></th>
                                <th width="100"><?= lang('code'); ?></th>
                                <th><?= lang('zone_name'); ?></th>
                                <th><?= lang("city_province"); ?></th>
                                <th><?= lang("district"); ?></th>
                                <th><?= lang("commune"); ?></th>
                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($getZones as $zone){
                                $group = $this->site->getcustomfieldById($zone->zone_group_id);
                                $cities = $this->settings_model->getMultiAreaByID($zone->city_id);
                                $districts = $this->settings_model->getMultiAreaByID($zone->district_id);
                                $communes = $this->settings_model->getMultiAreaByID($zone->commune_id);
                            ?>
                            <tr>

                                <td style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox" type="checkbox" name="val[]" value="<?= $zone->id ?>" id="<?= $zone->id ?>" />
                                </td>

                                <td><?= $group->name?></td>
                                <td><?= $zone->zone_code?></td>
                                <td><?= $zone->zone_name?></td>
                                <td>
                                    <?php 
                                    if(!empty($zone->city_id)){
                                        foreach($cities as $city){
                                            echo ' <span class="label label-success" style="margin-bottom:3px;">'.$city->name.'</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    
                                    <?php 
                                    if(!empty($zone->district_id)){
                                        foreach($districts as $district){
                                            echo ' <span class="label label-info">'.$district->name.'</span>';
                                        }
                                    }
                                    ?>

                                </td>
                                <td>
                                    <?php 
                                    if(!empty($zone->commune_id)){
                                        foreach($communes as $commune){
                                            echo ' <span class="label label-warning">'.$commune->name.'</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                      
                                        <?php //if ($Owner || $Admin) { ?>
                                        <a href="<?= admin_url('system_settings/edit_zone/' . $zone->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-edit"></i></a>


                                        
                                            <a href="#" class="tip po" title="" data-content="<p>Are you sure?</p><a class='btn btn-danger' href='<?= admin_url('system_settings/delete_zone/' . $zone->id) ?>'>Yes I'm sure</a> <button class='btn po-close'>No</button>" data-original-title="Delete Project"><i class="fa fa-trash-o"></i></a>
                                        <?php //} ?>
                                          
                                    </div>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
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
                            <th class="text-center"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>