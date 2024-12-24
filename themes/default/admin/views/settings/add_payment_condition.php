<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment_term'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');

        echo admin_form_open("system_settings/add_payment_condition", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang("title", "title"); ?>
                <?php echo form_input('title', '', 'class="form-control" id="title" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang("description", "description"); ?>
                <?php echo form_textarea('description', (isset($_POST['description']) ? $_POST['description'] : ""), 'class="form-control" id="jnnote" style="margin-top: 10px; height: 100px;"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment_term', lang('add_payment_condition'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

