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
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('ccp-shortcode-style', plugins_url('css/shortcode-style.css', dirname(__FILE__)));
    }

    public function render_shortcode($atts)
    {
        // Get current user
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            return 'Please log in to check appointment status.';
        }

        // Get appointment status and course ID
        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);
        $appointment_url = get_option('ccp_appointment_url', '/appointment-page');
        $course_id = get_option('ccp_course_id'); // Get the saved course ID

        // Append course ID to appointment URL
        $appointment_url = add_query_arg('course_id', $course_id, $appointment_url);

        ob_start();
        ?>
        <div class="appointment-status-container">
            <?php if ($appointment_status !== 'booked'): ?>
                <div class="status-icon success">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                    </svg>
                </div>
                <p class="status-text success">Your appointment has been scheduled already!</p>
            <?php else: ?>
                <div class="status-icon error">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                    </svg>
                </div>
                <p class="status-text error">No appointment scheduled yet.</p>

                <div class='appointment-button-wrap'>
                    <a href="<?php echo esc_url(site_url($appointment_url)); ?>" class="appointment-button">Schedule Shooting Appointment</a>
                </div>

            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}