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
		?>
		<div id="summaraize" class="wrap">
			<h1><?php esc_html_e( 'SummarAIze Settings', 'summaraize' ); ?></h1>
			
			<h2 class="nav-tab-wrapper">
				<a href="#main-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'Main Settings', 'summaraize' ); ?></a>
				<a href="#advanced-settings" class="nav-tab"><?php esc_html_e( 'Advanced Settings', 'summaraize' ); ?></a>
			</h2>

			<form class="summaraize-settings-form" method="post" action="options.php">
				<?php settings_fields( 'summaraize_settings' ); ?>
				
				<div id="main-settings" class="tab-content">
					<?php do_settings_sections( 'summaraize_settings' ); ?>
				</div>

				<div id="advanced-settings" class="tab-content" style="display:none;">
					<h2><?php esc_html_e( 'Advanced Settings', 'summaraize' ); ?></h2>

					<h4 class="description summaraize-alert">
						<?php esc_html_e( 'If you modify the Assistant Prompt Type, Custom Instructions, or AI Model, please regenerate the Assistant to apply these changes. If you encounter issues, try reverting to the default settings.', 'summaraize' ); ?>
					</h4>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'AI Model', 'summaraize' ); ?></th>
							<td><?php $this->summaraize_ai_model_callback(); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Assistant Prompt Type', 'summaraize' ); ?></th>
							<td><?php $this->summaraize_prompt_type_callback(); ?></td>
						</tr>
						<tr id="summaraize_custom_prompt_row" style="display: none;">
							<th scope="row"><?php esc_html_e( 'Custom Assistant Instructions', 'summaraize' ); ?></th>
							<td><?php $this->summaraize_custom_prompt_callback(); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Reset Assistant ID', 'summaraize' ); ?></th>
							<td>
								<button id="summariaze_create_assistant" class="button button-secondary">
									<?php esc_html_e( 'Regenerate Assistant', 'summaraize' ); ?>
								</button>
								<p class="description"><?php esc_html_e( 'This will clear the current Assistant ID and generate a new one.', 'summaraize' ); ?></p>
							</td>
						</tr>
					</table>
				</div>


				<?php submit_button(); ?>
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
		// Register the API key setting with sanitization.
		register_setting(
			'summaraize_settings',
			'summaraize_openai_api_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Add the main settings section.
		add_settings_section(
			'summaraize_settings_section',
			__( 'SummarAIze Settings', 'summaraize' ),
			array( $this, 'summaraize_settings_section_callback' ),
			'summaraize_settings'
		);

		// Add the OpenAI API key field.
		add_settings_field(
			'summaraize_openai_api_key',
			__( 'OpenAI API Key', 'summaraize' ),
			array( $this, 'summaraize_openai_api_key_callback' ),
			'summaraize_settings',
			'summaraize_settings_section',
			array( 'label_for' => 'summaraize_openai_api_key' )
		);

		// Retrieve the API key.
		$api_key = get_option( 'summaraize_openai_api_key' );

		// Check if the API key is valid.
		if ( ! empty( $api_key ) && self::validate_openai_api_key( $api_key ) ) {
			// Register other settings if the API key is valid.
			$this->register_summaraize_main_settings_fields();
			$this->register_summaraize_advanced_settings_fields();
		} else {
			// Display an error message if the API key is invalid.
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
	 * Register the main settings fields if the API key is valid.
	 */
	private function register_summaraize_main_settings_fields() {
		register_setting( 'summaraize_settings', 'summaraize_post_types' );
		register_setting( 'summaraize_settings', 'summaraize_assistant_id' );
		register_setting( 'summaraize_settings', 'summaraize_widget_title' );
		register_setting( 'summaraize_settings', 'summaraize_display_position' );
		register_setting( 'summaraize_settings', 'summaraize_display_mode' );
		register_setting( 'summaraize_settings', 'summaraize_button_style' );
		register_setting( 'summaraize_settings', 'summaraize_button_color' );
		register_setting( 'summaraize_settings', 'summaraize_list_type' );

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

		add_settings_field(
			'summaraize_list_type',
			__( 'List Type', 'summaraize' ),
			array( $this, 'summaraize_list_type_callback' ),
			'summaraize_settings',
			'summaraize_settings_section'
		);
	}

	/**
	 * Register the advanced settings fields if the API key is valid.
	 */
	private function register_summaraize_advanced_settings_fields() {
		register_setting( 'summaraize_settings_advanced', 'summaraize_prompt_type' );
		register_setting( 'summaraize_settings_advanced', 'summaraize_custom_prompt' );
		register_setting( 'summaraize_settings_advanced', 'summaraize_ai_model' );

		add_settings_field(
			'summaraize_prompt_type',
			__( 'Assistant Prompt Type', 'summaraize' ),
			array( $this, 'summaraize_prompt_type_callback' ),
			'summaraize_settings_advanced',
			'summaraize_advanced_settings_section'
		);

		add_settings_field(
			'summaraize_custom_prompt',
			__( 'Custom Assistant Instructions', 'summaraize' ),
			array( $this, 'summaraize_custom_prompt_callback' ),
			'summaraize_settings_advanced',
			'summaraize_advanced_settings_section',
			array( 'class' => 'summaraize_custom_prompt_field' )
		);

		add_settings_field(
			'summaraize_ai_model',
			__( 'AI Model', 'summaraize' ),
			array( $this, 'summaraize_ai_model_callback' ),
			'summaraize_settings_advanced',
			'summaraize_advanced_settings_section'
		);
	}



	/**
	 * Callback function for the List Type setting field.
	 *
	 * This function outputs a dropdown menu allowing users to select the type of list
	 * (ordered or unordered) for displaying key points generated by the SummarAIze plugin.
	 * The selected option is saved to the WordPress options table and used to control
	 * the list format in the front-end display.
	 *
	 * @return void
	 */
	public function summaraize_list_type_callback() {
		$list_type = get_option( 'summaraize_list_type', 'unordered' );
		?>
		<select name="summaraize_list_type" id="summaraize_list_type">
			<option value="unordered" <?php selected( $list_type, 'unordered' ); ?>><?php esc_html_e( 'Bullet List', 'summaraize' ); ?></option>
			<option value="ordered" <?php selected( $list_type, 'ordered' ); ?>><?php esc_html_e( 'Ordered List', 'summaraize' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the type of list for displaying the key points.', 'summaraize' ); ?></p>
		<?php
	}


	/**
	 * Callback for the custom prompt field.
	 */
	public function summaraize_custom_prompt_callback() {
		$value       = get_option( 'summaraize_custom_prompt', '' );
		$prompt_type = get_option( 'summaraize_prompt_type', '' );

		// Generate a unique ID for the custom prompt textarea.
		$unique_id = 'summaraize_custom_prompt_custom';

		// Only display the textarea if 'custom' is selected.
		$style = ( 'custom' === $prompt_type ) ? '' : 'display:none;';

		echo '<textarea id="' . esc_attr( $unique_id ) . '" name="summaraize_custom_prompt" rows="5" cols="50" style="' . esc_attr( $style ) . '">' . esc_textarea( $value ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Add your custom instructions or description for the Assistant. These will be prepended to the default instructions.', 'summaraize' ) . '</p>';
	}



	/**
	 * Callback for the custom prompt dropdown.
	 */
	public function summaraize_prompt_type_callback() {
		$value   = get_option( 'summaraize_prompt_type', '' );
		$options = array(
			''                 => 'Default',
			'formal'           => 'Formal and Professional',
			'statistics'       => 'Focus on Statistics',
			'non_expert'       => 'Understandable for Non-Experts',
			'summary_first'    => 'Include a Summary Sentence',
			'concise'          => 'Limit to Two Sentences',
			'actionable'       => 'Highlight Actionable Insights',
			'exclude_politics' => 'Avoid Politics',
			'spanish'          => 'Translate to Spanish',
			'explanation'      => 'Include Explanation of Importance',
			'environment'      => 'Relevant to Environmental Sustainability',
			'custom'           => 'Custom',
		);

		echo '<select id="summaraize_prompt_type" name="summaraize_prompt_type">';
		foreach ( $options as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Choose a predefined instruction set for the Assistant or select Custom to provide your own.', 'summaraize' ) . '</p>';
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
	 * Callback function for the Post Types setting field.
	 *
	 * @return void
	 */
	public function summaraize_post_types_callback() {
		// Ensure $selected_post_types is always an array.
		$selected_post_types = (array) get_option( 'summaraize_post_types', array( 'post' ) );
		$post_types          = get_post_types( array( 'public' => true ), 'names', 'and' );
		unset( $post_types['attachment'] );

		echo '<p>' . esc_html__( 'Select which post types SummarAIze should be enabled on:', 'summaraize' ) . '</p>';
		echo '<p><em>' . esc_html__( 'Custom post types must have the editor enabled.', 'summaraize' ) . '</em></p>';

		foreach ( $post_types as $post_type ) {
			$checked         = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';
			$post_type_label = str_replace( '_', ' ', ucwords( $post_type ) );

			echo '<div class="summaraize-toggle-wrapper">';
			echo '<label class="toggle-switch">';
			echo '<input type="checkbox" name="summaraize_post_types[]" value="' . esc_attr( $post_type ) . '" ' . esc_attr( $checked ) . '>';
			echo '<span class="slider"></span>';
			echo '</label>';
			echo '<span class="post-type-label">' . esc_html( $post_type_label ) . '</span>';
			echo '</div>';
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

		if ( ! isset( $_POST['field_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing field name.', 'summaraize' ) ) );
		}

		// Define allowed option keys.
		$allowed_options = array(
			'summaraize_openai_api_key',
			'summaraize_post_types',
			'summaraize_assistant_id',
			'summaraize_widget_title',
			'summaraize_display_position',
			'summaraize_display_mode',
			'summaraize_button_style',
			'summaraize_button_color',
			'summaraize_list_type',
			'summaraize_prompt_type',
			'summaraize_custom_prompt',
			'summaraize_ai_model',
		);

		$field_name = sanitize_text_field( wp_unslash( $_POST['field_name'] ) );
		$option_key = sanitize_key( str_replace( '[]', '', $field_name ) ); // Use sanitize_key() for option keys.

		// Check if the option key is in the allowed options.
		if ( ! in_array( $option_key, $allowed_options, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid option key.', 'summaraize' ) ) );
		}

		// Unslash and sanitize field_value simultaneously, allow empty values.
		if ( isset( $_POST['field_value'] ) && is_array( $_POST['field_value'] ) ) {
			$field_value = array_map( 'sanitize_text_field', wp_unslash( $_POST['field_value'] ) );
		} else {
			$field_value = isset( $_POST['field_value'] ) ? sanitize_text_field( wp_unslash( $_POST['field_value'] ) ) : '';
		}

		// Ensure field_value is an array if it's supposed to be one (for summaraize_post_types).
		if ( 'summaraize_post_types' === $option_key && empty( $field_value ) ) {
			$field_value = array(); // Handle the empty case.
		}

		// Save the option.
		if ( update_option( $option_key, $field_value ) || get_option( $option_key ) === $field_value ) {
			// Validate the API key if it's the API key field.
			if ( 'summaraize_openai_api_key' === $option_key && ! self::validate_openai_api_key( $field_value ) ) {
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
	private function summaraize_create_assistant() {
		$api_key        = get_option( 'summaraize_openai_api_key' );
		$selected_model = get_option( 'summaraize_ai_model', 'gpt-4o-mini' );
		if ( empty( $api_key ) ) {
			return false;
		}

		$prompt_type   = get_option( 'summaraize_prompt_type', '' );
		$custom_prompt = get_option( 'summaraize_custom_prompt', '' );

		$default_prompt = array(
			'description' => 'This Assistant extracts the top 5 key points from a given article and returns them in a JSON format being sure to use the `extract_key_points` function.',
			'behavior'    => array(
				array(
					'trigger'     => 'message',
					'instruction' => "When provided with a message containing the content of an article, analyze the article and identify the top 5 key points. Call the `extract_key_points` function to return these points in a JSON format. The expected JSON format is:\n[\n  { \"index\": 1, \"text\": \"Point 1 content\" },\n  { \"index\": 2, \"text\": \"Point 2 content\" },\n  { \"index\": 3, \"text\": \"Point 3 content\" },\n  { \"index\": 4, \"text\": \"Point 4 content\" },\n  { \"index\": 5, \"text\": \"Point 5 content\" }\n]",
				),
			),
		);

		$predefined_prompts = array(
			'formal'           => 'Ensure the language used in the key points is formal and professional.',
			'statistics'       => 'Focus on key points that mention data or statistics.',
			'non_expert'       => 'Summarize the key points in a way that is easily understandable for non-experts.',
			'summary_first'    => 'Include a short summary sentence before listing the key points.',
			'concise'          => 'Limit each key point to no more than two sentences.',
			'actionable'       => 'Highlight actionable insights or recommendations as key points.',
			'exclude_politics' => 'Avoid including points that mention politics.',
			'spanish'          => 'Translate the key points into Spanish before returning the JSON.',
			'explanation'      => 'Provide a brief explanation of why each key point is important.',
			'environment'      => 'Extract key points that are relevant to environmental sustainability.',
		);

		if ( 'custom' === $prompt_type && ! empty( $custom_prompt ) ) {
			$default_prompt['description'] = $custom_prompt . "\n\n" . $default_prompt['description'];
		} elseif ( array_key_exists( $prompt_type, $predefined_prompts ) ) {
			$default_prompt['description'] = $predefined_prompts[ $prompt_type ] . "\n\n" . $default_prompt['description'];
		}

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
			'instructions'    => wp_json_encode( $default_prompt ),
			'name'            => 'SummarAIze Assistant',
			'tools'           => array(
				array(
					'type'     => 'function',
					'function' => $function_definition,
				),
			),
			'model'           => $selected_model,
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
	 * Callback function for the AI Model setting field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function summaraize_ai_model_callback() {
		$selected_model = get_option( 'summaraize_ai_model', 'gpt-4o-mini' );
		$api_key        = get_option( 'summaraize_openai_api_key' );

		if ( ! empty( $api_key ) ) {
			$models = self::validate_openai_api_key( $api_key );

			if ( $models && is_array( $models ) ) {
				echo '<select id="summaraize_ai_model" name="summaraize_ai_model">';

				echo '<option value="gpt-4o-mini"' . selected( $selected_model, 'gpt-4o-mini', false ) . '>' . esc_html__( 'Default (gpt-4o-mini)', 'summaraize' ) . '</option>';

				foreach ( $models as $model ) {
					echo '<option value="' . esc_attr( $model ) . '"' . selected( $selected_model, $model, false ) . '>' . esc_html( $model ) . '</option>';
				}
				echo '</select>';
				echo '<p class="description">';
				esc_html_e( 'These models support the function calling ability that is required to use SummarAIze.', 'summaraize' );
				echo '</p>';
			} else {
				echo '<p class="summaraize-alert">';
				esc_html_e( 'Unable to retrieve models. Please check your API key.', 'summaraize' );
				echo '</p>';
			}
		} else {
			echo '<p class="summaraize-alert">';
			esc_html_e( 'Please enter a valid OpenAI API key first.', 'summaraize' );
			echo '</p>';
		}
	}



	/**
	 * Handles AJAX request to validate the OpenAI API key.
	 *
	 * This function checks the validity of the provided API key by sending a request
	 * to the OpenAI API and verifying the response. If the API key is valid, it returns
	 * a success response with the available models; otherwise, it returns an error.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function summaraize_ajax_validate_openai_api_key() {
		if ( ! check_ajax_referer( 'summaraize_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'summaraize' ) ) );
		}

		if ( ! isset( $_POST['api_key'] ) ) {
			wp_send_json_error( array( 'message' => __( 'API key is missing.', 'summaraize' ) ) );
		}

		$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );

		$models = self::validate_openai_api_key( $api_key );

		if ( false !== $models ) {
			wp_send_json_success(
				array(
					'message' => __( 'API key is valid.', 'summaraize' ),
					'models'  => $models,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'API key is invalid or no suitable models found.', 'summaraize' ) ) );
		}
	}


	/**
	 * Validates the OpenAI API key and fetches models that support function calling.
	 *
	 * @since 1.0.0
	 * @param string $api_key The API key to validate.
	 * @return array|bool List of models if successful, false otherwise.
	 */
	public static function validate_openai_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}

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

		$function_calling_cutoff          = 1686614400;
		$parallel_function_calling_cutoff = 1699228800;

		if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
			$models = array_filter(
				$data['data'],
				function ( $model ) use ( $function_calling_cutoff, $parallel_function_calling_cutoff ) {
					return isset( $model['created'] ) && (
						$model['created'] >= $function_calling_cutoff ||
						$model['created'] >= $parallel_function_calling_cutoff
					);
				}
			);

			if ( ! empty( $models ) ) {
				return array_map(
					function ( $model ) {
						return $model['id'];
					},
					$models
				);
			}
		}

		return false;
	}
}