<?php
use BookneticApp\Providers\Helpers\Helper;
use BookneticAddon\Inventory\ProductInventoryAddon;
use function BookneticAddon\Inventory\bkntc__;

defined('ABSPATH') or die();

/**
 * @var array $parameters
 */

$staffs = $parameters['staffs'];
$services = $parameters['services'];
$customers = $parameters['customers'];
$statuses = $parameters['statuses'];

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/daterangepicker/datepicker.css') ?>">
<link rel="stylesheet" href="<?php echo Helper::assets('css/daterangepicker/customDatePicker.css') ?>">

<script type="application/javascript" src="<?php echo Helper::assets('js/moment.min.js', 'Dashboard') ?>"></script>
<script src="<?php echo Helper::assets('js/daterangepicker.min.js', 'Dashboard') ?>"></script>
<script type="application/javascript" src="<?php echo ProductInventoryAddon::loadAsset("assets/backend/js/logs.js") ?>"></script>

<section class="table-wrapper">
    <header class="table-wrapper-header d-flex justify-content-between align-items-center">
        <h1><?php echo bkntc__('Logs') ?></h1>
        <div class="d-flex align-items-center justify-content-end">
            <div class="d-flex justify-content-between table-wrapper-buttons">
                <div class="range-wrapper d-flex">
                    <button class="select-predifined-date-badge d-flex align-items-center inventory-log-btn">
                        <div>
                            <img src="<?php echo Helper::assets('icons/date.svg') ?>"
                                 alt="">
                        </div>
                        <span class="select-predifined-date-badge__content"><?php echo bkntc__('Last 30 Days') ?></span>
                    </button>
                    <div class="range-picker ranger-hidden">
                        <div class="range-picker-container range-picker-commission">
                            <input type="text" name="inventory-date-picker" class="range-date-picker"
                                   id="inventory-date-filter" value="" />
                        </div>
                    </div>
                </div>
                <div class="advanced-filter-wrapper">
                    <button class="advanced-filter d-flex align-items-center inventory-advanced-filter-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 8H12M2 4H14M6 12H10" stroke="#212529" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round" />
                        </svg>
                        <span><?php echo bkntc__('Advanced filter') ?></span>
                    </button>
                    <div class="advanced-filter-dropdown inventory-advanced-filter-dropdown">
                        <div>
                            <label class="advanced-filter-header"><?php echo bkntc__('Service') ?></label>
                            <select class="form-control" id="advancedFilterService">
                                <option></option>
                                <?php foreach ($services as $service):?>
                                    <option value="<?php echo (int)$service['id']?>"><?php echo htmlspecialchars($service['name'])?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div>
                            <label class="advanced-filter-header"><?php echo bkntc__('Staff') ?></label>
                            <select class="form-control" id="advancedFilterStaff">
                                <option></option>
                                <?php foreach ($staffs as $staff):?>
                                    <option value="<?php echo (int)$staff['id']?>"><?php echo htmlspecialchars($staff['name'])?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div>
                            <label class="advanced-filter-header"><?php echo bkntc__('Customer') ?></label>
                            <select class="form-control" id="advancedFilterCustomer">
                                <option></option>
                                <?php foreach ($customers as $customer):?>
                                    <option value="<?php echo (int)$customer['id']?>"><?php echo htmlspecialchars($customer['first_name'] . " " . $customer['last_name'])?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div>
                            <label class="advanced-filter-header"><?php echo bkntc__('Status') ?></label>
                            <select class="form-control" id="advancedFilterStatus">
                                <option></option>
                                <?php foreach ($statuses as $status):?>
                                    <option value="<?php echo $status['slug']; ?>"><?php echo htmlspecialchars($status['title']); ?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <footer class="advanced-filter-footer d-flex justify-content-end">
                            <button class="advanced-filter-reset-btn inventory-filter-reset-btn"><?php echo bkntc__('Reset') ?></button>
                            <button class="advanced-filter-save-btn inventory-filter-save-btn"><?php echo bkntc__('Save') ?></button>
                        </footer>
                    </div>
                </div>
                <button id="exportLogs" class="export-csv d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M14 14H2M12 7.33333L8 11.3333M8 11.3333L4 7.33333M8 11.3333V2" stroke="#212529"
                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span><?php echo bkntc__('Export to CSV') ?></span>
                </button>
            </div>
        </div>
    </header>
    <div class="total-statistics d-flex justify-content-between align-items-center">
        <div class="total-statistics-card">
            <h3 id="totalSold">-</h3>
            <p class="d-flex align-items-center"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                      viewBox="0 0 20 20" fill="none">
                    <g clip-path="url(#clip0_3724_21373)">
                        <path
                            d="M10 4.99996V9.99996L13.3333 11.6666M18.3333 9.99996C18.3333 14.6023 14.6024 18.3333 10 18.3333C5.39762 18.3333 1.66666 14.6023 1.66666 9.99996C1.66666 5.39759 5.39762 1.66663 10 1.66663C14.6024 1.66663 18.3333 5.39759 18.3333 9.99996Z"
                            stroke="#626C76" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <clipPath id="clip0_3724_21373">
                            <rect width="20" height="20" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <span><?php echo bkntc__('Total sold') ?></span>
            </p>
        </div>
        <div class="total-statistics-card">
            <h3 id="totalRevenue">-</h3>
            <p class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
                    <g clip-path="url(#clip0_3724_21379)">
                        <path
                            d="M10.6667 0C5.15249 0 0.666656 4.48583 0.666656 10C0.666656 15.5142 5.15249 20 10.6667 20C16.1808 20 20.6667 15.5142 20.6667 10C20.6667 4.48583 16.1808 0 10.6667 0ZM10.6667 18.3333C6.07166 18.3333 2.33332 14.595 2.33332 10C2.33332 5.405 6.07166 1.66667 10.6667 1.66667C15.2617 1.66667 19 5.405 19 10C19 14.595 15.2617 18.3333 10.6667 18.3333ZM14 11.6667C14 13.045 12.8783 14.1667 11.5 14.1667V15C11.5 15.4608 11.1275 15.8333 10.6667 15.8333C10.2058 15.8333 9.83332 15.4608 9.83332 15V14.1667H9.60999C8.72082 14.1667 7.89082 13.6883 7.44499 12.9175C7.21416 12.5183 7.35082 12.0092 7.74832 11.7792C8.14749 11.5467 8.65749 11.685 8.88666 12.0825C9.03582 12.3408 9.31249 12.5 9.60916 12.5H11.4992C11.9592 12.5 12.3325 12.1267 12.3325 11.6667C12.3325 11.3517 12.1067 11.085 11.7958 11.0333L9.26166 10.6108C8.14332 10.425 7.33249 9.46667 7.33249 8.33333C7.33249 6.955 8.45416 5.83333 9.83249 5.83333V5C9.83249 4.54 10.205 4.16667 10.6658 4.16667C11.1267 4.16667 11.4992 4.54 11.4992 5V5.83333H11.7225C12.6117 5.83333 13.4417 6.3125 13.8875 7.08333C14.1183 7.48167 13.9817 7.99083 13.5842 8.22167C13.1842 8.4525 12.675 8.31583 12.4458 7.9175C12.2967 7.66 12.02 7.50083 11.7233 7.50083H9.83332C9.37332 7.50083 8.99999 7.875 8.99999 8.33417C8.99999 8.64917 9.22582 8.91583 9.53666 8.9675L12.0708 9.39C13.1892 9.57583 14 10.5342 14 11.6675V11.6667Z"
                            fill="#626C76" />
                    </g>
                    <defs>
                        <clipPath id="clip0_3724_21379">
                            <rect width="20" height="20" fill="white" transform="translate(0.666656)" />
                        </clipPath>
                    </defs>
                </svg>
                <span><?php echo bkntc__('Total revenue') ?></span>
            </p>
        </div>
    </div>
    <div class="responsive-datatables">
        <div class="datatables-container table-responsive" style="margin-top: 16px">
            <table id="inventoryLogsTable" class="hover nowrap dataTable">
                <thead>
                <tr>
                    <th data-key="purchase_id"><?php echo bkntc__('ID') ?></th>
                    <th data-key="product_name"><?php echo bkntc__('Product name') ?></th>
                    <th data-key="customer_name"><?php echo bkntc__('Customer') ?></th>
                    <th data-key="service_name"><?php echo bkntc__('Service') ?></th>
                    <th data-key="purchased_at"><?php echo bkntc__('Bought date') ?></th>
                    <th data-key="amount"><?php echo bkntc__('Revenue') ?></th>
                    <th><?php echo bkntc__('Status') ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>
