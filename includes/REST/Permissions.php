<?php

namespace CopywriteCat\REST;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Permissions {
	public static function must_be_logged_in() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'cwc_forbidden', 'You must be logged in.', [ 'status' => 401 ] );
		}
		return true;
	}

	public static function must_be_designer() {
		$logged_in = self::must_be_logged_in();
		if ( true !== $logged_in ) {
			return $logged_in;
		}
		if ( current_user_can( 'edit_posts' ) ) {
			return true;
		}
		return new WP_Error( 'cwc_forbidden', 'Designer access required.', [ 'status' => 403 ] );
	}

	public static function is_project_member( int $project_id, int $user_id ): bool {
		if ( ! $project_id || ! $user_id ) {
			return false;
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		$clients = (array) get_post_meta( $project_id, 'client_user_ids', true );
		$designers = (array) get_post_meta( $project_id, 'designer_user_ids', true );
		$clients = array_map( 'intval', $clients );
		$designers = array_map( 'intval', $designers );
		return in_array( $user_id, $clients, true ) || in_array( $user_id, $designers, true );
	}
}
