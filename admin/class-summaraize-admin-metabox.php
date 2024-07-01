<?php
/**
 * Class Summaraize_Admin_Metabox
 *
 * Handles the addition and rendering of the meta box, and saving the meta box data.
 *
 * @since 1.0.0
 * @package Summaraize
 */

/**
 * Class Summaraize_Admin_Metabox
 */
class Summaraize_Admin_Metabox {

	/**
	 * Add meta box to post edit screen.
	 *
	 * @since 1.0.0
	 */
	public static function add_meta_box() {
		// Get the OpenAI API key from the plugin settings.
		$api_key = get_option( 'summaraize_openai_api_key' );

		// Validate the API key.
		if ( ! empty( $api_key ) && Summaraize_Admin_Settings::validate_openai_api_key( $api_key ) ) {
			// Get selected post types from the plugin settings.
			$post_types = get_option( 'summaraize_post_types', array() );

			foreach ( $post_types as $post_type ) {
				if ( post_type_exists( $post_type ) ) {
					add_meta_box(
						'summaraize_meta_box',
						__( 'SummarAIze', 'summaraize' ),
						array( 'Summaraize_Admin_Metabox', 'render_meta_box' ),
						$post_type,
						'side',
						'default'
					);
				}
			}
		}
	}


	/**
	 * Initialize hooks for adding meta boxes.
	 *
	 * @since 1.0.0
	 */
	public static function init_hooks() {
		add_action( 'add_meta_boxes', array( 'Summaraize_Admin_Metabox', 'add_meta_box' ) );
	}

	/**
	 * Render the meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The current post.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'summaraize_meta_box', 'summaraize_meta_box_nonce' );

		$summaraize_points_meta = get_post_meta( $post->ID, 'summaraize_points', true );
		$summaraize_points      = ! empty( $summaraize_points_meta ) ? $summaraize_points_meta : array_fill( 0, 5, '' );

		echo '<button id="generate-summaraize-button"><div class="summaraize-spinner" style="display: none;"></div>Generate Top 5 Points</button>';
		echo '<div id="summaraize-points-list" class="list-group">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$point = isset( $summaraize_points[ $i - 1 ] ) ? $summaraize_points[ $i - 1 ] : '';
			// Remove Markdown formatting.
			$point = preg_replace( '/\*\*(.*?)\*\*/', '$1', $point );

			echo '<div style="margin-bottom: 10px;">';
			echo '<label for="summaraize_points_' . esc_attr( $i ) . '">' . esc_html( "Point $i:" ) . '</label>';
			echo '<input id="summaraize_points_' . esc_attr( $i ) . '" style="width: 100%;" type="text" name="summaraize_points[' . esc_attr( $i ) . ']" value="' . esc_attr( $point ) . '" placeholder="' . esc_attr( "Point $i" ) . '" />';
			echo '</div>';
		}

		echo '</div>';
	}


	/**
	 * Save the meta box data. Not in use in favor of AJAX save. Only CSS hiding the submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_summaraize_points( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['summaraize_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['summaraize_meta_box_nonce'] ) ), 'summaraize_meta_box' ) ) {
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
		$summaraize_points = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			if ( isset( $_POST['summaraize_points'][ $i ] ) ) {
				$summaraize_points[] = sanitize_text_field( wp_unslash( $_POST['summaraize_points'][ $i ] ) );
			}
		}
		update_post_meta( $post_id, 'summaraize_points', $summaraize_points );
	}
}
