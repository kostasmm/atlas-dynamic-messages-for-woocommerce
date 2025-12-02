<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      2.2.0
 * @package    AtlasDynamicMessages
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress/WooCommerce functions with reference to the global namespace
use function \add_action;
use function \add_filter;
use function \get_option;
use function \wp_enqueue_style;
use function \wp_enqueue_script;
use function \plugin_dir_url;
use function \plugin_dir_path;
use function \wp_register_script;
use function \wp_register_style;
use function \wp_localize_script;
use function \wp_add_inline_script;
use function \wp_add_inline_style;
use function \wp_create_nonce;
use function \rest_url;
use function \esc_html__;
use function \esc_attr__;
use function \current_user_can;
use function \is_user_logged_in;
use function \register_rest_route;
use function \wp_timezone_string;
use function \is_product;
use function \is_cart;
use function \is_checkout;
use function \esc_attr;
use function \esc_html;
use function \esc_url_raw;
use function \esc_url;
use function \sanitize_text_field;
use function \wp_kses_post;
use function \wp_kses;
use function \absint;
use function \wp_get_theme;
use \DateTime;
use \DateTimeZone;
use \DateInterval;
use \WP_REST_Response;

if (!defined('ABSPATH')) exit;

class AtlasDynamicMessages_Public {

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.2.0
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_endpoint'));
        
        // Hooks for different pages
        add_action('woocommerce_before_single_product', array($this, 'display_placeholder'), 20);
        add_action('woocommerce_before_cart', array($this, 'display_placeholder'), 10);
        add_action('woocommerce_before_checkout_form', array($this, 'display_placeholder'), 10);
    }

    /**
     * Register all scripts and styles
     */
    public function register_scripts(): void {
        // Get file modification times for better cache control
        $css_file = plugin_dir_path(__FILE__) . 'css/atlas-dynamic-messages.css';
        $js_file = plugin_dir_path(__FILE__) . 'js/atlas-dynamic-messages.js';
        
        $css_version = file_exists($css_file) ? filemtime($css_file) : ATLAS_DMSG_VERSION;
        $js_version = file_exists($js_file) ? filemtime($js_file) : ATLAS_DMSG_VERSION;
        
        wp_register_style('atlas-dmsg-style', plugin_dir_url(__FILE__) . 'css/atlas-dynamic-messages.css', array(), $css_version);
        wp_register_script('atlas-dmsg-script', plugin_dir_url(__FILE__) . 'js/atlas-dynamic-messages.js', array('jquery'), $js_version, true);
        
        // Generate nonce for API security
        $nonce = wp_create_nonce('wp_rest');
        
        wp_localize_script('atlas-dmsg-script', 'atlasDmsgVars', [
            'apiUrl'   => esc_url(rest_url('atlas-dmsg/v1/info')),
            'nonce'    => $nonce,
            'errorMsg' => esc_html__('Error loading Dynamic Messages countdown.', 'atlas-dynamic-messages-for-woocommerce'),
            'time' => [
                'hours' => [
                    'one' => esc_html__('hour', 'atlas-dynamic-messages-for-woocommerce'),
                    'many' => esc_html__('hours', 'atlas-dynamic-messages-for-woocommerce')
                ],
                'minutes' => [
                    'one' => esc_html__('minute', 'atlas-dynamic-messages-for-woocommerce'),
                    'many' => esc_html__('minutes', 'atlas-dynamic-messages-for-woocommerce')
                ],
                'seconds' => [
                    'one' => esc_html__('second', 'atlas-dynamic-messages-for-woocommerce'),
                    'many' => esc_html__('seconds', 'atlas-dynamic-messages-for-woocommerce')
                ],
            ],
            'and'      => esc_html__('and', 'atlas-dynamic-messages-for-woocommerce'),
            'comma'    => esc_html__(',', 'atlas-dynamic-messages-for-woocommerce'),
            'isAdmin'  => (bool) current_user_can('manage_options')
        ]);
        
        // Add theme-specific adjustments via inline scripts
        $current_theme = wp_get_theme();
        $theme_slug = $current_theme->get_stylesheet();
        
        // Default adjustments for all themes
        $inline_script = "
            // Atlas Dynamic Messages Theme Compatibility Adjustments
            window.atlasDmsgThemeCompat = {
                theme: '" . esc_js($theme_slug) . "',
                isCustomized: false
            };
        ";
        
        // Theme-specific adjustments
        switch ($theme_slug) {
            case 'storefront':
                $inline_script .= "
                    window.atlasDmsgThemeCompat.isCustomized = true;
                    window.atlasDmsgThemeCompat.adjustments = function() {
                        // Storefront-specific adjustments
                        const container = document.getElementById('atlas-dmsg-message-container');
                        if (container) {
                            // Example: Adjust positioning for Storefront theme
                            container.style.marginTop = '15px';
                        }
                    };
                ";
                break;
                
            case 'astra':
                $inline_script .= "
                    window.atlasDmsgThemeCompat.isCustomized = true;
                    window.atlasDmsgThemeCompat.adjustments = function() {
                        // Astra-specific adjustments
                        const containers = document.querySelectorAll('.atlas-dmsg-box');
                        containers.forEach(function(box) {
                            // Example: Adjust z-index for Astra theme
                            box.style.zIndex = '90';
                        });
                    };
                ";
                break;
                
            case 'flatsome':
                $inline_script .= "
                    window.atlasDmsgThemeCompat.isCustomized = true;
                    window.atlasDmsgThemeCompat.adjustments = function() {
                        // Flatsome-specific adjustments
                        const container = document.getElementById('atlas-dmsg-message-container');
                        if (container) {
                            container.style.margin = '20px auto';
                            container.style.maxWidth = '1080px';
                        }
                    };
                ";
                break;
                
            // Add more theme-specific adjustments as needed
        }
        
        // Add hook for themes to extend compatibility
        $inline_script .= "
            document.addEventListener('DOMContentLoaded', function() {
                // Hook for themes to extend compatibility
                if (typeof window.atlasDmsgExtendCompat === 'function') {
                    window.atlasDmsgExtendCompat();
                }
            });
        ";
        
        wp_add_inline_script('atlas-dmsg-script', $inline_script, 'before');
    }

    /**
     * Enqueue scripts and styles for frontend
     */
    public function enqueue_scripts(): void {
        $settings = get_option('atlas_dmsg_settings');
        
        // Check if there are any scenarios configured
        if (empty($settings['scenarios'])) return;
        
        // Check if we're on a page that might display a message
        $current_page = $this->get_current_page_type();
        if (empty($current_page)) return;
        
        // Check if there are any scenarios that should display on this page
        $has_scenarios_for_page = false;
        foreach ($settings['scenarios'] as $scenario) {
            if (isset($scenario['active']) && $scenario['active'] == 1 && 
                isset($scenario['display_location']) && 
                is_array($scenario['display_location']) && 
                in_array($current_page, $scenario['display_location'])) {
                $has_scenarios_for_page = true;
                break;
            }
        }
        
        if (!$has_scenarios_for_page) return;
        
        // Now actually enqueue the registered scripts
        wp_enqueue_style('atlas-dmsg-style');
        wp_enqueue_script('atlas-dmsg-script');
        
        // Add custom CSS after enqueuing the main stylesheet
        $this->add_custom_layouts_css();
    }
    
    /**
     * Register REST API endpoint
     */
    public function register_rest_endpoint(): void {
        register_rest_route('atlas-dmsg/v1', '/info', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_scenarios_data'],
            'permission_callback' => '__return_true' // Public endpoint for countdown data
        ]);
    }

    /**
     * Get scenarios data for REST API
     *
     * @return WP_REST_Response Scenarios data and server time with no-cache headers
     */
    public function get_scenarios_data(): WP_REST_Response {
        $settings = get_option('atlas_dmsg_settings');

        // Use WordPress timezone
        $timezone = new DateTimeZone(wp_timezone_string());
        $server_datetime = new DateTime('now', $timezone);

        // Apply configurable time offset (default to 0 if not set)
        $time_offset = intval($settings['time_offset'] ?? 0);
        if ($time_offset != 0) {
            $offset_interval = new DateInterval('PT' . abs($time_offset) . 'H');
            if ($time_offset < 0) {
                $server_datetime->sub($offset_interval);
            } else {
                $server_datetime->add($offset_interval);
            }
        }

        $server_timestamp = $server_datetime->getTimestamp();

        $scenarios = isset($settings['scenarios']) ? $this->sanitize_scenarios($settings['scenarios']) : array();

        // Filter out inactive scenarios
        $active_scenarios = array_filter($scenarios, fn($scenario) =>
            isset($scenario['active']) && $scenario['active'] == 1
        );

        // Get layout settings to send to frontend
        $layout_settings = isset($settings['layouts']) ? $settings['layouts'] : array();

        // Important: Include current page info so JavaScript can filter appropriately
        $current_page = $this->get_current_page_type();

        // Create WP_REST_Response with no-cache headers for LiteSpeed/other caching systems
        $response = new WP_REST_Response( array(
            'scenarios'       => array_values($active_scenarios), // Reset array keys
            'serverTime'      => $server_timestamp,
            'currentPage'     => $current_page, // Send current page type to JS
            'layout_settings' => $layout_settings // Send layout settings to JS
        ) );

        // No-cache headers to bypass LiteSpeed and other caching systems
        $response->set_headers( array(
            'Cache-Control'             => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
            'X-LiteSpeed-Cache-Control' => 'no-cache', // LiteSpeed-specific header
        ) );

        return $response;
    }

    /**
     * Get current page type
     * 
     * @return string Current page type (product, cart, checkout or empty)
     */
    private function get_current_page_type(): string {
        if (is_product()) {
            return 'product';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            return 'checkout';
        }
        return '';
    }

    /**
     * Sanitize scenarios data
     * 
     * @param array $scenarios Raw scenarios data
     * @return array Sanitized scenarios data
     */
    private function sanitize_scenarios(array $scenarios): array {
        return array_map(fn($scenario) => [
            'active'           => absint($scenario['active'] ?? 1),
            'days'             => array_map('intval', $scenario['days'] ?? []),
            'start_time'       => sanitize_text_field($scenario['start_time']),
            'end_time'         => sanitize_text_field($scenario['end_time']),
            'message'          => wp_kses_post($scenario['message']),
            'url'              => esc_url_raw($scenario['url'] ?? ''),
            'layout'           => absint($scenario['layout'] ?? 1),
            'display_location' => isset($scenario['display_location']) && is_array($scenario['display_location']) 
                                ? array_map('sanitize_text_field', $scenario['display_location']) 
                                : ['product']
        ], $scenarios);
    }

    /**
     * Display placeholder for messages
     */
    public function display_placeholder(): void {
        $current_page = $this->get_current_page_type();
        if (empty($current_page)) return;
        
        $allowed_html = [
            'div' => [
                'id' => [],
                'class' => [],
                'data-page' => [],
            ],
        ];
        
        echo wp_kses('<div id="atlas-dmsg-message-container" class="atlas-dmsg-message-wrap"></div>', $allowed_html);
        echo wp_kses('<div id="atlas-dmsg-countdown-container" data-page="' . esc_attr($current_page) . '"></div>', $allowed_html);
    }

    /**
     * Get layout template for a scenario
     * 
     * @param int $layout_id Layout ID
     * @return string Layout template HTML
     */
    public function get_layout_template(int $layout_id): string {
        $layout_file = plugin_dir_path(dirname(__FILE__)) . 'layouts/layout-' . absint($layout_id) . '.php';
        
        if (file_exists($layout_file)) {
            ob_start();
            include $layout_file;
            return ob_get_clean();
        }
        
        // Default to layout 1 if file not found
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'layouts/layout-1.php';
        return ob_get_clean();
    }
    
    /**
     * Add custom CSS for layouts based on settings
     */
    public function add_custom_layouts_css(): void {
        $settings = get_option('atlas_dmsg_settings');
        
        if (!isset($settings['layouts']) || empty($settings['layouts'])) {
            return;
        }
        
        // Start building CSS
        $css = '';
        
        // Process Layout 1
        if (isset($settings['layouts'][1])) {
            $layout1 = $settings['layouts'][1];
            
            if (!empty($layout1['bg_color'])) {
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box { background-color: ' . esc_attr($layout1['bg_color']) . '; }';
            }
            
            if (!empty($layout1['text_color'])) {
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box { color: ' . esc_attr($layout1['text_color']) . '; }';
            }
            
            if (!empty($layout1['border_color'])) {
                $border_style = !empty($layout1['border_style']) ? $layout1['border_style'] : 'dashed';
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box { border-left: 2px ' . esc_attr($border_style) . ' ' . esc_attr($layout1['border_color']) . '; border-right: 2px ' . esc_attr($border_style) . ' ' . esc_attr($layout1['border_color']) . '; border-bottom: 2px ' . esc_attr($border_style) . ' ' . esc_attr($layout1['border_color']) . '; }';
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box::before { background-color: ' . esc_attr($layout1['border_color']) . '; }';
            }
            
            if (!empty($layout1['accent_color'])) {
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box strong { color: ' . esc_attr($layout1['accent_color']) . '; }';
            }
            
            if (!empty($layout1['icon'])) {
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box::after { content: "' . esc_attr($layout1['icon']) . '"; position: absolute; top: 5px; right: 10px; font-size: 18px; }';
            }
            
            if (!empty($layout1['custom_css'])) {
                $css .= '.atlas-dmsg-layout-1 .atlas-dmsg-box { ' . esc_attr($layout1['custom_css']) . ' }';
            }
        }
        
        // Process Layout 2
        if (isset($settings['layouts'][2])) {
            $layout2 = $settings['layouts'][2];
            
            if (!empty($layout2['bg_color'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { background-color: ' . esc_attr($layout2['bg_color']) . '; }';
            }
            
            if (!empty($layout2['text_color'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { color: ' . esc_attr($layout2['text_color']) . '; }';
            }
            
            if (!empty($layout2['border_color'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { border: 2px solid ' . esc_attr($layout2['border_color']) . '; }';
            }
            
            if (!empty($layout2['accent_color'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box strong { color: ' . esc_attr($layout2['accent_color']) . '; }';
            }
            
            if (!empty($layout2['border_radius'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { border-radius: ' . esc_attr($layout2['border_radius']) . 'px; }';
            }
            
            if (!empty($layout2['shadow_intensity'])) {
                $shadow = '';
                switch ($layout2['shadow_intensity']) {
                    case 'light':
                        $shadow = '0 2px 4px rgba(0, 0, 0, 0.05)';
                        break;
                    case 'medium':
                        $shadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                        break;
                    case 'strong':
                        $shadow = '0 6px 10px rgba(0, 0, 0, 0.15)';
                        break;
                }
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { box-shadow: ' . $shadow . '; }';
            }
            
            if (!empty($layout2['icon'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box::before { content: "' . esc_attr($layout2['icon']) . '"; }';
            }
            
            if (!empty($layout2['custom_css'])) {
                $css .= '.atlas-dmsg-layout-2 .atlas-dmsg-box { ' . esc_attr($layout2['custom_css']) . ' }';
            }
        }
        
        // Process Layout 3
        if (isset($settings['layouts'][3])) {
            $layout3 = $settings['layouts'][3];
            
            if (!empty($layout3['gradient_start']) && !empty($layout3['gradient_end'])) {
                $direction = !empty($layout3['gradient_direction']) ? $layout3['gradient_direction'] : '135deg';
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box { background: linear-gradient(' . esc_attr($direction) . ', ' . esc_attr($layout3['gradient_start']) . ', ' . esc_attr($layout3['gradient_end']) . '); }';
            }
            
            if (!empty($layout3['text_color'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box { color: ' . esc_attr($layout3['text_color']) . '; }';
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box strong { color: ' . esc_attr($layout3['text_color']) . '; }';
            }
            
            if (!empty($layout3['border_color'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box { border: 2px solid ' . esc_attr($layout3['border_color']) . '; }';
            }
            
            if (!empty($layout3['accent_color'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box::before { background: ' . esc_attr($layout3['accent_color']) . '; }';
            }
            
            if (!empty($layout3['accent_height'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box::before { height: ' . esc_attr($layout3['accent_height']) . 'px; }';
            }
            
            if (!empty($layout3['icon'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box::after { content: "' . esc_attr($layout3['icon']) . '"; position: absolute; top: 10px; right: 10px; font-size: 18px; }';
            }
            
            if (!empty($layout3['custom_css'])) {
                $css .= '.atlas-dmsg-layout-3 .atlas-dmsg-box { ' . esc_attr($layout3['custom_css']) . ' }';
            }
        }
        
        // Only output if we have CSS
        if (!empty($css)) {
            // Add inline styles to the enqueued stylesheet
            wp_add_inline_style('atlas-dmsg-style', $css);
        }
    }
}