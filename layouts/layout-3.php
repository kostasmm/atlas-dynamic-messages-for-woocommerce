<?php
/**
 * Layout 3 - Gradient Alert Style
 * 
 * Displays a message with gradient background and a colored accent bar.
 * Used for high-visibility messages that need attention.
 *
 * @package AtlasDynamicMessages
 * @since 2.2.0
 */

namespace PluginAtlas\DynamicMessages;

// Using WordPress functions with reference to the global namespace
use function \apply_filters;
use function \esc_attr;

if (!defined('ABSPATH')) exit;

$wrapper_class = apply_filters('atlas_dmsg_layout_3_wrapper_class', 'atlas-dmsg-wrapper atlas-dmsg-layout-3');
$container_class = apply_filters('atlas_dmsg_layout_3_container_class', 'atlas-dmsg-box');
?>
<!-- 
    Layout 3: Gradient Alert Style
    Container with gradient background and colored top accent
-->
<div class="<?php echo esc_attr($wrapper_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>" role="alert" aria-live="polite">
        <!-- Message content will be inserted here by JavaScript -->
    </div>
</div>