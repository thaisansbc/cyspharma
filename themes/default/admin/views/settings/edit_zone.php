<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_zone'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('system_settings/edit_zone/' . $zone->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group hide">
                <?= lang('code', 'zone_code'); ?>
                <?= form_input('zone_code', set_value('zone_code', $zone->zone_code), 'class="form-control tip" id="code"'); ?>
            </div>
            <div class="form-group">
                <?= lang('zone_name', 'zone_group'); ?>
                <?php 
                $get_fields = $this->site->getcustomfield('ZoneGroup');
                $field ['']=lang('select');
                if (!empty($get_fields)) {
                    foreach ($get_fields as $field_id) {
                        $field[$field_id->id] = $field_id->name;
                    }
                }
                echo form_dropdown('zone_group',$field,$zone->zone_group_id, 'class="form-control select" required'); ?>
            </div>
            <div class="form-group">
                <?= lang('name', 'zone_name'); ?>
                <?= form_input('zone_name', set_value('zone_name', $zone->zone_name), 'class="form-control tip" id="name" required="required"'); ?>
            </div>
            <div class="form-group hide">
                <?= lang('parent_zone', 'parent') ?>
                <?php
                $z[''] = lang('select') . ' ' . lang('parent_zone');
                foreach ($zones as $zn) {
                    $z[$zn->id] = $zn->zone_name;
                }
                echo form_dropdown('parent', $z, (isset($_POST['parent']) ? $_POST['parent'] : $zone->parent_id), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
            
            <div class="form-group">
                <?php echo lang('city_province', 'city_id'); ?>
                <div class="controls">
                    <?php
                    $ct[""] = lang("select")." ".lang("city_province");
                    $city_id = explode(',', $zone->city_id);
                    if($cities){
                        foreach ($cities as $city) {
                           $ct[$city->id] = $city->name; 
                        }
                    }
                    echo form_dropdown('city_id[]', $ct, $city_id, 'id="city_id" class="form-control" multiple="multiple"');
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('district', 'district_id'); ?>
                <div class="district_box">
                    <?php
                        $ds[""] = lang("select")." ".lang("district");
                        $district_id = explode(',', $zone->district_id);
                        if($districts){
                            foreach ($districts as $district) {
                               $ds[$district->id] = $district->name; 
                            }
                        }
                        echo form_dropdown('district_id[]', $ds, $district_id, 'id="district_id" class="form-control" multiple="multiple" ');
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('commune', 'commune_id'); ?>
                <div class="commune_box">
                    <?php
                        $cm[""] = lang("select")." ".lang("commune");
                        $commune_id = explode(',', $zone->commune_id);
                        if($communes){
                            foreach ($communes as $commune) {
                               $cm[$commune->id] = $commune->name; 
                            }
                        }
                        echo form_dropdown('commune_id[]', $cm, $commune_id, 'id="commune_id" class="form-control"  multiple="multiple" ');
                    ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_zone', lang('edit_zone'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#city_id").live("change",function(){
            var city_id = $(this).val();
            $.ajax({
                url : site.base_url + "system_settings/get_district_area",
                dataType : "JSON",
                type : "GET",
                data : { city_id : city_id},
                success : function(data){
                    var district_sel = "<select class='form-control' id='district_id' name='district_id'><option value=''><?= lang('select').' '.lang('district') ?></option>";
                    if (data != false) {
                        $.each(data, function () {
                            district_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
                        });
                    }
                    district_sel += "</select>"
                    $(".district_box").html(district_sel);
                    $('select').select2();  
                }
            });
        });
        $("#district_id").live("change",function(){
            var district_id = $(this).val();
            $.ajax({
                url : site.base_url + "system_settings/get_commune_area",
                dataType : "JSON",
                type : "GET",
                data : { district_id : district_id},
                success : function(data){
                    var commune_sel = "<select class='form-control' id='commune_id' name='commune_id[]' multiple='multiple'><option value=''><?= lang('select').' '.lang('commune') ?></option>";
                    if (data != false) {
                        $.each(data, function () {
                            commune_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
                        });
                    }
                    commune_sel += "</select>"
                    $(".commune_box").html(commune_sel);
                    $('select').select2();  
                }
            });
        });
    });
</script>