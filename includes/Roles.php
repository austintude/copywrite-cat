<?php

namespace CopywriteCat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Roles {
	public const ROLE_CLIENT = 'cwc_client';

	public function add_roles(): void {
		if ( get_role( self::ROLE_CLIENT ) ) {
			return;
		}

		add_role(
			self::ROLE_CLIENT,
			'Copywriter Cat Client',
			[
				'read' => true,
				'cwc_portal_access' => true,
			]
		);
	}

	public function is_client(): bool {
		$user = wp_get_current_user();
		return $user && in_array( self::ROLE_CLIENT, (array) $user->roles, true );
	}

	public function maybe_lockout_client_role(): void {
		if ( ! $this->is_client() ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'profile' === $screen->base ) {
			return; // allow profile edits for now.
		}

		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	public function maybe_hide_admin_bar(): void {
		if ( $this->is_client() ) {
			show_admin_bar( false );
		}
	}
}
