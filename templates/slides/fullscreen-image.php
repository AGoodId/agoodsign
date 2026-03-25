<?php
/**
 * Slide template: Fullscreen Image.
 *
 * Full-bleed image with text overlay and gradient.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$overlay_class = 'agoodsign-overlay--' . esc_attr( $slide['overlay_position'] );
$has_text      = ! empty( $slide['heading'] ) || ! empty( $slide['body_text'] );
$font_styles   = '';
$font_styles  .= ! empty( $slide['heading_size'] ) ? '--agoodsign-heading-size:' . absint( $slide['heading_size'] ) . 'px;' : '';
$font_styles  .= ! empty( $slide['body_size'] ) ? '--agoodsign-body-size:' . absint( $slide['body_size'] ) . 'px;' : '';
?>
<div class="agoodsign-slide agoodsign-slide--fullscreen-image"
	style="color: <?php echo esc_attr( $slide['text_color'] ?: '#ffffff' ); ?>; <?php echo $font_styles; ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<?php if ( ! empty( $slide['image_url'] ) ) : ?>
		<div class="agoodsign-slide__bg" style="background-image: url('<?php echo esc_url( $slide['image_url'] ); ?>')"></div>
	<?php endif; ?>

	<?php if ( $has_text ) : ?>
		<div class="agoodsign-overlay <?php echo esc_attr( $overlay_class ); ?>"
			style="text-align: <?php echo esc_attr( $slide['text_align'] ?? 'center' ); ?>">
			<?php if ( ! empty( $slide['heading'] ) ) : ?>
				<h2 class="agoodsign-slide__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $slide['body_text'] ) ) : ?>
				<p class="agoodsign-slide__body"><?php echo wp_kses_post( nl2br( $slide['body_text'] ) ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php include AGOODSIGN_PLUGIN_DIR . 'templates/slides/partials/pin.php'; ?>
</div>
