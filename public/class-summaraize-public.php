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
		if ( $this->should_enqueue_assets() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/summaraize-public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( $this->should_enqueue_assets() ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/summaraize-public.js', array( 'jquery' ), $this->version, false );
		}
	}

	/**
	 * Shortcode to display the top 5 points.
	 *
	 * @since    1.0.0
	 * @param    array  $atts    Shortcode attributes.
	 * @param    string $content The content to include.
	 * @return   string HTML content to display.
	 */
	public function summaraize_shortcode( $atts, $content = null ) {

		$atts = shortcode_atts(
			array(
				'view' => '', // Empty default value to allow fallback to settings.
				'mode' => '', // Empty default value to allow fallback to settings.
			),
			$atts,
			'summaraize'
		);

		$post_id           = get_the_ID();
		$summaraize_points = get_post_meta( $post_id, 'summaraize_points', true );

		if ( ! is_array( $summaraize_points ) ) {
			$summaraize_points = array();
		}

		// Filter out empty points.
		$summaraize_points = array_filter( $summaraize_points );

		if ( empty( $summaraize_points ) ) {
			return '<p>' . esc_html__( 'No key points have been set for this post.', 'summaraize' ) . '</p>';
		}

		$override_settings = get_post_meta( $post_id, 'summaraize_override_settings', true );
		if ( $override_settings ) {
			$view         = get_post_meta( $post_id, 'summaraize_view', true );
			$mode         = get_post_meta( $post_id, 'summaraize_mode', true );
			$widget_title = get_post_meta( $post_id, 'summaraize_widget_title', true );
			$button_style = get_post_meta( $post_id, 'summaraize_button_style', true );
			$button_color = get_post_meta( $post_id, 'summaraize_button_color', true );
		} else {
			$view         = ! empty( $atts['view'] ) ? $atts['view'] : get_option( 'summaraize_display_position', 'above' );
			$mode         = ! empty( $atts['mode'] ) ? $atts['mode'] : get_option( 'summaraize_display_mode', 'light' );
			$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
			$button_style = get_option( 'summaraize_button_style', 'flat' );
			$button_color = get_option( 'summaraize_button_color', '#0073aa' );
		}

		$output = $this->build_view( $summaraize_points, $view, $mode, $content, $widget_title, $button_style, $button_color );

		return $output;
	}


	/**
	 * Build the view based on the provided attributes.
	 *
	 * @since    1.0.0
	 * @param    array  $summaraize_points Points to display.
	 * @param    string $view              View mode.
	 * @param    string $mode              Display mode.
	 * @param    string $content           Content to include.
	 * @param    string $widget_title      Widget title.
	 * @param    string $button_style      Button style.
	 * @param    string $button_color      Button color.
	 * @return   string HTML content to display.
	 */
	public function build_view( $summaraize_points, $view, $mode, $content, $widget_title, $button_style, $button_color ) {
		ob_start();

		if ( 'popup' === $view ) {
			$mode_class = 'dark' === $mode ? 'dark' : 'light';
			echo '<button class="summaraize-popup-btn ' . esc_attr( $mode_class ) . ' ' . esc_attr( $button_style ) . '" style="background-color: ' . esc_attr( $button_color ) . ';">' . esc_html( $widget_title ) . '</button>';
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
			$mode_class = 'dark' === $mode ? 'dark' : 'light';
			echo '<div class="summaraize ' . esc_attr( $mode_class ) . '">';
			echo '<h2>' . esc_html( $widget_title ) . '</h2>';
			echo '<ol>';
			foreach ( $summaraize_points as $point ) {
				echo '<li>' . esc_html( $point ) . '</li>';
			}
			echo '</ol>';
			echo '</div>';
		}

		$output = ob_get_clean();

		if ( 'below' === $view ) {
			return $content . $output;
		} else {
			return $output . $content;
		}
	}

	/**
	 * Register the shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'summaraize', array( $this, 'summaraize_shortcode' ) );
	}

	/**
	 * Automatically append the top 5 points to the content.
	 *
	 * @since    1.0.0
	 * @param    string $content The post content.
	 * @return   string Modified post content.
	 */
	public function append_summaraize_to_content_automatically( $content ) {
		if ( has_shortcode( $content, 'summaraize' ) ) {
			return $content;
		}

		$post_id           = get_the_ID();
		$summaraize_points = get_post_meta( $post_id, 'summaraize_points', true );

		if ( ! is_array( $summaraize_points ) || empty( array_filter( $summaraize_points ) ) ) {
			return $content;
		}

		$override_settings = get_post_meta( $post_id, 'summaraize_override_settings', true );
		if ( $override_settings ) {
			$view         = get_post_meta( $post_id, 'summaraize_view', true );
			$mode         = get_post_meta( $post_id, 'summaraize_mode', true );
			$widget_title = get_post_meta( $post_id, 'summaraize_widget_title', true );
			$button_style = get_post_meta( $post_id, 'summaraize_button_style', true );
			$button_color = get_post_meta( $post_id, 'summaraize_button_color', true );
		} else {
			$view         = get_option( 'summaraize_display_position', 'above' );
			$mode         = get_option( 'summaraize_display_mode', 'light' );
			$widget_title = get_option( 'summaraize_widget_title', 'Key Takeaways' );
			$button_style = get_option( 'summaraize_button_style', 'flat' );
			$button_color = get_option( 'summaraize_button_color', '#0073aa' );
		}

		$shortcode_output = $this->build_view( $summaraize_points, $view, $mode, '', $widget_title, $button_style, $button_color );

		if ( 'below' === $view ) {
			return $content . $shortcode_output;
		} else {
			return $shortcode_output . $content;
		}
	}

	/**
	 * Determine if we should enqueue assets.
	 *
	 * @since 1.0.0
	 * @return bool True if assets should be enqueued, false otherwise.
	 */
	private function should_enqueue_assets() {
		if ( is_singular() ) {
			global $post;
			if ( has_shortcode( $post->post_content, 'summaraize' ) || $this->should_append_summaraize_to_content( $post->ID ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if summaraize points should be appended to content automatically.
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID.
	 * @return bool True if summaraize points should be appended, false otherwise.
	 */
	private function should_append_summaraize_to_content( $post_id ) {
		$summaraize_points = get_post_meta( $post_id, 'summaraize_points', true );

		if ( ! is_array( $summaraize_points ) || empty( array_filter( $summaraize_points ) ) ) {
			return false;
		}

		return true;
	}
}
