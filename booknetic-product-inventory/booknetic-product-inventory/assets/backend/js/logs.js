(($) => {
    "use strict";

    const $document = $(document);

    $document.ready(() => {
        const table = $("#inventoryLogsTable");
        const tableHead = $("#inventoryLogsTable thead");

        const advancedFilterButton = $(".inventory-advanced-filter-btn");
        const advancedDropDown = $(".inventory-advanced-filter-dropdown");

        const inventoryResetButton = $(".inventory-filter-reset-btn");
        const inventorySaveButton = $(".inventory-filter-save-btn");

        const dateBtn = $('.inventory-log-btn');
        const dateFilter = $('#inventory-date-filter');

        const totalRevenue = $("#totalRevenue");
        const totalSold = $("#totalSold");

        const filterStaff = $("#advancedFilterStaff");
        const filterService = $("#advancedFilterService");
        const filterCustomer = $("#advancedFilterCustomer");
        const filterStatus = $("#advancedFilterStatus");

        const select2Elements = [filterStaff, filterService, filterCustomer, filterStatus];

        let currentPage = null;
        let orderBy = null;
        let sort = "";
        let displayCurrentPage = 0;
        let totalPages = null;
        let dateFilterData = {
            type: "last_30_days",
            from: "",
            to: "",
        };
        let advancedFilter = {
            product: "",
            service: "",
            customer: "",
            status: ""
        };

        // initialize select2 and close dropdown when clicking clear button
        select2Elements.forEach((select) => {
            select.select2({
                theme: "bootstrap",
                placeholder: booknetic.__("select"),
                allowClear: true,
            });

            select
                .on("select2:unselecting", function () {
                    $(this).data("unselecting", true);
                })
                .on("select2:opening", function (e) {
                    if ($(this).data("unselecting")) {
                        $(this).removeData("unselecting");
                        e.preventDefault();
                    }
                });
        });

        const dataTable = table.DataTable({
            autoWidth: false,
            responsive: true,
            paging: true,
            searching: false,
            info: true,
            order: [],
            processing: true,
            language: {
                info: "Page _PAGE_ of _PAGES_",
                processing: `<div class="lds-default"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>`,
                paginate: {
                    next: booknetic.__("Next"),
                    previous: booknetic.__("Previous"),
                },
            },
            layout: {
                bottomEnd: {
                    paging: {
                        type: "simple",
                    },
                },
            },
            columnDefs: [
                {
                    targets: 6,
                    orderable: false,
                },
                {width: '60px', targets: 0},

            ],
        });

        booknetic.initializeDateRangePicker(
            dateFilter,
            dateBtn,
            (dateFilter) => {
                dateFilterData = {...dateFilter};
                return loadTable(null, orderBy, sort, dateFilter, advancedFilter);
            }
        );

        loadTable();

        function loadTable(
            currentPage = null,
            orderBy = "",
            sort,
            dateFilter = dateFilterData,
            filter = {},
            limit = 10
        ) {
            dataTable.processing(true);
            booknetic.ajax(
                "get_logs",
                {
                    currentPage,
                    orderBy,
                    sort,
                    dateFilter,
                    filter,
                    limit,
                },
                function (result) {
                    booknetic.ajax("get_log_statistics", {dateFilter}, function (resultOfStatistics) {
                        dataTable.processing(false);
                        drawTable(result, resultOfStatistics);
                    })
                }
            );
        }

        function drawTable(result, resultOfStatistics) {
            totalPages = result.page?.meta.totalPages;
            displayCurrentPage = result.page?.meta.currentPage;

            const rowsData = result.page.data.map((res) => {
                const id = res.purchase_id;
                const purchaseDate = res.purchased_at;
                const serviceName = res.service_name;
                const revenue = res.amount;
                const productImage = res.image;
                const customerImage = res.customer_image;

                const inventoryStatusData = transformStatus(res.status);
                const productName = `<td class="d-flex align-items-center"><img src="${productImage ?? noProductImage}" alt=${res.product_name}><span>${res.product_name}</span></td>`;
                const customerName = `<td class="d-flex align-items-center"><img src="${customerImage ?? noUserImage}" alt='${res.customer_name}'><span>${res.customer_name}</span></td>`;
                const status = `
                                 <button class="status-dropdown-btn ${inventoryStatusData?.split(" ").join("-").toLowerCase()}"> 
                                     <span class="status-dropdown-text">${inventoryStatusData}</span>
                                 </button>`;

                return [id, productName, customerName, serviceName, purchaseDate, revenue, status];
            });

            const tableFooter = $(".dt-layout-row:last-child");
            const docURL = documentationURL || 'https://www.booknetic.com/documentation/';

            const helpButton = `<div class='help-btn d-flex align-items-center'><div><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"18\" height=\"18\" viewBox=\"0 0 18 18\" fill=\"none\">\n" +
                  "<path d=\"M7.5 6.00168C7.63215 5.62602 7.89298 5.30925 8.2363 5.10748C8.57962 4.90571 8.98327 4.83195 9.37576 4.89928C9.76825 4.9666 10.1243 5.17065 10.3807 5.4753C10.6372 5.77995 10.7775 6.16554 10.7769 6.56376C10.7769 7.68792 9.09069 8.25 9.09069 8.25M9.11243 10.5H9.11993M5.25 13.5V15.2516C5.25 15.6513 5.25 15.8511 5.33192 15.9537C5.40317 16.043 5.5112 16.0949 5.6254 16.0948C5.75672 16.0946 5.91275 15.9698 6.22482 15.7201L8.01391 14.2889C8.37939 13.9965 8.56213 13.8503 8.76561 13.7463C8.94615 13.6541 9.13832 13.5867 9.33691 13.5459C9.56075 13.5 9.79477 13.5 10.2628 13.5H12.15C13.4101 13.5 14.0402 13.5 14.5215 13.2548C14.9448 13.039 15.289 12.6948 15.5048 12.2715C15.75 11.7902 15.75 11.1601 15.75 9.9V5.85C15.75 4.58988 15.75 3.95982 15.5048 3.47852C15.289 3.05516 14.9448 2.71095 14.5215 2.49524C14.0402 2.25 13.4101 2.25 12.15 2.25H5.85C4.58988 2.25 3.95982 2.25 3.47852 2.49524C3.05516 2.71095 2.71095 3.05516 2.49524 3.47852C2.25 3.95982 2.25 4.58988 2.25 5.85V10.5C2.25 11.1975 2.25 11.5462 2.32667 11.8323C2.53472 12.6088 3.1412 13.2153 3.91766 13.4233C4.20378 13.5 4.55252 13.5 5.25 13.5Z\" stroke=\"#8895A0\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
                  "</svg></div><a target='_blank' href="${docURL}">${booknetic.__('Need Help?')}</a></div>`;

            tableFooter.find(".help-btn").remove();
            tableFooter.prepend(helpButton);

            dataTable.clear().rows.add(rowsData).draw();
            table.find("th:first-child").css("width", "20px !important");

            booknetic.updatePagination(
                result,
                "#inventoryLogsTable_info",
                "#inventoryLogsTable_wrapper",
            );

            if (resultOfStatistics.status === "ok") {
                const sold = resultOfStatistics.stats?.total_sold;
                const revenue = resultOfStatistics.stats?.total_revenue;
                totalSold.text(sold);
                totalRevenue.text(`$${formatValue(revenue)}`);
            }
        }

        // order table data
        tableHead.on("click", "th", function () {
            if (!$(this).is(":nth-child(7)")) {
                const inventoryKey = $(this).attr("data-key");
                const sortOrder = dataTable.order()[0][1];

                if (!sortOrder) {
                    sort = "";
                    orderBy = null;
                    return loadTable(null);
                }
                sort = sortOrder;
                orderBy = inventoryKey;
                loadTable(currentPage, orderBy, sort, dateFilterData, advancedFilter);
            }
        });

        // handle table loading indicator visibility
        dataTable.on("processing.dt", function (e, settings, processing) {
            $("#processingIndicator").css("display", "none");
        });

        dataTable.on('draw', function () {
            $("#inventoryLogsTable_info").html(booknetic.__(`Page ${displayCurrentPage} of ${totalPages}`));
        });

        // handle pagination
        $document.on(
            "click",
            "#inventoryLogsTable_wrapper .dt-paging-button.next:not(.disabled)",
            function () {
                currentPage++;
                loadTable(currentPage, orderBy, sort, dateFilterData, advancedFilter);
            }
        );

        $document.on(
            "click",
            "#inventoryLogsTable_wrapper .dt-paging-button.previous:not(.disabled)",
            function () {
                currentPage--;
                loadTable(currentPage, orderBy, sort, dateFilterData, advancedFilter);
            }
        ).on("click", "#exportLogs", function () {
            window.open(restBaseUrl + 'logs-csv/', '_blank');
        });

        // handle advanced filter
        advancedFilterButton.on("click", function (event) {
            advancedDropDown.fadeIn(200);
            table.find(".status-dropdown-menu").fadeOut(200);
            event.stopPropagation();
        });

        advancedDropDown.on("click", function (event) {
            event.stopPropagation();
        });

        $document.click(function () {
            table.find(".status-dropdown-menu").fadeOut(200);
            advancedDropDown.fadeOut(200);
        });

        // advanced filter action
        inventorySaveButton.on("click", function () {
            advancedFilter = {
                staff: filterStaff.val(),
                service: filterService.val(),
                customer: filterCustomer.val(),
                status: filterStatus.val()
            };
            loadTable(null, orderBy, sort, dateFilterData, advancedFilter);
            advancedDropDown.fadeOut(200);
        });

        inventoryResetButton.on("click", function () {
            filterStaff.val("").trigger("change.select2");
            filterService.val("").trigger("change.select2");
            filterCustomer.val("").trigger("change.select2");
            filterStatus.val("").trigger("change.select2");
        });

        // helpers
        function formatValue(value) {
            if (value == null) {
                value = 0;
            }
            return parseFloat(value).toFixed(2);
        }

        function transformStatus(status) {
            const map = {
                'not_paid': booknetic.__('Not Paid'),
                'canceled': booknetic.__('Canceled'),
                'pending': booknetic.__('Pending'),
                "paid": booknetic.__('Paid')
            };
            return map[status?.toLowerCase()] || booknetic.__(status);
        }

    });
})(jQuery);
