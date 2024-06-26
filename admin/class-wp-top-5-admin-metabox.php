<?php
/**
 * Class Wp_Top_5_Admin_Metabox
 *
 * Handles the addition and rendering of the meta box, and saving the meta box data.
 *
 * @since 1.0.0
 * @package Wp_Top_5
 */

/**
 * Class Wp_Top_5_Admin_Metabox
 */
class Wp_Top_5_Admin_Metabox {

	/**
	 * Add meta box to post edit screen.
	 *
	 * @since 1.0.0
	 */
	public static function add_meta_box() {
		// Get selected post types from the plugin settings.
		$post_types = get_option( 'wp_top_5_post_types', array() );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'wp_top_5_meta_box',
				__( 'WP Top 5 Pro', 'wp-top-5' ),
				array( 'Wp_Top_5_Admin_Metabox', 'render_meta_box' ),
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
	 *
	 * @param WP_Post $post The current post.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'wp_top_5_meta_box', 'wp_top_5_meta_box_nonce' );

		$top_5_points_meta = get_post_meta( $post->ID, 'wp_top_5_points', true );
		$top_5_points      = ! empty( $top_5_points_meta ) ? $top_5_points_meta : array_fill( 0, 5, '' );

		echo '<button id="generate-top-5-button">Generate Top 5 Points</button>';
		echo '<div id="loading-icon" style="display:none;">Thinking...</div>';
		echo '<div id="top-5-points-list" class="list-group">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$point = isset( $top_5_points[ $i - 1 ] ) ? $top_5_points[ $i - 1 ] : '';
			// Remove Markdown formatting.
			$point = preg_replace( '/\*\*(.*?)\*\*/', '$1', $point );

			echo '<div style="margin-bottom: 10px;">';
			echo '<label for="wp_top_5_points_' . esc_attr( $i ) . '">' . esc_html( "Point $i:" ) . '</label>';
			echo '<input id="wp_top_5_points_' . esc_attr( $i ) . '" style="width: 100%;" type="text" name="wp_top_5_points[' . esc_attr( $i ) . ']" value="' . esc_attr( $point ) . '" placeholder="' . esc_attr( "Point $i" ) . '" />';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Save the meta box data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_top_5_points( $post_id ) {
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
		$top_5_points = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			if ( isset( $_POST['wp_top_5_points'][ $i ] ) ) {
				$top_5_points[] = sanitize_text_field( wp_unslash( $_POST['wp_top_5_points'][ $i ] ) );
			}
		}
		update_post_meta( $post_id, 'wp_top_5_points', $top_5_points );
	}
}
