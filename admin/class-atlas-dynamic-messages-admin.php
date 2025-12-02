<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      2.3.0
 * @package    AtlasDynamicMessages
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress/WooCommerce functions with reference to the global namespace
use function \add_action;
use function \add_submenu_page;
use function \get_option;
use function \wp_enqueue_style;
use function \wp_enqueue_script;
use function \plugin_dir_url;
use function \plugin_dir_path;
use function \wp_localize_script;
use function \__;
use function \absint;
use function \sanitize_text_field;
use function \sanitize_textarea_field;
use function \determine_locale;

if (!defined('ABSPATH')) exit;

class AtlasDynamicMessages_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.3.0
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
    }

    /**
     * Enqueue scripts and styles for admin
     *
     * @param string $hook Current admin page
     */
    public function admin_scripts(string $hook): void {
        // Only load on our settings page
        if ('woocommerce_page_atlas-dynamic-messages-for-woocommerce' != $hook) {
            return;
        }
        
        // Register and enqueue jQuery UI
        wp_enqueue_style('jquery-ui-tabs', plugin_dir_url(__FILE__) . 'css/jquery-ui-tabs.min.css', array(), '1.13.2');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        
        // Add Color Picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Admin styles and scripts
        wp_enqueue_style('atlas-dmsg-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css', array(), '2.4.0');
        wp_enqueue_script('atlas-dmsg-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery', 'jquery-ui-tabs', 'wp-color-picker'), '2.4.0', true);
            
        wp_localize_script('atlas-dmsg-admin-script', 'atlasDmsgAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('atlas_dmsg_admin_nonce'),
            'scenarioCount' => count(get_option('atlas_dmsg_settings')['scenarios'] ?? array()),
            'strings' => array(
                'scenario' => __('Scenario', 'atlas-dynamic-messages-for-woocommerce'),
                'active_days' => __('Active Days', 'atlas-dynamic-messages-for-woocommerce'),
                'start_time' => __('Start Time', 'atlas-dynamic-messages-for-woocommerce'),
                'end_time' => __('End Time', 'atlas-dynamic-messages-for-woocommerce'),
                'message' => __('Message', 'atlas-dynamic-messages-for-woocommerce'),
                'time_placeholder' => __('Use {time_remain} placeholder for dynamic time display', 'atlas-dynamic-messages-for-woocommerce'),
                'remove_scenario' => __('Remove Scenario', 'atlas-dynamic-messages-for-woocommerce'),
                'confirm_remove' => __('Are you sure you want to remove this scenario?', 'atlas-dynamic-messages-for-woocommerce'),
                'sunday' => __('Sunday', 'atlas-dynamic-messages-for-woocommerce'),
                'monday' => __('Monday', 'atlas-dynamic-messages-for-woocommerce'),
                'tuesday' => __('Tuesday', 'atlas-dynamic-messages-for-woocommerce'),
                'wednesday' => __('Wednesday', 'atlas-dynamic-messages-for-woocommerce'),
                'thursday' => __('Thursday', 'atlas-dynamic-messages-for-woocommerce'),
                'friday' => __('Friday', 'atlas-dynamic-messages-for-woocommerce'),
                'saturday' => __('Saturday', 'atlas-dynamic-messages-for-woocommerce'),
                'hour' => __('hour', 'atlas-dynamic-messages-for-woocommerce'),
                'hours' => __('hours', 'atlas-dynamic-messages-for-woocommerce'),
                'minute' => __('minute', 'atlas-dynamic-messages-for-woocommerce'),
                'minutes' => __('minutes', 'atlas-dynamic-messages-for-woocommerce'),
                'second' => __('second', 'atlas-dynamic-messages-for-woocommerce'),
                'seconds' => __('seconds', 'atlas-dynamic-messages-for-woocommerce'),
                'and' => __('and', 'atlas-dynamic-messages-for-woocommerce'),
                'comma' => __(',', 'atlas-dynamic-messages-for-woocommerce'),
                'confirmResetLayout' => __('Are you sure you want to reset this layout to default settings? All your customizations will be lost.', 'atlas-dynamic-messages-for-woocommerce'),
                'layoutReset' => __('Layout settings have been reset to defaults.', 'atlas-dynamic-messages-for-woocommerce'),
                'preview_text' => __('Your customized message will appear like this. Time remaining:', 'atlas-dynamic-messages-for-woocommerce'),
                'midnight_crossing_notice' => __('This time range crosses midnight. The message will appear on the selected day(s) and continue until the end time on the following day.', 'atlas-dynamic-messages-for-woocommerce'),
                'start_day' => __('Start Day', 'atlas-dynamic-messages-for-woocommerce'),
                'end_day' => __('End Day', 'atlas-dynamic-messages-for-woocommerce'),
                'note' => __('Note', 'atlas-dynamic-messages-for-woocommerce')
            )
        ));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page(): void {
        add_submenu_page(
            'woocommerce',
            __('Dynamic Messages Settings', 'atlas-dynamic-messages-for-woocommerce'),
            __('Dynamic Messages', 'atlas-dynamic-messages-for-woocommerce'),
            'manage_options',
            'atlas-dynamic-messages-for-woocommerce',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings(): void {
        register_setting(
            'atlas_dmsg_settings_group',
            'atlas_dmsg_settings',
            array($this, 'validate_settings')
        );
    }

    /**
     * Validate and sanitize settings
     * 
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function validate_settings(array $input): array {
        // Verify nonce
        if (!isset($_POST['atlas_dmsg_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['atlas_dmsg_nonce'])), 'atlas_dmsg_save_settings')) {
            add_settings_error('atlas_dmsg_settings', 'atlas_dmsg_nonce_error', __('Security check failed.', 'atlas-dynamic-messages-for-woocommerce'), 'error');
            return get_option('atlas_dmsg_settings');
        }
        
        $output = [];
        
        // Validate time offset
        $output['time_offset'] = intval($input['time_offset'] ?? 0);
        
        // Validate scenarios
        if (isset($input['scenarios']) && is_array($input['scenarios'])) {
            foreach ($input['scenarios'] as $key => $scenario) {
                // Sanitize array key
                $key = sanitize_key($key);
                
                // Default value for active is 0 (inactive)
                $active = isset($scenario['active']) && $scenario['active'] == 1 ? 1 : 0;
                
                // Ensure days is an array, and all values are integers
                $days = isset($scenario['days']) && is_array($scenario['days']) 
                      ? array_map('absint', $scenario['days']) 
                      : [];
                
                // Sanitize all other fields
                $output['scenarios'][$key] = [
                    'active'           => $active,
                    'days'             => $days,
                    'start_time'       => sanitize_text_field($scenario['start_time'] ?? ''),
                    'end_time'         => sanitize_text_field($scenario['end_time'] ?? ''),
                    'message'          => wp_kses_post($scenario['message'] ?? ''),
                    'url'              => esc_url_raw($scenario['url'] ?? ''),
                    'layout'           => isset($scenario['layout']) ? absint($scenario['layout']) : 1,
                    'display_location' => isset($scenario['display_location']) && is_array($scenario['display_location']) 
                                        ? array_map('sanitize_text_field', $scenario['display_location']) 
                                        : ['product']
                ];
                
                // Verify layout is valid (1-3)
                if ($output['scenarios'][$key]['layout'] < 1 || $output['scenarios'][$key]['layout'] > 3) {
                    $output['scenarios'][$key]['layout'] = 1;
                }
            }
        }
        
        // Validate layouts settings
        if (isset($input['layouts']) && is_array($input['layouts'])) {
            foreach ($input['layouts'] as $layout_id => $layout_settings) {
                // Verify layout ID is valid (1-3)
                if (!in_array($layout_id, [1, 2, 3])) {
                    continue;
                }
                
                // Initialize this layout's settings array
                $output['layouts'][$layout_id] = [];
                
                // Layout 1 - Dashed Style
                if ($layout_id == 1) {
                    $output['layouts'][1]['bg_color'] = isset($layout_settings['bg_color']) ? sanitize_text_field($layout_settings['bg_color']) : '#fef9e7';
                    $output['layouts'][1]['text_color'] = isset($layout_settings['text_color']) ? sanitize_text_field($layout_settings['text_color']) : '#4b2900';
                    $output['layouts'][1]['border_color'] = isset($layout_settings['border_color']) ? sanitize_text_field($layout_settings['border_color']) : '#ffa800';
                    $output['layouts'][1]['accent_color'] = isset($layout_settings['accent_color']) ? sanitize_text_field($layout_settings['accent_color']) : '#b53300';
                    $output['layouts'][1]['border_style'] = isset($layout_settings['border_style']) && in_array($layout_settings['border_style'], ['dashed', 'solid', 'dotted']) ? $layout_settings['border_style'] : 'dashed';
                    $output['layouts'][1]['icon'] = isset($layout_settings['icon']) ? sanitize_text_field($layout_settings['icon']) : '';
                    $output['layouts'][1]['custom_css'] = isset($layout_settings['custom_css']) ? sanitize_textarea_field($layout_settings['custom_css']) : '';
                }
                
                // Layout 2 - Modern Card
                if ($layout_id == 2) {
                    $output['layouts'][2]['bg_color'] = isset($layout_settings['bg_color']) ? sanitize_text_field($layout_settings['bg_color']) : '#ffffff';
                    $output['layouts'][2]['text_color'] = isset($layout_settings['text_color']) ? sanitize_text_field($layout_settings['text_color']) : '#2c3e50';
                    $output['layouts'][2]['border_color'] = isset($layout_settings['border_color']) ? sanitize_text_field($layout_settings['border_color']) : '#2c3e50';
                    $output['layouts'][2]['accent_color'] = isset($layout_settings['accent_color']) ? sanitize_text_field($layout_settings['accent_color']) : '#2980b9';
                    $output['layouts'][2]['shadow_intensity'] = isset($layout_settings['shadow_intensity']) && in_array($layout_settings['shadow_intensity'], ['light', 'medium', 'strong']) ? $layout_settings['shadow_intensity'] : 'medium';
                    $output['layouts'][2]['icon'] = isset($layout_settings['icon']) ? sanitize_text_field($layout_settings['icon']) : '🚚';
                    $output['layouts'][2]['border_radius'] = isset($layout_settings['border_radius']) ? absint($layout_settings['border_radius']) : 8;
                    $output['layouts'][2]['custom_css'] = isset($layout_settings['custom_css']) ? sanitize_textarea_field($layout_settings['custom_css']) : '';
                }
                
                // Layout 3 - Gradient Alert
                if ($layout_id == 3) {
                    $output['layouts'][3]['gradient_start'] = isset($layout_settings['gradient_start']) ? sanitize_text_field($layout_settings['gradient_start']) : '#ffe8d4';
                    $output['layouts'][3]['gradient_end'] = isset($layout_settings['gradient_end']) ? sanitize_text_field($layout_settings['gradient_end']) : '#ffd8b2';
                    $output['layouts'][3]['gradient_direction'] = isset($layout_settings['gradient_direction']) && in_array($layout_settings['gradient_direction'], ['to right', 'to bottom', '135deg']) ? $layout_settings['gradient_direction'] : '135deg';
                    $output['layouts'][3]['text_color'] = isset($layout_settings['text_color']) ? sanitize_text_field($layout_settings['text_color']) : '#d32f2f';
                    $output['layouts'][3]['border_color'] = isset($layout_settings['border_color']) ? sanitize_text_field($layout_settings['border_color']) : '#ff6b6b';
                    $output['layouts'][3]['accent_color'] = isset($layout_settings['accent_color']) ? sanitize_text_field($layout_settings['accent_color']) : '#ff6b6b';
                    $output['layouts'][3]['icon'] = isset($layout_settings['icon']) ? sanitize_text_field($layout_settings['icon']) : '';
                    $output['layouts'][3]['accent_height'] = isset($layout_settings['accent_height']) ? absint($layout_settings['accent_height']) : 4;
                    $output['layouts'][3]['custom_css'] = isset($layout_settings['custom_css']) ? sanitize_textarea_field($layout_settings['custom_css']) : '';
                }
            }
        }
        
        return $output;
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        require_once plugin_dir_path(__FILE__) . 'partials/settings-page.php';
    }
}