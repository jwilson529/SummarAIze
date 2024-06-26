<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Top_5
 * @subpackage Wp_Top_5/public
 * @author     James Wilson <james@middletnwebdesign.com>
 */
class Wp_Top_5_Public {

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
	 * @param    string $plugin_name The name of the plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-top-5-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-top-5-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Shortcode to display the top 5 points.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML content to display.
	 */
	public function wp_top_5_shortcode( $atts ) {
		// Parse shortcode attributes.
		$atts = shortcode_atts(
			array(
				'view' => get_option( 'wp_top_5_display_position', 'above' ), // Use the setting as the default value.
				'mode' => get_option( 'wp_top_5_display_mode', 'light' ), // Default to light mode if not set.
			),
			$atts,
			'wp_top_5'
		);

		// Get the current post ID.
		$post_id = get_the_ID();

		// Fetch the top 5 points.
		$top_5_points = get_post_meta( $post_id, 'wp_top_5_points', true );

		// If no points are set, display a message.
		if ( empty( $top_5_points ) ) {
			return '<p>No top 5 points have been set for this post.</p>';
		}

		// Start the output buffer.
		ob_start();

		// Display based on the view.
		if ( 'popup' === $atts['view'] ) {
			$mode_class = 'dark' === $atts['mode'] ? 'dark' : 'light';
			echo '<button class="wp-top-5-popup-btn ' . esc_attr( $mode_class ) . '">View Key Takeaways</button>';
			echo '<div class="wp-top-5-popup-modal" style="display:none;">';
			echo '<div class="wp-top-5-popup-content">';
			echo '<span class="wp-top-5-popup-close">&times;</span>';
			echo '<h2>Key Takeaways</h2>';
			echo '<ol>';
			foreach ( $top_5_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
			echo '</div>';
		} else {
			$mode_class = 'dark' === $atts['mode'] ? 'dark' : 'light';
			echo '<div class="wp-top-5 ' . esc_attr( $mode_class ) . '">';
			echo '<h2>Key Takeaways</h2>';
			echo '<ol>';
			foreach ( $top_5_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
		}

		// Get the output buffer content.
		$output = ob_get_clean();

		if ( 'below' === $atts['view'] ) {
			add_filter(
				'the_content',
				function ( $content ) use ( $output ) {
					return $content . $output;
				}
			);
		} elseif ( 'popup' === $atts['view'] ) {
			return $output;
		} else {
			add_filter(
				'the_content',
				function ( $content ) use ( $output ) {
					return $output . $content;
				}
			);
		}

		return ''; // Shortcode itself does not return content, content is modified by the filter.
	}



	/**
	 * Registers the shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'wp_top_5', array( $this, 'wp_top_5_shortcode' ) );
	}

	/**
	 * Automatically append the top 5 points to the content.
	 *
	 * @since 1.0.0
	 * @param string $content The post content.
	 * @return string Modified post content.
	 */
	public function append_top_5_to_content_automatically( $content ) {
		if ( has_shortcode( $content, 'wp_top_5' ) ) {
			return $content;
		}

		$post_id      = get_the_ID();
		$top_5_points = get_post_meta( $post_id, 'wp_top_5_points', true );

		if ( empty( $top_5_points ) ) {
			return $content;
		}

		ob_start();

		// Get the default mode and position from settings.
		$mode_class = get_option( 'wp_top_5_display_mode', 'light' );
		$position   = get_option( 'wp_top_5_display_position', 'above' );

		if ( 'popup' === $position ) {
			echo '<button class="wp-top-5-popup-btn ' . esc_attr( $mode_class ) . '">View Key Takeaways</button>';
			echo '<div class="wp-top-5-popup-modal" style="display:none;">';
			echo '<div class="wp-top-5-popup-content">';
			echo '<span class="wp-top-5-popup-close">&times;</span>';
			echo '<h2>Key Takeaways</h2>';
			echo '<ol>';
			foreach ( $top_5_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
			echo '</div>';
		} else {
			echo '<div class="wp-top-5 ' . esc_attr( $mode_class ) . '">';
			echo '<h2>Key Takeaways</h2>';
			echo '<ol>';
			foreach ( $top_5_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
		}

		$top_5_html = ob_get_clean();

		if ( 'below' === $position ) {
			$content .= $top_5_html;
		} elseif ( 'popup' === $position ) {
			$content = $top_5_html . $content;
		} else {
			$content = $top_5_html . $content;
		}

		return $content;
	}
}
