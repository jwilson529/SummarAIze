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
		<div id="wp-top-5" class="wrap">
			<form class="wp-top-5-settings-form" method="post" action="options.php">
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
		register_setting( 'wp_top_5_settings', 'wp_top_5_widget_title' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_display_position' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_display_mode' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_button_style' );
		register_setting( 'wp_top_5_settings', 'wp_top_5_button_color' );

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

		add_settings_field(
			'wp_top_5_widget_title',
			__( 'Widget Title', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_widget_title_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_display_position',
			__( 'Display Position', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_display_position_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_display_mode',
			__( 'Display Mode', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_display_mode_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_button_style',
			__( 'Button Style', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_button_style_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);

		add_settings_field(
			'wp_top_5_button_color',
			__( 'Button Color', 'wp-top-5' ),
			array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_button_color_callback' ),
			'wp_top_5_settings',
			'wp_top_5_settings_section'
		);
	}

	/**
	 * Callback for the widget title field.
	 */
	public static function wp_top_5_widget_title_callback() {
		$widget_title = get_option( 'wp_top_5_widget_title', 'Key Takeaways' );
		?>
		<input type="text" name="wp_top_5_widget_title" id="wp_top_5_widget_title" value="<?php echo esc_attr( $widget_title ); ?>" />
		<p class="description"><?php esc_html_e( 'Enter the title for the widget.', 'wp-top-5' ); ?></p>
		<?php
	}

	/**
	 * Callback for the button style field.
	 */
	public static function wp_top_5_button_style_callback() {
		$selected_style = get_option( 'wp_top_5_button_style', 'flat' );
		$button_styles  = array(
			'flat'        => __( 'Flat', 'wp-top-5' ),
			'rounded'     => __( 'Rounded', 'wp-top-5' ),
			'angled'      => __( 'Angled', 'wp-top-5' ),
			'apple'       => __( 'Apple', 'wp-top-5' ),
			'google'      => __( 'Google', 'wp-top-5' ),
			'bubbly'      => __( 'Bubbly', 'wp-top-5' ),
			'material'    => __( 'Material', 'wp-top-5' ),
			'windows'     => __( 'Windows', 'wp-top-5' ),
			'neumorphism' => __( 'Neumorphism', 'wp-top-5' ),
			'3d'          => __( '3D', 'wp-top-5' ),
		);
		?>
		<select name="wp_top_5_button_style" id="wp_top_5_button_style">
			<?php foreach ( $button_styles as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected_style, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the style for the button.', 'wp-top-5' ); ?></p>
		<?php
	}

	/**
	 * Callback for the button color field.
	 */
	public static function wp_top_5_button_color_callback() {
		$button_color = get_option( 'wp_top_5_button_color', '#0073aa' );
		?>
		<input type="color" name="wp_top_5_button_color" id="wp_top_5_button_color" value="<?php echo esc_attr( $button_color ); ?>" />
		<p class="description"><?php esc_html_e( 'Choose the color for the button.', 'wp-top-5' ); ?></p>
		<?php
	}

	/**
	 * Callback for the display position field.
	 */
	public static function wp_top_5_display_position_callback() {
		$selected_position = get_option( 'wp_top_5_display_position', 'above' );
		?>
		<select name="wp_top_5_display_position" id="wp_top_5_display_position">
			<option value="above" <?php selected( $selected_position, 'above' ); ?>>Above Content</option>
			<option value="below" <?php selected( $selected_position, 'below' ); ?>>Below Content</option>
			<option value="popup" <?php selected( $selected_position, 'popup' ); ?>>Popup</option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose where to display the top 5 points.', 'wp-top-5' ); ?></p>
		<?php
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

		echo '<p>' . esc_html__( 'Select which post types WP Top 5 Pro should be enabled on:', 'wp-top-5' ) . '</p>';
		echo '<p><em>' . esc_html__( 'Custom post types must have titles enabled.', 'wp-top-5' ) . '</em></p>';

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
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
					$models      = $data['data'];
					$model_names = array();
					foreach ( $models as $model ) {
						$model_names[] = $model['id'];
					}
					sort( $model_names, SORT_STRING );
					foreach ( $model_names as $model_name ) {
						$model_display_name = ucwords( str_replace( '-', ' ', $model_name ) );
						echo '<option value="' . esc_attr( $model_name ) . '"' . selected( $model_name, $selected_model, false ) . '>' . esc_html( $model_display_name ) . '</option>';
					}
				} else {
					// Display the error message directly to the user.
					$error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : esc_html__( 'Unexpected API response', 'wp-top-5' );
					echo '<option value="">' . esc_html( $error_message ) . '</option>';
				}
			}
		} else {
			echo '<option value="">' . esc_html__( 'API key required', 'wp-top-5' ) . '</option>';
		}

		echo '</select>';
		echo '<div class="wp-top-5-selected-model-description">';
		if ( ! empty( $selected_model ) && ! is_wp_error( $response ) && isset( $data['data'] ) && is_array( $data['data'] ) ) {
			echo '<a href="https://platform.openai.com/docs/models/overview" target="_blank">' . esc_html__( 'Learn more about OpenAI models', 'wp-top-5' ) . '</a>';
		} else {
			echo esc_html__( 'You can find your API key at ', 'wp-top-5' ) . '<a href="https://platform.openai.com/account/api-keys" target="_blank">' . esc_html__( 'OpenAI API Keys', 'wp-top-5' ) . '</a>.';
		}
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Callback for the Display Mode field.
	 */
	public static function wp_top_5_display_mode_callback() {
		$value = get_option( 'wp_top_5_display_mode', 'light' );
		?>
		<select id="wp_top_5_display_mode" name="wp_top_5_display_mode">
			<option value="light" <?php selected( $value, 'light' ); ?>><?php esc_html_e( 'Light', 'wp-top-5' ); ?></option>
			<option value="dark" <?php selected( $value, 'dark' ); ?>><?php esc_html_e( 'Dark', 'wp-top-5' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the display mode for the top 5 points.', 'wp-top-5' ); ?></p>
		<?php
	}

	/**
	 * Initialize the update checker.
	 */
	public static function wp_top_5_init_update_checker() {
		add_filter( 'plugins_api', array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_plugins_api_handler' ), 10, 3 );
		add_filter( 'site_transient_update_plugins', array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_update_checker' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( 'Wp_Top_5_Admin_Settings', 'wp_top_5_update_checker' ) );
	}

	/**
	 * Handle the plugin information request.
	 *
	 * @param false|object|array $result The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Plugin Install API.
	 * @param object             $args   Plugin API arguments.
	 * @return false|object|array
	 */
	public static function wp_top_5_plugins_api_handler( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( isset( $args->slug ) && 'wp-top-5' === $args->slug ) {
			$response = wp_remote_get( 'https://oneclickcontent.com/wp-json/wptop5/v1/update-wp5?version=' . WP_TOP_5_VERSION );
			if ( is_wp_error( $response ) ) {
				return $result;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data['new_version'] ) ) {
				// Prepare the result object.
				$result                = new stdClass();
				$result->name          = 'WP Top 5';
				$result->slug          = 'wp-top-5';
				$result->plugin_name   = 'WP Top 5';
				$result->version       = $data['new_version'];
				$result->author        = 'OneClickContent';
				$result->homepage      = 'https://oneclickcontent.com';
				$result->download_link = $data['download_url'];
				$result->banners       = array(
					'low'  => $data['icons']['1x'],
					'high' => $data['icons']['2x'],
				);

				// Add description and changelog from the response.
				$result->sections = array(
					'description' => $data['sections']['description'] ?? 'A description of your plugin.',
					'changelog'   => $data['sections']['changelog'] ?? 'Changelog details here.',
				);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Check for plugin updates.
	 *
	 * @param object $transient The update transient.
	 * @return object The modified transient.
	 */
	public static function wp_top_5_update_checker( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$plugin_file = 'wp-top-5/wp-top-5.php'; // Adjust this to the correct path.
		if ( isset( $transient->response[ $plugin_file ] ) ) {
			return $transient;
		}

		$response = wp_remote_get( 'https://oneclickcontent.com/wp-json/wptop5/v1/update-wp5?version=' . WP_TOP_5_VERSION );
		if ( is_wp_error( $response ) ) {
			return $transient;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['new_version'] ) && version_compare( WP_TOP_5_VERSION, $data['new_version'], '<' ) ) {
			$update_data = array(
				'new_version'   => $data['new_version'],
				'package'       => $data['download_url'],
				'slug'          => 'wp-top-5',
				'plugin'        => $plugin_file,
				'url'           => $data['url'],
				'icons'         => $data['icons'],
				'changelog_url' => $data['changelog_url'],
			);

			$transient->response[ $plugin_file ] = (object) $update_data;
		}

		return $transient;
	}

	/**
	 * Auto-save settings via AJAX.
	 *
	 * @since 1.0.0
	 */
	public static function wp_top_5_auto_save() {
		// Check AJAX nonce for security.
		check_ajax_referer( 'wp_top_5_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wp-top-5' ) ) );
		}

		if ( isset( $_POST['field_name'], $_POST['field_value'] ) ) {
			$field_name  = sanitize_text_field( wp_unslash( $_POST['field_name'] ) );
			$field_value = sanitize_text_field( wp_unslash( $_POST['field_value'] ) );

			if ( update_option( $field_name, $field_value ) ) {
				wp_send_json_success( array( 'message' => __( 'Option saved.', 'wp-top-5' ) ) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to save option.', 'wp-top-5' ) ) );
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'wp-top-5' ) ) );
		}
	}

	/**
	 * Callback for the Assistant ID field.
	 */
	public static function wp_top_5_assistant_id_callback() {
		$default_assistant_id = 'asst_L4j2SowCX4dFnz8Vn6GZ4bp0';
		$value                = get_option( 'wp_top_5_assistant_id', $default_assistant_id );

		echo '<input type="text" name="wp_top_5_assistant_id" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Enter the Assistant ID provided by OpenAI. The default ID is asst_L4j2SowCX4dFnz8Vn6GZ4bp0.', 'wp-top-5' ) . '</p>';
	}
}
