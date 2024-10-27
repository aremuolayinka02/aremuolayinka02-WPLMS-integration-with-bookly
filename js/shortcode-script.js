jQuery(document).ready(function ($) {
  function checkAppointmentStatus() {
    const container = $(".appointment-status-container");
    if (!container.length) return;

    $.ajax({
      url: ccpShortcodeData.ajaxurl,
      type: "POST",
      data: {
        action: "check_appointment_status",
        nonce: ccpShortcodeData.nonce,
      },
      success: function (response) {
        if (response.success) {
          container.html(response.data.html);

          // If status is booked, stop checking
          if (response.data.status === "booked") {
            clearInterval(statusChecker);
          }
        }
      },
    });
  }

  // Check status every 5 seconds
  const statusChecker = setInterval(checkAppointmentStatus, 5000);

  // Also check when Bookly form completes
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
      // Wait a brief moment for the status to be updated
      setTimeout(checkAppointmentStatus, 1000);
    }
  });
});
