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

final class Language {

    /**
     * Language name on English and (transcription on this language)
     * World known languages and languages codes for February 2023
     */
    private static $languages = [
        'af'        => 'Afrikaans',
        'af-ZA'     => 'Afrikaans (Afrikaans)',
        'ar'        => 'Arabic',
        'ar-AE'     => 'Arabic (U.A.E.)',
        'ar-BH'     => 'Arabic (Bahrain)',
        'ar-DZ'     => 'Arabic (Algeria)',
        'ar-EG'     => 'Arabic (Egypt)',
        'ar-IQ'     => 'Arabic (Iraq)',
        'ar-JO'     => 'Arabic (Jordan)',
        'ar-KW'     => 'Arabic (Kuwait)',
        'ar-LB'     => 'Arabic (Lebanon)',
        'ar-LY'     => 'Arabic (Libya)',
        'ar-MA'     => 'Arabic (Morocco)',
        'ar-OM'     => 'Arabic (Oman)',
        'ar-QA'     => 'Arabic (Qatar)',
        'ar-SA'     => 'Arabic (Saudi Arabia)',
        'ar-SY'     => 'Arabic (Syria)',
        'ar-TN'     => 'Arabic (Tunisia)',
        'ar-YE'     => 'Arabic (Yemen)',
        'az'        => 'Azeri',
        'az-AZ'     => 'Azeri (Azerbaijan)',
        'ar-XA'     => 'Arabic (العربية)',
        'be'        => 'Belarusian',
        'be-BY'     => 'Belarusian (Belarus)',
        'bg'        => 'Bulgarian (Български)',
        'bg-BG'     => 'Bulgarian (Български)',
        'bn-IN'     => 'Bengali (বাংলা)',
        'bs-BA'     => 'Bosnian (Bosnia and Herzegovina)',
        'ca'        => 'Catalan',
        'ca-ES'     => 'Catalan (Català)',
        'cmn-CN'    => 'Chinese (官话)',
        'cmn-TW'    => 'Taiwanese Mandarin (中文(台灣))',
        'cs'        => 'Czech (Čeština)',
        'cs-CZ'     => 'Czech (Čeština)',
        'cy'        => 'Welsh',
        'cy-GB'     => 'Welsh (United Kingdom)',
        'da'        => 'Danish',
        'da-DK'     => 'Danish (Dansk)',
        'de'        => 'German',
        'de-AT'     => 'German (Austria)',
        'de-CH'     => 'German (Switzerland)',
        'de-DE'     => 'German (Deutsch)',
        'de-LI'     => 'German (Liechtenstein)',
        'de-LU'     => 'German (Luxembourg)',
        'dv'        => 'Divehi',
        'dv-MV'     => 'Divehi (Maldives)',
        'el'        => 'Greek (Ελληνικά)',
        'el-GR'     => 'Greek (Ελληνικά)',
        'en'        => 'English',
        'en-AU'     => 'English (Australian)',
        'en-BZ'     => 'English (Belize)',
        'en-CA'     => 'English (Canada)',
        'en-CB'     => 'English (Caribbean)',
        'en-GB'     => 'English (UK English)',
        'en-IE'     => 'English (Ireland)',
        'en-IN'     => 'English (Indian English)',
        'en-JM'     => 'English (Jamaica)',
        'en-NZ'     => 'English (New Zealand)',
        'en-PH'     => 'English (Republic of the Philippines)',
        'en-TT'     => 'English (Trinidad and Tobago)',
        'en-US'     => 'English (US English)',
        'en-ZA'     => 'English (South Africa)',
        'en-ZW'     => 'English (Zimbabwe)',
        'eo'        => 'Esperanto',
        'es'        => 'Spanish',
        'es-AR'     => 'Spanish (Argentina)',
        'es-BO'     => 'Spanish (Bolivia)',
        'es-CL'     => 'Spanish (Chile)',
        'es-CO'     => 'Spanish (Colombia)',
        'es-CR'     => 'Spanish (Costa Rica)',
        'es-DO'     => 'Spanish (Dominican Republic)',
        'es-EC'     => 'Spanish (Ecuador)',
        'es-ES'     => 'Spanish (Español)',
        'es-GT'     => 'Spanish (Guatemala)',
        'es-HN'     => 'Spanish (Honduras)',
        'es-MX'     => 'Spanish (Mexico)',
        'es-NI'     => 'Spanish (Nicaragua)',
        'es-PA'     => 'Spanish (Panama)',
        'es-PE'     => 'Spanish (Peru)',
        'es-PR'     => 'Spanish (Puerto Rico)',
        'es-PY'     => 'Spanish (Paraguay)',
        'es-SV'     => 'Spanish (El Salvador)',
        'es-US'     => 'US Spanish (Hispanoamericano)',
        'es-UY'     => 'Spanish (Uruguay)',
        'es-VE'     => 'Spanish (Venezuela)',
        'et'        => 'Estonian',
        'et-EE'     => 'Estonian (Estonia)',
        'eu'        => 'Basque (Vasco)',
        'eu-ES'     => 'Basque (Vasco)',
        'fa'        => 'Farsi',
        'fa-IR'     => 'Farsi (Iran)',
        'fi'        => 'Finnish (Suomi)',
        'fi-FI'     => 'Finnish (Suomi)',
        'fo'        => 'Faroese',
        'fo-FO'     => 'Faroese (Faroe Islands)',
        'fr'        => 'French',
        'fr-BE'     => 'French (Belgium)',
        'fr-CA'     => 'Canadian French (Français)',
        'fr-CH'     => 'French (Switzerland)',
        'fr-FR'     => 'French (Français)',
        'fr-LU'     => 'French (Luxembourg)',
        'fr-MC'     => 'French (Principality of Monaco)',
        'gl'        => 'Galician (Galego)',
        'gl-ES'     => 'Galician (Galego)',
        'gu'        => 'Gujarati (ગુજરાતી)',
        'gu-IN'     => 'Gujarati (ગુજરાતી)',
        'he'        => 'Hebrew (עִברִית)',
        'he-IL'     => 'Hebrew (עִברִית)',
        'hi'        => 'Hindi (हिन्दी)',
        'hi-IN'     => 'Hindi (हिन्दी)',
        'hr'        => 'Croatian',
        'hr-BA'     => 'Croatian (Bosnia and Herzegovina)',
        'hr-HR'     => 'Croatian (Croatia)',
        'hu'        => 'Hungarian (Magyar)',
        'hu-HU'     => 'Hungarian (Magyar)',
        'hy'        => 'Armenian',
        'hy-AM'     => 'Armenian (Armenia)',
        'id'        => 'Indonesian',
        'id-ID'     => 'Indonesian (Bahasa Indonesia)',
        'is'        => 'Icelandic (Íslensk)',
        'is-IS'     => 'Icelandic (Íslensk)',
        'it'        => 'Italian',
        'it-CH'     => 'Italian (Switzerland)',
        'it-IT'     => 'Italian (Italiano)',
        'ja'        => 'Japanese',
        'ja-JP'     => 'Japanese (日本語)',
        'ka'        => 'Georgian',
        'ka-GE'     => 'Georgian (Georgia)',
        'kk'        => 'Kazakh',
        'kk-KZ'     => 'Kazakh (Kazakhstan)',
        'kn'        => 'Kannada (ಕನ್ನಡ)',
        'kn-IN'     => 'Kannada (ಕನ್ನಡ)',
        'ko'        => 'Korean (한국어)',
        'ko-KR'     => 'Korean (한국어)',
        'kok'       => 'Konkani',
        'kok-IN'    => 'Konkani (India)',
        'ky'        => 'Kyrgyz',
        'ky-KG'     => 'Kyrgyz (Kyrgyzstan)',
        'lt'        => 'Lithuanian',
        'lt-LT'     => 'Lithuanian (Lithuania)',
        'lv'        => 'Latvian (Latvietis)',
        'lv-LV'     => 'Latvian (Latvietis)',
        'mi'        => 'Maori',
        'mi-NZ'     => 'Maori (New Zealand)',
        'mk'        => 'FYRO Macedonian',
        'mk-MK'     => 'FYRO Macedonian (Former Yugoslav Republic of Macedonia)',
        'ml-IN'     => 'Malayalam (മലയാളം)',
        'mn'        => 'Mongolian',
        'mn-MN'     => 'Mongolian (Mongolia)',
        'mr'        => 'Marathi',
        'mr-IN'     => 'Marathi (India)',
        'ms'        => 'Malay',
        'ms-BN'     => 'Malay (Brunei Darussalam)',
        'ms-MY'     => 'Malay (Malaysia)',
        'mt'        => 'Maltese',
        'mt-MT'     => 'Maltese (Malta)',
        'nb'        => 'Norwegian (Norsk)',
        'nb-NO'     => 'Norwegian (Norsk)',
        'nl'        => 'Dutch',
        'nl-BE'     => 'Dutch (Belgium)',
        'nl-NL'     => 'Dutch (Nederlandse)',
        'nn-NO'     => 'Norwegian (Nynorsk) (Norway)',
        'ns'        => 'Northern Sotho',
        'ns-ZA'     => 'Northern Sotho (South Africa)',
        'pa'        => 'Punjabi',
        'pa-IN'     => 'Punjabi (India)',
        'pl'        => 'Polish',
        'pl-PL'     => 'Polish (Polski)',
        'ps'        => 'Pashto',
        'ps-AR'     => 'Pashto (Afghanistan)',
        'pt'        => 'Portuguese',
        'pt-BR'     => 'Portuguese Brazil (Português)',
        'pt-PT'     => 'Portuguese Portugal (Portugal)',
        'qu'        => 'Quechua',
        'qu-BO'     => 'Quechua (Bolivia)',
        'qu-EC'     => 'Quechua (Ecuador)',
        'qu-PE'     => 'Quechua (Peru)',
        'ro'        => 'Romanian (Română)',
        'ro-RO'     => 'Romanian (Română)',
        'ru'        => 'Russian (Оркский)',
        'ru-RU'     => 'Russian (Оркский)',
        'sa'        => 'Sanskrit',
        'sa-IN'     => 'Sanskrit (India)',
        'se'        => 'Sami (Northern)',
        'se-FI'     => 'Sami (Finland)',
        'se-NO'     => 'Sami (Norway)',
        'se-SE'     => 'Sami (Sweden)',
        'sk'        => 'Slovak',
        'sk-SK'     => 'Slovak (Slovenčina)',
        'sl'        => 'Slovenian',
        'sl-SI'     => 'Slovenian (Slovenia)',
        'sq'        => 'Albanian',
        'sq-AL'     => 'Albanian (Albania)',
        'sr-BA'     => 'Serbian (Bosnia and Herzegovina)',
        'sr-RS'     => 'Serbian (Cрпски)',
        'sr-SP'     => 'Serbian (Serbia and Montenegro)',
        'sv'        => 'Swedish',
        'sv-FI'     => 'Swedish (Finland)',
        'sv-SE'     => 'Swedish (Svenska)',
        'sw'        => 'Swahili',
        'sw-KE'     => 'Swahili (Kenya)',
        'syr'       => 'Syriac',
        'syr-SY'    => 'Syriac (Syria)',
        'ta'        => 'Tamil (தமிழ்)',
        'ta-IN'     => 'Tamil (தமிழ்)',
        'te'        => 'Telugu (తెలుగు)',
        'te-IN'     => 'Telugu (తెలుగు)',
        'th'        => 'Thai (ภาษาไทย)',
        'th-TH'     => 'Thai (ภาษาไทย)',
        'tl'        => 'Tagalog',
        'tl-PH'     => 'Tagalog (Philippines)',
        'fil-PH'    => 'Philippines (Filipino)',
        'tn'        => 'Tswana',
        'tn-ZA'     => 'Tswana (South Africa)',
        'tr'        => 'Turkish',
        'tr-TR'     => 'Turkish (Türkçe)',
        'tt'        => 'Tatar',
        'tt-RU'     => 'Tatar (Russia)',
        'ts'        => 'Tsonga',
        'uk'        => 'Ukrainian (Українська)',
        'uk-UA'     => 'Ukrainian (Українська)',
        'ur'        => 'Urdu',
        'ur-PK'     => 'Urdu (Islamic Republic of Pakistan)',
        'uz'        => 'Uzbek',
        'uz-UZ'     => 'Uzbek (Uzbekistan)',
        'vi'        => 'Vietnamese (Tiếng Việt)',
        'vi-VN'     => 'Vietnamese (Tiếng Việt)',
        'xh'        => 'Xhosa',
        'xh-ZA'     => 'Xhosa (South Africa)',
        'yue-HK'    => 'Yue Chinese',
        'zh'        => 'Chinese',
        'zh-CN'     => 'Chinese (S)',
        'zh-HK'     => 'Chinese (Hong Kong)',
        'zh-MO'     => 'Chinese (Macau)',
        'zh-SG'     => 'Chinese (Singapore)',
        'zh-TW'     => 'Chinese (T)',
        'zu'        => 'Zulu',
        'zu-ZA'     => 'Zulu (South Africa)',
    ];

    /**
     * Get language name by language code
     * @param $lang_code
     * @return false|string
     */
    private static function get_language_name( $lang_code ) {

        if ( ! array_key_exists( $lang_code, self::$languages ) ) {

            return self::get_similar_language_name( $lang_code );

        } else {

            return self::$languages[ $lang_code ];

        }

    }

    /**
     * Get similar language name by language code
     * @param $lang_code
     * @return mixed|string
     */
    private static function get_similar_language_name( $lang_code ) {

        $base_lang = explode( '-', $lang_code );
        $language_name = esc_html__( 'Unknown', 'speaker-lite' );

        foreach ( self::$languages as $code => $name ) {

            if ( $code === $base_lang[0] ) {

                $language_name = $name;
                break;

            }

        }

        return $language_name;

    }

    /**
     * Return language name by code.
     *
     * @param $lang_code - Google language code.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string|false
     *
     **/
    public static function get_lang_by_code( $lang_code ) {

        if ( is_object( $lang_code ) ) {
            $lang_code = $lang_code[0];
        }

        return self::get_language_name( $lang_code );

    }

}
