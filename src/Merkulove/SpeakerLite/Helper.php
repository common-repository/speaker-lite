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

use WP_Filesystem_Direct;

/**
 * SINGLETON: Class used to implement work with WordPress filesystem.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class Helper {

	/**
	 * The one true Helper.
	 *
	 * @var Helper
	 * @since 1.0.0
	 **/
	private static $instance;

    /**
     * Parse a string between two strings.
     *
     * @param string $string
     * @param string $start
     * @param string $end
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
	public function get_string_between( $string, $start, $end ) {

        $string = ' ' . $string;
        $ini = strpos( $string, $start );
        if ( $ini === 0 ) { return ''; }

        $ini += strlen( $start );
        $len = strpos( $string, $end, $ini ) - $ini;

        return substr( $string, $ini, $len );
    }

	/**
	 * Delete Plugin Options.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function remove_settings() {

		$settings = [
			'mdp_speaker_lite_envato_id',
			'mdp_speaker_lite_settings',
			'mdp_speaker_lite_design_settings',
            'mdp_speaker_lite_post_types_settings',
			'mdp_speaker_lite_assignments_settings',
			'mdp_speaker_lite_uninstall_settings',
			'mdp_speaker_lite_developer_settings',
            'mdp_speaker_lite_speech_templates',
		];

		/** For Multisite. */
		if ( is_multisite() ) {

			foreach ( $settings as $key ) {

				if ( ! get_site_option( $key ) ) { continue; }

				delete_site_option( $key );

			}

			/** For Singular site. */
		} else {

			foreach ( $settings as $key ) {

				if ( ! get_option( $key ) ) { continue; }

				delete_option( $key );

			}

		}

	}

	/**
	 * Remove all speaker audio files.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 **/
	public function remove_audio_files() {

		/** Remove /wp-content/uploads/speaker/ folder. */
		$dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'speaker';
		$this->remove_directory( $dir );

	}

	/**
	 * Remove directory with all contents.
	 *
	 * @param $dir - Directory path to remove.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 **/
	public function remove_directory( $dir ) {

		require_once ( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once ( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		$fileSystemDirect = new WP_Filesystem_Direct( false );
		$fileSystemDirect->rmdir( $dir, true );

	}

	/**
	 * Create folder for audio files.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 **/
	public function create_speaker_lite_folder() {

		/** Create /wp-content/uploads/speaker/ folder. */
		wp_mkdir_p( trailingslashit( wp_upload_dir()['basedir'] ) . 'speaker' );

	}

	/**
	 * Return URL to  audio upload folder.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_audio_upload_url() {

		/** Get URL to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_baseurl = $upload_dir['baseurl'];

		/** URL to audio folder. */
		return $upload_baseurl . '/speaker/';

	}

	/**
	 * Main Helper Instance.
	 *
	 * Insures that only one instance of Helper exists in memory at any one time.
	 *
	 * @static
	 * @return Helper
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Helper.
