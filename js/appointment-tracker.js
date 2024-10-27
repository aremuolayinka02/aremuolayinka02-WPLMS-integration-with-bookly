jQuery(document).ready(function ($) {
//   console.log("Appointment tracker initialized");

  // Function to get URL parameters
  function getUrlParameter(name) {
    name = name.replace(/[$]/, "$").replace(/[$]/, "$");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  // Get course ID from URL and saved data
  const urlCourseId =
    getUrlParameter("course_id") || ccpTrackerData.saved_course_id;
//   console.log("Course ID to track:", urlCourseId);

  // Track Bookly form submission
  $(document).ajaxSend(function (event, jqxhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
    //   console.log("Bookly appointment save detected");

      // Parse existing data
      let formData = new FormData();
      let existingData = new URLSearchParams(settings.data);

      // Add existing data to FormData
      for (let pair of existingData.entries()) {
        formData.append(pair[0], pair[1]);
      }

      // Add course_id
      formData.append("course_id", urlCourseId);

      // Convert FormData back to URL encoded string
      settings.data = new URLSearchParams(formData).toString();

    //   console.log("Modified form data:", settings.data);
    }
  });

  // Track successful appointment creation
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
    //   console.log("Bookly appointment save completed");

      try {
        const response = JSON.parse(xhr.responseText);
        if (response.success) {
        //   console.log("Appointment created successfully");
          // Reload page after brief delay to show updated status
          setTimeout(function () {
            location.reload();
          }, 1000);
        }
      } catch (e) {
        // console.error("Error parsing response:", e);
      }
    }
  });
});
