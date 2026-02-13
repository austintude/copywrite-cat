<?php

namespace CopywriteCat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	public const DB_VERSION = 1;
	public const OPTION_DB_VERSION = 'cwc_db_version';

	public function hooks(): void {
		add_action( 'init', [ $this, 'register_cpts' ] );
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'init', [ $this, 'register_shortcodes' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_action( 'admin_init', [ $this, 'maybe_lockout_client_role' ] );
		add_action( 'wp_before_admin_bar_render', [ $this, 'maybe_hide_admin_bar' ] );

		( new Admin\SettingsPage() )->hooks();
	}

	public function activate(): void {
		( new Roles() )->add_roles();
		( new DB\Migrations() )->migrate();
		$this->ensure_portal_page();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		flush_rewrite_rules();
	}

	public function register_blocks(): void {
		$slot_block_json = dirname( __DIR__ ) . '/build/blocks/slot/block.json';
		if ( file_exists( $slot_block_json ) ) {
			register_block_type( dirname( __DIR__ ) . '/build/blocks/slot' );
		}
	}

	public function register_cpts(): void {
		( new CPT\ProjectCPT() )->register();
		( new CPT\SlotCPT() )->register();
		( new CPT\BrandGuideCPT() )->register();
	}

	public function register_rest_routes(): void {
		( new REST\RestBootstrap() )->register();
	}

	public function register_shortcodes(): void {
		add_shortcode( 'copywrite_cat_portal', [ $this, 'render_portal_shortcode' ] );
	}

	private function ensure_portal_page(): void {
		$page_id = (int) get_option( Settings::OPTION_PORTAL_PAGE_ID, 0 );
		if ( $page_id && get_post( $page_id ) ) {
			return;
		}

		// Create a portal page so clients have a single front-end entry point.
		$new_id = wp_insert_post(
			[
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_title' => 'Copywriter Cat Portal',
				'post_content' => '[copywrite_cat_portal]',
			]
		);
		if ( ! is_wp_error( $new_id ) && $new_id ) {
			update_option( Settings::OPTION_PORTAL_PAGE_ID, (int) $new_id );
		}
	}

	public function render_portal_shortcode( $atts = [] ): string {
		if ( ! is_user_logged_in() ) {
			return '<p>You must be logged in to access the Copywriter Cat portal.</p>';
		}

		$asset = dirname( __DIR__ ) . '/build/portal/index.asset.php';
		$asset_data = file_exists( $asset ) ? require $asset : [ 'dependencies' => [ 'wp-element', 'wp-api-fetch' ], 'version' => null ];

		wp_enqueue_script( 'cwc-portal', plugins_url( 'build/portal/index.js', dirname( __DIR__ ) . '/copywrite-cat.php' ), $asset_data['dependencies'], $asset_data['version'], true );

		wp_add_inline_script(
			'cwc-portal',
			'window.CWC_PORTAL = ' . wp_json_encode(
				[
					'restUrl'  => esc_url_raw( rest_url( 'copywrite-cat/v1' ) ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'userId'   => get_current_user_id(),
				]
			) . ';',
			'before'
		);

		return '<div id="cwc-portal-root"></div>';
	}

	public function maybe_lockout_client_role(): void {
		( new Roles() )->maybe_lockout_client_role();
	}

	public function maybe_hide_admin_bar(): void {
		( new Roles() )->maybe_hide_admin_bar();
	}
}
