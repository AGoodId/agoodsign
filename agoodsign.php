<?php
/**
 * Plugin Name: AGoodSign
 * Plugin URI:  https://github.com/AGoodId/agoodsign
 * Description: Lightweight digital signage plugin for WordPress. Create slides, organize them in channels, and display them on screens.
 * Version:     0.7.0
 * Author:      Mat Singerdal
 * Author URI:  https://github.com/matsingerdal
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: agoodsign
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGOODSIGN_VERSION', '0.7.0' );
define( 'AGOODSIGN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AGOODSIGN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AGOODSIGN_PLUGIN_FILE', __FILE__ );

/**
 * Auto-update from GitHub releases.
 */
require AGOODSIGN_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$agoodsignUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/AGoodId/agoodsign/',
	__FILE__,
	'agoodsign'
);
$agoodsignUpdateChecker->setBranch( 'main' );
$agoodsignUpdateChecker->getVcsApi()->enableReleaseAssets();

/**
 * Load plugin classes.
 */
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-post-type.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-templates.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-meta-box.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-player.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-screens.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-fonts.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-icons.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once AGOODSIGN_PLUGIN_DIR . 'includes/class-channel-editor.php';

/**
 * Initialize all plugin components.
 */
function agoodsign_init() {
	AGoodSign_Post_Type::init();
	AGoodSign_Templates::init();
	AGoodSign_Meta_Box::init();
	AGoodSign_Player::init();
	AGoodSign_Screens::init();
	AGoodSign_Fonts::init();
	AGoodSign_REST_API::init();
	AGoodSign_Channel_Editor::init();
}
add_action( 'plugins_loaded', 'agoodsign_init' );

/**
 * Flush rewrite rules on activation.
 */
function agoodsign_activate() {
	AGoodSign_Post_Type::register();
	AGoodSign_Player::register_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'agoodsign_activate' );

/**
 * Flush rewrite rules on deactivation.
 */
function agoodsign_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'agoodsign_deactivate' );
