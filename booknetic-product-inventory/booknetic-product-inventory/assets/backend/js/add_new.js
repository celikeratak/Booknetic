(($) => {
    "use strict";

    const $document = $(document);

    $document.ready(() => {
        const inventoryUploadButton = $('.inventory-upload-btn');
        const inventoryRemoveButton = $(".inventory-remove-btn");
        const imageInput = $("#uploadInventoryProductImage");
        const selectedProductImage = $("#selectedProductImage");
        const selectServices = $(".fs-modal #inventorySelectService");
        const modal = $(".fs-modal");
        let image = modal.find(".inventory-modal").attr("data-product-image");
        booknetic.select2Ajax(selectServices, 'appointments.get_services', {}, null, true);

        booknetic.initMultilangInput($("#inventoryProductName"), 'inventory', 'name');
        booknetic.initMultilangInput($("#inventoryNote"), 'inventory', 'note');

        modal.on('click', '#save_product', function () {
            const name = $('#inventoryProductName').val();
            const quantity = $('#inventoryProductQuantity').val();
            const purchasePrice = $('#inventoryProductPrice').val();
            const sellPrice = $('#inventoryProductSellPrice').val();
            const productId = $(this).closest(".inventory-modal").attr("data-product-id");
            const imageData = imageInput[0].files[0];

            // Validation
            if (!name || !quantity || isNaN(quantity) || quantity <= 0 ||
                !purchasePrice || isNaN(purchasePrice) || purchasePrice <= 0 ||
                !sellPrice || isNaN(sellPrice) || sellPrice <= 0) {
                booknetic.toast(booknetic.__('Please fill in all required fields correctly!'), 'unsuccess');
                return;
            }

            const params = new FormData();

            params.append('id', productId);
            params.append('name', name);
            params.append('quantity', quantity);
            params.append('purchasePrice', purchasePrice);
            params.append('sellPrice', sellPrice);
            params.append('services', selectServices.val());
            params.append('disableSelect', $('#disableSelect').is(':checked') ? 1 : 0);
            params.append('note', $('#inventoryNote').val());
            params.append("image", imageData ?? image);

            if (!(imageData ?? image)) {
                booknetic.ajax('remove_image', {id: productId}, () => {});
            }

            booknetic.ajax('save', params, () => {
                location.href = 'admin.php?page=' + BACKEND_SLUG + '&module=inventory&saved';
            });
        });

        // hande image upload
        inventoryUploadButton.on("click", function () {
            imageInput.trigger("click");
        })

        inventoryRemoveButton.on("click", function () {
            selectedProductImage.attr("src", noProductImage);
            image = null;

            $(this).addClass('disabled');

            imageInput.val("");
        })

        imageInput.on("change", function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    inventoryRemoveButton.removeClass('disabled');
                    selectedProductImage.attr("src", e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        })
    })
})(jQuery);
