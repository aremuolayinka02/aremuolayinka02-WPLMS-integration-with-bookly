<?php
class CCP_Return_Button_Shortcode
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
        $shortcode_name = get_option('ccp_return_shortcode_name', 'return-to-dashboard');
        add_shortcode($shortcode_name, array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_check_return_button_status', array($this, 'check_status_ajax'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('ccp-return-button', plugins_url('css/return-button.css', dirname(__FILE__)));
        wp_enqueue_script(
            'ccp-return-button',
            plugins_url('js/return-button.js', dirname(__FILE__)),
            array('jquery'),
            null,
            true
        );
    }

    public function check_status_ajax()
    {
        check_ajax_referer('ccp_check_course', 'nonce');

        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            wp_send_json_error();
            return;
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);

        wp_send_json_success(array(
            'status' => $appointment_status,
            'html' => $this->generate_button_html($current_user, $appointment_status)
        ));
    }

    private function generate_button_html($user, $status)
    {
        ob_start();
        if ($status === 'booked') {
            ?>
            <div class="return-button-wrap">
                <a href="<?php echo esc_url(site_url('/members-directory/' . bp_core_get_username($user->ID) . '/#component=course')); ?>"
                    class="return-dashboard-button">
                    Return to Course Dashboard
                </a>
            </div>
            <?php
        }
        return ob_get_clean();
    }

    public function render_shortcode($atts)
    {
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            return '';
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);

        ob_start();
        ?>
        <div class="return-button-container" data-status="<?php echo esc_attr($appointment_status); ?>">
            <?php echo $this->generate_button_html($current_user, $appointment_status); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}