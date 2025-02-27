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
 * SINGLETON: Class used to implement shortcodes.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class Shortcodes {

	/**
	 * The one true Shortcodes.
	 *
	 * @var Shortcodes
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new Shortcodes instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Initializes shortcodes. */
		$this->shortcodes_init();

	}

	/**
	 * Initializes shortcodes.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 **/
	public function shortcodes_init() {

		/** Add player by shortcode [speaker] or [speaker id=POST_ID] */
		add_shortcode( 'speaker', [ $this, 'speaker_shortcode' ] );

		/** Speaker Mute Shortcode. [speaker-mute]...[/speaker-mute] */
		add_shortcode( 'speaker-mute', [ $this, 'speaker_mute_shortcode' ] );

		/** Speaker Break Shortcode. [speaker-lite-break time="2s"] */
		add_shortcode( 'speaker-break', [ $this, 'speaker_break_shortcode' ] );

	}

	/**
	 * Add Speaker break by shortcode [speaker-break time="300ms"].
	 *
	 * @param $atts - An associative array of attributes specified in the shortcode.
	 *
	 * @since 1.0.0
	 * @access public
     *
     * @return string
	 **/
	public function speaker_break_shortcode( $atts ) {

		/** White list of options with default values. */
		$atts = shortcode_atts( [
			'time' => '500ms',
			'strength' => 'medium'
		], $atts );

		/** Extra protection from the fools */
		$atts['time'] = trim( wp_strip_all_tags( $atts['time'] ) );
		$atts['strength'] = trim( wp_strip_all_tags( $atts['strength'] ) );

		/** Show shortcodes only for our parser. Hide on frontend. */
		if ( isset( $_GET['speaker-lite-ssml'] ) && $_GET['speaker-lite-ssml'] ) {

			return '<break time="' . esc_attr( $atts['time'] ) . '" strength="' . esc_attr( $atts['strength'] ) . '" />';

		}

        return '';

    }

	/**
	 * Add Speaker mute by shortcode [speaker-lite-mute]...[/speaker-lite-mute].
	 *
	 * @param $atts - An associative array of attributes specified in the shortcode.
	 * @param $content - Shortcode content when using the closing shortcode construct: [foo] shortcode text [/ foo].
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public function speaker_mute_shortcode( $atts, $content ) {

        /** White list of options with default values. */
        $atts = shortcode_atts( [
            'tag' => 'div',
        ], $atts );

        $tag = $atts['tag'];

		/** Show shortcodes only for our parser. Hide on frontend. */
		if ( isset( $_GET['speaker-lite-ssml'] ) && $_GET['speaker-lite-ssml'] ) {

			return '<' . $tag . ' speaker-lite-mute="">' . do_shortcode( $content ) . '</' . $tag . '>';

		}

        return do_shortcode( $content );

    }

	/**
	 * Add player by shortcode [speaker].
	 *
	 * @param $atts - An associative array of attributes specified in the shortcode.
	 *
	 * @return bool|false|string
	 * @since 1.0.0
	 * @access public
	 **/
	public function speaker_shortcode( $atts ) {

        /** Checks if plugin should work on this page. */
        if ( ! AssignmentsTab::get_instance()->display() ) { return ''; }

		/**
		 * If selected other settings, but we found shortcode.
		 * Show short code, but don't read it.
		 **/
		if ( 'shortcode' !== Settings::get_instance()->options['position'] ) {
			return '<span class="speaker-lite-mute">[speaker]</span>';
		}

		$params = shortcode_atts( [ 'id' => '0' ], $atts );

		$id = (int) $params['id'];

		return SpeakerCaster::get_instance()->get_player( $id );

	}

	/**
	 * Main Shortcodes Instance.
	 *
	 * Insures that only one instance of Shortcodes exists in memory at any one time.
	 *
	 * @static
	 * @return Shortcodes
	 * @since 1.0.0
	 **/
	public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Shortcodes.
