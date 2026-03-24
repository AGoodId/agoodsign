<?php
/**
 * Slide partial: Pin / Marker overlay.
 *
 * Renders a positioned pin icon with optional label and animation.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $slide['pin_enabled'] ) ) {
	return;
}

$pin_icon      = sanitize_text_field( $slide['pin_icon'] ?: 'map-pin' );
$pin_x         = floatval( $slide['pin_x'] ?? 50 );
$pin_y         = floatval( $slide['pin_y'] ?? 50 );
$pin_color     = sanitize_hex_color( $slide['pin_color'] ?: '#ef4444' ) ?: '#ef4444';
$pin_size      = absint( $slide['pin_size'] ?: 48 );
$pin_label     = $slide['pin_label'] ?? '';
$pin_animation = sanitize_text_field( $slide['pin_animation'] ?: 'pulse' );

$allowed_animations = array( 'pulse', 'bounce', 'glow', 'none' );
if ( ! in_array( $pin_animation, $allowed_animations, true ) ) {
	$pin_animation = 'pulse';
}
?>
<div class="agoodsign-pin agoodsign-pin--<?php echo esc_attr( $pin_animation ); ?>"
	style="left:<?php echo esc_attr( $pin_x ); ?>%;top:<?php echo esc_attr( $pin_y ); ?>%;--pin-color:<?php echo esc_attr( $pin_color ); ?>;--pin-size:<?php echo esc_attr( $pin_size ); ?>px">
	<div class="agoodsign-pin__icon">
		<?php echo AGoodSign_Icons::render( $pin_icon, $pin_size, $pin_color ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup from trusted registry. ?>
	</div>
	<?php if ( ! empty( $pin_label ) ) : ?>
		<span class="agoodsign-pin__label"><?php echo esc_html( $pin_label ); ?></span>
	<?php endif; ?>
</div>
