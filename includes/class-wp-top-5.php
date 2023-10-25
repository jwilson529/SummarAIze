<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://middletnwebdesign.com
 * @since      1.0.0
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/includes
 * @author     James Wilson <james@middletnwebdesign.com>
 */
class Wp_Top_5 {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Top_5_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_TOP_5_VERSION' ) ) {
			$this->version = WP_TOP_5_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-top-5';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Top_5_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Top_5_i18n. Defines internationalization functionality.
	 * - Wp_Top_5_Admin. Defines all hooks for the admin area.
	 * - Wp_Top_5_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-top-5-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-top-5-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-top-5-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-top-5-public.php';

		$this->loader = new Wp_Top_5_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Top_5_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Top_5_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// error_log('define_admin_hooks called.');
		
		$plugin_admin = new Wp_Top_5_Admin( $this->get_plugin_name(), $this->get_version() );

		if (is_object($plugin_admin)) {
		    // error_log('Plugin admin object successfully created.');
		} else {
		    // error_log('Failed to create plugin admin object.');
		}
		$plugin_admin = new Wp_Top_5_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wp_top_5_register_options_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wp_top_5_register_settings' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'add_meta_box' );

		$this->loader->add_action( 'save_post', $plugin_admin, 'save_top_5_points' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_shortcodes' );


		// $this->loader->add_action( 'wp_ajax_contentmaster_generate_content', $plugin_admin, 'contentmaster_generate_content' );
		$this->loader->add_action( 'wp_ajax_contentmaster_gather_content', $plugin_admin, 'contentmaster_gather_content' );
		// $this->loader->add_action( 'admin_init', $plugin_admin, 'activation_redirect' );
		// $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wp_top_5_add_button_to_tags_metabox' );
		// Add link to settings in plugins list.
		// $this->loader->add_action( 'plugin_action_links_' . plugin_basename( CONTENTMASTER_FILE ), $plugin_admin, 'add_settings_link' );
		// $this->loader->add_action( 'plugin_row_meta', $plugin_admin, 'add_plugin_row_meta' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Top_5_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Top_5_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
