<?php
/**
 * Preview template for the editor iframe.
 *
 * Receives slide data via postMessage from the admin editor
 * and renders the appropriate template in real time.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Slide Preview', 'agoodsign' ); ?></title>

	<?php AGoodSign_Fonts::render_font_styles(); ?>

	<link rel="stylesheet" href="<?php echo esc_url( AGOODSIGN_PLUGIN_URL . 'assets/css/player.css' ); ?>?v=<?php echo esc_attr( AGOODSIGN_VERSION ); ?>">
</head>
<body>
	<div class="agoodsign-player" id="agoodsign-preview-root">
		<!-- Populated via postMessage from admin editor -->
	</div>

	<script>
		window.addEventListener( 'message', function ( event ) {
			if ( ! event.data || event.data.type !== 'agoodsign_preview' ) return;
			document.getElementById( 'agoodsign-preview-root' ).innerHTML = event.data.html;
		} );
	</script>
</body>
</html>
