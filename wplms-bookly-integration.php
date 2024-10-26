<?php
/*
Plugin Name: WPLMS Bookly Integration
Plugin URI: 
Description: Integrates Bookly appointments with WPLMS units
Version: 1.0.0
Author: Aremu Olayinka
License: GPL v2 or later
Text Domain: wplms-bookly-integration
*/

if (!defined('ABSPATH')) {
    exit;
}

class WPLMS_Bookly_Integration {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Check dependencies
        add_action('admin_init', array($this, 'check_dependencies'));
        
        // Add hooks
        add_filter('wplms_unit_types', array($this, 'add_appointment_unit_type'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_complete_appointment_unit', array($this, 'complete_appointment_unit'));
    }

    public function check_dependencies() {
        if (!class_exists('WPLMS_Plugin') || !class_exists('Bookly\Lib\Base\Plugin')) {
            add_action('admin_notices', array($this, 'dependency_notice'));
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }

    public function dependency_notice() {
        ?>
        <div class="error">
            <p><?php _e('WPLMS Bookly Integration requires both WPLMS and Bookly plugins to be installed and activated.', 'wplms-bookly-integration'); ?></p>
        </div>
        <?php
    }
}

// Initialize plugin
function wplms_bookly_integration_init() {
    return WPLMS_Bookly_Integration::get_instance();
}
add_action('plugins_loaded', 'wplms_bookly_integration_init');