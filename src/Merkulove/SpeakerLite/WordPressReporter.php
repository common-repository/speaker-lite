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
 * SINGLETON: Used to implement System report handler class
 * responsible for generating a report for the server environment.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky ( info@alexander.khmelnitskiy.ua )
 **/
final class WordPressReporter {

	/**
	 * The one true WordPressReporter.
	 *
	 * @var WordPressReporter
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new WordPressReporter instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

	}

	/**
	 * Get WordPress environment reporter title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Report title.
	 **/
	public function get_title() {
		return 'WordPress Environment';
	}

	/**
	 * Get WordPress environment report fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Required report fields with field ID and field label.
	 **/
	public function get_fields() {
		return [
			'version'               => esc_html__( 'Version', 'speaker-lite' ),
			'site_url'              => esc_html__( 'Site URL', 'speaker-lite' ),
			'home_url'              => esc_html__( 'Home URL', 'speaker-lite' ),
			'abspath'               => esc_html__( 'ABSPATH', 'speaker-lite' ),
			'is_multisite'          => esc_html__( 'WP Multisite', 'speaker-lite' ),
			'max_upload_size'       => esc_html__( 'Max Upload Size', 'speaker-lite' ),
			'memory_limit'          => esc_html__( 'Memory limit', 'speaker-lite' ),
			'permalink_structure'   => esc_html__( 'Permalink Structure', 'speaker-lite' ),
			'language'              => esc_html__( 'Language', 'speaker-lite' ),
			'timezone'              => esc_html__( 'Timezone', 'speaker-lite' ),
			'admin_email'           => esc_html__( 'Admin Email', 'speaker-lite' ),
			'debug_mode'            => esc_html__( 'Debug Mode', 'speaker-lite' ),
		];
	}

	/**
	 * Get report.
	 * Retrieve the report with all it's containing fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report fields.
	 *
	 *    @type string $name Field name.
	 *    @type string $label Field label.
	 * }
	 **/
	final public function get_report() {

		$result = [];

		foreach ( $this->get_fields() as $field_name => $field_label ) {
			$method = 'get_' . $field_name;

			$reporter_field = [
				'name' => $field_name,
				'label' => $field_label,
			];

			$reporter_field = array_merge( $reporter_field, $this->$method() );
			$result[ $field_name ] = $reporter_field;
		}

		return $result;
	}

	/**
	 * Get WordPress memory limit.
	 * Retrieve the WordPress memory limit.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value          WordPress memory limit.
	 *    @type string $recommendation Recommendation memory limit.
	 *    @type bool   $warning        Whether to display a warning. True if the limit
	 *                                 is below the recommended 64M, False otherwise.
	 * }
	 **/
	public function get_memory_limit() {
		$result = [
			'value' => ini_get( 'memory_limit' ),
		];

		$min_recommended_memory = '64M';

		$memory_limit_bytes = wp_convert_hr_to_bytes( $result['value'] );

		$min_recommended_bytes = wp_convert_hr_to_bytes( $min_recommended_memory );

		if ( $memory_limit_bytes < $min_recommended_bytes ) {
			$result['recommendation'] = esc_html__( 'We recommend setting memory to at least 64M. For more information, ask your hosting provider.','speaker-lite' );

			$result['warning'] = true;
		}

		return $result;
	}

	/**
	 * Get WordPress version.
	 * Retrieve the WordPress version.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress version.
	 * }
	 **/
	public function get_version() {
		return [
			'value' => get_bloginfo( 'version' ),
		];
	}

	/**
	 * Is multisite.
	 * Whether multisite is enabled or not.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value Yes if multisite is enabled, No otherwise.
	 * }
	 **/
	public function get_is_multisite() {
		return [
			'value' => is_multisite() ? esc_html__( 'Yes', 'speaker-lite' ) : esc_html__( 'No', 'speaker-lite' )
		];
	}

	/**
	 * Get site URL.
	 * Retrieve WordPress site URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress site URL.
	 * }
	 **/
	public function get_site_url() {
		return [
			'value' => get_site_url(),
		];
	}

	/**
	 * Get home URL.
	 * Retrieve WordPress home URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress home URL.
	 * }
	 **/
	public function get_home_url() {
		return [
			'value' => get_home_url(),
		];
	}

	/**
	 * Return WP ABSPATH.
	 *
	 * @since 2.0.4
	 * @access public
	 *
	 * @return array
	 **/
	public function get_abspath() {
		return [
			'value' => ABSPATH
		];
	}

	/**
	 * Get permalink structure.
	 * Retrieve the permalink structure.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress permalink structure.
	 * }
	 **/
	public function get_permalink_structure() {
		global $wp_rewrite;

		$structure = $wp_rewrite->permalink_structure;

		if ( ! $structure ) {
			$structure = esc_html__( 'Plain', 'speaker-lite' );
		}

		return [
			'value' => $structure,
		];
	}

	/**
	 * Get site language.
	 * Retrieve the site language.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress site language.
	 * }
	 **/
	public function get_language() {
		return [
			'value' => get_bloginfo( 'language' ),
		];
	}

	/**
	 * Get PHP `max_upload_size`.
	 * Retrieve the value of maximum upload file size defined in `php.ini` configuration file.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value Maximum upload file size allowed.
	 * }
	 **/
	public function get_max_upload_size() {
		return [
			'value' => size_format( wp_max_upload_size() ),
		];
	}

	/**
	 * Get WordPress timezone.
	 * Retrieve WordPress timezone.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress timezone.
	 * }
	 **/
	public function get_timezone() {
		$timezone = get_option( 'timezone_string' );
		if ( ! $timezone ) {
			$timezone = get_option( 'gmt_offset' );
		}

		return [
			'value' => $timezone,
		];
	}

	/**
	 * Get WordPress administrator email.
	 * Retrieve WordPress administrator email.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value WordPress administrator email.
	 * }
	 **/
	public function get_admin_email() {
		return [
			'value' => get_option( 'admin_email' ),
		];
	}

	/**
	 * Get debug mode.
	 * Whether WordPress debug mode is enabled or not.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value Active if debug mode is enabled, Inactive otherwise.
	 * }
	 **/
	public function get_debug_mode() {
		return [
			'value' => WP_DEBUG ? esc_html__('Active', 'speaker-lite' ) : esc_html__('Inactive', 'speaker-lite' )
		];
	}

	/**
	 * Main WordPressReporter Instance.
	 *
	 * Insures that only one instance of WordPressReporter exists in memory at any one time.
	 *
	 * @static
	 * @return WordPressReporter
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WordPressReporter ) ) {

			self::$instance = new WordPressReporter;

		}

		return self::$instance;
	}

} // End Class WordPressReporter.
