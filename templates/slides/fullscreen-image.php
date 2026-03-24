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
?>
<div class="agoodsign-slide agoodsign-slide--fullscreen-image"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<?php if ( ! empty( $slide['image_url'] ) ) : ?>
		<div class="agoodsign-slide__bg" style="background-image: url('<?php echo esc_url( $slide['image_url'] ); ?>')"></div>
	<?php endif; ?>

	<?php if ( $has_text ) : ?>
		<div class="agoodsign-overlay <?php echo esc_attr( $overlay_class ); ?>">
			<?php if ( ! empty( $slide['heading'] ) ) : ?>
				<h2 class="agoodsign-slide__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $slide['body_text'] ) ) : ?>
				<p class="agoodsign-slide__body"><?php echo wp_kses_post( $slide['body_text'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
