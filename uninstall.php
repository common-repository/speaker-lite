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

namespace Merkulove;

/** Include plugin autoloader for additional classes. */
require __DIR__ . '/src/autoload.php';

use Merkulove\SpeakerLite\Helper;

/** Exit if uninstall.php is not called by WordPress. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class used to implement Uninstall of Speaker plugin.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class Uninstall {

	/**
	 * The one true Uninstall.
	 *
	 * @var Uninstall
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new Uninstall instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Get Uninstall mode. */
		$uninstall_mode = $this->get_uninstall_mode();

		/** Remove Plugin and Settings. */
		if ( 'plugin+settings' === $uninstall_mode ) {

			/** Remove Plugin Settings. */
            Helper::remove_settings();

		/** Remove Plugin with Settings and Audio files. */
		} elseif ( 'plugin+settings+data' === $uninstall_mode ) {

			/** Remove Plugin Settings. */
            Helper::remove_settings();

			/** Remove Plugin Audio files. */
			Helper::get_instance()->remove_audio_files();

		}

	}

	/**
	 * Return uninstall mode.
	 * plugin - Will remove the plugin only. Settings and Audio files will be saved. Used when updating the plugin.
	 * plugin+settings - Will remove the plugin and settings. Audio files will be saved. As a result, all settings will be set to default values. Like after the first installation.
	 * plugin+settings+data - Full Removal. This option will remove the plugin with settings and all audio files. Use only if you are sure what you are doing.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_uninstall_mode() {

		$uninstall_settings = get_option( 'mdp_speaker_lite_uninstall_settings' );

		if( isset( $uninstall_settings['mdp_speaker_lite_uninstall_settings'] ) && $uninstall_settings['mdp_speaker_lite_uninstall_settings'] ) { // Default value.
			$uninstall_settings = [
				'delete_plugin' => 'plugin'
			];
		}

		return $uninstall_settings['delete_plugin'];

	}

	/**
	 * Main Uninstall Instance.
	 *
	 * Insures that only one instance of Uninstall exists in memory at any one time.
	 *
	 * @static
	 * @return Uninstall
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}

/** Runs on Uninstall of Speaker plugin. */
Uninstall::get_instance();
