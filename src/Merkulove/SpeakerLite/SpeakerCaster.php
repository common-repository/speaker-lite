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

use DOMXPath;
use DOMDocument;
use Merkulove\SpeakerLite;

use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\ApiCore\ApiException;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * @package Merkulove/SpeakerCaster
 */
final class SpeakerCaster {

	/**
	 * The one true SpeakerCaster.
	 *
	 * @var SpeakerCaster
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Add Ajax handlers and before_delete_post action.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function add_actions() {

		/** Ajax Create Audio on Backend. */
		add_action( 'wp_ajax_gspeak', [ $this, 'gspeak' ] );

		/** Ajax Remove Audio on Backend. */
		add_action( 'wp_ajax_remove_audio', [ $this, 'remove_audio' ] );

		/** Remove audio file on remove post record. */
		add_action( 'before_delete_post', [ $this, 'before_delete_post' ] );

		/** Process Speech Template Create/Update/Delete on Backend. */
		add_action( 'wp_ajax_process_st', [ $this, 'process_st' ] );

		/** Get Speech Template Data by ID. */
		add_action( 'wp_ajax_get_st', [ $this, 'get_st_ajax' ] );

		/** Set Speech Template as Default. */
		add_action( 'wp_ajax_set_default_st', [ $this, 'set_default_st' ] );

	}

	/**
	 * Combine multiple audio files to one .mp3.
	 *
	 * @param $files - Audio files for gluing into one big.
	 * @param $post_id - ID of the Post/Page.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function glue_audio( $files, $post_id ) {

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		/** Path to post audio file. */
		$audio_file = $upload_basedir . '/speaker/post-' . $post_id . '.mp3';

		/** Just in case, if it exist. */
		wp_delete_file( $audio_file );
		foreach ( $files as $audio ) {

			/** Add new audio part to file. */
			file_put_contents( $audio_file, file_get_contents( $audio ), FILE_APPEND );

			/** Remove temporary audio files. */
			wp_delete_file( $audio );

		}

	}

	/**
	 * Convert HTML to temporary audio file.
	 *
	 * @param $html - Content to be voiced.
	 * @param $post_id - ID of the Post/Page.
	 *
	 * @return string
	 * @throws ApiException
	 * @since 1.0.0
	 * @access public
	 **/
	public function part_speak( $html, $post_id ) {

		/**
		 * Filters html part before speak it.
		 *
		 * @param string $html Post content part.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$html = apply_filters( 'speaker_lite_before_part_speak', $html, $post_id );

		/** Strip all html tags, except SSML tags.  */
		$html = strip_tags( $html, '<p><break><say-as><sub><emphasis><prosody><voice>' );

		/** Remove the white spaces from the left and right sides.  */
		$html = trim( $html );

		/** Convert HTML entities to their corresponding characters: &quot; => " */
		$html = html_entity_decode( $html );

		/**
		 * Replace special characters with HTML Ampersand Character Codes.
		 * These codes prevent the API from confusing text with SSML tags.
		 * '&' --> '&amp;'
		 **/
		$html = str_replace( '&', '&amp;', $html );

		/** Get language code and name from <voice> tag, or use default. */
		list( $lang_code, $lang_name ) = XMLHelper::get_instance()->get_lang_params_from_tag( $html );

		/** Allow only standard voices */
		if ( TabVoice::get_voice_type( $lang_name ) !== 'standard' ) {
			$lang_name = $lang_code . '-Standard-A';
		}

		/** We don’t need <voice> tag anymore. */
		$html = strip_tags( $html, '<p><break><say-as><sub><emphasis><prosody>' );

		/** Force to SSML. */
		$ssml = "<speak>";
		$ssml .= $html;
		$ssml .= "</speak>";

		/**
		 * Filters $ssml content before Google Synthesis it.
		 *
		 * @param string $ssml Post content part.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$ssml = apply_filters( 'speaker_lite_before_synthesis', $ssml, $post_id );

		/** Instantiates a client. */
		$client = new TextToSpeechClient();

		$input = (new SynthesisInput())
			->setSsml( $ssml );

		$voice = (new VoiceSelectionParams())
			->setLanguageCode($lang_code)
			->setName( $lang_name );

		$options = Settings::get_instance()->options;
		$request = (new SynthesizeSpeechRequest())
			->setInput($input)
			->setVoice($voice)
			->setAudioConfig($this->get_audio_config( $options ));

		$response = $client->synthesizeSpeech($request);

		/** The response's audioContent is binary. */
		$audioContent = $response->getAudioContent();

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		/** Path to audio file. */
		$audio_file = $upload_basedir . '/speaker/tmp-' . uniqid( '', false ) . '-post-' . $post_id . '.mp3';
		file_put_contents( $audio_file, $audioContent );

		return $audio_file;

	}

	/**
	 * Get audio config
	 * @param $options
	 * @return AudioConfig
	 */
	private function get_audio_config( $options ) {

		return ( new AudioConfig() )
			->setAudioEncoding( AudioEncoding::MP3 )
			->setEffectsProfileId( [ $options['audio-profile'] ] )
			->setSpeakingRate( $options['speaking-rate'] )
			->setPitch( $options['pitch'] )
			->setSampleRateHertz( 24000 )
			->setVolumeGainDb( $options['volume'] );

	}

	/**
	 * Prepare HTML for Google TTS.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function great_divider() {

		esc_html_e( 'Speaker Lite have 5000 characters limit on the size of processed content. This post has exceeded limits. Use the full version of the Speaker plugin to generate audio for large texts.', 'speaker-lite' );
		wp_die();

	}

	/**
	 * Add custom text before/after audio.
	 *
	 * @param $parts - Content splitted to parts about 4500. Google have limits Total characters per request.
	 *
	 * @return array With text parts to speech.
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function add_watermark( $parts ) {

		/** Before Audio. */
		if ( Settings::get_instance()->options['before_audio'] ) {
			array_unshift( $parts, do_shortcode( Settings::get_instance()->options['before_audio'] ) );
		}

		/** After Audio. */
		if ( Settings::get_instance()->options['after_audio'] ) {
			$parts[] = do_shortcode( Settings::get_instance()->options['after_audio'] );
		}

		return $parts;
	}

	/**
	 * Divide parts by voice. One part voiced by one voice.
	 *
	 * @param array $parts HTML parts to be voiced.
	 *
	 * @return array() HTML parts to be voiced.
	 * @since 1.0.0
	 * @access public
	 */
	public function voice_divider( $parts ) {

		/** Array with parts splitted by voice. */
		$result = [];
		foreach ( $parts as $part ) {

			/** Mark location of the cut. */
			$part = str_replace( [ "<voice", "</voice>" ], [ "{|mdp|}<voice", "</voice>{|mdp|}" ], $part );

			/** Cut by marks. */
			$arr = explode( "{|mdp|}", $part );

			/** Clean the array. */
			$arr = array_filter( $arr );

			/** Combine results. */
			/** @noinspection SlowArrayOperationsInLoopInspection */
			$result = array_merge( $result, $arr );

		}

		/** Fix broken html of each part. */
		foreach ( $result as &$el ) {
			$el = XMLHelper::get_instance()->repair_html( $el );
		}
		unset( $el );

		/** Remove empty elements. */
		$result = array_filter( $result );

		return $result;

	}

	/**
	 * Return array content of each ST element.
	 *
	 * @param $post_id - ID of the Post/Page content from which we will parse.
	 * @param $stid - ID of Speech Template.
	 *
	 * @return array|mixed|object
	 * @since 1.0.0
	 * @access public
	 **/
	private function parse_st_content( $post_id, $stid ) {

		/** Get Speech Template data. */
		$st = $this->get_st( $stid );

		/** On error. */
		if ( ! $st ) {
			return false;
		}

		/** Get ST elements. */
		$elements = $st['elements'];

		/** On error. */
		if ( ! is_array( $elements ) ) {
			return false;
		}

		/** Use internal libxml errors -- turn on in production, off for debugging. */
		libxml_use_internal_errors( true );

		/** Create a new DomDocument object. */
		$dom = new DomDocument;

		/** Get Current post Content. */
		$post_content = $this->parse_post_content( $post_id );

		/** Load the HTML. */
		$dom->loadHTML( $post_content );

		$parts = [];

		/** Collect content foreach element. */
		foreach ( $elements as $key => $element ) {

			/** Parse content for DOM Elements in ST. */
			if ( 'element' === $element['type'] ) {

				/** Create a new XPath object. */
				$xpath = new DomXPath( $dom );

				/** Query all elements with xPath */
				$nodes = $xpath->evaluate( $element['xpath'] );

				/** Skip element, if it's not found. */
				if ( ! $nodes->length ) {
					continue;
				}

				/** Get element content. */
				$content = XMLHelper::get_instance()->get_inner_html( $nodes->item( 0 ) );

				$content = $this->clean_content( $content );

				/** Apply SSML tags to content. */
				$content = $this->apply_ssml_settings( $element, $content );

				if ( strlen( $content ) > 4995 ) {
					$this->great_divider();
				} else {
					/** Add first DomNode inner HTML. */
					$parts[] = $content;
				}

			} elseif ( 'text' === $element['type'] ) {

				/** Get custom content. */
				$content = $element['content'];

				$content = $this->clean_content( $content );

				/** Apply SSML tags to content. */
				$content = $this->apply_ssml_settings( $element, $content );

				/** Add custom content. */
				$parts[] = $content;

				if ( strlen( $content ) > 4995 ) {
					$this->great_divider();
				}

			} elseif ( 'pause' === $element['type'] ) {

				/** Add pause element. */
				$parts[] = "<break time=\"{$element['time']}ms\" strength=\"{$element['strength']}\" />";

			}

		}

		return $parts;

	}

	/**
	 * Apply SSML tags to content.
	 *
	 * @param array $element
	 * @param string $content
	 *
	 * @return array|mixed|object
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function apply_ssml_settings( $element, $content ) {

		/** Add 'Say As' if needed. */
		if ( ! in_array( $element['sayAs'], [ 'none', 'undefined' ] ) ) {
			$content = "<say-as interpret-as=\"{$element['sayAs']}\">{$content}</say-as>";
		}

		/** Add 'Emphasis' if needed. */
		if ( ! in_array( $element['emphasis'], [ 'none', 'undefined' ] ) ) {
			$content = "<emphasis level=\"{$element['emphasis']}\">{$content}</emphasis>";
		}

		/** If voice is different from default, change voice. */
		if (
			! in_array( $element['voice'], [ 'none', 'undefined' ] ) &&
			$element['voice'] !== Settings::get_instance()->options['language']
		) {

			$content = "<voice name=\"{$element['voice']}\">{$content}</voice>";

		}

		return $content;

	}

	/**
	 * Return post/page content by ID with executed shortcodes.
	 *
	 * @param $post_id - ID of the Post/Page content from which we will parse.
	 * @param string $template
	 *
	 * @return array|mixed|object
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function parse_post_content( $post_id, $template = null ) {

		/** Frontend url with post content to parse. */
		$url = $this->get_frontend_url( $post_id, $template );

		/** Prepare curl request to parse content. */
		$response = wp_remote_get(
			$url,
			[
				'timeout'     => 60,
				'ssl_verify'  => false,
			]
		);

		/** We’ll check whether the answer is correct. */
		if ( is_wp_error( $response ) ) {

			$return = [
				'success' => false,
				'message' => $response->get_error_message(),
			];
			wp_send_json( $return );
			wp_die();

		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ) {

			return wp_remote_retrieve_body( $response );

		}

		return false;

	}

	/**
	 * Speaker use custom page template to parse content without garbage.
	 *
	 * @param string $template - The path of the template to include.
	 *
	 * @return string
	 *
	 * @noinspection PhpUnused
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public static function speaker_lite_page_template( $template ) {

		/** Change template for correct parsing content. */
		if ( isset( $_GET['speaker-lite-template'] ) && 'speaker-lite' === $_GET['speaker-lite-template'] ) {

			/** Disable admin bar. */
			show_admin_bar( false );

			$template = SpeakerLite::$path . 'src/Merkulove/SpeakerLite/speaker-lite-template.php';

		}

		return $template;

	}

	/**
	 * Hide admin bar for Speech Template Editor.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public static function hide_admin_bar() {

		if ( isset( $_GET['speaker_lite_speech_template'] ) && '1' === $_GET['speaker_lite_speech_template'] ) {

			/** Hide admin bar for Speech Template Editor. */
			show_admin_bar( false );

		}

	}

	/**
	 * Return frontend url with post content to parse.
	 *
	 * @param int $post_id - ID of the Post/Page content from which we will parse.
	 * @param string $template
	 *
	 * @return string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function get_frontend_url( $post_id, $template = null ) {

		/** Get full permalink for the current post. */
		$url = get_permalink( $post_id );

		/** Returns a string if the URL has parameters or NULL if not. */
		$query = wp_parse_url( $url, PHP_URL_QUERY );

		/** Add speaker-lite-ssml param to URL. */
		if ( $query ) {

			$url .= '&speaker-lite-ssml=1';

		} else {

			$url .= '?speaker-lite-ssml=1';

		}

		/** Add template param to url. */
		if ( $template ) {

			$url .= '&speaker-lite-template=' . $template;

		}

		return $url;

	}

	/**
	 * Remove muted elements by class "speaker-lite-mute" or attribute speaker-lite-mute="".
	 *
	 * @param $post_content - Post/Page content.
	 *
	 * @return string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function remove_muted_html( $post_content ) {

		/** Hide DOM parsing errors. */
		libxml_use_internal_errors( true );
		libxml_clear_errors();

		/** Load the possibly malformed HTML into a DOMDocument. */
		$dom          = new DOMDocument();
		$dom->recover = true;
		$dom->loadHTML( '<?xml encoding="UTF-8"><body id="repair">' . $post_content . '</body>' ); // input UTF-8.

		$selector = new DOMXPath( $dom );

		/** Remove all elements with speaker-lite-mute="" attribute. */
		foreach ( $selector->query( '//*[@speaker-lite-mute]' ) as $e ) {
			$e->parentNode->removeChild( $e );
		}

		/** Remove all elements with class="speaker-lite-mute". */
		foreach ( $selector->query( '//*[contains(attribute::class, "speaker-lite-mute")]' ) as $e ) {
			$e->parentNode->removeChild( $e );
		}

		/** HTML without muted tags. */
		$body = $dom->documentElement->lastChild;

		return trim( XMLHelper::get_instance()->get_inner_html( $body ) );

	}

	/**
	 * Return Player code.
	 *
	 * @param int $id - Post/Page id.
	 *
	 * @return false|string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function get_player( $id = 0 ) {

		/** Show player if we have audio. */
		if ( ! $this->audio_exists( $id ) ) {
			return false;
		}

		/** Don't show player if we parse content. */
		if ( isset( $_GET['speaker-lite-ssml'] ) ) {
			return false;
		}

		/** Don't show player if in Speech Template Editor. */
		if ( isset( $_GET['speaker_lite_speech_template'] ) && '1' === $_GET['speaker_lite_speech_template'] ) {
			return false;
		}

		/** URL to post audio file. */
		$audio_url = $this->get_audio_url( $id );

		ob_start();
		$classes = '';
		$classes .= ' ' . Settings::get_instance()->options['position'] . ' ';
		$classes .= ' ' . Settings::get_instance()->options['style'] . ' ';
		$classes = trim( $classes );
		?>
        <div class="mdp-speaker-lite-wrapper">
            <div class="mdp-speaker-lite-box <?php echo esc_attr( $classes ); ?>"
                 style="background: <?php echo Settings::get_instance()->options['style'] === "speaker-lite-browser-default" ? 'none' : esc_attr( Settings::get_instance()->options['bgcolor'] ); ?>">
                <div>
					<?php echo do_shortcode( '[audio src="' . $audio_url . '" preload="metadata"]' ); ?>
                </div>
            </div>

			<?php if ( in_array( Settings::get_instance()->options['link'], [
				'frontend',
				'backend-and-frontend'
			] ) ) : ?>
                <p class="mdp-speaker-lite-download-box">
					<?php echo esc_html__( 'Download: ', 'speaker-lite' ) ?>
                    <a href="<?php echo esc_url( $audio_url ); ?>"
                       download=""
                       title="<?php echo esc_attr__( 'Download: ', 'speaker-lite' ) . htmlentities( get_the_title( $id ) ); ?>"><?php echo htmlentities( get_the_title( $id ) ); ?></a>
                </p>
			<?php endif; ?>
        </div>
		<?php

		return ob_get_clean();

	}

	/**
	 * Return URL to audio version of the post.
	 *
	 * @param int $id - Post/Page id.
	 *
	 * @return bool|string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function get_audio_url( $id = 0 ) {

		/** If audio file not exist. */
		$f_time = $this->audio_exists( $id );
		if ( ! $f_time ) {
			return false;
		}

		/** Current post ID. */
		if ( ! $id ) {

			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$id = get_the_ID();

			if ( ! $id ) {
				return false;
			}

		}

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_baseurl = $upload_dir['baseurl'];

		/** URL to post audio file. */
		$audio_url = $upload_baseurl . '/speaker/post-' . $id . '.mp3';

		/** Cache Busting. '.mp3' is needed. */
		$audio_url .= '?cb=' . $f_time . '.mp3';

		return $audio_url;
	}

	/**
	 * Checks if there is audio for the current post.
	 *
	 * @param int $id - Post/Page id.
	 *
	 * @return bool|false|int
	 * @since 1.0.0
	 * @access public
	 **/
	public function audio_exists( $id = 0 ) {

		/** Current post ID. */
		if ( ! $id ) {

			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$id = get_the_ID();

			if ( ! $id ) {
				return false;
			}

		}

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		/** Path to post audio file. */
		$audio_file = $upload_basedir . '/speaker/post-' . $id . '.mp3';

		/** True if we have audio. */
		if ( file_exists( $audio_file ) ) {
			return filemtime( $audio_file );
		}

		return false;
	}

	/**
	 * Add player code to page.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function add_player() {

		/** Get Player Position from plugin settings. */
		$position = Settings::get_instance()->options['position'];

		/** Add player before/after Title. */
		if ( in_array( $position, [ 'before-title', 'after-title' ] ) ) {
			add_filter( 'the_title', [ $this, 'add_player_to_title' ] );
		}

		/** Add player before/after Content and Top/Bottom Fixed. */
		if ( in_array( $position, [
			'before-content',
			'after-content',
			'top-fixed',
			'bottom-fixed'
		] ) ) {
			add_filter( 'the_content', [ $this, 'add_player_to_content' ] );
		}

	}

	/**
	 * Add player before/after Title.
	 *
	 * @param $title - Post/Page title.
	 *
	 * @return string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function add_player_to_title( $title ) {

		/** Checks if plugin should work on this page. */
		if ( ! AssignmentsTab::get_instance()->display() ) {
			return $title . '';
		}

		/** Check if we are in the loop and work only with selected post types. */
		if ( in_the_loop() && ! ( is_singular( Settings::get_instance()->options['cpt_support'] ) ) ) {
			return $title;
		}

		/** Run only once. */
		static $already_run = false;
		if ( $already_run === true ) {
			return $title;
		}
		$already_run = true;

		$player = $this->get_player();
		if ( Settings::get_instance()->options['position'] === 'before-title' ) {

			return $player . $title;

		}

		if ( Settings::get_instance()->options['position'] === 'after-title' ) {

			return $title . $player;

		}

		return $title;

	}

	/**
	 * Add player before/after Content and Top/Bottom Fixed.
	 *
	 * @param $content - Post/Page content.
	 *
	 * @return string
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function add_player_to_content( $content ) {

		/** Checks if plugin should work on this page. */
		if ( ! AssignmentsTab::get_instance()->display() ) {
			return $content;
		}

		/** Check if we are in the loop and work only with selected post types. */
		if ( in_the_loop() && ! ( is_singular( Settings::get_instance()->options['cpt_support'] ) ) ) {
			return $content;
		}

		/** Run only Once. */
		static $already_run = false;
		if ( $already_run === true ) {
			return $content;
		}
		$already_run = true;

		$player = $this->get_player();
		if ( Settings::get_instance()->options['position'] === 'before-content' ) {

			return $player . $content;

		}

		if ( in_array( Settings::get_instance()->options['position'], [
			'after-content',
			'top-fixed',
			'bottom-fixed'
		] ) ) {

			return $content . $player;

		}

		return $content;

	}

	/**
	 * Render Player code.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function the_player() {

		/** Show player if we have audio. */
		$f_time = $this->audio_exists();
		if ( ! $f_time ) {
			return;
		}

		/** URL to post audio file. */
		$audio_url = $this->get_audio_url();

		?>
        <div class="mdp-speaker-lite-box <?php echo esc_attr( Settings::get_instance()->options['position'] ); ?> <?php echo esc_attr( Settings::get_instance()->options['style'] ); ?>"
             style="background: <?php echo esc_attr( Settings::get_instance()->options['bgcolor'] ); ?>">
            <div>
				<?php echo do_shortcode( '[audio src="' . $audio_url . '" preload="metadata"]' ); ?>
            </div>
        </div>
        <div class="mdp-speaker-lite-audio-info">
			<?php if ( in_array( Settings::get_instance()->options['link'], [
				'backend',
				'backend-and-frontend'
			] ) ) : ?>
                <span class="dashicons dashicons-download"
                      title="<?php esc_html_e( 'Download audio', 'speaker-lite' ); ?>"></span>
                <a href="<?php echo esc_url( $audio_url ); ?>"
                   download=""><?php esc_html_e( 'Download audio', 'speaker-lite' ); ?></a><br>
			<?php endif; ?>
            <span class="dashicons dashicons-clock"
                  title="<?php esc_html__( 'Date of creation', 'speaker-lite' ) ?>"></span>
			<?php echo gmdate( "F d Y H:i:s", $f_time ); ?>
        </div>
		<?php

	}

	/**
	 * Remove audio on remove post record.
	 *
	 * @param $post_id - The post id that is being deleted.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function before_delete_post( $post_id ) {

		/** If we don't have audio then nothing to delete. */
		if ( ! $this->audio_exists( $post_id ) ) {
			return;
		}

		$this->remove_audio_by_id( $post_id );

	}

	/**
	 * Remove Audio by ID.
	 *
	 * @param $id - The post id from which we delete audio.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function remove_audio_by_id( $id ) {

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		/** Path to post audio file. */
		$audio_file = $upload_basedir . '/speaker/post-' . $id . '.mp3';

		/** Remove audio file. */
		wp_delete_file( $audio_file );

	}

	/**
	 * Ajax Remove Audio action hook here.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function remove_audio() {

		/** Security Check. */
		check_ajax_referer( 'speaker-lite-nonce', 'security' );

		/** Current post ID. */
		$post_id = (int) $_POST['post_id'];

		/** Get path to upload folder. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		/** Path to post audio file. */
		$audio_file = $upload_basedir . '/speaker/post-' . $post_id . '.mp3';

		/** Remove audio file. */
		wp_delete_file( $audio_file );

		echo 'ok';

		wp_die();

	}

	/**
	 * Ajax set Speech template as default.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function set_default_st() {

		/** Security Check. */
		check_ajax_referer( 'speaker-lite-nonce', 'security' );

		/** Get Speech Template ID. */
		$stid = filter_input( INPUT_POST, 'stid' );

		/** Error, no Speech Template ID */
		if ( ! trim( $stid ) ) {

			$return = [
				'success' => false,
				'message' => esc_html__( 'Error: There are no Speech Template ID received.', 'speaker-lite' ),
			];

			wp_send_json( $return );
			wp_die();

		}

		/** Get Post Type. */
		$post_type = filter_input( INPUT_POST, 'postType' );

		/** In this option we store all Speech Templates. */
		$st_opt_name = 'mdp_speaker_lite_speech_templates';

		/** Get all Speech Templates. */
		$st = get_option( $st_opt_name, false );

		/** We haven't any ST. */
		if ( ! $st ) {

			$return = [
				'success' => false,
				'message' => 'Speech Templates not found.',
			];

			wp_send_json( $return );
			wp_die();

		}

		/** For each ST. */
		foreach ( $st as $key => $template ) {

			/** Get current Post Types for ST. */
			$post_types = $st[ $key ]['default'];

			/** Add post_type if we found same id. */
			if ( $template['id'] === $stid && 'content' !== $stid ) {

				/** Add new Post Type to post Types. */
				$post_types[] = $post_type;

				/** Remove post_type from all others ST. */
			} else if ( ( $p_key = array_search( $post_type, $post_types, true ) ) !== false ) {

				unset( $post_types[ $p_key ] );

			}

			/** Remove duplicates from an array. */
			$post_types = array_unique( $post_types );

			/** Set new post types for ST. */
			$st[ $key ]['default'] = $post_types;

		}

		/** Update Speech Templates in option. */
		$updated = update_option( $st_opt_name, $st, false );

		if ( ! $updated ) {

			$return = [
				'success' => false,
				'message' => esc_html__( 'Failed to install Speech Template as Default.', 'speaker-lite' ),
			];

			wp_send_json( $return );
			wp_die();

		}

		$return = [
			'success' => true,
			'message' => esc_html__( 'Speech Template is installed as Default successfully.', 'speaker-lite' ),
		];

		wp_send_json( $return );
		wp_die();

	}

	/**
	 * Return Speech Template data by STID.
	 *
	 * @param $stid
	 *
	 * @return array|false
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function get_st( $stid ) {

		/** In this option we store all Speech Templates. */
		$st_opt_name = 'mdp_speaker_lite_speech_templates';

		/** Get all Speech Templates. */
		$st = get_option( $st_opt_name, false );

		/** We haven't any ST. */
		if ( ! $st ) {
			return false;
		}

		/** Search for existing st. */
		foreach ( $st as $key => $template ) {

			/** Update Speech template if we found same id. */
			if ( $template['id'] === $stid ) {

				return $st[ $key ];

			}

		}

		return false;

	}

	/**
	 * Ajax get Speech template data.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function get_st_ajax() {

		/** Security Check. */
		check_ajax_referer( 'speaker-lite-nonce', 'security' );

		/** Get Speech Template ID. */
		$stid = filter_input( INPUT_POST, 'stid' );

		/** In this option we store all Speech Templates. */
		$st_opt_name = 'mdp_speaker_lite_speech_templates';

		/** Get all Speech Templates. */
		$st = get_option( $st_opt_name, false );

		/** We haven't any ST. */
		if ( ! $st ) {

			$return = [
				'success' => false,
				'message' => 'Speech Templates not found.',
			];

			wp_send_json( $return );
			wp_die();

		}

		/** Search for existing st. */
		foreach ( $st as $key => $template ) {

			/** Update Speech template if we found same id. */
			if ( $template['id'] === $stid ) {

				$return = [
					'success' => true,
					'message' => $st[ $key ],
				];

				wp_send_json( $return );
				wp_die();

				break;

			}

		}

		/** Add new one if not found st with same id. */
		$return = [
			'success' => false,
			'message' => esc_html__( 'Speech Template not found.', 'speaker-lite' )
		];

		wp_send_json( $return );
		wp_die();

	}

	/**
	 * Ajax Create/Update/Delete Speech template.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function process_st() {

		/** Security Check. */
		check_ajax_referer( 'speaker-lite-nonce', 'security' );

		/** Get Speech Template data. */
		$st = filter_input( INPUT_POST, 'st' );

		/** Speech Template JSON to Object. */
		$st = json_decode( $st, true );

		/** Do we delete this Speech Template? */
		$delete = filter_input( INPUT_POST, 'delete', FILTER_VALIDATE_BOOLEAN );

		/** Remove Speech Template, */
		if ( $delete ) {

			if ( $this->delete_st( $st['id'] ) ) {

				$return = [
					'success' => true,
					'message' => esc_html__( 'Speech Template removed successfully.', 'speaker-lite' )
				];

				/** On fail. */
			} else {

				$return = [
					'success' => false,
					'message' => esc_html__( 'Failed to remove Speech Template', 'speaker-lite' )
				];

			}

			wp_send_json( $return );
			wp_die();

		}

		/** Update or create Speech Template. */
		if ( ! $this->update_st( $st ) ) {

			$return = [
				'success' => false,
				'message' => esc_html__( 'Failed to update Speech Template.', 'speaker-lite' )
			];

			wp_send_json( $return );
			wp_die();

		}

		$return = [
			'success' => true,
			'message' => esc_html__( 'Speech Template updated successfully.', 'speaker-lite' )
		];

		wp_send_json( $return );
		wp_die();

	}

	/**
	 * Update existing or create new Speech Template.
	 *
	 * @param $new_st
	 *
	 * @return boolean
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function update_st( $new_st ) {

		/** In this option we store all Speech Templates. */
		$st_opt_name = 'mdp_speaker_lite_speech_templates';

		/** Get all Speech Templates. */
		$st = get_option( $st_opt_name, false );

		/** We haven't any ST. */
		if ( ! $st ) {

			/** Add first one. */
			$st   = [];
			$st[] = $new_st;

			/** Search for existing st. */
		} else {

			$found = false;
			foreach ( $st as $key => $template ) {

				/** Update Speech template if we found same id. */
				if ( $template['id'] === $new_st['id'] ) {

					$found      = true;
					$st[ $key ] = $new_st;

					break;

				}

			}

			/** Add new one if not found st with same id. */
			if ( ! $found ) {
				$st[] = $new_st;
			}

		}

		/** Save Speech Templates in option. */
		$updated = update_option( $st_opt_name, $st, false );
		if ( ! $updated ) {
			return false;
		}

		return true;

	}

	/**
	 * Remove Speech Template by ID.
	 *
	 * @param string $id - Unique id of Speech Template.
	 *
	 * @return boolean
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function delete_st( $id ) {

		/** In this option we store all Speech Templates. */
		$st_opt_name = 'mdp_speaker_lite_speech_templates';

		/** Get all Speech Templates. */
		$st = get_option( $st_opt_name, false );

		/** We haven't any ST, nothing to remove. */
		if ( ! $st ) {
			return true;
		}

		/** Search for existing st. */
		foreach ( $st as $key => $template ) {

			/** Remove Speech template if we found same id. */
			if ( $template['id'] === $id ) {

				unset( $st[ $key ] ); // Remove ST.

				break;

			}

		}

		/** Save Speech Templates in option. */
		$updated = update_option( $st_opt_name, $st, false );
		if ( ! $updated ) {
			return false;
		}

		return true;

	}

	/**
	 * Ajax Create Audio action hook here.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function gspeak() {

		/** Security Check. */
		check_ajax_referer( 'speaker-lite-nonce', 'security' );

		if ( ! ( PHP_VERSION_ID >= 80100 ) ) {
			wp_send_json( [
				'success' => false,
				'message' => esc_html__( 'PHP 8.1 or higher is required.', 'speaker-lite' )
			] );
			wp_die();
		}

		/** Current post ID. */
		$post_id = (int) $_POST['post_id'];

		/** Get Speech Template ID. */
		$stid = filter_input( INPUT_POST, 'stid' );

		/** Create audio version of post. */
		if ( $this->voice_acting( $post_id, $stid ) ) {

			$return = [
				'success' => true,
				'message' => esc_html__( 'Audio Generated Successfully', 'speaker-lite' )
			];

		} else {

			$return = [
				'success' => false,
				'message' => esc_html__( 'An error occurred while generating the audio.', 'speaker-lite' )
			];

		}


		wp_send_json( $return );
		wp_die();

	}

	/**
	 * Let me speak. Create audio version of post.
	 *
	 * @param int $post_id
	 * @param string $stid
	 *
	 * @return boolean
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function voice_acting( $post_id = 0, $stid = 'content' ) {

		if ( 'content' === $stid ) {

			/** Prepare parts for generate audio for whole post content. */
			$parts = $this->content_based_generation( $post_id );

		} else {

			/** Prepare parts for generate audio for post based on Speech Template. */
			$parts = $this->template_based_generation( $post_id, $stid );

			/** On error. */
			if ( ! $parts ) {
				return false;
			}

		}

		/** Create audio file for each part. */
		$audio_parts = [];
		foreach ( $parts as $part ) {

			try {

				/** Convert HTML to temporary audio file. */
				$audio_parts[] = $this->part_speak( $part, $post_id );

			} catch ( ApiException $e ) {

				/** Show error message. */
				echo esc_html__( 'Caught exception: ' ) . $e->getMessage() . "\n";

			}

		}

		/** Combine multiple files to one. */
		$this->glue_audio( $audio_parts, $post_id );

		return true;

	}

	private function clean_content( $post_content ) {

		/** Remove <script>...</script>. */
		$post_content = preg_replace( '/<\s*script.+?<\s*\/\s*script.*?>/si', ' ', $post_content );

		/** Remove <style>...</style>. */
		$post_content = preg_replace( '/<\s*style.+?<\s*\/\s*style.*?>/si', ' ', $post_content );

		/** Trim, replace tabs and extra spaces with single space. */
		$post_content = preg_replace( '/[ ]{2,}|[\t]/', ' ', trim( $post_content ) );

		/** Remove muted elements by class "speaker-lite-mute" or attribute speaker-lite-mute="". */
		$post_content = $this->remove_muted_html( $post_content );

		/** Prepare HTML to splitting. */
		$post_content = XMLHelper::get_instance()->clean_html( $post_content );

		return $post_content;

	}

	/**
	 * Prepare parts for generate audio for whole post content.
	 *
	 * @param int $post_id
	 *
	 * @return array
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function content_based_generation( $post_id ) {

		/**
		 * Get Current post Content.
		 * Many shortcodes do not work in the admin area so we need this trick.
		 * We open frontend page in custom template and parse content.
		 **/
		$post_content = $this->parse_post_content( $post_id, 'speaker-lite' );

		/** Get only content part from full page. */
		$post_content = Helper::get_instance()->get_string_between( $post_content, '<!-- Speaker Content Start -->', '<!-- Speaker Content End -->' );

		/**
		 * Filters the post content before any manipulation.
		 *
		 * @param string $post_content Post content.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$post_content = apply_filters( 'speaker_lite_before_content_manipulations', $post_content, $post_id );

		$post_content = $this->clean_content( $post_content );

		/**
		 * Filters the post content before split to parts by 4500 chars.
		 *
		 * @param string $post_content Post content.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$post_content = apply_filters( 'speaker_lite_before_content_dividing', $post_content, $post_id );

		/** If all content is bigger than the quota. */
		$parts[] = $post_content;

		if ( strlen( $post_content ) > 4995 ) {
			$this->great_divider();
		}

		/**
		 * Filters content parts before voice_divider.
		 *
		 * @param string $parts Post content parts.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$parts = apply_filters( 'speaker_lite_before_voice_divider', $parts, $post_id );

		/** Divide parts by voice. One part voiced by one voice */
		$parts = $this->voice_divider( $parts );

		/**
		 * Filters content parts before adding watermarks.
		 *
		 * @param string $parts Post content parts.
		 * @param int $post_id Post ID.
		 **@since 1.0.0
		 *
		 */
		$parts = apply_filters( 'speaker_lite_before_adding_watermarks', $parts, $post_id );

		/** Add custom text before/after audio. */
		$parts = $this->add_watermark( $parts );

		return $parts;

	}

	/**
	 * Prepare parts for generate audio for post based on Speech Template.
	 *
	 * @param int $post_id
	 * @param string $stid
	 *
	 * @return array|false
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function template_based_generation( $post_id, $stid ) {

		/** Get content of each element. */
		$parts = $this->parse_st_content( $post_id, $stid );

		/** On error. */
		if ( ! $parts ) {
			return false;
		}

		return $parts;

	}

	/**
	 * Main SpeakerCaster Instance.
	 *
	 * Insures that only one instance of SpeakerCaster exists in memory at any one time.
	 *
	 * @static
	 * @return SpeakerCaster
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		/** @noinspection SelfClassReferencingInspection */
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SpeakerCaster ) ) {

			/** @noinspection SelfClassReferencingInspection */
			self::$instance = new SpeakerCaster;

		}

		return self::$instance;

	}

} // End Class SpeakerCaster.
