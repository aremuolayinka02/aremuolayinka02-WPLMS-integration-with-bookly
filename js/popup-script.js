jQuery(document).ready(function ($) {
  console.log("Document is ready");

  // Function to determine if the current URL indicates a course view
  function isCoursePage() {
    const urlFragment = window.location.hash;
    return (
      urlFragment.includes("component=course") &&
      urlFragment.includes("action=course")
    );
  }

  // Function to show the popup
  function showPopup() {
    $.ajax({
      url: ccpData.ajaxurl,
      type: "POST",
      data: {
        action: "ccp_check_course_status",
        nonce: ccpData.nonce,
      },
      success: function (response) {
        console.log("Course status check response:", response);

        if (response.show_popup) {
          // Update popup content
          $("#popup-course-id").text(response.course_id);
          $("#popup-user-name").text(response.user_name);
          $("#popup-course-name").text(ccpData.course_name);

          // Set the appointment button URL
          const appointmentUrl =
            window.location.origin +
            ccpData.appointment_url +
            "?course_id=" +
            response.course_id;
          $("#appointment-button").attr("href", appointmentUrl);
          console.log("Setting appointment URL to:", appointmentUrl);

          // Show popup
          $("#course-popup").fadeIn();
        }
      },
      error: function (xhr, status, error) {
        console.error("Ajax error:", error);
      },
    });
  }

  // Check on page load if URL contains component=course
  if (isCoursePage()) {
    console.log("Course page detected, checking status...");
    showPopup();
  }

  // Listen for hash changes
  $(window).on("hashchange", function () {
    if (isCoursePage()) {
      console.log("URL changed to course page, checking status...");
      showPopup();
    }
  });

  // Handle appointment button click
  $("#appointment-button").on("click", function (e) {
    const appointmentUrl = window.location.origin + ccpData.appointment_url;
    console.log("Navigating to:", appointmentUrl);
    window.location.href = appointmentUrl;
  });

  // Close popup handlers
  $(".course-popup-close").click(function () {
    $("#course-popup").fadeOut();
    console.log("Close button clicked, popup should be hidden");
  });

  $(window).click(function (event) {
    if ($(event.target).is(".course-popup-overlay")) {
      $("#course-popup").fadeOut();
      console.log("Clicked outside the popup, popup should be hidden");
    }
  });
});
