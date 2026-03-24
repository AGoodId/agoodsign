/**
 * AGoodSign Animations — CSS class triggers + GSAP presets.
 *
 * CSS animations are triggered by adding animation classes.
 * GSAP animations use gsap.timeline() for typewriter and stagger effects.
 */
( function () {
	'use strict';

	const AGoodSignAnimations = {

		/**
		 * Apply entrance animation to a slide element.
		 *
		 * @param {HTMLElement} slideEl  The .agoodsign-slide element.
		 * @param {string}      animation Animation slug.
		 * @param {number}      duration  Slide duration in seconds (for Ken Burns timing).
		 */
		play( slideEl, animation, duration ) {
			this.reset( slideEl );

			switch ( animation ) {
				case 'fade-in':
				case 'slide-up':
				case 'slide-left':
				case 'zoom-in':
					this.playCss( slideEl, animation );
					break;

				case 'ken-burns':
					this.playKenBurns( slideEl, duration );
					break;

				case 'typewriter':
					this.playTypewriter( slideEl );
					break;

				case 'stagger':
					this.playStagger( slideEl );
					break;

				default:
					this.playCss( slideEl, 'fade-in' );
			}
		},

		/**
		 * CSS-based animation: add class to animated elements.
		 */
		playCss( slideEl, animation ) {
			const targets = slideEl.querySelectorAll(
				'.agoodsign-overlay, .agoodsign-split__text, .agoodsign-text-only__content, .agoodsign-title-card__content'
			);

			targets.forEach( el => {
				el.classList.add( 'agoodsign-anim--' + animation );
			} );
		},

		/**
		 * Ken Burns: apply to background image elements.
		 */
		playKenBurns( slideEl, duration ) {
			const bgs = slideEl.querySelectorAll( '.agoodsign-slide__bg' );
			bgs.forEach( bg => {
				bg.style.setProperty( '--agoodsign-duration', duration + 's' );
				bg.classList.add( 'agoodsign-anim--ken-burns' );
			} );

			// Also fade in text.
			this.playCss( slideEl, 'fade-in' );
		},

		/**
		 * GSAP Typewriter: reveal heading text character by character.
		 */
		playTypewriter( slideEl ) {
			if ( typeof gsap === 'undefined' ) {
				this.playCss( slideEl, 'fade-in' );
				return;
			}

			const heading = slideEl.querySelector( '.agoodsign-slide__heading' );
			const body = slideEl.querySelector( '.agoodsign-slide__body' );

			if ( heading ) {
				const text = heading.textContent;
				heading.textContent = '';
				heading.style.opacity = '1';

				const tl = gsap.timeline();

				// Build character spans.
				const chars = text.split( '' ).map( char => {
					const span = document.createElement( 'span' );
					span.textContent = char;
					span.style.opacity = '0';
					heading.appendChild( span );
					return span;
				} );

				tl.to( chars, {
					opacity: 1,
					duration: 0.03,
					stagger: 0.04,
					ease: 'none',
				} );

				if ( body ) {
					body.style.opacity = '0';
					tl.to( body, {
						opacity: 0.9,
						duration: 0.5,
						ease: 'power2.out',
					}, '+=0.2' );
				}
			} else {
				this.playCss( slideEl, 'fade-in' );
			}
		},

		/**
		 * GSAP Stagger: reveal elements one by one with delay.
		 */
		playStagger( slideEl ) {
			if ( typeof gsap === 'undefined' ) {
				this.playCss( slideEl, 'fade-in' );
				return;
			}

			const elements = slideEl.querySelectorAll(
				'.agoodsign-slide__heading, .agoodsign-slide__body, .agoodsign-slide__bg, ' +
				'.agoodsign-title-card__divider, .agoodsign-text-only__divider, ' +
				'.agoodsign-title-card__border-top, .agoodsign-title-card__border-bottom'
			);

			if ( elements.length === 0 ) {
				this.playCss( slideEl, 'fade-in' );
				return;
			}

			gsap.set( elements, { opacity: 0, y: 30 } );

			gsap.to( elements, {
				opacity: 1,
				y: 0,
				duration: 0.5,
				stagger: 0.2,
				ease: 'power2.out',
			} );
		},

		/**
		 * Reset animations on a slide.
		 */
		reset( slideEl ) {
			const animated = slideEl.querySelectorAll( '[class*="agoodsign-anim--"]' );
			animated.forEach( el => {
				el.className = el.className.replace( /agoodsign-anim--[\w-]+/g, '' ).trim();
			} );

			if ( typeof gsap !== 'undefined' ) {
				gsap.killTweensOf( slideEl.querySelectorAll( '*' ) );
			}
		},
	};

	window.AGoodSignAnimations = AGoodSignAnimations;
} )();
