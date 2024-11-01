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
 * SINGLETON: Class adds Speaker Metabox for selected Post types.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class MetaBox {

	/**
	 * The one true MetaBox.
	 *
	 * @var MetaBox
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new MetaBox instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		add_action( 'add_meta_boxes', [ $this, 'meta_box' ] );

	}

	/**
	 * Add Meta Box for Post/Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function meta_box() {

		/** Get selected post types. */
		$screens = Settings::get_instance()->options['cpt_support'];

		foreach ( $screens as $screen ) {

			/** Add Speaker Metabox */
			add_meta_box(
				'mdp_speaker_lite_box_id',
				'Speaker Lite',
				[ $this, 'meta_box_html' ],
				$screen,
				'side',
				'core'
			);

		}

	}

	/**
	 * Render Meta Box.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function meta_box_html() {

		/** Show audio player if audio exist. */
		SpeakerCaster::get_instance()->the_player();

		/** Show "Generate Audio" button if Post already saved and published. */
		$status = get_post_status();
		if ( 'publish' !== $status ) :

            /** Show warning for unpublished posts. */
            $this->meta_box_html_status();

		elseif ( post_password_required() ) :

            /** Show warning for password protected posts. */
            $this->meta_box_html_password();

		elseif ( Settings::get_instance()->options['dnd-api-key'] ) :

            /** Show generate button. */
           $this->meta_box_html_generate();

        endif;
	}

    /**
     * Show warning for unpublished posts.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
	private function meta_box_html_status() {
        ?>
        <div class="mdp-warning">
            <?php esc_html_e( 'Publish a post before you can generate an audio version.', 'speaker-lite' ); ?>
        </div>
        <?php
    }

    /**
     * Show warning for password protected posts.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    private function meta_box_html_password() {
        ?>
        <div class="mdp-warning">
            <?php esc_html_e( 'Speaker reads only publicly available posts.', 'speaker-lite' ); ?><br>
            <?php esc_html_e( 'Remove the password from the post, create an audio version, then close the post again with a password.', 'speaker-lite' ); ?><br>
            <?php esc_html_e( 'This is a necessary safety measure.', 'speaker-lite' ); ?>
        </div>
        <?php
    }

    /**
     * Show generate button.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    private function meta_box_html_generate() {

        /** Checks if there is audio for the current post. */
        $audio_exists = SpeakerCaster::get_instance()->audio_exists();
        ?>
        <div class="mdp-speaker-lite-meta-box-controls">

            <div>
                <?php

                /** Generate with Speech Template button. */
                $this->speech_template_generate();

                ?>
            </div>

            <div>
                <button id="mdp_speaker_lite_generate" type="button"
                        class="button-large components-button is-button is-primary is-large">
                    <?php if ( $audio_exists ) : ?>
                        <?php esc_html_e( 'Re-create audio', 'speaker-lite' ); ?>
                    <?php else : ?>
                        <?php esc_html_e( 'Create audio', 'speaker-lite' ); ?>
                    <?php endif; ?>
                </button>

                <?php if ( $audio_exists ) : ?>
                    <button id="mdp_speaker_lite_remove" type="button"
                            class="button-large components-button button-link-delete is-button is-default is-large">
                        <?php esc_html_e( 'Remove', 'speaker-lite' ); ?>
                    </button>
                <?php endif; ?>
            </div>

        </div>
        <?php

    }

    /**
     * Generate with Speech Template button.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function speech_template_generate() {

        ?>
        <div>
            <?php

            /** Prepare options for ST select. */
            $options = $this->get_st_options();

            $selected = 'content';

            /** Render Speech Template Select. */
            UI::get_instance()->render_select(
                $options,
                $selected, // Selected template.
                esc_html__( 'Speech Template', 'speaker-lite' ),
                esc_html__( 'Selection of Speech Templates is available in the ', 'speaker-lite' ) .
                '<a href="https://1.envato.market/speaker" target="_blank">' .
                    esc_html__( 'Speaker PRO', 'speaker-lite' ) .
                '</a>',
                [
                    'name' => 'mdp_speaker_lite_speech_templates_template',
                    'id' => 'mdp-speaker-lite-speech-templates-template'
                ]
            );

            ?>
        </div>

        <?php

    }

    /**
     * Return array with all Speech Templates.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array
     **/
    public function get_st_options() {

        return [ 'content' => esc_html__( 'Content', 'speaker-lite' ) ];

    }

    /**
     * Return default ST for current post type.
     *
     * @param null $post_type
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public function get_default_st( $post_type = null ) {

        /** Use current post type if nothing received. */
        if ( ! isset( $post_type ) ) {

            /** Retrieves the post type of the current post. */
            $post_type = get_post_type();

        }

        /** In this option we store all Speech Templates. */
        $st_opt_name = 'mdp_speaker_lite_speech_templates';

        /** Get all Speech Templates. */
        $st = get_option( $st_opt_name, false );

        /** We haven't any ST. */
        if ( ! $st ) { return ''; }

        /** For each ST. */
        foreach ( $st as $key => $template ) {

            /** Skip if empty. */
            if ( ! isset( $st[$key]['default'] ) ) { continue; }

            /** Skip if not array. */
            if ( ! is_array( $st[$key]['default'] ) ) { continue; }

            if ( in_array( $post_type, $st[$key]['default'], false ) ) {

                return $st[$key]['id'];

            }

        }

        return '';

    }

	/**
	 * Main MetaBox Instance.
	 *
	 * Insures that only one instance of MetaBox exists in memory at any one time.
	 *
	 * @static
	 * @return MetaBox
	 * @since 1.0.0
	 **/
	public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class MetaBox.
