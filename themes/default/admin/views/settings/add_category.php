<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_category'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/add_category', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', set_value('code'), 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', set_value('name'), 'class="form-control gen_slug" id="name" required="required"'); ?>
            </div>
            <div class="form-group all hide">
                <?= lang('slug', 'slug'); ?>
                <?= form_input('slug', set_value('slug'), 'class="form-control tip" id="slug"'); ?>
            </div>
            <div class="form-group all hide">
                <?= lang('order_number', 'order_number'); ?>
                <input type="number" name="order_number" class="form-control">
            </div>
            <div class="form-group all">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', set_value('description'), 'class="form-control tip" id="description"'); ?>
            </div>
            <div class="form-group all hide">
                <?= lang('status', 'status'); ?>
                <?php $cst = ['show' => lang('show'), 'hide' => lang('hide')];
                echo form_dropdown('status', $cst, '', 'class="form-control input-tip" id="status"'); ?>
            </div>
            <?php if($this->config->item('multi_biller')){ ?>
                <div class="form-group hide">
                    <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                    <?php
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                    }
                    echo form_dropdown('biller[]', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control biller" id="biller" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                    ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= lang('category_image', 'image') ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
            <div class="form-group">
                <?= lang('parent_category', 'parent') ?>
                <?php
                $cat[''] = lang('select') . ' ' . lang('parent_category');
                if (!empty($categories)) {
                    foreach ($categories as $pcat) {
                        $cat[$pcat->id] = $pcat->name;
                    }
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : ''), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_category', lang('add_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
    $(document).ready(function() {
        $('.gen_slug').change(function(e) {
            getSlug($(this).val(), 'category');
        });
    });
</script>