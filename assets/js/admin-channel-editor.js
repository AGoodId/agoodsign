/**
 * AGoodSign Channel Editor — Drag & drop reordering + inline editing.
 */
( function () {
	'use strict';

	var list      = document.getElementById( 'agoodsign-ce-list' );
	var statusEl  = document.getElementById( 'agoodsign-ce-status' );
	var config    = window.agoodsignChannelEditor || {};
	var saveTimer = null;

	if ( ! list ) return;

	// === Drag & Drop ===

	var dragItem    = null;
	var placeholder = null;

	list.addEventListener( 'mousedown', function ( e ) {
		var handle = e.target.closest( '.agoodsign-ce__drag-handle' );
		if ( ! handle ) return;

		var item = handle.closest( '.agoodsign-ce__item' );
		if ( ! item ) return;

		e.preventDefault();
		startDrag( item, e.clientY );
	} );

	function startDrag( item, startY ) {
		dragItem = item;
		var rect = item.getBoundingClientRect();

		// Create placeholder.
		placeholder = document.createElement( 'div' );
		placeholder.className = 'agoodsign-ce__placeholder';
		placeholder.style.height = rect.height + 'px';
		item.parentNode.insertBefore( placeholder, item );

		// Float the dragged item.
		item.classList.add( 'is-dragging' );
		item.style.width = rect.width + 'px';
		item.style.top = rect.top + 'px';
		item.style.left = rect.left + 'px';

		var offsetY = startY - rect.top;

		function onMove( e ) {
			item.style.top = ( e.clientY - offsetY ) + 'px';

			// Find insertion point.
			var items = list.querySelectorAll( '.agoodsign-ce__item:not(.is-dragging)' );
			var insertBefore = null;

			for ( var i = 0; i < items.length; i++ ) {
				var r = items[ i ].getBoundingClientRect();
				if ( e.clientY < r.top + r.height / 2 ) {
					insertBefore = items[ i ];
					break;
				}
			}

			if ( insertBefore ) {
				list.insertBefore( placeholder, insertBefore );
			} else {
				list.appendChild( placeholder );
			}
		}

		function onUp() {
			document.removeEventListener( 'mousemove', onMove );
			document.removeEventListener( 'mouseup', onUp );

			// Place item where placeholder is.
			list.insertBefore( item, placeholder );
			placeholder.remove();
			placeholder = null;

			item.classList.remove( 'is-dragging' );
			item.style.width = '';
			item.style.top = '';
			item.style.left = '';
			dragItem = null;

			saveOrder();
		}

		document.addEventListener( 'mousemove', onMove );
		document.addEventListener( 'mouseup', onUp );
	}

	// === Save Order ===

	function saveOrder() {
		var items = list.querySelectorAll( '.agoodsign-ce__item' );
		var order = [];

		for ( var i = 0; i < items.length; i++ ) {
			order.push( items[ i ].dataset.id );
		}

		showStatus( config.i18n?.saving || 'Saving...', 'saving' );

		var formData = new FormData();
		formData.append( 'action', 'agoodsign_save_channel_order' );
		formData.append( 'nonce', config.nonce );

		for ( var j = 0; j < order.length; j++ ) {
			formData.append( 'order[]', order[ j ] );
		}

		fetch( config.ajaxUrl, {
			method: 'POST',
			body: formData,
		} )
		.then( function ( res ) { return res.json(); } )
		.then( function ( data ) {
			if ( data.success ) {
				showStatus( config.i18n?.saved || 'Saved', 'saved' );
			} else {
				showStatus( config.i18n?.error || 'Error', 'error' );
			}
		} )
		.catch( function () {
			showStatus( config.i18n?.error || 'Error', 'error' );
		} );
	}

	// === Inline Editing ===

	list.addEventListener( 'change', function ( e ) {
		var el = e.target;
		if ( ! el.dataset.id || ! el.dataset.field ) return;

		saveMeta( el.dataset.id, el.dataset.field, el.value );
	} );

	// Debounce number input while typing.
	list.addEventListener( 'input', function ( e ) {
		var el = e.target;
		if ( el.type !== 'number' || ! el.dataset.id || ! el.dataset.field ) return;

		clearTimeout( saveTimer );
		saveTimer = setTimeout( function () {
			saveMeta( el.dataset.id, el.dataset.field, el.value );
		}, 600 );
	} );

	function saveMeta( postId, field, value ) {
		showStatus( config.i18n?.saving || 'Saving...', 'saving' );

		var formData = new FormData();
		formData.append( 'action', 'agoodsign_save_slide_meta' );
		formData.append( 'nonce', config.nonce );
		formData.append( 'post_id', postId );
		formData.append( 'field', field );
		formData.append( 'value', value );

		fetch( config.ajaxUrl, {
			method: 'POST',
			body: formData,
		} )
		.then( function ( res ) { return res.json(); } )
		.then( function ( data ) {
			if ( data.success ) {
				showStatus( config.i18n?.saved || 'Saved', 'saved' );
			} else {
				showStatus( config.i18n?.error || 'Error', 'error' );
			}
		} )
		.catch( function () {
			showStatus( config.i18n?.error || 'Error', 'error' );
		} );
	}

	// === Status Indicator ===

	var statusTimeout = null;

	function showStatus( text, type ) {
		if ( ! statusEl ) return;

		clearTimeout( statusTimeout );
		statusEl.textContent = text;
		statusEl.className = 'agoodsign-ce__status is-' + type;

		if ( type !== 'saving' ) {
			statusTimeout = setTimeout( function () {
				statusEl.className = 'agoodsign-ce__status';
				statusEl.textContent = '';
			}, 2000 );
		}
	}
} )();
