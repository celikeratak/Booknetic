(($) => {
    "use strict";

    const $document = $(document);

    $document.ready(() => {
        const container = $(".inventory-container");

        container.find(".nav-tabs").find(".nav-link:first").addClass("active");
        container.find(".tab-content").find(".tab-pane:first").addClass("active");

        // click products tab if user saves product
        const currentUrl = window.location.href;
        const url = new URL(currentUrl);
        const param = url.searchParams.get('saved');
        const productTab = $('.nav-link:not(.active)');

        if (param !== null) {
            productTab.click();
            booknetic.toast(booknetic.__('changes_saved'), 'success');
            url.searchParams.delete('saved');
            window.history.pushState({}, '', url);
        }
    });
})(jQuery);
