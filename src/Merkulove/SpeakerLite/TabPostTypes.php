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

final class TabPostTypes {

	public function __construct() {

		$group = 'PostTypesOptionsGroup';
		$section = 'mdp_speaker_lite_settings_page_post_types_section';
		register_setting( $group, 'mdp_speaker_lite_post_types_settings' );
		add_settings_section( $section, '', null, $group );

		add_settings_field(
			'cpt_support',
			esc_html__( 'Post Types:', 'speaker-lite' ),
			[ $this, 'cpt_support' ],
			$group,
			$section
		);

		add_settings_field(
			'default_templates',
			esc_html__( 'Speech Templates:', 'speaker-lite' ),
			[ $this, 'default_templates' ],
			$group,
			$section
		);

	}

	/**
	 * Render Post Types field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function cpt_support() {

		/** All available post types. */
		$options = self::get_cpt();

		UI::get_instance()->render_chosen_dropdown(
			$options,
			Settings::get_instance()->options['cpt_support'],
			esc_html__( 'Select post types for which the plugin will work.', 'speaker-lite' ),
			[
				'name' => 'mdp_speaker_lite_post_types_settings[cpt_support][]',
				'id' => 'mdp-speaker-lite-post-types-settings-cpt-support',
				'multiple' => 'multiple',
				'badge'     => esc_html__( 'Upgrade to PRO', 'speaker-lite' ),
			]
		);

	}

	/**
	 * Render Default Speech Templates field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function default_templates() {

		/** All available post types. */
		$custom_posts = self::get_cpt();

		/** Prepare options for ST select. */
		$options = MetaBox::get_instance()->get_st_options();

		?>
		<div class="mdc-data-table">
			<table id="mdp-custom-posts-tbl" class="mdc-data-table__table" aria-label="<?php esc_attr_e( 'Default Speech Templates', 'speaker-lite' ); ?>">
				<thead>
				<tr class="mdc-data-table__header-row">
					<th class="mdc-data-table__header-cell" role="columnheader" scope="col"><?php esc_html_e( 'Post Type', 'speaker-lite' ); ?></th>
					<th class="mdc-data-table__header-cell" role="columnheader" scope="col"><?php esc_html_e( 'Default Speech Template', 'speaker-lite' ); ?></th>
				</tr>
				</thead>
				<tbody class="mdc-data-table__content">
				<?php foreach ( $custom_posts as $key => $post_type ) : ?>
					<tr class="mdc-data-table__row">
						<td class="mdp-post-type mdp-sc-name mdc-data-table__cell">
                            <span data-post-type="<?php echo esc_attr( $key ); ?>">
                                <?php echo esc_html( $post_type ); ?>
                                <span>(<?php echo esc_html( $key ); ?>)</span>
                            </span>
						</td>
						<td class="mdp-sc-name mdc-data-table__cell">

							<?php

							/** Return default ST for current post type. */
							$default = MetaBox::get_instance()->get_default_st( $key );

							/** Render Speech Template Select. */
							UI::get_instance()->render_select(
								$options,
								$default, // Selected option.
								esc_html__( 'Speech Template', 'speaker-lite' ),
								'',
								[
									'name' => 'mdp_speaker_lite_default_speech_template_for__' . $key,
									'id' => 'mdp-speaker-lite-default-speech-template-for--' . $key,
								] );

							?>

						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<p class="mdp-speaker-full-text"><?php echo esc_html__( 'Speech Templates for Custom Post Types are not available in the Speaker Lite. Go ', 'speaker-lite' ) . '<a href="https://1.envato.market/speaker" target="_blank">' . esc_html__( 'PRO', 'speaker-lite' ) . "</a>" . esc_html__( ' to unlock additional functionality.', 'speaker-lite' )?></p>
		<?php

	}

	/**
	 * Return list of Custom Post Types.
	 *
	 * @param array $cpt Array with posts types to exclude.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return array
	 **/
	private static function get_cpt( $cpt = [] ) {

		$defaults = [
			'exclude' => [
				'attachment',
				'elementor_library',
				'notification'
			],
		];

		$cpt = array_merge( $defaults, $cpt );

		$post_types_objects = get_post_types(
			[
				'public' => true,
			], 'objects'
		);

		/**
		 * Filters the list of post type objects used by plugin.
		 *
		 * @since 1.0.0
		 *
		 * @param array $post_types_objects List of post type objects used by plugin.
		 **/
		$post_types_objects = apply_filters( 'speaker/post_type_objects', $post_types_objects );

		$cpt['options'] = [];

		foreach ( $post_types_objects as $cpt_slug => $post_type ) {

			if ( in_array( $cpt_slug, $cpt['exclude'], true ) ) {
				continue;
			}

			$cpt['options'][ $cpt_slug ] = $post_type->labels->name;

		}

		return $cpt['options'];

	}

	/**
	 * @var TabPostTypes|null
	 */
	private static ?TabPostTypes $instance = null;

	/**
	 * Get the instance of this class.
	 * @return TabPostTypes
	 */
	public static function getInstance(): TabPostTypes {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
