<?php
/**
 * Slide template definitions and rendering.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Templates {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// Templates are loaded on demand, no hooks needed.
	}

	/**
	 * Get all available slide templates.
	 *
	 * @return array Template definitions.
	 */
	public static function get_templates() {
		return array(
			'fullscreen-image' => array(
				'name'        => __( 'Fullscreen Image', 'agoodsign' ),
				'description' => __( 'Full-bleed image with text overlay', 'agoodsign' ),
				'icon'        => 'format-image',
				'fields'      => array( 'image', 'heading', 'body_text', 'overlay_position' ),
			),
			'split'            => array(
				'name'        => __( 'Split', 'agoodsign' ),
				'description' => __( 'Image and text side by side', 'agoodsign' ),
				'icon'        => 'columns',
				'fields'      => array( 'image', 'heading', 'body_text', 'split_image_side', 'bg_color' ),
			),
			'text-only'        => array(
				'name'        => __( 'Text Only', 'agoodsign' ),
				'description' => __( 'Centered text on solid background', 'agoodsign' ),
				'icon'        => 'editor-aligncenter',
				'fields'      => array( 'heading', 'body_text', 'bg_color' ),
			),
			'video'            => array(
				'name'        => __( 'Video', 'agoodsign' ),
				'description' => __( 'Fullscreen video with optional overlay', 'agoodsign' ),
				'icon'        => 'video-alt3',
				'fields'      => array( 'video', 'heading', 'body_text', 'overlay_position' ),
			),
			'title-card'       => array(
				'name'        => __( 'Title Card', 'agoodsign' ),
				'description' => __( 'Large centered title with subtitle', 'agoodsign' ),
				'icon'        => 'heading',
				'fields'      => array( 'heading', 'body_text', 'image', 'bg_color' ),
			),
		);
	}

	/**
	 * Get all available animations.
	 *
	 * @return array Animation definitions.
	 */
	public static function get_animations() {
		return array(
			'fade-in'    => array(
				'name'   => __( 'Fade In', 'agoodsign' ),
				'type'   => 'css',
			),
			'slide-up'   => array(
				'name'   => __( 'Slide Up', 'agoodsign' ),
				'type'   => 'css',
			),
			'slide-left' => array(
				'name'   => __( 'Slide Left', 'agoodsign' ),
				'type'   => 'css',
			),
			'zoom-in'    => array(
				'name'   => __( 'Zoom In', 'agoodsign' ),
				'type'   => 'css',
			),
			'ken-burns'  => array(
				'name'   => __( 'Ken Burns', 'agoodsign' ),
				'type'   => 'css',
			),
			'typewriter' => array(
				'name'   => __( 'Typewriter', 'agoodsign' ),
				'type'   => 'gsap',
			),
			'stagger'    => array(
				'name'   => __( 'Stagger', 'agoodsign' ),
				'type'   => 'gsap',
			),
		);
	}

	/**
	 * Get slide data from a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Slide data.
	 */
	public static function get_slide_data( $post_id ) {
		$image_id  = get_post_meta( $post_id, '_agoodsign_image_id', true );
		$image_url = '';

		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( absint( $image_id ), 'full' );
		}

		if ( ! $image_url ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( $thumb_id ) {
				$image_url = wp_get_attachment_image_url( $thumb_id, 'full' );
			}
		}

		return array(
			'id'               => $post_id,
			'title'            => get_the_title( $post_id ),
			'template'         => get_post_meta( $post_id, '_agoodsign_template', true ) ?: 'fullscreen-image',
			'duration'         => absint( get_post_meta( $post_id, '_agoodsign_duration', true ) ) ?: 10,
			'animation'        => get_post_meta( $post_id, '_agoodsign_animation', true ) ?: 'fade-in',
			'heading'          => get_post_meta( $post_id, '_agoodsign_heading', true ) ?: '',
			'body_text'        => get_post_meta( $post_id, '_agoodsign_body_text', true ) ?: '',
			'media_type'       => get_post_meta( $post_id, '_agoodsign_media_type', true ) ?: 'image',
			'image_url'        => $image_url ?: '',
			'video_url'        => get_post_meta( $post_id, '_agoodsign_video_url', true ) ?: '',
			'overlay_position' => get_post_meta( $post_id, '_agoodsign_overlay_position', true ) ?: 'bottom',
			'overlay_color'    => get_post_meta( $post_id, '_agoodsign_overlay_color', true ) ?: '#000000',
			'overlay_opacity'  => floatval( get_post_meta( $post_id, '_agoodsign_overlay_opacity', true ) ?: 0.6 ),
			'bg_color'         => get_post_meta( $post_id, '_agoodsign_bg_color', true ) ?: '#000000',
			'split_image_side' => get_post_meta( $post_id, '_agoodsign_split_image_side', true ) ?: 'left',
			'image_focus_x'    => floatval( get_post_meta( $post_id, '_agoodsign_image_focus_x', true ) ?: 50 ),
			'image_focus_y'    => floatval( get_post_meta( $post_id, '_agoodsign_image_focus_y', true ) ?: 50 ),
			'text_color'       => get_post_meta( $post_id, '_agoodsign_text_color', true ) ?: '#ffffff',
			'pin_enabled'      => (bool) get_post_meta( $post_id, '_agoodsign_pin_enabled', true ),
			'pin_icon'         => get_post_meta( $post_id, '_agoodsign_pin_icon', true ) ?: 'map-pin',
			'pin_x'            => floatval( get_post_meta( $post_id, '_agoodsign_pin_x', true ) ?: 50 ),
			'pin_y'            => floatval( get_post_meta( $post_id, '_agoodsign_pin_y', true ) ?: 50 ),
			'pin_color'        => get_post_meta( $post_id, '_agoodsign_pin_color', true ) ?: '#ef4444',
			'pin_size'         => absint( get_post_meta( $post_id, '_agoodsign_pin_size', true ) ?: 48 ),
			'pin_label'        => get_post_meta( $post_id, '_agoodsign_pin_label', true ) ?: '',
			'pin_animation'    => get_post_meta( $post_id, '_agoodsign_pin_animation', true ) ?: 'pulse',
		);
	}

	/**
	 * Render a slide using its template.
	 *
	 * @param array $slide Slide data array.
	 */
	public static function render_slide( $slide ) {
		$template_slug = sanitize_file_name( $slide['template'] );
		$template_file = AGOODSIGN_PLUGIN_DIR . 'templates/slides/' . $template_slug . '.php';

		if ( ! file_exists( $template_file ) ) {
			$template_file = AGOODSIGN_PLUGIN_DIR . 'templates/slides/fullscreen-image.php';
		}

		include $template_file;
	}

	/**
	 * Get slides for a channel.
	 *
	 * @param string $channel_slug Channel taxonomy slug.
	 * @return array Array of slide data.
	 */
	public static function get_channel_slides( $channel_slug ) {
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
				$slides[] = self::get_slide_data( get_the_ID() );
			}
			wp_reset_postdata();
		}

		return $slides;
	}
}
