<?php
/*
Plugin Name: Course Custom Popup
Description: Displays a custom popup on specific course pages
Version: 1.0
Author: Olayinka Aremu
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// In your main PHP file, modify the wp_localize_script part:
function ccp_enqueue_scripts() {
    if (strpos($_SERVER['REQUEST_URI'], 'members-directory') !== false) {
        wp_enqueue_style('course-popup-style', plugins_url('css/popup-style.css', __FILE__));
        wp_enqueue_script('course-popup-script', plugins_url('js/popup-script.js', __FILE__), array('jquery'), null, true);
        
        // Get course name
        $course_id = get_option('ccp_course_id');
        $course_name = get_the_title($course_id);
        
        wp_localize_script('course-popup-script', 'ccpData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ccp_check_course'),
            'saved_course_id' => $course_id,
            'course_name' => $course_name,
            'appointment_url' => get_option('ccp_appointment_url', '/appointment-page')
        ));
    }
}
add_action('wp_enqueue_scripts', 'ccp_enqueue_scripts');

// AJAX handler to check course status and user meta
function ccp_check_course_status() {
    check_ajax_referer('ccp_check_course', 'nonce');
    
    $response = array(
        'show_popup' => false,
        'message' => '',
        'course_id' => '',
        'user_name' => ''
    );
    
    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json($response);
        return;
    }
    
    // Get saved course ID
    $course_id = get_option('ccp_course_id');
    if (!$course_id) {
        wp_send_json($response);
        return;
    }
    
    // Check if user has access to the course and it's active
    $user_course_status = bp_course_get_user_course_status($current_user->ID, $course_id);
    $is_course_active = wplms_user_course_active_check($current_user->ID, $course_id);
    
    if ($user_course_status && $is_course_active) {
        // Check appointment status
        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);
        
        if ($appointment_status !== 'booked') {
            $response['show_popup'] = true;
            $response['course_id'] = $course_id;
            $response['user_name'] = $current_user->display_name;
        }
    }
    
    wp_send_json($response);
}
add_action('wp_ajax_ccp_check_course_status', 'ccp_check_course_status');


// Modify the popup HTML:
function ccp_add_popup_html() {
    if (strpos($_SERVER['REQUEST_URI'], needle: 'members-directory') !== false) {
        ?>
        <div id="course-popup" class="course-popup-overlay" style="display: none;">
            <div class="course-popup-content">
                <span class="course-popup-close">&times;</span>
                <div class="course-popup-body">
                    <h2>Schedule Your Appointment</h2>
                    <!-- <p>Course ID: <span id="popup-course-id"></span></p> -->
                    <p class="popup-user-name">Welcome, <span id="popup-user-name"></span>!</p>
                    <p>Please schedule your appointment for the <b><span id="popup-course-name"></span></b> course.</p>
                </div>
                <div class='popup-button-wrap'>
                    <a href="#" id="appointment-button" class="custom-button">SCHEDULE APPOINTMENT</a>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'ccp_add_popup_html');

// Add to your settings page:
function ccp_render_settings_page() {
    if (isset($_POST['ccp_save_settings'])) {
        check_admin_referer('ccp_save_settings_nonce');
        // Existing settings
        if (isset($_POST['ccp_course_id'])) {
            update_option('ccp_course_id', sanitize_text_field($_POST['ccp_course_id']));
        }
        if (isset($_POST['ccp_appointment_url'])) {
            update_option('ccp_appointment_url', sanitize_text_field($_POST['ccp_appointment_url']));
        }
        // Add new shortcode setting
        if (isset($_POST['ccp_shortcode_name'])) {
            update_option('ccp_shortcode_name', sanitize_text_field($_POST['ccp_shortcode_name']));
        }
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $course_id = get_option('ccp_course_id');
    $appointment_url = get_option('ccp_appointment_url', '/appointment-page');
    $shortcode_name = get_option('ccp_shortcode_name', 'appointment-status-check');
    ?>
    <div class="wrap">
        <h1>Course Custom Popup Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('ccp_save_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ccp_course_id">Course ID</label>
                    </th>
                    <td>
                        <input type="text" id="ccp_course_id" name="ccp_course_id" 
                               value="<?php echo esc_attr($course_id); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ccp_appointment_url">Appointment Page URL</label>
                    </th>
                    <td>
                        <input type="text" id="ccp_appointment_url" name="ccp_appointment_url" 
                               value="<?php echo esc_attr($appointment_url); ?>" class="regular-text">
                        <p class="description">Enter the URL path (e.g., /appointment-page)</p>
                    </td>
                </tr>
                  <tr>
        <th scope="row">
            <label for="ccp_shortcode_name">Shortcode Name</label>
        </th>
        <td>
            <input type="text" id="ccp_shortcode_name" name="ccp_shortcode_name" 
                   value="<?php echo esc_attr($shortcode_name); ?>" class="regular-text">
            <p class="description">Enter the shortcode name without brackets (e.g., appointment-status-check)</p>
        </td>
    </tr>
            </table>
            <p class="submit">
                <input type="submit" name="ccp_save_settings" class="button button-primary" 
                       value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

// Register the settings page
function ccp_add_admin_menu() {
    add_options_page(
        'Course Custom Popup Settings', // Page title
        'Course Popup', // Menu title
        'manage_options', // Capability required
        'course-popup-settings', // Menu slug
        'ccp_render_settings_page' // Function to render the page
    );
}
add_action('admin_menu', 'ccp_add_admin_menu');


// register shortcode
require_once plugin_dir_path(__FILE__) . 'shortcodes/appointment-status.php';

function initialize_shortcodes() {
    CCP_Appointment_Status_Shortcode::get_instance();
}
add_action('init', 'initialize_shortcodes');