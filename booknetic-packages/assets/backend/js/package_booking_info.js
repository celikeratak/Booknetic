(function ($) {
    "use strict";
    const $document = $(document);

    $document.ready(function ()
    {
        // handle package info visibility
        const modal = $('.package-modal').closest(".fs-modal");
        const accordion = modal.find(".package-info-accordion");

        accordion.on("click", function () {
            $(".package-accordion-content").slideToggle(200);
            $(".accordion-icon").toggleClass("rotate");
        });

        // handle more menu visibility
        modal.on("click", ".package-more-menu-btn", function () {
            modal.find(".package-more-menu").hide();
            const dropdown = $(this).parent().find(".package-more-menu")
            dropdown.toggle();
        });

        $(document).on("click", function (e) {
            if (e.target.tagName.toLowerCase() !== "img") {
                modal.find(".package-more-menu").hide();
            }
            e.stopPropagation();
        });

        // delete appointment
        modal.on("click", ".delete-package-btn", function () {
            const ID = $(this).attr("data-delete-id");
            booknetic.confirm(
                `${booknetic.__("are_you_sure_want_to_delete")}`,
                "danger", `trash`,
                () => {
                    $.post(String(location.href).replace('package_bookings', 'appointments'), {
                        'ids': [ID],
                        'fs-data-table-action': 'delete'
                    }, function ( result )
                    {
                        booknetic.loading(0);

                        if( booknetic.ajaxResultCheck( result ) )
                        {
                            booknetic.toast( booknetic.__('Deleted'), 'success', 2000 );
                            booknetic.reloadModal( modal.attr('id').replace('FSModal', '') );
                            booknetic.dataTable.reload( $("#fs_data_table_div") );
                        }
                    });
                },
                `${booknetic.__("DELETE")}`,
                `${booknetic.__("CANCEL")}`);
        });
    });

})(jQuery);