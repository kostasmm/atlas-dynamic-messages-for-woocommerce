<?php

namespace PluginAtlas\DynamicMessages;

// Using WordPress functions with reference to the global namespace
use function \delete_option;
use function \delete_site_option;

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('atlas_dmsg_settings');

// For site options in multisite
delete_site_option('atlas_dmsg_settings');