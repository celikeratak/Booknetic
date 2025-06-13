<?php

defined( 'ABSPATH' ) or die();

use BookneticAddon\AdvancedReports\AdvancedReportsAddon;
use BookneticApp\Providers\Helpers\Helper;
use function BookneticAddon\AdvancedReports\bkntc__;


?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<link rel="stylesheet" href="<?php echo AdvancedReportsAddon::loadAsset('assets/backend/css/main.css'); ?>">

<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

<script>
  window.booknetic = window.booknetic || {};
  window.booknetic.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>


<script src="<?php echo AdvancedReportsAddon::loadAsset('assets/backend/js/advancedreports.js'); ?>"></script>

<div class="m_header clearfix">
  <div class="m_head_title float-left">Appointments Table (DataTables) - Test</div>
</div>

<div id="module-advancedreports" style="margin-top:20px;">
  <div class="row">
    <div class="col-md-3">
      <label>Status filter</label>
      <select class="form-control" id="statusFilter">
        <option value="">-- All --</option>
        <option value="approved">approved</option>
        <option value="canceled">canceled</option>
        <option value="rejected">rejected</option>
        <option value="punkte">punkte</option>
      </select>
    </div>
    <div class="col-md-3">
      <label>Payment method</label>
      <select class="form-control" id="paymentFilter">
        <option value="">-- All --</option>
        <option value="local">local</option>
        <option value="giftcard">giftcard</option>
        <option value="punkte">punkte</option>
      </select>
    </div>
    <div class="col-md-3">
      <label>Staff ID</label>
      <input type="text" class="form-control" id="staffFilter" placeholder="Enter staff_id" />
    </div>
    <div class="col-md-3" style="margin-top:25px;">
      <button class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
    </div>
  </div>

  <div class="row" style="margin-top:20px;">
    <div class="col-md-12">
      <table id="myTable" class="display" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Staff ID</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Paid Amount</th>
            <th>Created At</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>