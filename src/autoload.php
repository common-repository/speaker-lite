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

/** Register plugin custom autoloader. */
/** @noinspection PhpUnhandledExceptionInspection */
spl_autoload_register( static function ($class ) {

	$namespace = 'Merkulove\\';

	/** Bail if the class is not in our namespace. */
	if ( 0 !== strpos( $class, $namespace ) ) {
		return;
	}

	/** Build the filename. */
	$file = realpath( __DIR__ );
	$file .= DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

	/** If the file exists for the class name, load it. */
	if ( file_exists( $file ) ) {

		/** @noinspection PhpIncludeInspection */
        include_once( $file );

	}

} );