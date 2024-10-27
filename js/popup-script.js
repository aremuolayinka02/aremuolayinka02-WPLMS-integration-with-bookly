jQuery(document).ready(function ($) {
  console.log("Document is ready");

  // Function to determine if the current URL indicates a course view
  function isCoursePage() {
    const urlFragment = window.location.hash; // Get the hash part of the URL
    return (
      urlFragment.includes("component=course") &&
      urlFragment.includes("action=course")
    );
  }

  // Function to show the popup
  function showPopup() {
    // Make the API call
    $.ajax({
      type: "GET",
      url: "/wp-admin/admin-ajax.php?action=wp_ajax_course_click",
      data: {
        course_id: ccpData.currentId,
      },
      success: function (data) {
        // Show popup when the course page is confirmed
        $("#course-popup").fadeIn();
        console.log("Popup should be visible now");
      },
    });
  }

  // Check if the page is a course page on initial load
  if (isCoursePage()) {
    showPopup();
  }

  // Listen for hash changes in the URL to detect navigation
  $(window).on("hashchange", function () {
    if (isCoursePage()) {
      showPopup();
    }
  });

  // Close popup when clicking the close button
  $(".course-popup-close").click(function () {
    $("#course-popup").fadeOut();
    console.log("Close button clicked, popup should be hidden");
  });

  // Close popup when clicking outside
  $(window).click(function (event) {
    if ($(event.target).is(".course-popup-overlay")) {
      $("#course-popup").fadeOut();
      console.log("Clicked outside the popup, popup should be hidden");
    }
  });
});
