jQuery(document).ready(function ($) {
  function checkAppointmentStatus() {
    const container = $(".return-button-container");
    if (!container.length) return;

    $.ajax({
      url: ccpData.ajaxurl,
      type: "POST",
      data: {
        action: "check_return_button_status",
        nonce: ccpData.nonce,
      },
      success: function (response) {
        if (response.success) {
          container.html(response.data.html);
          if (response.data.status === "booked") {
            container.find(".return-button-wrap").fadeIn();
          }
        }
      },
    });
  }

  // Check status when Bookly form completes
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
      setTimeout(checkAppointmentStatus, 1000);
    }
  });

  // Check status periodically
  setInterval(checkAppointmentStatus, 5000);
});
