<?php
/**
 * Layout 2 - Solid Modern Card
 * 
 * Displays a message in a modern card style with solid borders and an emoji icon.
 * Uses a cleaner, more professional appearance.
 *
 * @package AtlasDynamicMessages
 * @since 2.2.0
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress functions with reference to the global namespace
use function \apply_filters;
use function \esc_attr;

if (!defined('ABSPATH')) exit;

$wrapper_class = apply_filters('atlas_dmsg_layout_2_wrapper_class', 'atlas-dmsg-wrapper atlas-dmsg-layout-2');
$container_class = apply_filters('atlas_dmsg_layout_2_container_class', 'atlas-dmsg-box');
?>
<!-- 
    Layout 2: Modern Card Style
    Container with solid borders and emoji icon
-->
<div class="<?php echo esc_attr($wrapper_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>" role="alert" aria-live="polite">
        <!-- Message content will be inserted here by JavaScript -->
    </div>
</div>