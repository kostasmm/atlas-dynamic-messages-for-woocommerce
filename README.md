# Atlas Dynamic Messages for WooCommerce

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/atlas-dynamic-messages-for-woocommerce)](https://wordpress.org/plugins/atlas-dynamic-messages-for-woocommerce/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/atlas-dynamic-messages-for-woocommerce)](https://wordpress.org/plugins/atlas-dynamic-messages-for-woocommerce/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/atlas-dynamic-messages-for-woocommerce)](https://wordpress.org/plugins/atlas-dynamic-messages-for-woocommerce/)
[![WordPress Tested](https://img.shields.io/badge/WordPress-6.9-green.svg)](https://wordpress.org/)
[![WooCommerce Tested](https://img.shields.io/badge/WooCommerce-10.3.6-purple.svg)](https://woocommerce.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Cache-compatible real-time countdown messages for WooCommerce. Works with LiteSpeed, WP Rocket, Cloudflare & more!

## The ONLY Cache-Compatible Countdown Plugin

While other countdown plugins show outdated times when pages are cached, Atlas Dynamic Messages uses advanced client-side technology to ensure your customers ALWAYS see the correct remaining time - whether you're using LiteSpeed Cache, WP Rocket, W3 Total Cache, WP Super Cache, Cloudflare, Varnish, or any other caching solution.

## Features

- **100% Cache Compatible** - Works flawlessly with ALL caching plugins and CDNs
- **Always Accurate** - Countdowns update in real-time, every second
- **Better Performance** - Cached pages load faster while countdowns remain dynamic
- **No Cache Exclusions Needed** - No need to exclude pages from cache
- **CDN Friendly** - Works perfectly with Cloudflare, BunnyCDN, and others

## Perfect For

- **Shipping Deadlines**: "Order in the next 2 hours 34 minutes for same-day delivery!"
- **Flash Sales**: "Sale ends in 4 hours 12 minutes!"
- **Limited Offers**: "Free shipping for the next 1 hour 45 minutes!"
- **Event Countdowns**: "Black Friday starts in 3 days 14 hours!"

## Core Features

- Create unlimited time-based scenarios
- Set specific days and time ranges for each message
- Display different messages on product pages, cart, and checkout
- Real-time countdown updates every second
- 3 beautiful, customizable layouts
- Clickable messages with custom URLs
- Smart timezone handling
- Midnight-crossing support
- Mobile responsive design

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Installation

1. Upload the `atlas-dynamic-messages-for-woocommerce` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Dynamic Messages to configure your scenarios
4. Use the `{time_remain}` placeholder to show the remaining time in your message

## How It Works

Unlike traditional countdown plugins that render times server-side (and get stuck in cache), our plugin uses a smart REST API approach:

1. **Static Placeholder** - Minimal HTML placeholders that can be safely cached
2. **Client-Side Initialization** - JavaScript detects placeholders after page load
3. **REST API Call** - Single, lightweight API call to fetch active scenarios and server time
4. **Local Computation** - All countdown calculations happen in the browser
5. **Real-Time Updates** - JavaScript updates the countdown every second locally

## Support

- **Documentation**: [pluginatlas.com/atlas-dynamic-messages](https://pluginatlas.com/atlas-dynamic-messages)
- **Support**: [WordPress.org Forums](https://wordpress.org/support/plugin/atlas-dynamic-messages-for-woocommerce/)
- **Issues**: [GitHub Issues](https://github.com/kostasmm/atlas-dynamic-messages-for-woocommerce/issues)

## License

This plugin is licensed under the GPL v2 or later.

---

Made with care by [PluginAtlas](https://pluginatlas.com)
