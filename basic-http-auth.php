<?php
/**
 * Plugin Name: Basic HTTP Auth
 * Description: A simple plugin to password protect your WordPress site using HTTP authentication, cookies, and a settings page.
 * Version: 1.0
 * Author: Derrick Tennant
 */

namespace emrikol\basic_http_auth;

/**
 * Checks if the given IP address is within any of the specified IP ranges.
 *
 * This function takes an IP address and an array of IP ranges in CIDR notation.
 * It returns true if the IP address is within any of the specified ranges, and
 * false otherwise.
 *
 * @param string $ip     The IP address to check.
 * @param array  $ranges An array of IP ranges in CIDR notation.
 *
 * @return bool True if the IP address is within any of the ranges, false otherwise.
 */
function is_ip_in_ranges( $ip, $ranges ) {
	$long_ip = ip2long( $ip );

	foreach ( $ranges as $range ) {
		list($subnet, $bits) = explode( '/', $range );
		$subnet_long         = ip2long( $subnet );
		$mask                = -1 << ( 32 - $bits );

		if ( ( $long_ip & $mask ) == ( $subnet_long & $mask ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Protects the WordPress site using HTTP authentication and cookies.
 *
 * This function checks the user's cookies and HTTP authentication credentials against the
 * configured list of valid credentials. If the user's credentials are valid, they are granted
 * access to the site. If the user's credentials are not valid or not present, they are prompted
 * to enter valid credentials via HTTP authentication.
 *
 * The function reads the valid credentials from the 'http_auth_credentials' option, which is
 * set through the plugin's settings page in the WordPress admin area. The number of days the
 * authentication cookie should last is set through the 'http_auth_cookie_days' option.
 *
 * @return void
 */
function http_auth_protect() {
	// Allow Jetpack IPs: https://jetpack.com/support/how-to-add-jetpack-ips-allowlist/ may need updated in the future.
	$allowed_ranges = array(
		'122.248.245.244/32',
		'54.217.201.243/32',
		'54.232.116.4/32',
		'192.0.80.0/20',
		'192.0.96.0/20',
		'192.0.112.0/20',
		'195.234.108.0/22',
	);

	// Check if the user's IP is within the allowed IP ranges.
	$user_ip = filter_var( $_SERVER['REMOTE_ADDR'] ?? false, FILTER_VALIDATE_IP ); // phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__REMOTE_ADDR__, WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders

	if ( is_ip_in_ranges( $user_ip, $allowed_ranges ) ) {
		return;
	}

	$credentials  = get_option( 'http_auth_credentials', '' );
	$credentials  = explode( PHP_EOL, $credentials );
	$cookie_name  = 'http_auth_cookie';
	$cookie_valid = false;

	foreach ( $credentials as $credential ) {
		list($user, $pass) = explode( ',', $credential );
		$cookie_value      = md5( $user . $pass );
		$authenticated     = false;

		// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
		if ( isset( $_COOKIE[ $cookie_name ] ) && $_COOKIE[ $cookie_name ] === $cookie_value ) {
			$cookie_valid = true;
			break;
		}
	}

	if ( ! $cookie_valid ) {
		// phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.BasicAuthentication
		if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			authenticate();
		} else {
			foreach ( $credentials as $credential ) {
				list($user, $pass) = explode( ',', $credential );

				// phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.BasicAuthentication
				if ( $_SERVER['PHP_AUTH_USER'] === $user && $_SERVER['PHP_AUTH_PW'] === $pass ) {
					$cookie_days = intval( get_option( 'http_auth_cookie_days', 30 ) );
					// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
					setcookie( $cookie_name, md5( $user . $pass ), time() + ( 86400 * $cookie_days ), '/' );
					$authenticated = true;
					break;
				}
			}
		}

		if ( ! $authenticated ) {
			authenticate();
		}
	}
}
add_action( 'wp', '\emrikol\basic_http_auth\http_auth_protect' );

/**
 * Sanitizes and validates the credentials input from the settings page.
 *
 * This function checks if the input format for usernames and passwords is valid and
 * displays an error message for invalid input. The correct format is 'username,password'.
 *
 * @param string $input The raw input from the settings page.
 *
 * @return string The sanitized input if valid, an empty string if invalid.
 */
function sanitize_http_auth_credentials( $input ) {
	// Split the input by line.
	$lines = explode( PHP_EOL, $input );

	$sanitized_lines = array();

	foreach ( $lines as $line ) {
		$parts = explode( ',', $line );

		// Check if the line has exactly two parts (username and password).
		if ( count( $parts ) !== 2 ) {
			add_settings_error(
				'http_auth_credentials',
				'invalid_format',
				'Invalid format: Each line should contain exactly one username and one password, separated by a comma.'
			);
			continue;
		}

		// Add any additional validation checks for usernames and passwords here, e.g., length, allowed characters, etc.

		// Sanitize the username and password.
		$sanitized_user = sanitize_text_field( $parts[0] );
		$sanitized_pass = sanitize_text_field( $parts[1] );

		$sanitized_lines[] = $sanitized_user . ',' . $sanitized_pass;
	}

	return implode( PHP_EOL, $sanitized_lines );
}

/**
 * Adds caching headers to the HTTP response.
 *
 * This function adds cache-control, expires, and pragma headers to the HTTP response
 * to prevent clients from caching the content.
 *
 * @param array $headers The current headers to be sent in the HTTP response.
 *
 * @return array The modified headers with caching headers added.
 */
function add_caching_headers( $headers ) {
	// Set Cache-Control to no-cache, no-store, must-revalidate.
	$headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';

	// Set Expires header to a past date.
	$headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';

	// Set Pragma header to no-cache.
	$headers['Pragma'] = 'no-cache';

	return $headers;
}
add_filter( 'wp_headers', '\emrikol\basic_http_auth\add_caching_headers' );

/**
 * Clears server-side cache when authentication fails.
 *
 * This function checks for popular caching plugins' cache clearing functions and
 * calls them if they are available to clear the cache when authentication fails.
 * This ensures that the server-side cache doesn't serve protected content to unauthorized users.
 */
function clear_cache_on_auth_failure() {
	// Batcache.
	if ( function_exists( 'batcache_cancel' ) ) {
		batcache_cancel();
	}

	// WP Super Cache.
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}

	// W3 Total Cache.
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	}
}

/**
 * Sends an HTTP authentication header and displays an access denied message.
 *
 * This function is called when a user fails to provide valid credentials or
 * when their authentication cookie is not present or invalid. It sends the
 * 'WWW-Authenticate' and 'HTTP/1.0 401 Unauthorized' headers, and then displays
 * an 'Access denied' message.
 *
 * @return void
 */
function authenticate() {
	clear_cache_on_auth_failure();

	header( 'WWW-Authenticate: Basic realm="Restricted Area"' );
	header( 'HTTP/1.0 401 Unauthorized' );
	echo 'Access denied';
	exit;
}

/**
 * Adds the HTTP Authentication settings page to the WordPress admin menu.
 *
 * This function creates a new options page under the 'Settings' menu in the
 * WordPress admin area. The page is titled 'HTTP Authentication Settings' and
 * uses the 'http_auth_settings_page()' function to display its content.
 *
 * @return void
 */
function http_auth_settings_menu() {
	add_options_page( 'HTTP Authentication Settings', 'HTTP Authentication', 'manage_options', 'http-auth-settings', '\emrikol\basic_http_auth\http_auth_settings_page' );
}
add_action( 'admin_menu', '\emrikol\basic_http_auth\http_auth_settings_menu' );

/**
 * Adds the HTTP Authentication settings page to the WordPress admin menu.
 *
 * This function creates a new options page under the 'Settings' menu in the
 * WordPress admin area. The page is titled 'HTTP Authentication Settings' and
 * uses the 'http_auth_settings_page()' function to display its content.
 *
 * @return void
 */
function http_auth_settings_page() {
	?>
	<div class="wrap">
		<h1>HTTP Authentication Settings</h1>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'http-auth-settings' );
				do_settings_sections( 'http-auth-settings' );
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Cookie Days</th>
					<td><input type="number" name="http_auth_cookie_days" value="<?php echo esc_attr( get_option( 'http_auth_cookie_days', 30 ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<p>Usernames and Passwords. One per line, comma separated.</p>
						<p>Example: <code>username,password</code></p>
					</th>
					<td><textarea rows="5" cols="50" name="http_auth_credentials"><?php echo esc_textarea( get_option( 'http_auth_credentials', '' ) ); ?></textarea></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Registers the HTTP Authentication settings with the WordPress Settings API.
 *
 * This function registers two settings, 'http_auth_cookie_days' and
 * 'http_auth_credentials', with their respective sanitization callbacks.
 * The 'intval' function is used as a sanitization callback for the
 * 'http_auth_cookie_days' setting, while the 'sanitize_textarea_field'
 * function is used for the 'http_auth_credentials' setting.
 *
 * @return void
 */
function http_auth_register_settings() {
	register_setting( 'http-auth-settings', 'http_auth_cookie_days', 'intval' );
	register_setting( 'http-auth-settings', 'http_auth_credentials', '\emrikol\basic_http_auth\sanitize_http_auth_credentials' );
}
add_action( 'admin_init', '\emrikol\basic_http_auth\http_auth_register_settings' );
