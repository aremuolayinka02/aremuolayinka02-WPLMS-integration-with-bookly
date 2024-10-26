jQuery(document).ready(function($) {
    $('.complete-appointment-unit').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var unitId = button.data('unit-id');
        var courseId = button.data('course-id');
        
        $.ajax({
            url: appointmentUnit.ajaxurl,
            type: 'POST',
            data: {
                action: 'complete_appointment_unit',
                unit_id: unitId,
                course_id: courseId,
                nonce: appointmentUnit.nonce
            },
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    button.text(appointmentUnit.completedText);
                    // Trigger WPLMS unit completion
                    if (typeof complete_unit === 'function') {
                        complete_unit(unitId, courseId);
                    }
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert(appointmentUnit.errorMessage);
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
});