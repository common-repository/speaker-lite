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

( function ( $ ) {
    
    "use strict";
    
    jQuery( document ).ready( function () {
        
        jQuery( '.mdp-rating-stars' ).find( 'a' ).on( 'hover', function() {
            jQuery( this ).nextAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-filled' ).addClass( 'dashicons-star-empty' );
            jQuery( this ).prevAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
            jQuery( this ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
        } );
        
    } );

} ( jQuery ) );
