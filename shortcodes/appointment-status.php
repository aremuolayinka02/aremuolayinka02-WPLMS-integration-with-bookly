<?php
class CCP_Appointment_Status_Shortcode
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
        $shortcode_name = get_option('ccp_shortcode_name', 'appointment-status-check');
        add_shortcode($shortcode_name, array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // Add AJAX handler for status check
        add_action('wp_ajax_check_appointment_status', array($this, 'check_appointment_status_ajax'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('ccp-shortcode-style', plugins_url('css/shortcode-style.css', dirname(__FILE__)));

        // Enqueue JavaScript for dynamic updates
        wp_enqueue_script(
            'ccp-shortcode-script',
            plugins_url('js/shortcode-script.js', dirname(__FILE__)),
            array('jquery'),
            null,
            true
        );

        // Pass data to JavaScript
        wp_localize_script('ccp-shortcode-script', 'ccpShortcodeData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('check_appointment_status')
        ));
    }

    public function check_appointment_status_ajax()
    {
        check_ajax_referer('check_appointment_status', 'nonce');

        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            wp_send_json_error('User not logged in');
            return;
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);
        $appointment_url = get_option('ccp_appointment_url', '/appointment-page');
        $course_id = get_option('ccp_course_id');
        $appointment_url = add_query_arg('course_id', $course_id, $appointment_url);

        wp_send_json_success(array(
            'status' => $appointment_status,
            'html' => $this->generate_status_html($appointment_status, $appointment_url)
        ));
    }

    private function generate_status_html($appointment_status, $appointment_url)
    {
        ob_start();
        if ($appointment_status === 'booked') {
            ?>
            <div class="status-icon success">
                <svg viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                </svg>
            </div>
            <p class="status-text success">Your appointment has been scheduled already!</p>
            <?php
        } else {
            ?>
            <div class="status-icon error">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                </svg>
            </div>
            <p class="status-text error">No appointment scheduled yet.</p>
            <div class='appointment-button-wrap'>
                <a href="<?php echo esc_url(site_url($appointment_url)); ?>" class="appointment-button">Schedule Shooting
                    Appointment</a>
            </div>
            <?php
        }
        return ob_get_clean();
    }

    public function render_shortcode($atts)
    {
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            return 'Please log in to check appointment status.';
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);
        $appointment_url = get_option('ccp_appointment_url', '/appointment-page');
        $course_id = get_option('ccp_course_id');
        $appointment_url = add_query_arg('course_id', $course_id, $appointment_url);

        ob_start();
        ?>
        <div class="appointment-status-container" data-user-id="<?php echo esc_attr($current_user->ID); ?>">
            <?php echo $this->generate_status_html($appointment_status, $appointment_url); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}