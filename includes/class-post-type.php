<?php
/**
 * Custom Post Type and Taxonomy registration.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Post_Type {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_signage_slide_posts_columns', array( __CLASS__, 'add_admin_columns' ) );
		add_action( 'manage_signage_slide_posts_custom_column', array( __CLASS__, 'render_admin_columns' ), 10, 2 );
	}

	/**
	 * Register the CPT and taxonomy.
	 */
	public static function register() {
		self::register_taxonomy();
		self::register_post_type();
		self::register_meta();
	}

	/**
	 * Register signage_channel taxonomy.
	 */
	private static function register_taxonomy() {
		$labels = array(
			'name'              => __( 'Channels', 'agoodsign' ),
			'singular_name'     => __( 'Channel', 'agoodsign' ),
			'search_items'      => __( 'Search Channels', 'agoodsign' ),
			'all_items'         => __( 'All Channels', 'agoodsign' ),
			'parent_item'       => __( 'Parent Channel', 'agoodsign' ),
			'parent_item_colon' => __( 'Parent Channel:', 'agoodsign' ),
			'edit_item'         => __( 'Edit Channel', 'agoodsign' ),
			'update_item'       => __( 'Update Channel', 'agoodsign' ),
			'add_new_item'      => __( 'Add New Channel', 'agoodsign' ),
			'new_item_name'     => __( 'New Channel Name', 'agoodsign' ),
			'menu_name'         => __( 'Channels', 'agoodsign' ),
		);

		register_taxonomy( 'signage_channel', 'signage_slide', array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		) );
	}

	/**
	 * Register signage_slide post type.
	 */
	private static function register_post_type() {
		$labels = array(
			'name'               => __( 'Slides', 'agoodsign' ),
			'singular_name'      => __( 'Slide', 'agoodsign' ),
			'add_new'            => __( 'Add New Slide', 'agoodsign' ),
			'add_new_item'       => __( 'Add New Slide', 'agoodsign' ),
			'edit_item'          => __( 'Edit Slide', 'agoodsign' ),
			'new_item'           => __( 'New Slide', 'agoodsign' ),
			'view_item'          => __( 'View Slide', 'agoodsign' ),
			'search_items'       => __( 'Search Slides', 'agoodsign' ),
			'not_found'          => __( 'No slides found', 'agoodsign' ),
			'not_found_in_trash' => __( 'No slides found in Trash', 'agoodsign' ),
			'all_items'          => __( 'All Slides', 'agoodsign' ),
			'menu_name'          => __( 'AGoodSign', 'agoodsign' ),
		);

		register_post_type( 'signage_slide', array(
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'thumbnail' ),
			'menu_icon'    => 'dashicons-format-gallery',
			'menu_position' => 30,
			'has_archive'  => false,
			'rewrite'      => false,
			'taxonomies'   => array( 'signage_channel' ),
		) );
	}

	/**
	 * Register post meta fields.
	 */
	private static function register_meta() {
		$meta_fields = array(
			'_agoodsign_template'         => array(
				'type'              => 'string',
				'default'           => 'fullscreen-image',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_duration'         => array(
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'_agoodsign_animation'        => array(
				'type'              => 'string',
				'default'           => 'fade-in',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_heading'          => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_body_text'        => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post',
			),
			'_agoodsign_media_type'       => array(
				'type'              => 'string',
				'default'           => 'image',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_video_url'        => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			),
			'_agoodsign_video_fit'        => array(
				'type'              => 'string',
				'default'           => 'cover',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_overlay_position' => array(
				'type'              => 'string',
				'default'           => 'bottom',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_overlay_color'    => array(
				'type'              => 'string',
				'default'           => '#000000',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'_agoodsign_overlay_opacity'  => array(
				'type'              => 'number',
				'default'           => 0.6,
				'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
			),
			'_agoodsign_bg_color'         => array(
				'type'              => 'string',
				'default'           => '#000000',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'_agoodsign_image_id'         => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			),
			'_agoodsign_split_image_side' => array(
				'type'              => 'string',
				'default'           => 'left',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_image_focus_x'    => array(
				'type'              => 'number',
				'default'           => 50,
				'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
			),
			'_agoodsign_image_focus_y'    => array(
				'type'              => 'number',
				'default'           => 50,
				'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
			),
			'_agoodsign_text_color'       => array(
				'type'              => 'string',
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'_agoodsign_text_align'       => array(
				'type'              => 'string',
				'default'           => 'center',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_image_size'       => array(
				'type'              => 'integer',
				'default'           => 60,
				'sanitize_callback' => 'absint',
			),
			'_agoodsign_image_position'   => array(
				'type'              => 'string',
				'default'           => 'top',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_image_radius'     => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			),
			'_agoodsign_pin_enabled'      => array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			),
			'_agoodsign_pin_icon'         => array(
				'type'              => 'string',
				'default'           => 'map-pin',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_pin_x'            => array(
				'type'              => 'number',
				'default'           => 50,
				'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
			),
			'_agoodsign_pin_y'            => array(
				'type'              => 'number',
				'default'           => 50,
				'sanitize_callback' => array( __CLASS__, 'sanitize_float' ),
			),
			'_agoodsign_pin_color'        => array(
				'type'              => 'string',
				'default'           => '#ef4444',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'_agoodsign_pin_size'         => array(
				'type'              => 'integer',
				'default'           => 48,
				'sanitize_callback' => 'absint',
			),
			'_agoodsign_pin_label'        => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'_agoodsign_pin_animation'    => array(
				'type'              => 'string',
				'default'           => 'pulse',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		foreach ( $meta_fields as $key => $args ) {
			register_post_meta( 'signage_slide', $key, array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => $args['type'],
				'default'           => $args['default'],
				'sanitize_callback' => $args['sanitize_callback'],
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			) );
		}
	}

	/**
	 * Sanitize a float value (wrapper for floatval that accepts only 1 arg).
	 *
	 * @param mixed $value The value to sanitize.
	 * @return float
	 */
	public static function sanitize_float( $value ) {
		return floatval( $value );
	}

	/**
	 * Add custom columns to the slides list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public static function add_admin_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			if ( 'title' === $key ) {
				$new_columns['agoodsign_template']  = __( 'Template', 'agoodsign' );
				$new_columns['agoodsign_animation'] = __( 'Animation', 'agoodsign' );
				$new_columns['agoodsign_duration']  = __( 'Duration', 'agoodsign' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public static function render_admin_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'agoodsign_template':
				$template  = get_post_meta( $post_id, '_agoodsign_template', true );
				$templates = AGoodSign_Templates::get_templates();
				echo esc_html( isset( $templates[ $template ] ) ? $templates[ $template ]['name'] : $template );
				break;

			case 'agoodsign_animation':
				$animation  = get_post_meta( $post_id, '_agoodsign_animation', true );
				$animations = AGoodSign_Templates::get_animations();
				echo esc_html( isset( $animations[ $animation ] ) ? $animations[ $animation ]['name'] : $animation );
				break;

			case 'agoodsign_duration':
				$duration = get_post_meta( $post_id, '_agoodsign_duration', true );
				/* translators: %d: number of seconds */
				printf( esc_html__( '%ds', 'agoodsign' ), absint( $duration ) );
				break;
		}
	}
}
