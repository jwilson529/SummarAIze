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
	public function summaraize_register_options_page() {
		add_options_page(
			__( 'SummarAIze Settings', 'summaraize' ),
			__( 'SummarAIze', 'summaraize' ),
			'manage_options',
			'summaraize-settings',
			array( $this, 'summaraize_options_page' )
		);
	}

	/**
	 * Display the options page.
	 */
	public function summaraize_options_page() {

		$api_key = get_option( 'summaraize_openai_api_key' );

		?>
		<div id="summaraize" class="wrap">
			<form class="summaraize-settings-form" method="post" action="">
				<?php settings_fields( 'summaraize_settings' ); ?>
				<?php do_settings_sections( 'summaraize_settings' ); ?>
				<?php submit_button(); ?>
				
				<?php if ( ! empty( $api_key ) && self::validate_openai_api_key( $api_key ) ) : ?>
					<h2><?php esc_html_e( 'Assistant Settings', 'summaraize' ); ?></h2>
					<button type="submit" id="summariaze_create_assistant" class="button button-secondary"><?php esc_html_e( 'Create Assistant', 'summaraize' ); ?></button>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}



	/**
	 * Display admin notices for settings.
	 */
	public function display_admin_notices() {
		settings_errors();
	}

	/**
	 * Hook to handle the assistant creation.
	 */
	public function summaraize_handle_assistant_creation() {
		// Log that the function was called.

		// Check if the create assistant button was clicked.
		if ( isset( $_POST['summariaze_create_assistant'] ) ) {

			// Check nonce for security.
			if ( check_admin_referer( 'summaraize_ajax_nonce', 'summaraize_create_assistant_nonce' ) ) {

				// Attempt to create the assistant.
				$assistant_id = $this->summaraize_create_assistant();

				if ( $assistant_id ) {

					update_option( 'summaraize_assistant_id', $assistant_id );
					add_settings_error( 'summaraize_assistant_id', 'assistant-created', __( 'Assistant successfully created.', 'summaraize' ), 'updated' );
				} else {

					add_settings_error( 'summaraize_assistant_id', 'assistant-creation-failed', __( 'Failed to create assistant.', 'summaraize' ), 'error' );
				}
			} else {

				add_settings_error( 'summaraize_assistant_id', 'nonce-failed', __( 'Nonce verification failed.', 'summaraize' ), 'error' );
			}
		}
	}



	/**
	 * Register the plugin settings.
	 */
	public function summaraize_register_settings() {
		register_setting( 'summaraize_settings', 'summaraize_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		add_settings_section(
			'summaraize_settings_section',
			__( 'SummarAIze Settings', 'summaraize' ),
			array( $this, 'summaraize_settings_section_callback' ),
			'summaraize_settings'
		);

		add_settings_field(
			'summaraize_openai_api_key',
			__( 'OpenAI API Key', 'summaraize' ),
			array( $this, 'summaraize_openai_api_key_callback' ),
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
				array( $this, 'summaraize_post_types_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_assistant_id',
				__( 'Assistant ID', 'summaraize' ),
				array( $this, 'summaraize_assistant_id_callback' ),
				'summaraize_settings',
				'summaraize_settings_section',
				array( 'label_for' => 'summaraize_assistant_id' )
			);

			add_settings_field(
				'summaraize_widget_title',
				__( 'Widget Title', 'summaraize' ),
				array( $this, 'summaraize_widget_title_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_display_position',
				__( 'Display Position', 'summaraize' ),
				array( $this, 'summaraize_display_position_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_display_mode',
				__( 'Display Mode', 'summaraize' ),
				array( $this, 'summaraize_display_mode_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_button_style',
				__( 'Button Style', 'summaraize' ),
				array( $this, 'summaraize_button_style_callback' ),
				'summaraize_settings',
				'summaraize_settings_section'
			);

			add_settings_field(
				'summaraize_button_color',
				__( 'Button Color', 'summaraize' ),
				array( $this, 'summaraize_button_color_callback' ),
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
	public function summaraize_widget_title_callback() {
		$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
		?>
		<input type="text" name="summaraize_widget_title" id="summaraize_widget_title" value="<?php echo esc_attr( $widget_title ); ?>" />
		<p class="description"><?php esc_html_e( 'Enter the title for the widget.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the button style field.
	 */
	public function summaraize_button_style_callback() {
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
	public function summaraize_button_color_callback() {
		$button_color = get_option( 'summaraize_button_color', '#0073aa' );
		?>
		<input type="color" name="summaraize_button_color" id="summaraize_button_color" value="<?php echo esc_attr( $button_color ); ?>" />
		<p class="description"><?php esc_html_e( 'Choose the color for the button.', 'summaraize' ); ?></p>
		<?php
	}

	/**
	 * Callback for the display position field.
	 */
	public function summaraize_display_position_callback() {
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
	public function summaraize_post_types_callback() {
		// Get the selected post types from the options table, or set the default to 'post'.
		$selected_post_types = get_option( 'summaraize_post_types', array( 'post' ) );

		// Retrieve all public post types, excluding 'attachment'.
		$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );
		unset( $post_types['attachment'] );

		// Output the instructions for selecting post types.
		echo '<p>' . esc_html__( 'Select which post types SummarAIze should be enabled on:', 'summaraize' ) . '</p>';
		echo '<p><em>' . esc_html__( 'Custom post types must have titles enabled.', 'summaraize' ) . '</em></p>';

		// Loop through each public post type and create a checkbox.
		foreach ( $post_types as $post_type ) {
			$checked         = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';
			$post_type_label = str_replace( '_', ' ', ucwords( $post_type ) );
			echo '<input type="checkbox" name="summaraize_post_types[]" value="' . esc_attr( $post_type ) . '" class="summaraize-settings-checkbox" ' . esc_attr( $checked ) . '> ' . esc_html( $post_type_label ) . '<br>';
		}
	}



	/**
	 * Callback for the settings section.
	 */
	public function summaraize_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the settings for the SummarAIze Pro plugin.', 'summaraize' ) . '</p>';
	}

	/**
	 * Callback for the OpenAI API key field.
	 */
	public function summaraize_openai_api_key_callback() {
		$value = get_option( 'summaraize_openai_api_key', '' );
		echo '<input type="password" name="summaraize_openai_api_key" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . wp_kses_post( __( 'Get your OpenAI API Key <a href="https://beta.openai.com/signup/">here</a>.', 'summaraize' ) ) . '</p>';
	}

	/**
	 * Callback for the Display Mode field.
	 */
	public function summaraize_display_mode_callback() {
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
	public function summaraize_auto_save() {
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
		if ( is_array( $_POST['field_value'] ) ) {
			$field_value = array_map( 'sanitize_text_field', wp_unslash( $_POST['field_value'] ) );
		} else {
			$field_value = sanitize_text_field( wp_unslash( $_POST['field_value'] ) );
		}

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
	public function summaraize_assistant_id_callback() {
		$value = get_option( 'summaraize_assistant_id', '' );

		if ( empty( $value ) ) {
			// Attempt to create a new assistant if none exists.
			$assistant_id = $this->summaraize_create_assistant();
			$value        = $assistant_id ? $assistant_id : 'Failed to create assistant';
			update_option( 'summaraize_assistant_id', $value );
		}

		echo '<input type="text" id="summaraize_assistant_id" name="summaraize_assistant_id" value="' . esc_attr( $value ) . '" />';
		echo '<p class="description">' . esc_html__( 'Enter the Assistant ID provided by OpenAI or leave as is to use the auto-generated one.', 'summaraize' ) . '</p>';
	}

	/**
	 * Create the OpenAI assistant.
	 *
	 * @since 1.0.0
	 */
	/**
	 * Create the OpenAI assistant.
	 *
	 * @since 1.0.0
	 */
	private function summaraize_create_assistant() {
		$api_key = get_option( 'summaraize_openai_api_key' );
		if ( empty( $api_key ) ) {

			return false;
		}

		$initial_prompt = array(
			'description' => 'This Assistant extracts the top 5 key points from a given article and returns them in a JSON format being sure to use the `extract_key_points` function.',
			'behavior'    => array(
				array(
					'trigger'     => 'message',
					'instruction' => "When provided with a message containing the content of an article, analyze the article and identify the top 5 key points. Call the `extract_key_points` function to return these points in a JSON format. The expected JSON format is:\n[\n  { \"index\": 1, \"text\": \"Point 1 content\" },\n  { \"index\": 2, \"text\": \"Point 2 content\" },\n  { \"index\": 3, \"text\": \"Point 3 content\" },\n  { \"index\": 4, \"text\": \"Point 4 content\" },\n  { \"index\": 5, \"text\": \"Point 5 content\" }\n]",
				),
			),
		);

		$function_definition = array(
			'name'        => 'extract_key_points',
			'description' => 'Extract the top 5 key points from the provided article content and return them in a specific JSON format.',
			'parameters'  => array(
				'type'       => 'object',
				'properties' => array(
					'points' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'index' => array(
									'type'        => 'integer',
									'description' => 'The index of the key point.',
								),
								'text'  => array(
									'type'        => 'string',
									'description' => 'The content of the key point.',
								),
							),
							'required'   => array( 'index', 'text' ),
						),
					),
				),
				'required'   => array( 'points' ),
			),
		);

		$payload = array(
			'description'     => 'Assistant for generating concise content summaries.',
			'instructions'    => wp_json_encode( $initial_prompt ),
			'name'            => 'SummarAIze Assistant',
			'tools'           => array(
				array(
					'type'     => 'function',
					'function' => $function_definition,
				),
			),
			'model'           => 'gpt-4o',
			'response_format' => array( 'type' => 'json_object' ),
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/assistants',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
					'OpenAI-Beta'   => 'assistants=v2',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body  = wp_remote_retrieve_body( $response );
		$assistant_data = json_decode( $response_body, true );

		if ( isset( $assistant_data['id'] ) ) {
			return $assistant_data['id'];
		}

		return false;
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