<?php
class CCP_Appointment_Tracker
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
        add_action('wp_ajax_bookly_save_appointment', array($this, 'track_appointment_creation'), 1);
        add_action('wp_ajax_nopriv_bookly_save_appointment', array($this, 'track_appointment_creation'), 1);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_tracker_script'));
    }

    public function enqueue_tracker_script()
    {
        if (
            has_shortcode(get_post()->post_content, 'bookly-form') ||
            has_shortcode(get_post()->post_content, get_option('ccp_conditional_form_shortcode', 'conditional-bookly-form'))
        ) {

            wp_enqueue_script(
                'ccp-appointment-tracker',
                plugins_url('js/appointment-tracker.js', dirname(__FILE__)),
                array('jquery'),
                null,
                true
            );

            wp_localize_script('ccp-appointment-tracker', 'ccpTrackerData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ccp_track_appointment'),
                'saved_course_id' => get_option('ccp_course_id')
            ));
        }
    }

    public function track_appointment_creation()
    {
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            // error_log('No user logged in during appointment creation');
            return;
        }

        // Get course ID from multiple possible sources
        $url_course_id = '';
        if (isset($_POST['course_id'])) {
            $url_course_id = sanitize_text_field($_POST['course_id']);
        } elseif (isset($_GET['course_id'])) {
            $url_course_id = sanitize_text_field($_GET['course_id']);
        }

        $saved_course_id = get_option('ccp_course_id');

        // error_log(sprintf(
        //     'Appointment Creation Attempt - User: %s, Current Status: %s, POST Course ID: %s, Saved Course ID: %s',
        //     $current_user->user_login,
        //     get_user_meta($current_user->ID, 'appointment_status', true),
        //     $url_course_id,
        //     $saved_course_id
        // ));

        // Compare as strings and trim to ensure accurate comparison
        if (trim($url_course_id) === trim($saved_course_id)) {
            // Update user meta
            update_user_meta($current_user->ID, 'appointment_status', 'booked');

            // error_log(sprintf(
            //     'Appointment Status Updated - User: %s, New Status: booked',
            //     $current_user->user_login
            // ));

            // Add user info to response
            add_filter('bookly_save_appointment_response', function ($response) use ($current_user) {
                $response['user_login'] = $current_user->user_login;
                $response['appointment_status'] = 'booked';
                return $response;
            });
        } else {
            // error_log(sprintf(
            //     'Course ID mismatch - URL Course ID: "%s", Saved Course ID: "%s"',
            //     $url_course_id,
            //     $saved_course_id
            // ));
        }
    }
}