<?php
/**
 * Fullscreen signage player template.
 *
 * @package AGoodSign
 * @var array  $slides     Array of slide data.
 * @var array  $resolution Array with 'width' and 'height'.
 * @var string $font_heading Font family for headings.
 * @var string $font_body    Font family for body text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_preview = isset( $_GET['preview'] ) && '1' === $_GET['preview'];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'AGoodSign Player', 'agoodsign' ); ?></title>

	<?php
	// Load fonts.
	AGoodSign_Fonts::render_font_styles();
	?>

	<link rel="stylesheet" href="<?php echo esc_url( AGOODSIGN_PLUGIN_URL . 'assets/css/player.css' ); ?>?v=<?php echo esc_attr( AGOODSIGN_VERSION ); ?>">

	<style>
		:root {
			<?php if ( $font_heading ) : ?>
				--agoodsign-font-heading: '<?php echo esc_attr( $font_heading ); ?>', sans-serif;
			<?php endif; ?>
			<?php if ( $font_body ) : ?>
				--agoodsign-font-body: '<?php echo esc_attr( $font_body ); ?>', sans-serif;
			<?php endif; ?>
		}
	</style>
</head>
<body>
	<div class="agoodsign-player" id="agoodsign-player">
		<?php foreach ( $slides as $index => $slide ) : ?>
			<div class="agoodsign-slide-wrapper<?php echo 0 === $index ? ' is-active' : ''; ?>"
				data-index="<?php echo absint( $index ); ?>">
				<?php AGoodSign_Templates::render_slide( $slide ); ?>
			</div>
		<?php endforeach; ?>

		<!-- Navigation controls (visible on hover) -->
		<div class="agoodsign-controls" id="agoodsign-controls">
			<button class="agoodsign-controls__btn agoodsign-controls__prev" id="agoodsign-prev" title="<?php esc_attr_e( 'Previous slide', 'agoodsign' ); ?>">&#8249;</button>
			<button class="agoodsign-controls__btn agoodsign-controls__pause" id="agoodsign-pause" title="<?php esc_attr_e( 'Pause / Play', 'agoodsign' ); ?>">&#10074;&#10074;</button>
			<button class="agoodsign-controls__btn agoodsign-controls__next" id="agoodsign-next" title="<?php esc_attr_e( 'Next slide', 'agoodsign' ); ?>">&#8250;</button>
			<span class="agoodsign-controls__counter" id="agoodsign-counter">1 / <?php echo count( $slides ); ?></span>
		</div>
	</div>

	<script>
		window.agoodsignPlayerData = <?php echo wp_json_encode( array(
			'slides'     => array_map( function ( $slide ) {
				return array(
					'duration'  => $slide['duration'],
					'animation' => $slide['animation'],
				);
			}, $slides ),
			'resolution' => $resolution,
			'isPreview'  => $is_preview,
			'screenId'   => isset( $screen_id ) ? absint( $screen_id ) : 0,
			'hashUrl'    => isset( $screen_id ) ? rest_url( 'agoodsign/v1/screens/' . absint( $screen_id ) . '/hash' ) : '',
		) ); ?>;
	</script>

	<script src="<?php echo esc_url( AGOODSIGN_PLUGIN_URL . 'assets/js/vendor/gsap.min.js' ); ?>"></script>
	<script src="<?php echo esc_url( AGOODSIGN_PLUGIN_URL . 'assets/js/animations.js' ); ?>?v=<?php echo esc_attr( AGOODSIGN_VERSION ); ?>"></script>
	<script src="<?php echo esc_url( AGOODSIGN_PLUGIN_URL . 'assets/js/player.js' ); ?>?v=<?php echo esc_attr( AGOODSIGN_VERSION ); ?>"></script>
</body>
</html>
