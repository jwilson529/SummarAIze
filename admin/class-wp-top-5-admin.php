<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two example hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/admin
 * @author     James Wilson <james@middletnwebdesign.com>
 */
class Wp_Top_5_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-top-5-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-top-5-admin.js', array( 'jquery' ), $this->version, false );

		// Get the selected model from the options.
		$selected_model = get_option( 'wp_top_5_selected_model', 'gpt3.5-turbo' );

		// Localize the script with the necessary nonces.
		wp_localize_script(
			$this->plugin_name,
			'wp_top_5_admin_vars',
			array(
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'wp_top_5_ajax_nonce'     => wp_create_nonce( 'wp_top_5_ajax_nonce' ),
				'wp_top_5_meta_box_nonce' => wp_create_nonce( 'wp_top_5_meta_box' ),
				'selected_model'          => $selected_model,
			)
		);
	}

	/**
	 * Add meta box to post edit screen.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		// Get selected post types from the plugin settings.
		$post_types = get_option( 'wp_top_5_post_types', array() );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'wp_top_5_meta_box',
				__( 'WP Top 5 Pro', 'wp-top-5' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The current post.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'wp_top_5_meta_box', 'wp_top_5_meta_box_nonce' );

		$top_5_points = get_post_meta( $post->ID, 'wp_top_5_points', true ) ? get_post_meta( $post->ID, 'wp_top_5_points', true ) : array();

		// Get OpenAI API key from settings.
		$openai_api_key = get_option( 'wp_top_5_openai_api_key', '' );

		$selected_model = get_option( 'wp_top_5_selected_model', 'gpt3.5-turbo' );

		echo '<button id="generate-top-5-button">Generate Top 5 Points</button>';
		echo '<div id="loading-icon" style="display:none;">Thinking...</div>';
		echo '<div id="top-5-points-list" class="list-group">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$point = $top_5_points[ $i ] ?? '';
			echo "<div style='margin-bottom: 10px;'>";
			echo "<label for='wp_top_5_points[" . esc_attr( $i ) . "]'>" . esc_html( "Point $i:" ) . '</label>';
			echo "<input style='width: 100%;' type='text' name='wp_top_5_points[" . esc_attr( $i ) . "]' value='" . esc_attr( $point ) . "' placeholder='" . esc_attr( "Point $i" ) . "' />";
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Save the meta box data.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_top_5_points( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['wp_top_5_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_top_5_meta_box_nonce'] ) ), 'wp_top_5_meta_box' ) ) {
			return;
		}

		// Don't save during autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( isset( $_POST['post_type'] ) && 'page' === sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save the meta data.
		$top_5_points = isset( $_POST['wp_top_5_points'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wp_top_5_points'] ) ) : array();
		update_post_meta( $post_id, 'wp_top_5_points', $top_5_points );
	}

	/**
	 * Gather content using OpenAI API.
	 *
	 * @since 1.0.0
	 */
	public static function wp_top_5_gather_content() {
		// Verify the nonce.
		$nonce = sanitize_key( wp_unslash( $_POST['nonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_top_5_ajax_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed. Unable to proceed.' );
			exit;
		}

		// Sanitize and validate inputs.
		$title   = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$tags    = sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) );
		$content = sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) );
		$model   = sanitize_text_field( wp_unslash( $_POST['model'] ?? '' ) );

		// Get the API key.
		$api_key = get_option( 'wp_top_5_openai_api_key' );
		if ( empty( $api_key ) ) {
			wp_send_json_error( 'API key is missing.' );
			exit;
		}

		// Set up the OpenAI API call.
		$url            = 'https://api.openai.com/v1/chat/completions';
		$headers        = array(
			'Content-Type'  => 'application/json',
			'Authorization' => "Bearer $api_key",
		);
		$system_message = 'You are a helpful assistant that analyzes an article and identifies its top five points. Return these points in an array format.';

		$data = array(
			'model'    => $model,
			'messages' => array(
				array(
					'role'    => 'system',
					'content' => $system_message,
				),
				array(
					'role'    => 'user',
					'content' => "Please analyze the following article and identify its top five points:\n\nTitle: {$title}\nTags: {$tags}\nContent: {$content}",
				),
			),
		);
		$args = array(
			'headers'   => $headers,
			'body'      => wp_json_encode( $data ),
			'sslverify' => true, // Enable SSL verification.
			'timeout'   => 120,  // Set the timeout value to 120 seconds.
		);

		// Make the API call.
		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
			exit;
		}

		$body                  = wp_remote_retrieve_body( $response );
		$json                  = json_decode( $body, true );
		$article_points_string = $json['choices'][0]['message']['content'] ?? '';
		preg_match_all( '/\d+\.\s.*?(\n|$)/', $article_points_string, $matches );

		if ( isset( $matches[0] ) && is_array( $matches[0] ) ) {
			$article_points_array = $matches[0];
		} else {
			$article_points_array = array();
		}

		if ( $article_points_array ) {
			wp_send_json_success( $article_points_array );
		} else {
			wp_send_json_error( 'Failed to generate top 5 points.' );
		}
		exit;
	}

	/**
	 * Display top 5 points on the front-end.
	 *
	 * @since 1.0.0
	 * @return string HTML output of the top 5 points.
	 */
	public function wp_top_5_display_points() {
		global $post;

		$top_5_points = get_post_meta( $post->ID, 'wp_top_5_points', true );

		if ( ! is_array( $top_5_points ) || empty( $top_5_points ) ) {
			return '<p>' . esc_html__( 'No top 5 points available.', 'wp-top-5' ) . '</p>';
		}

		$output  = '<div class="wp-top-5-wrapper">';
		$output .= '<div class="wp-top-5-header">' . esc_html__( 'View Key Points &#9650;', 'wp-top-5' ) . '</div>';
		$output .= '<div class="container">';
		$output .= '<ul class="wp-top-5-list hidden">';

		foreach ( $top_5_points as $point ) {
			$output .= '<li class="wp-top-5-point">' . esc_html( $point ) . '</li>';
		}

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Register shortcodes for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'wp_top_5', array( $this, 'wp_top_5_display_points' ) );
	}
}
