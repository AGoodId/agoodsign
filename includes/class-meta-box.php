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

		wp_enqueue_script(
			'alpinejs',
			AGOODSIGN_PLUGIN_URL . 'assets/js/vendor/alpine.min.js',
			array(),
			'3.14.8',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'agoodsign-admin-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/js/admin-editor.js',
			array( 'alpinejs', 'wp-i18n' ),
			AGOODSIGN_VERSION,
			true
		);

		wp_enqueue_style(
			'agoodsign-admin-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/css/admin-editor.css',
			array(),
			AGOODSIGN_VERSION
		);

		$templates  = AGoodSign_Templates::get_templates();
		$animations = AGoodSign_Templates::get_animations();

		wp_localize_script( 'agoodsign-admin-editor', 'agoodsignEditor', array(
			'templates'  => $templates,
			'animations' => $animations,
			'previewUrl' => add_query_arg(
				array(
					'agoodsign_preview' => 1,
					'post_id'           => get_the_ID(),
				),
				home_url( '/' )
			),
			'pluginUrl'  => AGOODSIGN_PLUGIN_URL,
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
		?>
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

					<!-- Body text -->
					<div class="agoodsign-editor__section">
						<label class="agoodsign-editor__label" for="agoodsign-body"><?php esc_html_e( 'Body text', 'agoodsign' ); ?></label>
						<textarea
							id="agoodsign-body"
							x-model="slide.body_text"
							class="agoodsign-editor__textarea widefat"
							rows="3"
							placeholder="<?php esc_attr_e( 'Enter body text...', 'agoodsign' ); ?>"></textarea>
					</div>

					<!-- Image (shown for templates that need it) -->
					<div class="agoodsign-editor__section" x-show="needsImage" x-transition>
						<label class="agoodsign-editor__label"><?php esc_html_e( 'Image', 'agoodsign' ); ?></label>
						<div class="agoodsign-editor__image-picker">
							<div class="agoodsign-editor__image-preview" x-show="slide.image_url">
								<img :src="slide.image_url" alt="">
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

					<!-- Overlay position -->
					<div class="agoodsign-editor__section" x-show="needsOverlay" x-transition>
						<label class="agoodsign-editor__label" for="agoodsign-overlay"><?php esc_html_e( 'Text Position', 'agoodsign' ); ?></label>
						<select id="agoodsign-overlay" x-model="slide.overlay_position" class="agoodsign-editor__select">
							<option value="top"><?php esc_html_e( 'Top', 'agoodsign' ); ?></option>
							<option value="center"><?php esc_html_e( 'Center', 'agoodsign' ); ?></option>
							<option value="bottom"><?php esc_html_e( 'Bottom', 'agoodsign' ); ?></option>
						</select>
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

			<!-- Hidden inputs for WP save -->
			<input type="hidden" name="_agoodsign_template" :value="slide.template">
			<input type="hidden" name="_agoodsign_heading" :value="slide.heading">
			<input type="hidden" name="_agoodsign_body_text" :value="slide.body_text">
			<input type="hidden" name="_agoodsign_image_id" :value="slide.image_id || 0">
			<input type="hidden" name="_agoodsign_video_url" :value="slide.video_url">
			<input type="hidden" name="_agoodsign_animation" :value="slide.animation">
			<input type="hidden" name="_agoodsign_duration" :value="slide.duration">
			<input type="hidden" name="_agoodsign_bg_color" :value="slide.bg_color">
			<input type="hidden" name="_agoodsign_overlay_position" :value="slide.overlay_position">
			<input type="hidden" name="_agoodsign_media_type" :value="slide.template === 'video' ? 'video' : 'image'">
			<input type="hidden" name="_agoodsign_split_image_side" :value="slide.split_image_side">
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
			'_agoodsign_animation'        => 'sanitize_text_field',
			'_agoodsign_duration'         => 'absint',
			'_agoodsign_bg_color'         => 'sanitize_hex_color',
			'_agoodsign_overlay_position' => 'sanitize_text_field',
			'_agoodsign_media_type'       => 'sanitize_text_field',
			'_agoodsign_split_image_side' => 'sanitize_text_field',
		);

		foreach ( $fields as $key => $sanitize_fn ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = call_user_func( $sanitize_fn, wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
