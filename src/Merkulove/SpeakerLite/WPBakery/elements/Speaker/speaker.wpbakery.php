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

use Merkulove\SpeakerLite\SpeakerCaster;

/** @noinspection PhpUnused */
class vceSpeaker {

	/**
	 * Get things started.
     *
     * @since 1.0.0
	 * @access public
	 **/
	public function __construct() {

		/** Speaker VC Element map. */
        $this->vce_speaker_lite_map();

		/** Shortcode for Speaker Element. */
		add_shortcode('vce_speaker', [$this, 'vce_speaker_lite_render'] );

	}

	/**
	 * Shortcode [vce_speaker] output.
	 *
	 * @param $atts array - Shortcode parameters.
	 *
	 * @since 1.0.0
	 * @access public
     *
     * @return false|string
	 **/
	public function vce_speaker_lite_render( $atts ) {

	    /** Prepare element parameters. */
		$css = '';

		extract( shortcode_atts( [
			'css' => ''
		], $atts ) );

		/** Prepare custom css from css_editor. */
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, '' ), 'vce_speaker', $atts );

		ob_start(); ?>
		<div class="mdp-vce-speaker-lite-box <?php echo esc_attr( $css_class ); ?>" ><?php echo SpeakerCaster::get_instance()->get_player(); ?></div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Speaker VC Element map.
     *
     * @return void
	 **/
	public function vce_speaker_lite_map() {

		vc_map( [
			'name'                      => esc_html__( 'Speaker Lite', 'speaker-lite' ),
			'description'               => esc_html__( 'Create an audio version of your posts.', 'speaker-lite' ),
			'base'                      => 'vce_speaker',
			'icon'                      => 'icon-vce-speaker-lite',
			'category'                  => esc_html__( 'Social', 'speaker-lite' ),
			'show_settings_on_create'   => false,
			'params'                    => [
				[
					'param_name'    => 'css',
					'type'          => 'css_editor',
					'heading'       => esc_html__( 'Css', 'speaker-lite' ),
					'group'         => esc_html__( 'Design options', 'speaker-lite' ),
				]
			],
		] );

	}

} // END Class vceSpeaker.

/** Run Speaker Element. */
new vceSpeaker();