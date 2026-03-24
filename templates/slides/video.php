<?php
/**
 * Slide template: Video.
 *
 * Fullscreen video with optional text overlay.
 *
 * @package AGoodSign
 * @var array $slide Slide data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$overlay_class = 'agoodsign-overlay--' . esc_attr( $slide['overlay_position'] );
$has_text      = ! empty( $slide['heading'] ) || ! empty( $slide['body_text'] );
$video_url     = $slide['video_url'];
$video_fit     = $slide['video_fit'] ?? 'cover';

$is_youtube = preg_match( '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video_url, $yt_match );
$is_vimeo   = preg_match( '/vimeo\.com\/([0-9]+)/', $video_url, $vm_match );
?>
<div class="agoodsign-slide agoodsign-slide--video"
	style="color: <?php echo esc_attr( $slide['text_color'] ?: '#ffffff' ); ?>"
	data-duration="<?php echo absint( $slide['duration'] ); ?>"
	data-animation="<?php echo esc_attr( $slide['animation'] ); ?>">

	<?php if ( $is_youtube ) : ?>
		<iframe class="agoodsign-slide__video-embed agoodsign-slide__video-embed--<?php echo esc_attr( $video_fit ); ?>"
			src="https://www.youtube.com/embed/<?php echo esc_attr( $yt_match[1] ); ?>?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&rel=0&playlist=<?php echo esc_attr( $yt_match[1] ); ?>"
			allow="autoplay; encrypted-media"
			allowfullscreen
			frameborder="0"></iframe>
	<?php elseif ( $is_vimeo ) : ?>
		<iframe class="agoodsign-slide__video-embed agoodsign-slide__video-embed--<?php echo esc_attr( $video_fit ); ?>"
			src="https://player.vimeo.com/video/<?php echo esc_attr( $vm_match[1] ); ?>?autoplay=1&muted=1&loop=1&background=1"
			allow="autoplay"
			allowfullscreen
			frameborder="0"></iframe>
	<?php elseif ( ! empty( $video_url ) ) : ?>
		<video class="agoodsign-slide__video agoodsign-slide__video--<?php echo esc_attr( $video_fit ); ?>"
			src="<?php echo esc_url( $video_url ); ?>"
			muted autoplay playsinline></video>
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

	<?php include AGOODSIGN_PLUGIN_DIR . 'templates/slides/partials/pin.php'; ?>
</div>
