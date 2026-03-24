/**
 * AGoodSign Player — Slide rotation with per-slide timing and animations.
 */
( function () {
	'use strict';

	const data      = window.agoodsignPlayerData || {};
	const slides    = data.slides || [];
	const player    = document.getElementById( 'agoodsign-player' );
	const wrappers  = player ? player.querySelectorAll( '.agoodsign-slide-wrapper' ) : [];

	if ( ! wrappers.length ) return;

	let currentIndex = 0;
	let timer        = null;

	/**
	 * Initialize: play animation on first slide and schedule next.
	 */
	function init() {
		playAnimation( currentIndex );
		scheduleNext();

		// Pause when tab is hidden, resume when visible.
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				clearTimeout( timer );
			} else {
				scheduleNext();
			}
		} );
	}

	/**
	 * Schedule transition to next slide.
	 */
	function scheduleNext() {
		clearTimeout( timer );

		const slideData = slides[ currentIndex ];
		const duration  = ( slideData?.duration || 10 ) * 1000;

		// For video slides, listen for video end.
		const activeWrapper = wrappers[ currentIndex ];
		const video = activeWrapper?.querySelector( 'video' );

		if ( video ) {
			const onVideoEnd = function () {
				video.removeEventListener( 'ended', onVideoEnd );
				clearTimeout( timer );
				goToNext();
			};
			video.addEventListener( 'ended', onVideoEnd );

			// Also set timer as fallback.
			timer = setTimeout( function () {
				video.removeEventListener( 'ended', onVideoEnd );
				goToNext();
			}, duration );
		} else {
			timer = setTimeout( goToNext, duration );
		}
	}

	/**
	 * Transition to next slide.
	 */
	function goToNext() {
		const prevIndex = currentIndex;
		currentIndex = ( currentIndex + 1 ) % wrappers.length;

		// Deactivate previous.
		wrappers[ prevIndex ].classList.remove( 'is-active' );

		// Activate next.
		wrappers[ currentIndex ].classList.add( 'is-active' );

		// Play animation.
		playAnimation( currentIndex );

		// Preload next image/video.
		preloadNext();

		// Schedule.
		scheduleNext();
	}

	/**
	 * Play entrance animation on a slide.
	 *
	 * @param {number} index Slide index.
	 */
	function playAnimation( index ) {
		const slideData = slides[ index ];
		const wrapper   = wrappers[ index ];
		const slideEl   = wrapper?.querySelector( '.agoodsign-slide' );

		if ( slideEl && window.AGoodSignAnimations ) {
			window.AGoodSignAnimations.play(
				slideEl,
				slideData?.animation || 'fade-in',
				slideData?.duration || 10
			);
		}
	}

	/**
	 * Preload the next slide's media.
	 */
	function preloadNext() {
		const nextIndex   = ( currentIndex + 1 ) % wrappers.length;
		const nextWrapper = wrappers[ nextIndex ];

		if ( ! nextWrapper ) return;

		// Preload background images.
		const bgEls = nextWrapper.querySelectorAll( '.agoodsign-slide__bg' );
		bgEls.forEach( function ( el ) {
			const bgImage = el.style.backgroundImage;
			const match   = bgImage?.match( /url\(['"]?(.*?)['"]?\)/ );
			if ( match && match[1] ) {
				const img = new Image();
				img.src = match[1];
			}
		} );

		// Preload video.
		const video = nextWrapper.querySelector( 'video' );
		if ( video ) {
			video.preload = 'auto';
		}
	}

	// Start.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
