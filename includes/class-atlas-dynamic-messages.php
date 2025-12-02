<?php
/**
 * The core plugin class.
 *
 * @since      2.3.0
 * @package    AtlasDynamicMessages
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress/WooCommerce functions with reference to the global namespace
use function \add_action;
use function \plugin_dir_path;
use function \dirname;
use function \defined;
use function \current_user_can;
use function \esc_html__;
use function \do_action;

if (!defined('ABSPATH')) exit;

class AtlasDynamicMessages {

    /**
     * Admin instance
     *
     * @since    2.3.0
     * @access   protected
     * @var      AtlasDynamicMessages_Admin    $admin    Handles all admin functionality
     */
    protected AtlasDynamicMessages_Admin $admin;

    /**
     * Public instance
     *
     * @since    2.3.0
     * @access   protected
     * @var      AtlasDynamicMessages_Public    $public    Handles all public-facing functionality
     */
    protected AtlasDynamicMessages_Public $public;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    2.3.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    2.3.0
     * @access   private
     */
    private function load_dependencies(): void {
        // Admin area
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-atlas-dynamic-messages-admin.php';
        
        // Public-facing functionality
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-atlas-dynamic-messages-public.php';
    }

    /**
     * Register all of the hooks related to the admin area.
     *
     * @since    2.3.0
     * @access   private
     */
    private function define_admin_hooks(): void {
        $this->admin = new AtlasDynamicMessages_Admin();
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    2.3.0
     * @access   private
     */
    private function define_public_hooks(): void {
        $this->public = new AtlasDynamicMessages_Public();
    }

    /**
     * Run the plugin.
     *
     * @since    2.3.0
     */
public function run(): void {
    try {
        // Plugin initialization code
        do_action('atlas_dmsg_before_init');
        
        // Additional initialization tasks can go here
        
        do_action('atlas_dmsg_after_init');
    } catch (\Exception $e) {
        // Optionally show admin notice
        add_action('admin_notices', function() use ($e) {
            if (current_user_can('manage_options')) {
                echo '<div class="error"><p>' . 
                     esc_html__('Error initializing Atlas Dynamic Messages for WooCommerce. Please try deactivating and reactivating the plugin.', 'atlas-dynamic-messages-for-woocommerce') .
                     '</p></div>';
            }
        });
    }
}
}