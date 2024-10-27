<?php
/*
Plugin Name: Course Custom Popup
Description: Displays a custom popup on specific course pages
Version: 1.0
Author: Olayinka Aremu
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add scripts and styles
function ccp_enqueue_scripts() {
    // Get the current post ID
    $current_id = is_singular() ? get_queried_object_id() : 0;
    $saved_id = get_option('ccp_course_id');
    
    // Define file paths
    $css_file = plugin_dir_path(__FILE__) . 'css/popup-style.css';
    $js_file = plugin_dir_path(__FILE__) . 'js/popup-script.js';
    
    // Check if CSS file exists
    if (file_exists($css_file)) {
        wp_enqueue_style('course-popup-style', plugins_url('css/popup-style.css', __FILE__), array(), null);
    }
    
    // Check if JS file exists
    if (file_exists($js_file)) {
        wp_enqueue_script('course-popup-script', plugins_url('js/popup-script.js', __FILE__), array('jquery'), null, true);
    }

    // Add debug data to JavaScript
    wp_localize_script('course-popup-script', 'ccpData', array(
        'currentId' => $current_id,
        'savedId' => $saved_id,
        'pluginUrl' => plugins_url('', __FILE__)
    ));
}
add_action('wp_enqueue_scripts', 'ccp_enqueue_scripts');

// Add popup HTML to footer
function ccp_add_popup_html() {
    $course_id = is_singular() ? get_queried_object_id() : 0;
    $saved_course_id = get_option('ccp_course_id');
    
    if ($course_id == $saved_course_id) {
        ?>
        <div id="course-popup" class="course-popup-overlay" style="display: none;" role="dialog" aria-labelledby="popup-title" aria-describedby="popup-description">
            <div class="course-popup-content">
                <span class="course-popup-close" role="button" tabindex="0" aria-label="Close Popup">&times;</span>
                <div class="course-popup-body">
                    <h2 id="popup-title">Welcome to the Course!</h2>
                    <p id="popup-description">Current ID: <?php echo esc_html($course_id); ?></p>
                    <p>Saved ID: <?php echo esc_html($saved_course_id); ?></p>
                    <div class="custom-content">
                        <p>This is a test popup to verify functionality.</p>
                        <button class="custom-button">Test Button</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'ccp_add_popup_html', 999);

// Debug function to verify scripts are loading
function ccp_add_test_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            if (ccpData.currentId == ccpData.savedId) {
                $('#course-popup').fadeIn();
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'ccp_add_test_script', 1000);

// Add settings menu
function ccp_add_settings_menu() {
    add_options_page(
        'Course Custom Popup Settings',
        'Course Popup',
        'manage_options',
        'ccp-settings',
        'ccp_render_settings_page'
    );
}
add_action('admin_menu', 'ccp_add_settings_menu');

// Render the settings page
function ccp_render_settings_page() {
    if (isset($_POST['ccp_save_settings'])) {
        check_admin_referer('ccp_save_settings_nonce'); // Security check
        if (isset($_POST['ccp_course_id'])) {
            update_option('ccp_course_id', sanitize_text_field($_POST['ccp_course_id']));
        }
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $course_id = get_option('ccp_course_id');
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
                        <p class="description">Enter the course ID where you want the popup to appear.</p>
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

// Cleanup on deactivation
function ccp_deactivate() {
    // Optional: Remove plugin settings
    // delete_option('ccp_course_id');
}
register_deactivation_hook(__FILE__, 'ccp_deactivate');
