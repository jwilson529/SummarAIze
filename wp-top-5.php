<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oneclickcontent.com
 * @since             1.0.0
 * @package           Wp_Top_5
 *
 * @wordpress-plugin
 * Plugin Name:       WP Top 5
 * Plugin URI:        https://oneclickcontent.com
 * Description:       WP Top 5 - Generate Key Takeaways with AI
 * Version:           1.1.0
 * Author:            James Wilson
 * Author URI:        https://oneclickcontent.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-top-5
 * Domain Path:       /languages
 * GitHub Plugin URI: jwilson529/wp-top-5
 * GitHub Plugin URI: https://github.com/jwilson529/wp-top-5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_TOP_5_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-top-5-activator.php
 */
function activate_wp_top_5() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-top-5-activator.php';
	Wp_Top_5_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-top-5-deactivator.php
 */
function deactivate_wp_top_5() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-top-5-deactivator.php';
	Wp_Top_5_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_top_5' );
register_deactivation_hook( __FILE__, 'deactivate_wp_top_5' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-top-5.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_top_5() {

	$plugin = new Wp_Top_5();
	$plugin->run();
}
run_wp_top_5();
