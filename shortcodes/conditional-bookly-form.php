<?php
class CCP_Conditional_Bookly_Form_Shortcode
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Get shortcode name from settings or use default
        $shortcode_name = get_option('ccp_conditional_form_shortcode', 'conditional-bookly-form');
        add_shortcode($shortcode_name, array($this, 'render_shortcode'));
    }

    public function render_shortcode($atts)
    {
        // Get current user
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            return do_shortcode('[bookly-form service_id="1" staff_member_id="1" hide="date,week_days,time_range"]');
        }

        // Get course ID from URL
        $course_id = isset($_GET['course_id']) ? sanitize_text_field($_GET['course_id']) : '';

        // error_log(sprintf(
        //     'Conditional Form Render - User: %s, Course ID: %s',
        //     $current_user->user_login,
        //     $course_id
        // ));

        // Check appointment status
        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);

        // Get the status check shortcode name from settings
        $status_check_shortcode = get_option('ccp_shortcode_name', 'appointment-status-check');

        ob_start();

        if ($appointment_status === 'booked') {
            echo do_shortcode('[' . $status_check_shortcode . ']');
        } else {
            // Add hidden input for course ID
            echo '<input type="hidden" name="course_id" value="' . esc_attr($course_id) . '">';
            echo do_shortcode('[bookly-form service_id="1" staff_member_id="1" hide="date,week_days,time_range"]');
        }

        return ob_get_clean();
    }
}