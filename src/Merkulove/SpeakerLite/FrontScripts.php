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
final class FrontScripts {

	/**
	 * The one true FrontScripts.
	 *
	 * @var FrontScripts
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new FrontScripts instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add plugin scripts. */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	/**
	 * Add plugin scripts.
	 *
	 * @return void
	 * @since   1.0.0
	 **/
	public function enqueue_scripts() {

		/** Remove WP mediaElement if set Default Browser Player. */
		if ( 'speaker-lite-browser-default' === Settings::get_instance()->options['style'] ) { return; }

		wp_enqueue_script( 'jquery' );

	}

	/**
	 * Main FrontScripts Instance.
	 *
	 * Insures that only one instance of FrontScripts exists in memory at any one time.
	 *
	 * @static
	 * @return FrontScripts
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FrontScripts ) ) {

			self::$instance = new FrontScripts;

		}

		return self::$instance;

	}

} // End Class FrontScripts.
