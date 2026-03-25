<?php
/**
 * Slide template: Image + Text.
 *
 * Positioned image with heading and body text on a solid background.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_text       = ! empty( $slide['heading'] ) || ! empty( $slide['body_text'] );
$image_size     = absint( $slide['image_size'] ?? 60 );
$image_position = $slide['image_position'] ?? 'top';
$image_radius   = absint( $slide['image_radius'] ?? 0 );
$bg_color       = $slide['bg_color'] ?: '#000000';
$text_color     = $slide['text_color'] ?: '#ffffff';

$layout_class = 'agoodsign-image-text--image-' . esc_attr( $image_position );
?>
<div class="agoodsign-slide agoodsign-slide--image-text <?php echo esc_attr( $layout_class ); ?>"
	style="color: <?php echo esc_attr( $text_color ); ?>; background-color: <?php echo esc_attr( $bg_color ); ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<?php if ( ! empty( $slide['image_url'] ) ) : ?>
		<div class="agoodsign-image-text__image"
			style="width: <?php echo absint( $image_size ); ?>%">
			<img src="<?php echo esc_url( $slide['image_url'] ); ?>"
				alt=""
				style="<?php echo $image_radius ? 'border-radius: ' . absint( $image_radius ) . 'px' : ''; ?>;
					object-position: <?php echo floatval( $slide['image_focus_x'] ); ?>% <?php echo floatval( $slide['image_focus_y'] ); ?>%">
		</div>
	<?php endif; ?>

	<?php if ( $has_text ) : ?>
		<div class="agoodsign-image-text__text"
			style="text-align: <?php echo esc_attr( $slide['text_align'] ?? 'center' ); ?>">
			<?php if ( ! empty( $slide['heading'] ) ) : ?>
				<h2 class="agoodsign-slide__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $slide['body_text'] ) ) : ?>
				<p class="agoodsign-slide__body"><?php echo wp_kses_post( $slide['body_text'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php include AGOODSIGN_PLUGIN_DIR . 'templates/slides/partials/pin.php'; ?>
</div>
