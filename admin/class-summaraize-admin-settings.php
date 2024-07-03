<?php
/**
 * Class Summaraize_Admin_Settings
 *
 * Manages the admin settings page for the SummarAIze plugin.
 *
 * @since 1.0.0
 * @package Summaraize
 */

/**
 * Class Summaraize_Admin_Settings
 */
class Summaraize_Admin_Settings {

	/**
	 * Register the plugin settings page.
	 */
	public static function summaraize_register_options_page() {
		add_options_page(
			__( 'SummarAIze Settings', 'summaraize' ),
			__( 'SummarAIze', 'summaraize' ),
			'manage_options',
			'summaraize-settings',
			array( 'Summaraize_Admin_Settings', 'summaraize_options_page' )
		);
	}

	/**
	 * Display the options page.
	 */
	public static function summaraize_options_page() {
		?>
		<div id="summaraize" class="wrap">
			<form class="summaraize-settings-form" method="post" action="options.php">
				<?php settings_fields( 'summaraize_settings' ); ?>
				<?php do_settings_sections( 'summaraize_settings' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the plugin settings.
	 */
	public static function summaraize_register_settings() {
		register_setting( 'summaraize_settings', 'summaraize_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		add_settings_section(
			'summaraize_settings_section',
			__( 'SummarAIze Settings', 'summaraize' ),
			array( 'Summaraize_Admin_Settings', 'summaraize_settings_section_callback' ),
			'summaraize_settings'
		);

		add_settings_field(
			'summaraize_openai_api_key',
			__( 'OpenAI API Key', 'summaraize' ),
			array( 'Summaraize_Admin_Settings', 'summaraize_openai_api_key_callback' ),
			'summaraize_settings',
			'summaraize_settings_section',
			array( 'label_for' => 'summaraize_openai_api_key' )
		);

		$api_key = get_option( 'summaraize_openai_api_key' );

		if ( ! empty( $api_key ) && self::validate_openai_api_key( $api_key ) ) {
			register_setting( 'summaraize_settings', 'summaraize_post_types' );
			register_setting( 'summaraize_settings', 'summaraize_assistant_id' );
			register_setting( 'summaraize_settings', 'summaraize_widget_title' );
			register_setting( 'summaraize_settings', 'summaraize_display_position' );
			register_setting( 'summaraize_settings', 'summaraize_display_mode' );
			register_setting( 'summaraize_settings', 'summaraize_button_style' );
			register_setting( 'summaraize_settings', 'summaraize_button_color' );

			add_settings_field(
				'summaraize_post_types',
				__( 'Post Types', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_post_types_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_assistant_id',
				__( 'Assistant ID', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_assistant_id_callback' ),
				'summaraize_settings',
				'summaraize_settings_section',
				array( 'label_for' => 'summaraize_assistant_id' )
			);

			add_settings_field(
				'summaraize_widget_title',
				__( 'Widget Title', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_widget_title_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_display_position',
				__( 'Display Position', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_display_position_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_display_mode',
				__( 'Display Mode', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_display_mode_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_button_style',
				__( 'Button Style', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_button_style_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_button_color',
				__( 'Button Color', 'summaraize' ),
				array( 'Summaraize_Admin_Settings', 'summaraize_button_color_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);
		} else {
			add_settings_error(
				'summaraize_openai_api_key',
				'invalid-api-key',
				sprintf(
					/* translators: %s: URL to SummarAIze settings page */
					__( 'The OpenAI API key is invalid. Please enter a valid API key in the <a href="%s">SummarAIze settings</a> to use SummarAIze.', 'summaraize' ),
					esc_url( admin_url( 'options-general.php?page=summaraize-settings' ) )
				),
				'error'
			);

		}
	}

	/**
	 * Callback for the widget title field.
	 */
	public static function summaraize_widget_title_callback() {
		$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
		?>
		<input type="text" name="summaraize_widget_title" id="summaraize_widget_title" value="<?php echo esc_attr( $widget_title ); ?>" />
		<p class="description"><?php esc_html_e( 'Enter the title for the widget.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the button style field.
	 */
	public static function summaraize_button_style_callback() {
		$selected_style = get_option( 'summaraize_button_style', 'flat' );
		$button_styles  = array(
			'flat'        => __( 'Flat', 'summaraize' ),
			'rounded'     => __( 'Rounded', 'summaraize' ),
			'angled'      => __( 'Angled', 'summaraize' ),
			'apple'       => __( 'Apple', 'summaraize' ),
			'google'      => __( 'Google', 'summaraize' ),
			'bubbly'      => __( 'Bubbly', 'summaraize' ),
			'material'    => __( 'Material', 'summaraize' ),
			'windows'     => __( 'Windows', 'summaraize' ),
			'neumorphism' => __( 'Neumorphism', 'summaraize' ),
			'3d'          => __( '3D', 'summaraize' ),
		);
		?>
		<select name="summaraize_button_style" id="summaraize_button_style">
			<?php foreach ( $button_styles as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected_style, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the style for the button.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the button color field.
	 */
	public static function summaraize_button_color_callback() {
		$button_color = get_option( 'summaraize_button_color', '#0073aa' );
		?>
		<input type="color" name="summaraize_button_color" id="summaraize_button_color" value="<?php echo esc_attr( $button_color ); ?>" />
		<p class="description"><?php esc_html_e( 'Choose the color for the button.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the display position field.
	 */
	public static function summaraize_display_position_callback() {
		$selected_position = get_option( 'summaraize_display_position', 'above' );
		?>
		<select name="summaraize_display_position" id="summaraize_display_position">
			<option value="above" <?php selected( $selected_position, 'above' ); ?>><?php esc_html_e( 'Above Content', 'summaraize' ); ?></option>
			<option value="below" <?php selected( $selected_position, 'below' ); ?>><?php esc_html_e( 'Below Content', 'summaraize' ); ?></option>
			<option value="popup" <?php selected( $selected_position, 'popup' ); ?>><?php esc_html_e( 'Popup', 'summaraize' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose where to display the key points.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the post types field.
	 */
	public static function summaraize_post_types_callback() {
		$selected_post_types = get_option( 'summaraize_post_types', array() );

		if ( empty( $selected_post_types ) ) {
			$selected_post_types = array( 'post' );
		}

		$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );

		echo '<p>' . esc_html__( 'Select which post types SummarAIze should be enabled on:', 'summaraize' ) . '</p>';
		echo '<p><em>' . esc_html__( 'Custom post types must have titles enabled.', 'summaraize' ) . '</em></p>';

		foreach ( $post_types as $post_type ) {
			$checked         = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';
			$post_type_label = str_replace( '_', ' ', ucwords( $post_type ) );
			echo '<input type="checkbox" name="summaraize_post_types[]" value="' . esc_attr( $post_type ) . '" class="summaraize-settings-checkbox" ' . esc_attr( $checked ) . '> ' . esc_html( $post_type_label ) . '<br>';
		}
	}

	/**
	 * Callback for the settings section.
	 */
	public static function summaraize_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the settings for the SummarAIze Pro plugin.', 'summaraize' ) . '</p>';
	}

	/**
	 * Callback for the OpenAI API key field.
	 */
	public static function summaraize_openai_api_key_callback() {
		$value = get_option( 'summaraize_openai_api_key', '' );
		echo '<input type="password" name="summaraize_openai_api_key" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . wp_kses_post( __( 'Get your OpenAI API Key <a href="https://beta.openai.com/signup/">here</a>.', 'summaraize' ) ) . '</p>';
	}

	/**
	 * Callback for the Display Mode field.
	 */
	public static function summaraize_display_mode_callback() {
		$value = get_option( 'summaraize_display_mode', 'light' );
		?>
		<select id="summaraize_display_mode" name="summaraize_display_mode">
			<option value="light" <?php selected( $value, 'light' ); ?>><?php esc_html_e( 'Light', 'summaraize' ); ?></option>
			<option value="dark" <?php selected( $value, 'dark' ); ?>><?php esc_html_e( 'Dark', 'summaraize' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the display mode for the key points.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Auto-save settings via AJAX.
	 *
	 * @since 1.0.0
	 */
	public static function summaraize_auto_save() {
		// Check AJAX nonce for security.
		check_ajax_referer( 'summaraize_ajax_nonce', 'nonce' );

		// Verify the user has the appropriate capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'summaraize' ) ) );
		}

		if ( ! isset( $_POST['field_name'] ) || ! isset( $_POST['field_value'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'summaraize' ) ) );
		}

		$field_name = sanitize_text_field( wp_unslash( $_POST['field_name'] ) );

		// Unslash and sanitize field_value simultaneously.
		$field_value = sanitize_text_field( wp_unslash( $_POST['field_value'] ) );
		$field_value = is_array( $field_value )
			? array_map( 'sanitize_text_field', $field_value )
			: sanitize_text_field( $field_value );

		// Use `update_option` with a proper option key.
		$option_key = str_replace( '[]', '', $field_name ); // Ensure correct option key format.

		// Use Yoda condition checks.
		if ( update_option( $option_key, $field_value ) || get_option( $option_key ) === $field_value ) {
			// Validate the API key if it's the API key field.
			if ( 'summaraize_openai_api_key' === $field_name && ! self::validate_openai_api_key( $field_value ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Invalid API key. Please enter a valid API key.', 'summaraize' ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'message' => __( 'Option saved.', 'summaraize' ),
					)
				);
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save option.', 'summaraize' ) ) );
		}
	}


	/**
	 * Callback for the Assistant ID field.
	 */
	public static function summaraize_assistant_id_callback() {
		$default_assistant_id = 'asst_L4j2SowCX4dFnz8Vn6GZ4bp0';
		$value                = get_option( 'summaraize_assistant_id', $default_assistant_id );

		if ( $value === $default_assistant_id && get_option( 'summaraize_assistant_id' ) === false ) {
			update_option( 'summaraize_assistant_id', $default_assistant_id );
		}

		echo '<input type="text" id="summaraize_assistant_id" name="summaraize_assistant_id" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Enter the Assistant ID provided by OpenAI. The default ID is asst_L4j2SowCX4dFnz8Vn6GZ4bp0.', 'summaraize' ) . '</p>';
	}

	/**
	 * Validate the OpenAI API key.
	 *
	 * @param string $api_key The OpenAI API key to validate.
	 * @return bool True if the API key is valid, false otherwise.
	 */
	public static function validate_openai_api_key( $api_key ) {
		$response = wp_remote_get(
			'https://api.openai.com/v1/models',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return isset( $data['data'] ) && is_array( $data['data'] );
	}
}