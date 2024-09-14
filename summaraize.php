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
 * @package           Summaraize
 *
 * @wordpress-plugin
 * Plugin Name:       SummarAIze – Generate Key Takeaways with AI
 * Plugin URI:        https://oneclickcontent.com
 * Description:       Bring your own OpenAI API key to instantly distill your posts into 5 key takeaways, boosting reader engagement and making your content more digestible at a glance.
 * Version:           1.1.11
 * Author:            James Wilson
 * Author URI:        https://oneclickcontent.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       summaraize
 * Domain Path:       /languages
 * GitHub Plugin URI: jwilson529/summaraize
 * GitHub Plugin URI: https://github.com/jwilson529/summaraize
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
define( 'SUMMARAIZE_VERSION', '1.1.12' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-summaraize-activator.php
 */
function summaraize_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-summaraize-activator.php';
	Summaraize_Activator::activate();

	// Ensure default post types are set during activation.
	$selected_post_types = get_option( 'summaraize_post_types', false );

	if ( false === $selected_post_types ) {
		update_option( 'summaraize_post_types', array( 'post' ) );
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-summaraize-deactivator.php
 */
function summaraize_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-summaraize-deactivator.php';
	Summaraize_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'summaraize_activate' );
register_deactivation_hook( __FILE__, 'summaraize_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-summaraize.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function summaraize_run() {

	$plugin = new Summaraize();
	$plugin->run();
}
summaraize_run();
