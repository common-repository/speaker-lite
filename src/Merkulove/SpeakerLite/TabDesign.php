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

final class TabDesign {

	/**
	 * Create Design Tab.
	 */
	public function __construct() {

		/** Create Design Tab. */
		$group   = 'DesignOptionsGroup';
		$section = 'mdp_speaker_lite_settings_page_design_section';
		register_setting( $group, 'mdp_speaker_lite_design_settings' );
		add_settings_section( $section, '', null, $group );

		add_settings_field(
			'position',
			esc_html__( 'Player Position:', 'speaker-lite' ),
			[ $this, 'position' ],
			$group,
			$section
		);

		add_settings_field(
			'style',
			esc_html__( 'Player Style:', 'speaker-lite' ),
			[ $this, 'style' ],
			$group,
			$section
		);

		add_settings_field(
			'bgcolor',
			esc_html__( 'Background Color:', 'speaker-lite' ),
			[ $this, 'bgcolor' ],
			$group,
			$section
		);

		add_settings_field(
			'link',
			esc_html__( 'Download Link:', 'speaker-lite' ),
			[ $this, 'link' ],
			$group,
			$section
		);

	}

	/**
	 * Render Player Position field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function position() {

		$options = [
			"before-content" => esc_html__( 'Before Content', 'speaker-lite' ),
			"after-content"  => esc_html__( 'After Content', 'speaker-lite' ),
			"top-fixed"      => esc_html__( 'Top Fixed', 'speaker-lite' ),
			"bottom-fixed"   => esc_html__( 'Bottom Fixed', 'speaker-lite' ),
			"before-title"   => esc_html__( 'Before Title', 'speaker-lite' ),
			"after-title"    => esc_html__( 'After Title', 'speaker-lite' ),
			"shortcode"      => esc_html__( 'Shortcode [speaker]', 'speaker-lite' )
		];

		/** Render select. */
		UI::get_instance()->render_select(
			$options,
			Settings::get_instance()->options['position'], // Selected option.
			esc_html__( 'Position', 'speaker-lite' ),
			esc_html__( 'Select the Player position or use shortcode.', 'speaker-lite' ),
			[ 'name' => 'mdp_speaker_lite_design_settings[position]' ]
		);

	}

	/**
	 * Render Style field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function style() {

		$options = [
			'speaker-lite-round'           => esc_html__( 'Round player', 'speaker-lite' ),
			'speaker-lite-rounded'         => esc_html__( 'Rounded player', 'speaker-lite' ),
			'speaker-lite-squared'         => esc_html__( 'Squared player', 'speaker-lite' ),
			'speaker-lite-wp-default'      => esc_html__( 'WordPress default player', 'speaker-lite' ),
			'speaker-lite-browser-default' => esc_html__( 'Browser default player', 'speaker-lite' )
		];

		/** Render select. */
		UI::get_instance()->render_select(
			$options,
			Settings::get_instance()->options['style'], // Selected option.
			esc_html__( 'Player Style', 'speaker-lite' ),
			esc_html__( 'Select one of the Player styles', 'speaker-lite' ),
			[ 'name' => 'mdp_speaker_lite_design_settings[style]' ]
		);

	}

	/**
	 * Render Player Background Color field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function bgcolor() {

		/** Render colorpicker. */
		UI::get_instance()->render_colorpicker(
			Settings::get_instance()->options['bgcolor'],
			esc_html__( 'Background Color', 'speaker-lite' ),
			esc_html__( 'Select the Player background-color', 'speaker-lite' ),
			[
				'name'     => 'mdp_speaker_lite_design_settings[bgcolor]',
				'id'       => 'mdp-speaker-lite-bgcolor',
				'readonly' => 'readonly'
			]
		);

	}

	/**
	 * Render Download link field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function link() {

		$options = [
			'none'                 => esc_html__( 'Do not show', 'speaker-lite' ),
			'backend'              => esc_html__( 'Backend Only', 'speaker-lite' ),
			'frontend'             => esc_html__( 'Frontend Only', 'speaker-lite' ),
			'backend-and-frontend' => esc_html__( 'Backend and Frontend', 'speaker-lite' )
		];

		/** Render select. */
		UI::get_instance()->render_select(
			$options,
			Settings::get_instance()->options['link'], // Selected option.
			esc_html__( 'Download link', 'speaker-lite' ),
			esc_html__( 'Position of the Download audio link', 'speaker-lite' ),
			[
				'name' => 'mdp_speaker_lite_design_settings[link]',
				'id'   => 'mdp-speaker-lite-design-settings-link'
			]
		);

	}

	/**
	 * @var TabDesign|null
	 */
	private static ?TabDesign $instance = null;

	/**
	 * Get the instance of this class.
	 * @return TabDesign
	 */
	public static function getInstance(): TabDesign {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
