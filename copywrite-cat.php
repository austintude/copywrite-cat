<?php
/**
 * Plugin Name: Copywrite Cat
 * Description: Human-led. Cat-assisted. On-brand. Collaborate on website copy using slot-based workflow embedded in Gutenberg pages.
 * Version: 0.0.1
 * Author: austintude
 * Text Domain: copywrite-cat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Simple PSR-4-ish autoload for our includes/ directory (MVP).
spl_autoload_register(
	static function ( $class ) {
		if ( 0 !== strpos( $class, 'CopywriteCat\\' ) ) {
			return;
		}
		$rel = str_replace( 'CopywriteCat\\', '', $class );
		$rel = str_replace( '\\', DIRECTORY_SEPARATOR, $rel );
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $rel . '.php';
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

$plugin = new CopywriteCat\Plugin();
$plugin->hooks();

register_activation_hook( __FILE__, [ $plugin, 'activate' ] );
register_deactivation_hook( __FILE__, [ $plugin, 'deactivate' ] );
