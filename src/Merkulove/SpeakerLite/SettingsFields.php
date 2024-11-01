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
 * @package Merkulove/SpeakerLite
 */
final class SettingsFields {

	/**
	 * The one true SettingsFields.
	 *
	 * @var SettingsFields
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Render CSS field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function custom_css() {
		?>
		<div>
            <label>
                <textarea
                    id="mdp_custom_css_fld"
                    name="mdp_speaker_lite_css_settings[custom_css]"
                    class="mdp_custom_css_fld"><?php echo esc_textarea( Settings::get_instance()->options['custom_css'] ); ?></textarea>
            </label>
			<p class="description"><?php esc_html_e( 'Add custom CSS here.', 'speaker-lite' ); ?></p>
		</div>
		<?php
	}

    /**
     * Render Purchase Code field.
     *
     * @since 1.0.0
     * @access public
     **/
    public function render_go_pro() {

	    $this->render_PRO(); ?>

        <div class="mdp-activation">

            <?php $this->render_FAQ(); ?>
            <?php $this->render_subscribe(); ?>

        </div>

        <?php
    }

	/**
	 * Render GO PRO block.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function render_PRO() {
	    ?>

        <div class="mdp-go-pro-box">
            <h3><?php esc_html_e( 'Unlock additional functionality!', 'speaker-lite' ); ?></h3>
            <p>
				<?php esc_html_e( 'Speaker Lite includes only the basic functions. Go ', 'speaker-lite' ); ?>
                <a href="https://speaker.merkulov.design/lite" target="_blank"><?php esc_html_e( 'Speaker PRO', 'speaker-lite' ); ?></a>
				<?php esc_html_e( ' to get more awesome features. Buy a license and gain access to all hidden features.', 'speaker-lite' ); ?>
            </p>
            <div class="mdp-pro-features">
                <ul>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>No prohibitions or restrictions</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>More High-end Voices</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Full power of SSML</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Automatically Speech Synthesizing​</span>
                    </li>
                </ul>
                <ul>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Full support of Custom Post Types</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Visual Speech Template Editor</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Batch Pages Processing</span>
                    </li>
                    <li>
                        <i class="material-icons">label_important</i>
                        <span>Premium Customer Support</span>
                    </li>
                </ul>
            </div>
            <div class="mdp-pro-buttons">
                <a href="https://1.envato.market/speaker-buy" target="_blank" class="mdp-button-pro">
					<?php esc_html_e( 'Upgrade to PRO', 'speaker-lite' ); ?>
                </a>
                <a href="https://speaker.merkulov.design/lite#speaker-compare" target="_blank" class="mdp-button-compare">
					<?php esc_html_e( 'Compare Speaker', 'speaker-lite' ); ?>
                </a>
            </div>

        </div>

        <?php
    }

    /**
     * Render FAQ block.
     *
     * @since 1.0.0
     * @access public
     **/
    public function render_FAQ() {
        ?>

        <div class="mdp-activation-faq">

            <div class="mdc-accordion" data-mdc-accordion="showfirst: true">

                <h3><?php esc_html_e( 'FAQ\'S', 'speaker-lite' ); ?></h3>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'Why should I go to Pro version?', 'speaker-lite' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p><?php esc_html_e( 'The Speaker Pro provides ', 'speaker-lite' ); ?>
                        <a href="https://speaker.merkulov.design/lite/#speaker-compare" target="_blank"><?php esc_html_e( ' advanced features', 'speaker-lite' );?></a>
                        <?php esc_html_e( 'including custom post types support and speech templates, which makes the plugin compatible with most themes and plugins. You can create audio for posts with any number of characters and explore other useful functions to work with your project. ', 'speaker-lite' ); ?>
                    </p>
                </div>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'Can I use one license for multiple sites?', 'speaker-lite' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p>
                        <?php esc_html_e( 'According to the Envato rules, all products with a', 'speaker-lite' ); ?>
                        <a href="https://themeforest.net/licenses/terms/regular" target="_blank"><?php esc_html_e( 'regular license', 'speaker-lite' );?></a>
                        <?php esc_html_e( 'can be used only for one end product except the situation when several sites are used for one project. Otherwise, a separate license is needed for each site.', 'speaker-lite' ); ?>
                    </p>
                </div>

                <div class="mdc-accordion-title">
                    <i class="material-icons">help</i>
                    <span class="mdc-list-item__text"><?php esc_html_e( 'Are there any restrictions in the Pro version?', 'speaker-lite' ); ?></span>
                </div>
                <div class="mdc-accordion-content">
                    <p>
                        <?php esc_html_e( 'For our part, we provide all the available functions for the Speaker Pro but please note the plugin based on Google Cloud API, which provides a free quota of 4 million characters every month. If your website consumes more, then each next million characters will cost $4. Here is', 'speaker-lite' ); ?>
                        <a href="https://cloud.google.com/text-to-speech/pricing" target="_blank"><?php esc_html_e( 'Google Pricing', 'speaker-lite' );?></a>
                        <?php esc_html_e( '. In the Google settings, there are quotas for dropping money from the card, and you can set them to $0. And when the free characters quota ends, the plugin will stop working until next month.', 'speaker-lite' ); ?>
                    </p>
                </div>

            </div>

        </div>
        <?php
    }

	/**
	 * Render e-sputnik Subscription Form block.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function render_subscribe() {
	    ?>
        <div class="mdp-activation-form">

            <h3><?php esc_html_e( 'Subscribe to news', 'speaker-lite' ); ?></h3>
            <p><?php esc_html_e( 'Sign up for the newsletter to be the first to know about news and discounts on Speaker and other WordPress plugins.', 'speaker-lite' ); ?></p>

			<?php
			/** Render Name. */
			UI::get_instance()->render_input(
				'',
				esc_html__( 'Your Name', 'speaker-lite'),
				'',
				[
					'name' => 'mdp-speaker-lite-subscribe-name',
					'id' => 'mdp-speaker-lite-subscribe-name'
				]
			);

			/** Render e-Mail. */
			UI::get_instance()->render_input(
				'',
				esc_html__( 'Your E-Mail', 'speaker-lite'),
				'',
				[
					'name'  => 'mdp-speaker-lite-subscribe-mail',
					'id'    => 'mdp-speaker-lite-subscribe-mail',
					'type'  => 'email',
				]
			);

			/** Render button. */
			UI::get_instance()->render_button(
				esc_html__( 'Subscribe', 'speaker-lite' ),
				'',
				[
					"name"  => "mdp-speaker-lite-subscribe",
					"id"    => "mdp-speaker-lite-subscribe",
					"class" => "mdp-reset"
				],
				''
			);
			?>

        </div>
        <?php
    }

	/**
	 * Render "Settings Saved" nags.
	 *
     * @return void
	 * @since 1.0.0
	 **/
	public function render_nags() {

		if ( ! isset( $_GET['settings-updated'] ) ) { return; }

		if ( strcmp( $_GET['settings-updated'], 'true' ) === 0 ) {

			/** Render "Settings Saved" message. */
			UI::get_instance()->render_snackbar( esc_html__( 'Settings saved!', 'speaker-lite' ) );

		}

	}

	/**
	 * Main SettingsFields Instance.
	 *
	 * Insures that only one instance of SettingsFields exists in memory at any one time.
	 *
	 * @static
	 * @return SettingsFields
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class SettingsFields.
