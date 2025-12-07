=== Atlas Dynamic Messages for WooCommerce ===
Contributors: malakontask
Tags: woocommerce, countdown, shipping, timer, urgency
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.4.3
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Real-time dynamic countdown messages that work perfectly with ALL caching plugins - LiteSpeed Cache, WP Rocket, W3 Total Cache, Cloudflare, and more!

== Description ==

**🚀 The ONLY WooCommerce countdown plugin that displays accurate, real-time countdowns even with page caching enabled!**

While other countdown plugins show outdated times when pages are cached, Atlas Dynamic Messages uses advanced client-side technology to ensure your customers ALWAYS see the correct remaining time - whether you're using LiteSpeed Cache, WP Rocket, W3 Total Cache, WP Super Cache, Cloudflare, Varnish, or any other caching solution.

**Why Atlas Dynamic Messages is Different:**

Unlike traditional countdown plugins that render times server-side (and get stuck in cache), our plugin uses a smart REST API approach that bypasses cache entirely. This means:

✅ **100% Cache Compatible** - Works flawlessly with ALL caching plugins and CDNs
✅ **Always Accurate** - Countdowns update in real-time, every second
✅ **Better Performance** - Cached pages load faster while countdowns remain dynamic
✅ **No Cache Exclusions Needed** - No need to exclude pages from cache
✅ **CDN Friendly** - Works perfectly with Cloudflare, BunnyCDN, and others

**Perfect For:**

* **Shipping Deadlines**: "Order in the next 2 hours 34 minutes for same-day delivery!"
* **Flash Sales**: "Sale ends in 4 hours 12 minutes!"
* **Limited Offers**: "Free shipping for the next 1 hour 45 minutes!"
* **Event Countdowns**: "Black Friday starts in 3 days 14 hours!"

**Core Features:**

* Create unlimited time-based scenarios
* Set specific days and time ranges for each message
* Display different messages on product pages, cart, and checkout
* Real-time countdown updates every second
* 3 beautiful, customizable layouts
* Clickable messages with custom URLs
* Smart timezone handling
* Midnight-crossing support
* Mobile responsive design

**Use Cases:**

* "Order within the next {time_remain} for same-day shipping!"
* "Free shipping available for {time_remain} more!"
* "Flash sale ends in {time_remain}!"
* "Order now, get it by tomorrow - offer valid for {time_remain}"

== Installation ==

1. Upload the `atlas-dynamic-messages-for-woocommerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Dynamic Messages to configure your scenarios
4. Create your first scenario by setting active days, time period, and message text
5. Use the {time_remain} placeholder to show the remaining time in your message

== Frequently Asked Questions ==

= How does this plugin work with caching? =

**This is our secret sauce!** Unlike other countdown plugins, Atlas Dynamic Messages uses client-side JavaScript and REST API calls to fetch real-time data. This means:
- The countdown timer bypasses ALL page caching
- Works with LiteSpeed Cache, WP Rocket, W3 Total Cache, WP Super Cache, Cloudflare, etc.
- Includes dedicated LiteSpeed Cache headers for optimal compatibility
- No need to exclude pages from cache
- Your pages stay cached for optimal performance
- Countdowns remain 100% accurate and update every second

= Will this slow down my site? =

No! In fact, it can make your site faster. Because the countdown is handled client-side, your pages can be fully cached. The lightweight JavaScript only loads when needed and makes a single API call to get the current time.

= Does this plugin require WooCommerce? =

Yes, this plugin is designed specifically for WooCommerce stores and requires WooCommerce to be installed and activated.

= Can I display different messages on different pages? =

Yes! Each scenario can be configured to display on product pages, cart page, checkout page, or any combination of these.

= Can I make messages clickable? =

Yes, you can add a URL to any message to make it clickable and redirect customers to any page.

= Will the messages display correctly on mobile devices? =

Yes, all message layouts are fully responsive and will display properly on all devices.

= How do I show the remaining time in my message? =

Simply include the {time_remain} placeholder in your message text, and it will be automatically replaced with the remaining time until the end of the active period.

= Can I create scenarios that cross midnight? =

Yes! The plugin automatically detects when a time range crosses midnight (e.g., 23:30 to 01:00) and handles it intelligently. You'll see a helpful notification when setting up such scenarios, and the message will display correctly based on the selected start day.

= How do midnight-crossing scenarios work? =

When you set a time range that crosses midnight (e.g., Tuesday 23:30 to 01:00), the message will appear on Tuesday starting at 23:30 and continue until 01:00 on Wednesday. The scenario is based on the start day selection.

== Technical Details for Developers ==

**How the Cache-Proof Technology Works:**

1. **Static Placeholder**: The plugin injects minimal HTML placeholders that can be safely cached
2. **Client-Side Initialization**: JavaScript detects these placeholders after page load
3. **REST API Call**: Makes a single, lightweight API call to fetch active scenarios and server time
4. **Local Computation**: All countdown calculations happen in the browser
5. **Real-Time Updates**: JavaScript updates the countdown every second locally

This architecture ensures:
- Pages remain fully cacheable
- No PHP execution on cached pages
- Accurate countdowns regardless of cache age
- Minimal server load (one API call per page load)
- Compatible with all caching layers (plugin, server, CDN)
- LiteSpeed Cache compatibility with dedicated X-LiteSpeed-Cache-Control headers
- Cache-busting parameters to bypass aggressive server-side caching

== Screenshots ==

1. Real-time countdown message on a cached product page
2. Admin settings page with scenario configuration
3. Layout customization options
4. Three different layout styles to choose from
5. Midnight crossing notification and day selector highlighting
6. Cache-compatible countdown updating in real-time

== Changelog ==

= 2.4.3 =
* Updated: Compatibility with WordPress 6.9
* Updated: Compatibility with WooCommerce 10.3.6

= 2.4.2 =
* NEW: Added LiteSpeed Cache compatibility with dedicated no-cache headers
* NEW: Added cache-busting parameter to REST API calls for better cache bypass
* Fixed: Plugin tags reduced to 5 as per WordPress.org guidelines

= 2.4.1 =
* NEW: Enhanced marketing to highlight cache-compatible real-time countdown technology
* Updated: Compatibility with WordPress 6.8.2
* Updated: Compatibility with WooCommerce 9.5
* Improved: Documentation to emphasize unique cache-bypass architecture
* Tested: Full compatibility verification with latest WordPress and WooCommerce versions

= 2.4.0 =
* Fixed: Proper implementation of wp_add_inline_style() for custom CSS
* Fixed: Removed unnecessary load_plugin_textdomain() for WordPress.org compatibility
* Fixed: Added direct file access protection to all PHP files
* Fixed: Removed non-English comments from JavaScript files
* Improved: Code compliance with WordPress.org plugin guidelines

= 2.3.0 =
* NEW: Smart midnight crossing detection with visual notifications
* NEW: Enhanced day selector with highlighting for complex scenarios
* Improved: Time selection, mobile responsiveness, and theme compatibility
* Fixed: Various CSS styling issues and JavaScript performance improvements

= 2.2.0 =
* Rebranded to "Atlas Dynamic Messages for WooCommerce"
* Improved code organization and WooCommerce HPOS compatibility

= 2.1.0 - 2.0.0 =
* Added third layout style, multiple scenarios support, and modern UI
* Complete redesign with new features and improved performance

= 1.5.0 =
* Initial public release

== Upgrade Notice ==

= 2.4.0 =
Important update for WordPress.org compliance. Fixes CSS implementation, removes deprecated functions, and improves security with direct file access protection.

= 2.3.0 =
Major update with midnight crossing detection and enhanced user experience. Includes performance improvements and better theme compatibility.