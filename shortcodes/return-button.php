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
        // Debug: Log the attempted CSS file path
        $css_url = plugins_url('css/return-button.css', dirname(__FILE__));
        error_log('Attempting to load CSS from: ' . $css_url);

        // Only enqueue on pages with the shortcode or Bookly form
        if (is_page() || has_shortcode(get_post()->post_content, get_option('ccp_return_shortcode_name', 'return-to-dashboard'))) {
            wp_enqueue_style(
                'ccp-return-button',
                $css_url,
                array(),
                filemtime(plugin_dir_path(dirname(__FILE__)) . 'css/return-button.css') // Add version number
            );

            wp_enqueue_script(
                'ccp-return-button',
                plugins_url('js/return-button.js', dirname(__FILE__)),
                array('jquery'),
                null,
                true
            );

            // Localize script with proper data
            wp_localize_script('ccp-return-button', 'ccpReturnData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('check_return_button_status')
            ));
        }
    }

    public function check_status_ajax()
    {
        check_ajax_referer('check_return_button_status', 'nonce');

        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            wp_send_json_error('User not logged in');
            return;
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);

        if ($appointment_status === 'booked') {
            $dashboard_url = site_url('/members-directory/' . bp_core_get_username($current_user->ID) . '/#component=course');
            wp_send_json_success(array(
                'status' => 'booked',
                'html' => $this->generate_button_html($dashboard_url)
            ));
        } else {
            wp_send_json_success(array(
                'status' => 'not-booked',
                'html' => ''
            ));
        }
    }

    private function generate_button_html($dashboard_url)
    {
        ob_start();
        ?>
        <div class="return-button-wrap">
            <a href="<?php echo esc_url($dashboard_url); ?>" class="return-dashboard-button">
                Return to Course Dashboard
            </a>
            </div>
            <?php
        return ob_get_clean();
    }

    public function render_shortcode($atts)
    {
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            return '';
        }

        $appointment_status = get_user_meta($current_user->ID, 'appointment_status', true);
        $dashboard_url = '';

        if ($appointment_status === 'booked') {
            $dashboard_url = site_url('/members-directory/' . bp_core_get_username($current_user->ID) . '/#component=course');
        }
        
        ob_start();
        ?>
        <div class="return-button-container" data-status="<?php echo esc_attr($appointment_status); ?>">
            <?php if ($appointment_status === 'booked'): ?>
                <?php echo $this->generate_button_html($dashboard_url); ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}