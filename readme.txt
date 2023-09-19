=== Basic HTTP Auth ===
Contributors: emrikol
Tags: authentication, http, password, protect, security, cookie, multisite, xml-rpc, rest api, basic auth
Requires at least: 6.3
Tested up to: 6.3
Requires PHP: 8.0
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to password protect your WordPress site using HTTP authentication, cookies, and a settings page.

== Description ==

Basic HTTP Auth is a straightforward WordPress plugin designed to provide a basic layer of protection for your entire WordPress site using HTTP authentication. With support for both single sites and multisite installations, you can configure access credentials either on a per-site basis or at the network level. In a multisite setup, network-level credentials grant access to all sites within the network, while site-specific credentials only grant access to the individual site. When both network and site-specific credentials are available, the network-level credentials take precedence.

Two key areas that the plugin impacts are XML-RPC and the REST API:
- XML-RPC gets disabled when authentication fails, potentially affecting any tools or plugins relying on it.
- Unauthenticated access to the REST API is also restricted, which might impact certain decoupled setups or external tools that expect open access.

For security considerations regarding this plugin, please refer to the `Security Notice` section.

### Features:

- Password protection for the entire WordPress site.
- IP whitelist for services like Jetpack.
- Uses cookies for authenticated users.
- Settings page for configuring authentication credentials.
- Cache prevention headers.
- Compatibility with popular caching plugins to clear cache on authentication failure.

Enter each username and password pair as comma-separated values in the settings, one pair per line. Ensure that neither usernames nor passwords contain commas to avoid potential issues.

== Security Notice ==

While Basic HTTP Auth adds a layer of protection to your WordPress site, it's crucial to understand its limitations:

1. **Plaintext Passwords:** This plugin stores credentials (usernames and passwords) in plaintext in the database. This storage method makes it vulnerable to potential database breaches. Always ensure you have other security measures in place, such as regular backups and database encryption, to mitigate such risks.

2. **Not a Comprehensive Solution:** Basic HTTP Auth is designed to be a simple access block, similar to WordPress's built-in post passwords feature. It is not intended to be a full-fledged security solution. For enhanced site security, consider employing more comprehensive security plugins and practices alongside Basic HTTP Auth.

3. **Use HTTPS:** As this plugin involves the transmission of credentials, always ensure your site runs over HTTPS to encrypt the data between the browser and the server. This step is crucial to prevent potential man-in-the-middle attacks and eavesdropping.

Always prioritize the security of your website. Regularly update all plugins, themes, and WordPress core, and consider periodic security audits to identify and rectify potential vulnerabilities.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/http-authentication-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to the 'Settings' > 'HTTP Authentication' screen to configure the plugin.

== Frequently Asked Questions ==

= How does this plugin work with multisite installations? =

In a multisite setup, you can configure credentials at both the network level and the site-specific level. Network-level credentials grant access to all sites within the network, whereas site-specific credentials only grant access to the individual site. When both types of credentials are set up, network-level credentials take precedence.

= What happens if both network and site-specific credentials are available? =

Network-level credentials take precedence over site-specific ones. This means that if a user provides network-level credentials, they will be granted access to all sites in the network, regardless of site-specific settings.

= Why was XML-RPC access disabled? =

For added security, this plugin disables XML-RPC when HTTP authentication fails. This measure helps prevent unauthorized XML-RPC requests, which can allow access to "public" content.

= I use tools that rely on the REST API. How does this plugin affect them? =

This plugin restricts unauthenticated access to the REST API. If you use tools or setups that require open access to the REST API, you might need to authenticate them or consider alternative configurations.

= I noticed the passwords are stored in plaintext. Why is this, and is it safe? =

The plugin uses a basic method for storing credentials, similar to how WordPress's post password feature works. While it offers a level of protection, it's not considered highly secure. Always use complex, unique passwords, and consider this plugin as a basic access block rather than a comprehensive security solution. Refer to the `Security Notice` section for more details.

= Can I protect specific pages or posts instead of the entire site? =

This plugin is designed to protect the entire site using HTTP authentication. To protect specific pages or posts, you may need to use a different plugin or modify the plugin code.

= Can I use this plugin with a custom login page? =

This plugin uses HTTP authentication, which does not require a custom login page. The authentication prompt will be displayed by the user's web browser when they try to access the site.

= Why would I need this plugin? =

If you want to add an additional layer of security to your WordPress website by requiring HTTP authentication, this plugin provides a simple way to do that. It's particularly useful for staging or development environments.

== Changelog ==

= 1.2 =
* Updated plugin version.
* Enhanced IP range comparison for strict equality.
* Added support for multisite network-level credentials.
* Introduced the Network HTTP Authentication settings submenu in the WordPress network admin.
* Enhanced the HTTP authentication flow to include both network-level and site-specific credentials.
* Restricted access to XML-RPC when authentication fails.
* Restricted access to the REST API for unauthenticated users with a new `rest_authenticate` function.

= 1.1 =
* Refactor authentication flow using WordPress hooks.
* Improve cache clearing for popular caching plugins.
* Add caching headers to prevent client-side caching of protected content.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.2 =
Introduced multisite support with network-level authentication credentials. Added further restrictions to XML-RPC and the REST API for unauthenticated users. It's recommended to update for enhanced security and multisite compatibility.

= 1.1 =
Refactored authentication flow and improved cache handling. Update recommended for better compatibility and security.

= 1.0 =
Initial release.
