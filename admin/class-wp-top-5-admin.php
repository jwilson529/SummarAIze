<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://middletnwebdesign.com
 * @since      1.0.0
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
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
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-top-5-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-top-5-admin.js', array( 'jquery' ), $this->version, false );

	    // Localize the script with the necessary nonces.
	    wp_localize_script(
	        $this->plugin_name,
	        'wp_top_5_admin_vars',
	        array(
	            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
	            'wp_top_5_ajax_nonce'       => wp_create_nonce( 'wp_top_5_ajax_nonce' ),
	            'wp_top_5_meta_box_nonce'   => wp_create_nonce( 'wp_top_5_meta_box' )
	        )
	    );
	}


	/**
	 * Register the plugin settings page.
	 */
	public static function wp_top_5_register_options_page() {
		add_options_page(
			__( 'WP Top 5 Pro Settings', 'wp-top-5' ),
			__( 'WP Top 5 Pro', 'wp-top-5' ),
			'manage_options',
			'wp-top-5-settings',
			array( 'WP_top_5_Admin', 'wp_top_5_options_page' )
		);
	}

	/**
	 * Create the options page.
	 */
	public static function wp_top_5_options_page() {
		?>
	<div class="wrap">
		<h2><?php esc_html_e( 'WP Top 5 Pro Settings', 'wp-top-5' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'wp_top_5_settings' ); ?>
			<?php do_settings_sections( 'wp_top_5_settings' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
		<?php
	}

	/**
	 * Register the plugin settings.
	 */
	public static function wp_top_5_register_settings() {
		register_setting( 'wp_top_5_settings', 'wp_top_5_openai_api_key' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_selected_model' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_post_types' );

		add_settings_section(
			'wp_top_5_settings_section',
			__( 'WP Top 5 Settings', 'wp-top-5' ),
			array( 'WP_top_5_Admin', 'wp_top_5_settings_section_callback' ),
			'wp_top_5_settings'
		);

		add_settings_field(
			'wp_top_5_openai_api_key',
			__( 'OpenAI API Key', 'wp-top-5' ),
			array( 'WP_top_5_Admin', 'wp_top_5_openai_api_key_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section',
			array( 'label_for' => 'wp_top_5_openai_api_key' )
		);

		add_settings_field(
			'wp_top_5_selected_model',
			__( 'Selected Model', 'wp-top-5' ),
			array( 'WP_top_5_Admin', 'wp_top_5_selected_model_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_post_types',
			__( 'Post Types', 'wp-top-5' ),
			array( 'WP_top_5_Admin', 'wp_top_5_post_types_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);
	}

	/**
	 * Post types field callback.
	 */
	public static function wp_top_5_post_types_callback() {
		$selected_post_types = get_option( 'wp_top_5_post_types', array() );
		// Set the default selected post type to 'post' if the option is empty.
		if ( empty( $selected_post_types ) ) {
			$selected_post_types = array( 'post' );
		}
		$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );

		echo '<p>Select which post types WP Top 5 Pro should be enabled on:</p>';
		echo '<p><em>Custom post types must have titles enabled.</em></p>';
		foreach ( $post_types as $post_type ) {
			$checked         = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';
			$post_type_label = str_replace( '_', ' ', ucwords( $post_type ) );
			echo '<input type="checkbox" name="wp_top_5_post_types[]" value="' . esc_attr( $post_type ) . '" ' . esc_attr( $checked ) . '> ' . esc_html( $post_type_label ) . '<br>';
		}
	}



	/**
	 * Section callback.
	 */
	public static function wp_top_5_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the settings for the WP Top 5 Pro plugin.', 'wp-top-5' ) . '</p>';
	}

	/**
	 * OpenAI API key field callback.
	 */
	public static function wp_top_5_openai_api_key_callback() {
		$value = get_option( 'wp_top_5_openai_api_key', '' );
		echo '<input type="password" name="wp_top_5_openai_api_key" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . wp_kses_post( __( 'Get your OpenAI API Key <a href="https://beta.openai.com/signup/">here</a>.', 'wp-top-5' ) ) . '</p>';
	}



	/**
	 * Model selection field callback.
	 */
	public static function wp_top_5_selected_model_callback() {
		$selected_model = get_option( 'wp_top_5_selected_model', 'gpt3.5-turbo' );
		echo '<div class="wp-top-5-selected-model-wrapper">';
		echo '<select id="wp_top_5_selected_model" name="wp_top_5_selected_model">';

		// Get API key from options.
		$api_key = get_option( 'wp_top_5_openai_api_key' );
		if ( ! empty( $api_key ) ) {
			// Fetch models from OpenAI API.
			$response = wp_remote_get(
				'https://api.openai.com/v1/models',
				array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $api_key,
					),
				)
			);

			// Check for error.
			if ( is_wp_error( $response ) ) {
				echo '<option value="">' . esc_html__( 'Error fetching models', 'wp-top-5' ) . '</option>';
			} else {
				$models      = json_decode( wp_remote_retrieve_body( $response ), true )['data'];
				$model_names = array();
				foreach ( $models as $model ) {
					$model_names[] = $model['id'];
				}
				sort( $model_names, SORT_STRING );
				foreach ( $model_names as $model_name ) {
					$model_display_name = ucwords( str_replace( '-', ' ', $model_name ) );
					echo '<option value="' . esc_attr( $model_name ) . '"' . selected( $model_name, $selected_model, false ) . '>' . esc_html( $model_display_name ) . '</option>';
				}
			}
		} else {
			echo '<option value="">' . esc_html__( 'API key required', 'wp-top-5' ) . '</option>';
		}

		echo '</select>';
		echo '<div class="wp-top-5-selected-model-description">';
		echo '<p>' . esc_html__( 'It is recommended to choose one of the following OpenAI models for tag generation: Davinci, GPT-3 or GPT-3.5 Turbo.', 'wp-top-5' ) . '</p>';
		echo '<a href="https://beta.openai.com/docs/models/overview" target="_blank">' . esc_html__( 'Learn more about OpenAI models', 'wp-top-5' ) . '</a>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Add meta box to post edit screen.
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
	 * @param array $post The current post.
	 */
	public function render_meta_box( $post ) {
	    wp_nonce_field( 'wp_top_5_meta_box', 'wp_top_5_meta_box_nonce' );

	    $top_5_points = get_post_meta($post->ID, 'wp_top_5_points', true) ?: [];

	    // Get OpenAI API key from settings.
	    $openai_api_key = get_option( 'wp_top_5_openai_api_key', '' );

	    $selected_model = get_option( 'wp_top_5_selected_model', 'gpt3.5-turbo' );

	    echo '<button id="generate-top-5-button">Generate Top 5 Points</button>';
	    echo '<div id="loading-icon" style="display:none;">Thinking...</div>';
	    echo '<div id="top-5-points-list" class="list-group">';

	    for ($i = 1; $i <= 5; $i++) {
	        $point = $top_5_points[$i] ?? '';
	        echo "<div style='margin-bottom: 10px;'>";
	        echo "<label for='wp_top_5_points[$i]'>Point $i:</label>";
	        echo "<input style='width: 100%;' type='text' name='wp_top_5_points[$i]' value='$point' placeholder='Point $i' />";
	        echo "</div>";
	    }

	    echo '</div>';
	}


	public function save_top_5_points( $post_id ) {
	    // Check if our nonce is set.
	    if ( ! isset( $_POST['wp_top_5_meta_box_nonce'] ) ) {
	        return;
	    }

	    // Verify that the nonce is valid.
	    if ( ! wp_verify_nonce( $_POST['wp_top_5_meta_box_nonce'], 'wp_top_5_meta_box' ) ) {
	        return;
	    }

	    // Don't save during autosave
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	        return;
	    }

	    // Check permissions
	    if ( 'page' === $_POST['post_type'] ) {
	        if ( ! current_user_can( 'edit_page', $post_id ) ) {
	            return;
	        }
	    } else {
	        if ( ! current_user_can( 'edit_post', $post_id ) ) {
	            return;
	        }
	    }

	    // Save the meta data
	    $top_5_points = isset( $_POST['wp_top_5_points'] ) ? $_POST['wp_top_5_points'] : [];
	    update_post_meta( $post_id, 'wp_top_5_points', $top_5_points );
	}



	public static function contentmaster_gather_content() {
	    // error_log('contentmaster_gather_content function called.');

	    // Verify the nonce
	    $nonce = sanitize_key( wp_unslash( $_POST['nonce'] ?? '' ) );
	    if ( ! wp_verify_nonce( $nonce, 'wp_top_5_ajax_nonce' ) ) {
	        // error_log('Nonce verification failed.');
	        wp_send_json_error( 'Nonce verification failed. Unable to proceed.' );
	        exit;
	    }
	    // error_log('Nonce verified.');

	    // Sanitize and Validate Inputs
	    $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
	    $tags = sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) );
	    $content = sanitize_text_field( wp_unslash( $_POST['content'] ?? '' ) );
	    $model = sanitize_text_field( wp_unslash( $_POST['model'] ?? '' ) );

	    // error_log("Title: $title, Tags: $tags, Content: $content, Model: $model");

	    // Get the API key
	    $api_key = get_option( 'wp_top_5_openai_api_key' );
	    if ( empty( $api_key ) ) {
	        // error_log('API key is missing.');
	        wp_send_json_error( 'API key is missing.' );
	        exit;
	    }
	    // error_log('API key retrieved.');

	    // Set up the OpenAI API call
	    $url = 'https://api.openai.com/v1/chat/completions';
	    $headers = [
	        'Content-Type' => 'application/json',
	        'Authorization' => "Bearer $api_key",
	    ];
	    $system_message = "You are a helpful assistant that analyzes an article and identifies its top five points. Return these points in an array format.";

	    $data = [
	        'model' => $model,
	        'messages' => [
	            [
	                'role' => 'system',
	                'content' => $system_message,
	            ],
	            [
	                'role' => 'user',
	                'content' => "Please analyze the following article and identify its top five points:\n\nTitle: {$title}\nTags: {$tags}\nContent: {$content}"
	            ],
	        ],
	    ];
	    $args = [
	        'headers' => $headers,
	        'body' => wp_json_encode( $data ),
	        'sslverify' => true, // Enable SSL verification
	        'timeout' => 120, // Set the timeout value to 120 seconds
	    ];

	    // Make the API call
	    $response = wp_remote_post( $url, $args );

	    // error_log('API call response: ' . print_r($response, true));

	    if ( is_wp_error( $response ) ) {
	        // error_log('API call failed: ' . $response->get_error_message());
	        wp_send_json_error( $response->get_error_message() );
	        exit;
	    }

	    $body = wp_remote_retrieve_body( $response );
	    $json = json_decode( $body, true );
	    $article_points_string = $json['choices'][0]['message']['content'] ?? '';
	    preg_match_all('/\d+\.\s.*?(\n|$)/', $article_points_string, $matches);

	    if (isset($matches[0]) && is_array($matches[0])) {
	        $article_points_array = $matches[0];
	    } else {
	        $article_points_array = [];
	    }


	    // error_log(print_r($json, true));  // Log the entire JSON response
	    // error_log(print_r($article_points_array, true));  // Log the parsed array


	    if ($article_points_array) {
	        // error_log('Successfully generated top 5 points.');
	        wp_send_json_success( $article_points_array );
	    } else {
	        // error_log('Failed to generate top 5 points.');
	        wp_send_json_error( 'Failed to generate top 5 points.' );
	    }
	    exit;
	}


	function wp_top_5_display_points( $atts ) {
	    global $post;

	    $top_5_points = get_post_meta( $post->ID, 'wp_top_5_points', true );

	    if ( ! is_array( $top_5_points ) || empty( $top_5_points ) ) {
	        return '<p>No top 5 points available.</p>';
	    }

	    $output = '<div class="wp-top-5-wrapper">';
	    $output .= '<div class="wp-top-5-header">View Key Points &#9650;</div>';
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


	public function register_shortcodes() {
	    add_shortcode( 'wp_top_5', array( $this, 'wp_top_5_display_points' ) );
	}



}
