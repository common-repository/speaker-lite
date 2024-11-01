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

use Exception;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class for handler errors with extra features.
 *
 * @since 1.0.0
 * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 **/
final class ErrorHandler {

	/**
	 * Custom error handler function.
	 *
	 * @param  int          $err_no   Error number. (can be a PHP Error level constant)
	 * @param  string       $err_str  Error description.
	 * @param  string|false $err_file File in which the error occurs.
	 * @param  int|false    $err_line Line number where the error is situated.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @example:
	 * <code>
	 * // Set custom error handler.
	 * set_error_handler( [ErrorHandler::class, 'error_handler'] );
	 *
	 * ...
	 *
	 * // Trigger custom error.
	 * trigger_error("A custom error has been triggered" );
	 *
	 * ...
	 *
	 * // Restores previous error handler.
	 * restore_error_handler();
	 * </code>
	 **/
	public static function error_handler( $err_no, $err_str, $err_file = false, $err_line = false ) {

		/** Render "Error" message. */
		UI::get_instance()->render_snackbar(
			esc_html__( 'Error number: ', 'speaker-lite' ) . $err_no . '. ' .
			$err_str . esc_html__( ' in ', 'speaker-lite' ) . $err_file .
			esc_html__( ' on line ', 'speaker-lite' ) . $err_line,
			'error', // Type
			-1, // Timeout
			true // Is Closable
		);

	}

	/**
	 * Custom error handler function.
	 *
	 * @param  Exception $exception
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @example:
	 * <code>
	 * // Set custom exception handler.
	 * set_exception_handler( [ErrorHandler::class, 'exception_handler'] );
	 *
	 * ...
	 *
	 * // Restores previous exception handler.
	 * restore_exception_handler();
	 * </code>
	 **/
	public static function exception_handler( $exception ) {

		$msg = $exception->getMessage();
		$file = $exception->getFile();
		$line = $exception->getLine();

		?>
        <div class="mdp-key-error">
            <?php
                esc_html_e( 'Error: ', 'speaker-lite' );
                echo esc_html( $msg );
                echo "<br>";
                esc_html_e( 'In: ', 'speaker-lite' );
                echo esc_html( $file );
                esc_html_e( ' on line ', 'speaker-lite' );
                echo esc_html( $line );
		    ?>
        </div>
        <p>
            <?php esc_html_e( 'If you think the error is caused by an invalid key file then ', 'speaker-lite' ); ?>
            <a href="/wp-admin/admin.php?page=mdp_speaker_lite_settings&tab=voice&reset-api-key=1"><?php esc_attr_e( 'Reset API Key', 'speaker-lite' ); ?></a>
        </p>
        <?php

	}


} // End Class ErrorHandler.
