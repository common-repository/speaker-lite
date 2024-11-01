"use strict";

/**
 * Mdp crawler main object
 * @type {{ crawler: mdpCrawler.crawler, tickerSlider: mdpCrawler.tickerSlider, addCrawler: mdpCrawler.addCrawler }}
 */

const mdpCrawler = {

    crawler: function ( wrapperName ) {
        const tickerType = document.querySelector( `.${wrapperName}` ).dataset.tickerType

        if ( tickerType === 'slider' ) {

            const widescreenSlidesToShow = +document.querySelector( `.${wrapperName} ` ).dataset.slidesToShowWidescreen;
            const laptopSlidesToShow = +document.querySelector( `.${wrapperName} ` ).dataset.slidesToShowLaptop;
            const tabletExtraSlidesToShow = +document.querySelector( `.${wrapperName} ` ).dataset.slidesToShowTabletExtra;
            const tabletSlidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShowTablet;
            const mobileExtraSlidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShowMobileExtra;
            const mobileSlidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShowMobile;

            function setSlidesToShow() {

                let slidesToShow;

                // set responsive slides to show
                if ( elementorFrontend.config.responsive.activeBreakpoints.hasOwnProperty( 'widescreen' )
                    && window.matchMedia( `(min-width: ${elementorFrontend.config.responsive.activeBreakpoints.widescreen.value}px)` ).matches
                    && widescreenSlidesToShow ) {
                    slidesToShow = widescreenSlidesToShow;
                }

                if ( widescreenSlidesToShow && elementorFrontend.config.responsive.activeBreakpoints.hasOwnProperty( 'widescreen' ) ) {
                    if ( window.matchMedia( `(min-width: ${elementorFrontend.config.responsive.activeBreakpoints.tablet.value + 1}px)` ).matches
                        && window.matchMedia( `(max-width: ${elementorFrontend.config.responsive.activeBreakpoints.widescreen.value - 1}px)` ).matches ) {
                        slidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShow;
                    }
                } else {
                    if ( window.matchMedia( `(min-width: ${elementorFrontend.config.responsive.activeBreakpoints.tablet.value + 1}px)` ).matches ) {
                        slidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShow;
                    }
                }

                if ( elementorFrontend.config.responsive.activeBreakpoints.hasOwnProperty( 'laptop' )
                    && window.matchMedia( `(max-width: ${elementorFrontend.config.responsive.activeBreakpoints.laptop.value}px)` ).matches
                    && laptopSlidesToShow ) {
                    slidesToShow = laptopSlidesToShow
                }

                if ( elementorFrontend.config.responsive.activeBreakpoints.hasOwnProperty( 'tablet_extra' )
                    && window.matchMedia( `(max-width: ${elementorFrontend.config.responsive.activeBreakpoints.tablet_extra.value}px) ` ).matches
                    && tabletExtraSlidesToShow ) {
                    slidesToShow = tabletExtraSlidesToShow;
                }

                if ( window.matchMedia( `(max-width: ${elementorFrontend.config.responsive.activeBreakpoints.tablet.value}px)` ).matches && tabletSlidesToShow ) {
                    slidesToShow = tabletSlidesToShow;
                }

                if ( elementorFrontend.config.responsive.activeBreakpoints.hasOwnProperty( 'mobile_extra' )
                    && window.matchMedia( ` (max-width: ${elementorFrontend.config.responsive.activeBreakpoints.mobile_extra.value}px) ` ).matches
                    && mobileExtraSlidesToShow ) {
                    slidesToShow = mobileExtraSlidesToShow;
                }

                if ( window.matchMedia( `(max-width: ${elementorFrontend.config.responsive.activeBreakpoints.mobile.value}px)` ).matches && mobileSlidesToShow ) {
                    slidesToShow = mobileSlidesToShow;
                }

                return slidesToShow;
            }

            setTimeout( () => {
                this.tickerSlider( wrapperName, setSlidesToShow() );
            }, 200 );

            window.addEventListener( 'resize', () => {
                setTimeout( () => {
                    this.tickerSlider( wrapperName, setSlidesToShow() );
                }, 200 );
            } );

        } else {
            setTimeout( () => {
                const tickerWrapperWidth = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-content-wrapper` ).offsetWidth;
                const $tickerWrapper = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-content-wrapper` );
                const containerWidth = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-ticker` ).offsetWidth;
                const root = document.documentElement;

                if ( tickerWrapperWidth < containerWidth ) {
                    $tickerWrapper.style.width = containerWidth + 'px';
                }

                // set css variable for animation keyframes
                root.style.setProperty('--ticker-track-width', containerWidth + "px");

            }, 200 );
        }

    },

    tickerSlider: function ( wrapperName, slidesToShow ) {
        const $sliderWrapper = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-ticker-slider` );
        const $nextArrow = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-next-arrow` );
        const $previousArrow = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-previous-arrow` );
        const $label = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-ticker-label` );
        const slideItems = $sliderWrapper.children;
        const maxPosition = $sliderWrapper.children.length;
        const slidesToScroll = +document.querySelector( `.${wrapperName}` ).dataset.slidesToScroll;
        const slidesTransitionSpeed = +document.querySelector( `.${wrapperName}` ).dataset.slidesTransitionSpeed;
        const sliderAnimation = document.querySelector( `.${wrapperName}` ).dataset.sliderAnimation;
        const slidesDirection = document.querySelector( `.${wrapperName}` ).dataset.slidesDirection;
        const isInfinite = document.querySelector( `.${wrapperName}` ).dataset.infiniteLoop;
        let position = 0,
            posX1,
            posX2,
            threshold = 100,
            allowSwitchLeft = true,
            allowSwitchRight = true,
            startPosition,
            endPosition;

        if ( sliderAnimation === 'slide' ) {

            if ( slidesDirection === 'right' ) {
                $sliderWrapper.style.flexDirection = 'row-reverse';
            }

        // check if responsive slides to show o set set desktop slides to show
        if( !slidesToShow ) {
            slidesToShow = +document.querySelector( `.${wrapperName}` ).dataset.slidesToShow;
        }

            let labelWidth = 0;

            // set content width if label
            if ( $label ) {
                labelWidth = $label.getBoundingClientRect().width.toFixed( 2 );
                const containerWidth = document.querySelector(`.${wrapperName}`).getBoundingClientRect().width.toFixed( 2 ) - labelWidth;

                $sliderWrapper.parentElement.style.width = containerWidth + 'px';

            } else {
                $sliderWrapper.parentElement.style.width = 100 + '%';
            }

            // cloning elements for infinite slider
            function cloneElements() {
                let clonesFirst = [];
                let clonesLast = [];

                let childLength = maxPosition - 1;

                for ( let i = 0; i < slidesToShow; i++ ) {
                    clonesFirst.push( slideItems[i].cloneNode( true ) );
                    clonesLast.push( slideItems[childLength--].cloneNode( true ) );
                }

                for ( let i = 0; i < slidesToShow; i++ ) {
                    $sliderWrapper.appendChild( clonesFirst[i] );
                    $sliderWrapper.insertBefore( clonesLast[i], slideItems[0] );
                }
            }

            if ( isInfinite === 'yes' && $sliderWrapper.children.length >= slidesToShow ) {
                cloneElements();
            }


            // slides to show
            for ( let i = 0; i < $sliderWrapper.children.length; i++ ) {
                slideItems[i].style.flexBasis = 100 / slidesToShow + '%';
                slideItems[i].style.width = 100 / slidesToShow + '%';
            }

            const slideSize = slideItems[0].getBoundingClientRect().width.toFixed( 2 );

            // set initial position
            if ( slidesDirection === 'left' ) {
                $sliderWrapper.style.left = isInfinite === 'yes' && $sliderWrapper.children.length >= slidesToShow ? -slideSize * slidesToShow + 'px' : 0 + 'px';
            } else {
                $sliderWrapper.style.left = isInfinite === 'yes' && $sliderWrapper.children.length >= slidesToShow ? slideSize * slidesToShow + 'px' : 0 + 'px';
            }


            // check boundary position
            $sliderWrapper.addEventListener( 'transitionend', checkBoundaryPosition );

            // mouse events
            $sliderWrapper.onmousedown = dragStart;

            // touch events
            $sliderWrapper.addEventListener( 'touchstart', dragStart );
            $sliderWrapper.addEventListener( 'touchend', dragEnd );
            $sliderWrapper.addEventListener( 'touchmove', dragAction );

            // arrows click events
            if ( $nextArrow || $previousArrow ) {
                $nextArrow.addEventListener( 'click', () => {
                    switchSlides( 1 );
                } );

                $previousArrow.addEventListener( 'click', () => {
                    switchSlides( -1 );
                } );
            }


            function dragStart( e ) {
                e.preventDefault();
                startPosition = parseFloat( window.getComputedStyle( $sliderWrapper ).getPropertyValue('left') );

                if ( e.type === 'touchstart' ) {
                    posX1 = e.touches[0].clientX;
                } else {
                    posX1 = e.clientX;
                    document.onmouseup = dragEnd;
                    document.onmousemove = dragAction;
                }
            }

            function dragAction( e ) {
                if ( e.type === 'touchmove' ) {
                    posX2 = posX1 - e.touches[0].clientX;
                    posX1 = e.touches[0].clientX;
                } else {
                    posX2 = posX1 - e.clientX;
                    posX1 = e.clientX;
                }

                $sliderWrapper.style.left = ( $sliderWrapper.offsetLeft - posX2 ) + 'px';
            }

            function dragEnd( e ) {
                endPosition = $sliderWrapper.offsetLeft;
                if ( endPosition - startPosition < -threshold && $sliderWrapper.children.length >= slidesToShow ) {
                    switchSlides( 1, 'drag' );
                } else if ( endPosition - startPosition > threshold && $sliderWrapper.children.length >= slidesToShow ) {
                    switchSlides( -1, 'drag' );
                } else {
                    $sliderWrapper.style.left = ( startPosition ) + 'px';
                }

                document.onmouseup = null;
                document.onmousemove = null;
            }

            function switchSlides( dir, action ) {
                if ( slidesTransitionSpeed ) $sliderWrapper.style.transition = `all ${slidesTransitionSpeed}s`;

                if ( !action && ( allowSwitchLeft || allowSwitchRight ) ) {
                    startPosition = parseFloat( window.getComputedStyle( $sliderWrapper ).getPropertyValue('left') );
                }

                if ( dir === 1 && allowSwitchRight && $sliderWrapper.children.length >= slidesToShow ) {
                    position += slidesToScroll;
                    $sliderWrapper.style.left = slidesDirection === 'right' ? ( startPosition + slideSize * slidesToScroll ) + 'px' : ( startPosition - slideSize * slidesToScroll ) + 'px';
                    if ( position >= maxPosition - slidesToShow && isInfinite !== 'yes' ) {
                        $sliderWrapper.style.left = slidesDirection === 'right' ? ( slideSize * ( maxPosition - slidesToShow ) ) + 'px' : -( slideSize * ( maxPosition - slidesToShow ) ) + 'px';
                    }
                    allowSwitchRight = false;
                } else if ( dir === -1 && allowSwitchLeft && $sliderWrapper.children.length >= slidesToShow ) {
                    position -= slidesToScroll;
                    $sliderWrapper.style.left = slidesDirection === 'right' ? ( startPosition - slideSize * slidesToScroll ) + 'px' : ( startPosition + slideSize * slidesToScroll ) + 'px';
                    if ( position <= 0 && isInfinite !== 'yes' ) {
                        $sliderWrapper.style.left = 0 + 'px';
                    }
                    allowSwitchLeft = false;
                }

            }

            function checkBoundaryPosition() {
                $sliderWrapper.style.transition = 'none';

                allowSwitchLeft = true;
                allowSwitchRight = true;


                if ( position === 0 && isInfinite !== 'yes' ) {
                    allowSwitchLeft = false;
                } else {
                    if ( position <= -1 ) {
                        position += maxPosition;
                        const offset = slideSize * ( position + slidesToShow );
                        $sliderWrapper.style.left = slidesDirection === 'right' ? offset + 'px' : -offset + 'px';
                    }
                }

                if ( position >= maxPosition - slidesToShow && isInfinite !== 'yes' ) {
                    allowSwitchRight = false;
                } else {
                    if ( position >= maxPosition ) {
                        position -= maxPosition;
                        const offset = slideSize * ( position + slidesToShow );
                        $sliderWrapper.style.left = slidesDirection === 'right' ? offset + 'px' : -offset + 'px';
                    }
                }
            }
        } else {

            // set initial values
            for ( let i = 0; i < maxPosition; i++ ) {
                slideItems[i].style.flexBasis = '100%';
                slideItems[i].style.display = 'none';
                if ( sliderAnimation === 'fade' ) {
                    slideItems[i].style.animation = 'fade_in_show';
                    slideItems[i].style.animationDuration = slidesTransitionSpeed + 's';
                }
            }

            slideItems[0].classList.add( 'mdp-ticker-elementor-ticker-item-active' );

            function switchSlidesFade ( dir ) {
                const $currentElement = document.querySelector( `.${wrapperName} .mdp-ticker-elementor-ticker-item-active` );
                const nextElement = $currentElement.nextElementSibling;
                const previousElement = $currentElement.previousElementSibling;

                if ( dir === 1 ) {
                    if ( !nextElement ) {
                        $currentElement.classList.remove( 'mdp-ticker-elementor-ticker-item-active' );
                        slideItems[isInfinite === 'yes' ? 0 : maxPosition - 1].classList.add( 'mdp-ticker-elementor-ticker-item-active' );
                    } else {
                        $currentElement.classList.remove( 'mdp-ticker-elementor-ticker-item-active' )
                        nextElement.classList.add( 'mdp-ticker-elementor-ticker-item-active' );
                    }
                } else if ( dir === -1 ) {
                    if ( !previousElement ) {
                        $currentElement.classList.remove( 'mdp-ticker-elementor-ticker-item-active' );
                        slideItems[isInfinite === 'yes' ? maxPosition - 1 : 0].classList.add( 'mdp-ticker-elementor-ticker-item-active' );
                    } else {
                        $currentElement.classList.remove( 'mdp-ticker-elementor-ticker-item-active' );
                        previousElement.classList.add( 'mdp-ticker-elementor-ticker-item-active' );
                    }
                }
            }

            // arrows click events
            if ( $nextArrow || $previousArrow ) {
                $nextArrow.addEventListener( 'click', () => {
                    switchSlidesFade( 1 );
                } );

                $previousArrow.addEventListener( 'click', () => {
                    switchSlidesFade( -1 );
                } );
            }

            // drag events
            let startX;
            let isDragging = false;
            $sliderWrapper.addEventListener( 'mousedown', dragStartFade, false );
            $sliderWrapper.addEventListener( 'mouseup', dragEndFade, false );
            $sliderWrapper.addEventListener( 'mouseleave', dragEndFade, false );

            $sliderWrapper.addEventListener( 'touchstart', dragStartFade, false );
            $sliderWrapper.addEventListener( 'touchend', dragEndFade, false );
            $sliderWrapper.addEventListener( 'touchcancel', dragEndFade, false );

            function dragStartFade ( e ) {
                startX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                isDragging = true;
                if ( e.type === 'touchstart' ) {
                    $sliderWrapper.addEventListener( 'touchmove', dragActionFade, false );
                } else {
                    $sliderWrapper.addEventListener( 'mousemove', dragActionFade, false );
                }
            }
            function dragEndFade( e ) {
                isDragging = false;
                $sliderWrapper.removeEventListener( 'mousemove', dragActionFade, false );
                $sliderWrapper.removeEventListener( 'touchmove', dragActionFade, false );
            }

            function dragActionFade( e ) {
                e.preventDefault();
                if ( !isDragging ) return;

                let delta = 'touchmove' === e.type ?
                    Math.round( startX - e.touches[0].clientX ) :
                    startX - e.clientX;

                delta > 0 ? switchSlidesFade( 1 ) : switchSlidesFade( -1 );
                isDragging = false;
            }

        }

    },

    addCrawler: function () {
        const crawlerWrapper = document.querySelectorAll( '.mdp-ticker-elementor-box' );

        for ( let i = 0; i < crawlerWrapper.length; i++ ) {
            crawlerWrapper[i].classList.add( 'mdp-ticker-elementor-box-' + i );
            this.crawler( 'mdp-ticker-elementor-box-' + i );
        }
    }

}


/**
 * Init for Front-End
 * @param callback
 */
document.addEventListener( 'DOMContentLoaded', mdpCrawler.addCrawler.bind( mdpCrawler ) );