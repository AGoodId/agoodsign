<?php
/**
 * Font management — curated Google Fonts + custom upload.
 *
 * @package AGoodSign
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGoodSign_Fonts {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Get curated list of Google Fonts suitable for signage.
	 *
	 * @return array Font family names.
	 */
	public static function get_font_list() {
		return array(
			''                  => __( '— System default —', 'agoodsign' ),
			'Inter'             => 'Inter',
			'Roboto'            => 'Roboto',
			'Open Sans'         => 'Open Sans',
			'Montserrat'        => 'Montserrat',
			'Poppins'           => 'Poppins',
			'Lato'              => 'Lato',
			'Oswald'            => 'Oswald',
			'Raleway'           => 'Raleway',
			'Playfair Display'  => 'Playfair Display',
			'Merriweather'      => 'Merriweather',
			'Nunito'            => 'Nunito',
			'Bebas Neue'        => 'Bebas Neue',
			'Archivo Black'     => 'Archivo Black',
			'DM Sans'           => 'DM Sans',
			'DM Serif Display'  => 'DM Serif Display',
			'Space Grotesk'     => 'Space Grotesk',
			'Sora'              => 'Sora',
			'Outfit'            => 'Outfit',
			'Plus Jakarta Sans' => 'Plus Jakarta Sans',
			'Barlow'            => 'Barlow',
			'Barlow Condensed'  => 'Barlow Condensed',
			'Rubik'             => 'Rubik',
			'Work Sans'         => 'Work Sans',
			'Cabin'             => 'Cabin',
			'Quicksand'         => 'Quicksand',
		);
	}

	/**
	 * Render font @font-face styles for the player.
	 * Loads from Google Fonts CDN (WOFF2, display=swap).
	 */
	public static function render_font_styles() {
		$heading = get_option( 'agoodsign_font_heading', '' );
		$body    = get_option( 'agoodsign_font_body', '' );
		$custom  = get_option( 'agoodsign_custom_fonts', array() );

		$families = array_filter( array_unique( array( $heading, $body ) ) );

		// Load Google Fonts.
		if ( ! empty( $families ) ) {
			$families_encoded = array_map( function ( $f ) {
				return str_replace( ' ', '+', $f ) . ':wght@400;700';
			}, $families );

			$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families_encoded ) . '&display=swap';
			echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
			echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
			echo '<link rel="stylesheet" href="' . esc_url( $url ) . '">' . "\n";
		}

		// Load custom uploaded fonts.
		if ( ! empty( $custom ) ) {
			echo "<style>\n";
			foreach ( $custom as $font ) {
				$font_url = esc_url( $font['url'] );
				$font_name = esc_attr( $font['name'] );
				echo "@font-face {\n";
				echo "\tfont-family: '{$font_name}';\n";
				echo "\tsrc: url('{$font_url}') format('woff2');\n";
				echo "\tfont-weight: 400;\n";
				echo "\tfont-style: normal;\n";
				echo "\tfont-display: swap;\n";
				echo "}\n";
			}
			echo "</style>\n";
		}
	}

	/**
	 * Render the font settings section on the settings page.
	 */
	public static function render_settings_section() {
		$heading      = get_option( 'agoodsign_font_heading', '' );
		$body         = get_option( 'agoodsign_font_body', '' );
		$fonts        = self::get_font_list();
		$custom_fonts = get_option( 'agoodsign_custom_fonts', array() );
		foreach ( $custom_fonts as $font ) {
			$fonts[ $font['name'] ] = $font['name'];
		}

		settings_errors( 'agoodsign_fonts' );
		?>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'agoodsign_save_fonts', 'agoodsign_fonts_nonce' ); ?>

			<h2><?php esc_html_e( 'Fonts', 'agoodsign' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="agoodsign-font-heading"><?php esc_html_e( 'Heading Font', 'agoodsign' ); ?></label>
					</th>
					<td>
						<select name="agoodsign_font_heading" id="agoodsign-font-heading" style="min-width:250px">
							<?php foreach ( $fonts as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"
									<?php selected( $heading, $value ); ?>
									<?php if ( $value ) : ?>style="font-family:'<?php echo esc_attr( $value ); ?>',sans-serif"<?php endif; ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<span id="agoodsign-heading-preview" style="font-size:24px;font-weight:700;display:block;margin-top:8px">
								<?php esc_html_e( 'Preview Heading Text', 'agoodsign' ); ?>
							</span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="agoodsign-font-body"><?php esc_html_e( 'Body Font', 'agoodsign' ); ?></label>
					</th>
					<td>
						<select name="agoodsign_font_body" id="agoodsign-font-body" style="min-width:250px">
							<?php foreach ( $fonts as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"
									<?php selected( $body, $value ); ?>
									<?php if ( $value ) : ?>style="font-family:'<?php echo esc_attr( $value ); ?>',sans-serif"<?php endif; ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<span id="agoodsign-body-preview" style="font-size:16px;display:block;margin-top:8px">
								<?php esc_html_e( 'Preview body text that appears on your signage slides.', 'agoodsign' ); ?>
							</span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="agoodsign-font-upload"><?php esc_html_e( 'Upload Custom Font', 'agoodsign' ); ?></label>
					</th>
					<td>
						<input type="file" name="agoodsign_font_upload" id="agoodsign-font-upload"
							accept=".woff2,.woff,.ttf">
						<p class="description"><?php esc_html_e( 'Upload a .woff2, .woff, or .ttf file. The font will be available in the dropdowns after upload.', 'agoodsign' ); ?></p>

						<?php
						$custom_fonts = get_option( 'agoodsign_custom_fonts', array() );
						if ( ! empty( $custom_fonts ) ) :
						?>
							<h4 style="margin-top:16px"><?php esc_html_e( 'Uploaded Fonts', 'agoodsign' ); ?></h4>
							<ul>
								<?php foreach ( $custom_fonts as $idx => $font ) : ?>
									<li>
										<?php echo esc_html( $font['name'] ); ?>
										<label>
											<input type="checkbox" name="agoodsign_remove_font[]" value="<?php echo absint( $idx ); ?>">
											<?php esc_html_e( 'Remove', 'agoodsign' ); ?>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Font Settings', 'agoodsign' ) ); ?>
		</form>

		<script>
		( function () {
			var customFontNames = <?php echo wp_json_encode( array_column( get_option( 'agoodsign_custom_fonts', array() ), 'name' ) ); ?>;
			var customFontUrls  = <?php
				$pairs = array();
				foreach ( get_option( 'agoodsign_custom_fonts', array() ) as $f ) {
					$pairs[ $f['name'] ] = $f['url'];
				}
				echo wp_json_encode( $pairs );
			?>;

			var headingSel    = document.getElementById( 'agoodsign-font-heading' );
			var bodySel       = document.getElementById( 'agoodsign-font-body' );
			var headingPreview = document.getElementById( 'agoodsign-heading-preview' );
			var bodyPreview   = document.getElementById( 'agoodsign-body-preview' );

			function loadFont( family ) {
				if ( ! family ) return;
				var id = 'agoodsign-preview-font-' + family.replace( /\s+/g, '-' );
				if ( document.getElementById( id ) ) return;

				if ( customFontUrls[ family ] ) {
					// Custom uploaded font — inject @font-face.
					var style = document.createElement( 'style' );
					style.id = id;
					style.textContent = "@font-face { font-family: '" + family + "'; src: url('" + customFontUrls[ family ] + "'); font-display: swap; }";
					document.head.appendChild( style );
				} else {
					// Google Font.
					var link = document.createElement( 'link' );
					link.id = id;
					link.rel = 'stylesheet';
					link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent( family ) + ':wght@400;700&display=swap';
					document.head.appendChild( link );
				}
			}

			function updatePreview() {
				var hFont = headingSel.value;
				var bFont = bodySel.value;
				loadFont( hFont );
				loadFont( bFont );
				headingPreview.style.fontFamily = hFont ? "'" + hFont + "', sans-serif" : 'sans-serif';
				bodyPreview.style.fontFamily = bFont ? "'" + bFont + "', sans-serif" : 'sans-serif';
			}

			headingSel.addEventListener( 'change', updatePreview );
			bodySel.addEventListener( 'change', updatePreview );
			updatePreview();
		} )();
		</script>
		<?php
	}

	/**
	 * Handle font settings save.
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['agoodsign_fonts_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['agoodsign_fonts_nonce'], 'agoodsign_save_fonts' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save font selections.
		if ( isset( $_POST['agoodsign_font_heading'] ) ) {
			update_option( 'agoodsign_font_heading', sanitize_text_field( wp_unslash( $_POST['agoodsign_font_heading'] ) ) );
		}

		if ( isset( $_POST['agoodsign_font_body'] ) ) {
			update_option( 'agoodsign_font_body', sanitize_text_field( wp_unslash( $_POST['agoodsign_font_body'] ) ) );
		}

		// Handle font upload.
		if ( ! empty( $_FILES['agoodsign_font_upload']['name'] ) ) {
			self::handle_font_upload();
		}

		// Handle font removal.
		if ( ! empty( $_POST['agoodsign_remove_font'] ) && is_array( $_POST['agoodsign_remove_font'] ) ) {
			$custom_fonts = get_option( 'agoodsign_custom_fonts', array() );
			foreach ( $_POST['agoodsign_remove_font'] as $idx ) {
				unset( $custom_fonts[ absint( $idx ) ] );
			}
			update_option( 'agoodsign_custom_fonts', array_values( $custom_fonts ) );
		}

		add_settings_error( 'agoodsign_fonts', 'saved', __( 'Font settings saved.', 'agoodsign' ), 'success' );
	}

	/**
	 * Handle font file upload.
	 */
	private static function handle_font_upload() {
		$allowed_types = array(
			'woff2' => 'font/woff2',
			'woff'  => 'font/woff',
			'ttf'   => 'font/ttf',
		);

		$file = $_FILES['agoodsign_font_upload'];
		$ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( ! isset( $allowed_types[ $ext ] ) ) {
			add_settings_error( 'agoodsign_fonts', 'invalid_type', __( 'Invalid font file type. Allowed: .woff2, .woff, .ttf', 'agoodsign' ), 'error' );
			return;
		}

		// Allow font MIME types.
		add_filter( 'upload_mimes', function ( $mimes ) use ( $allowed_types ) {
			return array_merge( $mimes, $allowed_types );
		} );

		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( isset( $upload['error'] ) ) {
			add_settings_error( 'agoodsign_fonts', 'upload_error', $upload['error'], 'error' );
			return;
		}

		$font_name = pathinfo( $file['name'], PATHINFO_FILENAME );
		$font_name = ucwords( str_replace( array( '-', '_' ), ' ', $font_name ) );

		$custom_fonts   = get_option( 'agoodsign_custom_fonts', array() );
		$custom_fonts[] = array(
			'name' => sanitize_text_field( $font_name ),
			'url'  => esc_url_raw( $upload['url'] ),
			'file' => $upload['file'],
		);
		update_option( 'agoodsign_custom_fonts', $custom_fonts );
	}
}
