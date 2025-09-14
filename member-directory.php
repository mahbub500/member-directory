<?php
/**
 * Plugin Name: Member Directory
 * Description: Manage members, teams, and their relationships.
 * Plugin URI: https://techwithmahbub.com/
 * Author: Mahbub
 * Author URI: https://techwithmahbub.com/
 * Version: 1.0.0
 * Text Domain: member-directory
 * Domain Path: /languages
 */

namespace MemberDirectory;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

/**
 * Essential Constants
 */
define( 'MD_VERSION', '1.0.0' );
define( 'MD_PLUGIN_FILE', __FILE__ );
define( 'MD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MD_PLUGIN_NAME', 'member-directory' );
define( 'MD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MD_ASSETS_URL', MD_PLUGIN_URL . 'assets/' );
define( 'MD_TABLE_PREFIX', 'md_' );

/**
 * Autoload (Composer)
 */
if ( file_exists( MD_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once MD_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Functions file
 */
if ( file_exists( MD_PLUGIN_DIR . 'includes/functions.php' ) ) {
    require_once MD_PLUGIN_DIR . 'includes/functions.php';
}

/**
 * Activation / Deactivation Hooks
 */
register_activation_hook( __FILE__, array( __NAMESPACE__ . '\App\Controller\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\App\Controller\Deactivator', 'deactivate' ) );

/**
 * Run plugin when all plugins are loaded
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\md_active' );

function md_active() {
    $plugin = new \MemberDirectory\App\Controller\Loader();
    $plugin->run();
}
