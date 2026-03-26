<?php
/**
 * Slide template: Title Card.
 *
 * Large centered title with subtitle and decorative elements.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_bg_image  = ! empty( $slide['image_url'] );
$font_styles   = '';
$font_styles  .= ! empty( $slide['heading_size'] ) ? '--agoodsign-heading-size:' . absint( $slide['heading_size'] ) . 'px;' : '';
$font_styles  .= ! empty( $slide['body_size'] ) ? '--agoodsign-body-size:' . absint( $slide['body_size'] ) . 'px;' : '';
?>
<div class="agoodsign-slide agoodsign-slide--title-card"
	style="color: <?php echo esc_attr( $slide['text_color'] ?: '#ffffff' ); ?>;<?php if ( ! $has_bg_image ) : ?> background-color: <?php echo esc_attr( $slide['bg_color'] ); ?>;<?php endif; ?> <?php echo $font_styles; ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<?php if ( $has_bg_image ) : ?>
		<div class="agoodsign-slide__bg agoodsign-title-card__bg-darken" style="background-image: url('<?php echo esc_url( $slide['image_url'] ); ?>'); background-position: <?php echo floatval( $slide['image_focus_x'] ); ?>% <?php echo floatval( $slide['image_focus_y'] ); ?>%"></div>
	<?php endif; ?>

	<div class="agoodsign-title-card__content" style="text-align: <?php echo esc_attr( $slide['text_align'] ?? 'center' ); ?>">
		<div class="agoodsign-title-card__border-top"></div>

		<?php if ( ! empty( $slide['heading'] ) ) : ?>
			<h2 class="agoodsign-slide__heading agoodsign-title-card__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
		<?php endif; ?>

		<div class="agoodsign-title-card__divider"></div>

		<?php if ( ! empty( $slide['body_text'] ) ) : ?>
			<p class="agoodsign-slide__body agoodsign-title-card__subtitle"><?php echo wp_kses_post( nl2br( $slide['body_text'] ) ); ?></p>
		<?php endif; ?>

		<div class="agoodsign-title-card__border-bottom"></div>
	</div>

	<?php include AGOODSIGN_PLUGIN_DIR . 'templates/slides/partials/pin.php'; ?>
</div>
