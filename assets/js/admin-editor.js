/**
 * AGoodSign Slide Editor — Alpine.js component.
 *
 * Drives the two-column editor with live preview via srcdoc iframe.
 */
document.addEventListener( 'alpine:init', () => {
	Alpine.data( 'agoodsignSlideEditor', ( initialData ) => ( {
		slide: {
			template: initialData.template || 'fullscreen-image',
			heading: initialData.heading || '',
			body_text: initialData.body_text || '',
			image_url: initialData.image_url || '',
			image_id: initialData.image_id || 0,
			video_url: initialData.video_url || '',
			video_fit: initialData.video_fit || 'cover',
			animation: initialData.animation || 'fade-in',
			duration: initialData.duration || 10,
			bg_color: initialData.bg_color || '#000000',
			overlay_position: initialData.overlay_position || 'bottom',
			overlay_color: initialData.overlay_color || '#000000',
			overlay_opacity: initialData.overlay_opacity ?? 0.6,
			split_image_side: initialData.split_image_side || 'left',
			image_focus_x: initialData.image_focus_x ?? 50,
			image_focus_y: initialData.image_focus_y ?? 50,
			text_color: initialData.text_color || '#ffffff',
			pin_enabled: initialData.pin_enabled || false,
			pin_icon: initialData.pin_icon || 'map-pin',
			pin_x: initialData.pin_x ?? 50,
			pin_y: initialData.pin_y ?? 50,
			pin_color: initialData.pin_color || '#ef4444',
			pin_size: initialData.pin_size || 48,
			pin_label: initialData.pin_label || '',
			pin_animation: initialData.pin_animation || 'pulse',
			image_size: initialData.image_size || 60,
			image_position: initialData.image_position || 'top',
			image_radius: initialData.image_radius || 0,
		},

		// Pin icon search state.
		pinIconSearch: '',
		pinIconDropdownOpen: false,

		get filteredIcons() {
			if ( ! window.AGoodSignIcons ) return [];
			return window.AGoodSignIcons.search( this.pinIconSearch );
		},

		selectPinIcon( name ) {
			this.slide.pin_icon = name;
			this.pinIconSearch = '';
			this.pinIconDropdownOpen = false;
		},

		getPinIconSvg( name, size, color ) {
			if ( ! window.AGoodSignIcons ) return '';
			return window.AGoodSignIcons.render( name || 'map-pin', size || 24, color || '#ef4444' );
		},

		/**
		 * Which templates need which fields.
		 */
		templateFields: {
			'fullscreen-image': [ 'image', 'overlay' ],
			'split':            [ 'image', 'bg_color' ],
			'text-only':        [ 'bg_color' ],
			'video':            [ 'video', 'overlay' ],
			'title-card':       [ 'image', 'bg_color' ],
			'image-text':       [ 'image', 'bg_color' ],
		},

		get needsImage() {
			const fields = this.templateFields[ this.slide.template ] || [];
			return fields.includes( 'image' );
		},

		get needsVideo() {
			return this.slide.template === 'video';
		},

		get needsBgColor() {
			const fields = this.templateFields[ this.slide.template ] || [];
			return fields.includes( 'bg_color' );
		},

		get overlayRgba() {
			const hex = this.slide.overlay_color || '#000000';
			const r = parseInt( hex.slice( 1, 3 ), 16 );
			const g = parseInt( hex.slice( 3, 5 ), 16 );
			const b = parseInt( hex.slice( 5, 7 ), 16 );
			return `rgba(${r},${g},${b},${this.slide.overlay_opacity})`;
		},

		get needsOverlay() {
			const fields = this.templateFields[ this.slide.template ] || [];
			return fields.includes( 'overlay' );
		},

		/**
		 * Generate preview HTML for the iframe srcdoc.
		 */
		get previewHtml() {
			const s = this.slide;
			const pluginUrl = window.agoodsignEditor?.pluginUrl || '';
			const fontHeading = 'var(--agoodsign-font-heading, sans-serif)';
			const fontBody = 'var(--agoodsign-font-body, sans-serif)';

			let slideHtml = '';

			switch ( s.template ) {
				case 'fullscreen-image':
					slideHtml = this.buildFullscreenImage( s );
					break;
				case 'split':
					slideHtml = this.buildSplit( s );
					break;
				case 'text-only':
					slideHtml = this.buildTextOnly( s );
					break;
				case 'video':
					slideHtml = this.buildVideo( s );
					break;
				case 'title-card':
					slideHtml = this.buildTitleCard( s );
					break;
				case 'image-text':
					slideHtml = this.buildImageText( s );
					break;
				default:
					slideHtml = this.buildFullscreenImage( s );
			}

			return `<!DOCTYPE html>
<html>
<head>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
	width: 1080px;
	height: 1920px;
	overflow: hidden;
	background: #000;
	font-family: ${fontBody};
	color: ${this.escHtml( s.text_color ) || '#fff'};
}
.slide {
	position: relative;
	width: 100%;
	height: 100%;
	overflow: hidden;
}
.slide__bg {
	position: absolute;
	inset: 0;
	background-size: cover;
	background-position: ${s.image_focus_x}% ${s.image_focus_y}%;
}
.overlay {
	position: absolute;
	left: 0;
	right: 0;
	padding: 60px 50px;
	background: linear-gradient(transparent, ${this.overlayRgba});
	z-index: 2;
}
.overlay--top {
	top: 0;
	bottom: auto;
	background: linear-gradient(${this.overlayRgba}, transparent);
}
.overlay--center {
	top: 50%;
	transform: translateY(-50%);
	background: ${this.overlayRgba};
}
.overlay--bottom { bottom: 0; }
.heading {
	font-family: ${fontHeading};
	font-size: 72px;
	font-weight: 700;
	line-height: 1.15;
	margin-bottom: 20px;
}
.body-text {
	font-size: 40px;
	line-height: 1.4;
	opacity: 0.9;
}

/* Split */
.split {
	display: flex;
	height: 100%;
}
.split--image-right { flex-direction: row-reverse; }
.split__image, .split__text {
	width: 50%;
	height: 100%;
}
.split__image { position: relative; }
.split__text {
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding: 60px 50px;
}

/* Text only */
.text-only {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	text-align: center;
	padding: 80px;
}
.text-only__divider {
	width: 80px;
	height: 4px;
	background: rgba(255,255,255,0.4);
	margin: 30px auto;
}

/* Title card */
.title-card {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	text-align: center;
	position: relative;
}
.title-card__bg-darken::after {
	content: '';
	position: absolute;
	inset: 0;
	background: rgba(0,0,0,0.5);
}
.title-card__content {
	position: relative;
	z-index: 2;
	padding: 80px;
}
.title-card__heading {
	font-size: 96px;
}
.title-card__divider {
	width: 120px;
	height: 4px;
	background: rgba(255,255,255,0.6);
	margin: 40px auto;
}
.title-card__subtitle {
	font-size: 44px;
	opacity: 0.85;
}
.title-card__border-top,
.title-card__border-bottom {
	width: 200px;
	height: 2px;
	background: rgba(255,255,255,0.2);
	margin: 30px auto;
}

/* Video — native element */
.video-el {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	border: 0;
}
.video-el--cover { object-fit: cover; }
.video-el--contain { object-fit: contain; background: #000; }

/* Video — iframe embed (cover mode: scale up to fill) */
.video-embed {
	position: absolute;
	border: 0;
}
.video-embed--contain {
	inset: 0;
	width: 100%;
	height: 100%;
}
.video-embed--cover {
	top: 50%;
	left: 50%;
	width: 350%;
	height: 200%;
	transform: translate(-50%, -50%);
}
.video-placeholder {
	position: absolute;
	inset: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #111;
	color: #555;
	font-size: 48px;
}

/* Pin / Marker */
.pin {
	position: absolute;
	z-index: 10;
	transform: translate(-50%, -50%);
	display: flex;
	flex-direction: column;
	align-items: center;
	pointer-events: none;
}
.pin__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
}
.pin__label {
	margin-top: 8px;
	padding: 6px 16px;
	background: rgba(0,0,0,0.7);
	border-radius: 20px;
	font-size: 24px;
	font-weight: 600;
	white-space: nowrap;
	color: #fff;
}

/* Pin animations */
.pin--pulse .pin__icon {
	animation: pin-pulse 2s ease-in-out infinite;
}
@keyframes pin-pulse {
	0%, 100% { transform: scale(1); }
	50% { transform: scale(1.15); }
}
.pin--pulse::before {
	content: '';
	position: absolute;
	width: var(--pin-size);
	height: var(--pin-size);
	border-radius: 50%;
	animation: pin-ring 2s ease-out infinite;
}
@keyframes pin-ring {
	0% { box-shadow: 0 0 0 0 var(--pin-color); opacity: 0.6; }
	100% { box-shadow: 0 0 0 30px var(--pin-color); opacity: 0; }
}

.pin--bounce .pin__icon {
	animation: pin-bounce 1s ease-in-out infinite;
}
@keyframes pin-bounce {
	0%, 100% { transform: translateY(0); }
	50% { transform: translateY(-16px); }
}

.pin--glow .pin__icon {
	animation: pin-glow 2s ease-in-out infinite;
}
@keyframes pin-glow {
	0%, 100% { filter: drop-shadow(0 0 8px var(--pin-color)) drop-shadow(0 2px 8px rgba(0,0,0,0.4)); }
	50% { filter: drop-shadow(0 0 24px var(--pin-color)) drop-shadow(0 0 48px var(--pin-color)) drop-shadow(0 2px 8px rgba(0,0,0,0.4)); }
}

.pin--none .pin__icon {
	filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
}

/* Image + Text */
.image-text {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	padding: 60px;
	gap: 40px;
	position: relative;
}
.image-text--image-bottom { flex-direction: column-reverse; }
.image-text__image { flex-shrink: 0; }
.image-text__image img {
	display: block;
	width: 100%;
	height: auto;
	object-fit: cover;
}
.image-text__text {
	text-align: center;
	flex-shrink: 0;
}
</style>
</head>
<body>${slideHtml}</body>
</html>`;
		},

		buildFullscreenImage( s ) {
			const bg = s.image_url ? `<div class="slide__bg" style="background-image:url('${this.escHtml( s.image_url )}')"></div>` : '';
			return `<div class="slide">${bg}${this.buildOverlay( s )}${this.buildPin( s )}</div>`;
		},

		buildSplit( s ) {
			const dir = s.split_image_side === 'right' ? ' split--image-right' : '';
			const bg = s.image_url ? `<div class="slide__bg" style="background-image:url('${this.escHtml( s.image_url )}')"></div>` : '';
			return `<div class="split${dir}" style="position:relative">
				<div class="split__image">${bg}</div>
				<div class="split__text" style="background-color:${this.escHtml( s.bg_color )}">
					${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
					${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
				</div>
				${this.buildPin( s )}
			</div>`;
		},

		buildTextOnly( s ) {
			return `<div class="text-only" style="background-color:${this.escHtml( s.bg_color )};position:relative">
				<div>
					${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
					<div class="text-only__divider"></div>
					${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
				</div>
				${this.buildPin( s )}
			</div>`;
		},

		buildVideo( s ) {
			let videoEl = '<div class="video-placeholder">&#9654; Video Preview</div>';
			const fit = s.video_fit || 'cover';

			if ( s.video_url ) {
				const ytMatch = s.video_url.match( /(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/ );
				const vmMatch = s.video_url.match( /vimeo\.com\/([0-9]+)/ );

				if ( ytMatch ) {
					videoEl = `<iframe class="video-embed video-embed--${fit}" src="https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0&mute=1&controls=0&showinfo=0" allow="autoplay"></iframe>`;
				} else if ( vmMatch ) {
					videoEl = `<iframe class="video-embed video-embed--${fit}" src="https://player.vimeo.com/video/${vmMatch[1]}?muted=1&background=1" allow="autoplay"></iframe>`;
				} else {
					videoEl = `<video class="video-el video-el--${fit}" src="${this.escHtml( s.video_url )}" muted playsinline></video>`;
				}
			}

			return `<div class="slide">${videoEl}${this.buildOverlay( s )}${this.buildPin( s )}</div>`;
		},

		buildTitleCard( s ) {
			const bg = s.image_url
				? `<div class="slide__bg title-card__bg-darken" style="background-image:url('${this.escHtml( s.image_url )}')"></div>`
				: '';
			const bgStyle = ! s.image_url ? `background-color:${this.escHtml( s.bg_color )}` : '';

			return `<div class="title-card" style="${bgStyle}">
				${bg}
				<div class="title-card__content">
					<div class="title-card__border-top"></div>
					${s.heading ? `<h2 class="heading title-card__heading">${this.escHtml( s.heading )}</h2>` : ''}
					<div class="title-card__divider"></div>
					${s.body_text ? `<p class="body-text title-card__subtitle">${this.escHtml( s.body_text )}</p>` : ''}
					<div class="title-card__border-bottom"></div>
				</div>
				${this.buildPin( s )}
			</div>`;
		},

		buildImageText( s ) {
			const size = s.image_size || 60;
			const pos = s.image_position || 'top';
			const radius = s.image_radius || 0;
			const dirClass = pos === 'bottom' ? ' image-text--image-bottom' : '';

			const img = s.image_url
				? `<div class="image-text__image" style="width:${size}%">
					<img src="${this.escHtml( s.image_url )}"
						style="${radius ? 'border-radius:' + radius + 'px;' : ''}object-position:${s.image_focus_x}% ${s.image_focus_y}%"
						alt="">
				</div>`
				: '';

			const text = ( s.heading || s.body_text )
				? `<div class="image-text__text">
					${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
					${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
				</div>`
				: '';

			return `<div class="image-text${dirClass}" style="background-color:${this.escHtml( s.bg_color )};position:relative">
				${img}${text}${this.buildPin( s )}
			</div>`;
		},

		buildOverlay( s ) {
			if ( ! s.heading && ! s.body_text ) return '';
			const pos = s.overlay_position || 'bottom';
			return `<div class="overlay overlay--${pos}">
				${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
				${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
			</div>`;
		},

		buildPin( s ) {
			if ( ! s.pin_enabled ) return '';
			const svg = this.getPinIconSvg( s.pin_icon, s.pin_size, s.pin_color );
			const label = s.pin_label ? `<span class="pin__label">${this.escHtml( s.pin_label )}</span>` : '';
			return `<div class="pin pin--${this.escHtml( s.pin_animation )}" style="left:${s.pin_x}%;top:${s.pin_y}%;--pin-color:${this.escHtml( s.pin_color )};--pin-size:${s.pin_size}px">
				<div class="pin__icon">${svg}</div>
				${label}
			</div>`;
		},

		escHtml( str ) {
			if ( ! str ) return '';
			const div = document.createElement( 'div' );
			div.textContent = str;
			return div.innerHTML;
		},

		/**
		 * Open WordPress Media Library to select an image.
		 */
		selectImage() {
			const frame = wp.media( {
				title: 'Select Slide Image',
				multiple: false,
				library: { type: 'image' },
			} );

			frame.on( 'select', () => {
				const attachment = frame.state().get( 'selection' ).first().toJSON();
				this.slide.image_url = attachment.url;
				this.slide.image_id = attachment.id;
			} );

			frame.open();
		},

		removeImage() {
			this.slide.image_url = '';
			this.slide.image_id = 0;
		},

		setFocalPoint( event ) {
			const rect = event.currentTarget.getBoundingClientRect();
			this.slide.image_focus_x = Math.round( ( event.clientX - rect.left ) / rect.width * 100 );
			this.slide.image_focus_y = Math.round( ( event.clientY - rect.top ) / rect.height * 100 );
		},
	} ) );
} );
