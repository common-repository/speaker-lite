<?php
/**
 * Plugin Name: Speaker Lite
 * Plugin URI: https://speaker-lite.merkulov.design/
 * Description: Create an audio version of your posts, with a selection of more than 400 voices across more than 40 languages and variants.
 * Author: Merkulove
 * License: GPLv3 or later
 * Version: 1.2.0
 * Author URI: https://1.envato.market/cc-merkulove
 * Requires PHP: 8.1
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Text Domain: speaker-lite
 * Domain Path: /languages
 **/

namespace Merkulove;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/** Include plugin autoloader for additional classes. */
require __DIR__ . '/src/autoload.php';

/** Includes the autoloader for libraries installed with Composer. */
require __DIR__ . '/vendor/autoload.php';

use Merkulove\SpeakerLite\Helper;
use Merkulove\SpeakerLite\MetaBox;
use Merkulove\SpeakerLite\Settings;
use Merkulove\SpeakerLite\WPBakery;
use Merkulove\SpeakerLite\Elementor;
use Merkulove\SpeakerLite\Shortcodes;
use Merkulove\SpeakerLite\AdminStyles;
use Merkulove\SpeakerLite\FrontStyles;
use Merkulove\SpeakerLite\PluginHelper;
use Merkulove\SpeakerLite\AdminScripts;
use Merkulove\SpeakerLite\FrontScripts;
use Merkulove\SpeakerLite\SpeakerCaster;
use Merkulove\SpeakerLite\DeveloperBoard;
use Merkulove\SpeakerLite\CheckCompatibility;

/**
 * SINGLETON: Core class used to instantiate and control a Speaker plugin.
 *
 * @since 1.0.0
 **/
final class SpeakerLite {

    /**
     * Plugin version.
     *
     * @string version
     * @since 1.0.0
     **/
    public static $version;

    /**
     * Use minified libraries if SCRIPT_DEBUG is turned off.
     *
     * @since 1.0.0
     **/
    public static $suffix;

    /**
     * URL (with trailing slash) to plugin folder.
     *
     * @var string
     * @since 1.0.0
     **/
    public static $url;

    /**
     * PATH to plugin folder.
     *
     * @var string
     * @since 1.0.0
     **/
    public static $path;

    /**
     * Plugin base name.
     *
     * @var string
     * @since 1.0.0
     **/
    public static $basename;

	/**
	 * Plugin admin menu base.
	 *
	 * @var string
	 * @since 1.0.0
	 **/
	public static $menu_base;

    /**
     * The one true Speaker.
     *
     * @since 1.0.0
     **@var SpeakerLite
     */
    private static $instance;

    /**
     * Sets up a new plugin instance.
     *
     * @since 1.0.0
     * @access public
     **/
    private function __construct() {

	    /** Initialize main variables. */
	    $this->initialization();

    }

	/**
	 * Setup the plugin.
	 *
	 * @since 1.0.0
	 * @access public
     *
	 * @return void
	 **/
	public function setup() {

        /** Disable all if full version of Speaker was found. */
	    if ( class_exists( '\Merkulove\Speaker' ) ) { return; }

	    /** Do critical initial checks. */
	    if ( ! CheckCompatibility::get_instance()->do_initial_checks( true ) ) { return; }

		/** Define hooks that runs on both the front-end as well as the dashboard. */
		$this->both_hooks();

		/** Define public hooks. */
		$this->public_hooks();

		/** Define admin hooks. */
		$this->admin_hooks();

	}

	/**
	 * Initialize main variables.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 **/
	public function initialization() {

		/** Get Plugin version. */
		$plugin_data = $this->get_plugin_data();
	    self::$version = $plugin_data['Version'];

		/** Gets the plugin URL (with trailing slash). */
		self::$url = plugin_dir_url( __FILE__ );

		/** Gets the plugin PATH. */
		self::$path = plugin_dir_path( __FILE__ );

		/** Use minified libraries if SCRIPT_DEBUG is turned off. */
		self::$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		/** Set plugin basename. */
		self::$basename = plugin_basename( __FILE__ );

		/** Plugin settings page base. */
		self::$menu_base = 'toplevel_page_mdp_speaker_lite_settings';

	}

	/**
	 * Define hooks that runs on both the front-end as well as the dashboard.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 **/
	private function both_hooks() {

		/** Load the plugin text domain for translation. */
		PluginHelper::get_instance()->load_plugin_textdomain();

		/** Load plugin settings. */
		Settings::get_instance();

        /** Register WPBakery Elements. */
        $this->register_wpbakery_elements();

        /** Register Elementor Widgets. */
        $this->register_elementor_widgets();

        /** Adds all the necessary shortcodes. */
        Shortcodes::get_instance();

	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 **/
	private function public_hooks() {

		/** Work only on frontend area. */
		if ( is_admin() ) { return; }

		/** Load CSS for Frontend Area. */
		FrontStyles::get_instance();

		/** Load JavaScripts for Frontend Area. */
		FrontScripts::get_instance();

		/** Add player code to page. */
		SpeakerCaster::get_instance()->add_player();

		/** Speaker use custom page template to parse content without garbage. */
        add_filter( 'template_include', [SpeakerCaster::class, 'speaker_lite_page_template'], PHP_INT_MAX );

        /** Hide admin bar for Speech Template Editor. */
        SpeakerCaster::hide_admin_bar();

	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 **/
	private function admin_hooks() {

		/** Work only in admin area. */
		if ( ! is_admin() ) { return; }

		/** Remove notices, add links in plugin list, show admin warnings, remove wp copyrights. */
		PluginHelper::get_instance()->add_actions();

		/** Create folder for audio files. */
		Helper::get_instance()->create_speaker_lite_folder();

		/** Add plugin settings page. */
		Settings::get_instance()->add_settings_page();

		/** Add Ajax handlers and before_delete_post action. */
		SpeakerCaster::get_instance()->add_actions();

		/** Add Meta Box for selected post types. */
		MetaBox::get_instance();

		/** Add admin styles. */
		AdminStyles::get_instance();

		/** Add admin javascript. */
		AdminScripts::get_instance();

		/** Add Ajax handlers for Developer Board. */
		DeveloperBoard::add_ajax();

	}

    /**
     * Registers a WPBakery element.
     *
     * @return void
     * @since 1.0.0
     * @access public
     **/
    public function register_elementor_widgets() {

        /** Initialize Elementor widgets. */
        Elementor::get_instance();

    }

    /**
     * Registers a WPBakery element.
     *
     * @return void
     * @since 1.0.0
     * @access public
     **/
    public function register_wpbakery_elements() {

        /** Initialize WPBakery Element. */
        WPBakery::get_instance();

    }

	/**
	 * Return current plugin metadata.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *     Plugin data. Values will be empty if not supplied by the plugin.
	 *
	 *     @type string $Name        Name of the plugin. Should be unique.
	 *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
	 *     @type string $Description Plugin description.
	 *     @type string $Author      Author's name.
	 *     @type string $AuthorURI   Author's website address (if set).
	 *     @type string $Version     Plugin version.
	 *     @type string $TextDomain  Plugin textdomain.
	 *     @type string $DomainPath  Plugins relative directory path to .mo files.
	 *     @type bool   $Network     Whether the plugin can only be activated network-wide.
	 *     @type string $RequiresWP  Minimum required version of WordPress.
	 *     @type string $RequiresPHP Minimum required version of PHP.
	 * }
	 **/
	public function get_plugin_data() {

		if ( ! function_exists('get_plugin_data') ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return get_plugin_data( __FILE__ );

	}

    /**
     * Main Speaker Instance.
     *
     * Insures that only one instance of Speaker exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     *
     * @return SpeakerLite
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

} // End Class Speaker.

/** Run Speaker class once after activated plugins have loaded. */
add_action( 'plugins_loaded', [SpeakerLite::get_instance(), 'setup'], PHP_INT_MAX );

