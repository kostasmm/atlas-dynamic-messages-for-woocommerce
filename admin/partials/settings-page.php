<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
?>
<div class="wrap atlas-dmsg-admin-panel">
    <h1><?php esc_html_e('Dynamic Messages Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></h1>
    
    <div class="atlas-dmsg-admin-header">
        <div class="atlas-dmsg-logo">
            <span class="dashicons dashicons-megaphone"></span>
            <span class="atlas-dmsg-version">v<?php echo esc_html(ATLAS_DMSG_VERSION); ?></span>
        </div>
        <div class="atlas-dmsg-header-text">
            <p><?php esc_html_e('Configure dynamic shipping messages that display on your WooCommerce store.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
        </div>
    </div>
    
    <div class="notice notice-success" style="margin: 20px 0;">
        <p><strong>🚀 <?php esc_html_e('Cache-Compatible Technology', 'atlas-dynamic-messages-for-woocommerce'); ?></strong></p>
        <p><?php esc_html_e('Your countdown messages will display accurate, real-time updates even with page caching enabled! This plugin works perfectly with WP Rocket, W3 Total Cache, Cloudflare, and all other caching solutions - no configuration needed.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
    </div>
    
    <form method="post" action="options.php">
        <?php 
        settings_fields('atlas_dmsg_settings_group');
        $settings = get_option('atlas_dmsg_settings');
        $scenarios = isset($settings['scenarios']) ? $settings['scenarios'] : array();
        $time_offset = intval($settings['time_offset'] ?? 0);
    
        wp_nonce_field('atlas_dmsg_save_settings', 'atlas_dmsg_nonce');
        ?>
        
        <div id="atlas-dmsg-tabs">
            <ul class="nav-tab-wrapper">
                <li><a href="#general-settings" class="nav-tab"><?php esc_html_e('General Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
                <li><a href="#scenarios" class="nav-tab"><?php esc_html_e('Scenarios', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
                <li><a href="#layouts-settings" class="nav-tab"><?php esc_html_e('Layouts Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
                <li><a href="#help" class="nav-tab"><?php esc_html_e('Help & Support', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
            </ul>
            
            <!-- Always visible submit button -->
            <div class="atlas-dmsg-submit-wrapper" style="margin: 20px 0; padding: 15px; background: #f8f8f8; border-radius: 5px; text-align: right;">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save All Settings', 'atlas-dynamic-messages-for-woocommerce'); ?>" style="padding: 8px 20px; font-size: 14px; height: auto;">
            </div>
            
            <!-- General Settings Tab -->
            <div id="general-settings" class="atlas-dmsg-tab-content">
                <h2><?php esc_html_e('Message Scenarios', 'atlas-dynamic-messages-for-woocommerce'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="time-offset"><?php esc_html_e('Time Offset (hours)', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                            <span class="atlas-dmsg-tooltip" title="<?php esc_attr_e('Adjust server time by adding or subtracting hours. Use negative values to subtract hours.', 'atlas-dynamic-messages-for-woocommerce'); ?>">?</span>
                        </th>
                        <td>
                            <input type="number" id="time-offset" name="atlas_dmsg_settings[time_offset]" 
                                value="<?php echo esc_attr($time_offset); ?>" min="-12" max="12" step="1" class="atlas-dmsg-number-input">
                            <p class="description">
                                <?php esc_html_e('Current server time (after offset):', 'atlas-dynamic-messages-for-woocommerce'); ?>
<span id="atlas-dmsg-server-time"><?php 
    $timezone = new DateTimeZone(wp_timezone_string());
    $server_datetime = new DateTime('now', $timezone);
    if ($time_offset != 0) {
        $offset_interval = new DateInterval('PT' . abs($time_offset) . 'H');
        if ($time_offset < 0) {
            $server_datetime->sub($offset_interval);
        } else {
            $server_datetime->add($offset_interval);
        }
    }
    echo esc_html($server_datetime->format('H:i:s'));
?></span>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Scenarios Tab -->
            <div id="scenarios" class="atlas-dmsg-tab-content">
                <h2><?php esc_html_e('Shipping Scenarios', 'atlas-dynamic-messages-for-woocommerce'); ?></h2>
                
                <div class="atlas-dmsg-scenarios-help">
                    <p><?php esc_html_e('Create and configure your shipping message scenarios below. Each scenario has specific days and times when it will be active.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    <p><?php esc_html_e('Use the {time_remain} placeholder in your message to display a countdown timer.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                </div>
                
                <div id="atlas-dmsg-scenarios-container">
                    <?php foreach ($scenarios as $index => $scenario): ?>
                    <div class="atlas-dmsg-scenario-box" data-index="<?php echo esc_attr($index); ?>">
                        <div class="atlas-dmsg-scenario-header">
                            <h3><?php esc_html_e('Scenario', 'atlas-dynamic-messages-for-woocommerce'); ?> #<span class="scenario-number"><?php echo esc_html($index + 1); ?></span></h3>
                            <div class="atlas-dmsg-scenario-actions">
                                <label class="atlas-dmsg-toggle">
                                    <!-- Hidden field to always send the value, even if the checkbox is unchecked -->
                                    <input type="hidden" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][active]" value="0">
                                    <input type="checkbox" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][active]" 
                                        value="1" <?php checked(isset($scenario['active']) ? $scenario['active'] : 1, 1); ?>>
                                    <span class="atlas-dmsg-toggle-slider"></span>
                                    <span class="atlas-dmsg-toggle-label"><?php esc_html_e('Active', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                                </label>
                                <button type="button" class="button remove-scenario">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php esc_html_e('Remove', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="atlas-dmsg-scenario-content">
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e('Display Location', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <div class="atlas-dmsg-checkbox-group">
                                            <?php 
                                            $display_location = isset($scenario['display_location']) ? $scenario['display_location'] : array('product');
                                            ?>
                                            <label class="atlas-dmsg-checkbox">
                                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][display_location][]" 
                                                    value="product" <?php checked(in_array('product', $display_location)); ?>>
                                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Product Pages', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                                            </label>
                                            <label class="atlas-dmsg-checkbox">
                                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][display_location][]" 
                                                    value="cart" <?php checked(in_array('cart', $display_location)); ?>>
                                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Cart Page', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                                            </label>
                                            <label class="atlas-dmsg-checkbox">
                                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][display_location][]" 
                                                    value="checkout" <?php checked(in_array('checkout', $display_location)); ?>>
                                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Checkout Page', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Layout Style', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <?php $current_layout = isset($scenario['layout']) ? absint($scenario['layout']) : 1; ?>
                                        <div class="atlas-dmsg-layout-selector">
                                            <div class="atlas-dmsg-layout-option <?php echo ($current_layout == 1) ? 'selected' : ''; ?>">
                                                <label>
                                                    <input type="radio" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][layout]" 
                                                        value="1" <?php checked($current_layout, 1); ?>>
                                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-1-preview">
                                                        <div class="atlas-dmsg-preview-content">
                                                            <?php esc_html_e('Dashed Border Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="atlas-dmsg-layout-option <?php echo ($current_layout == 2) ? 'selected' : ''; ?>">
                                                <label>
                                                    <input type="radio" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][layout]" 
                                                        value="2" <?php checked($current_layout, 2); ?>>
                                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-2-preview">
                                                        <div class="atlas-dmsg-preview-content">
                                                            <?php esc_html_e('Modern Card Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="atlas-dmsg-layout-option <?php echo ($current_layout == 3) ? 'selected' : ''; ?>">
                                                <label>
                                                    <input type="radio" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][layout]" 
                                                        value="3" <?php checked($current_layout, 3); ?>>
                                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-3-preview">
                                                        <div class="atlas-dmsg-preview-content">
                                                            <?php esc_html_e('Gradient Alert Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Active Days', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <div class="atlas-dmsg-days-selector">
                                            <?php foreach (array(
                                                0 => __('Sunday', 'atlas-dynamic-messages-for-woocommerce'),
                                                1 => __('Monday', 'atlas-dynamic-messages-for-woocommerce'),
                                                2 => __('Tuesday', 'atlas-dynamic-messages-for-woocommerce'),
                                                3 => __('Wednesday', 'atlas-dynamic-messages-for-woocommerce'),
                                                4 => __('Thursday', 'atlas-dynamic-messages-for-woocommerce'),
                                                5 => __('Friday', 'atlas-dynamic-messages-for-woocommerce'),
                                                6 => __('Saturday', 'atlas-dynamic-messages-for-woocommerce')
                                            ) as $day => $label): ?>
                                            <label class="atlas-dmsg-day-button <?php echo (in_array($day, $scenario['days'])) ? 'selected' : ''; ?>">
                                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][days][]" 
                                                    value="<?php echo esc_attr($day); ?>" <?php checked(in_array($day, $scenario['days'])); ?>>
                                                <span><?php echo esc_html(substr($label, 0, 3)); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Time Period', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <div class="atlas-dmsg-time-inputs">
                                            <div class="atlas-dmsg-time-field">
                                                <label><?php esc_html_e('Start', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                                                <input type="time" class="atlas-dmsg-time-input" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][start_time]" 
                                                    value="<?php echo esc_attr($scenario['start_time']); ?>" required>
                                            </div>
                                            <div class="atlas-dmsg-time-separator">-</div>
                                            <div class="atlas-dmsg-time-field">
                                                <label><?php esc_html_e('End', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                                                <input type="time" class="atlas-dmsg-time-input" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][end_time]" 
                                                    value="<?php echo esc_attr($scenario['end_time']); ?>" required>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Message', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <textarea name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][message]" 
                                            rows="3" class="large-text atlas-dmsg-message-input" required><?php echo esc_textarea($scenario['message']); ?></textarea>
                                        <div class="atlas-dmsg-message-preview">
                                            <label><?php esc_html_e('Preview:', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                                            <div class="atlas-dmsg-preview-box atlas-dmsg-layout-<?php echo esc_attr($current_layout); ?>">
                                                <?php echo wp_kses_post(wpautop(str_replace('{time_remain}', '<strong>2 hours, 30 minutes and 45 seconds</strong>', $scenario['message']))); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Link URL (optional)', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <input type="url" name="atlas_dmsg_settings[scenarios][<?php echo esc_attr($index); ?>][url]" 
                                            value="<?php echo esc_url(isset($scenario['url']) ? $scenario['url'] : ''); ?>" 
                                            class="regular-text" placeholder="https://example.com">
                                        <p class="description"><?php esc_html_e('Make message clickable and redirect to this URL. Leave empty for non-clickable message.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="atlas-dmsg-add-scenario-wrapper">
                    <button type="button" id="add-scenario" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e('Add New Scenario', 'atlas-dynamic-messages-for-woocommerce'); ?>
                    </button>
                </div>
            </div>

<!-- Layouts Settings Tab -->
<div id="layouts-settings" class="atlas-dmsg-tab-content">
    <h2><?php esc_html_e('Layouts Customization', 'atlas-dynamic-messages-for-woocommerce'); ?></h2>
    
    <div class="atlas-dmsg-layouts-intro">
        <p><?php esc_html_e('Customize the appearance of your message layouts. Changes will affect all messages using the selected layout.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
    </div>
    
    <div class="atlas-dmsg-layouts-tabs">
        <ul class="atlas-dmsg-layouts-tabs-nav">
            <li><a href="#layout-1-settings"><?php esc_html_e('Dashed Style', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
            <li><a href="#layout-2-settings"><?php esc_html_e('Modern Card', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
            <li><a href="#layout-3-settings"><?php esc_html_e('Gradient Alert', 'atlas-dynamic-messages-for-woocommerce'); ?></a></li>
        </ul>
        
        <!-- Layout 1 Settings -->
        <div id="layout-1-settings" class="atlas-dmsg-layout-settings-panel">
            <h3><?php esc_html_e('Dashed Border Style Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Background Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][1][bg_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][1]['bg_color']) ? $settings['layouts'][1]['bg_color'] : '#fef9e7'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Text Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][1][text_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][1]['text_color']) ? $settings['layouts'][1]['text_color'] : '#4b2900'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Border Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][1][border_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][1]['border_color']) ? $settings['layouts'][1]['border_color'] : '#ffa800'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Accent Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][1][accent_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][1]['accent_color']) ? $settings['layouts'][1]['accent_color'] : '#b53300'); ?>">
                        <p class="description"><?php esc_html_e('Color used for highlighted elements and countdown timer', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Border Style', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <select name="atlas_dmsg_settings[layouts][1][border_style]">
                            <option value="dashed" <?php selected(isset($settings['layouts'][1]['border_style']) ? $settings['layouts'][1]['border_style'] : 'dashed', 'dashed'); ?>><?php esc_html_e('Dashed', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="solid" <?php selected(isset($settings['layouts'][1]['border_style']) ? $settings['layouts'][1]['border_style'] : 'dashed', 'solid'); ?>><?php esc_html_e('Solid', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="dotted" <?php selected(isset($settings['layouts'][1]['border_style']) ? $settings['layouts'][1]['border_style'] : 'dashed', 'dotted'); ?>><?php esc_html_e('Dotted', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Top Icon/Emoji', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="regular-text" name="atlas_dmsg_settings[layouts][1][icon]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][1]['icon']) ? $settings['layouts'][1]['icon'] : ''); ?>" placeholder="🚚 🕒 ⏰">
                        <p class="description"><?php esc_html_e('Leave empty for default style. Example emojis: 🚚 🕒 ⏰ 📦 🛒', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom CSS', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <textarea name="atlas_dmsg_settings[layouts][1][custom_css]" rows="4" class="large-text code"><?php echo esc_textarea(isset($settings['layouts'][1]['custom_css']) ? $settings['layouts'][1]['custom_css'] : ''); ?></textarea>
                        <p class="description"><?php esc_html_e('Add custom CSS rules for this layout', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
    <th></th>
    <td>
        <button type="button" class="button atlas-dmsg-reset-layout" data-layout="1">
            <span class="dashicons dashicons-image-rotate"></span>
            <?php esc_html_e('Reset to Default Settings', 'atlas-dynamic-messages-for-woocommerce'); ?>
        </button>
    </td>
</tr>
            </table>
            
            <div class="atlas-dmsg-layout-preview-box">
                <h4><?php esc_html_e('Preview', 'atlas-dynamic-messages-for-woocommerce'); ?></h4>
                <div class="atlas-dmsg-live-preview atlas-dmsg-layout-1-preview">
                    <div class="atlas-dmsg-preview-content">
                        <?php esc_html_e('Your customized message will appear like this. Time remaining: ', 'atlas-dynamic-messages-for-woocommerce'); ?><strong>2 hours, 30 minutes and 45 seconds</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Layout 2 Settings -->
        <div id="layout-2-settings" class="atlas-dmsg-layout-settings-panel">
            <h3><?php esc_html_e('Modern Card Style Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Background Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][2][bg_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['bg_color']) ? $settings['layouts'][2]['bg_color'] : '#ffffff'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Text Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][2][text_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['text_color']) ? $settings['layouts'][2]['text_color'] : '#2c3e50'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Border Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][2][border_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['border_color']) ? $settings['layouts'][2]['border_color'] : '#2c3e50'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Accent Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][2][accent_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['accent_color']) ? $settings['layouts'][2]['accent_color'] : '#2980b9'); ?>">
                        <p class="description"><?php esc_html_e('Color used for highlighted elements and countdown timer', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Shadow Intensity', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <select name="atlas_dmsg_settings[layouts][2][shadow_intensity]">
                            <option value="light" <?php selected(isset($settings['layouts'][2]['shadow_intensity']) ? $settings['layouts'][2]['shadow_intensity'] : 'medium', 'light'); ?>><?php esc_html_e('Light', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="medium" <?php selected(isset($settings['layouts'][2]['shadow_intensity']) ? $settings['layouts'][2]['shadow_intensity'] : 'medium', 'medium'); ?>><?php esc_html_e('Medium', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="strong" <?php selected(isset($settings['layouts'][2]['shadow_intensity']) ? $settings['layouts'][2]['shadow_intensity'] : 'medium', 'strong'); ?>><?php esc_html_e('Strong', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Icon/Emoji', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="regular-text" name="atlas_dmsg_settings[layouts][2][icon]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['icon']) ? $settings['layouts'][2]['icon'] : '🚚'); ?>">
                        <p class="description"><?php esc_html_e('Icon displayed at the top of the message. Example emojis: 🚚 🕒 ⏰ 📦 🛒', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Border Radius', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="range" min="0" max="20" step="1" name="atlas_dmsg_settings[layouts][2][border_radius]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][2]['border_radius']) ? $settings['layouts'][2]['border_radius'] : '8'); ?>"
                            oninput="this.nextElementSibling.value = this.value + 'px'">
                        <output><?php echo esc_html(isset($settings['layouts'][2]['border_radius']) ? $settings['layouts'][2]['border_radius'] : '8'); ?>px</output>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom CSS', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <textarea name="atlas_dmsg_settings[layouts][2][custom_css]" rows="4" class="large-text code"><?php echo esc_textarea(isset($settings['layouts'][2]['custom_css']) ? $settings['layouts'][2]['custom_css'] : ''); ?></textarea>
                        <p class="description"><?php esc_html_e('Add custom CSS rules for this layout', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
    <th></th>
    <td>
        <button type="button" class="button atlas-dmsg-reset-layout" data-layout="2">
            <span class="dashicons dashicons-image-rotate"></span>
            <?php esc_html_e('Reset to Default Settings', 'atlas-dynamic-messages-for-woocommerce'); ?>
        </button>
    </td>
</tr>
            </table>
            
            <div class="atlas-dmsg-layout-preview-box">
                <h4><?php esc_html_e('Preview', 'atlas-dynamic-messages-for-woocommerce'); ?></h4>
                <div class="atlas-dmsg-live-preview atlas-dmsg-layout-2-preview">
                    <div class="atlas-dmsg-preview-content">
                        <?php esc_html_e('Your customized message will appear like this. Time remaining: ', 'atlas-dynamic-messages-for-woocommerce'); ?><strong>2 hours, 30 minutes and 45 seconds</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Layout 3 Settings -->
        <div id="layout-3-settings" class="atlas-dmsg-layout-settings-panel">
            <h3><?php esc_html_e('Gradient Alert Style Settings', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Gradient Start Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][3][gradient_start]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['gradient_start']) ? $settings['layouts'][3]['gradient_start'] : '#ffe8d4'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Gradient End Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][3][gradient_end]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['gradient_end']) ? $settings['layouts'][3]['gradient_end'] : '#ffd8b2'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Text Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][3][text_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['text_color']) ? $settings['layouts'][3]['text_color'] : '#d32f2f'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Border Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][3][border_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['border_color']) ? $settings['layouts'][3]['border_color'] : '#ff6b6b'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Accent Bar Color', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="atlas-dmsg-color-picker" name="atlas_dmsg_settings[layouts][3][accent_color]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['accent_color']) ? $settings['layouts'][3]['accent_color'] : '#ff6b6b'); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Gradient Direction', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <select name="atlas_dmsg_settings[layouts][3][gradient_direction]">
                            <option value="to right" <?php selected(isset($settings['layouts'][3]['gradient_direction']) ? $settings['layouts'][3]['gradient_direction'] : '135deg', 'to right'); ?>><?php esc_html_e('Horizontal', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="to bottom" <?php selected(isset($settings['layouts'][3]['gradient_direction']) ? $settings['layouts'][3]['gradient_direction'] : '135deg', 'to bottom'); ?>><?php esc_html_e('Vertical', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                            <option value="135deg" <?php selected(isset($settings['layouts'][3]['gradient_direction']) ? $settings['layouts'][3]['gradient_direction'] : '135deg', '135deg'); ?>><?php esc_html_e('Diagonal', 'atlas-dynamic-messages-for-woocommerce'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Icon/Emoji', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="text" class="regular-text" name="atlas_dmsg_settings[layouts][3][icon]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['icon']) ? $settings['layouts'][3]['icon'] : ''); ?>" placeholder="⚠️ ⏰ 🔔">
                        <p class="description"><?php esc_html_e('Leave empty for default style. Example emojis: ⚠️ ⏰ 🔔 🛎️ 📣', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Accent Bar Height', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <input type="range" min="1" max="10" step="1" name="atlas_dmsg_settings[layouts][3][accent_height]" 
                            value="<?php echo esc_attr(isset($settings['layouts'][3]['accent_height']) ? $settings['layouts'][3]['accent_height'] : '4'); ?>"
                            oninput="this.nextElementSibling.value = this.value + 'px'">
                        <output><?php echo esc_html(isset($settings['layouts'][3]['accent_height']) ? $settings['layouts'][3]['accent_height'] : '4'); ?>px</output>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom CSS', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <textarea name="atlas_dmsg_settings[layouts][3][custom_css]" rows="4" class="large-text code"><?php echo esc_textarea(isset($settings['layouts'][3]['custom_css']) ? $settings['layouts'][3]['custom_css'] : ''); ?></textarea>
                        <p class="description"><?php esc_html_e('Add custom CSS rules for this layout', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <tr>
    <th></th>
    <td>
        <button type="button" class="button atlas-dmsg-reset-layout" data-layout="3">
            <span class="dashicons dashicons-image-rotate"></span>
            <?php esc_html_e('Reset to Default Settings', 'atlas-dynamic-messages-for-woocommerce'); ?>
        </button>
    </td>
</tr>
            </table>
            
            <div class="atlas-dmsg-layout-preview-box">
                <h4><?php esc_html_e('Preview', 'atlas-dynamic-messages-for-woocommerce'); ?></h4>
                <div class="atlas-dmsg-live-preview atlas-dmsg-layout-3-preview">
                    <div class="atlas-dmsg-preview-content">
                        <?php esc_html_e('Your customized message will appear like this. Time remaining: ', 'atlas-dynamic-messages-for-woocommerce'); ?><strong>2 hours, 30 minutes and 45 seconds</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help & Support Tab -->
            <div id="help" class="atlas-dmsg-tab-content">
                <h2><?php esc_html_e('Help & Support', 'atlas-dynamic-messages-for-woocommerce'); ?></h2>
                
                <div class="atlas-dmsg-help-section">
                    <h3><?php esc_html_e('How to Use Dynamic Messages', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
                    <div class="atlas-dmsg-help-content">
                        <p><?php esc_html_e('Dynamic Messages allows you to display dynamic shipping messages on your WooCommerce store. Here\'s how to use it:', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                        <ol>
                            <li><?php esc_html_e('Create scenarios with specific days and times when they should be active', 'atlas-dynamic-messages-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Choose where to display each scenario (product pages, cart, checkout)', 'atlas-dynamic-messages-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Select a layout style for each scenario that matches your site design', 'atlas-dynamic-messages-for-woocommerce'); ?></li>
                            <li><?php esc_html_e('Customize your message with the {time_remain} placeholder to show a countdown', 'atlas-dynamic-messages-for-woocommerce'); ?></li>
                        </ol>
                    </div>
                </div>
                
                <div class="atlas-dmsg-help-section">
                    <h3><?php esc_html_e('Message Placeholders', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
                    <div class="atlas-dmsg-help-content">
                        <p><?php esc_html_e('You can use the following placeholders in your messages:', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                        <ul>
                            <li><code>{time_remain}</code> - <?php esc_html_e('Shows the remaining time until the end of the active period', 'atlas-dynamic-messages-for-woocommerce'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <div class="atlas-dmsg-help-section">
                    <h3><?php esc_html_e('Need Support?', 'atlas-dynamic-messages-for-woocommerce'); ?></h3>
                    <div class="atlas-dmsg-help-content">
                        <p><?php esc_html_e('If you need help with Dynamic Messages, feel free to contact the developer:', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                        <p><strong>Email:</strong> <a href="mailto:info@pluginatlas.com">info@pluginatlas.com</a></p>
                    </div>
                </div>
            </div>

</form>
<!-- Scenario Template -->
<script type="text/template" id="atlas-dmsg-scenario-template">
    <div class="atlas-dmsg-scenario-box" data-index="{{index}}">
        <div class="atlas-dmsg-scenario-header">
            <h3><?php esc_html_e('Scenario', 'atlas-dynamic-messages-for-woocommerce'); ?> #<span class="scenario-number">{{number}}</span></h3>
            <div class="atlas-dmsg-scenario-actions">
                <label class="atlas-dmsg-toggle">
                    <!-- Hidden field for active/inactive status -->
                    <input type="hidden" name="atlas_dmsg_settings[scenarios][{{index}}][active]" value="0">
                    <input type="checkbox" name="atlas_dmsg_settings[scenarios][{{index}}][active]" value="1" checked>
                    <span class="atlas-dmsg-toggle-slider"></span>
                    <span class="atlas-dmsg-toggle-label"><?php esc_html_e('Active', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                </label>
                <button type="button" class="button remove-scenario">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Remove', 'atlas-dynamic-messages-for-woocommerce'); ?>
                </button>
            </div>
        </div>
        <div class="atlas-dmsg-scenario-content">
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Display Location', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <div class="atlas-dmsg-checkbox-group">
                            <label class="atlas-dmsg-checkbox">
                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][{{index}}][display_location][]" value="product" checked>
                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Product Pages', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                            </label>
                            <label class="atlas-dmsg-checkbox">
                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][{{index}}][display_location][]" value="cart">
                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Cart Page', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                            </label>
                            <label class="atlas-dmsg-checkbox">
                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][{{index}}][display_location][]" value="checkout">
                                <span class="atlas-dmsg-checkbox-text"><?php esc_html_e('Checkout Page', 'atlas-dynamic-messages-for-woocommerce'); ?></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Layout Style', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <div class="atlas-dmsg-layout-selector">
                            <div class="atlas-dmsg-layout-option selected">
                                <label>
                                    <input type="radio" name="atlas_dmsg_settings[scenarios][{{index}}][layout]" value="1" checked>
                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-1-preview">
                                        <div class="atlas-dmsg-preview-content">
                                            <?php esc_html_e('Dashed Border Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="atlas-dmsg-layout-option">
                                <label>
                                    <input type="radio" name="atlas_dmsg_settings[scenarios][{{index}}][layout]" value="2">
                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-2-preview">
                                        <div class="atlas-dmsg-preview-content">
                                            <?php esc_html_e('Modern Card Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="atlas-dmsg-layout-option">
                                <label>
                                    <input type="radio" name="atlas_dmsg_settings[scenarios][{{index}}][layout]" value="3">
                                    <div class="atlas-dmsg-layout-preview atlas-dmsg-layout-3-preview">
                                        <div class="atlas-dmsg-preview-content">
                                            <?php esc_html_e('Gradient Alert Style', 'atlas-dynamic-messages-for-woocommerce'); ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Active Days', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <div class="atlas-dmsg-days-selector">
                            <?php foreach (array(
                                0 => __('Sunday', 'atlas-dynamic-messages-for-woocommerce'),
                                1 => __('Monday', 'atlas-dynamic-messages-for-woocommerce'),
                                2 => __('Tuesday', 'atlas-dynamic-messages-for-woocommerce'),
                                3 => __('Wednesday', 'atlas-dynamic-messages-for-woocommerce'),
                                4 => __('Thursday', 'atlas-dynamic-messages-for-woocommerce'),
                                5 => __('Friday', 'atlas-dynamic-messages-for-woocommerce'),
                                6 => __('Saturday', 'atlas-dynamic-messages-for-woocommerce')
                            ) as $day => $label): ?>
                            <label class="atlas-dmsg-day-button">
                                <input type="checkbox" name="atlas_dmsg_settings[scenarios][{{index}}][days][]" value="<?php echo esc_attr($day); ?>">
                                <span><?php echo esc_html(substr($label, 0, 3)); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Time Period', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <div class="atlas-dmsg-time-inputs">
                            <div class="atlas-dmsg-time-field">
                                <label><?php esc_html_e('Start', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                                <input type="time" class="atlas-dmsg-time-input" name="atlas_dmsg_settings[scenarios][{{index}}][start_time]" required>
                            </div>
                            <div class="atlas-dmsg-time-separator">-</div>
                            <div class="atlas-dmsg-time-field">
                                <label><?php esc_html_e('End', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                                <input type="time" class="atlas-dmsg-time-input" name="atlas_dmsg_settings[scenarios][{{index}}][end_time]" required>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Message', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                    <td>
                        <textarea name="atlas_dmsg_settings[scenarios][{{index}}][message]" rows="3" class="large-text atlas-dmsg-message-input" required></textarea>
                        <div class="atlas-dmsg-message-preview">
                            <label><?php esc_html_e('Preview:', 'atlas-dynamic-messages-for-woocommerce'); ?></label>
                            <div class="atlas-dmsg-preview-box atlas-dmsg-layout-1">
                                <p><?php esc_html_e('Your message preview will appear here. Use {time_remain} placeholder to show countdown.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                                    <th><?php esc_html_e('Link URL (optional)', 'atlas-dynamic-messages-for-woocommerce'); ?></th>
                                    <td>
                                        <input type="url" name="atlas_dmsg_settings[scenarios][{{index}}][url]" 
                                            value="" 
                                            class="regular-text" placeholder="https://example.com">
                                        <p class="description"><?php esc_html_e('Make message clickable and redirect to this URL. Leave empty for non-clickable message.', 'atlas-dynamic-messages-for-woocommerce'); ?></p>
                                    </td>
                                </tr>
            </table>
        </div>
    </div>
</script>