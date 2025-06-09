(function ($) {
    "use strict";

    const $document = $(document);

    $document.ready(() => {
        const modal = $('.fs-modal');

        const packageUploadButton = $(".package-upload-btn");
        const packageRemoveButton = $(".package-remove-btn");

        const imageInput = $("#uploadPackageImage");
        const selectedPackageImage = $("#selectedPackageImage");

        const packageName = $("#packageName");
        const packageDescription = $("#packageDesc");
        const paymentMethods = $("#packagePaymentMethods");
        const hasExpiration = $("#packageHasExpiration");
        const expiration = $("#packageExpiration");
        const expirationValue = $(".expiration-input");
        const isPackagePrivate = $("#isPackagePrivate");
        const packageServices = $('#packageServices');
        const servicesContainer = $(".services");
        const expirationContainer = $('.expiration-select-container');
        const priceContainer = $(".package-price-container");
        const totalPackagePrice = $('#totalPackagePrice');

        let selectedPackageServices = JSON.parse($('.add-service-container').attr('data-detailed-services-json')) || [];
        let image = $(".package-details-container").attr("data-package-image");
        const packageId = modal.find(".package-details-container").attr("data-package-id");

        // init select2 on modal
        paymentMethods.select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select a payment method'),
            allowClear: false,
        });
        expiration.select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('select'),
            allowClear: false,
        });
        packageServices.select2({
            theme: 'bootstrap',
            placeholder: booknetic.__('Select a service'),
            allowClear: false
        });

        // set service when user selects
        packageServices.on('select2:select', function (e) {
            const serviceID = packageServices.val();
            const selectedOption = $(e.target).find('option[value="' + serviceID + '"]');
            const price = selectedOption.attr('data-price');
            const serviceName = booknetic.htmlspecialchars(selectedOption.text());

            const packageService = `
                        <div class="package-service" data-id="${serviceID}" data-price="${price}">
                            <div class="package-service-header d-flex align-items-center justify-content-between">
                                <h2 class="package-service-name m-0">${serviceName}</h2>
                                <button id="deletePackageService">
                                    <img src="${deleteIcon}" alt="">
                                </button>
                            </div>
                            <p class="d-flex align-items-center gap-6">
                                <span class="number-of-appointments">${booknetic.__('Number of appointments')}</span>
                                <i class="fa fa-info-circle help-icon do_tooltip" data-content="${booknetic.__('Set available appointment count for  the service.')}" data-original-title="" title=""></i>
                            </p>
                            <div class="number-appointments d-flex align-items-center justify-content-between">
                                <button class="decrement amount-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M4.16675 10H15.8334" stroke="#14151A" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <input type="text" min="0" value="1" class="flex-1 number-of-appointments-input">
                                <button class="increment amount-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <path d="M10.0001 4.1665V15.8332M4.16675 9.99984H15.8334" stroke="#14151A"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>`;

            const selectedService = {
                id: serviceID,
                price,
                count: 1,
                name: serviceName.trim()
            };

            selectedPackageServices.push(selectedService);
            servicesContainer.append(packageService);

            renderPrice();

            packageServices.find('option[value="' + serviceID + '"]').prop("disabled", true);
            packageServices.val('').trigger('change');
        });

        function renderPrice() {
            priceContainer.show();
            priceContainer.empty();
            let totalPrice = 0;

            const servicePriceUI = selectedPackageServices.map(selectedService => {
                let lastPrice = selectedService.count * selectedService.price;
                totalPrice += lastPrice;
                return `
                <div class="d-flex gap-6 align-items-center justify-content-between">
                    <p class="product-name">${selectedService.name} <span class="appointment-amount">[x${selectedService.count}]</span></p>
                    <div class="line"></div>
                    <p class="product-price">${booknetic.priceAndNumberFormatter.formatPrice(lastPrice)}</p>
                </div>`;
            });

            priceContainer.append(servicePriceUI);
            totalPackagePrice.val(booknetic.priceAndNumberFormatter.formatPrice(totalPrice));

            if (selectedPackageServices.length === 0) {
                priceContainer.hide();
            }
        }

        // set multilanguage input for name and description
        booknetic.initMultilangInput(packageName, 'packages', 'name');
        booknetic.initMultilangInput(packageDescription, 'packages', 'notes');

        // modal save action
        modal.on('click', '#addPackageBookingSave', function () {
            const imageData = imageInput[0].files[0];
            const serviceDetails = selectedPackageServices.map(service => ({
                id: service.id,
                count: service.count
            }));

            // Validation
            if (totalPackagePrice.data('value') < 0 || (hasExpiration.is(":checked") && expirationValue.val() <= 0) || !packageName.val() || paymentMethods.val().length === 0 || serviceDetails.length === 0) {
                booknetic.toast(booknetic.__('Please fill in all required fields correctly!'), 'unsuccess');
                return;
            }

            const params = new FormData();

            params.append('id', packageId);
            params.append('name', packageName.val());
            params.append('duration', hasExpiration.is(":checked") ? expiration.val() : 0);
            params.append('duration_value', hasExpiration.is(":checked") ? expirationValue.val() : 0);
            params.append('is_public', isPackagePrivate.is(':checked') ? 0 : 1);
            params.append("payment_methods", JSON.stringify(paymentMethods.val()));
            params.append("notes", packageDescription.val());
            params.append("services", JSON.stringify(serviceDetails));
            params.append("price", totalPackagePrice.data('value'));

            if (!(imageData ?? image)) {
                params.append("remove_image", 1);
            } else {
                params.append("image", imageData ?? image);
            }

            booknetic.ajax('packages.save', params, () => {
                booknetic.toast(booknetic.__('changes_saved'), 'success');
                booknetic.modalHide($(".fs-modal"));
                booknetic.dataTable.reload($("#fs_data_table_div"));
            });
        });

        // hande image upload
        packageUploadButton.on("click", function (e) {
            imageInput.trigger("click");
            e.preventDefault();
        });

        packageRemoveButton.on("click", function (e) {
            selectedPackageImage.attr("src", noProductImage);
            image = null;
            $(this).addClass('disabled');
            imageInput.val("");
            e.preventDefault();
        });

        imageInput.on("change", function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    packageRemoveButton.removeClass('disabled');
                    selectedPackageImage.attr("src", e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // handle service delete
        modal.on('click', '#deletePackageService', function (e) {
            const serviceContainer = $(this).closest(".package-service");
            const serviceID = serviceContainer.attr("data-id");
            serviceContainer.remove();
            selectedPackageServices = selectedPackageServices.filter(service => Number(service.id) !== Number(serviceID));
            packageServices.find('option[value="' + serviceID + '"]').prop("disabled", false);
            renderPrice();
            e.preventDefault();
        });

        // handle expiration checkbox
        hasExpiration.on("change", function () {
            expirationContainer.toggleClass("hidden")
        });

        // handle count of service
        modal.on("click", ".amount-btn", function (e) {
            e.preventDefault();

            const serviceID = $(this).closest(".package-service").attr("data-id");
            let amount = $(this).parent().find("input").val();

            if ($(this).hasClass("increment")) {
                amount++
            } else if (Number(amount) !== 0) {
                amount--
            }

            $(this).parent().find("input").val(amount);
            const service = selectedPackageServices.find(service => Number(service.id) === Number(serviceID));
            if (service) {
                service.count = amount;
            }
            renderPrice();
        });

        // validate number of appointments input
        modal.on('change', '.number-of-appointments-input', function (e){
            let value = e.target.value;
            const regex = /^[0-9]\d*$/;
            if (!regex.test(value)) {
                value = 1
                e.target.value = value;
            }

            const serviceID = $(this).closest(".package-service").attr("data-id");
            const service = selectedPackageServices.find(service => Number(service.id) === Number(serviceID));
            if (service) {
                service.count = value;
            }
            renderPrice();
        });
    });

})(jQuery);