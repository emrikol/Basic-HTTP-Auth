=== Basic HTTP Auth ===
Contributors: emrikol
Tags: authentication, http, password, protect, security, cookie
Requires at least: 4.0
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to password protect your WordPress site using HTTP authentication, cookies, and a settings page.

== Description ==

Basic HTTP Auth is a simple WordPress plugin that helps you protect your entire WordPress site using HTTP authentication. The plugin provides a settings page where you can configure the number of days the authentication cookie should last and enter the valid credentials (username and password) for accessing the site.

Enter each username and password pair as comma-separated values in the settings, one pair per line, ensuring that neither usernames nor passwords contain commas to avoid potential issues with this very simple plugin.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/http-authentication-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to the 'Settings' > 'HTTP Authentication' screen to configure the plugin.

== Frequently Asked Questions ==

= Can I protect specific pages or posts instead of the entire site? =

This plugin is designed to protect the entire site using HTTP authentication. To protect specific pages or posts, you may need to use a different plugin or modify the plugin code.

= Can I use this plugin with a custom login page? =

This plugin uses HTTP authentication, which does not require a custom login page. The authentication prompt will be displayed by the user's web browser when they try to access the site.

== Changelog ==

= 1.1 =
* Refactor authentication flow using WordPress hooks.
* Improve cache clearing for popular caching plugins.
* Add caching headers to prevent client-side caching of protected content.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1 =
Refactored authentication flow and improved cache handling. Update recommended for better compatibility and security.

= 1.0 =
Initial release.
