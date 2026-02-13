<?php

namespace CopywriteCat\Admin;

use CopywriteCat\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsPage {
	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_init', [ $this, 'register' ] );
	}

	public function menu(): void {
		add_options_page(
			'Copywriter Cat',
			'Copywriter Cat',
			'manage_options',
			'copywrite-cat',
			[ $this, 'render' ]
		);
	}

	public function register(): void {
		register_setting( 'cwc_settings', Settings::OPTION_PORTAL_PAGE_ID, [
			'type' => 'integer',
			'sanitize_callback' => 'absint',
			'default' => 0,
		] );

		add_settings_section( 'cwc_main', 'Main', '__return_null', 'copywrite-cat' );

		add_settings_field(
			'cwc_portal_page_id',
			'Portal Page',
			[ $this, 'field_portal_page' ],
			'copywrite-cat',
			'cwc_main'
		);
	}

	public function field_portal_page(): void {
		$value = (int) get_option( Settings::OPTION_PORTAL_PAGE_ID, 0 );
		wp_dropdown_pages(
			[
				'name' => Settings::OPTION_PORTAL_PAGE_ID,
				'show_option_none' => '— Select —',
				'option_none_value' => 0,
				'selected' => $value,
			]
		);
		$url = Settings::get_portal_url();
		echo '<p class="description">Client role is redirected here when trying to access wp-admin. Current URL: <a href="' . esc_url( $url ) . '" target="_blank" rel="noreferrer">' . esc_html( $url ) . '</a></p>';
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap">';
		echo '<h1>Copywriter Cat Settings</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( 'cwc_settings' );
		do_settings_sections( 'copywrite-cat' );
		submit_button();
		echo '</form>';
		echo '</div>';
	}
}
