/**
 * AGoodSign Screens Admin — Copy URL + Preview Modal.
 */
( function () {
	'use strict';

	/**
	 * Copy URL to clipboard.
	 */
	document.addEventListener( 'click', function ( e ) {
		const btn = e.target.closest( '.agoodsign-copy-url' );
		if ( ! btn ) return;

		const url = btn.dataset.url;
		if ( ! url ) return;

		navigator.clipboard.writeText( url ).then( function () {
			const icon = btn.querySelector( '.dashicons' );
			if ( icon ) {
				icon.classList.remove( 'dashicons-clipboard' );
				icon.classList.add( 'dashicons-yes' );
				setTimeout( function () {
					icon.classList.remove( 'dashicons-yes' );
					icon.classList.add( 'dashicons-clipboard' );
				}, 1500 );
			}
		} );
	} );

	/**
	 * Preview modal.
	 */
	document.addEventListener( 'click', function ( e ) {
		const btn = e.target.closest( '.agoodsign-preview-btn' );
		if ( ! btn ) return;

		const url  = btn.dataset.url;
		const name = btn.dataset.name || 'Preview';

		openPreviewModal( url, name );
	} );

	function openPreviewModal( url, title ) {
		// Remove existing modal.
		const existing = document.getElementById( 'agoodsign-preview-modal' );
		if ( existing ) existing.remove();

		const modal = document.createElement( 'div' );
		modal.id = 'agoodsign-preview-modal';
		modal.style.cssText = 'position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,0.9);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px';

		// Title bar.
		const titleBar = document.createElement( 'div' );
		titleBar.style.cssText = 'color:#fff;font-size:16px;font-family:-apple-system,BlinkMacSystemFont,sans-serif;display:flex;align-items:center;gap:16px';
		titleBar.innerHTML = '<span>' + escHtml( title ) + '</span>';

		const closeBtn = document.createElement( 'button' );
		closeBtn.textContent = '\u00D7 Close';
		closeBtn.style.cssText = 'background:#333;border:1px solid #555;color:#fff;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:14px';
		closeBtn.onclick = function () { modal.remove(); };
		titleBar.appendChild( closeBtn );

		// iframe container — simulate a screen.
		const container = document.createElement( 'div' );
		container.style.cssText = 'border-radius:8px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.6);position:relative';

		const iframe = document.createElement( 'iframe' );
		iframe.src = url;
		iframe.style.cssText = 'border:none;width:1080px;height:1920px;transform-origin:top left';

		// Calculate scale to fit viewport.
		const maxH = window.innerHeight * 0.82;
		const maxW = window.innerWidth * 0.8;
		const scale = Math.min( maxH / 1920, maxW / 1080 );

		iframe.style.transform = 'scale(' + scale + ')';
		container.style.width = ( 1080 * scale ) + 'px';
		container.style.height = ( 1920 * scale ) + 'px';

		container.appendChild( iframe );
		modal.appendChild( titleBar );
		modal.appendChild( container );
		document.body.appendChild( modal );

		// Close on Escape.
		function onEscape( e ) {
			if ( e.key === 'Escape' ) {
				modal.remove();
				document.removeEventListener( 'keydown', onEscape );
			}
		}
		document.addEventListener( 'keydown', onEscape );
	}

	function escHtml( str ) {
		const div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}
} )();
