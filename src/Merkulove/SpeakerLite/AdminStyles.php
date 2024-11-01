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
 * SINGLETON: Class adds admin styles.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class AdminStyles {

	/**
	 * The one true AdminStyles.
	 *
	 * @var AdminStyles
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new AdminStyles instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add admin styles. */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

	}

	/**
	 * Add CSS for admin area.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	public function admin_styles() {

		/** Plugin Settings Page. */
		$this->settings_styles();

		/** Styles for selected post types edit screen. */
		$this->edit_post_styles();

		/** Plugins page. Styles for "View version details" popup. */
		$this->plugin_update_styles();

	}

	/**
	 * Styles for selected post types edit screen.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	private function edit_post_styles() {

		/** Edit Post/Page. */
		$screen = get_current_screen();

		/** Get supported post types from plugin settings. */
        $cpt_support = Settings::get_instance()->options['cpt_support'];

		if (
		    null !== $screen &&
            $screen->base !== 'edit' &&
            in_array( $screen->post_type, $cpt_support, false )
        ) {

            /** Add class .mdc-disable to body. So we can use UI without overrides WP CSS, only for this page.  */
            add_action( 'admin_body_class', [$this, 'add_admin_class'] );

            wp_enqueue_style( 'merkulov-ui', SpeakerLite::$url . 'css/merkulov-ui.min.css', [], SpeakerLite::$version );
			wp_enqueue_style( 'mdp-speaker-lite-admin-post', SpeakerLite::$url . 'css/admin-post' . SpeakerLite::$suffix . '.css', [], SpeakerLite::$version );

		}

	}

	/**
	 * Styles for plugin setting page.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	private function settings_styles() {

		/** Add styles only on plugin setting page. */
		$screen = get_current_screen();

		if ( null === $screen || $screen->base !== SpeakerLite::$menu_base ) { return; }

		wp_enqueue_style( 'merkulov-ui', SpeakerLite::$url . 'css/merkulov-ui.min.css', [], SpeakerLite::$version );
		wp_enqueue_style( 'dataTables', SpeakerLite::$url . 'css/jquery.dataTables' . SpeakerLite::$suffix . '.css', [], SpeakerLite::$version );
		wp_enqueue_style( 'mdp-speaker-lite-admin', SpeakerLite::$url . 'css/admin' . SpeakerLite::$suffix . '.css', [], SpeakerLite::$version );

	}

	/**
	 * Styles for plugins page. "View version details" popup.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	private function plugin_update_styles() {

		/** Plugin install page, for style "View version details" popup. */
		$screen = get_current_screen();
		if ( null === $screen || $screen->base !== 'plugin-install' ) { return; }

		/** Styles only for our plugin. */
		if ( isset( $_GET['plugin'] ) && $_GET['plugin'] === 'speaker-lite' ) {

			wp_enqueue_style( 'mdp-speaker-lite-plugin-install', SpeakerLite::$url . 'css/plugin-install' . SpeakerLite::$suffix . '.css', [], SpeakerLite::$version );

		}

	}

    /**
     * Add class to body in admin area.
     *
     * @param string $classes - Space-separated list of CSS classes.
     *
     * @since 1.0.0
     * @return string
     */
    public function add_admin_class( $classes ) {

        return $classes . ' mdc-disable ';

    }

	/**
	 * Main AdminStyles Instance.
	 *
	 * Insures that only one instance of AdminStyles exists in memory at any one time.
	 *
	 * @static
	 * @return AdminStyles
	 * @since 1.0.0
	 **/
	public static function get_instance() {

        /** @noinspection SelfClassReferencingInspection */
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminStyles ) ) {

			self::$instance = new AdminStyles;

		}

		return self::$instance;

	}

} // End Class AdminStyles.
