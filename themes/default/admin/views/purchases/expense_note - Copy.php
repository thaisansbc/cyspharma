<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
  @media print {
    #myModal .modal-content {
      display: none !important;
    }
  }
</style>
<div class="modal-dialog modal-lg no-modal-header">
  <div class="modal-content">
    <div class="modal-body print">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        <i class="fa fa-2x">&times;</i>
      </button>
      <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
        <i class="fa fa-print"></i> <?= lang('print'); ?>
      </button>
      <div class="col-xs-4">
        <?php if ($logo) { ?>
          <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>" style="width:90%">
        <?php } ?>
      </div>
      <!-- <div class="clearfix"></div> -->
      <div class="col-xs-4">
        <h2 style="font-family: Khmer OS Content">សក្ខីប័ត្រចំណាយ</h1>
          <h3>PAYMENT VOUCHER</h1>
      </div>
      <div​ class="col-xs-4">
        <h4>លេខសក្ខីប័ត្រ/P.V No: Testdemo</h4>
        <h4>កាលបរិច្ឆេទ/Date: <?php echo $this->bpas->hrld($expense->date); ?></h4>
    </div>
    <!-- <div class="row padding10">
                <div class="col-xs-5">
                    <h2 class=""><?= $Settings->site_name; ?></h2>

                    <div class="clearfix"></div>
                </div>
            </div> -->
    <div class="clearfix"></div>
    <div class="col-xs-4">
      <h4>ឈ្មោះអ្នកផ្គត់ផ្គង់/Supplier Name:</h4>
      <h4>អាសយដ្ឋាន/Address:</h4>
    </div>
    <div class="col-xs-4" style="text-align: left;">
      <h4>General Supplier</h4>
      <h4>Phnom Penh</h4>
    </div>
    <div class="col-xs-4">
      <h4>លេខទូរស័ព្ទ/Phone:</h4>
      <h4>Payment to:</h4>
    </div>
    <div style="clear: both;"></div>
    <p>&nbsp;</p>


    <!-- <div class="well">
                <table class="table table-borderless" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang('date'); ?></strong></td>
                        <td><strong class="text-right"><?php echo $this->bpas->hrld($expense->date); ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang('reference'); ?></strong></td>
                        <td><strong class="text-right"><?php echo $expense->reference; ?></strong></td>
                    </tr>
                    <?php if ($category) { ?>
                    <tr>
                        <td><strong><?= lang('category'); ?></strong></td>
                        <td><strong class="text-right"><?php echo $category->name; ?></strong></td>
                    </tr>
                    <?php } ?>
                    <?php if ($warehouse) { ?>
                    <tr>
                        <td><strong><?= lang('warehouse'); ?></strong></td>
                        <td><strong class="text-right"><?php echo $warehouse->name; ?></strong></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?= lang('amount'); ?></strong></td>
                        <td><strong class="text-right"><?php echo $this->bpas->formatMoney($expense->amount); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $expense->note; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div> -->

    <!-- <div class="well">
                <table  cellspacing="0" border="0" class="table table-hover table-striped" style="margin-bottom:0;">
                    
                    <tbody>
                    
                    <tr>
                        <td colspan="1" rowspan="2">
                            <strong>ទូទាត់ដោយៈ <br>Payment By</strong>
                        </td>
                        <td colspan="7">
                            <input type="checkbox" name=""> Cash/
                            <input type="checkbox" name=""> Bank
                             &emsp;
                            <input type="checkbox" name=""> T.T/
                            <input type="checkbox" name=""> Cheque No
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            Cash/Bank Name &emsp;Cash on hand
                            <input type="checkbox" name=""> Bank Account No:
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7">លេខយោង​/ Ref.No:  Test3433</td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <input type="checkbox" name=""> សំណងទូទាត់ Reimbursement &emsp;
                            <input type="checkbox" name=""> ទូទាត់បុរេប្រទាន​ Advance Settlement &emsp;
                            <input type="checkbox" name=""> គម្រោងថវិការ Budget Plan &emsp;
                            <input type="checkbox" name=""> ផ្សេងៗ​ Other &emsp;
                        </td>
                    </tr>
                    
                    <tr>
                        <td rowspan="1"  rowspan="2">
                            <strong>លេខយោង​/ Ref.No: </strong>
                        </td>
                        <td colspan="5" rowspan="2">
                            <input type="checkbox" name=""> Cash/
                            <input type="checkbox" name=""> Bank
                        </td>
                        <td colspan="7" rowspan="2">
                            <input type="checkbox" name=""> Cash/test
                        </td>
                        <td colspan="7" rowspan="2">
                            <input type="checkbox" name=""> Cash/test
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div> -->
    <div class="well">
      <table cellspacing="0" border="0" class="table table-hover table-striped" style="margin-bottom:0;">
        <tbody>
          <tr>
            <td width="147" rowspan="2">
              <p>ទូទាត់ដោយៈ </p>
              <p>Payment By</p>
            </td>
            <td height="29" colspan="6">&nbsp;
              <input type="checkbox" name="">
              Cash /
              <input type="checkbox" name=""> Bank
              &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
              <input type="checkbox" name=""> T.T/
              <input type="checkbox" name=""> Cheque No</td>
          </tr>
          <tr>
            <td height="29" colspan="6">&nbsp;
              Cash/Bank Name: &emsp;Cash on hand
              &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
              <input type="checkbox" name=""> Bank Account No:

            </td>
          </tr>
          <tr>
            <td height="38" colspan="7">លេខយោង/ Ref No: <?php echo $expense->reference; ?></td>
          </tr>
          <tr>
            <td height="42" colspan="7">
              <p>
                <input type="checkbox" name="">
                សំណងទូទាត់ Reimbursement &emsp;
                <input type="checkbox" name=""> ទូទាត់បុរេប្រទាន​ Advance Settlement &emsp;
                <input type="checkbox" name=""> គម្រោងថវិការ Budget Plan &emsp;
                <input type="checkbox" name=""> ផ្សេងៗ​ Other &emsp;

              </p>
            </td>
          </tr>
          <tr>
            <td rowspan="2"><strong>លេខយោង / Ref.No: </strong>&nbsp;</td>
            <td colspan="4" rowspan="2" style="text-align: center;">បរិយាយ Description&nbsp;</td>
            <td colspan="2" style="text-align: center;">ចំនួនតួលេខ Amount in Figure</td>
          </tr>
          <tr>
            <td width="152" style="text-align: center;">ប្រាក់រៀល KHR</td>
            <td width="167">ប្រាក់ដុល្លាUSD</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td colspan="4">&nbsp;</td>
            <td>&nbsp;<?php echo $this->bpas->formatMoney($expense->amount) * 4000; ?></td>
            <td>&nbsp;<?php echo $this->bpas->formatMoney($expense->amount); ?></td>
          </tr>
          <tr>
            <td colspan="5" align="right">សរុប Total</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="6" rowspan="2">
              <p>ចំនួនជាអក្សរ KHR:</p>
              <p>In words: USD:</p>
            </td>
            <td height="35">អត្រា Exchange Rate</td>
          </tr>
          <tr>
            <td height="31">KH: 4000</td>
          </tr>
          <tr>
            <td height="54">&nbsp;</td>
            <td width="123">&nbsp;</td>
            <td width="138">&nbsp;</td>
            <td width="110">&nbsp;</td>
            <td width="117">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="32">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="65">
              <p>រៀបចំដោយ:</p>
              <p>Prepared by:</p>
            </td>
            <td>
              <p>ត្រួតពិនិត្យដោយ:</p>
              <p>Checked by:</p>
            </td>
            <td>
              <p>ទទួលស្គាល់ដោយ:</p>
              <p>Acknowledge By:</p>
            </td>
            <td>
              <p>សម្រេចដោយ:</p>
              <p>Approved By:</p>
            </td>
            <td>
              <p>សម្គាល់:</p>
              <p>Remark:</p>
            </td>
            <td>
              <p>ទូទាត់ដោយ:</p>
              <p>Paid By:</p>
            </td>
            <td>
              <p>ទទួលដោយ:</p>
              <p>Received By:</p>
            </td>
          </tr>
        </tbody>
      </table>

      <table cellspacing="0" border="0" class="table table-hover table-striped hide" style="margin-bottom:0;">
        <tbody>
          <tr>
            <td colspan="6" style="text-align: center; color: white; background-color: #1e5c70;">សម្រាប់ការិយាល័យគណនេយ្យ For Accounting Department</td>
          </tr>
          <tr>
            <td width="142">លេខគណនី <br>A/C Code</td>
            <td width="251">ឈ្មោះគណនេយ្យ <br>Account Name</td>
            <td width="269" colspan="2">បរិយាយ <br>
              Description</td>
            <td width="157">ឥណពន្ធ <br>Debit</td>
            <td width="146">ឥណទាន <br> Credit</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="4" style="text-align: right;">សរុប Total:</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>

        </tbody>
      </table>
      <table cellspacing="0" border="0" class="table table-hover table-striped" style="margin-bottom:0;">
        <tbody>
          <tr>
            <td width="143">
              <p>កត់ត្រាដោយ</p>
              <p>Posted by</p>
            </td>
            <td width="243">
              <p>&nbsp;</p>
              <p>&nbsp;</p>
            </td>
            <td width="130">
              <p>ត្រួតពិនិត្យដោយ</p>
              <p>Verified By</p>
            </td>
            <td width="292">&nbsp;</td>
            <td width="146">កត់ត្រា Posting</td>
          </tr>
          <tr>
            <td height="52">ឈ្មោះ Name</td>
            <td>&nbsp;</td>
            <td>ឈ្មោះ Name</td>
            <td>&nbsp;</td>
            <td rowspan="2">
              <input type="checkbox" name=""> Enter Bill <br>
              <input type="checkbox" name=""> Pay Bill <br>
              <input type="checkbox" name=""> Write Check <br>
              <input type="checkbox" name=""> Journal
            </td>
          </tr>
          <tr>
            <td height="53">កាលបរិច្ឆេទ Date</td>
            <td>&nbsp;</td>
            <td>កាលបរិច្ឆេទ Date</td>
            <td>&nbsp;</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="clear: both;"></div>
    <div class="row">
      <div class="col-sm-4 pull-left">
        <p>&nbsp;</p>

        <p>&nbsp;</p>

        <p>&nbsp;</p>

        <p style="border-bottom: 1px solid #666;">&nbsp;</p>

        <p><?= lang('stamp_sign'); ?></p>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</div>
</div>