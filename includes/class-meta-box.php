<?php
/**
 * Slide editor meta box with Alpine.js and live preview.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Meta_Box {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_signage_slide', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the meta box.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'agoodsign_slide_editor',
			__( 'Slide Settings', 'agoodsign' ),
			array( __CLASS__, 'render' ),
			'signage_slide',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue admin assets for the slide editor.
	 *
	 * @param string $hook_suffix Admin page hook suffix.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		global $post_type;

		if ( 'signage_slide' !== $post_type || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();

		// Load our scripts FIRST so alpine:init listener is registered
		// before Alpine auto-initializes.
		wp_enqueue_script(
			'agoodsign-lucide-icons',
			AGOODSIGN_PLUGIN_URL . 'assets/js/lucide-icons.js',
			array(),
			AGOODSIGN_VERSION,
			true
		);

		wp_enqueue_script(
			'agoodsign-admin-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/js/admin-editor.js',
			array( 'agoodsign-lucide-icons', 'wp-i18n' ),
			AGOODSIGN_VERSION,
			true
		);

		// Alpine loads LAST — depends on our editor script to guarantee order.
		wp_enqueue_script(
			'alpinejs',
			AGOODSIGN_PLUGIN_URL . 'assets/js/vendor/alpine.min.js',
			array( 'agoodsign-admin-editor' ),
			'3.14.8',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_style(
			'agoodsign-admin-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/css/admin-editor.css',
			array(),
			AGOODSIGN_VERSION
		);

		$templates    = AGoodSign_Templates::get_templates();
		$animations   = AGoodSign_Templates::get_animations();
		$font_heading = get_option( 'agoodsign_font_heading', '' );
		$font_body    = get_option( 'agoodsign_font_body', '' );
		$custom_fonts = get_option( 'agoodsign_custom_fonts', array() );

		// Build @font-face CSS string for custom fonts so the preview iframe can load them.
		$custom_font_css = '';
		$fmt_map = array( 'woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype' );
		foreach ( $custom_fonts as $font ) {
			$ext       = strtolower( pathinfo( $font['url'], PATHINFO_EXTENSION ) );
			$fmt       = isset( $fmt_map[ $ext ] ) ? $fmt_map[ $ext ] : 'woff2';
			$base_name = AGoodSign_Fonts::get_base_family( $font['name'] );
			$name_lc   = strtolower( $font['name'] );
			$weight    = '400';
			$style     = 'normal';
			if ( false !== strpos( $name_lc, 'bold' ) ) { $weight = '700'; }
			if ( false !== strpos( $name_lc, 'italic' ) ) { $style = 'italic'; }
			$custom_font_css .= "@font-face{font-family:'" . esc_attr( $base_name ) . "';src:url('" . esc_url_raw( $font['url'] ) . "') format('" . $fmt . "');font-weight:{$weight};font-style:{$style};font-display:swap;}";
		}

		// Build Google Fonts URL for fonts not in custom list.
		$custom_names = array();
		foreach ( $custom_fonts as $f ) {
			$custom_names[] = AGoodSign_Fonts::get_base_family( $f['name'] );
		}
		$custom_names = array_unique( $custom_names );
		$google_families = array();
		if ( $font_heading && ! in_array( $font_heading, $custom_names, true ) ) {
			$google_families[] = str_replace( ' ', '+', $font_heading ) . ':wght@400;700';
		}
		if ( $font_body && ! in_array( $font_body, $custom_names, true ) && $font_body !== $font_heading ) {
			$google_families[] = str_replace( ' ', '+', $font_body ) . ':wght@400;700';
		}
		$google_fonts_url = ! empty( $google_families )
			? 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $google_families ) . '&display=swap'
			: '';

		wp_localize_script( 'agoodsign-admin-editor', 'agoodsignEditor', array(
			'templates'      => $templates,
			'animations'     => $animations,
			'previewUrl'     => add_query_arg(
				array(
					'agoodsign_preview' => 1,
					'post_id'           => get_the_ID(),
				),
				home_url( '/' )
			),
			'pluginUrl'      => AGOODSIGN_PLUGIN_URL,
			'fontHeading'    => $font_heading,
			'fontBody'       => $font_body,
			'customFontCss'  => $custom_font_css,
			'googleFontsUrl' => $google_fonts_url,
		) );
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'agoodsign_save_slide', 'agoodsign_nonce' );

		$slide = AGoodSign_Templates::get_slide_data( $post->ID );

		$templates  = AGoodSign_Templates::get_templates();
		$animations = AGoodSign_Templates::get_animations();

		// Debug: show saved meta value and template file status.
		$raw_template  = get_post_meta( $post->ID, '_agoodsign_template', true );
		$valid_tpls    = array_keys( AGoodSign_Templates::get_templates() );
		$resolved_slug = in_array( $raw_template, $valid_tpls, true ) ? $raw_template : 'fullscreen-image';
		$tpl_file      = AGOODSIGN_PLUGIN_DIR . 'templates/slides/' . $resolved_slug . '.php';
		?>
		<div style="background:#fff3cd;border:1px solid #ffc107;padding:8px 12px;margin-bottom:12px;font-size:13px;border-radius:4px">
			<strong>Debug:</strong>
			Saved template in DB: <code><?php echo esc_html( $raw_template ?: '(empty)' ); ?></code> |
			File exists: <code><?php echo file_exists( $tpl_file ) ? 'YES' : 'NO — ' . esc_html( $tpl_file ); ?></code> |
			Plugin version: <code><?php echo esc_html( AGOODSIGN_VERSION ); ?></code>
		</div>
		<div x-data="agoodsignSlideEditor(<?php echo esc_attr( wp_json_encode( $slide ) ); ?>)"
			class="agoodsign-editor">

			<!-- Two-column layout -->
			<div class="agoodsign-editor__layout">

				<!-- Left column: Fields -->
				<div class="agoodsign-editor__fields">

					<!-- Template selector -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Template', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__templates">
							<?php foreach ( $templates as $slug => $tpl ) : ?>
								<button type="button"
									class="agoodsign-editor__template-btn"
									:class="{ 'is-active': slide.template === '<?php echo esc_attr( $slug ); ?>' }"
									@click="slide.template = '<?php echo esc_attr( $slug ); ?>'"
									title="<?php echo esc_attr( $tpl['description'] ); ?>">
									<span class="dashicons dashicons-<?php echo esc_attr( $tpl['icon'] ); ?>"></span>
									<span class="agoodsign-editor__template-name"><?php echo esc_html( $tpl['name'] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Heading -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-heading"><?php esc_html_e( 'Heading', 'agoodsign' ); ?></label>
						<input type="text"
							id="agoodsign-heading"
							x-model="slide.heading"
							class="agoodsign-editor__input widefat"
							placeholder="<?php esc_attr_e( 'Enter heading...', 'agoodsign' ); ?>">
					</div>

					<!-- Text alignment -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Text Alignment', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__btn-group">
							<button type="button"
								class="agoodsign-editor__btn-group-item"
								:class="{ 'is-active': slide.text_align === 'left' }"
								@click="slide.text_align = 'left'"
								title="<?php esc_attr_e( 'Left', 'agoodsign' ); ?>">
								<span class="dashicons dashicons-editor-alignleft"></span>
							</button>
							<button type="button"
								class="agoodsign-editor__btn-group-item"
								:class="{ 'is-active': slide.text_align === 'center' }"
								@click="slide.text_align = 'center'"
								title="<?php esc_attr_e( 'Center', 'agoodsign' ); ?>">
								<span class="dashicons dashicons-editor-aligncenter"></span>
							</button>
							<button type="button"
								class="agoodsign-editor__btn-group-item"
								:class="{ 'is-active': slide.text_align === 'right' }"
								@click="slide.text_align = 'right'"
								title="<?php esc_attr_e( 'Right', 'agoodsign' ); ?>">
								<span class="dashicons dashicons-editor-alignright"></span>
							</button>
						</div>
					</div>

					<!-- Font sizes -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label">
							<?php esc_html_e( 'Heading Size', 'agoodsign' ); ?>: <span x-text="slide.heading_size ? slide.heading_size + 'px' : '<?php esc_attr_e( 'Default', 'agoodsign' ); ?>'"></span>
						</label>
						<div class="agoodsign-editor__range-row">
							<input type="range" x-model.number="slide.heading_size" min="0" max="120" step="2" style="flex:1">
							<button type="button" class="button button-small" @click="slide.heading_size = 0" title="<?php esc_attr_e( 'Reset to default', 'agoodsign' ); ?>">&times;</button>
						</div>
					</div>

					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label">
							<?php esc_html_e( 'Body Text Size', 'agoodsign' ); ?>: <span x-text="slide.body_size ? slide.body_size + 'px' : '<?php esc_attr_e( 'Default', 'agoodsign' ); ?>'"></span>
						</label>
						<div class="agoodsign-editor__range-row">
							<input type="range" x-model.number="slide.body_size" min="0" max="80" step="2" style="flex:1">
							<button type="button" class="button button-small" @click="slide.body_size = 0" title="<?php esc_attr_e( 'Reset to default', 'agoodsign' ); ?>">&times;</button>
						</div>
					</div>

					<!-- Body text with toolbar -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-body"><?php esc_html_e( 'Body text', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__toolbar">
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="wrapSelection('agoodsign-body', 'strong')" title="<?php esc_attr_e( 'Bold', 'agoodsign' ); ?>">
								<strong>B</strong>
							</button>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="wrapSelection('agoodsign-body', 'em')" title="<?php esc_attr_e( 'Italic', 'agoodsign' ); ?>">
								<em>I</em>
							</button>
							<span class="agoodsign-editor__toolbar-sep"></span>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="insertAtCursor('agoodsign-body', '&lt;br&gt;')" title="<?php esc_attr_e( 'Line break', 'agoodsign' ); ?>">
								&#8629;
							</button>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="insertAtCursor('agoodsign-body', '—')" title="<?php esc_attr_e( 'Em dash', 'agoodsign' ); ?>">
								—
							</button>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="insertAtCursor('agoodsign-body', '–')" title="<?php esc_attr_e( 'En dash', 'agoodsign' ); ?>">
								–
							</button>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="insertAtCursor('agoodsign-body', '°')" title="<?php esc_attr_e( 'Degree', 'agoodsign' ); ?>">
								°
							</button>
							<button type="button" class="agoodsign-editor__toolbar-btn" @click="insertAtCursor('agoodsign-body', '•')" title="<?php esc_attr_e( 'Bullet', 'agoodsign' ); ?>">
								•
							</button>
						</div>
						<textarea
							id="agoodsign-body"
							x-model="slide.body_text"
							class="agoodsign-editor__textarea widefat"
							rows="4"
							placeholder="<?php esc_attr_e( 'Enter body text...', 'agoodsign' ); ?>"></textarea>
					</div>

					<!-- Image (shown for templates that need it) -->
					<div class="agoodsign-editor__section" x-show="needsImage" x-transition>
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Image', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__image-picker">
							<div class="agoodsign-editor__image-preview" x-show="slide.image_url">
								<img :src="slide.image_url" alt=""
									:style="'object-position:' + slide.image_focus_x + '% ' + slide.image_focus_y + '%'">
								<div class="agoodsign-editor__focal-point"
									@click="setFocalPoint($event)"
									:title="'<?php esc_attr_e( 'Click to set focal point', 'agoodsign' ); ?>'">
									<div class="agoodsign-editor__focal-point-marker"
										:style="'left:' + slide.image_focus_x + '%;top:' + slide.image_focus_y + '%'">
									</div>
								</div>
								<button type="button" class="agoodsign-editor__image-remove" @click="removeImage()">
									<span class="dashicons dashicons-no-alt"></span>
								</button>
							</div>
							<button type="button"
								class="button agoodsign-editor__image-btn"
								@click="selectImage()">
								<span class="dashicons dashicons-format-image"></span>
								<span x-text="slide.image_url ? '<?php esc_attr_e( 'Change Image', 'agoodsign' ); ?>' : '<?php esc_attr_e( 'Select Image', 'agoodsign' ); ?>'"></span>
							</button>
						</div>
					</div>

					<!-- Video URL (shown for video template) -->
					<div class="agoodsign-editor__section" x-show="needsVideo" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-video"><?php esc_html_e( 'Video URL', 'agoodsign' ); ?></label>
						<input type="url"
							id="agoodsign-video"
							x-model="slide.video_url"
							class="agoodsign-editor__input widefat"
							placeholder="<?php esc_attr_e( 'https://example.com/video.mp4 or YouTube/Vimeo URL', 'agoodsign' ); ?>">
					</div>

					<!-- Video fit (shown for video template) -->
					<div class="agoodsign-editor__section" x-show="needsVideo" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-video-fit"><?php esc_html_e( 'Video Display', 'agoodsign' ); ?></label>
						<select id="agoodsign-video-fit" x-model="slide.video_fit" class="agoodsign-editor__select">
							<option value="cover"><?php esc_html_e( 'Full bleed (crop to fill)', 'agoodsign' ); ?></option>
							<option value="contain"><?php esc_html_e( 'Fit (show entire video)', 'agoodsign' ); ?></option>
						</select>
					</div>

					<!-- Background color (shown for templates that need it) -->
					<div class="agoodsign-editor__section" x-show="needsBgColor" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-bg-color"><?php esc_html_e( 'Background Color', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__color-row">
							<input type="color"
								id="agoodsign-bg-color"
								x-model="slide.bg_color"
								class="agoodsign-editor__color-input">
							<input type="text"
								x-model="slide.bg_color"
								class="agoodsign-editor__color-text"
								maxlength="7"
								pattern="#[0-9a-fA-F]{6}">
						</div>
					</div>

					<!-- Text color -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-text-color"><?php esc_html_e( 'Text Color', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__color-row">
							<input type="color"
								id="agoodsign-text-color"
								x-model="slide.text_color"
								class="agoodsign-editor__color-input">
							<input type="text"
								x-model="slide.text_color"
								class="agoodsign-editor__color-text"
								maxlength="7"
								pattern="#[0-9a-fA-F]{6}">
						</div>
					</div>

					<!-- Overlay position -->
					<div class="agoodsign-editor__section" x-show="needsOverlay" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-overlay"><?php esc_html_e( 'Text Position', 'agoodsign' ); ?></label>
						<select id="agoodsign-overlay" x-model="slide.overlay_position" class="agoodsign-editor__select">
							<option value="top"><?php esc_html_e( 'Top', 'agoodsign' ); ?></option>
							<option value="center"><?php esc_html_e( 'Center', 'agoodsign' ); ?></option>
							<option value="bottom"><?php esc_html_e( 'Bottom', 'agoodsign' ); ?></option>
						</select>
					</div>

					<!-- Overlay style -->
					<div class="agoodsign-editor__section" x-show="needsOverlay" x-transition>
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Overlay', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__pin-row" style="flex-wrap:wrap;gap:12px">
							<div>
								<label class="agoodsign-editor__label" style="font-weight:normal"><?php esc_html_e( 'Color', 'agoodsign' ); ?></label>
								<div class="agoodsign-editor__color-row">
									<input type="color" x-model="slide.overlay_color" class="agoodsign-editor__color-input">
									<input type="text" x-model="slide.overlay_color" class="agoodsign-editor__color-text" maxlength="7">
								</div>
							</div>
							<div style="flex:1;min-width:120px">
								<label class="agoodsign-editor__label" style="font-weight:normal">
									<?php esc_html_e( 'Opacity', 'agoodsign' ); ?>: <span x-text="Math.round(slide.overlay_opacity * 100)"></span>%
								</label>
								<input type="range" x-model.number="slide.overlay_opacity" min="0" max="1" step="0.05" style="width:100%">
							</div>
						</div>
					</div>

					<!-- Image + Text settings -->
					<div class="agoodsign-editor__section" x-show="slide.template === 'image-text'" x-transition>
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Image Layout', 'agoodsign' ); ?></label>
						<select x-model="slide.image_position" class="agoodsign-editor__select">
							<option value="top"><?php esc_html_e( 'Image above text', 'agoodsign' ); ?></option>
							<option value="bottom"><?php esc_html_e( 'Image below text', 'agoodsign' ); ?></option>
							<option value="center"><?php esc_html_e( 'Image centered, text below', 'agoodsign' ); ?></option>
						</select>
					</div>

					<div class="agoodsign-editor__section" x-show="slide.template === 'image-text'" x-transition>
						<label class="agoodsign-editor__label">
							<?php esc_html_e( 'Image Size', 'agoodsign' ); ?>: <span x-text="slide.image_size"></span>%
						</label>
						<input type="range" x-model.number="slide.image_size" min="20" max="100" step="5" style="width:100%">
					</div>

					<div class="agoodsign-editor__section" x-show="slide.template === 'image-text'" x-transition>
						<label class="agoodsign-editor__label">
							<?php esc_html_e( 'Border Radius', 'agoodsign' ); ?>: <span x-text="slide.image_radius"></span>px
						</label>
						<input type="range" x-model.number="slide.image_radius" min="0" max="60" step="2" style="width:100%">
					</div>

					<!-- Split image side -->
					<div class="agoodsign-editor__section" x-show="slide.template === 'split'" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-split-side"><?php esc_html_e( 'Image Side', 'agoodsign' ); ?></label>
						<select id="agoodsign-split-side" x-model="slide.split_image_side" class="agoodsign-editor__select">
							<option value="left"><?php esc_html_e( 'Left', 'agoodsign' ); ?></option>
							<option value="right"><?php esc_html_e( 'Right', 'agoodsign' ); ?></option>
						</select>
					</div>

					<!-- Animation -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-animation"><?php esc_html_e( 'Animation', 'agoodsign' ); ?></label>
						<select id="agoodsign-animation" x-model="slide.animation" class="agoodsign-editor__select">
							<?php foreach ( $animations as $slug => $anim ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $anim['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Duration -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-duration"><?php esc_html_e( 'Duration (seconds)', 'agoodsign' ); ?></label>
						<input type="number"
							id="agoodsign-duration"
							x-model.number="slide.duration"
							class="agoodsign-editor__input agoodsign-editor__input--small"
							min="1"
							max="300"
							step="1">
					</div>

					<!-- Pin / Marker -->
					<div class="agoodsign-editor__section agoodsign-editor__pin-section">
						<div class="agoodsign-editor__pin-header">
							<label class="agoodsign-editor__label">
								<input type="checkbox" x-model="slide.pin_enabled">
								<?php esc_html_e( 'Pin / Marker', 'agoodsign' ); ?>
							</label>
						</div>

						<div x-show="slide.pin_enabled" x-transition class="agoodsign-editor__pin-fields">

							<!-- Icon search -->
							<div class="agoodsign-editor__section">
								<label class="agoodsign-editor__label"><?php esc_html_e( 'Icon', 'agoodsign' ); ?></label>
								<div class="agoodsign-editor__icon-picker" @click.outside="pinIconDropdownOpen = false">
									<div class="agoodsign-editor__icon-current" @click="pinIconDropdownOpen = !pinIconDropdownOpen">
										<span x-html="getPinIconSvg(slide.pin_icon, 20, slide.pin_color)"></span>
										<span x-text="slide.pin_icon"></span>
										<span class="dashicons dashicons-arrow-down-alt2" style="font-size:14px;width:14px;height:14px"></span>
									</div>
									<div class="agoodsign-editor__icon-dropdown" x-show="pinIconDropdownOpen" x-transition>
										<input type="text"
											x-model="pinIconSearch"
											class="agoodsign-editor__input widefat"
											placeholder="<?php esc_attr_e( 'Search icons... (e.g. globe, arrow, coffee)', 'agoodsign' ); ?>"
											@focus="pinIconDropdownOpen = true">
										<div class="agoodsign-editor__icon-grid">
											<template x-for="icon in filteredIcons.slice(0, 40)" :key="icon.name">
												<button type="button"
													class="agoodsign-editor__icon-btn"
													:class="{ 'is-active': slide.pin_icon === icon.name }"
													@click="selectPinIcon(icon.name)"
													:title="icon.name">
													<span x-html="getPinIconSvg(icon.name, 20, slide.pin_icon === icon.name ? slide.pin_color : '#666')"></span>
													<span class="agoodsign-editor__icon-name" x-text="icon.name"></span>
												</button>
											</template>
										</div>
									</div>
								</div>
							</div>

							<!-- Pin color + size -->
							<div class="agoodsign-editor__section agoodsign-editor__pin-row">
								<div>
									<label class="agoodsign-editor__label"><?php esc_html_e( 'Color', 'agoodsign' ); ?></label>
									<div class="agoodsign-editor__color-row">
										<input type="color" x-model="slide.pin_color" class="agoodsign-editor__color-input">
										<input type="text" x-model="slide.pin_color" class="agoodsign-editor__color-text" maxlength="7">
									</div>
								</div>
								<div>
									<label class="agoodsign-editor__label"><?php esc_html_e( 'Size (px)', 'agoodsign' ); ?></label>
									<input type="number" x-model.number="slide.pin_size" class="agoodsign-editor__input agoodsign-editor__input--small" min="16" max="128" step="4">
								</div>
							</div>

							<!-- Pin animation -->
							<div class="agoodsign-editor__section">
								<label class="agoodsign-editor__label"><?php esc_html_e( 'Pin Animation', 'agoodsign' ); ?></label>
								<select x-model="slide.pin_animation" class="agoodsign-editor__select">
									<option value="pulse"><?php esc_html_e( 'Pulse', 'agoodsign' ); ?></option>
									<option value="bounce"><?php esc_html_e( 'Bounce', 'agoodsign' ); ?></option>
									<option value="glow"><?php esc_html_e( 'Glow', 'agoodsign' ); ?></option>
									<option value="none"><?php esc_html_e( 'None (static)', 'agoodsign' ); ?></option>
								</select>
							</div>

							<!-- Pin label -->
							<div class="agoodsign-editor__section">
								<label class="agoodsign-editor__label"><?php esc_html_e( 'Label (optional)', 'agoodsign' ); ?></label>
								<input type="text" x-model="slide.pin_label" class="agoodsign-editor__input widefat"
									placeholder="<?php esc_attr_e( 'e.g. You are here', 'agoodsign' ); ?>">
							</div>

							<!-- Pin position -->
							<div class="agoodsign-editor__section">
								<label class="agoodsign-editor__label"><?php esc_html_e( 'Position', 'agoodsign' ); ?></label>
								<div class="agoodsign-editor__pin-row">
									<div>
										<label class="agoodsign-editor__label" style="font-weight:normal">X: <span x-text="Math.round(slide.pin_x)"></span>%</label>
										<input type="range" x-model.number="slide.pin_x" min="0" max="100" step="0.5" style="width:100%">
									</div>
									<div>
										<label class="agoodsign-editor__label" style="font-weight:normal">Y: <span x-text="Math.round(slide.pin_y)"></span>%</label>
										<input type="range" x-model.number="slide.pin_y" min="0" max="100" step="0.5" style="width:100%">
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Use sliders or click on the preview to position the pin.', 'agoodsign' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Right column: Preview -->
				<div class="agoodsign-editor__preview-col">
					<div class="agoodsign-editor__preview-label"><?php esc_html_e( 'Preview', 'agoodsign' ); ?></div>
					<div class="agoodsign-editor__preview-container">
						<div class="agoodsign-editor__preview-frame">
							<iframe
								x-ref="previewFrame"
								class="agoodsign-editor__preview-iframe"
								sandbox="allow-same-origin allow-scripts"
								:srcdoc="previewHtml">
							</iframe>
						</div>
					</div>
				</div>
			</div>

			<!-- Hidden inputs for WP save — x-effect sets DOM .value property directly -->
			<input type="hidden" name="_agoodsign_template" x-effect="$el.value = slide.template">
			<input type="hidden" name="_agoodsign_heading" x-effect="$el.value = slide.heading">
			<input type="hidden" name="_agoodsign_body_text" x-effect="$el.value = slide.body_text">
			<input type="hidden" name="_agoodsign_image_id" x-effect="$el.value = slide.image_id || 0">
			<input type="hidden" name="_agoodsign_video_url" x-effect="$el.value = slide.video_url">
			<input type="hidden" name="_agoodsign_video_fit" x-effect="$el.value = slide.video_fit">
			<input type="hidden" name="_agoodsign_animation" x-effect="$el.value = slide.animation">
			<input type="hidden" name="_agoodsign_duration" x-effect="$el.value = slide.duration">
			<input type="hidden" name="_agoodsign_bg_color" x-effect="$el.value = slide.bg_color">
			<input type="hidden" name="_agoodsign_overlay_position" x-effect="$el.value = slide.overlay_position">
			<input type="hidden" name="_agoodsign_media_type" x-effect="$el.value = slide.template === 'video' ? 'video' : 'image'">
			<input type="hidden" name="_agoodsign_split_image_side" x-effect="$el.value = slide.split_image_side">
			<input type="hidden" name="_agoodsign_overlay_color" x-effect="$el.value = slide.overlay_color">
			<input type="hidden" name="_agoodsign_overlay_opacity" x-effect="$el.value = slide.overlay_opacity">
			<input type="hidden" name="_agoodsign_image_focus_x" x-effect="$el.value = slide.image_focus_x">
			<input type="hidden" name="_agoodsign_image_focus_y" x-effect="$el.value = slide.image_focus_y">
			<input type="hidden" name="_agoodsign_text_color" x-effect="$el.value = slide.text_color">
			<input type="hidden" name="_agoodsign_text_align" x-effect="$el.value = slide.text_align">
			<input type="hidden" name="_agoodsign_image_size" x-effect="$el.value = slide.image_size">
			<input type="hidden" name="_agoodsign_image_position" x-effect="$el.value = slide.image_position">
			<input type="hidden" name="_agoodsign_image_radius" x-effect="$el.value = slide.image_radius">
			<input type="hidden" name="_agoodsign_heading_size" x-effect="$el.value = slide.heading_size">
			<input type="hidden" name="_agoodsign_body_size" x-effect="$el.value = slide.body_size">
			<input type="hidden" name="_agoodsign_pin_enabled" x-effect="$el.value = slide.pin_enabled ? '1' : '0'">
			<input type="hidden" name="_agoodsign_pin_icon" x-effect="$el.value = slide.pin_icon">
			<input type="hidden" name="_agoodsign_pin_x" x-effect="$el.value = slide.pin_x">
			<input type="hidden" name="_agoodsign_pin_y" x-effect="$el.value = slide.pin_y">
			<input type="hidden" name="_agoodsign_pin_color" x-effect="$el.value = slide.pin_color">
			<input type="hidden" name="_agoodsign_pin_size" x-effect="$el.value = slide.pin_size">
			<input type="hidden" name="_agoodsign_pin_label" x-effect="$el.value = slide.pin_label">
			<input type="hidden" name="_agoodsign_pin_animation" x-effect="$el.value = slide.pin_animation">
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save( $post_id, $post ) {
		if ( 'signage_slide' !== get_post_type( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['agoodsign_nonce'] ) || ! wp_verify_nonce( $_POST['agoodsign_nonce'], 'agoodsign_save_slide' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_agoodsign_template'         => 'sanitize_text_field',
			'_agoodsign_heading'          => 'sanitize_text_field',
			'_agoodsign_body_text'        => 'wp_kses_post',
			'_agoodsign_image_id'         => 'absint',
			'_agoodsign_video_url'        => 'esc_url_raw',
			'_agoodsign_video_fit'        => 'sanitize_text_field',
			'_agoodsign_animation'        => 'sanitize_text_field',
			'_agoodsign_duration'         => 'absint',
			'_agoodsign_bg_color'         => 'sanitize_hex_color',
			'_agoodsign_overlay_position' => 'sanitize_text_field',
			'_agoodsign_media_type'       => 'sanitize_text_field',
			'_agoodsign_split_image_side' => 'sanitize_text_field',
			'_agoodsign_overlay_color'    => 'sanitize_hex_color',
			'_agoodsign_overlay_opacity'  => 'floatval',
			'_agoodsign_image_focus_x'    => 'floatval',
			'_agoodsign_image_focus_y'    => 'floatval',
			'_agoodsign_text_color'       => 'sanitize_hex_color',
			'_agoodsign_text_align'       => 'sanitize_text_field',
			'_agoodsign_image_size'       => 'absint',
			'_agoodsign_image_position'   => 'sanitize_text_field',
			'_agoodsign_image_radius'     => 'absint',
			'_agoodsign_heading_size'     => 'absint',
			'_agoodsign_body_size'        => 'absint',
			'_agoodsign_pin_enabled'      => 'rest_sanitize_boolean',
			'_agoodsign_pin_icon'         => 'sanitize_text_field',
			'_agoodsign_pin_x'            => 'floatval',
			'_agoodsign_pin_y'            => 'floatval',
			'_agoodsign_pin_color'        => 'sanitize_hex_color',
			'_agoodsign_pin_size'         => 'absint',
			'_agoodsign_pin_label'        => 'sanitize_text_field',
			'_agoodsign_pin_animation'    => 'sanitize_text_field',
		);

		foreach ( $fields as $key => $sanitize_fn ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = call_user_func( $sanitize_fn, wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
