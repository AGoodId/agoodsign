<?php
/**
 * Channel Editor — drag & drop slide ordering with inline editing.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Channel_Editor {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_agoodsign_save_channel_order', array( __CLASS__, 'ajax_save_order' ) );
		add_action( 'wp_ajax_agoodsign_save_slide_meta', array( __CLASS__, 'ajax_save_meta' ) );
	}

	/**
	 * Add submenu page.
	 */
	public static function add_menu_page() {
		add_submenu_page(
			'edit.php?post_type=signage_slide',
			__( 'Channel Editor', 'agoodsign' ),
			__( 'Channel Editor', 'agoodsign' ),
			'edit_posts',
			'agoodsign-channel-editor',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue assets on the channel editor page.
	 *
	 * @param string $hook_suffix Admin page hook suffix.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'agoodsign-channel-editor' ) ) {
			return;
		}

		wp_enqueue_style(
			'agoodsign-channel-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/css/admin-channel-editor.css',
			array(),
			AGOODSIGN_VERSION
		);

		wp_enqueue_script(
			'agoodsign-channel-editor',
			AGOODSIGN_PLUGIN_URL . 'assets/js/admin-channel-editor.js',
			array(),
			AGOODSIGN_VERSION,
			true
		);

		$animations = AGoodSign_Templates::get_animations();

		wp_localize_script( 'agoodsign-channel-editor', 'agoodsignChannelEditor', array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'agoodsign_channel_editor' ),
			'animations' => $animations,
			'i18n'       => array(
				'saved'      => __( 'Saved', 'agoodsign' ),
				'saving'     => __( 'Saving...', 'agoodsign' ),
				'error'      => __( 'Error saving', 'agoodsign' ),
				'dragHandle' => __( 'Drag to reorder', 'agoodsign' ),
			),
		) );
	}

	/**
	 * AJAX: Save slide order for a channel.
	 */
	public static function ajax_save_order() {
		check_ajax_referer( 'agoodsign_channel_editor', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : array();

		foreach ( $order as $position => $post_id ) {
			wp_update_post( array(
				'ID'         => $post_id,
				'menu_order' => $position,
			) );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX: Save individual slide meta (duration, animation).
	 */
	public static function ajax_save_meta() {
		check_ajax_referer( 'agoodsign_channel_editor', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$field   = sanitize_text_field( $_POST['field'] ?? '' );
		$value   = sanitize_text_field( $_POST['value'] ?? '' );

		if ( ! $post_id || ! $field ) {
			wp_send_json_error( 'Missing data' );
		}

		$allowed_fields = array(
			'duration'  => '_agoodsign_duration',
			'animation' => '_agoodsign_animation',
		);

		if ( ! isset( $allowed_fields[ $field ] ) ) {
			wp_send_json_error( 'Invalid field' );
		}

		$meta_key = $allowed_fields[ $field ];

		if ( 'duration' === $field ) {
			$value = absint( $value );
			if ( $value < 1 ) {
				$value = 1;
			}
		}

		update_post_meta( $post_id, $meta_key, $value );

		wp_send_json_success();
	}

	/**
	 * Render the channel editor page.
	 */
	public static function render_page() {
		$channels = get_terms( array(
			'taxonomy'   => 'signage_channel',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $channels ) ) {
			$channels = array();
		}

		// Current channel from URL param.
		$current_slug = sanitize_text_field( $_GET['channel'] ?? '' );
		$current_term = null;
		$slides       = array();

		if ( $current_slug ) {
			$current_term = get_term_by( 'slug', $current_slug, 'signage_channel' );
			if ( $current_term ) {
				$slides = self::get_slides_for_editor( $current_slug );
			}
		}

		$animations = AGoodSign_Templates::get_animations();
		$templates  = AGoodSign_Templates::get_templates();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Channel Editor', 'agoodsign' ); ?></h1>

			<!-- Channel selector -->
			<div class="agoodsign-ce__channel-bar">
				<?php foreach ( $channels as $ch ) :
					$is_active = $current_term && $current_term->slug === $ch->slug;
					$url       = admin_url( 'edit.php?post_type=signage_slide&page=agoodsign-channel-editor&channel=' . urlencode( $ch->slug ) );
				?>
					<a href="<?php echo esc_url( $url ); ?>"
						class="agoodsign-ce__channel-tab<?php echo $is_active ? ' is-active' : ''; ?>">
						<?php echo esc_html( $ch->name ); ?>
						<span class="agoodsign-ce__channel-count"><?php echo absint( $ch->count ); ?></span>
					</a>
				<?php endforeach; ?>

				<?php if ( empty( $channels ) ) : ?>
					<p><?php esc_html_e( 'No channels created yet. Create a channel first.', 'agoodsign' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $current_term && ! empty( $slides ) ) : ?>
				<!-- Status indicator -->
				<div class="agoodsign-ce__status" id="agoodsign-ce-status"></div>

				<!-- Slide list -->
				<div class="agoodsign-ce__list" id="agoodsign-ce-list" data-channel="<?php echo esc_attr( $current_slug ); ?>">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<div class="agoodsign-ce__item" data-id="<?php echo absint( $slide['id'] ); ?>">
							<div class="agoodsign-ce__drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'agoodsign' ); ?>">
								<span class="dashicons dashicons-menu"></span>
							</div>

							<div class="agoodsign-ce__thumb">
								<?php if ( ! empty( $slide['image_url'] ) ) : ?>
									<img src="<?php echo esc_url( $slide['image_url'] ); ?>" alt="">
								<?php else : ?>
									<span class="dashicons dashicons-<?php echo esc_attr( $templates[ $slide['template'] ]['icon'] ?? 'format-image' ); ?>"></span>
								<?php endif; ?>
							</div>

							<div class="agoodsign-ce__info">
								<strong class="agoodsign-ce__title">
									<a href="<?php echo esc_url( get_edit_post_link( $slide['id'] ) ); ?>">
										<?php echo esc_html( $slide['title'] ?: __( '(no title)', 'agoodsign' ) ); ?>
									</a>
								</strong>
								<span class="agoodsign-ce__template">
									<?php echo esc_html( $templates[ $slide['template'] ]['name'] ?? $slide['template'] ); ?>
								</span>
							</div>

							<div class="agoodsign-ce__field">
								<label class="agoodsign-ce__field-label"><?php esc_html_e( 'Duration', 'agoodsign' ); ?></label>
								<div class="agoodsign-ce__duration-wrap">
									<input type="number"
										class="agoodsign-ce__duration"
										data-id="<?php echo absint( $slide['id'] ); ?>"
										data-field="duration"
										value="<?php echo absint( $slide['duration'] ); ?>"
										min="1" max="300" step="1">
									<span class="agoodsign-ce__duration-unit"><?php esc_html_e( 's', 'agoodsign' ); ?></span>
								</div>
							</div>

							<div class="agoodsign-ce__field">
								<label class="agoodsign-ce__field-label"><?php esc_html_e( 'Animation', 'agoodsign' ); ?></label>
								<select class="agoodsign-ce__animation"
									data-id="<?php echo absint( $slide['id'] ); ?>"
									data-field="animation">
									<?php foreach ( $animations as $slug => $anim ) : ?>
										<option value="<?php echo esc_attr( $slug ); ?>"
											<?php selected( $slide['animation'], $slug ); ?>>
											<?php echo esc_html( $anim['name'] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="agoodsign-ce__actions">
								<a href="<?php echo esc_url( get_edit_post_link( $slide['id'] ) ); ?>"
									class="button button-small"
									title="<?php esc_attr_e( 'Edit slide', 'agoodsign' ); ?>">
									<span class="dashicons dashicons-edit" style="font-size:14px;width:14px;height:14px;vertical-align:middle"></span>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			<?php elseif ( $current_term ) : ?>
				<p class="agoodsign-ce__empty">
					<?php esc_html_e( 'No slides in this channel yet.', 'agoodsign' ); ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=signage_slide' ) ); ?>">
						<?php esc_html_e( 'Add a slide', 'agoodsign' ); ?>
					</a>
				</p>
			<?php elseif ( ! empty( $channels ) ) : ?>
				<p class="agoodsign-ce__empty"><?php esc_html_e( 'Select a channel above to manage its slides.', 'agoodsign' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get slides for a channel, ordered for the editor.
	 *
	 * @param string $channel_slug Channel slug.
	 * @return array Slide data with ordering info.
	 */
	private static function get_slides_for_editor( $channel_slug ) {
		$query = new WP_Query( array(
			'post_type'      => 'signage_slide',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'signage_channel',
					'field'    => 'slug',
					'terms'    => $channel_slug,
				),
			),
		) );

		$slides = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$image_id  = get_post_meta( $post_id, '_agoodsign_image_id', true );
				$image_url = '';
				if ( $image_id ) {
					$image_url = wp_get_attachment_image_url( absint( $image_id ), 'thumbnail' );
				}
				if ( ! $image_url ) {
					$thumb_id = get_post_thumbnail_id( $post_id );
					if ( $thumb_id ) {
						$image_url = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
					}
				}

				$slides[] = array(
					'id'        => $post_id,
					'title'     => get_the_title( $post_id ),
					'template'  => get_post_meta( $post_id, '_agoodsign_template', true ) ?: 'fullscreen-image',
					'duration'  => absint( get_post_meta( $post_id, '_agoodsign_duration', true ) ) ?: 10,
					'animation' => get_post_meta( $post_id, '_agoodsign_animation', true ) ?: 'fade-in',
					'image_url' => $image_url,
				);
			}
			wp_reset_postdata();
		}

		return $slides;
	}
}
