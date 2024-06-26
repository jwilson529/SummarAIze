<?php
/**
 * Class Wp_Top_5_Admin_Settings
 *
 * Manages the admin settings page for the WP Top 5 plugin.
 *
 * @since 1.0.0
 * @package Wp_Top_5
 */

/**
 * Class Wp_Top_5_Admin_Settings
 */
class Wp_Top_5_Admin_Settings {

	/**
	 * Register the plugin settings page.
	 */
	public static function wp_top_5_register_options_page() {
		add_options_page(
			__( 'WP Top 5 Pro Settings', 'wp-top-5' ),
			__( 'WP Top 5 Pro', 'wp-top-5' ),
			'manage_options',
			'wp-top-5-settings',
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_options_page' )
		);
	}

	/**
	 * Display the options page.
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
		register_setting( 'wp_top_5_settings', 'wp_top_5_assistant_id' );

		add_settings_section(
			'wp_top_5_settings_section',
			__( 'WP Top 5 Settings', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_settings_section_callback' ),
			'wp_top_5_settings'
		);

		add_settings_field(
			'wp_top_5_openai_api_key',
			__( 'OpenAI API Key', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_openai_api_key_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section',
			array( 'label_for' => 'wp_top_5_openai_api_key' )
		);

		add_settings_field(
			'wp_top_5_selected_model',
			__( 'Selected Model', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_selected_model_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_post_types',
			__( 'Post Types', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_post_types_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_assistant_id',
			__( 'Assistant ID', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_assistant_id_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section',
			array( 'label_for' => 'wp_top_5_assistant_id' )
		);
	}

	/**
	 * Callback for the post types field.
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
	 * Callback for the settings section.
	 */
	public static function wp_top_5_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the settings for the WP Top 5 Pro plugin.', 'wp-top-5' ) . '</p>';
	}

	/**
	 * Callback for the OpenAI API key field.
	 */
	public static function wp_top_5_openai_api_key_callback() {
		$value = get_option( 'wp_top_5_openai_api_key', '' );
		echo '<input type="password" name="wp_top_5_openai_api_key" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . wp_kses_post( __( 'Get your OpenAI API Key <a href="https://beta.openai.com/signup/">here</a>.', 'wp-top-5' ) ) . '</p>';
	}

	/**
	 * Callback for the selected model field.
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
	 * Callback for the Assistant ID field.
	 */
	public static function wp_top_5_assistant_id_callback() {
		$value = get_option( 'wp_top_5_assistant_id', '' );
		echo '<input type="text" name="wp_top_5_assistant_id" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Enter the Assistant ID provided by OpenAI.', 'wp-top-5' ) . '</p>';
	}
}
