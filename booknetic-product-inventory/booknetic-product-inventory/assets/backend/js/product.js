(($) => {
    "use strict";

    const $document = $(document);

    $document.ready(() => {
        const table = $("#inventoryProductTable");
        const tableHead = $("#inventoryProductTable thead");
        const createProductButton = $(".create-product-button");
        const bulkActionContainer = $(".bulk-action");
        const bulkActionCheckbox = $('#bulkActionCheckbox');
        const showBulkActionCheckbox = $("#showBulkAction");
        const selectedProductCount = $(".selected-product-count");
        const deleteProductsButton = $(".delete-products-btn");

        let currentPage = null;
        let displayCurrentPage = 0;
        let orderBy = null;
        let sort = "";
        let totalProductCount = 0;
        let totalPages = null;
        const selectedProducts = [];

        // base table config
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
                    targets: 0,
                    orderable: false,
                },
                {width: '10px', targets: 0},
                {width: "60px", targets: 1},
                {width: "200px", targets: 6},
            ],
        });

        loadTable();

        function loadTable(
            currentPage = null,
            orderBy = "",
            sort = null,
            limit = 10
        ) {
            dataTable.processing(true);
            booknetic.ajax(
                "get_products",
                {
                    currentPage,
                    orderBy,
                    sort,
                    limit,
                },
                function (result) {
                    dataTable.processing(false);
                    drawTable(result);
                }
            );
        }

        function drawTable(result) {
            totalProductCount = result.page?.data.length;
            totalPages = result.page?.meta.totalPages;
            displayCurrentPage = result.page?.meta.currentPage;

            const rowsData = result.page?.data.map((res) => {
                const productId = res.id;
                const productName = res.name;
                const createdAt = res.created_at;
                const purchasePrice = formatValue(res.purchase_price);
                const sellPrice = formatValue(res.sell_price);
                const quantity = res.quantity;
                const isItemSelected = `<input class="inventory-product-checkbox" data-product-id="${productId}" type="checkbox">`;
                const productContent = `<td class="d-flex align-items-center"><img src=${res.image ? productPhotoPath + res.image : noProductImage} alt="${productName}"><span>${productName}</span></td>`;
                const quantityColumn = `
                            <div class="inventory-quantity-wrapper d-flex align-items-center justify-content-between" data-product-id="${productId}" data-quantity="${quantity}">
                                <div class="inventory-quantity-content d-flex justify-content-between align-items-center">
                                    <p class="inventory-product-quantity">${Number(quantity).toLocaleString("en-US")}</p>
                                    <button class="create-product-button quantity-add-product">+</button>
                                </div>
                                <div class="inventory-more-menu-container">
                                   <button class="inventory-more-menu-btn">
                                       <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="dots-vertical">
                                                <g id="Icon">
                                                    <path d="M10.0001 10.8334C10.4603 10.8334 10.8334 10.4603 10.8334 10C10.8334 9.5398 10.4603 9.16671 10.0001 9.16671C9.53984 9.16671 9.16675 9.5398 9.16675 10C9.16675 10.4603 9.53984 10.8334 10.0001 10.8334Z" stroke="#8895A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10.0001 5.00004C10.4603 5.00004 10.8334 4.62694 10.8334 4.16671C10.8334 3.70647 10.4603 3.33337 10.0001 3.33337C9.53984 3.33337 9.16675 3.70647 9.16675 4.16671C9.16675 4.62694 9.53984 5.00004 10.0001 5.00004Z" stroke="#8895A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10.0001 16.6667C10.4603 16.6667 10.8334 16.2936 10.8334 15.8334C10.8334 15.3731 10.4603 15 10.0001 15C9.53984 15 9.16675 15.3731 9.16675 15.8334C9.16675 16.2936 9.53984 16.6667 10.0001 16.6667Z" stroke="#8895A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </g>
                                            </g>
                                       </svg>
                                    </button>
                                    <ul class="inventory-more-menu">
                                        <li class="edit-inventory-btn"><img src="${inventoryAssetsURL}assets/backend/icons/edit.svg" alt=""><span>Edit</span></li>  
                                        <li class="delete-inventory-btn"><img src="${assetsUrl}/icons/delete-icon.svg" alt=""><span>Delete</span></li>      
                                    </ul>
                                </div>
                            </div>`;

                return [
                    isItemSelected,
                    productId,
                    productContent,
                    `$${purchasePrice}`,
                    `$${sellPrice}`,
                    createdAt,
                    quantityColumn
                ];
            });

            const tableFooter = $(".dt-layout-row:last-child");
            const docURL = documentationURL || 'https://www.booknetic.com/documentation/';

            const helpButton = `<div class='help-btn d-flex align-items-center'><div><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"18\" height=\"18\" viewBox=\"0 0 18 18\" fill=\"none\">\n" +
                  "<path d=\"M7.5 6.00168C7.63215 5.62602 7.89298 5.30925 8.2363 5.10748C8.57962 4.90571 8.98327 4.83195 9.37576 4.89928C9.76825 4.9666 10.1243 5.17065 10.3807 5.4753C10.6372 5.77995 10.7775 6.16554 10.7769 6.56376C10.7769 7.68792 9.09069 8.25 9.09069 8.25M9.11243 10.5H9.11993M5.25 13.5V15.2516C5.25 15.6513 5.25 15.8511 5.33192 15.9537C5.40317 16.043 5.5112 16.0949 5.6254 16.0948C5.75672 16.0946 5.91275 15.9698 6.22482 15.7201L8.01391 14.2889C8.37939 13.9965 8.56213 13.8503 8.76561 13.7463C8.94615 13.6541 9.13832 13.5867 9.33691 13.5459C9.56075 13.5 9.79477 13.5 10.2628 13.5H12.15C13.4101 13.5 14.0402 13.5 14.5215 13.2548C14.9448 13.039 15.289 12.6948 15.5048 12.2715C15.75 11.7902 15.75 11.1601 15.75 9.9V5.85C15.75 4.58988 15.75 3.95982 15.5048 3.47852C15.289 3.05516 14.9448 2.71095 14.5215 2.49524C14.0402 2.25 13.4101 2.25 12.15 2.25H5.85C4.58988 2.25 3.95982 2.25 3.47852 2.49524C3.05516 2.71095 2.71095 3.05516 2.49524 3.47852C2.25 3.95982 2.25 4.58988 2.25 5.85V10.5C2.25 11.1975 2.25 11.5462 2.32667 11.8323C2.53472 12.6088 3.1412 13.2153 3.91766 13.4233C4.20378 13.5 4.55252 13.5 5.25 13.5Z\" stroke=\"#8895A0\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
                  "</svg></div><a target='_blank' href="${docURL}">${booknetic.__('Need Help?')}</a></div>`;

            tableFooter.find(".help-btn").remove();
            tableFooter.prepend(helpButton)

            dataTable.clear().rows.add(rowsData).draw();

            booknetic.updatePagination(
                result,
                "#inventoryProductTable_info",
                "#inventoryProductTable_wrapper",
            );
        }

        // order table data
        tableHead.on("click", "th", function () {
            const tableColumn = $(this).attr("data-dt-column");
            if (tableColumn !== "0") {
                const inventoryKey = $(this).attr("data-key");
                const sortOrder = dataTable.order()[0][1];
                if (!sortOrder) {
                    sort = "";
                    orderBy = null;
                    return loadTable(null);
                }
                sort = sortOrder;
                orderBy = inventoryKey;
                loadTable(currentPage, orderBy, sort);
            }
        });

        dataTable.on('draw', function () {
            $("#inventoryProductTable_info").html(booknetic.__(`Page ${displayCurrentPage} of ${totalPages}`));
        });

        // handle table loading indicator visibility
        dataTable.on("processing.dt", function (e, settings, processing) {
            $("#processingIndicator").css("display", "none");
        });

        // disable more menu when user clicks outside of container
        $document.on("click", function (e) {
            const isMenuVisible = $('.inventory-more-menu:visible')
            if (isMenuVisible) {
                table.find(".inventory-more-menu").fadeOut(200);
                table.closest(".datatables-container").css('min-height', '');
            }
            e.stopPropagation();
        });

        // handle pagination
        $document.on(
            "click",
            "#inventoryProductTable_wrapper .dt-paging-button.next:not(.disabled)",
            function () {
                currentPage++
                loadTable(currentPage, orderBy, sort);
                cleanBulkAction()
            }
        );

        $document.on(
            "click",
            "#inventoryProductTable_wrapper .dt-paging-button.previous:not(.disabled)",
            function () {
                currentPage--
                loadTable(currentPage, orderBy, sort);
                cleanBulkAction()
            }
        );

        // set product quantity
        table.on("click", '.quantity-add-product', function () {
            const quantity = $(this).closest(".inventory-quantity-wrapper").attr("data-quantity");
            const productId = $(this).closest(".inventory-quantity-wrapper").attr("data-product-id");

            const modalContent =
                `<div class="inventory-quantity-confirm-modal" data-product-id="${productId}">
                    <header class="inventory-quantity-confirm-modal__header d-flex justify-content-between align-items-center">
                        <p>${booknetic.__("Set product quantity")}</p>
                        <svg data-dismiss="modal" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="#8895A0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </header> 
                    <div class="inventory-quantity-body d-flex flex-column">
                        <label for="exampleFormControlInput1">${booknetic.__("Quantity")}</label>
                        <input type="number" class="form-control set-product-quantity" id="setInventoryQuantity" placeholder="0" min="0" value="${quantity}">
                        <span>${booknetic.__("Set your product quantity")}</span>
                    </div>
                    <footer class="d-flex justify-content-end align-items-center">
                        <button data-dismiss="modal" class="inventory-cancel-btn">${booknetic.__("Cancel")}</button>
                        <button data-dismiss="modal" class="inventory-save-btn">${booknetic.__("Save")}</button>            
                    </footer>
                 </div>`;
            const [id, count, modalSelector] = booknetic.modal(modalContent, {type: "center", width: "600px"});
        });

        $document.on("click", ".inventory-save-btn", function () {
            const $element = $(this);
            const productId = $element.closest(".inventory-quantity-confirm-modal").attr("data-product-id");
            const quantity = $element.parent().prev().find("input").val();

            booknetic.ajax("set_quantity", {id: productId, quantity}, function (result) {
                if (result.status === "ok") {
                    booknetic.toast(booknetic.__('Quantity changes successfully'), 'success');
                    loadTable(currentPage, orderBy, sort)
                }
            });
        });

        // open more menu
        $document.on("click", ".inventory-more-menu-btn", function (event) {
            const currentMenu = $(this).parent().find(".inventory-more-menu");
            const allButtons = $(".inventory-more-menu-btn");
            const isLastButton = $(this).is(allButtons.last());
            const extraHeight = 30;
            const tableContainer = currentMenu.closest(".datatables-container");

            // Reset all container heights first
            tableContainer.css('min-height', '');

            // Close other menus
            $document.find(".inventory-more-menu").not(currentMenu).fadeOut(200);

            // Toggle current menu
            currentMenu.fadeToggle(0, function() {
                if (isLastButton && $(this).is(":visible")) {
                    tableContainer.css({
                        'min-height': tableContainer.height() + extraHeight + 'px'
                    });
                }
            });

            event.stopPropagation();
        });

        // delete inventory product
        $document.on("click", ".delete-inventory-btn", function () {
            const productId = $(this).closest(".inventory-quantity-wrapper").attr("data-product-id");
            booknetic.confirm(
                `${booknetic.__("Are you sure, you want to delete this item?")}`,
                "danger", `delete-icon`,
                () => booknetic.ajax("remove_products", {ids: [productId]}, () => {
                    booknetic.toast(booknetic.__('Product deleted successfully'), "success");
                    loadTable(currentPage, orderBy, sort);
                }),
                `${booknetic.__("DELETE")}`,
                `${booknetic.__("CANCEL")}`);
        });

        // handle bulk action visibility
        table.on("change", ".inventory-product-checkbox", function (event) {
            const productId = $(this).attr("data-product-id");
            const index = selectedProducts.indexOf(productId);
            (index !== -1) ? selectedProducts.splice(index, 1) : selectedProducts.push(productId);
            selectedProductCount.text(selectedProducts.length);

            if (selectedProducts.length > 0) {
                bulkActionContainer.fadeIn(200);
                tableHead.addClass("hide-element");
            } else {
                bulkActionContainer.fadeOut(200);
                tableHead.removeClass("hide-element");
            }

            bulkActionCheckbox.prop("checked", selectedProducts.length === totalProductCount)
        });

        showBulkActionCheckbox.on("change", function () {
            const $checkbox = $(this);

            bulkActionContainer.fadeIn(200);
            tableHead.addClass("hide-element");

            $checkbox.prop("checked", false);
            bulkActionCheckbox.prop("checked", true);

            const $productCheckboxes = table.find(".inventory-product-checkbox");
            selectedProducts.length = 0;

            $productCheckboxes.each(function () {
                const $productCheckbox = $(this);
                const productId = $productCheckbox.attr("data-product-id");
                $productCheckbox.prop("checked", true);
                selectedProducts.push(productId);
            });

            selectedProductCount.text(selectedProducts.length);
        })

        // handle product checkboxes
        bulkActionCheckbox.on("change", (function () {
            const isChecked = $(this).is(':checked');
            selectedProducts.length = 0;

            const $productCheckboxes = table.find(".inventory-product-checkbox");

            if (isChecked) {
                $productCheckboxes.each(function () {
                    const productId = $(this).attr("data-product-id");
                    $(this).prop("checked", true);
                    selectedProducts.push(productId);
                });
            } else {
                $productCheckboxes.prop("checked", false);
                cleanBulkAction()
            }

            selectedProductCount.text(selectedProducts.length);
        }));

        // create product
        createProductButton.on("click", function () {
            booknetic.loadModal("add_new", {});
        });

        // edit product
        table.on("click", ".edit-inventory-btn", function () {
            const productId = $(this).closest('.inventory-quantity-wrapper').attr("data-product-id");
            booknetic.loadModal('add_new', {'id': productId});
        });

        // bulk delete action
        deleteProductsButton.on('click', function () {
            booknetic.confirm(
                booknetic.__("Are you sure, you want to delete this items?"),
                "danger",
                `delete-icon`,
                () =>
                    booknetic.ajax("remove_products", {ids: selectedProducts}, () => {
                        cleanBulkAction();
                        loadTable(currentPage, orderBy, sort);
                    }), booknetic.__("DELETE"), booknetic.__("CANCEL"), cleanBulkAction);
        });

        // HELPERS
        function formatValue(value) {
            if (!value) {
                return 0;
            }
            return parseFloat(value).toFixed(2);
        }

        // hide bulk action
        function cleanBulkAction() {
            selectedProducts.length = 0;
            bulkActionContainer.fadeOut(200);
            tableHead.removeClass("hide-element");

            const $productCheckboxes = table.find(".inventory-product-checkbox");

            $productCheckboxes.each(function () {
                const $productCheckbox = $(this);
                const productId = $productCheckbox.attr("data-product-id");
                $productCheckbox.prop("checked", false);
            });
        }
    });
})(jQuery);
