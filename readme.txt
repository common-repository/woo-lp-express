=== WooCommerce LP Express ===
Requires at least: 4.4
Tested up to: 4.9.4
Requires PHP: 5.3
Stable tag: 2.0.4
Tags: lp-express, woocommerce, shipping, lp-express woocommerce, lp express
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

LP-EXPRESS shipping plugin for WordPress and WooCommerce that allows you to use all "Lietuvos Paštas" shipping methods.
This plugin has number of features like 'cronjob' methods that allows you to create manifests automatically (call courier),
also fixed prices and terminal type settings, automatic label generation by products dimensions.

Features:

* Label manual generation
* Label automatic generation by products dimensions
* Manifest automatic and manual generation
* Fixed or automatic prices
* Automatic terminal synchronization
* Organized shipping rules

IMPORTANT NOTE!
Please make sure you have added dimensions inside product shipping settings.

For more information please contact: pagalba@noriusvetaines.lt

== Installation ==

1. Upload plugin files the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Write all LP-EXPRESS required authorization data in plugin settings page.
4. Allow shipping methods in WooCommerce settings -> Shipping.

== Screenshots ==

1. You can choose any shipping method, also overseas shipping methods.
2. You can choose manual setup for price calculations and cronjobs, automatic label generation.
3. Explanation about terminal types.
4. Generated label and manifest links.
5. Organized shipping rules

== Changelog ==

= 2.0 - 2017 12 29 =
* Stable version released

= 2.0.1 to 2.0.1.4 - 2018 01 03 =
* Added more clear validation in settings page
* Added testing authorization data feature
* Fixed soapFault error message when false authorization data
* Fixed a bug with automatic label generation and identcode registration

= 2.0.2 2018 01 05 =
* Fixed bugs with saving data async message
* Refactored js files for deprecated ajax functions
* Added new feature for fixed international shipping
* Fixed bug with international fixed shipping calculation
* Fixed bug with empty overseas method settings
* Improved admin interface and added select2

= 2.0.3 2018 01 15 =
* Fixed bugs with validation
* Added shipping method rules functionality
* Fixed display message bug when it needs to disappear after few seconds
* Added on cart view event clear shipping rates cache
* Fixed disabling methods
* Fixed bug when only one method is enabled interface.js breaks
* Fixed lots of issues with cost calculation and shipping availability