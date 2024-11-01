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

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class provides Developer Board tab in settings.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class DeveloperBoard {

	/**
	 * The one true DeveloperBoard.
	 *
	 * @var DeveloperBoard
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Add Ajax handlers for Developer Board.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 **/
	public static function add_ajax() {

		/** Output Developer tab only if DEBUG mode enabled. */
		if (defined('WP_DEBUG') && WP_DEBUG && defined('DOING_AJAX')) {

            /** Reset Settings. */
            add_action( 'wp_ajax_reset_settings', [__CLASS__, 'ajax_reset_settings'] );

        }

	}

	/**
	 * Ajax Reset plugin settings.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 *
     * @noinspection PhpUnused
     **/
	public static function ajax_reset_settings() {

		/** Check nonce for security. */
		check_ajax_referer( 'reset_settings', 'nonce' );

		/** Do we need to do a full reset? */
		if ( empty( $_POST['doReset'] ) ) { wp_die( 'Wrong Parameter Value.' ); }

		/** Reset Plugin Settings call. */
		Helper::get_instance()->remove_settings();

		/** Return JSON result. */
		echo wp_json_encode( true );

		/** Exit. */
		wp_die();

	}

	/**
	 * Render form with all settings fields.
	 *
	 * @access public
	 * @since 1.0.0
	 **/
	public function render_form() {

		/** Output Developer tab only if DEBUG mode enabled. */
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) { return; }

		settings_fields( 'SpeakerDeveloperOptionsGroup' );
		do_settings_sections( 'SpeakerDeveloperOptionsGroup' );

	}

	/**
	 * Generate Developer Tab.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_settings() {

		/** Output Developer tab only if DEBUG mode enabled. */
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) { return; }

		/** Developer Tab. */
		$group = 'SpeakerDeveloperOptionsGroup';
		$section = 'mdp_speaker_lite_settings_page_developer_section';
		register_setting( $group, 'mdp_speaker_lite_developer_settings' );
		add_settings_section( $section, '', null, $group );

		/** Reset Settings. */
		add_settings_field( 'reset_settings', esc_html__( 'Reset Settings:', 'speaker-lite' ), [$this, 'reset_settings'], $group, $section );

	}

	/**
	 * Render reset settings button.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function reset_settings() {

		UI::get_instance()->render_button(
			esc_html__( 'Reset', 'speaker-lite' ),
			esc_html__( 'Press to reset all plugin settings to default state.', 'speaker-lite' ),
			[
				"name" => "mdp_speaker_lite_developer_settings[reset_settings]",
				"id" => "mdp-dev-reset-settings-btn",
				"class" => "mdp-reset"
			],
			'clear_all'
		);

	}

	/**
	 * Main DeveloperBoard Instance.
	 *
	 * Insures that only one instance of DeveloperBoard exists in memory at any one time.
	 *
	 * @static
	 * @return DeveloperBoard
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class DeveloperBoard.
