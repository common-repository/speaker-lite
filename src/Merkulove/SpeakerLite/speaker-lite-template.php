<?php
/**
 * Template Name: Speaker Template
 * File: speaker-lite-template.php
 **/

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

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

use Merkulove\SpeakerLite\Settings;

get_header();

?><!-- Speaker Content Start --><?php
if ( have_posts() ) {

    while ( have_posts() ) {

        the_post();

        /** Include title in audio version? */
        $options = Settings::get_instance()->options;
        if ( 'on' === $options['read_title'] ) {
            ?><h1><?php the_title(); ?></h1><break time="1s"></break><?php
        }

        the_content();

    }

}
?><!-- Speaker Content End --><?php

get_footer();
