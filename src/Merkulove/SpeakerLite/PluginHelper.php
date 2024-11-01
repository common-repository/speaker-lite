<?php
/**
 * Create an audio version of your posts, with a selection of more than 120+ voices across more than 30 languages and variants.
 * Exclusively on Envato Market: https://speaker-airy.merkulov.design/
 *
 * @encoding        UTF-8
 * @version         1.2.0
 * @copyright       Copyright (C) 2018 - 2024 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         GPLv3 or later
 * @contributors    Alexander Khmelnitskiy (info@alexander.khmelnitskiy.ua), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\SpeakerLite;

use Merkulove\SpeakerLite;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class used to implement additional plugin features.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class PluginHelper {

	/**
	 * The one true PluginHelper.
	 *
	 * @var PluginHelper
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 * @access public
     *
	 * @return void
	 **/
	public function load_plugin_textdomain() {

		/** Loads plugin translated strings. */
		load_plugin_textdomain( 'speaker-lite', false, dirname( SpeakerLite::$basename ) . '/languages/' );

	}

	/**
	 * Apply remove filters.
	 * Remove "Thank you for creating with WordPress" and WP version from plugin settings page.
	 *
	 * @since 1.0.0
	 * @access public
     *
	 * @return void
	 **/
    public function remove_copyrights() {

	    /** Remove only from plugin settings page. */
	    $screen = get_current_screen();
	    if ( null === $screen ) { return; }
	    if ( $screen->base !== SpeakerLite::$menu_base ) { return; }

	    /** Remove "Thank you for creating with WordPress" from plugin settings page. */
	    add_filter( 'admin_footer_text', '__return_empty_string', 11 );

	    /** Remove WP version from plugin settings page. */
	    add_filter( 'update_footer', '__return_empty_string', 11 );

    }

    public function add_actions() {

	    /** Remove all "third-party" notices from other plugins on our settings page. */
	    add_action( 'in_admin_header', [ $this, 'remove_all_notices' ], 1000 );

	    /** Show admin warning, if we need API Key. */
	    add_action( 'admin_notices', [ $this, 'key_notice' ] );

	    /** Add additional links in plugin list. */
	    add_filter( 'plugin_action_links_' . SpeakerLite::$basename, [ $this, 'add_links' ] );

	    /** Add plugin meta in plugin list. */
	    add_filter( 'plugin_row_meta', [ $this, 'add_row_meta' ], 10, 2 );

	    /** Load JS and CSS for Backend Area. */
	    $this->enqueue_backend();

	    /** Remove "Thank you for creating with WordPress" and WP version from plugin settings page. */
	    add_action( 'admin_enqueue_scripts', [$this, 'remove_copyrights'] );

    }

	/**
	 * Remove all notices from other plugins on our settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 **/
	public function remove_all_notices() {

		/** Work only on plugin settings page. */
		$screen = get_current_screen();
        if ( null === $screen ) { return; }
		if ( $screen->base !== SpeakerLite::$menu_base ) { return; }

		/** Remove all notices. */
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		/** Show admin warning, if we need API Key. */
		if ( ! Settings::get_instance()->options['dnd-api-key'] ) {

			add_action( 'admin_notices', [self::get_instance(), 'key_notice'] );

		}

	}

	/**
	 * Show admin warning, if we need API Key.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function key_notice() {

	    /** We have api key and don't need notice. */
		if ( Settings::get_instance()->options['dnd-api-key'] ) { return; }

		/** Get current screen. */
		$screen = get_current_screen();

		/** Speaker Settings Page. */
		if ( null !== $screen && $screen->base === SpeakerLite::$menu_base ) {

			/** Render "Before you start" message. */
			UI::get_instance()->render_snackbar(
				esc_html__( 'This plugin uses the Google Cloud Text-to-Speech API Key File. Set up your Google Cloud Platform project before the start.', 'speaker-lite' ),
				'warning', // Type
				-1, // Timeout
				true, // Is Closable
				[[ 'caption' => 'Get Key File', 'link' => 'https://docs.merkulov.design/about-key-file-for-the-voicer-wordpress-plugin/' ]] // Buttons
			);

		} else {

			/** Render "Before you start" message in old fashion style. */
			?>
			<div class="settings-error notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Speaker Lite: Before you begin', 'speaker-lite' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'This plugin uses the Cloud Text-to-Speech API. You need to set up your Google Cloud Platform project and authorization before creating audio from text. Visit', 'speaker-lite' ); ?>
					<a href="https://docs.merkulov.design/about-key-file-for-the-voicer-wordpress-plugin/" target="_blank">
						<?php esc_html_e( 'Online Documentation', 'speaker-lite' ); ?>
					</a>
					<?php esc_html_e( 'for more details.', 'speaker-lite' ); ?>
				</p>
			</div>
			<?php

		}

	}

	/**
	 * Load JS and CSS for Backend Area.
	 *
	 * @since 1.0.0
	 * @access public
     *
     * @return void
	 **/
	private function enqueue_backend() {

		/** Add admin styles. */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

		/** Add admin javascript. */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

	}

	/**
	 * Add CSS for admin area.
	 *
	 * @since   1.0.0
     *
	 * @return void
	 **/
	public function admin_styles() {

		$screen = get_current_screen();
        if ( null === $screen ) { return; }

		/** Add styles only on WP Plugins page. */
		if ( ! in_array( $screen->base, ['plugins', 'plugins-network'] )  ) { return; }

		wp_enqueue_style( 'mdp-speaker-lite-plugins', SpeakerLite::$url . 'css/plugins' . SpeakerLite::$suffix . '.css', [], SpeakerLite::$version );

	}

	/**
	 * Add JS for admin area.
	 *
	 * @since   1.0.0
     * @access  public
     *
	 * @return  void
	 **/
	public function admin_scripts() {

		$screen = get_current_screen();
        if ( null === $screen ) { return; }

		/** Add scripts only on WP Plugins page. */
		if ( ! in_array( $screen->base, ['plugins', 'plugins-network'] )  ) { return; }

		wp_enqueue_script( 'mdp-speaker-lite-plugins', SpeakerLite::$url . 'js/plugins' . SpeakerLite::$suffix . '.js', ['jquery'], SpeakerLite::$version, true );

	}

	/**
	 * Add "merkulov.design" and  "Envato Profile" links on plugin page.
	 *
	 * @param array $links Current links: Deactivate | Edit
	 * @since 1.0.0
	 * @access public
     *
	 * @return array
	 **/
	public function add_links( $links ) {

		array_unshift( $links, '<a title="' . esc_html__( 'Settings', 'speaker-lite' ) . '" href="' . admin_url( 'admin.php?page=mdp_speaker_lite_settings' ) . '">' . esc_html__( 'Settings', 'speaker-lite' ) . '</a>' );
		$links[] = '<a title="' . esc_html__('Documentation', 'speaker-lite') . '" href="https://docs.merkulov.design/speaker-lite/" target="_blank">' . esc_html__('Documentation', 'speaker-lite') . '</a>';
		$links[] = '<a href="https://1.envato.market/cc-merkulove" target="_blank" class="cc-merkulove"><img src="' . $this->get_base64_logo() . '" alt="' . esc_html__('Plugins', 'speaker-lite') . '">' . esc_html__('Plugins', 'speaker-lite') . '</a>';

		return $links;

	}

	/**
	 * Return svg logo of Merkulove Team in base64 encode.
	 *
	 * @since 1.0.0
	 * @access public
     *
	 * @return string - svg logo in base64 encode.
	 **/
	private function get_base64_logo() {

		/** @noinspection SpellCheckingInspection */
		return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2aWV3Qm94PSIwIDAgMTE3Ljk5IDY3LjUxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZGVmcz4KPHN0eWxlPi5jbHMtMSwuY2xzLTJ7ZmlsbDojMDA5ZWQ1O30uY2xzLTIsLmNscy0ze2ZpbGwtcnVsZTpldmVub2RkO30uY2xzLTN7ZmlsbDojMDA5ZWUyO308L3N0eWxlPgo8L2RlZnM+CjxjaXJjbGUgY2xhc3M9ImNscy0xIiBjeD0iMTUiIGN5PSI1Mi41MSIgcj0iMTUiLz4KPHBhdGggY2xhc3M9ImNscy0yIiBkPSJNMzAsMmgwQTE1LDE1LDAsMCwxLDUwLjQ4LDcuNUw3Miw0NC43NGExNSwxNSwwLDEsMS0yNiwxNUwyNC41LDIyLjVBMTUsMTUsMCwwLDEsMzAsMloiLz4KPHBhdGggY2xhc3M9ImNscy0zIiBkPSJNNzQsMmgwQTE1LDE1LDAsMCwxLDk0LjQ4LDcuNUwxMTYsNDQuNzRhMTUsMTUsMCwxLDEtMjYsMTVMNjguNSwyMi41QTE1LDE1LDAsMCwxLDc0LDJaIi8+Cjwvc3ZnPgo=";

	}

	/**
	 * Add "Rate us" link on plugin page.
	 *
	 * @param array $links Current links: Deactivate | Edit
	 * @param $file - Path to the plugin file relative to the plugins directory.
	 *
	 * @since 1.0.0
	 * @access public
     *
	 * @return array
	 **/
	public function add_row_meta( $links, $file ) {

		if ( SpeakerLite::$basename !== $file ) {
			return $links;
		}

		$links[] = esc_html__( 'Rate this plugin:', 'speaker-lite' ) . "
        <span class='mdp-rating-stars'>
           <a href='https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post' target='_blank'><span class='dashicons dashicons-star-filled'></span></a>
           <a href='https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post' target='_blank'><span class='dashicons dashicons-star-filled'></span></a>
           <a href='https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post' target='_blank'><span class='dashicons dashicons-star-filled'></span></a>
           <a href='https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post' target='_blank'><span class='dashicons dashicons-star-filled'></span></a>
           <a href='https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post' target='_blank'><span class='dashicons dashicons-star-filled'></span></a>
        <span>";

		return $links;
	}

	/**
	 * Main Helper Instance.
	 *
	 * Insures that only one instance of Helper exists in memory at any one time.
	 *
	 * @static
	 * @return PluginHelper
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();

		}

		return self::$instance;

	}

} // End Class Helper.
