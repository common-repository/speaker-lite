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
 * SINGLETON: Class adds admin scripts.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class AdminScripts {

	/**
	 * The one true AdminScripts.
	 *
	 * @var AdminScripts
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new AdminScripts instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add admin styles. */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

	}

	/**
	 * Add JavaScrips for admin area.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	public function admin_scripts() {

		/** Plugin Settings Page. */
		$this->settings_scripts();

		/** Scripts for selected post types on edit screen. */
		$this->edit_post_scripts();

	}

	/**
	 * Scripts for selected post types edit screen.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	private function edit_post_scripts() {

		/** Edit screen for selected post types. */
		$screen = get_current_screen();

		/** Get supported post types from plugin settings. */
        $cpt_support = Settings::get_instance()->options['cpt_support'];

		if (
		    null !== $screen &&
            $screen->base !== 'edit' &&
            in_array( $screen->post_type, $cpt_support, false )
        ) {

            wp_enqueue_script( 'merkulov-ui', SpeakerLite::$url . 'js/merkulov-ui' . SpeakerLite::$suffix . '.js', [], SpeakerLite::$version, true );
            wp_enqueue_script( 'mdp-sortable', SpeakerLite::$url . 'js/Sortable' . SpeakerLite::$suffix . '.js', [], SpeakerLite::$version, true );
            wp_enqueue_script( 'dataTables', SpeakerLite::$url . 'js/jquery.dataTables' . SpeakerLite::$suffix . '.js', [ 'jquery' ], SpeakerLite::$version, true );
			wp_enqueue_script( 'mdp-admin-post', SpeakerLite::$url . 'js/admin-post' . SpeakerLite::$suffix . '.js', ['jquery', 'mdp-sortable', 'dataTables'], SpeakerLite::$version, true );

			/** Pass some vars to JS. */
			wp_localize_script( 'mdp-admin-post', 'mdpSpeaker', [
                'post_id'               => get_the_ID(), // Current post ID.
				'ajax_nonce'            => wp_create_nonce( 'speaker-lite-nonce' ), // Nonce for security.
				'audio_url'             => Helper::get_instance()->get_audio_upload_url(), // Upload folder URL.
                'voice'                 => Settings::get_instance()->options['language'], // Default voice.
                'speechTemplateCount' => count( MetaBox::get_instance()->get_st_options() )
			] );

		}

	}

	/**
	 * Scripts for plugin setting page.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	private function settings_scripts() {

		/** Add scripts only on plugin settings page. */
		$screen = get_current_screen();
		if ( null === $screen || $screen->base !== SpeakerLite::$menu_base ) { return; }

		wp_enqueue_script( 'merkulov-ui', SpeakerLite::$url . 'js/merkulov-ui' . SpeakerLite::$suffix . '.js', [], SpeakerLite::$version, true );
		wp_enqueue_script( 'dataTables', SpeakerLite::$url . 'js/jquery.dataTables' . SpeakerLite::$suffix . '.js', [ 'jquery' ], SpeakerLite::$version, true );

		wp_enqueue_script( 'mdp-speaker-lite-admin', SpeakerLite::$url . 'js/admin' . SpeakerLite::$suffix . '.js', [ 'jquery', 'dataTables' ], SpeakerLite::$version, true );
		wp_localize_script('mdp-speaker-lite-admin', 'mdpSpeaker', [
			'ajaxURL' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('reset_settings'),
            'speaker_lite_nonce' => wp_create_nonce( 'speaker-lite-nonce' ), // Nonce for security.
		] );

	}

	/**
	 * Main AdminScripts Instance.
	 *
	 * Insures that only one instance of AdminScripts exists in memory at any one time.
	 *
	 * @static
	 * @return AdminScripts
	 * @since 1.0.0
	 **/
	public static function get_instance() {

        /** @noinspection SelfClassReferencingInspection */
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminScripts ) ) {

			self::$instance = new AdminScripts;

		}

		return self::$instance;

	}

} // End Class AdminScripts.
