jQuery(document).ready(function ($) {
  console.log("Appointment tracker initialized");

  // Function to get URL parameters
  function getUrlParameter(name) {
    name = name.replace(/[$]/, "$").replace(/[$]/, "$");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  // Get course ID from URL
  const urlCourseId = getUrlParameter("course_id");
  const savedCourseId = ccpTrackerData.saved_course_id;

  console.log("URL Course ID:", urlCourseId);
  console.log("Saved Course ID:", savedCourseId);

  // Track Bookly form submission
  $(document).ajaxSend(function (event, jqxhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
      console.log("Bookly appointment save detected");

      // Append course_id to the form data
      let formData = new URLSearchParams(settings.data);
      formData.append("course_id", urlCourseId);
      settings.data = formData.toString();

      console.log("Modified form data:", settings.data);
      console.log("Course ID verification:", {
        urlCourseId: urlCourseId,
        savedCourseId: savedCourseId,
        matches: urlCourseId === savedCourseId,
      });
    }
  });

  // Track successful appointment creation
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings.data &&
      settings.data.indexOf("action=bookly_save_appointment") !== -1
    ) {
      console.log("Bookly appointment save completed");

      try {
        const response = JSON.parse(xhr.responseText);
        if (response.success) {
          console.log("Appointment created successfully");

          // Add return button
          const dashboardUrl =
            window.location.origin +
            "/members-directory/" +
            response.user_login +
            "/#component=course";
          const returnButton = `
                        <div class="return-to-dashboard" style="text-align: center; margin-top: 20px;">
                            <a href="${dashboardUrl}" 
                               class="return-button" 
                               style="display: inline-block; padding: 10px 20px; background: #2434e5; color: white; text-decoration: none; border-radius: 4px; font-weight: 600;">
                                Return to Dashboard
                            </a>
                        </div>
                    `;

          $(".bookly-form").append(returnButton);
        }
      } catch (e) {
        console.error("Error parsing response:", e);
      }
    }
  });
});
