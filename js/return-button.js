jQuery(document).ready(function ($) {
  function checkAppointmentStatus() {
    const container = $(".return-button-container");
    if (!container.length) return;

    $.ajax({
      url: ccpReturnData.ajaxurl,
      type: "POST",
      data: {
        action: "check_return_button_status",
        nonce: ccpReturnData.nonce,
      },
      success: function (response) {
        console.log("Return button status check:", response);
        if (response.success && response.data.status === "booked") {
          container.html(response.data.html);
          container.find(".return-button-wrap").fadeIn();
        }
      },
      error: function (xhr, status, error) {
        console.error("Return button check error:", error);
      },
    });
  }

  // Check status when Bookly form completes
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
      console.log(
        "Bookly appointment completed, checking return button status..."
      );
      setTimeout(checkAppointmentStatus, 1000);
    }
  });

  // Initial check
  if ($(".return-button-container").length) {
    checkAppointmentStatus();
  }
});
