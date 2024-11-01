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

use Google\ApiCore\ApiException;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\ListVoicesRequest;
use Merkulove\SpeakerLite;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

final class TabVoice {

	private static $tts_client;
	private static $voices;
	private static $api_error_message;

	public function __construct() {

		/** Voice Tab. */
		$group_name = 'SpeakerOptionsGroup';
		$section_id = 'mdp_speaker_lite_pluginPage_section';
		register_setting( $group_name, 'mdp_speaker_lite_settings' );
		add_settings_section( $section_id, '', null, $group_name );

		$options = Settings::get_instance()->options;

		/** Render Settings fields. */
		if ( $options['dnd-api-key'] && PHP_VERSION_ID >= 80100 ) {

			add_settings_field(
				'current_language',
				esc_html__( 'Voice example:', 'speaker-lite' ),
				[ $this, 'current_language' ],
				$group_name,
                $section_id
            );

			add_settings_field(
				'language',
				esc_html__( 'Select Language:', 'speaker-lite' ),
				[ $this, 'language' ],
				$group_name,
                $section_id
            );

			add_settings_field(
				'audio_profile',
				esc_html__( 'Audio Profile:', 'speaker-lite' ),
				[ $this, 'audio_profile' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'speaking_rate',
				esc_html__( 'Speaking Speed:', 'speaker-lite' ),
				[ $this, 'speaking_rate' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'pitch',
				esc_html__( 'Pitch:', 'speaker-lite' ),
				[ $this, 'pitch' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'volume',
				esc_html__( 'Volume Gain:', 'speaker-lite' ),
				[ $this, 'volume' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'before_audio',
				esc_html__( 'Before Audio:', 'speaker-lite' ),
				[ $this, 'before_audio' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'after_audio',
				esc_html__( 'After Audio:', 'speaker-lite' ),
				[ $this, 'after_audio' ],
				$group_name,
				$section_id );

			add_settings_field(
				'read_title',
				esc_html__( 'Read the Title:', 'speaker-lite' ),
				[ $this, 'read_title' ],
				$group_name,
				$section_id
			);

			add_settings_field(
				'auto_generation',
				esc_html__( 'Automatic synthesis:', 'speaker-lite' ),
				[ $this, 'auto_generation' ],
				$group_name,
				$section_id
			);


		}

		add_settings_field(
			'dnd_api_key',
			esc_html__( 'API Key File:', 'speaker-lite' ),
			[ $this, 'dnd_api_key' ],
			$group_name,
			$section_id
		);

	}

	/**
	 * Render Current Language
	 */
	public function current_language(): void {

		?>
        <div class="mdp-now-used">
            <div>
                <strong><?php echo esc_attr( Settings::get_instance()->options['language'] ); ?></strong>
            </div>
            <div>
                <audio controls="">
                    <source src="https://cloud.google.com/text-to-speech/docs/audio/<?php echo esc_attr( Settings::get_instance()->options['language'] ); ?>.mp3" type="audio/mp3">
                    <source src="https://cloud.google.com/text-to-speech/docs/audio/<?php echo esc_attr( Settings::get_instance()->options['language'] ); ?>.wav" type="audio/mp3">
					<?php esc_html_e( 'Your browser does not support the audio element.', 'speaker-lite' ); ?>
                </audio>
            </div>
        </div>
		<?php

	}

	/**
	 * Render Language field.
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @since 1.0.0
	 * @access public
	 **/
	public function language(): void {

		self::tts_list_voices();

		/** Prepare Languages Options. */
		$options = [];
		$options[] = esc_html__( 'Select Language', 'speaker-lite' );
		foreach ( self::$voices as $voice ) {

			$lang = Language::get_lang_by_code( $voice->getLanguageCodes() );

			/** Skip missing language. */
			if ( false === $lang ) { continue; }

			$options[$lang] = $lang;

		}
		ksort( $options ); // Sort by language name.

		/** Render Language select. */
		UI::get_instance()->render_select(
			$options,
			'',
			esc_html__('Language', 'speaker-lite' ),
			'',
			[
				'name' => 'mdp_speaker_lite_language_filter',
				'id' => 'mdp-speaker-lite-language-filter'
			]
		);

		?>

        <div class="mdc-text-field-helper-line mdp-speaker-lite-helper-padding">
            <div class="mdc-text-field-helper-text mdc-text-field-helper-text--persistent"><?php esc_html_e( 'The list includes both standard and', 'speaker-lite' ); ?>
                <a href="https://cloud.google.com/text-to-speech/docs/wavenet"
                   target="_blank"><?php esc_html_e( 'WaveNet voices', 'speaker-lite' ); ?></a>.
				<?php esc_html_e( 'WaveNet voices are higher quality voices with different', 'speaker-lite' ); ?>
                <a href="https://cloud.google.com/text-to-speech/pricing"
                   target="_blank"><?php esc_html_e( 'pricing', 'speaker-lite' ); ?></a>;
				<?php esc_html_e( 'in the list, they have the voice type "WaveNet".', 'speaker-lite' ); ?>
            </div>
        </div>

        <table id="mdp-speaker-lite-settings-language-tbl" class="display stripe hidden">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Language', 'speaker-lite' ); ?></th>
                <th><?php esc_html_e( 'Voice', 'speaker-lite' ); ?></th>
                <th><?php esc_html_e( 'Gender', 'speaker-lite' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php

			$rendered_voices = array();
			$table = array();

			foreach ( self::$voices as $voice ) :

				/** Skip already rendered voices. */
				if ( in_array( $voice->getName(), $rendered_voices ) ) {
					continue;
				} else {
					$rendered_voices[] = $voice->getName();
				}

				/** Get language name */
				$lang_name = Language::get_lang_by_code( $voice->getLanguageCodes() );
				if ( false === $lang_name ) { continue; } // Skip missing language

				/** Prepare classes. */
				$class = 'mdp-speaker-lite-voice-type-' . self::get_voice_type( $voice->getName() );
				if ( $voice->getName() === Settings::get_instance()->options['language'] ) {
					$class .= ' selected ';
				}

				$voice_markup = wp_sprintf(
					'<span class="mdp-lang-code" title="%1$s">%1$s</span> - <span>%2$s</span> - <span class="mdp-voice-name" title="%3$s">%4$s</span>',
					esc_html( $voice->getLanguageCodes()[0] ),
					self::get_voice_type( $voice->getName(), false ),
					esc_html( $voice->getName() ),
					esc_html( substr( $voice->getName(), -1 ) )
				);

				$ssmlVoiceGender = [ 'SSML_VOICE_GENDER_UNSPECIFIED', 'Male', 'Female', 'Neutral' ];
				$gender_markup =  wp_sprintf(
					'<span title="%1$s"><img src="%2$s" alt="%1$s">%3$s</span>',
					esc_attr( $ssmlVoiceGender[ $voice->getSsmlGender() ] ),
					SpeakerLite::$url . 'images/' . strtolower( $ssmlVoiceGender[ $voice->getSsmlGender() ] ) . '.svg',
					esc_html( $ssmlVoiceGender[ $voice->getSsmlGender() ] )
				);

				$lang_code = $voice->getLanguageCodes()[0];

				$lang_type = self::get_voice_type( $voice->getName() );
				$lang_type_index = $lang_type === 'standard' ? 0 : 1;

				$table[ $lang_code ][ $lang_type_index ][] = array(
					'lang_name' => $lang_name,
					'voice' => $voice_markup,
					'gender' => $gender_markup,
					'class' => $class
				);

			endforeach;
			ksort( $table ); // Sort by language code

			foreach( $table as $lang ) {

				ksort( $lang );

				foreach ( $lang as $type ) {

					foreach ( $type as $row ) {

						echo wp_sprintf(
							'<tr class="%1$s">
                                <td class="mdp-lang-name">%2$s</td>
                                <td>%3$s</td>
                                <td>%4$s</td>
                            </tr>',
							esc_attr( $row['class'] ),
							esc_html( $row['lang_name'] ),
							$row[ 'voice' ],
							$row[ 'gender' ]
						);

					}

				}

			}

			self::$tts_client->close();

			?>
            </tbody>

        </table>

        <input id="mdp-speaker-lite-settings-language" type='hidden' name='mdp_speaker_lite_settings[language]'
               value='<?php echo esc_attr( Settings::get_instance()->options['language'] ); ?>'>
        <input id="mdp-speaker-lite-settings-language-code" type='hidden' name='mdp_speaker_lite_settings[language-code]'
               value='<?php echo esc_attr( Settings::get_instance()->options['language-code'] ); ?>'>
		<?php

		/** Restore previous exception handler. */
		restore_exception_handler();

	}

	/**
	 * Render Audio Profile field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function audio_profile(): void {

		/** Prepare options for select. */
		$options = [
			'wearable-class-device' => esc_html__( 'Smart watches and other wearables', 'speaker-lite' ),
			'handset-class-device' => esc_html__( 'Smartphones', 'speaker-lite' ),
			'headphone-class-device' => esc_html__( 'Earbuds or headphones', 'speaker-lite' ),
			'small-bluetooth-speaker-lite-class-device' => esc_html__( 'Small home speakers', 'speaker-lite' ),
			'medium-bluetooth-speaker-lite-class-device' => esc_html__( 'Smart home speakers', 'speaker-lite' ),
			'large-home-entertainment-class-device' => esc_html__( 'Home entertainment systems', 'speaker-lite' ),
			'large-automotive-class-device' => esc_html__( 'Car speakers', 'speaker-lite' ),
			'telephony-class-application' => esc_html__( 'Interactive Voice Response', 'speaker-lite' ),
		];

		/** Render select. */
		UI::get_instance()->render_select(
			$options,
			Settings::get_instance()->options['audio-profile'], // Selected option.
			esc_html__( 'Audio Profile', 'speaker-lite' ),
			esc_html__( 'Optimize the synthetic speech for playback on different types of hardware.', 'speaker-lite' ),
			['name' => 'mdp_speaker_lite_settings[audio-profile]']
		);

	}

	/**
	 * Render Speaking Rate/Speed field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function speaking_rate(): void {

		/** Render slider. */
		UI::get_instance()->render_slider(
			Settings::get_instance()->options['speaking-rate'],
			0.25,
			4.0,
			0.25,
			esc_html__( 'Speaking Rate/Speed', 'speaker-lite'),
			esc_html__( 'Speaking rate:', 'speaker-lite') . ' <strong>' . esc_html( Settings::get_instance()->options['speaking-rate'] ) . '</strong><br>',
			[
				'name' => 'mdp_speaker_lite_settings[speaking-rate]',
				'class' => 'mdc-slider-width',
				'id' => 'mdp_speaker_lite_settings_rate'
			],
			false
		);

	}

	/**
	 * Render Pitch field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function pitch(): void {

		/** Render slider. */
		UI::get_instance()->render_slider(
			Settings::get_instance()->options['pitch'],
			-20,
			20,
			0.1,
			esc_html__( 'Pitch', 'speaker-lite'),
			esc_html__( 'Current pitch:', 'speaker-lite') . ' <strong>' . esc_html( Settings::get_instance()->options['pitch'] ) . '</strong>',
			[
				'name' => 'mdp_speaker_lite_settings[pitch]',
				'class' => 'mdc-slider-width',
				'id' => 'mdp_speaker_lite_settings_pitch'
			],
			false
		);

	}

	/**
	 * Render Volume Gain field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function volume(): void {

		/** Render slider. */
		UI::get_instance()->render_slider(
			Settings::get_instance()->options['volume'],
			-10,
			15,
			0.1,
			esc_html__( 'Volume Gain', 'speaker-lite'),
			esc_html__( 'Current volume gain:', 'speaker-lite') .
			' <strong>' . esc_html( Settings::get_instance()->options['volume'] ) . '</strong>' .
			esc_html__( ' dB', 'speaker-lite'),
			[
				'name' => 'mdp_speaker_lite_settings[volume]',
				'class' => 'mdc-slider-width',
				'id' => 'mdp-speaker-lite-settings-volume'
			],
			false
		);

	}

	/**
	 * Render Before Audio field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @noinspection PhpUnused
	 **/
	public function before_audio(): void {

		/** Render input. */
		UI::get_instance()->render_input(
			Settings::get_instance()->options['before_audio'],
			esc_html__( 'Before Audio', 'speaker-lite'),
			esc_html__( 'Add text before audio(intro).', 'speaker-lite' ),
			[
				'name'      => 'mdp_speaker_lite_settings[before_audio]',
				'id'        => 'mdp-speaker-lite-settings-before-audio',
				'maxlength' => '4500',
				'disabled'  => 'disabled',
				'badge'     => esc_html__( 'Upgrade to PRO', 'speaker-lite' ),
			]
		);

	}

	/**
	 * Render After Audio field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 **/
	public function after_audio(): void {

		/** Render input. */
		UI::get_instance()->render_input(
			Settings::get_instance()->options['after_audio'],
			esc_html__( 'After Audio', 'speaker-lite'),
			esc_html__( 'Add a text after audio(outro).', 'speaker-lite' ),
			[
				'name'      => 'mdp_speaker_lite_settings[after_audio]',
				'id'        => 'mdp-speaker-lite-settings-after-audio',
				'maxlength' => '4500',
				'disabled'  => 'disabled',
				'badge'     => esc_html__( 'Upgrade to PRO', 'speaker-lite' ),
			]
		);

	}

	/**
	 * Render Read the Title field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 **/
	public function read_title(): void {

		/** Render Read the Title switcher. */
		UI::get_instance()->render_switches(
			Settings::get_instance()->options['read_title'],
			esc_html__( 'Read the Title', 'speaker-lite' ),
			esc_html__( 'Include title in audio version.', 'speaker-lite' ),
			[
				'name'      => 'mdp_speaker_lite_settings[read_title]',
				'id'        => 'mdp-speaker-lite-settings-read-title',
				'class'     => 'mdc-switch--disabled',
				'disabled'  => 'disabled',
				'badge'     => esc_html__( 'Upgrade to PRO', 'speaker-lite' ),
			]
		);

	}

	/**
	 * Render Auto Generation field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @noinspection PhpUnused
	 **/
	public function auto_generation(): void {

		/** Render Auto Generation switcher. */
		UI::get_instance()->render_switches(
			Settings::get_instance()->options['auto_generation'],
			esc_html__( 'Synthesize audio on save', 'speaker-lite' ),
			esc_html__( 'This significantly increases your expenses in Google Cloud.', 'speaker-lite' ),
			[
				'name' => 'mdp_speaker_lite_settings[auto_generation]',
				'id' => 'mdp_speaker_lite_settings_auto_generation',
				'class'     => 'mdc-switch--disabled',
				'disabled'  => 'disabled',
				'badge'     => esc_html__( 'Upgrade to PRO', 'speaker-lite' ),
			]
		);

	}

	/**
	 * Render Drag & Drop API Key field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function dnd_api_key(): void {

		// Check PHP version
		if (!(PHP_VERSION_ID >= 80100)) {
			self::php_error_message();
		}

		$key_exist = false;
		if ( Settings::get_instance()->options['dnd-api-key'] ) { $key_exist = true; }

		?>
		<div class="mdp-dnd">
			<!--suppress HtmlFormInputWithoutLabel -->
			<div class="mdc-text-field mdc-input-width mdc-text-field--outlined mdc-hidden">
				<!--suppress HtmlFormInputWithoutLabel -->
				<input  type="text"
				        class="mdc-text-field__input"
				        name="mdp_speaker_lite_settings[dnd-api-key]"
				        id="mdp-speaker-lite-settings-dnd-api-key"
				        value="<?php echo esc_attr( Settings::get_instance()->options['dnd-api-key'] ); ?>"
				>
				<div class="mdc-notched-outline mdc-notched-outline--upgraded mdc-notched-outline--notched">
					<div class="mdc-notched-outline__leading"></div>
					<div class="mdc-notched-outline__notch">
						<label for="mdp-speaker-lite-settings-dnd-api-key" class="mdc-floating-label mdc-floating-label--float-above"><?php esc_html_e( 'API Key', 'speaker-lite' ); ?></label>
					</div>
					<div class="mdc-notched-outline__trailing"></div>
				</div>
			</div>
			<div id="mdp-api-key-drop-zone" class="<?php if ( $key_exist ) : ?>mdp-key-uploaded<?php endif; ?>">
				<?php if ( $key_exist ) : ?>
					<span class="material-icons">check_circle_outline</span><?php esc_html_e( 'API Key file exist', 'speaker-lite' ); ?>
					<span class="mdp-drop-zone-hover"><?php esc_html_e( 'Drop Key file here or click to upload', 'speaker-lite' ); ?></span>
				<?php else : ?>
					<span class="material-icons">cloud</span><?php esc_html_e( 'Drop Key file here or click to upload.', 'speaker-lite' ); ?>
				<?php endif; ?>
			</div>
			<?php if ( $key_exist ) : ?>
				<div class="mdp-messages mdc-text-field-helper-line mdc-text-field-helper-text mdc-text-field-helper-text--persistent">
					<?php esc_html_e( 'Drag and drop or click on the form to replace API key. |', 'speaker-lite' ); ?>
					<a href="#" class="mdp-reset-key-btn"><?php esc_html_e( 'Reset API Key', 'speaker-lite' ); ?></a>
				</div>
			<?php else : ?>
				<div class="mdp-messages mdc-text-field-helper-line mdc-text-field-helper-text mdc-text-field-helper-text--persistent">
					<?php esc_html_e( 'Drag and drop or click on the form to add API key.', 'speaker-lite' ); ?>
				</div>
			<?php endif; ?>
			<input id="mdp-dnd-file-input" type="file" name="name" class="mdc-hidden" />
		</div>
		<?php

	}

	public static function tts_list_voices(): bool {

		/** Setting custom exception handler. */
		set_exception_handler( [ ErrorHandler::class, 'exception_handler' ] );

		/** Create client object. */
		self::$tts_client = new TextToSpeechClient();

		/** Get a list of voices from transient */
		if ( self::tts_list_voices_transient() ) {
			return true;
		}

		/** Perform list voices request. */
		try {
			$response = self::$tts_client->listVoices( new ListVoicesRequest() );
		} catch ( ApiException $e ) {
			self::$api_error_message = $e;
			return false;
		} finally {
			self::$tts_client->close();
		}

		/** Perform list voices request. */
		$voices   = $response->getVoices();

		/** Show a warning if it was not possible to get a list of voices. */
		if ( count( $voices ) === 0 ) {

			self::voice_list_error_message();
			return false;

		}

		/** Set transient for 12 hours to reduce request count */
		set_transient( 'speaker_list_voices', $voices, 12 * HOUR_IN_SECONDS );

		self::$voices = $voices;
		return true;

	}

	/**
	 * Error message for a voice list.
	 * @return void
	 */
	private static function voice_list_error_message(): void {

		if ( ! is_admin() ) { return; }

		?><div class="mdp-alert-error"><?php

		esc_html_e( 'Failed to get the list of languages. 
            The request failed. It looks like a problem with your API Key File. 
            Make sure that you are using the correct key file, and that the quotas have not been exceeded. 
            If you set security restrictions on a key, make sure that the current domain is added to the exceptions.', 'speaker-lite' );

		?></div><?php

	}

	/**
	 * Error message for PHP version
	 * @return void
	 */
	private static function php_error_message(): void {

		if ( ! is_admin() ) { return; }

        $message = wp_sprintf( "Plugin requires PHP 8.1 or later. Your current PHP version is %s. To ensure optimal performance and compatibility, please update your PHP version.",
            PHP_VERSION
        );

		?><div class="mdp-alert-error"><?php
		echo esc_html( $message );
        ?><hr><?php
		echo esc_html__( 'Need a PHP 7 alternative?', 'speaker-lite');
		?><br><?php
		echo esc_html__( 'Look for the Speaker PHP7 child-plugin within the', 'speaker-lite')
        ?> <a href="https://1.envato.market/speaker-buy" target="_blank">Speaker PRO</a> <?php
        echo esc_html__( 'package', 'speaker-lite')
		?></div><?php

	}

	/**
	 * Get a list of voices from transient.
	 *
	 * @return bool Returns true if the list of voices is retrieved from transient, false otherwise.
	 */
	private static function tts_list_voices_transient(): bool {

		/** Get a list of voices from transient */
		$transient_list_voices = get_transient( 'speaker_list_voices' );
		if ( $transient_list_voices ) {
			self::$voices = $transient_list_voices;
			return true;
		}

		return false;

	}

	/**
	 * Return Voice Type.
	 *
	 * @param $lang_name - Google voice name.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public static function render_voice_type( $lang_name ): string {

		$wavenet = strpos( $lang_name, 'Wavenet' );
		if ( $wavenet !== false ) {
			return wp_sprintf(
				'<img src="%s" alt="%s">%s',
				SpeakerLite::$url . 'images/wavenet.svg',
				esc_html__( 'WaveNet voice', 'speaker-lite' ),
				esc_html( 'WaveNet' )
			);
		}

		$neural = strpos( $lang_name, 'Neural' );
		if ( $neural !== false ) {
			return wp_sprintf(
				'<img src="%s" alt="%s">%s',
				SpeakerLite::$url . 'images/neural.svg',
				esc_html__( 'Neural2 voice', 'speaker-lite' ),
				esc_html( 'Neural2' )
			);
		}

		$news = strpos( $lang_name, 'News' );
		if ( $news !== false ) {
			return wp_sprintf(
				'<img src="%s" alt="%s">%s',
				SpeakerLite::$url . 'images/news.svg',
				esc_html__( 'News voice', 'speaker-lite' ),
				esc_html( 'News' )
			);
		}

		$studio = strpos( $lang_name, 'Studio' );
		if ( $studio !== false ) {
			return wp_sprintf(
				'<img src="%s" alt="%s">%s',
				SpeakerLite::$url . 'images/studio.svg',
				esc_html__( 'Studio voice', 'speaker-lite' ),
				esc_html( 'Studio' )
			);
		}

		return wp_sprintf(
			'<img src="%s" alt="%s">%s',
			SpeakerLite::$url . 'images/standard.svg',
			esc_html__( 'Standard voice', 'speaker-lite' ),
			esc_html( 'Standard' )
		);

	}

	/**
	 * Return Voice Type.
	 *
	 * @param $lang_name - Google voice name.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public static function get_voice_type( $lang_name, $strtolower = true ): string {

		$parts = explode( '-', $lang_name );

		if ( is_array( $parts ) ) {

			return isset( $parts[ 2 ] ) ?
				$strtolower ?
					strtolower( $parts[ 2 ] ) :
					$parts[ 2 ] :
				'unknown';

		}
		return 'unknown';

	}

	/**
	 * @var TabVoice|null
	 */
	private static ?TabVoice $instance = null;

	/**
	 * Get the instance of this class.
	 * @return TabVoice
	 */
	public static function getInstance(): TabVoice {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
