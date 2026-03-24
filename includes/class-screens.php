<?php
/**
 * Screen management settings page.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Screens {

	const MAX_SCREENS = 5;

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Add admin menu pages.
	 */
	public static function add_menu_pages() {
		add_submenu_page(
			'edit.php?post_type=signage_slide',
			__( 'Screens', 'agoodsign' ),
			__( 'Screens', 'agoodsign' ),
			'manage_options',
			'agoodsign-screens',
			array( __CLASS__, 'render_page' )
		);

		add_submenu_page(
			'edit.php?post_type=signage_slide',
			__( 'Settings', 'agoodsign' ),
			__( 'Settings', 'agoodsign' ),
			'manage_options',
			'agoodsign-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets for screens page.
	 *
	 * @param string $hook_suffix Admin page hook suffix.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'agoodsign-screens' ) ) {
			return;
		}

		wp_enqueue_script(
			'agoodsign-admin-screens',
			AGOODSIGN_PLUGIN_URL . 'assets/js/admin-screens.js',
			array(),
			AGOODSIGN_VERSION,
			true
		);
	}

	/**
	 * Handle screen settings save.
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['agoodsign_screens_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['agoodsign_screens_nonce'], 'agoodsign_save_screens' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save screens.
		if ( isset( $_POST['agoodsign_screens'] ) && is_array( $_POST['agoodsign_screens'] ) ) {
			$screens = array();

			foreach ( $_POST['agoodsign_screens'] as $id => $screen_data ) {
				$id = absint( $id );
				if ( $id < 1 || $id > self::MAX_SCREENS ) {
					continue;
				}

				$screens[ $id ] = array(
					'name'                   => sanitize_text_field( wp_unslash( $screen_data['name'] ?? '' ) ),
					'channel'                => sanitize_text_field( wp_unslash( $screen_data['channel'] ?? '' ) ),
					'active'                 => ! empty( $screen_data['active'] ),
					'use_default_resolution' => ! empty( $screen_data['use_default_resolution'] ),
					'resolution'             => array(
						'width'  => absint( $screen_data['resolution']['width'] ?? 1080 ),
						'height' => absint( $screen_data['resolution']['height'] ?? 1920 ),
					),
				);
			}

			update_option( 'agoodsign_screens', $screens );
		}

		// Save default resolution.
		if ( isset( $_POST['agoodsign_default_resolution'] ) ) {
			$res = $_POST['agoodsign_default_resolution'];
			update_option( 'agoodsign_default_resolution', array(
				'width'  => absint( $res['width'] ?? 1080 ),
				'height' => absint( $res['height'] ?? 1920 ),
			) );
		}

		add_settings_error( 'agoodsign', 'saved', __( 'Screens saved.', 'agoodsign' ), 'success' );
	}

	/**
	 * Render the screens management page.
	 */
	public static function render_page() {
		$screens     = get_option( 'agoodsign_screens', array() );
		$default_res = get_option( 'agoodsign_default_resolution', array( 'width' => 1080, 'height' => 1920 ) );
		$channels    = get_terms( array(
			'taxonomy'   => 'signage_channel',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $channels ) ) {
			$channels = array();
		}

		$presets = array(
			'1080x1920' => __( 'Portrait FHD (1080x1920)', 'agoodsign' ),
			'1920x1080' => __( 'Landscape FHD (1920x1080)', 'agoodsign' ),
			'2160x3840' => __( 'Portrait 4K (2160x3840)', 'agoodsign' ),
			'3840x2160' => __( 'Landscape 4K (3840x2160)', 'agoodsign' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AGoodSign — Screens', 'agoodsign' ); ?></h1>

			<?php settings_errors( 'agoodsign' ); ?>

			<form method="post">
				<?php wp_nonce_field( 'agoodsign_save_screens', 'agoodsign_screens_nonce' ); ?>

				<!-- Default Resolution -->
				<h2><?php esc_html_e( 'Default Resolution', 'agoodsign' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Preset', 'agoodsign' ); ?></th>
						<td>
							<select id="agoodsign-res-preset" onchange="agoodsignApplyPreset(this.value)">
								<?php
								$current_preset = $default_res['width'] . 'x' . $default_res['height'];
								$is_custom      = ! isset( $presets[ $current_preset ] );
								?>
								<?php foreach ( $presets as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_preset, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
								<option value="custom" <?php selected( $is_custom ); ?>><?php esc_html_e( 'Custom', 'agoodsign' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Width x Height', 'agoodsign' ); ?></th>
						<td>
							<input type="number" name="agoodsign_default_resolution[width]" id="agoodsign-res-w"
								value="<?php echo absint( $default_res['width'] ); ?>" min="320" max="7680" style="width:100px">
							&times;
							<input type="number" name="agoodsign_default_resolution[height]" id="agoodsign-res-h"
								value="<?php echo absint( $default_res['height'] ); ?>" min="320" max="7680" style="width:100px">
							px
						</td>
					</tr>
				</table>

				<!-- Screens Table -->
				<h2><?php esc_html_e( 'Screens', 'agoodsign' ); ?></h2>
				<table class="wp-list-table widefat fixed striped" id="agoodsign-screens-table">
					<thead>
						<tr>
							<th style="width:40px">#</th>
							<th><?php esc_html_e( 'Name', 'agoodsign' ); ?></th>
							<th><?php esc_html_e( 'Channel', 'agoodsign' ); ?></th>
							<th><?php esc_html_e( 'Resolution', 'agoodsign' ); ?></th>
							<th style="width:60px"><?php esc_html_e( 'Active', 'agoodsign' ); ?></th>
							<th><?php esc_html_e( 'Display URL', 'agoodsign' ); ?></th>
							<th style="width:80px"><?php esc_html_e( 'Preview', 'agoodsign' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 1; $i <= self::MAX_SCREENS; $i++ ) :
							$screen = $screens[ $i ] ?? array(
								'name'                   => '',
								'channel'                => '',
								'active'                 => false,
								'use_default_resolution' => true,
								'resolution'             => array( 'width' => 1080, 'height' => 1920 ),
							);
							$display_url = home_url( '/signage/screen/' . $i . '/' );
						?>
							<tr>
								<td><strong><?php echo absint( $i ); ?></strong></td>
								<td>
									<input type="text"
										name="agoodsign_screens[<?php echo absint( $i ); ?>][name]"
										value="<?php echo esc_attr( $screen['name'] ); ?>"
										placeholder="<?php esc_attr_e( 'Screen name...', 'agoodsign' ); ?>"
										class="regular-text">
								</td>
								<td>
									<select name="agoodsign_screens[<?php echo absint( $i ); ?>][channel]">
										<option value=""><?php esc_html_e( '— Select channel —', 'agoodsign' ); ?></option>
										<?php foreach ( $channels as $channel ) : ?>
											<option value="<?php echo esc_attr( $channel->slug ); ?>"
												<?php selected( $screen['channel'], $channel->slug ); ?>>
												<?php echo esc_html( $channel->name ); ?>
												(<?php echo absint( $channel->count ); ?>)
											</option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<label style="margin-right:8px">
										<input type="checkbox"
											name="agoodsign_screens[<?php echo absint( $i ); ?>][use_default_resolution]"
											value="1"
											<?php checked( $screen['use_default_resolution'] ); ?>>
										<?php esc_html_e( 'Default', 'agoodsign' ); ?>
									</label>
									<br>
									<input type="number"
										name="agoodsign_screens[<?php echo absint( $i ); ?>][resolution][width]"
										value="<?php echo absint( $screen['resolution']['width'] ?? 1080 ); ?>"
										min="320" max="7680" style="width:70px">
									&times;
									<input type="number"
										name="agoodsign_screens[<?php echo absint( $i ); ?>][resolution][height]"
										value="<?php echo absint( $screen['resolution']['height'] ?? 1920 ); ?>"
										min="320" max="7680" style="width:70px">
								</td>
								<td>
									<input type="checkbox"
										name="agoodsign_screens[<?php echo absint( $i ); ?>][active]"
										value="1"
										<?php checked( $screen['active'] ); ?>>
								</td>
								<td>
									<code class="agoodsign-screen-url"><?php echo esc_html( $display_url ); ?></code>
									<button type="button" class="button button-small agoodsign-copy-url"
										data-url="<?php echo esc_attr( $display_url ); ?>"
										title="<?php esc_attr_e( 'Copy URL', 'agoodsign' ); ?>">
										<span class="dashicons dashicons-clipboard" style="font-size:14px;width:14px;height:14px;vertical-align:middle"></span>
									</button>
								</td>
								<td>
									<button type="button" class="button button-small agoodsign-preview-btn"
										data-url="<?php echo esc_attr( $display_url . '?preview=1' ); ?>"
										data-name="<?php echo esc_attr( $screen['name'] ?: sprintf( __( 'Screen %d', 'agoodsign' ), $i ) ); ?>">
										<span class="dashicons dashicons-visibility" style="font-size:14px;width:14px;height:14px;vertical-align:middle"></span>
									</button>
								</td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Screens', 'agoodsign' ) ); ?>
			</form>
		</div>

		<script>
		function agoodsignApplyPreset( value ) {
			if ( value === 'custom' ) return;
			var parts = value.split( 'x' );
			document.getElementById( 'agoodsign-res-w' ).value = parts[0];
			document.getElementById( 'agoodsign-res-h' ).value = parts[1];
		}
		</script>
		<?php
	}

	/**
	 * Render the settings page (fonts + general).
	 */
	public static function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AGoodSign — Settings', 'agoodsign' ); ?></h1>
			<p><?php esc_html_e( 'Font settings and general configuration.', 'agoodsign' ); ?></p>
			<?php
			// Font settings are rendered by AGoodSign_Fonts.
			AGoodSign_Fonts::render_settings_section();
			?>
		</div>
		<?php
	}
}
