<?php
/**
 * Slide template: Split.
 *
 * Image and text side by side (50/50).
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_side = $slide['split_image_side'] ?: 'left';
$modifier   = 'agoodsign-slide--split-image-' . esc_attr( $image_side );
?>
<div class="agoodsign-slide agoodsign-slide--split <?php echo esc_attr( $modifier ); ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<div class="agoodsign-split__image">
		<?php if ( ! empty( $slide['image_url'] ) ) : ?>
			<div class="agoodsign-slide__bg" style="background-image: url('<?php echo esc_url( $slide['image_url'] ); ?>')"></div>
		<?php endif; ?>
	</div>

	<div class="agoodsign-split__text" style="background-color: <?php echo esc_attr( $slide['bg_color'] ); ?>">
		<?php if ( ! empty( $slide['heading'] ) ) : ?>
			<h2 class="agoodsign-slide__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $slide['body_text'] ) ) : ?>
			<p class="agoodsign-slide__body"><?php echo wp_kses_post( $slide['body_text'] ); ?></p>
		<?php endif; ?>
	</div>
</div>
