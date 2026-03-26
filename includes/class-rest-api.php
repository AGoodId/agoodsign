<?php
/**
 * REST API endpoints.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_REST_API {

	const NAMESPACE = 'agoodsign/v1';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		// GET /channels
		register_rest_route( self::NAMESPACE, '/channels', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_channels' ),
			'permission_callback' => '__return_true',
		) );

		// GET /channels/{slug}/slides
		register_rest_route( self::NAMESPACE, '/channels/(?P<slug>[a-zA-Z0-9_-]+)/slides', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_channel_slides' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'slug' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		// GET /screens
		register_rest_route( self::NAMESPACE, '/screens', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_screens' ),
			'permission_callback' => '__return_true',
		) );

		// GET /screens/{id}/hash — lightweight change-detection for player polling.
		register_rest_route( self::NAMESPACE, '/screens/(?P<id>[0-9]+)/hash', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_screen_hash' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		) );

		// GET /screens/{id}
		register_rest_route( self::NAMESPACE, '/screens/(?P<id>[0-9]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_screen' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		) );
	}

	/**
	 * GET /channels — List all channels.
	 *
	 * @return WP_REST_Response
	 */
	public static function get_channels() {
		$terms = get_terms( array(
			'taxonomy'   => 'signage_channel',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $terms ) ) {
			return new WP_REST_Response( array( 'error' => $terms->get_error_message() ), 500 );
		}

		$channels = array_map( function ( $term ) {
			return array(
				'name'        => $term->name,
				'slug'        => $term->slug,
				'description' => $term->description,
				'count'       => $term->count,
			);
		}, $terms );

		return new WP_REST_Response( $channels, 200 );
	}

	/**
	 * GET /channels/{slug}/slides — Get slides for a channel.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_channel_slides( $request ) {
		$slug = $request->get_param( 'slug' );
		$term = get_term_by( 'slug', $slug, 'signage_channel' );

		if ( ! $term ) {
			return new WP_REST_Response( array( 'error' => 'Channel not found.' ), 404 );
		}

		$slides = AGoodSign_Templates::get_channel_slides( $slug );

		return new WP_REST_Response( $slides, 200 );
	}

	/**
	 * GET /screens — List all configured screens.
	 *
	 * @return WP_REST_Response
	 */
	public static function get_screens() {
		$screens     = get_option( 'agoodsign_screens', array() );
		$default_res = get_option( 'agoodsign_default_resolution', array( 'width' => 1080, 'height' => 1920 ) );
		$result      = array();

		foreach ( $screens as $id => $screen ) {
			$resolution = ( ! empty( $screen['resolution'] ) && empty( $screen['use_default_resolution'] ) )
				? $screen['resolution']
				: $default_res;

			$result[] = array(
				'id'           => absint( $id ),
				'name'         => $screen['name'] ?? '',
				'channel_slug' => $screen['channel'] ?? '',
				'resolution'   => $resolution,
				'active'       => ! empty( $screen['active'] ),
				'display_url'  => home_url( '/signage/screen/' . absint( $id ) . '/' ),
			);
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * GET /screens/{id}/hash — Return a hash of the screen's current content.
	 *
	 * The player polls this endpoint to detect changes without transferring
	 * all slide data. When the hash changes, the player reloads the page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_screen_hash( $request ) {
		$id      = $request->get_param( 'id' );
		$screens = get_option( 'agoodsign_screens', array() );

		if ( ! isset( $screens[ $id ] ) ) {
			return new WP_REST_Response( array( 'error' => 'Screen not found.' ), 404 );
		}

		$screen = $screens[ $id ];
		$slides = array();

		if ( ! empty( $screen['channel'] ) ) {
			$slides = AGoodSign_Templates::get_channel_slides( $screen['channel'] );
		}

		// Build a hash from slide data that would affect rendering.
		$hash_input = wp_json_encode( array(
			'channel'    => $screen['channel'] ?? '',
			'slides'     => $slides,
			'resolution' => $screen['resolution'] ?? array(),
		) );

		return new WP_REST_Response( array(
			'hash' => md5( $hash_input ),
		), 200 );
	}

	/**
	 * GET /screens/{id} — Get a single screen with its slides.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_screen( $request ) {
		$id      = $request->get_param( 'id' );
		$screens = get_option( 'agoodsign_screens', array() );

		if ( ! isset( $screens[ $id ] ) ) {
			return new WP_REST_Response( array( 'error' => 'Screen not found.' ), 404 );
		}

		$screen      = $screens[ $id ];
		$default_res = get_option( 'agoodsign_default_resolution', array( 'width' => 1080, 'height' => 1920 ) );
		$resolution  = ( ! empty( $screen['resolution'] ) && empty( $screen['use_default_resolution'] ) )
			? $screen['resolution']
			: $default_res;

		$slides = array();
		if ( ! empty( $screen['channel'] ) ) {
			$slides = AGoodSign_Templates::get_channel_slides( $screen['channel'] );
		}

		return new WP_REST_Response( array(
			'id'           => absint( $id ),
			'name'         => $screen['name'] ?? '',
			'channel_slug' => $screen['channel'] ?? '',
			'resolution'   => $resolution,
			'active'       => ! empty( $screen['active'] ),
			'display_url'  => home_url( '/signage/screen/' . absint( $id ) . '/' ),
			'slides'       => $slides,
		), 200 );
	}
}
