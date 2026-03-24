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
			animation: initialData.animation || 'fade-in',
			duration: initialData.duration || 10,
			bg_color: initialData.bg_color || '#000000',
			overlay_position: initialData.overlay_position || 'bottom',
			split_image_side: initialData.split_image_side || 'left',
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
	color: #fff;
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
	background-position: center;
}
.overlay {
	position: absolute;
	left: 0;
	right: 0;
	padding: 60px 50px;
	background: linear-gradient(transparent, rgba(0,0,0,0.75));
	z-index: 2;
}
.overlay--top {
	top: 0;
	bottom: auto;
	background: linear-gradient(rgba(0,0,0,0.75), transparent);
}
.overlay--center {
	top: 50%;
	transform: translateY(-50%);
	background: rgba(0,0,0,0.5);
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

/* Video */
.video-embed, .video-el {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	border: 0;
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
</style>
</head>
<body>${slideHtml}</body>
</html>`;
		},

		buildFullscreenImage( s ) {
			const bg = s.image_url ? `<div class="slide__bg" style="background-image:url('${this.escHtml( s.image_url )}')"></div>` : '';
			return `<div class="slide">${bg}${this.buildOverlay( s )}</div>`;
		},

		buildSplit( s ) {
			const dir = s.split_image_side === 'right' ? ' split--image-right' : '';
			const bg = s.image_url ? `<div class="slide__bg" style="background-image:url('${this.escHtml( s.image_url )}')"></div>` : '';
			return `<div class="split${dir}">
				<div class="split__image">${bg}</div>
				<div class="split__text" style="background-color:${this.escHtml( s.bg_color )}">
					${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
					${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
				</div>
			</div>`;
		},

		buildTextOnly( s ) {
			return `<div class="text-only" style="background-color:${this.escHtml( s.bg_color )}">
				<div>
					${s.heading ? `<h2 class="heading">${this.escHtml( s.heading )}</h2>` : ''}
					<div class="text-only__divider"></div>
					${s.body_text ? `<p class="body-text">${this.escHtml( s.body_text )}</p>` : ''}
				</div>
			</div>`;
		},

		buildVideo( s ) {
			let videoEl = '<div class="video-placeholder">&#9654; Video Preview</div>';

			if ( s.video_url ) {
				const ytMatch = s.video_url.match( /(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/ );
				const vmMatch = s.video_url.match( /vimeo\.com\/([0-9]+)/ );

				if ( ytMatch ) {
					videoEl = `<iframe class="video-embed" src="https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0&mute=1&controls=0&showinfo=0" allow="autoplay"></iframe>`;
				} else if ( vmMatch ) {
					videoEl = `<iframe class="video-embed" src="https://player.vimeo.com/video/${vmMatch[1]}?muted=1&background=1" allow="autoplay"></iframe>`;
				} else {
					videoEl = `<video class="video-el" src="${this.escHtml( s.video_url )}" muted playsinline></video>`;
				}
			}

			return `<div class="slide">${videoEl}${this.buildOverlay( s )}</div>`;
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
	} ) );
} );
