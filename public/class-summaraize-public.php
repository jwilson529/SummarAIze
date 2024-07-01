<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Summaraize
 * @subpackage Summaraize/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Summaraize
 * @subpackage Summaraize/public
 * @author     James Wilson <james@middletnwebdesign.com>
 */
class Summaraize_Public {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/summaraize-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/summaraize-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Shortcode to display the top 5 points.
	 *
	 * @since    1.0.0
	 * @param    array $atts Shortcode attributes.
	 * @return   string HTML content to display.
	 */
	public function summaraize_shortcode( $atts ) {
		// Parse shortcode attributes.
		$atts = shortcode_atts(
			array(
				'view' => get_option( 'summaraize_display_position', 'above' ), // Use the setting as the default value.
				'mode' => get_option( 'summaraize_display_mode', 'light' ), // Default to light mode if not set.
			),
			$atts,
			'summaraize'
		);

		// Get the current post ID.
		$post_id = get_the_ID();

		// Fetch the top 5 points.
		$summaraize_points = get_post_meta( $post_id, 'summaraize_points', true );

		// If no points are set, display a message.
		if ( empty( $summaraize_points ) ) {
			return '<p>No key points have been set for this post.</p>';
		}

		// Get widget title, button style, and button color from settings.
		$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
		$button_style = get_option( 'summaraize_button_style', 'flat' );
		$button_color = get_option( 'summaraize_button_color', '#0073aa' );

		// Start the output buffer.
		ob_start();

		// Display based on the view.
		if ( 'popup' === $atts['view'] ) {
			$mode_class = 'dark' === $atts['mode'] ? 'dark' : 'light';
			echo '<button class="summaraize-popup-btn ' . esc_attr( $mode_class ) . ' ' . esc_attr( $button_style ) . '" style="background-color: ' . esc_attr( $button_color ) . ';">View ' . esc_html( $widget_title ) . '</button>';
			echo '<div class="summaraize-popup-modal" style="display:none;">';
			echo '<div class="summaraize-popup-content">';
			echo '<span class="summaraize-popup-close">&times;</span>';
			echo '<h2>' . esc_html( $widget_title ) . '</h2>';
			echo '<ol>';
			foreach ( $summaraize_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
			echo '</div>';
		} else {
			$mode_class = 'dark' === $atts['mode'] ? 'dark' : 'light';
			echo '<div class="summaraize ' . esc_attr( $mode_class ) . '">';
			echo '<h2>' . esc_html( $widget_title ) . '</h2>';
			echo '<ol>';
			foreach ( $summaraize_points as $point ) {
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
		add_shortcode( 'summaraize', array( $this, 'summaraize_shortcode' ) );
	}

	/**
	 * Automatically append the top 5 points to the content.
	 *
	 * @since 1.0.0
	 * @param string $content The post content.
	 * @return string Modified post content.
	 */
	public function append_summaraize_to_content_automatically( $content ) {
		if ( has_shortcode( $content, 'summaraize' ) ) {
			return $content;
		}

		$post_id      = get_the_ID();
		$summaraize_points = get_post_meta( $post_id, 'summaraize_points', true );

		// Check if $summaraize_points is an array and filter out empty values.
		if ( ! is_array( $summaraize_points ) || empty( array_filter( $summaraize_points ) ) ) {
			return $content;
		}

		ob_start();

		// Get the default mode and position from settings.
		$mode_class = get_option( 'summaraize_display_mode', 'light' );
		$position   = get_option( 'summaraize_display_position', 'above' );

		// Get widget title, button style, and button color from settings.
		$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
		$button_style = get_option( 'summaraize_button_style', 'flat' );
		$button_color = get_option( 'summaraize_button_color', '#0073aa' );

		if ( 'popup' === $position ) {
			echo '<button class="summaraize-popup-btn ' . esc_attr( $mode_class ) . ' ' . esc_attr( $button_style ) . '" style="background-color: ' . esc_attr( $button_color ) . ';">View ' . esc_html( $widget_title ) . '</button>';
			echo '<div class="summaraize-popup-modal" style="display:none;">';
			echo '<div class="summaraize-popup-content">';
			echo '<span class="summaraize-popup-close">&times;</span>';
			echo '<h2>' . esc_html( $widget_title ) . '</h2>';
			echo '<ol>';
			foreach ( $summaraize_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
			echo '</div>';
		} else {
			echo '<div class="summaraize ' . esc_attr( $mode_class ) . '">';
			echo '<h2>' . esc_html( $widget_title ) . '</h2>';
			echo '<ol>';
			foreach ( $summaraize_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
		}

		$summaraize_html = ob_get_clean();

		if ( 'below' === $position ) {
			$content .= $summaraize_html;
		} elseif ( 'popup' === $position ) {
			$content = $summaraize_html . $content;
		} else {
			$content = $summaraize_html . $content;
		}

		return $content;
	}
}
