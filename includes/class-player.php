<?php
/**
 * Fullscreen player — rewrite rules and template loading.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Player {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_request' ) );
	}

	/**
	 * Register rewrite rules for screen display URLs.
	 */
	public static function register_rewrite_rules() {
		add_rewrite_rule(
			'^signage/screen/([0-9]+)/?$',
			'index.php?agoodsign_screen=$matches[1]',
			'top'
		);

		// Preview route for editor iframe.
		add_rewrite_rule(
			'^signage/preview/?$',
			'index.php?agoodsign_preview=1',
			'top'
		);
	}

	/**
	 * Register custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'agoodsign_screen';
		$vars[] = 'agoodsign_preview';
		return $vars;
	}

	/**
	 * Handle signage display requests.
	 */
	public static function handle_request() {
		$screen_id = get_query_var( 'agoodsign_screen' );
		$preview   = get_query_var( 'agoodsign_preview' );

		if ( $screen_id ) {
			self::render_screen( absint( $screen_id ) );
			exit;
		}

		if ( $preview ) {
			self::render_preview();
			exit;
		}
	}

	/**
	 * Render the fullscreen player for a screen.
	 *
	 * @param int $screen_id Screen ID (1-5).
	 */
	private static function render_screen( $screen_id ) {
		$screens = get_option( 'agoodsign_screens', array() );

		if ( ! isset( $screens[ $screen_id ] ) || empty( $screens[ $screen_id ]['active'] ) ) {
			self::render_error( __( 'Screen not found or inactive.', 'agoodsign' ) );
			return;
		}

		$screen       = $screens[ $screen_id ];
		$channel_slug = $screen['channel'] ?? '';

		if ( empty( $channel_slug ) ) {
			self::render_error( __( 'No channel assigned to this screen.', 'agoodsign' ) );
			return;
		}

		$slides = AGoodSign_Templates::get_channel_slides( $channel_slug );

		if ( empty( $slides ) ) {
			self::render_error( __( 'No published slides in this channel.', 'agoodsign' ) );
			return;
		}

		// Determine resolution.
		$default_res = get_option( 'agoodsign_default_resolution', array( 'width' => 1080, 'height' => 1920 ) );
		$resolution  = ( ! empty( $screen['resolution'] ) && empty( $screen['use_default_resolution'] ) )
			? $screen['resolution']
			: $default_res;

		// Font settings.
		$font_heading = get_option( 'agoodsign_font_heading', '' );
		$font_body    = get_option( 'agoodsign_font_body', '' );

		include AGOODSIGN_PLUGIN_DIR . 'templates/player.php';
	}

	/**
	 * Render the preview template (for editor iframe).
	 */
	private static function render_preview() {
		include AGOODSIGN_PLUGIN_DIR . 'templates/preview.php';
	}

	/**
	 * Render an error message in fullscreen.
	 *
	 * @param string $message Error message.
	 */
	private static function render_error( $message ) {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'AGoodSign', 'agoodsign' ); ?></title>
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body {
					background: #000;
					color: #555;
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
					display: flex;
					align-items: center;
					justify-content: center;
					height: 100vh;
					text-align: center;
					padding: 40px;
				}
				p { font-size: 24px; }
			</style>
		</head>
		<body>
			<p><?php echo esc_html( $message ); ?></p>
		</body>
		</html>
		<?php
	}
}
