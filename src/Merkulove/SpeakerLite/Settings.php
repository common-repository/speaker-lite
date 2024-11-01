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
 * SINGLETON: Class used to implement plugin settings.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class Settings {

	/**
	 * Speaker Plugin settings.
	 *
	 * @var array()
	 * @since 1.0.0
	 **/
	public $options = [];

	/**
	 * The one true Settings.
	 *
	 * @var Settings
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new Settings instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Get plugin settings. */
		$this->get_options();

	}

	/**
	 * Render Tabs Headers.
	 *
	 * @param string $current - Selected tab key.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function print_tabs( $current ) {

		/** Get available tabs. */
        $tabs = $this->get_tabs();

		/** Render Tabs. */
		?>
        <aside class="mdc-drawer">
            <div class="mdc-drawer__content">
                <nav class="mdc-list">
                    <?php

                    /** Render logo in plugin settings. */
                    $this->render_logo();

                    /** Render settings tabs. */
                    $this->render_tabs( $tabs, $current );

					/** Helpful links. */
					$this->support_link();

                    /** Display Go Pro link. */
					$this->display_go_pro();

					?>
                </nav>
            </div>
        </aside>
		<?php
	}

    /**
     * Render settings tabs.
     *
     * @param array $tabs       - Array of available tabs.
     * @param string $current   - Slug of active tab.
     *
     * @access private
     * @since 1.0.0
     *
     * @return void
     **/
	private function render_tabs( $tabs, $current ) {

	    ?>
        <hr class="mdc-plugin-menu">
        <hr class="mdc-list-divider">
        <h6 class="mdc-list-group__subheader"><?php echo esc_html__( 'Plugin settings', 'speaker-lite' ) ?></h6>
        <?php

        /** Plugin settings tabs. */
        foreach ( $tabs as $tab => $value ) {

            /** Prepare CSS classes. */
            $classes = [];
            $classes[] = 'mdc-list-item';

            /** Mark Active Tab. */
            if ( $tab === $current ) {
                $classes[] = 'mdc-list-item--activated';
            }

            /** Hide Developer tab before multiple clicks on logo. */
            if ( 'developer' === $tab ) {
                $classes[] = 'mdp-developer';
                $classes[] = 'mdc-hidden';
                $classes[] = 'mdc-list-item--activated';
            }

            /** Prepare link. */
            $link = '?page=mdp_speaker_lite_settings&tab=' . $tab;

            ?>
            <a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_attr( $link ); ?>">
                <i class='material-icons mdc-list-item__graphic' aria-hidden='true'><?php echo esc_html( $value['icon'] ); ?></i>
                <span class='mdc-list-item__text'><?php echo esc_html( $value['name'] ); ?></span>
            </a>
            <?php
        }

    }

    /**
     * Return an array of available tabs in plugin settings.
     *
     * @access private
     * @since 1.0.0
     *
     * @return array
     **/
	private function get_tabs() {

        $tabs = [];

        /** Check for required extensions. */
        if ( CheckCompatibility::get_instance()->do_settings_checks( true ) ) {

            $tabs['voice'] = [
                'icon' => 'tune',
                'name' => esc_html__( 'Voice', 'speaker-lite' )
            ];

            /** Adds key dependent tabs: design, css, assignments. */
            $tabs = $this->add_key_dependent_tabs( $tabs );

        }

        $tabs['status'] = [
            'icon' => 'info',
            'name' => esc_html__( 'Status', 'speaker-lite' )
        ];

        $tabs['uninstall'] = [
            'icon' => 'delete_sweep',
            'name' => esc_html__( 'Uninstall', 'speaker-lite' )
        ];

        /** Adds a developer tab. */
        $tabs = $this->add_developer_tab( $tabs );

        return $tabs;

    }

    /**
     * Adds key dependent tabs: design, css, assignments.
     *
     * @param array $tabs - Array of tabs to show in plugin settings.
     *
     * @access private
     * @since 1.0.0
     *
     * @return array - Array of tabs to show in plugin settings.
     **/
    private function add_key_dependent_tabs( $tabs ) {

        /** Show this tabs only if we have key file. */
        if ( ! $this->options['dnd-api-key'] ) { return $tabs; }

        $tabs['design'] = [
            'icon' => 'brush',
            'name' => esc_html__( 'Design', 'speaker-lite' )
        ];

        $tabs['post_types'] = [
            'icon' => 'article',
            'name' => esc_html__( 'Post Types', 'speaker-lite' )
        ];

        $tabs['css'] = [
            'icon' => 'code',
            'name' => esc_html__( 'Custom CSS', 'speaker-lite' )
        ];

        $tabs['assignments'] = [
            'icon' => 'flag',
            'name' => esc_html__( 'Assignments', 'speaker-lite' )
        ];

        return $tabs;

    }

    /**
     * Adds a developer tab if all the necessary conditions are met.
     *
     * @param array $tabs - Array of tabs to show in plugin settings.
     *
     * @access private
     * @since 1.0.0
     *
     * @return array - Array of tabs to show in plugin settings.
     **/
    private function add_developer_tab( $tabs ) {

        /** Output Developer tab only if DEBUG mode enabled. */
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

            $tabs['developer'] = [
                'icon' => 'developer_board',
                'name' => esc_html__( 'Developer', 'speaker-lite' )
            ];

        }

        return $tabs;

    }

    /**
     * Render logo and Save changes button in plugin settings.
     *
     * @access private
     * @since 1.0.0
     *
     * @return void
     **/
	private function render_logo() {

	    ?>
        <div class="mdc-drawer__header mdc-plugin-fixed">
            <!--suppress HtmlUnknownAnchorTarget -->
            <a class="mdc-list-item mdp-plugin-title" href="#wpwrap">
                <i class="mdc-list-item__graphic" aria-hidden="true">
                    <img src="<?php echo esc_attr( SpeakerLite::$url . 'images/logo-color.svg' ); ?>" alt="<?php echo esc_html__( 'Speaker Lite', 'speaker-lite' ) ?>">
                </i>
                <span class="mdc-list-item__text">
                    <?php echo esc_html__( 'Speaker Lite', 'speaker-lite' ) ?>
                    <sup><?php echo esc_html__( 'v.', 'speaker-lite' ) . esc_html( SpeakerLite::$version ); ?></sup>
                </span>
            </a>
            <button type="submit" name="submit" id="submit" class="mdc-button mdc-button--dense mdc-button--raised">
                <span class="mdc-button__label"><?php echo esc_html__( 'Save changes', 'speaker-lite' ) ?></span>
            </button>
        </div>
        <?php

    }

	/**
	 * Displays useful links for an activated and non-activated plugin.
	 *
	 * @since 1.0.0
     *
     * @return void
	 **/
	public function support_link() { ?>

        <hr class="mdc-list-divider">
        <h6 class="mdc-list-group__subheader"><?php echo esc_html__( 'Helpful links', 'speaker-lite' ) ?></h6>

        <a class="mdc-list-item" href="https://docs.merkulov.design/speaker-lite/" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'collections_bookmark' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'Documentation', 'speaker-lite' ) ?></span>
        </a>

        <a class="mdc-list-item" href="https://wordpress.org/support/plugin/speaker-lite/" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'mail' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'Get help', 'speaker-lite' ) ?></span>
        </a>
        <a class="mdc-list-item" href="https://wordpress.org/support/plugin/speaker-lite/reviews/#new-post" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'thumb_up' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'Rate this plugin', 'speaker-lite' ) ?></span>
        </a>

        <a class="mdc-list-item" href="https://1.envato.market/cc-merkulove" target="_blank">
            <i class="material-icons mdc-list-item__graphic" aria-hidden="true"><?php echo esc_html__( 'store' ) ?></i>
            <span class="mdc-list-item__text"><?php echo esc_html__( 'More plugins', 'speaker-lite' ) ?></span>
        </a>
		<?php

	}

    /**
     * Display Go Pro button.
     *
     * @since 1.0.0
     * @access public
     **/
	private function display_go_pro() {

        $go_pro_tab = admin_url( 'admin.php?page=mdp_speaker_lite_settings&tab=go_pro' );
        ?>

        <hr class="mdc-list-divider">
        <h6 class="mdc-list-group__subheader"><?php esc_html_e( 'Upgrade License', 'speaker-lite' ); ?></h6>

        <a class="mdc-list-item mdc-activation-status activated" href="<?php echo esc_url( $go_pro_tab ); ?>">
            <i class='material-icons mdc-list-item__graphic' aria-hidden='true'>label_important</i>
            <span class="mdc-list-item__text"><?php esc_html_e( 'Upgrade to Pro', 'speaker-lite' ); ?></span>
        </a>

        <?php

    }

	/**
	 * Add plugin settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_settings_page() {

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'settings_init' ] );

	}

	/**
	 * Create Custom CSS Tab.
	 *
	 * @since 1.0.0
	 * @access private
	 **/
	private function tab_custom_css() {

		/** Custom CSS. */
		$group_name = 'SpeakerCSSOptionsGroup';
		$section_id = 'mdp_speaker_lite_settings_page_css_section';

		/** Create settings section. */
		register_setting( $group_name, 'mdp_speaker_lite_css_settings' );
		add_settings_section( $section_id, '', null, $group_name );

    }

    /**
     * Create Go Pro Tab.
     *
     * @since 1.0.0
     * @access private
     **/
    private function tab_go_pro() {

        /** Go Pro Tab. */
        register_setting( 'SpeakerGoProOptionsGroup', 'mdp_speaker_lite_go_pro_settings' );
        add_settings_section( 'mdp_speaker_settings_page_gp_pro_section', '', null, 'SpeakerGoProOptionsGroup' );

    }

	/**
	 * Generate Settings Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function settings_init() {

        TabVoice::getInstance();
        TabDesign::getInstance();
        TabPostTypes::getInstance();

        /** Create Assignments Tab. */
        AssignmentsTab::get_instance()->add_settings();

		/** Create Custom CSS Tab. */
		$this->tab_custom_css();

        /** Create Go Pro Tab. */
        $this->tab_go_pro();

		/** Create Status Tab. */
		StatusTab::get_instance()->add_settings();

		/** Create Uninstall Tab. */
		UninstallTab::get_instance()->add_settings();

		/** Create Developer Tab. */
		DeveloperBoard::get_instance()->add_settings();

	}

	/**
	 * Add admin menu for plugin settings.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_admin_menu() {

		add_menu_page(
			esc_html__( 'Speaker Settings', 'speaker-lite' ),
			esc_html__( 'Speaker Lite', 'speaker-lite' ),
			'manage_options',
			'mdp_speaker_lite_settings',
			[ $this, 'options_page' ],
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( SpeakerLite::$path . 'images/logo-menu.svg' ) ),
			'58.1961'// Always change digits after "." for different plugins.
		);

	}

	/**
	 * Plugin Settings Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function options_page() {

		if ( ! current_user_can( 'manage_options' ) ) { return; } ?>

        <!--suppress HtmlUnknownTarget -->
        <form action='options.php' method='post'>
            <div class="wrap">

				<?php
                /** Get active tab slug. */
                $tab = $this->get_active_tab();

				/** Render "Speaker settings saved!" message. */
				SettingsFields::get_instance()->render_nags();

				/** Render Tabs Headers. */
				?><section class="mdp-aside"><?php $this->print_tabs( $tab ); ?></section><?php

				/** Render Tabs Body. */
				?><section class="mdp-tab-content mdp-tab-name-<?php echo esc_attr( $tab ) ?>"><?php

					/** General Tab. */
					if ( 'voice' === $tab ) {

						echo '<h3>' . esc_html__( 'Voice Settings', 'speaker-lite' ) . '</h3>';
						settings_fields( 'SpeakerOptionsGroup' );
						do_settings_sections( 'SpeakerOptionsGroup' );

                    /** Design Tab. */
					} elseif ( 'design' === $tab ) {

						echo '<h3>' . esc_html__( 'Design Settings', 'speaker-lite' ) . '</h3>';
						settings_fields( 'DesignOptionsGroup' );
						do_settings_sections( 'DesignOptionsGroup' );

                    /** Post Types Tab. */
                    } elseif ( 'post_types' === $tab ) {

                        echo '<h3>' . esc_html__( 'Post Types Settings', 'speaker-lite' ) . '</h3>';
                        settings_fields( 'PostTypesOptionsGroup' );
                        do_settings_sections( 'PostTypesOptionsGroup' );

                    /** Assignments Tab. */
					} elseif ( 'assignments' === $tab ) {

						echo '<h3>' . esc_html__( 'Assignments Settings', 'speaker-lite' ) . '</h3>';
						settings_fields( 'SpeakerAssignmentsOptionsGroup' );
						do_settings_sections( 'SpeakerAssignmentsOptionsGroup' );
						AssignmentsTab::get_instance()->render_assignments();

                    /** Custom CSS Tab. */
                    } elseif ( 'css' === $tab ) {

						echo '<h3>' . esc_html__( 'Custom CSS', 'speaker-lite' ) . '</h3>';
						settings_fields( 'SpeakerCSSOptionsGroup' );
						do_settings_sections( 'SpeakerCSSOptionsGroup' );
						SettingsFields::get_instance()->custom_css();

                    /** Go Pro Tab. */
                    } elseif ( 'go_pro' === $tab ) {

                        settings_fields( 'SpeakerGoProOptionsGroup' );
                        do_settings_sections( 'SpeakerGoProOptionsGroup' );
                        SettingsFields::get_instance()->render_go_pro();

                    /** Status tab. */
					} elseif ( 'status' === $tab ) {

						echo '<h3>' . esc_html__( 'System Requirements', 'speaker-lite' ) . '</h3>';
						StatusTab::get_instance()->render_form();

                    /** Uninstall Tab. */
					} elseif ( 'uninstall' === $tab ) {

						echo '<h3>' . esc_html__( 'Uninstall Settings', 'speaker-lite' ) . '</h3>';
						UninstallTab::get_instance()->render_form();

                    /** Developer Tab. */
					} elseif ( 'developer' === $tab ) {

						echo '<h3>' . esc_html__( 'Developer Board', 'speaker-lite' ) . '</h3>';
						DeveloperBoard::get_instance()->render_form();

					}
					?>
                </section>
            </div>
        </form>

		<?php
	}

    /**
     * Return active tab slug.
     *
     * @access private
     * @since 1.0.0
     *
     * @return void
     **/
	private function get_active_tab() {

        /** Default Tab. */
        $tab = 'status';

        /** If the min requirements are met, show the voice tab. */
        $min_requirements = CheckCompatibility::get_instance()->do_settings_checks( false );
        if ( $min_requirements ) {
            $tab = 'voice';
        }

        /** Tab selected by user. */
        if ( isset ( $_GET['tab'] ) ) {
            $tab = filter_input( INPUT_GET, 'tab' );
        }

        /** If the min requirements are not met and selected hidden tab - show the status tab. */
        if ( ( ! $min_requirements ) && in_array( $tab, ['voice', 'design', 'post_types', 'go_pro', 'css', 'assignments'], true ) ) {
            $tab = 'status';
        }

        return $tab;

    }

	/**
	 * Get plugin settings with default values.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 **/
	public function get_options() {

		/** Default values. */
		$defaults = [

			# Voice Tab.
			'dnd-api-key'   => '',  // Encoded JSON API Key file.
			'language'      => 'en-US-Standard-C', // Language.
			'language-code' => 'en-US', // Language Code.
			'audio-profile' => 'handset-class-device', // Audio profile.
			'speaking-rate' => '1', // Speaking rate/speed.
			'pitch'         => '0', // Pitch.
            'volume'        => '0.0', // Volume Gain.
			'before_audio'  => '', // Before Audio.
            'read_title'    => 'off', // Read the Title.
			'after_audio'   => '', // After audio.
			'auto_generation' => 'off',

			# Design Tab.
			'position'      => 'before-content', // Player Position.

			'style'         => 'speaker-lite-round', // Default style.
			'bgcolor'       => 'rgba(2, 83, 238, 1)', // Tooltip background color.
			'link'          => 'none', // Download Link.

            # Post Types Tab.
            'cpt_support'   => ['post','page'], // Post Types

			# Custom CSS Tab.
            'custom_css'    => ''

		];

		/** Voice tab settings. */
		$options = get_option( 'mdp_speaker_lite_settings' );
		$results = wp_parse_args( $options, $defaults );

        $results['auto_generation'] = 'off';

		/** Design tab settings. */
		$design_settings = get_option( 'mdp_speaker_lite_design_settings' );
		$results = wp_parse_args( $design_settings, $results );

		/** Custom CSS tab settings. */
		$custom_css_settings = get_option( 'mdp_speaker_lite_css_settings' );
		$results = wp_parse_args( $custom_css_settings, $results );

		/** Reset API Key on fatal error. */
        if ( isset( $_GET['reset-api-key'] ) && '1' === $_GET['reset-api-key'] ) {

            $this->reset_api_key();

        }

        $results['before_audio'] = '';
        $results['read_title'] = 'off';
        $results['after_audio'] = '';
        $results['auto_generation'] = 'off';

		$this->options = $results;

	}

    /**
     * Reset API Key on fatal error.
     *
     * @since 1.0.0
     * @access public
     * @return void
     **/
	private function reset_api_key() {

	    /** Remove API Key. */
        $options = get_option( 'mdp_speaker_lite_settings' );
        $options['dnd-api-key'] = '';

        /** Save new value. */
        update_option( 'mdp_speaker_lite_settings', $options );

        /** Go to first tab. */
        wp_redirect( admin_url( '/admin.php?page=mdp_speaker_lite_settings&tab=voice' ) );
        exit;

    }

	/**
	 * Main Settings Instance.
	 *
	 * Insures that only one instance of Settings exists in memory at any one time.
	 *
	 * @static
	 * @return Settings
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Settings.
