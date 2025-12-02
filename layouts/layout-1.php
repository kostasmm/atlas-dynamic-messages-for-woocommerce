<?php
/**
 * Layout 1 - Original Dashed Style
 * 
 * Displays a message with dashed borders and a colored top bar.
 * Used as default layout style.
 *
 * @package AtlasDynamicMessages
 * @since 2.2.0
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress functions with reference to the global namespace
use function \apply_filters;
use function \esc_attr;

if (!defined('ABSPATH')) exit;

$wrapper_class = apply_filters('atlas_dmsg_layout_1_wrapper_class', 'atlas-dmsg-wrapper atlas-dmsg-layout-1');
$container_class = apply_filters('atlas_dmsg_layout_1_container_class', 'atlas-dmsg-box');
?>
<!-- 
    Layout 1: Dashed Border Style
    Container wrapped with dashed border and top accent bar
-->
<div class="<?php echo esc_attr($wrapper_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>" role="alert" aria-live="polite">
        <!-- Message content will be inserted here by JavaScript -->
    </div>
</div>