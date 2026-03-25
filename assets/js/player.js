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
	let paused       = false;

	// Control elements.
	const prevBtn    = document.getElementById( 'agoodsign-prev' );
	const nextBtn    = document.getElementById( 'agoodsign-next' );
	const pauseBtn   = document.getElementById( 'agoodsign-pause' );
	const counter    = document.getElementById( 'agoodsign-counter' );

	/**
	 * Initialize: play animation on first slide and schedule next.
	 */
	function init() {
		// Show controls permanently in preview mode.
		if ( data.isPreview && player ) {
			player.classList.add( 'is-preview' );
		}

		playAnimation( currentIndex );
		updateCounter();
		scheduleNext();

		// Pause when tab is hidden, resume when visible.
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				clearTimeout( timer );
			} else if ( ! paused ) {
				scheduleNext();
			}
		} );

		// Navigation buttons.
		if ( prevBtn ) prevBtn.addEventListener( 'click', goToPrev );
		if ( nextBtn ) nextBtn.addEventListener( 'click', function () { goToNext(); } );
		if ( pauseBtn ) pauseBtn.addEventListener( 'click', togglePause );

		// External control via postMessage (used by preview modal).
		window.addEventListener( 'message', function ( e ) {
			if ( ! e.data || e.data.source !== 'agoodsign' ) return;
			switch ( e.data.action ) {
				case 'prev':
					goToPrev();
					break;
				case 'next':
					goToNext();
					break;
				case 'toggle-pause':
					togglePause();
					break;
			}
		} );

		// Keyboard navigation.
		document.addEventListener( 'keydown', function ( e ) {
			switch ( e.key ) {
				case 'ArrowLeft':
					goToPrev();
					break;
				case 'ArrowRight':
					goToNext();
					break;
				case ' ':
					e.preventDefault();
					togglePause();
					break;
			}
		} );
	}

	/**
	 * Schedule transition to next slide.
	 */
	function scheduleNext() {
		clearTimeout( timer );

		if ( paused ) return;

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
			timer = setTimeout( function () { goToNext(); }, duration );
		}
	}

	/**
	 * Go to a specific slide by index.
	 */
	function goTo( newIndex ) {
		if ( newIndex === currentIndex ) return;

		// Deactivate previous.
		wrappers[ currentIndex ].classList.remove( 'is-active' );

		currentIndex = newIndex;

		// Activate next.
		wrappers[ currentIndex ].classList.add( 'is-active' );

		// Play animation.
		playAnimation( currentIndex );

		// Update counter.
		updateCounter();

		// Preload next image/video.
		preloadNext();

		// Reschedule.
		scheduleNext();
	}

	/**
	 * Transition to next slide.
	 */
	function goToNext() {
		goTo( ( currentIndex + 1 ) % wrappers.length );
	}

	/**
	 * Transition to previous slide.
	 */
	function goToPrev() {
		goTo( ( currentIndex - 1 + wrappers.length ) % wrappers.length );
	}

	/**
	 * Toggle pause/play.
	 */
	function togglePause() {
		paused = ! paused;

		if ( pauseBtn ) {
			if ( paused ) {
				pauseBtn.innerHTML = '&#9654;';
				pauseBtn.classList.add( 'is-paused' );
			} else {
				pauseBtn.innerHTML = '&#10074;&#10074;';
				pauseBtn.classList.remove( 'is-paused' );
			}
		}

		if ( paused ) {
			clearTimeout( timer );
		} else {
			scheduleNext();
		}

		// Notify parent window of pause state.
		if ( window.parent !== window ) {
			window.parent.postMessage( {
				source: 'agoodsign',
				action: 'pause-update',
				paused: paused,
			}, '*' );
		}
	}

	/**
	 * Update the slide counter display.
	 */
	function updateCounter() {
		if ( counter ) {
			counter.textContent = ( currentIndex + 1 ) + ' / ' + wrappers.length;
		}
		// Notify parent window (preview modal) of slide change.
		if ( window.parent !== window ) {
			window.parent.postMessage( {
				source: 'agoodsign',
				action: 'counter-update',
				current: currentIndex + 1,
				total: wrappers.length,
				paused: paused,
			}, '*' );
		}
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
