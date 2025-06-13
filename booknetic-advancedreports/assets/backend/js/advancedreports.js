(function($) {
  "use strict";

  $(document).ready(function() {
    let table = $("#myTable").DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: booknetic.ajaxUrl + "?module=advancedreports&action=AdvancedReports.getAppointmentsData",
        type: "POST",
        data: function(d) {
          d.module = "advancedreports"; 
          d.flg = "getAppointmentsData";

          d.status  = $("#statusFilter").val();
          d.payment = $("#paymentFilter").val();
          d.staff   = $("#staffFilter").val();
        },
        dataSrc: function(json) {
          if(!json.status) {
            console.log("ERROR", json.errorMsg);
            return [];
          }
          return json.response.data || [];
        }
      },
      columns: [
        { data: "id", title: "ID" },
        { data: "staff_id", title: "Staff ID" },
        { data: "status", title: "Status" },
        { data: "payment_method", title: "Payment Method" },
        { data: "paid_amount", title: "Paid Amount" },
        {
          data: "created_at", 
          title: "Created",
          render: function(value, type, row) {
            if(!value) return "";
            let dateObj = new Date(value * 1000);
            return dateObj.toLocaleString();
          }
        }
      ],
      pageLength: 10
    });

    $("#applyFiltersBtn").on("click", function() {
      table.ajax.reload();
    });
  });
})(jQuery);
