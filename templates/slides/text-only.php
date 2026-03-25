<?php
/**
 * Slide template: Text Only.
 *
 * Centered text on solid background.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$font_styles  = '';
$font_styles .= ! empty( $slide['heading_size'] ) ? '--agoodsign-heading-size:' . absint( $slide['heading_size'] ) . 'px;' : '';
$font_styles .= ! empty( $slide['body_size'] ) ? '--agoodsign-body-size:' . absint( $slide['body_size'] ) . 'px;' : '';
?>
<div class="agoodsign-slide agoodsign-slide--text-only"
	style="background-color: <?php echo esc_attr( $slide['bg_color'] ); ?>; color: <?php echo esc_attr( $slide['text_color'] ?: '#ffffff' ); ?>; text-align: <?php echo esc_attr( $slide['text_align'] ?? 'center' ); ?>; <?php echo $font_styles; ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<div class="agoodsign-text-only__content">
		<?php if ( ! empty( $slide['heading'] ) ) : ?>
			<h2 class="agoodsign-slide__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
		<?php endif; ?>

		<div class="agoodsign-text-only__divider"></div>

		<?php if ( ! empty( $slide['body_text'] ) ) : ?>
			<p class="agoodsign-slide__body"><?php echo wp_kses_post( nl2br( $slide['body_text'] ) ); ?></p>
		<?php endif; ?>
	</div>

	<?php include AGOODSIGN_PLUGIN_DIR . 'templates/slides/partials/pin.php'; ?>
</div>
