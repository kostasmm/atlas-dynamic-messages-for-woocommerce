<?php
/**
 * Plugin Name: Atlas Dynamic Messages for WooCommerce
 * Description: Cache-compatible real-time countdown messages for WooCommerce. Works perfectly with ALL caching plugins - the only countdown plugin that bypasses cache for accurate, live updates!
 * Version: 2.4.2
 * Author: PluginAtlas
 * Author URI: https://pluginatlas.com
 * Text Domain: atlas-dynamic-messages-for-woocommerce
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 9.5
 * Requires Plugins: woocommerce
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress/WooCommerce functions with reference to the global namespace
use function \add_action;
use function \add_filter;
use function \plugin_basename;
use function \deactivate_plugins;
use function \plugin_dir_path;
use function \plugin_dir_url;
use function \__;
use function \class_exists;
use function \array_unshift;
use function \require_once;

if (!defined('ABSPATH')) exit;

/**
 * Check if WooCommerce is active
 */
function atlas_dmsg_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', __NAMESPACE__ . '\atlas_dmsg_woocommerce_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', __NAMESPACE__ . '\atlas_dmsg_check_woocommerce');

/**
 * WooCommerce missing notice
 */
function atlas_dmsg_woocommerce_notice() {
    echo '<div class="error"><p>' . 
         esc_html__('Atlas Dynamic Messages for WooCommerce requires WooCommerce to be installed and active.', 'atlas-dynamic-messages-for-woocommerce') . 
         '</p></div>';
}

// Define plugin constants
define('ATLAS_DMSG_VERSION', '2.4.2');
define('ATLAS_DMSG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ATLAS_DMSG_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS)
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables', 
            __FILE__, 
            true
        );
    }
});

// Include required files
require_once ATLAS_DMSG_PLUGIN_DIR . 'includes/class-atlas-dynamic-messages.php';

function atlas_dmsg_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=atlas-dynamic-messages-for-woocommerce">' . __('Settings', 'atlas-dynamic-messages-for-woocommerce') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin_basename = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin_basename", __NAMESPACE__ . '\atlas_dmsg_add_settings_link');

/**
 * Begins execution of the plugin.
 */
function run_atlas_dynamic_messages() {
    $plugin = new \PluginAtlas\DynamicMessages\AtlasDynamicMessages();
    $plugin->run();
}
run_atlas_dynamic_messages();