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

function cwc_register_blocks() {
	$slot_block_json = __DIR__ . '/build/blocks/slot/block.json';
	if ( file_exists( $slot_block_json ) ) {
		register_block_type( __DIR__ . '/build/blocks/slot' );
	}
}
add_action( 'init', 'cwc_register_blocks' );
